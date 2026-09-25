# ShopWave API (Laravel)

The headless backend for [ShopWave](../../README.md) — a strictly REST API with no server-rendered views, consumed by `apps/web`.

## Stack

Laravel 13 · PHP 8.4 · PostgreSQL 16 · Redis · Laravel Sanctum · spatie/laravel-permission · Laravel Reverb · Stripe Connect · nginx + php-fpm (Docker)

## Running

**Docker (recommended)** — from the repo root:
```bash
make up
```
This runs the `api` (nginx + php-fpm), `queue` (worker), and `reverb` (websockets) containers together against the shared `postgres`/`redis` containers. See the [root README](../../README.md#-quick-start-docker) for the full command set and Stripe webhook setup.

**Native (no Docker)** — requires PostgreSQL 16, Redis, and PHP 8.4 with the `bcmath`, `pdo_pgsql`, and `redis` extensions installed locally:
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan admin:promote you@example.com   # grants yourself admin access
php artisan reverb:start --debug            # separate terminal
php artisan serve                            # http://localhost:8000
```

## Testing

```bash
php artisan test --testsuite=Feature
```
One PHPUnit feature test per endpoint (happy path + at least one failure case) — this is a hard requirement before any endpoint is considered done. Cart/coupon/checkout/dashboard/admin suites need a real local Redis.

## API Contract

Every controller response follows one envelope via `app/Http/Responses/ApiResponse.php`:
```json
{ "success": true, "message": "...", "data": {}, "errors": null }
```
Form Requests' `failedValidation()` is overridden globally to emit this shape automatically. Full request/response examples live in `postman/ShopWave.postman_collection.json`.

## Structure

```text
app/
├── Actions/     # Single-responsibility business logic (incl. Actions/Admin/*)
├── Console/Commands/   # admin:promote and other ops tooling
├── Http/Controllers/   # Slim REST controllers
├── Http/Requests/ # Form Requests — the actual backend validation boundary
├── Http/Responses/ # ApiResponse — unified envelope
├── Models/ # Eloquent models & observers
├── Notifications/ # Database + broadcast notification classes
├── Policies/ # Ownership & authorization boundaries
└── Services/ # Stripe & Redis service adapters
```

## Stripe Webhooks (local)

Two separate listeners — Connect (thin events) and payments (classic) are different event streams:
```bash
stripe listen --thin-events 'v2.core.account[requirements].updated' \
  --forward-thin-to http://localhost:8000/api/v1/webhooks/stripe

stripe listen --forward-to http://localhost:8000/api/v1/webhooks/stripe-payments
```
Put the signing secrets each command prints into `STRIPE_WEBHOOK_SECRET` / `STRIPE_PAYMENT_WEBHOOK_SECRET`.

## Worth knowing

- `config('auth.defaults.guard')` must be `sanctum`, not Laravel's default `web` — this API authenticates purely via Sanctum, and `spatie/laravel-permission`'s role checks are guard-aware.
- The php-fpm pool sets `clear_env = no` explicitly — the default `clear_env = yes` silently strips DB/Redis/Stripe env vars from every fpm worker in Docker.
- All money math written to the database uses `bcmath`, never native float arithmetic.
- Route changes: run `php artisan route:list --path=vendor` and `--path=admin` to confirm middleware applies to every row.

See the [root README](../../README.md) for the full architecture and feature list.
