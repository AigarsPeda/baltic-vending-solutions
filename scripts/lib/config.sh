# Shared validation. Source after argument parsing so --help needs no config.
require_config() {
	local name value
	for name in "$@"; do
		value="${!name:-}"
		[ -n "$value" ] || die "$name is blank. Configure scripts/baltic-vending-solutions.env."
	done
	for name in REMOTE_WP_PATH REMOTE_BACKUP_DIR; do
		value="${!name:-}"
		[ -n "$value" ] || continue
		[[ "$value" =~ ^/[a-zA-Z0-9_./-]+$ ]] || die "$name must be an absolute path without spaces or shell characters."
		case "$value" in /|*/|*//*|*/../*|*/..|*/./*|*/.) die "Unsafe $name: $value" ;; esac
	done
	if [ -n "${REMOTE_WP_PATH:-}" ]; then
		[[ "$REMOTE_WP_PATH" =~ ^/.+/.+$ ]] || die "REMOTE_WP_PATH must name a dedicated WordPress directory."
	fi
	if [ -n "${REMOTE_BACKUP_DIR:-}" ] && [ -n "${REMOTE_WP_PATH:-}" ]; then
		case "$REMOTE_BACKUP_DIR/" in "$REMOTE_WP_PATH/"*) die "Backups must be outside WordPress." ;; esac
	fi
	case "${SSH_KEY:-}" in
		*\"*|*$'\n'*|*$'\r'*) die "SSH_KEY must not contain double quotes or line breaks." ;;
	esac
	if [ -n "${REMOTE_HOST:-}" ]; then
		[[ "$REMOTE_HOST" =~ ^[a-zA-Z0-9_][a-zA-Z0-9_-]*@[a-zA-Z0-9][a-zA-Z0-9.-]*$ ]] || die "REMOTE_HOST must be user@hostname or user@IPv4."
	fi
	for name in LOCAL_URL REMOTE_URL; do
		value="${!name:-}"
		[ -n "$value" ] || continue
		[[ "$value" =~ ^https?://[a-zA-Z0-9][a-zA-Z0-9.:-]*/?$ ]] || die "$name must be an HTTP(S) origin without a path."
	done
	if [ -n "${LOCAL_URL:-}" ]; then
		[[ "$LOCAL_URL" =~ ^https?://[a-zA-Z0-9.-]+\.local(:[0-9]+)?/?$ ]] || die "LOCAL_URL must identify this project's .local site."
	fi
	if [ -n "${LOCAL_URL:-}" ] && [ -n "${REMOTE_URL:-}" ]; then
		[ "${LOCAL_URL%/}" != "${REMOTE_URL%/}" ] || die "Local and remote URLs must differ."
	fi
	for name in LOCAL_WP_PATH LOCAL_PHP_BIN LOCAL_PHP_INI LOCAL_WP_CLI LOCAL_MYSQL_BIN_DIR LOCAL_MYSQL_SOCKET SSH_KEY BACKUP_DIR LOCAL_THEME_PATH; do
		value="${!name:-}"
		[ -n "$value" ] || continue
		[[ "$value" == /* ]] || die "$name must be an absolute path."
	done
	if [ -n "${BACKUP_DIR:-}" ] && [ -n "${LOCAL_WP_PATH:-}" ]; then
		case "$BACKUP_DIR/" in "$LOCAL_WP_PATH/"*) die "Local backups must be outside WordPress." ;; esac
	fi
}
