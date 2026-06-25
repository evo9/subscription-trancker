# Subscription Tracker

[![CI](https://github.com/evo9/subscription-trancker/actions/workflows/ci.yml/badge.svg)](https://github.com/evo9/subscription-trancker/actions/workflows/ci.yml)
![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat&logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat&logo=laravel&logoColor=white)
![Next.js](https://img.shields.io/badge/Next.js-16-000000?style=flat&logo=nextdotjs&logoColor=white)
![TypeScript](https://img.shields.io/badge/TypeScript-5-3178C6?style=flat&logo=typescript&logoColor=white)
![React](https://img.shields.io/badge/React-19-61DAFB?style=flat&logo=react&logoColor=black)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?style=flat&logo=postgresql&logoColor=white)
![Redis](https://img.shields.io/badge/Redis-7-DC382D?style=flat&logo=redis&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=flat&logo=docker&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind-CSS-06B6D4?style=flat&logo=tailwindcss&logoColor=white)
![Pest](https://img.shields.io/badge/Pest-3-brightgreen?style=flat)

A portfolio project demonstrating idiomatic Laravel 13 on a realistic, self-contained domain. The goal is not full-stack breadth but depth: every framework feature used here grows naturally from the problem rather than being bolted on. The centrepiece is the **Scheduler → Queued Job → Notification** pipeline — the exact async pattern interviewers ask about, made concrete by a genuine business need ("remind the user before a subscription renews").

---

## Quickstart

**Prerequisite:** Docker and Docker Compose only — PHP, Node, Postgres, and Redis all run in containers.

```bash
cp .env.example .env
make install
```

`make install` builds images, installs Composer and npm dependencies, generates the app key, runs migrations, and seeds demo data.

| Service | URL |
|---------|-----|
| Web (Next.js) | http://localhost |
| API (via nginx) | http://localhost/api |
| pgAdmin | http://localhost:5050 |

Demo credentials: `demo@example.com` / `password`

---

## Laravel Features

The table below maps each showcased feature to its location in the codebase.

| Feature | Location |
|---------|----------|
| Eloquent relationships | [`apps/api/app/Models/`](apps/api/app/Models/) — `User→Subscription/Category`, `Subscription→Payment` |
| Eloquent casts | [`apps/api/app/Models/Subscription.php`](apps/api/app/Models/Subscription.php) — `billing_cycle`/`status` → Enum, `price` → decimal, dates → `date` |
| Accessors (computed fields) | [`apps/api/app/Models/Subscription.php`](apps/api/app/Models/Subscription.php) — `monthlyCost`, `yearlyCost` |
| Query scopes | [`apps/api/app/Models/Subscription.php`](apps/api/app/Models/Subscription.php) — `active()`, `dueWithin($days)`, `forUser($user)`, `dueForReminder()` |
| Soft deletes | `subscriptions.deleted_at` — transparent to all queries via `SoftDeletes` |
| Factories + Seeders | [`apps/api/database/factories/`](apps/api/database/factories/), [`apps/api/database/seeders/`](apps/api/database/seeders/) |
| Scheduler | [`apps/api/routes/console.php`](apps/api/routes/console.php) — two daily commands |
| Artisan commands | [`apps/api/app/Console/Commands/`](apps/api/app/Console/Commands/) — `app:process-renewals`, `app:send-renewal-reminders` |
| Queued Jobs | [`apps/api/app/Jobs/SendRenewalReminderJob.php`](apps/api/app/Jobs/SendRenewalReminderJob.php) — dispatched to Redis |
| Notifications | [`apps/api/app/Notifications/RenewalReminder.php`](apps/api/app/Notifications/RenewalReminder.php) — `mail` + `database` channels |
| Events / Observers | [`apps/api/app/Events/`](apps/api/app/Events/), [`apps/api/app/Observers/`](apps/api/app/Observers/) |
| Sanctum token auth | Bearer token for the Next.js SPA — register, login, logout |
| Policies | [`apps/api/app/Policies/`](apps/api/app/Policies/) — `SubscriptionPolicy`, `CategoryPolicy` (owner-only) |
| Form Requests | [`apps/api/app/Http/Requests/`](apps/api/app/Http/Requests/) — validation on every mutating endpoint |
| API Resources | [`apps/api/app/Http/Resources/`](apps/api/app/Http/Resources/) — typed response shaping |
| Tests (Pest 3) | [`apps/api/tests/`](apps/api/tests/) — Feature + Unit, `Queue::fake()`, `Notification::fake()` |

---

## Architecture

### Action-class pattern

Controllers are intentionally thin. Every operation — including non-trivial reads — lives in a dedicated Action class:

```
Route
  └── Controller (Form Request + Policy check)
        └── Action (all domain logic)
              └── API Resource (response shaping)
```

Actions live in `apps/api/app/Actions/{Domain}/` and follow a simple `handle()` contract. There is no separate Service or Repository layer — Eloquent models are used directly inside Actions.

A module layout (`app/Modules/Subscriptions/...`) was considered and rejected: the project is compact enough that modules would add navigation overhead without meaningful boundary enforcement. The Action namespace itself provides the separation that matters.

### Async pipeline

The Scheduler → Queue → Notification chain is the core showcase:

```
Scheduler (daily)
  ├── app:process-renewals
  │     └── ProcessDueRenewals Action
  │           ├── Creates Payment + advances next_billing_date (BillingCycle::advance)
  │           ├── Fires SubscriptionRenewed event
  │           └── Idempotent — re-running the same day creates no duplicate Payment
  │
  └── app:send-renewal-reminders
        └── SendDueReminders Action
              └── Dispatches SendRenewalReminderJob → Redis queue
                    └── Job sends RenewalReminder notification
                          ├── mail channel  (logged in dev, real SMTP in prod)
                          └── database channel (readable via /api/notifications)
```

The `queue` and `scheduler` containers are separate Docker services that reuse the `api` image with a different command — no extra image to build or maintain.

### Domain value objects

`BillingCycle` and `SubscriptionStatus` are backed PHP enums with methods (`advance()`, `perYear()`) that encapsulate date arithmetic and cost normalization. This keeps model accessors and Action logic free of `switch` statements.

---

## Common Commands

```bash
make up            # start all containers
make down          # stop all containers

make migrate       # php artisan migrate --seed
make fresh         # php artisan migrate:fresh --seed

make test          # Pest test suite
make stan          # PHPStan level 8
make pint          # fix style with Laravel Pint

make api-sh        # shell into the api container
make web-sh        # shell into the web container
```

---

## Project Layout

```
apps/api/                 Laravel 13 REST API (php-fpm behind nginx)
  app/Actions/            one class per operation
  app/Console/Commands/   app:process-renewals, app:send-renewal-reminders
  app/Events/             SubscriptionRenewed, …
  app/Http/
    Controllers/          thin — validate, authorize, call Action, return Resource
    Requests/             Form Request validation
    Resources/            API response shaping
  app/Jobs/               SendRenewalReminderJob
  app/Models/             User, Subscription, Category, Payment
  app/Notifications/      RenewalReminder (mail + database)
  app/Observers/          model observers
  app/Policies/           SubscriptionPolicy, CategoryPolicy
  database/
    migrations/
    factories/
    seeders/
  routes/
    api.php               REST routes
    console.php           scheduler definitions
  tests/
    Feature/              HTTP, queue, notification, command tests
    Unit/                 BillingCycle, cost normalization

apps/web/                 Next.js 16 SPA (App Router, React 19, TypeScript)
  src/app/(auth)/         login, register
  src/app/(app)/          dashboard, subscriptions, notifications
  src/components/         charts (Recharts), subscription forms, UI primitives
  src/lib/api.ts          axios instance + Sanctum bearer interceptor
  src/lib/queries.ts      all server state via TanStack Query
  src/types/api.ts        API response types

docker/                   Dockerfiles + nginx config
docs/                     specs/ (source of truth) + tasks/ (work breakdown)
```

---

## Out of Scope

These are deliberate omissions, not gaps. The project is sized to demonstrate patterns clearly without fake complexity.

- **Payment processing** — `Payment` is an accounting record, not a transaction; no Stripe or equivalent
- **Multi-currency conversion** — `currency` is stored as a label; all calculations assume a single currency
- **Roles, teams, sharing** — single-user; Policies demonstrate authorization without needing multi-tenancy
- **SSR / ISR on the frontend** — the Next.js client is a thin API consumer; no server components beyond routing
- **Expense splitting** — out of domain

---

## Screenshots

<!-- TODO: add dashboard, subscriptions, notifications screenshots -->
