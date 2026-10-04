#!/usr/bin/env bash
set -Eeuo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
if [ "${1:-}" = "--help" ] || [ "${1:-}" = "-h" ]; then
 printf 'Usage: ./scripts/wp-local.sh <wp-cli command> [arguments]\nRuns WP-CLI with this Local site runtime.\n'; exit 0
fi
CONFIG_FILE="${CONFIG_FILE:-$SCRIPT_DIR/baltic-vending-solutions.env}"
[ ! -f "$CONFIG_FILE" ] || . "$CONFIG_FILE"
die() { printf 'Error: %s\n' "$*" >&2; exit 1; }
. "$SCRIPT_DIR/lib/config.sh"
require_config LOCAL_WP_PATH LOCAL_PHP_BIN LOCAL_PHP_INI LOCAL_WP_CLI LOCAL_MYSQL_SOCKET
[ -f "$LOCAL_WP_PATH/wp-config.php" ] || die 'Configured Local installation is missing.'
[ -S "$LOCAL_MYSQL_SOCKET" ] || die 'Start this site in Local first.'
export PATH="${LOCAL_MYSQL_BIN_DIR:-/usr/bin}:$PATH"
exec "$LOCAL_PHP_BIN" -c "$LOCAL_PHP_INI" -d "mysqli.default_socket=$LOCAL_MYSQL_SOCKET" -d "pdo_mysql.default_socket=$LOCAL_MYSQL_SOCKET" "$LOCAL_WP_CLI" --path="$LOCAL_WP_PATH" "$@"
