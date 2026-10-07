#!/usr/bin/env bash
set -euo pipefail
TASK_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$TASK_ROOT"
TASK_PHP="${ARMAGHAN_PHP_BIN:-php}"
TASK_URL="${ARMAGHAN_SITE_URL:-http://127.0.0.1:8788}"
if [[ -z "${ARMAGHAN_SITE_URL:-}" && -f "$TASK_ROOT/.runtime/site-url.txt" ]]; then TASK_URL="$(cat "$TASK_ROOT/.runtime/site-url.txt")"; fi
TASK_URL="${TASK_URL%/}"
TASK_WP="$TASK_ROOT/.runtime/wordpress"
command -v "$TASK_PHP" >/dev/null || { echo 'PHP missing. Run scripts/cloud-setup.sh on Linux.' >&2; exit 1; }
"$TASK_PHP" -r 'if(PHP_VERSION_ID<80100||!extension_loaded("pdo_sqlite")){fwrite(STDERR,"PHP 8.1+ with pdo_sqlite is required.\n");exit(1);}'
mkdir -p .runtime
"$TASK_PHP" -r '$m=json_decode(file_get_contents("vendor/checksums.json"),true);foreach($m as $p=>$sha){if(hash_file("sha256","vendor/".$p)!==$sha){fwrite(STDERR,"Vendor checksum mismatch: ".$p."\n");exit(1);}}'
if [[ -f .runtime/site-url.txt ]] && [[ "$(cat .runtime/site-url.txt)" != "$TASK_URL" ]]; then
 echo 'This workspace was initialized with another URL. Keep its original ARMAGHAN_SITE_URL or migrate the runtime separately; setup never resets existing content.' >&2; exit 1
fi
if [[ ! -f "$TASK_WP/wp-load.php" ]]; then tar -xzf vendor/wordpress-7.1.3.tar.gz -C .runtime; fi
mkdir -p "$TASK_WP/wp-content/"{plugins,themes,database,languages}
cp -R vendor/sqlite-database-integration "$TASK_WP/wp-content/plugins/"
cp vendor/sqlite-db.php "$TASK_WP/wp-content/db.php"
tar -xzf vendor/languages-fa_IR.tar.gz -C "$TASK_WP/wp-content/languages"
for spec in 'themes/vatan-authority:theme/vatan-authority' 'plugins/vatan-core:plugin/vatan-core'; do
 TASK_FROM="${spec%%:*}"; TASK_TO="${spec#*:}"
 if [[ -e "$TASK_WP/wp-content/$TASK_FROM" && ! -L "$TASK_WP/wp-content/$TASK_FROM" ]]; then echo "Expected a development symlink at $TASK_FROM; keeping the existing directory." >&2; exit 1; fi
 ln -sfn "../../../../$TASK_TO" "$TASK_WP/wp-content/$TASK_FROM"
done
export ARMAGHAN_SITE_URL="$TASK_URL"
TASK_NETWORK=initial
[[ -f .runtime/network-ready ]] && TASK_NETWORK=network
"$TASK_PHP" scripts/configure.php "$TASK_NETWORK"
wp(){ "$TASK_PHP" vendor/wp-cli-2.12.0.phar --allow-root --path="$TASK_WP" --url="$TASK_URL" "$@"; }
if [[ ! -f .runtime/network-ready ]]; then
 TASK_PASSWORD="${ARMAGHAN_ADMIN_PASSWORD:-$("$TASK_PHP" -r 'echo bin2hex(random_bytes(20));')}"
 wp core multisite-install --url="$TASK_URL" --title='ارمغان تجارت وطن' --admin_user=armaghan_dev --admin_password="$TASK_PASSWORD" --admin_email=dev@example.invalid --skip-email
 touch .runtime/network-ready
 "$TASK_PHP" scripts/configure.php network
 umask 077
 printf 'Development only\nSite: %s\nAdmin: %s/wp-admin/\nUsername: armaghan_dev\nPassword: %s\n' "$TASK_URL" "$TASK_URL" "$TASK_PASSWORD" > .runtime/access.txt
 unset TASK_PASSWORD
fi
wp plugin activate vatan-core --network
wp theme enable vatan-authority --network
wp theme activate vatan-authority
if [[ ! -f .runtime/public-seed-imported ]]; then
 wp eval-file scripts/import-content.php
 wp site create --slug=en --title='Armaghan Tejarat Vatan' --email=dev@example.invalid --porcelain >/dev/null
 wp --url="$TASK_URL/en/" option update vatan_language_pending 1
 wp --url="$TASK_URL/en/" option update WPLANG en_US
 touch .runtime/public-seed-imported
fi
printf '%s\n' "$TASK_URL" > .runtime/site-url.txt
printf 'Setup complete. Private development credentials: .runtime/access.txt\nStart: bash scripts/start.sh\n'
