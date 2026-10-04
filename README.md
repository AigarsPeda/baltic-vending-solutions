# Baltic Vending Solutions

Starter repository for a WordPress website offering vending equipment for sale or rent and custom branding to match the customer's business. Development will use Local App, with deployment to a DigitalOcean droplet later.

The repository currently contains documentation and synchronisation scripts. WordPress, the custom theme, the Local site and the droplet have not been created.

- [Project handoff](HANDOFF.md) records the current state and next steps.
- [Client profile](CLIENT_PROFILE.md) defines the audience, offer and content direction.
- [Style guide](STYLE_GUIDE.md) sets the working B2B visual and editorial rules.
- [WordPress plan](WORDPRESS_PLAN.md) defines plugins, native visual editing, media management and storage.
- [Equipment research](docs/equipment-research.md) summarises our proposal specifications and supporting manufacturer information.
- [Development and deployment](docs/development-and-deployment.md) explains configuration and complete website transfer.

The two supporting documents under `docs/` currently retain their original Latvian text. The client profile, handoff, README and style guide are in English. Documentation language does not determine the public website's languages.

GitHub repository: [AigarsPeda/baltic-vending-solutions](https://github.com/AigarsPeda/baltic-vending-solutions).

## WordPress requirements

Plan Polylang, Rank Math SEO, Site Kit by Google and WP Mail SMTP. Keep all website content editable through Gutenberg and native WordPress admin interfaces. Manage photographs and logos through Media Library; store authored content in the WordPress database and media in the configured uploads directory. The [WordPress plan](WORDPRESS_PLAN.md) records the hybrid-theme baseline, optional Polylang Pro/Site Editor route, Google setup and email responsibilities.

## Configuration

```bash
cp scripts/baltic-vending-solutions.env.example scripts/baltic-vending-solutions.env
```

All configuration fields are blank. Git ignores the real `.env` file. Scripts check the required fields and stop before connecting or making changes if they are missing. `--help` works without configuration. Check the Local and server paths for this project before filling them in.

## Scripts

| Script | Purpose |
| --- | --- |
| `scripts/sync-code-to-local.sh` | Copy the repository theme into Local |
| `scripts/sync-code-to-droplet.sh` | Copy the theme to the droplet and archive the previous theme |
| `scripts/sync-plugins-to-droplet.sh` | Copy plugins and mu-plugins after backing up the server copies |
| `scripts/sync-uploads-to-droplet.sh` | Copy upload files without creating Media Library records |
| `scripts/push-db-to-droplet.sh` | Replace the server database after a server backup |
| `scripts/pull-db-from-droplet.sh` | Replace the Local database after a Local backup and download uploads |

Every script supports `--dry-run`. File scripts show rsync changes; database scripts check connectivity and database availability without exporting or importing. Push and pull require an exact confirmation phrase after preflight. Use `--yes` only for an already authorised database replacement.

## Checks

```bash
python3 scripts/tests/smoke.py
```

Checks use temporary files and mocked SSH/WP-CLI processes. They also exercise installed rsync against a temporary Local directory. They do not connect to a droplet or modify a real WordPress site. Actual Local and test-server verification is still required after configuration.
