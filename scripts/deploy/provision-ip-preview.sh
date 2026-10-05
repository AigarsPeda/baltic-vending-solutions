#!/usr/bin/env bash
set -Eeuo pipefail
umask 077

DEPLOY_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
WP_PATH=/var/www/baltic-vending-solutions
BACKUP_PATH=/var/backups/baltic-vending-solutions

[[ $EUID -eq 0 ]] || { printf 'Run on the droplet as root.\n' >&2; exit 1; }
[[ ! -f "$WP_PATH/wp-config.php" ]] || { printf 'WordPress already configured; refusing to provision over it.\n' >&2; exit 1; }
[[ -s /tmp/bvs-wp-cli.phar ]] || { printf 'Upload WP-CLI to /tmp/bvs-wp-cli.phar first.\n' >&2; exit 1; }

if ! swapon --show=NAME --noheadings | grep -q .; then
    [[ ! -e /swapfile ]] || { printf 'Existing inactive swapfile needs inspection.\n' >&2; exit 1; }
    fallocate -l 1G /swapfile
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile
    printf '/swapfile none swap sw 0 0\n' >> /etc/fstab
fi

export DEBIAN_FRONTEND=noninteractive
apt-get update
apt-get install -y --no-install-recommends nginx mariadb-server mariadb-client \
    php8.3-fpm php8.3-cli php8.3-mysql php8.3-curl php8.3-gd php8.3-mbstring \
    php8.3-xml php8.3-zip php8.3-intl curl rsync unzip

install -m 644 "$DEPLOY_DIR/mariadb-small-droplet.cnf" /etc/mysql/mariadb.conf.d/90-bvs.cnf
install -m 644 "$DEPLOY_DIR/php-small-droplet.conf" /etc/php/8.3/fpm/pool.d/zz-bvs.conf
install -m 755 /tmp/bvs-wp-cli.phar /usr/local/bin/wp
install -d -m 755 -o www-data -g www-data "$WP_PATH"
install -d -m 700 "$BACKUP_PATH"
install -d -m 700 -o www-data -g www-data /var/lib/bvs-private-designs
systemctl restart mariadb php8.3-fpm

DB_PASSWORD="$(openssl rand -hex 32)"
mysql <<SQL
CREATE DATABASE bvs_wordpress CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'bvs_wordpress'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON bvs_wordpress.* TO 'bvs_wordpress'@'localhost';
FLUSH PRIVILEGES;
SQL
printf 'BVS_DB_PASSWORD=%s\n' "$DB_PASSWORD" > /root/bvs-wordpress-db.env
chmod 600 /root/bvs-wordpress-db.env

install -m 644 "$DEPLOY_DIR/nginx-ip-preview.conf" /etc/nginx/sites-available/baltic-vending-solutions
ln -s /etc/nginx/sites-available/baltic-vending-solutions /etc/nginx/sites-enabled/baltic-vending-solutions
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl enable nginx mariadb php8.3-fpm
systemctl restart nginx
ufw allow OpenSSH
ufw allow 80/tcp
ufw --force enable
printf 'Droplet ready for the local WordPress files and database.\n'
