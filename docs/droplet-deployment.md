# Droplet deployment

Deployed the complete running Local website on 2026-10-05 at [http://167.71.44.25/](http://167.71.44.25/). The [English version](http://167.71.44.25/en/) and [WordPress admin](http://167.71.44.25/wp-admin/) use the same saved content and WordPress accounts as Local. No domain is configured. The preview uses HTTP.

## Configuration

All transfer scripts read `scripts/baltic-vending-solutions.env`. This file is ignored by Git and contains the configured values on this machine. Keep the portable example blank for other installations.

| Variable | Value |
| --- | --- |
| `SSH_KEY` | `/Users/aigarspeda/.ssh/janogago_ed25519` |
| `REMOTE_HOST` | `root@167.71.44.25` |
| `REMOTE_WP_PATH` | `/var/www/baltic-vending-solutions` |
| `REMOTE_URL` | `http://167.71.44.25` |
| `REMOTE_BACKUP_DIR` | `/var/backups/baltic-vending-solutions` |
| `BACKUP_DIR` | `/Users/aigarspeda/Desktop/Baltic-Vending-Solutions/backups` |
| `BACKUP_KEEP` | `3` |

The existing Local PHP runtime, database socket, WP-CLI and `.local` origin remain configured for this project. JanogaGo's server and database are separate; only its SSH key was reused.

## Server

The fresh Ubuntu 24.04 droplet has nginx, PHP 8.3 FPM, MariaDB 10.11, WP-CLI and rsync. Its 512 MB RAM is supported by 1 GB swap, two on-demand PHP workers and a 64 MB InnoDB buffer pool. Services start at boot. UFW allows SSH and HTTP; MariaDB listens on loopback only.

### PHP security update, 2026-10-22

Updated all 12 installed PHP 8.3 packages from `8.3.6-0ubuntu0.24.04.12` to `8.3.6-0ubuntu0.24.04.13`, then explicitly restarted PHP-FPM at 06:46:49 UTC. This Ubuntu package includes the fixes for CVE-2026-109618 and CVE-2026-109619, as documented in [USN-8948-1](https://ubuntu.com/security/notices/USN-8948-1) and the installed package changelog. The upstream version still displays as PHP 8.3.6 because Ubuntu backports the fixes. Existing configuration was retained; no packages were added or removed.

The private backup is `/var/backups/baltic-vending-solutions/php-security-20261022-4SdRPU/`, containing `database-before.sql.gz`, `service-config-before.tar.gz` for PHP/nginx/APT configuration, and `packages-before.tsv`.

Verification passed PHP-FPM/nginx configuration tests, active PHP-FPM/nginx/MariaDB services, the running FPM executable and nginx socket mapping, WordPress bootstrap, and all database integrity checks. The PHP-FPM/nginx service journals contained no warnings since the restart. Existing deployment browser helpers passed ten LV/EN routes at 1440 and 375 px, including loaded images/models, one main heading, no horizontal overflow, no failed HTTP requests and no browser errors. The WordPress login redirect, mobile menu, actual 3D camera/texture changes, draft persistence and PNG export also passed. No quote was submitted. Browser captures and `report.json` are in ignored `output/php-security-20261022/`.

Local remains on PHP 8.2.29. Its installed Local service bundles do not include a patched release. The updater and refreshed selector were checked on 2026-10-22; Local's newest offered PHP 8.3 build is still 8.3.30. The owner chose to wait for a supported patched Local build rather than integrate a custom runtime. Runtime paths are unchanged, and the existing local site passed verification after the app restarted. See `docs/local-development.md` for the pending update and backup record. When available, update through Local to PHP 8.3.36 or later on the 8.3 branch to match the server, then update `LOCAL_PHP_BIN` and any changed runtime paths and verify Local again. A Homebrew/CLI-only update does not replace Local's web-serving PHP runtime.

WordPress 7.1.2 and all source files, uploads and plugin versions were copied from Local. The database includes the ten published pages, both languages, widgets, menus, Media Library metadata, theme settings, SEO scores and accounts. Server `wp-config.php` has independent database credentials and authentication salts. It uses `WP_ENVIRONMENT_TYPE=staging`; both WordPress and nginx prevent indexing.

Customer designs are stored in `/var/lib/bvs-private-designs`, owned by `www-data` with directory mode 700 and file mode 600. The WordPress config is mode 640. nginx denies configuration files, hidden files, SQL/log backups, XML-RPC and PHP execution in uploads. Domain-specific configuration lives at `/etc/nginx/sites-available/baltic-vending-solutions`.

Outbound email remains unconfigured, as on Local. The server has no sendmail transport or configured SMTP provider. Quote submissions save in WordPress even when notification delivery fails. SMTP, Google account connections, final business/contact details, a domain and its HTTPS certificate are still required for the later public launch.

`scripts/deploy/` records the initial provisioning and WordPress configuration. These scripts run on a fresh droplet and refuse to overwrite an existing WordPress config. Routine updates use the transfer scripts below.

## Updates

Review these dry runs before transferring a change:

```bash
./scripts/sync-code-to-droplet.sh --dry-run
./scripts/sync-plugins-to-droplet.sh --dry-run
./scripts/sync-uploads-to-droplet.sh --dry-run
./scripts/push-db-to-droplet.sh --dry-run
./scripts/pull-db-from-droplet.sh --dry-run
```

All five passed against this droplet. The theme, plugins and uploads had no content differences. A full database push replaces live content, users, settings and enquiries; check for newer server data before using it. A pull replaces Local content. Source-code and database transfers are separate operations, and private customer designs outside WordPress are not copied by the existing upload script.

The private initial release backup is `/var/backups/baltic-vending-solutions/initial-release-CN4fnYDA/`. It contains `database.sql.gz`, `wordpress.tar.gz`, `private-designs.tar.gz` and nginx/PHP/MariaDB configuration copies. It was refreshed after removing the synthetic enquiry. Transfer scripts also create their documented backups before replacing database, theme or plugin data. Automatic daily backups have not been configured.

## Verification

`scripts/tests/deployment-parity.cjs` compares Local with the deployed site. It requires Playwright, Chrome and `pngjs`:

```bash
NODE_PATH=/path/to/node_modules node scripts/tests/deployment-parity.cjs
```

It checks ten routes across 28 desktop/mobile screenshot pairs, matching text, navigation destinations, source images and responsive image metadata, with no overflow, failed asset requests or browser errors. Twenty-four screenshot pairs were pixel-identical. Four homepage pairs at 768/375 px had small responsive-image raster differences, at most 0.00061% of pixels differing by more than eight RGB levels. Both editor pages matched exactly at every checked width.

The live interactions passed mobile menu controls, rendered 3D camera movement, texture recolouring, IndexedDB draft persistence over the HTTP IP origin and PNG export. The initial run also used `BVS_VERIFY_FORMS=1` to submit a synthetic enquiry with a design. Its private record and file were verified, then removed. Normal reruns do not submit a quote unless that flag is explicitly enabled.

Additional live checks passed no cookies before a choice, consent persistence across languages on the HTTP IP, reopening, acceptance and withdrawal, with no Analytics requests or browser errors. The admin route redirects to the IP-based login page. nginx/PHP configuration checks and database integrity checks also passed.

Screenshots and `report.json` are in ignored `output/deployment-parity/`. A final checksum dry run compared the complete deployed WordPress files with Local, excluding the independent server config and temporary files. URL replacement validation reported zero remaining Local references outside preserved GUID columns.
