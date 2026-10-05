# Baltic Vending Solutions

The first local WordPress version is implemented for vending equipment purchase, rental and custom branding. It runs in Local App with Latvian and English pages.

- [Droplet preview, no domain](http://167.71.44.25/) and [deployment notes](docs/droplet-deployment.md)
- [Local website](http://baltic-vending-solutions.local/)
- [English website](http://baltic-vending-solutions.local/en/)
- [WordPress admin](http://baltic-vending-solutions.local/wp-admin/)
- [Local development and editing guide](docs/local-development.md)
- [Privacy drafts and pending business details](docs/privacy-page.md)
- [Project handoff](HANDOFF.md)
- [Client profile](CLIENT_PROFILE.md), [style guide](STYLE_GUIDE.md) and [WordPress plan](WORDPRESS_PLAN.md)
- [Equipment research](docs/equipment-research.md) and [deployment workflow](docs/development-and-deployment.md)
- [Payments and product-management research](docs/payments-and-management.md)
- [Logo, favicon and native replacement controls](docs/brand-assets.md)

The equipment research and original deployment document remain in Latvian. New documentation is in English. GitHub repository: [AigarsPeda/baltic-vending-solutions](https://github.com/AigarsPeda/baltic-vending-solutions).

## Implementation

`theme/baltic-vending-solutions/` contains the hybrid theme, editor styles, locally hosted IBM Plex Sans fonts and navigation behaviour. `plugins/bvs-site/` contains the editable Quote Form and Cookie banner blocks, private enquiry records and notification handling.

Page content uses core Gutenberg blocks. Navigation uses native WordPress menus, and header/footer content uses per-language block widgets. Photos are imported into Media Library and stored in WordPress uploads. The repository does not contain the WordPress database, credentials or content photos.

Polylang, Rank Math SEO, Site Kit by Google, WP Mail SMTP and WP Consent API are installed. The translated [cookie banner](docs/cookie-consent.md) is active locally. Google services and production mail are unconfigured. Local intercepts mail through the BVS plugin, so quote notifications cannot leave the development site. Enquiries remain visible under **Quote enquiries** in admin.

## Local workflow

The ignored `scripts/baltic-vending-solutions.env` contains this machine's Local runtime paths and the droplet settings for `root@167.71.44.25`, using the JanogaGo SSH key. The example retains blank values for a different installation. Start the site in Local before running commands.

```bash
./scripts/sync-code-to-local.sh --dry-run
./scripts/sync-code-to-local.sh
./scripts/sync-site-plugin-to-local.sh
./scripts/wp-local.sh core version
```

Theme and plugin syncs update code without modifying saved pages, widgets, menus or media. The one-time content seed is described in the local guide. It exits once imported and never restores deleted sections.

## Scripts

| Script | Purpose |
| --- | --- |
| `sync-code-to-local.sh` | Copy the repository theme into Local |
| `sync-site-plugin-to-local.sh` | Copy the first-party site plugin into Local |
| `wp-local.sh` | Run WP-CLI with this site's PHP runtime and database socket |
| `sync-code-to-droplet.sh` | Copy theme code and archive the previous theme |
| `sync-plugins-to-droplet.sh` | Copy Local plugins and mu-plugins after a server backup |
| `sync-uploads-to-droplet.sh` | Copy uploaded files without creating attachment records |
| `push-db-to-droplet.sh` | Replace the server database after a server backup |
| `pull-db-from-droplet.sh` | Replace the Local database after a Local backup and download uploads |

Transfer scripts support `--help` and `--dry-run`. Database replacement requires explicit confirmation. The complete Local installation was deployed to `http://167.71.44.25` on 2026-10-05, including WordPress, saved content, uploads, theme and plugins. All five transfer-script dry runs passed. See [deployment notes](docs/droplet-deployment.md) for server paths, backups and verification.

## Checks

```bash
python3 scripts/tests/smoke.py
./scripts/wp-local.sh eval-file scripts/tests/wordpress.php
./scripts/wp-local.sh eval-file scripts/tests/consent.php
NODE_PATH=/path/to/playwright/node_modules node scripts/tests/browser.cjs
NODE_PATH=/path/to/playwright/node_modules node scripts/tests/cookies.cjs
NODE_PATH=/path/to/playwright/node_modules node scripts/tests/privacy.cjs
```

The smoke checks isolate transfer behaviour with temporary files and mocked remote tools. The WordPress checks use synthetic enquiries and remove their test records. The browser checks use Chrome, inspect eight routes at several widths, exercise navigation and submit a synthetic enquiry named `BVS browser verification`. Remove that test record from Quote enquiries after a manual run. Screenshots go into ignored `output/`.

Before launch, complete the legal identity, contact details, privacy notice, commercial terms, Google/consent and production email setup. The current site is a local development draft with search indexing disabled.
