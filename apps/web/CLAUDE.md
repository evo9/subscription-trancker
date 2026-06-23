# apps/web — Next.js 16 client

Thin SPA client for the Laravel API. Spec: `../../docs/specs/subscription-tracker-spec.md`
(§2, §10).

Stack rules (auto-imported):

@.claude/rules/code-style.md
@.claude/rules/architecture.md

## Requirements

- Node 22+, Next.js 16 (App Router, React 19, Turbopack), TypeScript
- TailwindCSS, TanStack Query, React Hook Form + Zod, Recharts, Axios

## Setup

```bash
docker compose up -d web        # or: cd apps/web && pnpm install && pnpm dev
```

Set `NEXT_PUBLIC_API_URL` to the API base (via root compose env).

## Common commands

```bash
pnpm dev      # next dev (Turbopack)
pnpm build    # production build — keep clean before done
pnpm lint     # ESLint
```

## Architecture (see .claude/rules/architecture.md)

- App Router under `src/app` with `(auth)` (public) and `(app)` (guarded) route groups.
- `lib/api.ts` — axios instance + token interceptor (Sanctum bearer).
- `lib/queries.ts` — all server state via TanStack Query; invalidate after mutations.
- Forms with RHF + Zod; types in `types/api.ts`.
- Screens: dashboard (Recharts donut + totals + upcoming), subscriptions list/detail/form,
  notifications. No SSR/ISR depth (spec §15).

## Relevant agents / skills (in root .claude)

Agent: `qa` (Playwright E2E). Skills: `playwright-expert`, `playwright-skill`.

> The original toolkit's Vue 3 + Inertia.js frontend agent/skills do not apply here and
> were dropped — this is a standalone Next.js client over HTTP.
