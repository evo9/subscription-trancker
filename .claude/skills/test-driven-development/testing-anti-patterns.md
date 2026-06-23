# Testing Anti-Patterns

**Load this reference when:** writing or changing tests, adding mocks, or tempted to add
test-only methods to production code.

## Overview

Tests must verify real behavior, not mock behavior. Mocks are a means to isolate, not the
thing being tested.

**Core principle:** Test what the code does, not what the mocks do.

## The Iron Laws

```
1. NEVER test mock behavior
2. NEVER add test-only methods to production classes
3. NEVER mock without understanding dependencies
```

## Anti-Pattern 1: Testing Mock/Fake Plumbing

Asserting that a fake was set up, instead of the behaviour it stands in for.

```php
// ❌ BAD: proves the fake exists, not that a reminder is sent
Queue::fake();
$action->handle();
expect(true)->toBeTrue();

// ✅ GOOD: assert the real outcome
Queue::fake();
$action->handle();
Queue::assertPushed(SendRenewalReminderJob::class, fn ($job) => $job->subscription->is($sub));
```

Before asserting on a fake: "Am I testing real behaviour or just that I called `fake()`?"
If the latter — assert the dispatched job / sent notification / fired event instead.

## Anti-Pattern 2: Test-Only Methods in Production

Don't add methods to models/Actions that exist only for tests (reset helpers, back-doors).
Put setup/cleanup in factories, `beforeEach`, or test helpers — never on the production class.

## Anti-Pattern 3: Mocking Without Understanding

Over-mocking to "be safe" removes the side effect the test depends on.

```php
// ❌ BAD: mocking the model hides the DB write the idempotency check relies on
// ✅ GOOD: use a real (refreshed) database; only fake the boundary (Queue/Mail/Notification)
```

If unsure what a test depends on, run it against the real implementation first, observe
what must happen, then fake only the external boundary.

## Anti-Pattern 4: Incomplete Fixtures

Build entities through factories so every column/relationship the code reads is present.
Partial hand-built arrays fail silently when downstream code touches a field you omitted.

## Anti-Pattern 5: Tests as an Afterthought

"Implementation complete, tests later" is not complete. For pure logic, lead with a failing
test; for the async pipeline, a feature test proving it is part of the deliverable (spec §12).

## Quick Reference

| Anti-Pattern | Fix |
|--------------|-----|
| Assert on fake plumbing | Assert the dispatched job / sent notification / fired event |
| Test-only methods in production | Move to factories / test helpers |
| Mock without understanding | Use real DB; fake only the boundary (Queue/Mail/Notification) |
| Incomplete fixtures | Build via factories — full, realistic data |
| Tests as afterthought | Test-first for logic; prove the async path |

## The Bottom Line

**Fakes isolate the boundary; they are not the thing under test.** Laravel gives you
`Queue::fake`, `Notification::fake`, `Event::fake`, `Mail::fake` — assert on what they
*recorded*, run everything else against a real database.
