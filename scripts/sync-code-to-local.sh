#!/usr/bin/env bash
set -Eeuo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
DRY_RUN=0
for arg in "$@"; do
	case "$arg" in
		--dry-run) DRY_RUN=1 ;;
		-h|--help) printf 'Usage: ./scripts/sync-code-to-local.sh [--dry-run]\nCopies repository theme into the configured Local site.\n'; exit 0 ;;
		*) printf 'Unknown argument: %s\n' "$arg" >&2; exit 1 ;;
	esac
done
CONFIG_FILE="${CONFIG_FILE:-$SCRIPT_DIR/baltic-vending-solutions.env}"
if [ -f "$CONFIG_FILE" ]; then . "$CONFIG_FILE"; fi
die() { printf 'Error: %s\n' "$*" >&2; exit 1; }
. "$SCRIPT_DIR/lib/config.sh"
require_config LOCAL_WP_PATH
LOCAL_THEME_PATH="${LOCAL_THEME_PATH:-$PROJECT_ROOT/theme/baltic-vending-solutions}"
TARGET="$LOCAL_WP_PATH/wp-content/themes/baltic-vending-solutions"
[ -f "$LOCAL_WP_PATH/wp-config.php" ] || die "Local WordPress is not installed at LOCAL_WP_PATH."
[ -d "$LOCAL_WP_PATH/wp-content/themes" ] || die "Local themes directory is missing."
[ -f "$LOCAL_THEME_PATH/style.css" ] && [ -f "$LOCAL_THEME_PATH/functions.php" ] || die "Build the repository theme first."
[ ! -L "$TARGET" ] || die "Local theme destination must not be a symlink."
[ ! -e "$TARGET" ] || [ "$(cd "$TARGET" && pwd -P)" != "$(cd "$LOCAL_THEME_PATH" && pwd -P)" ] || die "Source and destination are the same theme."
ARGS=(--archive --no-owner --no-group --checksum --delete-after --itemize-changes --exclude=.DS_Store)
if [ "$DRY_RUN" -eq 1 ]; then ARGS+=(--dry-run); else mkdir -p "$TARGET"; fi
rsync "${ARGS[@]}" "$LOCAL_THEME_PATH/" "$TARGET/"
