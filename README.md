# Subscription Tracker

Personal subscription tracker — a portfolio project demonstrating idiomatic Laravel 13
on a realistic domain. The backend is the focus: Action-class architecture, Sanctum token
auth, owner-only Policies, and a Redis-backed queue pipeline for async renewal processing.

## Stack

| Layer | Technology |
|-------|------------|
| API | PHP 8.3, Laravel 13, Sanctum, PostgreSQL 16, Redis |
| Web | Next.js 16 (App Router), React 19, TypeScript, TanStack Query |
| Testing | Pest 3, Larastan/PHPStan level 8, Laravel Pint |
| Infra | Docker Compose, nginx, GitHub Actions |

## Project Layout

```
apps/api/   Laravel 13 REST API (php-fpm behind nginx)
apps/web/   Next.js 16 SPA client
docker/     Dockerfiles + nginx config
docs/       specs/ and tasks/
```

## Architecture

The API follows a thin-controller pattern:

```
Route → Controller (Form Request + Policy) → Action → API Resource
```

Domain logic lives in Action classes under `app/Actions/`, typed PHP 8.3 Enums
(`BillingCycle`, `SubscriptionStatus`), Eloquent accessors, and query scopes — not in
controllers or a separate service layer.

The async showcase: a daily scheduler command dispatches jobs to a Redis queue, which
processes renewals and sends `RenewalReminder` notifications via the `mail` (logged in
dev) and `database` channels. The `queue` and `scheduler` containers reuse the `api`
image with a different command.

## Prerequisites

- Docker and Docker Compose

Everything else (PHP, Node, Postgres, Redis) runs inside containers.

## Setup

```bash
cp .env.example .env
make install        # build images, start containers, install deps, migrate + seed
```

Or step by step:

```bash
cp .env.example .env
docker compose up -d
docker compose exec api composer install
docker compose exec api php artisan key:generate
docker compose exec api php artisan migrate --seed
```

| Service | URL |
|---------|-----|
| API (via nginx) | http://localhost/api |
| Web | http://localhost |
| pgAdmin | http://localhost:5050 |

## Common Commands

```bash
# Start / stop
make up
make down

# Database
make migrate          # migrate --seed
make fresh            # migrate:fresh --seed

# Testing
make test             # Pest
make stan             # Larastan / PHPStan level 8

# Code style
make pint             # fix with Laravel Pint
docker compose exec api ./vendor/bin/pint --test   # check only (CI)

# Shells
make api-sh
make web-sh
```

## API Endpoints

All routes are prefixed `/api`. Everything except `register` and `login` requires a
Sanctum bearer token.

```
POST   /api/register
POST   /api/login
POST   /api/logout

GET    /api/subscriptions
POST   /api/subscriptions
GET    /api/subscriptions/{id}
PUT    /api/subscriptions/{id}
DELETE /api/subscriptions/{id}

POST   /api/subscriptions/{id}/pause
POST   /api/subscriptions/{id}/resume

GET    /api/subscriptions/{id}/payments
```

`SubscriptionPolicy` enforces owner-only access on every subscription route.

## Features

- Sanctum token authentication (register, login, logout)
- Subscription CRUD with `SubscriptionPolicy` (owner-only)
- Subscription lifecycle: `active → paused → active → cancelled`
- `BillingCycle` enum (`weekly`, `monthly`, `quarterly`, `yearly`) with
  `advance()` (date arithmetic) and `perYear()` (normalization) methods
- `SubscriptionStatus` enum enforced at the model and Form Request layers
- Payment history per subscription
- Soft deletes on subscriptions
- API Resources for all responses

## Development Notes

- `declare(strict_types=1)` in every PHP file; full type hints throughout
- PHPStan level 8 must pass clean before merging
- Pint (PSR-12 preset) is enforced in CI via `pint --test`
- Tests use a separate Postgres schema with `RefreshDatabase`
- Out of scope: real payment integration, multi-currency conversion, sharing/roles
