# ShopCraft

A small e-commerce platform — product catalog, cart, and a real Stripe
Checkout flow — built to demonstrate production-style Laravel: queue-driven
order processing, a server-rendered admin panel, and a typed Next.js
storefront talking to a Laravel API.

**Live demo:** _deploying — see [DEPLOYMENT.md](./DEPLOYMENT.md) for the Railway + Vercel setup._

## Problem

Most "e-commerce demo" portfolio pieces stop at a fake cart. This one carries
a purchase all the way through: a real Stripe Checkout Session, a signed
webhook that confirms payment, and a queued job that decrements stock and
clears the cart — the parts that actually make a checkout flow correct under
concurrent orders.

## Architecture

```
┌──────────────────┐        HTTPS/JSON        ┌───────────────────────┐
│   Next.js Web     │ ───────────────────────▶ │      Laravel API      │
│  (App Router, TS,  │ ◀─────────────────────── │  (Sanctum token auth, │
│   Tailwind)         │                          │   validation)         │
└──────────────────┘                          └───────────┬───────────┘
                                                             │
                            ┌────────────────────────────────┼────────────────────────────────┐
                            ▼                                ▼                                ▼
                   ┌────────────────┐             ┌──────────────────┐             ┌──────────────────────┐
                   │     MySQL       │             │       Redis        │             │        Stripe          │
                   │ (Eloquent ORM,  │             │  (cache + queue)   │             │  Checkout Sessions +    │
                   │  products/carts/│             │                    │             │  signed webhooks        │
                   │  orders)        │             └──────────────────┘             └──────────────────────┘
                   └────────────────┘                       │
                                                              ▼
                                                    ┌──────────────────────┐
                                                    │  ProcessPaidOrder job  │
                                                    │  (decrements stock,    │
                                                    │   clears cart)         │
                                                    └──────────────────────┘
```

- **Auth**: Laravel Sanctum, token-based (not cookie/SPA-stateful) — the
  storefront and API run on different origins in production, so a Bearer
  token avoids cross-domain cookie complications. The admin panel uses a
  separate session-based guard (`web`) gated by an `is_admin` flag.
- **Checkout**: `CheckoutController` snapshots the cart into an `Order` +
  `OrderItem`s (status `pending`) inside a transaction, then creates a Stripe
  Checkout Session from that snapshot. This means the order and its priced
  line items exist independently of what Stripe returns later — no need to
  reconstruct them from Stripe's API on the way back.
- **Webhook → queue**: `StripeWebhookController` verifies the signature,
  flips the order to `paid`, and dispatches `ProcessPaidOrder` onto a Redis
  queue rather than doing stock decrement / cart-clearing inline on the
  webhook request — the same "background job for the slow/side-effecting
  part" pattern as [saas-pm-platform](https://github.com/Subhashchandra3295/saas-pm-platform)'s
  BullMQ jobs, here in the PHP/Laravel ecosystem.
- **Admin panel**: server-rendered Blade + Tailwind, no separate SPA — product
  CRUD and order status management, gated by `EnsureUserIsAdmin` middleware.

## Stack

| Layer | Tech |
|---|---|
| Frontend | Next.js 16 (App Router, TypeScript, Tailwind v4) |
| Backend | Laravel 13, Sanctum, MySQL |
| Cache / Queue | Redis |
| Payments | Stripe Checkout Sessions + webhooks |
| Infra | Docker Compose, GitHub Actions CI |
| Tests | PHPUnit (23 feature tests: auth, cart ownership, checkout guards, webhook signature verification), Playwright (manual browser verification) |

## Project layout

```
apps/
  api/   Laravel backend (auth, catalog, cart, checkout, admin panel)
  web/   Next.js storefront (product list/detail, cart, orders)
docker-compose.yml          MySQL + Redis + api + web, wired together
.github/workflows/ci.yml    Pint + PHPUnit for the API, lint + build for the web
```

## Running locally

### Option A — Docker Compose (closest to production)

```bash
docker compose up -d --build
# web:   http://localhost:3000
# api:   http://localhost:4000
# admin: http://localhost:4000/admin (admin@shopcraft.test / password, after seeding)
```

### Option B — Native dev (hot reload)

```bash
# 1. Start just the datastores
docker compose up -d mysql redis

# 2. API
cd apps/api
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed   # seeds an admin user + sample catalog
npm install && npm run build # compiles the admin panel's Tailwind CSS
php artisan serve --port=8000

# In a separate terminal, run the queue worker (required for order
# confirmation — stock decrement / cart clearing happen here, not inline):
php artisan queue:work

# 3. Web (separate terminal)
cd apps/web
cp .env.local.example .env.local
npm install
npm run dev # http://localhost:3000
```

To test the Stripe flow locally, forward webhooks with the
[Stripe CLI](https://stripe.com/docs/stripe-cli):

```bash
stripe listen --forward-to localhost:8000/api/stripe/webhook --events checkout.session.completed
```

Copy the webhook signing secret it prints into `STRIPE_WEBHOOK_SECRET` in
`apps/api/.env`, and use a real Stripe test-mode secret key for
`STRIPE_SECRET_KEY` (from [dashboard.stripe.com/test/apikeys](https://dashboard.stripe.com/test/apikeys)).
Test card: `4242 4242 4242 4242`, any future expiry, any CVC.

## Testing

```bash
cd apps/api
./vendor/bin/pint --test  # code style
php artisan test          # 23 feature tests — in-memory SQLite, no external services needed

cd apps/web
npm run lint
npm run build              # type-checks the whole app
```

## Verification checklist

- [x] `docker compose up -d --build` reproduces the full stack from a fresh clone
- [x] CI (`.github/workflows/ci.yml`) runs Pint + PHPUnit for the API and lint + build for the web on every push
- [x] Auth, cart ownership (a user cannot touch another user's cart items), checkout guards (empty cart, insufficient stock), and Stripe webhook signature verification are covered by tests
- [x] A real Stripe test-mode Checkout Session was completed end to end (test card `4242...`), confirming: webhook delivery → order marked `paid` → queued job decrements stock and clears the cart
- [x] Golden path (browse → filter by category → register → add to cart → view cart) verified in a real browser via Playwright, zero console errors
