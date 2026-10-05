#!/usr/bin/env bash
set -Eeuo pipefail
umask 077

WP_PATH=/var/www/baltic-vending-solutions
[[ $EUID -eq 0 ]] || exit 1
[[ ! -f "$WP_PATH/wp-config.php" ]] || { printf 'Existing WordPress config; refusing to overwrite.\n' >&2; exit 1; }
source /root/bvs-wordpress-db.env
wp --allow-root --path="$WP_PATH" config create --dbname=bvs_wordpress \
    --dbuser=bvs_wordpress --dbpass="$BVS_DB_PASSWORD" --dbhost=localhost \
    --dbprefix=wp_ --dbcharset=utf8 --skip-salts --quiet
for salt in AUTH_KEY SECURE_AUTH_KEY LOGGED_IN_KEY NONCE_KEY AUTH_SALT SECURE_AUTH_SALT LOGGED_IN_SALT NONCE_SALT; do
    wp --allow-root --path="$WP_PATH" config set "$salt" "$(openssl rand -hex 32)" --quiet
done
wp --allow-root --path="$WP_PATH" config set WP_ENVIRONMENT_TYPE staging --quiet
wp --allow-root --path="$WP_PATH" config set WP_MEMORY_LIMIT 256M --quiet
wp --allow-root --path="$WP_PATH" config set WP_MAX_MEMORY_LIMIT 256M --quiet
wp --allow-root --path="$WP_PATH" config set BVS_PRIVATE_DESIGN_DIR /var/lib/bvs-private-designs --quiet
wp --allow-root --path="$WP_PATH" config set DISALLOW_FILE_EDIT true --raw --quiet
chown -R www-data:www-data "$WP_PATH"
find "$WP_PATH" -type d -exec chmod 755 {} +
find "$WP_PATH" -type f -exec chmod 644 {} +
chmod 640 "$WP_PATH/wp-config.php"
rm -f /root/bvs-wordpress-db.env
printf 'WordPress configured with independent server credentials and salts.\n'
