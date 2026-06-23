---
name: test-driven-development
description: >
  Use when writing tests or implementing a feature/bugfix. Pragmatic stance for this
  project: test-first for pure logic (BillingCycle advance/perYear, monthlyCost/yearlyCost
  normalization, renewal idempotency, Action branching, Policy rules, Form Request
  validation); test-after but always-proven for the async/infra parts (queue dispatch,
  notifications, scheduler) using Laravel fakes and feature tests on a real database.
  This is NOT a strict test-first mandate — match the technique to the code.
---

# Test-Driven Development (pragmatic)

## Stance for this project

Tests are non-negotiable; *test-first* is a tool, not a law. Match the technique to the code:

- **Pure logic → test-first.** `BillingCycle::advance()` / `perYear()`, `monthlyCost` /
  `yearlyCost` normalization, renewal idempotency decision, Action branching, scope
  results, Policy allow/deny, Form Request rules. Write the failing test first — it
  clarifies the invariant and proves the test actually catches the bug.
- **Async / infra → proven, not declared.** Queued reminder dispatch, `RenewalReminder`
  channels, the scheduler commands. A short spike to learn the behavior is fine; then lock
  it with a test: `Queue::fake()`, `Notification::fake()`, `Event::fake()`, and feature
  tests on a real (refreshed) database. Don't claim the async pipeline works until a test
  demonstrates it.

This fits the pipeline in `workflow.md`: the `tester` step may run after `implement`, but
pure-logic work should still lead with a failing test where practical.

## The loop (for logic): RED → GREEN → REFACTOR

### RED — write a failing test
One behaviour, clear name, real code over mocks.

```php
it('rejects advancing without overflow at month end', function (): void {
    $next = BillingCycle::Monthly->advance(Carbon::parse('2026-01-31'));
    expect($next->toDateString())->toBe('2026-02-28');
});
```

Bad (vague name, asserts a mock instead of behaviour):

```php
it('works', function (): void {
    $repo = Mockery::mock(/* ... */);
    $action->handle($repo);
    expect(true)->toBeTrue();
});
```

### Verify RED — watch it fail
```bash
docker compose exec api php artisan test --filter='advancing without overflow'
```
Confirm it fails for the right reason (feature missing, not a typo). A test that passes
immediately tests nothing new.

### GREEN — minimal code
Write the simplest code that passes. No speculative options (YAGNI).

### Verify GREEN — watch it pass
Re-run; the new test and the existing suite are green; output is clean.

### REFACTOR
Improve names, remove duplication, extract helpers. Stay green. Don't add behaviour.

## Good tests

| Quality | Good | Bad |
|---------|------|-----|
| **Minimal** | One behaviour; "and" in the name → split it | `it('validates price and status and cycle')` |
| **Clear** | Name states the behaviour | `it('works')` |
| **Real** | Exercises real code/invariant | Asserts on a mock |

## Real behaviour over mocks

Prefer exercising real code. For the queue/notification/scheduler, use Laravel's fakes
(`Queue::fake`, `Notification::fake`, `Event::fake`) and a real database
(`RefreshDatabase`) — not hand-rolled mocks of Eloquent or the queue. Mocking those proves
the mock, not the behaviour. See `testing-anti-patterns.md`.

## Verification checklist (before marking work done)

- [ ] Pure logic has unit tests; you watched the key ones fail first
- [ ] The async pipeline touched has a test: idempotent renewal (no duplicate Payment),
      reminder dispatched to the queue, `RenewalReminder` on `mail` + `database`
- [ ] Tests assert real behaviour/invariants, not mocks or private internals
- [ ] Failure / edge paths covered (paused/cancelled not renewed, wrong owner → 403),
      not just the happy path
- [ ] All tests pass; output clean
- [ ] Ran: `docker compose exec api php artisan test`

## When stuck

| Problem | Solution |
|---------|----------|
| Hard to test | Hard to use — simplify the interface, extract an Action |
| Must mock everything | Too coupled — inject dependencies, use fakes |
| Don't know how to assert | Write the wished-for API / assertion first |

Keep tests deterministic — control time with `travelTo()` / `Carbon::setTestNow()`, never
arbitrary sleeps.
