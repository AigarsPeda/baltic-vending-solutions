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
REMOTE_BACKUP_DIR="${REMOTE_BACKUP_DIR:-}"
REMOTE_URL="${REMOTE_URL:-}"
LOCAL_THEME_PATH="${LOCAL_THEME_PATH:-$PROJECT_ROOT/theme/baltic-vending-solutions}"
REMOTE_THEME_PATH="$REMOTE_WP_PATH/wp-content/themes/baltic-vending-solutions"

DRY_RUN=0

usage() {
	cat <<'EOF'
Usage: ./scripts/sync-code-to-droplet.sh [--dry-run]

Synchronize the local Baltic Vending Solutions theme with the DigitalOcean droplet.
The remote theme directory becomes an exact copy of the local theme directory.
Apply runs save a private archive of the live theme before changing files.

Before the first use, copy scripts/baltic-vending-solutions.env.example to
scripts/baltic-vending-solutions.env and fill in the required values.

Environment overrides:
  CONFIG_FILE, SSH_KEY, REMOTE_HOST, REMOTE_WP_PATH, REMOTE_BACKUP_DIR, REMOTE_URL,
  LOCAL_THEME_PATH
EOF
}

die() {
	printf 'Error: %s\n' "$*" >&2
	exit 1
}

for arg in "$@"; do
	case "$arg" in
		--dry-run)
			DRY_RUN=1
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
require_config SSH_KEY REMOTE_HOST REMOTE_WP_PATH REMOTE_BACKUP_DIR REMOTE_URL

command -v ssh >/dev/null 2>&1 || die "ssh is not installed."
command -v rsync >/dev/null 2>&1 || die "rsync is not installed."
command -v shasum >/dev/null 2>&1 || die "shasum is not installed."
command -v curl >/dev/null 2>&1 || die "curl is not installed."

[ -r "$SSH_KEY" ] || die "SSH key not found: $SSH_KEY"
[ -n "$REMOTE_HOST" ] || die "REMOTE_HOST is not configured. Update scripts/baltic-vending-solutions.env."
[ -n "$REMOTE_URL" ] || die "REMOTE_URL is not configured. Update scripts/baltic-vending-solutions.env."
[ -d "$LOCAL_THEME_PATH" ] || die "Local theme not found: $LOCAL_THEME_PATH"
[ -f "$LOCAL_THEME_PATH/style.css" ] || die "Local theme is missing style.css."
[ -f "$LOCAL_THEME_PATH/functions.php" ] || die "Local theme is missing functions.php."

case "$REMOTE_THEME_PATH" in
	*/wp-content/themes/baltic-vending-solutions)
		;;
	*)
		die "Refusing to synchronize unexpected remote path: $REMOTE_THEME_PATH"
		;;
esac
case "$REMOTE_BACKUP_DIR/" in
	"$REMOTE_WP_PATH/"*) die "Backups must be outside the public WordPress directory." ;;
esac

SSH_ARGS=(-i "$SSH_KEY" -o BatchMode=yes -o ConnectTimeout=10)
RSYNC_SSH="ssh -i \"$SSH_KEY\" -o BatchMode=yes -o ConnectTimeout=10"

ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" \
	"test -d '$REMOTE_WP_PATH/wp-content/themes' && test -f '$REMOTE_WP_PATH/wp-config.php'" \
	|| die "Remote WordPress installation was not found at $REMOTE_WP_PATH"

RSYNC_ARGS=(
	--archive
	--no-owner
	--no-group
	--compress
	--checksum
	--delete-after
	--itemize-changes
	--exclude=.DS_Store
)

if [ "$DRY_RUN" -eq 1 ]; then
	RSYNC_ARGS+=(--dry-run)
	printf 'Dry run. No remote files will change.\n'
else
	REMOTE_BACKUP="$(ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" \
		"umask 077; mkdir -p '$REMOTE_BACKUP_DIR' && mktemp -d '$REMOTE_BACKUP_DIR/theme-$(date +%Y%m%d-%H%M%S).XXXXXXXX'")"
	ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" \
		"set -e; umask 077; if [ -d '$REMOTE_THEME_PATH' ]; then tar -czf '$REMOTE_BACKUP/theme.tar.gz' -C '$(dirname "$REMOTE_THEME_PATH")' baltic-vending-solutions; else touch '$REMOTE_BACKUP/no-previous-theme'; fi; mkdir -p '$REMOTE_THEME_PATH'"
	printf 'Private theme backup directory: %s\n' "$REMOTE_BACKUP"
fi

rsync "${RSYNC_ARGS[@]}" -e "$RSYNC_SSH" \
	"$LOCAL_THEME_PATH/" \
	"$REMOTE_HOST:$REMOTE_THEME_PATH/"

if [ "$DRY_RUN" -eq 1 ]; then
	exit 0
fi

ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" \
	"chown -R www-data:www-data '$REMOTE_THEME_PATH' && wp --allow-root --path='$REMOTE_WP_PATH' cache flush && wp --allow-root --path='$REMOTE_WP_PATH' rewrite flush"

LOCAL_HASH="$(shasum -a 256 "$LOCAL_THEME_PATH/functions.php" | awk '{print $1}')"
REMOTE_HASH="$(ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" "sha256sum '$REMOTE_THEME_PATH/functions.php'" | awk '{print $1}')"

[ "$LOCAL_HASH" = "$REMOTE_HASH" ] || die "Remote verification failed: functions.php hashes differ."

curl --fail --silent --show-error --location "$REMOTE_URL/" >/dev/null

printf 'Theme synchronized and verified at %s\n' "$REMOTE_URL"
