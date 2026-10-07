#!/usr/bin/env bash
set -euo pipefail
TASK_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$TASK_ROOT"
TASK_PHP="${ARMAGHAN_PHP_BIN:-php}"
TASK_URL="$(cat .runtime/site-url.txt)"
TASK_PORT="$("$TASK_PHP" -r '$u=parse_url($argv[1]);echo $u["port"]??($u["scheme"]==="https"?443:80);' "$TASK_URL")"
TASK_BIND="${ARMAGHAN_BIND_ADDRESS:-127.0.0.1}"
if [[ -f .runtime/server.pid ]] && kill -0 "$(cat .runtime/server.pid)" 2>/dev/null; then
 echo 'Workspace server is already running.'; exit 0
fi
TASK_ADDRESS="$TASK_BIND:$TASK_PORT"
if curl --max-time 2 -fsS "http://127.0.0.1:$TASK_PORT/" >/dev/null 2>&1; then
 echo "Port $TASK_PORT is already in use. Choose ARMAGHAN_SITE_URL with a free port before first setup." >&2; exit 1
fi
if [[ "${1:-}" == --foreground ]]; then
 export PHP_CLI_SERVER_WORKERS=4
 exec "$TASK_PHP" -S "$TASK_ADDRESS" -t "$TASK_ROOT/.runtime/wordpress" "$TASK_ROOT/scripts/router.php"
fi
PHP_CLI_SERVER_WORKERS=4 nohup "$TASK_PHP" -S "$TASK_ADDRESS" -t "$TASK_ROOT/.runtime/wordpress" "$TASK_ROOT/scripts/router.php" > .runtime/server.log 2>&1 &
printf '%s\n' "$!" > .runtime/server.pid
for TASK_ATTEMPT in {1..40}; do
 if curl --max-time 2 -fsS "http://127.0.0.1:$TASK_PORT/" >/dev/null 2>&1; then printf 'Development server ready: %s\n' "$TASK_URL"; exit 0; fi
 sleep 0.25
done
echo 'Server did not become ready. Check .runtime/server.log.' >&2;exit 1
