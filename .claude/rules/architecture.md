# Frontend Architecture (apps/web — Next.js 16)

Thin client over the Laravel API (spec §10). The backend is the star; don't over-build
the frontend. No SSR/ISR depth (spec §15) — an accurate API client is enough.

## Layout (spec §10)

```
src/
├── app/
│   ├── (auth)/login, (auth)/register
│   ├── (app)/dashboard, (app)/subscriptions, (app)/subscriptions/[id]
│   └── layout.tsx
├── components/{subscriptions,charts,ui}
├── lib/api.ts        # axios instance + token interceptor
├── lib/queries.ts    # TanStack Query hooks
└── types/api.ts      # API response types
```

## Data flow

- `lib/api.ts` — one axios instance; interceptor injects the Sanctum bearer token.
- `lib/queries.ts` — all server interaction through TanStack Query hooks; invalidate
  affected queries after mutations (create/update/pause/resume/delete).
- Route groups: `(auth)` public, `(app)` guarded — redirect to `/login` without a token.

## Screens

- **Dashboard**: category donut (Recharts) + monthly/yearly totals + upcoming charges
  (from `stats/summary` + `stats/upcoming`).
- **Subscriptions**: list with filters (status / category / due_within), create/edit
  form, detail page with payment history and pause/resume/delete.
- **Notifications**: list + mark-as-read (`/api/notifications`).

## Testing

- E2E via Playwright (see `qa` agent and the `playwright-*` skills) against running
  `web` + `api` containers.

> Note: the original toolkit's frontend tooling targeted Vue 3 + Inertia.js and was
> intentionally dropped — this app is a standalone Next.js client talking to the API
> over HTTP only.
