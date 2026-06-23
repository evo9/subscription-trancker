# Architecture (apps/api — Laravel 13)

## Business logic — Action classes

- One operation = one Action class under `app/Actions/...` (spec §9).
- Plain Action classes (constructor/`handle()`); the `lorisleiva/laravel-actions`
  package is optional, not required.
- No separate Service or Repository layer — use Eloquent models directly.
- Flow: **Controller** (Form Request validation + Policy check) → **Action** (logic)
  → **API Resource** (response). Controllers stay thin.

## API surface

- REST under `/api` (spec §6). All routes except register/login behind `auth:sanctum`.
- **Sanctum** token auth for the Next.js SPA.
- **Form Requests** for all input validation.
- **API Resources** for all responses (`SubscriptionResource`, etc.).
- **Policies** (`SubscriptionPolicy`, `CategoryPolicy`) — owner-only access.

## Async core (spec §7 — the showcase)

- Two daily Artisan commands in `routes/console.php`: `app:process-renewals`,
  `app:send-renewal-reminders`.
- Reminders are **dispatched to the Redis queue** (Job), never sent synchronously.
- `RenewalReminder` Notification via `mail` (dev: `log`) + `database` channels.
- `process-renewals` must be **idempotent** — re-running the same day must not
  duplicate a Payment for the same period.
- `queue` and `scheduler` run as separate containers reusing the `api` image (spec §11).

## Database

- Every schema change → a new migration. Every data change → update seeder + factory.
- PostgreSQL 16 with proper indexing (`subscriptions(user_id,status)`, `(next_billing_date)`).
- Soft deletes on `subscriptions`.

## Performance / infra

- php-fpm 8.3 behind nginx (not Octane).
- Redis for queue + cache.
- Telescope optional, dev-only (spec §2).
