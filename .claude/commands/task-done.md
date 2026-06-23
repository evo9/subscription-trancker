---
description: Run the reviewer on recently changed files and fix issues before marking a task complete
---

You have just finished implementing a task. Before reporting it as done:

## Step 1: Identify changed files

```bash
git diff --name-only
git ls-files --others --exclude-standard
```

## Step 2: Review

Dispatch the `reviewer` agent (subagent_type: "reviewer") scoped to the changed files.
Say: "Review the following files against the project rules: [list changed files]".

For changes that touch domain contracts (enums, routes, migrations, the renewal/reminder
flow), also dispatch `spec-guardian` to check alignment with
`docs/specs/subscription-tracker-spec.md`.

## Step 3: Act on findings

- **Critical** — fix immediately, then re-review.
- **Important** — fix immediately, then re-review.
- **Minor / Suggestion** — fix if trivial, otherwise note it and leave for later.

Loop Steps 2–3 until the reviewer is clean of Critical/Important findings.

## Step 4: Confirm completion

Only after the reviewer returns clean (or all Critical/Important resolved):
- Mark the task complete.
- Give a short summary: what was implemented and what review found.
- Do **not** commit or push unless the user explicitly asks (`git-operations.md`).
