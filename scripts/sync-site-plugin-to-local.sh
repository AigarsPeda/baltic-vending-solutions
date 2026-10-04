#!/usr/bin/env bash
set -Eeuo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
DRY_RUN=0
for arg in "$@"; do
 case "$arg" in --dry-run) DRY_RUN=1;; -h|--help) printf 'Usage: ./scripts/sync-site-plugin-to-local.sh [--dry-run]\n'; exit 0;; *) printf 'Unknown argument: %s\n' "$arg" >&2; exit 1;; esac
done
CONFIG_FILE="${CONFIG_FILE:-$SCRIPT_DIR/baltic-vending-solutions.env}"
[ ! -f "$CONFIG_FILE" ] || . "$CONFIG_FILE"
die() { printf 'Error: %s\n' "$*" >&2; exit 1; }
. "$SCRIPT_DIR/lib/config.sh"
require_config LOCAL_WP_PATH
SOURCE="$PROJECT_ROOT/plugins/bvs-site"
TARGET="$LOCAL_WP_PATH/wp-content/plugins/bvs-site"
[ -f "$LOCAL_WP_PATH/wp-config.php" ] || die 'Local installation is missing.'
[ -f "$SOURCE/bvs-site.php" ] || die 'Site plugin source is missing.'
[ ! -L "$TARGET" ] || die 'Plugin destination must not be a symlink.'
ARGS=(--archive --no-owner --no-group --checksum --delete-after --itemize-changes --exclude=.DS_Store)
if [ "$DRY_RUN" -eq 1 ]; then ARGS+=(--dry-run); else mkdir -p "$TARGET"; fi
rsync "${ARGS[@]}" "$SOURCE/" "$TARGET/"
