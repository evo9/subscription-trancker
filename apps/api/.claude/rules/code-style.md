# PHP & Eloquent Code Style (apps/api)

Aligned with the spec: PHP 8.3+, Laravel 13, Pint (PSR-12), Larastan/PHPStan **level 8**.

## Strict PHP

- Every PHP file declares `declare(strict_types=1)`.
- Full type hints on all parameters and return types.
- Strict comparisons (`===` / `!==`).
- Use modern PHP 8.3 features (enums with methods, readonly props, first-class callable syntax).
- Trailing commas in multiline arrays and parameter lists.

## Class organization

Order: 1) constants, 2) properties, 3) methods.

## Eloquent conventions

- Prefer `Model::query()->...` over static `Model::where(...)`.
- Prefer relationships, scopes, pagination, soft deletes over raw `DB::` queries.
- Eager-load to prevent N+1: `with()`, `withCount()`, `withTrashed()`.
- Domain logic lives in **enums** (`BillingCycle::perYear/advance`), **accessors**
  (`monthlyCost`/`yearlyCost`), **scopes** (`active`, `dueWithin`, `forUser`) and
  **Action** classes — not in controllers.

## Code quality tools (spec §2, §13)

| Tool | Purpose |
|------|---------|
| Laravel Pint | Formatting (PSR-12). `pint --test` in CI, `pint` to fix. |
| Larastan / PHPStan **level 8** | Static analysis. Must pass clean. |

> Rector / Octane / FrankenPHP from the original toolkit are intentionally **not** used
> (out of scope per spec). Stick to php-fpm + nginx and the tools above.
