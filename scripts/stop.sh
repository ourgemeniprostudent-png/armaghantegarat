#!/usr/bin/env bash
set -euo pipefail
TASK_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
TASK_PID_FILE="$TASK_ROOT/.runtime/server.pid"
[[ -f "$TASK_PID_FILE" ]] || exit 0
TASK_PID="$(cat "$TASK_PID_FILE")"
TASK_PROCESS="$(ps -p "$TASK_PID" -o args= 2>/dev/null || true)"
if [[ "$TASK_PROCESS" == *"$TASK_ROOT/scripts/router.php"* ]]; then
 pkill -TERM -P "$TASK_PID" 2>/dev/null || true
 kill "$TASK_PID" 2>/dev/null || true
 rm "$TASK_PID_FILE"
else echo 'Saved PID no longer belongs to this workspace; no process was stopped.'; fi
