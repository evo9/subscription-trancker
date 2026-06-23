# Workflow (common)

Stack-agnostic working rules for the whole monorepo. Stack-specific rules live in
`apps/api/.claude/rules/` and `apps/web/.claude/rules/`.

## General

- Enter **plan mode** for any non-trivial task (3+ steps or architectural decisions).
- If something goes sideways, STOP and re-plan — don't keep pushing.
- Write detailed specs/plans upfront to reduce ambiguity. The source of truth is
  `docs/specs/subscription-tracker-spec.md`; the task breakdown is in `docs/tasks/`.
- Never mark a task complete without proving it works (tests, logs, demonstrated behavior).

## Subagents

- Use subagents liberally to keep the main context clean.
- Offload research, exploration, and parallel analysis. One focused task per subagent.
- Run independent steps in parallel where possible.

## Self-improvement loop

- After any correction from the user: capture the pattern in `docs/lessons.md`.
- Write a rule for yourself that prevents the same mistake; review lessons at session start.

## Verification before done

- Diff behavior between baseline and your change when relevant.
- Ask: "Would a staff engineer approve this?" Run tests, check logs, demonstrate correctness.

## Core principles

- **Simplicity first** — make every change as simple as possible; touch minimal code.
- **No laziness** — find root causes, no temporary fixes.
- **Minimal impact** — change only what's necessary.

## Suggested feature pipeline

For non-trivial features, dispatch agents roughly in this order (parallelize where independent):

1. **ba** — clarify requirements, acceptance criteria, scope.
2. **ddd-architect** (api) — domain modeling / logic placement, if architectural.
3. **developer** (api) / frontend work (web) — implement.
4. **tester** (api) / **qa** (web E2E) — tests. Pragmatic TDD: lead with a failing test for pure logic, prove the async pipeline with Laravel fakes (see the `test-driven-development` skill). Run in parallel with review.
5. **reviewer** — code review against rules; loop back on Critical/Important findings.
6. **security-scanner** — auth/authz, secrets, OWASP.
7. **docs-writer** — summary / docs / PR description (see `git-operations.md`).

For CI/Docker/deploy work use **devops** / **ci-cd-engineer**.
Skip the pipeline for trivial changes (typo, config value).
