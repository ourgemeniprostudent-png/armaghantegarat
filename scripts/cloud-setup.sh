#!/usr/bin/env bash
set -euo pipefail
TASK_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
if ! command -v python3 >/dev/null || ! command -v curl >/dev/null || ! command -v php >/dev/null || ! php -r 'exit(extension_loaded("pdo_sqlite")?0:1);'; then
 if [[ "$(uname -s)" != Linux ]] || ! command -v apt-get >/dev/null; then echo 'Install PHP 8.1+ with PDO SQLite, XML, mbstring and curl, then run scripts/setup.sh.' >&2;exit 1;fi
 TASK_PRIVILEGE=()
 [[ "$(id -u)" == 0 ]] || TASK_PRIVILEGE=(sudo)
 "${TASK_PRIVILEGE[@]}" apt-get update
 "${TASK_PRIVILEGE[@]}" apt-get install -y php-cli php-sqlite3 php-xml php-mbstring php-curl curl python3
fi
bash "$TASK_ROOT/scripts/setup.sh"
