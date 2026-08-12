# Deploying to Render + Vercel

The API, Postgres, and Redis (via Render's Key Value service) go on
**Render**; the Next.js storefront goes on **Vercel**. Render's free tier
doesn't offer managed MySQL — only PostgreSQL — so the deployed environment
uses `pgsql` while local dev/tests still use MySQL/SQLite (the app is
DB-agnostic; nothing in the codebase depends on MySQL-specific SQL).

Deploy in this order: **Render → Vercel → back to Render once to fix CORS.**

## 1. Render — API + Postgres + Redis (Key Value)

1. [dashboard.render.com](https://dashboard.render.com) → **New +** → **PostgreSQL**. Free plan. Note: Render's free Postgres **expires 30 days after creation** — fine for a portfolio demo, but not for anything long-lived without upgrading.
2. **New +** → **Key Value** (Render's Redis-compatible store). Free plan, memory policy "cache" (`allkeys_lru`).
3. **New +** → **Web Service** → connect the `ecommerce-platform` GitHub repo. Set **Root Directory** to `apps/api`; Render detects the `Dockerfile` there.
4. Generate a stable `APP_KEY` locally — `php artisan key:generate --show` in `apps/api` — and use that exact value in production. Don't let it regenerate on every deploy; that invalidates sessions and encrypted data.
5. On the web service, add environment variables:

   | Variable | Value |
   |---|---|
   | `APP_KEY` | the value from step 4 |
   | `APP_ENV` | `production` |
   | `APP_DEBUG` | `false` |
   | `DB_CONNECTION` | `pgsql` |
   | `DB_URL` | the Postgres instance's **internal** connection string (from its Render dashboard page) |
   | `REDIS_CLIENT` | `predis` |
   | `REDIS_URL` | the Key Value instance's **internal** connection string |
   | `CACHE_STORE` | `redis` |
   | `QUEUE_CONNECTION` | `redis` |
   | `SESSION_DRIVER` | `database` |
   | `FRONTEND_URL` | `http://localhost:3000` for now — update after step 2 |
   | `CORS_ALLOWED_ORIGINS` | same placeholder, same reason |
   | `STRIPE_SECRET_KEY` | your Stripe **test-mode** secret key |
   | `STRIPE_WEBHOOK_SECRET` | see step 6 |
   | `PORT` | `4000` |

6. Once the first deploy is live, copy its `onrender.com` URL. In the [Stripe dashboard](https://dashboard.render.com/test/webhooks), add an endpoint at `https://<render-url>/api/stripe/webhook` listening for `checkout.session.completed`, and copy its signing secret into `STRIPE_WEBHOOK_SECRET`.
7. Seed the catalog. Render's free plan doesn't support one-off Jobs, and SSH needs a registered key, so the practical path is: temporarily add your local IP to the Postgres instance's **IP Allow List** (dashboard → the Postgres instance → Access Control), then run `php artisan db:seed --force` from your machine with `DB_CONNECTION=pgsql DB_URL=<external connection string>` set inline. **Remove the IP allow-list entry again afterward** — the running app doesn't need external access, it talks to Postgres over Render's private network.
8. Confirm it's live: `curl https://<render-url>/api/categories` should return your seeded categories.

## 2. Vercel — Web

1. [vercel.com](https://vercel.com) → **Add New → Project** → import the same `ecommerce-platform` GitHub repo.
2. Set **Root Directory** to `apps/web`. Framework preset (Next.js) is auto-detected.
3. Add an environment variable: `NEXT_PUBLIC_API_URL` = the Render URL from step 1.6 (no trailing slash).
4. Deploy. Copy the resulting URL.

## 3. Back to Render — fix CORS + redirects

1. Web service → **Environment** → set both `FRONTEND_URL` and `CORS_ALLOWED_ORIGINS` to the Vercel URL from step 2.4.
2. **Trigger a manual deploy, not just a restart.** Render's own "Restart" action reuses the already-running instance and does **not** pick up new environment variable values — only a fresh deploy does. This cost real debugging time: CORS kept reporting the old `localhost:3000` origin after a restart, and only cleared once a new deploy actually ran.

## 4. Verify

Visit the Vercel URL, register, browse products, add one to the cart, and
check out with Stripe's test card `4242 4242 4242 4242` (any future expiry,
any CVC, **and fill in the email field** — Stripe Checkout requires it, and
skipping it silently blocks the "Pay" button with no visible error until you
look closely). After redirect, confirm the order shows `paid` — this proves
the webhook reached Render and the queued `ProcessPaidOrder` job ran.

## Notes

- Local `docker-compose.yml` (MySQL + Redis) is for development only — Render's
  Postgres/Key Value replace it in production.
- The queue worker runs as a background process inside the same container as
  the web server (see the Dockerfile comment) — a deliberate simplification
  for a single free-tier service.
- Render's free Postgres expires 30 days after creation. If the live demo
  stops responding with DB errors after that window, that's why.
- Rotate `STRIPE_SECRET_KEY`/`APP_KEY` if either is ever exposed.
