#!/usr/bin/env bash
set -Eeuo pipefail
umask 077
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DRY_RUN=0
for arg in "$@"; do
	case "$arg" in
		--dry-run) DRY_RUN=1 ;;
		-h|--help) printf 'Usage: ./scripts/sync-plugins-to-droplet.sh [--dry-run]\nCopies Local plugins and mu-plugins, backing up remote directories first.\nDoes not delete remote plugins or activate plugins.\n'; exit 0 ;;
		*) printf 'Unknown argument: %s\n' "$arg" >&2; exit 1 ;;
	esac
done
CONFIG_FILE="${CONFIG_FILE:-$SCRIPT_DIR/baltic-vending-solutions.env}"
if [ -f "$CONFIG_FILE" ]; then . "$CONFIG_FILE"; fi
die() { printf 'Error: %s\n' "$*" >&2; exit 1; }
. "$SCRIPT_DIR/lib/config.sh"
require_config SSH_KEY REMOTE_HOST REMOTE_WP_PATH REMOTE_BACKUP_DIR LOCAL_WP_PATH
[ -r "$SSH_KEY" ] || die "SSH key is unreadable."
[ -f "$LOCAL_WP_PATH/wp-config.php" ] || die "Local WordPress is missing."
[ -d "$LOCAL_WP_PATH/wp-content/plugins" ] || die "Local plugins are missing."
SSH_ARGS=(-i "$SSH_KEY" -o BatchMode=yes -o ConnectTimeout=10)
RSYNC_SSH="ssh -i \"$SSH_KEY\" -o BatchMode=yes -o ConnectTimeout=10"
ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" "test -f '$REMOTE_WP_PATH/wp-config.php' && test -d '$REMOTE_WP_PATH/wp-content'"
ARGS=(--archive --no-owner --no-group --compress --checksum --itemize-changes --exclude=.DS_Store)
if [ "$DRY_RUN" -eq 1 ]; then
	ARGS+=(--dry-run)
else
	BACKUP="$(ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" "umask 077; mkdir -p '$REMOTE_BACKUP_DIR' && mktemp -d '$REMOTE_BACKUP_DIR/plugins-$(date +%Y%m%d-%H%M%S).XXXXXXXX'")"
	ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" "set -e; umask 077; for dir in plugins mu-plugins; do if [ -d '$REMOTE_WP_PATH/wp-content/'\"\$dir\" ]; then tar -czf '$BACKUP/'\"\$dir\"'.tar.gz' -C '$REMOTE_WP_PATH/wp-content' \"\$dir\"; fi; done"
	printf 'Private plugin backup directory: %s\n' "$BACKUP"
fi
for dir in plugins mu-plugins; do
	[ -d "$LOCAL_WP_PATH/wp-content/$dir" ] || continue
	if [ "$DRY_RUN" -eq 0 ]; then ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" "mkdir -p '$REMOTE_WP_PATH/wp-content/$dir'"; fi
	rsync "${ARGS[@]}" -e "$RSYNC_SSH" "$LOCAL_WP_PATH/wp-content/$dir/" "$REMOTE_HOST:$REMOTE_WP_PATH/wp-content/$dir/"
	if [ "$DRY_RUN" -eq 0 ]; then ssh "${SSH_ARGS[@]}" "$REMOTE_HOST" "chown -R www-data:www-data '$REMOTE_WP_PATH/wp-content/$dir'"; fi
done
