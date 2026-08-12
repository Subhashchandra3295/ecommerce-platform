# Deploying to Railway + Vercel

The API, MySQL, and Redis go on **Railway**; the Next.js storefront goes on
**Vercel**. Deploy in this order: **Railway → Vercel → back to Railway once
to fix CORS** — the storefront's `NEXT_PUBLIC_API_URL` is baked in at build
time (needs the Railway URL first), and the API's `CORS_ALLOWED_ORIGINS`
needs the storefront's final URL.

## 1. Railway — API + MySQL + Redis

1. [railway.app](https://railway.app) → **New Project** → **Deploy from GitHub repo** → select `ecommerce-platform`.
2. On the service Railway creates, set **Root Directory** to `apps/api`. Railway detects the `Dockerfile` there (it runs migrations and starts a background queue worker alongside the web process on boot — see the `CMD` in `apps/api/Dockerfile`).
3. **+ New → Database → Add MySQL**, and **+ New → Database → Add Redis**.
4. Generate a stable `APP_KEY` locally — `php artisan key:generate --show` in `apps/api` — and use that exact value in production. Don't let it regenerate on every deploy; that invalidates all existing sessions and encrypted data.
5. Open the API service's **Variables** tab and add:

   | Variable | Value |
   |---|---|
   | `APP_KEY` | the value from step 4 |
   | `APP_ENV` | `production` |
   | `APP_DEBUG` | `false` |
   | `DB_URL` | reference the MySQL plugin's connection URL variable from the dropdown |
   | `REDIS_CLIENT` | `predis` |
   | `REDIS_URL` | reference the Redis plugin's connection URL variable from the dropdown |
   | `CACHE_STORE` | `redis` |
   | `QUEUE_CONNECTION` | `redis` |
   | `SESSION_DRIVER` | `database` |
   | `FRONTEND_URL` | `http://localhost:3000` for now — update after step 2 |
   | `CORS_ALLOWED_ORIGINS` | same placeholder, same reason |
   | `STRIPE_SECRET_KEY` | your Stripe **test-mode** secret key |
   | `STRIPE_WEBHOOK_SECRET` | see step 6 |
   | `PORT` | `4000` |

   **Set `PORT` explicitly.** Railway injects its own default port that
   silently overrides the app's fallback — [saas-pm-platform](https://github.com/Subhashchandra3295/saas-pm-platform)'s
   `DEPLOYMENT.md` has the full story on the 502s this causes if skipped.

6. **Settings → Networking → Generate Domain**, port **4000** — copy the resulting URL. Then in the [Stripe dashboard](https://dashboard.stripe.com/test/webhooks), add an endpoint at `https://<railway-domain>/api/stripe/webhook` listening for `checkout.session.completed`, and copy its signing secret into `STRIPE_WEBHOOK_SECRET` above.
7. Confirm it's live: `curl https://<railway-domain>/api/categories` should return `[]` (or your seeded categories, if you ran `php artisan db:seed` against the Railway MySQL instance).

## 2. Vercel — Web

1. [vercel.com](https://vercel.com) → **Add New → Project** → import the same `ecommerce-platform` GitHub repo.
2. Set **Root Directory** to `apps/web`. Framework preset (Next.js) is auto-detected.
3. Add an environment variable: `NEXT_PUBLIC_API_URL` = the Railway domain from step 1.6 (no trailing slash).
4. Deploy. Copy the resulting URL.

## 3. Back to Railway — fix CORS + redirects

1. API service → **Variables** → set both `FRONTEND_URL` and `CORS_ALLOWED_ORIGINS` to the Vercel URL from step 2.4. (`FRONTEND_URL` drives the Stripe Checkout success/cancel redirect targets; `CORS_ALLOWED_ORIGINS` gates the browser API calls — they should match.)
2. Railway redeploys automatically on variable change.

## 4. Verify

Visit the Vercel URL, register, browse products, add one to the cart, and
check out with Stripe's test card `4242 4242 4242 4242` (any future expiry,
any CVC). After redirect, confirm the order shows `paid` — this proves the
webhook reached Railway and the queued `ProcessPaidOrder` job ran.

## Notes

- Local `docker-compose.yml` is for development only — Railway's MySQL/Redis
  plugins replace it in production.
- The queue worker runs as a background process inside the same container as
  the web server (see the Dockerfile comment) — a deliberate simplification
  for a single Railway service. A larger deployment would run it as its own
  service so it restarts independently of the web process.
- Rotate `STRIPE_SECRET_KEY`/`APP_KEY` if either is ever exposed.
