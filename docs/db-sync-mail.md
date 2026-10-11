# Mail settings during database sync

Both full database scripts preserve the destination's mail connection configuration. A Local push keeps the droplet's SMTP/OAuth connection; a droplet pull keeps Local's connection. The policy is adapted from the updated JanogaGo helper and tests, with BVS's own enquiry-recipient option.

`bvs_recipient` and `admin_email` synchronize with ordinary site settings. Pending admin-address changes and their verification hashes remain environment-specific. Public contact copy remains ordinary editable content.

The protected settings include WP Mail SMTP configuration and related options/transients, legacy SMTP settings, provider keys, sender/return-path settings and WordPress mail-server settings. This preserves the whole provider configuration, including client IDs, client secrets, access/refresh tokens and SMTP usernames/passwords. Credentials stored in `wp-config.php` or environment variables remain separate because those files are not transferred.

Mail logs/queues and WP Mail SMTP task tables are excluded. The WordPress `cron` option, Action Scheduler tables and scheduler runtime options also remain on the destination. All scheduled jobs are preserved because these shared queues can contain mail-delivery jobs.

## Transfer behaviour

`scripts/wordpress-db-sync.php` supplies one policy for both directions. Each script validates matching table prefixes and an InnoDB options table on both environments before confirmation or replacement. `--dry-run` checks these prerequisites without exports, imports or remote helper files.

Transfers export only whitelisted site tables. The options table is omitted and ordinary options are exported separately as a transactional merge. Protected destination rows are never deleted, and source connection values never enter the transfer SQL. Serialized option URLs are replaced during this export. The later content URL replacement skips the options table and protected mail/scheduler tables, leaving their values untouched. Sender settings remain paired with the destination connection even when recipients change.

Rollback backups remain complete copies of the destination database, including its existing mail configuration. They stay in that environment's configured private backup directory. A normal transfer still replaces content, users, enquiries and other plugin data, so the existing confirmation and backup requirements continue to apply.

## Verification

```bash
python3 scripts/tests/smoke.py
./scripts/wp-local.sh --skip-plugins --skip-themes eval-file scripts/tests/db-sync-mail.php
./scripts/push-db-to-droplet.sh --dry-run
./scripts/pull-db-from-droplet.sh --dry-run
```

The SQL integration checks use temporary tables with synthetic SMTP/OAuth values. Both directions must preserve destination values and autoload flags, copy recipients, exclude source secrets, retain serialized Unicode/text values, and leave an unconfigured destination without source credentials. Read-only exports verify table exclusions. The test hashes the real options table before and after to check that site settings were not changed. It sends no email.

The smoke checks run the complete shell workflows with mocked network and database tools, verify filtered exports and protected URL-replacement exclusions, and check that mismatched prefixes and empty table lists fail before any export/import. Local SQL integration, smoke checks and both Local/droplet preflights passed on 2026-10-11. No full database replacement was performed.
