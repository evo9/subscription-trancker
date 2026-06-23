# Frontend Code Style (apps/web — Next.js 16 / React 19 / TS)

## TypeScript

- **Кавычки: одинарные по умолчанию** (`'...'`); двойные — только чтобы избежать экранирования. JSX-атрибуты — тоже одинарные. Бэктики — для интерполяции. Enforced через ESLint (правила `quotes` + `jsx-quotes`).

- `strict` mode on. No `any` — model API responses in `src/types/api.ts`.
- Prefer type inference; export shared types from `src/types/`.
- Functional components only; hooks for state/effects.

## React / Next

- App Router (`src/app`). Client components only where interactivity is needed
  (`"use client"`); keep them small.
- Co-locate component-specific logic; shared UI in `src/components/ui`.
- Server state via **TanStack Query** — never duplicate it in local state.
- Forms via **React Hook Form + Zod**; the Zod schema is the single source of
  validation truth and feeds inferred types.

## Styling

- **TailwindCSS** utility classes. Extract repeated patterns into components, not
  `@apply` soup.

## Quality tools

- ESLint + Prettier. Keep `next build` and lint clean before done.

## Conventions

- No secrets in client code. API base URL via `NEXT_PUBLIC_*` env.
- Components: `PascalCase`; hooks: `useCamelCase`; files match the default export.
