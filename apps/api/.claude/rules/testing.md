# Testing Rules (apps/api — Pest 3)

Goal (spec §12): not 100% coverage, but proof you can test the **async** part
(queues, notifications, scheduler). That's the valuable signal.

## Approach — pragmatic TDD

Test-first for **pure logic**: `BillingCycle::advance/perYear`, `monthlyCost`/`yearlyCost`
normalization, renewal idempotency, Action/Policy branching, Form Request rules. Write the
failing test first, watch it fail for the right reason, then implement.

For the **async/infra** parts (queue dispatch, notifications, scheduler) a short spike is
fine, then lock behavior with a feature test using Laravel fakes (`Queue::fake`,
`Notification::fake`, `Event::fake`) on a real (refreshed) database — proven, not declared.
Not a strict test-first mandate; match the technique to the code. See the
`test-driven-development` skill.

## Models testing policy

Do **not** write unit tests for plain Eloquent behavior (basic relationships, CRUD,
casts, fillable). The framework already covers it. Test models via feature tests instead.

Do test: custom business logic, accessors/scopes with rules, observer side effects,
Action behavior.

## What to cover (spec §12)

- **Feature/API**: subscription CRUD; Policy (can't see/edit another user's data);
  list filters; `stats/summary` computes sums correctly.
- **Commands**: `ProcessRenewalsCommand` creates Payment + advances date; idempotent
  re-run doesn't duplicate. `SendRenewalRemindersCommand` dispatches a job — assert via `Queue::fake()`.
- **Notifications**: `Notification::fake()` — `RenewalReminder` goes to the right user
  on `mail` + `database`.
- **Unit**: `BillingCycle::advance()` for all cycles; `monthlyCost`/`yearlyCost` normalization.

## Tooling

- **Pest 3** (BDD: `describe()` + `it()` + `expect()`), `RefreshDatabase`.
- Separate Postgres instance/schema for tests.
- Mutation testing (Infection) is optional — nice-to-have, not required by spec.

## Style

```php
<?php
declare(strict_types=1);

it('advances monthly cycle by one month', function (): void {
    $next = BillingCycle::Monthly->advance(now());
    expect($next->day)->toBe(now()->day);
});
```
