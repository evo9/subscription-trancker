# apps/api — Laravel 13 API

REST API for Subscription Tracker. Spec: `../../docs/specs/subscription-tracker-spec.md`
(§2, §4–§9, §12).

Stack rules (auto-imported):

@.claude/rules/code-style.md
@.claude/rules/architecture.md
@.claude/rules/testing.md

## Requirements

- PHP **8.3+**, Laravel 13, Composer
- PostgreSQL 16, Redis
- Pest 3, Laravel Pint, Larastan/PHPStan **level 8**
- Docker Compose (run everything in containers)

## Service names (docker-compose, spec §11)

`api` (php-fpm), `nginx`, `postgres`, `redis`, `queue` (`queue:work`),
`scheduler` (`schedule:work`). `queue`/`scheduler` reuse the `api` image with a different
command. (The inherited toolkit used `app`; this project uses `api`.)

## Setup

```bash
cp .env.example .env                       # at repo root (shared compose env)
docker compose up -d
docker compose exec api composer install
docker compose exec api php artisan key:generate
docker compose exec api php artisan migrate --seed
```

## Common commands

```bash
docker compose exec api php artisan test                 # Pest
docker compose exec api ./vendor/bin/pint                # fix style
docker compose exec api ./vendor/bin/pint --test         # check style (CI)
docker compose exec api ./vendor/bin/phpstan analyse     # Larastan level 8
```

## Architecture (see .claude/rules/architecture.md)

- Flow: Controller (Form Request + Policy) → **Action** → **API Resource**.
- Domain logic in enums / accessors / scopes / Actions, not controllers.
- **Async showcase** (spec §7): daily commands → Redis-queued Job → `RenewalReminder`
  notification (`mail`=log in dev + `database`). `process-renewals` is idempotent.
- Sanctum token auth; Policies for owner-only access; soft deletes on subscriptions.

## Relevant agents / skills (in root .claude)

Agents: `developer`, `tester`, `dba`, `ddd-architect`, `queue-specialist`,
`laravel-refactoring-expert`. Skills: `laravel-specialist`, `php-pro`, `pest-testing`,
`postgres-best-practices`, `database-optimizer`, `ddd-strategic-design`.

> Not Octane/FrankenPHP, not Rector, not Inertia/Filament — out of scope per spec.
