# Subscription Tracker — Monorepo

Pet/portfolio project: personal subscription tracker. Goal — showcase **idiomatic
Laravel** on a compact but realistic domain. Full spec:
[`docs/specs/subscription-tracker-spec.md`](docs/specs/subscription-tracker-spec.md).
Implementation broken into tasks under [`docs/tasks/`](docs/tasks/).

## Layout

```
apps/api/   Laravel 13 REST API (PHP 8.3, Sanctum, Postgres, Redis queues)
apps/web/   Next.js 16 client (React 19, TS, TanStack Query)
docker/     Dockerfiles + nginx config
docs/       specs/ (source of truth) + tasks/ (work breakdown)
```

Two independent builds under one repo, connected only over HTTP.

## Tech stack (see spec §2)

- **API**: PHP 8.3+, Laravel 13, Sanctum, PostgreSQL 16, Redis, Pest 3, Pint,
  Larastan/PHPStan level 8. php-fpm behind nginx.
- **Web**: Next.js 16 (App Router, React 19), TypeScript, TailwindCSS, TanStack Query,
  React Hook Form + Zod, Recharts.
- **Infra**: Docker Compose (api, nginx, web, postgres, redis, queue, scheduler), Make,
  GitHub Actions.

## Architecture decisions (spec §9, §15)

- Backend uses the **Action-class** pattern (thin controllers) — not modules, not a
  service/repository layer.
- Frontend is a **thin Next.js client** — no SSR/ISR depth.
- Out of scope: real payment integration, multi-currency conversion, expense-splitting,
  roles/sharing (single-user).

## Claude tooling layout

All runtime config lives at the **repo root** (`claude` is launched from here):
- `.claude/agents/`, `.claude/skills/` — every agent/skill (cross-stack + Laravel + web).
- `.claude/settings.json` — model, `.env` denials, Pint Stop-hook (guarded; no-ops until
  the `api` container is up).
- `.mcp.json` — `github`, `context7`, `laravel-boost` (root-only; subdir `.mcp.json` is
  not supported by Claude Code).

Stack-specific **rules** are split per app and pulled in via the `@`-imports below
(Claude Code does not auto-load subdir `settings`/`.mcp.json`, and nested `CLAUDE.md`
auto-load is unreliable — explicit imports guarantee loading):

@.claude/rules/git-operations.md
@.claude/rules/workflow.md
@.claude/rules/code-comments.md
@apps/api/CLAUDE.md
@apps/web/CLAUDE.md

## Stack note vs. inherited toolkit

The `tmp/` toolkit was authored for a different stack (Laravel 12 + Inertia.js + Vue 3 +
Octane/FrankenPHP + Filament + Rector + PHP 8.4). It was curated: Inertia/Vue/Filament
agents and duplicate skills were dropped, and rules rewritten to match this spec. `tmp/`
is left intact as a staging area for files you add later.
