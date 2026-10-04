#!/usr/bin/env bash

set -Eeuo pipefail
umask 077

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

CONFIG_FILE="${CONFIG_FILE:-$SCRIPT_DIR/baltic-vending-solutions.env}"
if [ -f "$CONFIG_FILE" ]; then
	# shellcheck disable=SC1090
	. "$CONFIG_FILE"
fi

SSH_KEY="${SSH_KEY:-}"
REMOTE_HOST="${REMOTE_HOST:-}"
REMOTE_WP_PATH="${REMOTE_WP_PATH:-}"
REMOTE_URL="${REMOTE_URL:-}"
REMOTE_BACKUP_DIR="${REMOTE_BACKUP_DIR:-}"
LOCAL_URL="${LOCAL_URL:-}"
LOCAL_WP_PATH="${LOCAL_WP_PATH:-}"
LOCAL_PHP_BIN="${LOCAL_PHP_BIN:-}"
LOCAL_PHP_INI="${LOCAL_PHP_INI:-}"
LOCAL_WP_CLI="${LOCAL_WP_CLI:-}"
LOCAL_MYSQL_BIN_DIR="${LOCAL_MYSQL_BIN_DIR:-}"
LOCAL_MYSQL_SOCKET="${LOCAL_MYSQL_SOCKET:-}"
BACKUP_KEEP="${BACKUP_KEEP:-3}"

DRY_RUN=0
ASSUME_YES=0
TEMP_DIR=""
REMOTE_TEMP=""
REMOTE_BACKUP=""

usage() {
	cat <<'EOF'
Usage: ./scripts/push-db-to-droplet.sh [--dry-run] [--yes]

Replace the droplet WordPress database with the local database. The script:
  1. validates both databases and the local URL;
  2. creates a compressed droplet backup;
  3. exports Local with production-safe URLs;
  4. imports that export on the droplet;
  5. flushes caches and rewrite rules.

This overwrites production pages, menus, settings, users, and plugin data.
It does not copy files from wp-content/uploads.
--dry-run checks connectivity and databases without replacing data.
Without --yes, type PUSH TO PRODUCTION when prompted.

Before the first use, copy scripts/baltic-vending-solutions.env.example to
scripts/baltic-vending-solutions.env and fill in the required values.

Environment overrides:
  CONFIG_FILE, SSH_KEY, REMOTE_HOST, REMOTE_WP_PATH, REMOTE_URL, REMOTE_BACKUP_DIR
  LOCAL_URL, LOCAL_WP_PATH, LOCAL_PHP_BIN, LOCAL_PHP_INI, LOCAL_WP_CLI
  LOCAL_MYSQL_BIN_DIR, LOCAL_MYSQL_SOCKET, BACKUP_KEEP
EOF
}

die() {
	printf 'Error: %s\n' "$*" >&2
	exit 1
}

cleanup() {
	if [ -n "$TEMP_DIR" ] && [ -d "$TEMP_DIR" ]; then
		rm -f -- "$TEMP_DIR/local-for-droplet.sql"
		rmdir "$TEMP_DIR" 2>/dev/null || true
	fi

	if [ -n "$REMOTE_TEMP" ]; then
		ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" "rm -f -- '$REMOTE_TEMP'" >/dev/null 2>&1 || true
	fi
}
trap cleanup EXIT

local_wp() {
	MYSQL_UNIX_PORT="$LOCAL_MYSQL_SOCKET" PATH="$LOCAL_MYSQL_BIN_DIR:$PATH" \
		"$LOCAL_PHP_BIN" -c "$LOCAL_PHP_INI" -d "mysqli.default_socket=$LOCAL_MYSQL_SOCKET" -d "pdo_mysql.default_socket=$LOCAL_MYSQL_SOCKET" "$LOCAL_WP_CLI" \
		--path="$LOCAL_WP_PATH" "$@"
}

for arg in "$@"; do
	case "$arg" in
		--dry-run) DRY_RUN=1 ;;
		--yes)
			ASSUME_YES=1
			;;
		-h|--help)
			usage
			exit 0
			;;
		*)
			die "Unknown argument: $arg"
			;;
	esac
done

. "$SCRIPT_DIR/lib/config.sh"
require_config SSH_KEY REMOTE_HOST REMOTE_WP_PATH REMOTE_URL REMOTE_BACKUP_DIR LOCAL_URL LOCAL_WP_PATH LOCAL_PHP_BIN LOCAL_PHP_INI LOCAL_WP_CLI LOCAL_MYSQL_BIN_DIR LOCAL_MYSQL_SOCKET

command -v ssh >/dev/null 2>&1 || die "ssh is not installed."
command -v scp >/dev/null 2>&1 || die "scp is not installed."
command -v curl >/dev/null 2>&1 || die "curl is not installed."
command -v grep >/dev/null 2>&1 || die "grep is not installed."

[ -r "$SSH_KEY" ] || die "SSH key not found: $SSH_KEY"
[ -n "$REMOTE_HOST" ] || die "REMOTE_HOST is not configured. Update scripts/baltic-vending-solutions.env."
[ -n "$REMOTE_URL" ] || die "REMOTE_URL is not configured. Update scripts/baltic-vending-solutions.env."
[ -x "$LOCAL_PHP_BIN" ] || die "Local PHP binary not found: $LOCAL_PHP_BIN"
[ -r "$LOCAL_PHP_INI" ] || die "Local php.ini not found: $LOCAL_PHP_INI"
[ -r "$LOCAL_WP_CLI" ] || die "Local WP-CLI not found: $LOCAL_WP_CLI"
[ -d "$LOCAL_WP_PATH" ] || die "Local WordPress path not found: $LOCAL_WP_PATH"
[ -x "$LOCAL_MYSQL_BIN_DIR/mysql" ] || die "Local MySQL client not found in $LOCAL_MYSQL_BIN_DIR"
[ -x "$LOCAL_MYSQL_BIN_DIR/mysqldump" ] || die "Local mysqldump not found in $LOCAL_MYSQL_BIN_DIR"
[ -S "$LOCAL_MYSQL_SOCKET" ] || die "Local MySQL socket not found: $LOCAL_MYSQL_SOCKET. Start the Baltic Vending Solutions site in Local."
case "$BACKUP_KEEP" in
	''|*[!0-9]*) die "BACKUP_KEEP must be a positive integer." ;;
esac
[ "$BACKUP_KEEP" -ge 1 ] || die "BACKUP_KEEP must be at least 1."

REMOTE_URL="${REMOTE_URL%/}"
LOCAL_URL="${LOCAL_URL%/}"
SSH_ARGS=(-i "$SSH_KEY" -o BatchMode=yes -o ConnectTimeout=10)

local_wp --skip-plugins --skip-themes db check >/dev/null \
	|| die "Local database is unavailable. Start the Baltic Vending Solutions site in Local."
ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" \
	"wp --allow-root --path='$REMOTE_WP_PATH' --skip-plugins --skip-themes db check >/dev/null" \
	|| die "Droplet database is unavailable."

ACTUAL_LOCAL_HOME="$(local_wp --skip-plugins --skip-themes option get home)"
[ "${ACTUAL_LOCAL_HOME%/}" = "$LOCAL_URL" ] \
	|| die "Local home URL is $ACTUAL_LOCAL_HOME, expected $LOCAL_URL"

if [ "$DRY_RUN" -eq 1 ]; then
	printf 'Preflight passed. No database or uploads changed.\n'
	exit 0
fi

if [ "$ASSUME_YES" -ne 1 ]; then
	printf 'This will REPLACE the database on %s with the local database.\n' "$REMOTE_HOST"
	printf 'Production pages, menus, settings, users, and plugin data will be overwritten.\n'
	printf 'Type PUSH TO PRODUCTION to continue: '
	read -r confirmation || die "No confirmation received."
	[ "$confirmation" = "PUSH TO PRODUCTION" ] || die "Cancelled."
fi

TIMESTAMP="$(date +%Y%m%d-%H%M%S)"
TEMP_DIR="$(mktemp -d /tmp/baltic-vending-solutions-push.XXXXXX)"
LOCAL_EXPORT="$TEMP_DIR/local-for-droplet.sql"
REMOTE_TEMP="$(ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" "umask 077; mktemp /tmp/baltic-vending-solutions-push.XXXXXXXX.sql")"
REMOTE_BACKUP="$REMOTE_BACKUP_DIR/before-local-push-$TIMESTAMP.sql.gz"

printf 'Preparing the local database with production URLs...\n'
local_wp --skip-plugins --skip-themes search-replace \
	"$LOCAL_URL" "$REMOTE_URL" \
	--all-tables-with-prefix --precise --skip-columns=guid \
	--export="$LOCAL_EXPORT" >/dev/null

[ -s "$LOCAL_EXPORT" ] || die "The local export is empty. Production was not changed."
LC_ALL=C grep -q 'CREATE TABLE' "$LOCAL_EXPORT" || die "The local export does not look like a SQL dump. Production was not changed."

printf 'Backing up the droplet database...\n'
ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" \
	"set -e; umask 077; mkdir -p '$REMOTE_BACKUP_DIR'; wp --allow-root --path='$REMOTE_WP_PATH' --skip-plugins --skip-themes db export '${REMOTE_BACKUP%.gz}' --add-drop-table --quiet; gzip -f '${REMOTE_BACKUP%.gz}'"
printf 'Remote rollback backup: %s\n' "$REMOTE_BACKUP"

printf 'Uploading and importing the local database...\n'
scp "${SSH_ARGS[@]}" "$LOCAL_EXPORT" "$REMOTE_HOST:$REMOTE_TEMP"
ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" \
	"set -e; wp --allow-root --path='$REMOTE_WP_PATH' --skip-plugins --skip-themes db import '$REMOTE_TEMP'; wp --allow-root --path='$REMOTE_WP_PATH' --skip-plugins --skip-themes search-replace '$LOCAL_URL' '$REMOTE_URL' --all-tables-with-prefix --precise --skip-columns=guid --quiet; wp --allow-root --path='$REMOTE_WP_PATH' --skip-plugins --skip-themes option update home '$REMOTE_URL' --quiet; wp --allow-root --path='$REMOTE_WP_PATH' --skip-plugins --skip-themes option update siteurl '$REMOTE_URL' --quiet; wp --allow-root --path='$REMOTE_WP_PATH' cache flush; wp --allow-root --path='$REMOTE_WP_PATH' rewrite flush; rm -f -- '$REMOTE_TEMP'"
REMOTE_TEMP=""

ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" \
	"count=0; for backup in \$(find '$REMOTE_BACKUP_DIR' -maxdepth 1 -type f -name 'before-local-push-*.sql.gz' -print | sort -r); do count=\$((count + 1)); if [ \"\$count\" -gt '$BACKUP_KEEP' ]; then rm -f -- \"\$backup\"; fi; done"

ACTUAL_REMOTE_HOME="$(ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" "wp --allow-root --path='$REMOTE_WP_PATH' --skip-plugins --skip-themes option get home")"
[ "${ACTUAL_REMOTE_HOME%/}" = "$REMOTE_URL" ] \
	|| die "Production URL verification failed. Restore from $REMOTE_BACKUP"

curl --fail --silent --show-error --location "$REMOTE_URL/" >/dev/null

printf 'Droplet database replaced and verified at %s\n' "$REMOTE_URL"
printf 'Remote rollback backup: %s\n' "$REMOTE_BACKUP"
