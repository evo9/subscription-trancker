#!/usr/bin/env bash
# Hard block: the assistant/agents must never mutate git. The human owns all git
# operations in this repo (see .claude/rules/git-operations.md). Fails closed.

payload="$(cat)"
if command -v jq >/dev/null 2>&1; then
  cmd="$(printf '%s' "$payload" | jq -r '.tool_input.command // empty' 2>/dev/null)"
else
  cmd="$payload"
fi

if printf '%s\n' "$cmd" | grep -qiE '(^|[;&|(`]|[[:space:]])git[[:space:]]+(add|commit|push|merge|rebase|reset|revert|restore|tag|cherry-pick|stash|am|apply|rm|mv|init)([[:space:]]|$)'; then
  echo "BLOCKED: git write commands are forbidden for the assistant and all subagents in this repo. The human performs git manually (see .claude/rules/git-operations.md). Read-only git (status/diff/log/show/branch) is fine." >&2
  exit 2
fi
exit 0
