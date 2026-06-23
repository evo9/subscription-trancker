# Git & PR Rules

## Commit Rules

> Enforced by a PreToolUse hook (`.claude/hooks/block-git.sh`): any `git add/commit/push/merge/rebase/reset/revert/restore/tag/cherry-pick/stash/init` from the assistant or any subagent is hard-blocked. The human runs git manually. Read-only git (status/diff/log/show/branch) is allowed.

- **NEVER create commits automatically** — only commit when explicitly requested by the user
- **NEVER push to remote** without explicit user request
- **NEVER force push** or run destructive git commands without explicit approval
- When changes are ready, inform the user and wait for their instruction
- Always show `git diff` or `git status` to let the user review before committing

## Pull Request Descriptions

- **NEVER mention AI tools** (Claude, Copilot, Gemini, etc.) in PR title or body
- **NEVER include change statistics** (file count, lines added/removed)
- **NEVER add test plan checklists** — there is no QA team to execute them
- Keep PR descriptions focused on **what** changed and **why**
