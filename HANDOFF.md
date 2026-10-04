# Baltic Vending Solutions project handoff

## Goal

Build a WordPress website offering vending equipment for sale or rent, with custom branding and wrapping. Develop in Local App and later transfer the complete website to DigitalOcean. The initial phase establishes project documentation and adapted scripts with blank configuration.

## Current progress

As of 2026-10-04, the project contains a client and audience profile, equipment research notes, development and deployment instructions, six executable scripts and a configuration template. The client profile, this handoff and README have been translated into English. `STYLE_GUIDE.md` now defines the working B2B design direction. `WORDPRESS_PLAN.md` adds the plugin, native editing, media and storage requirements. The two existing supporting documents under `docs/` remain in Latvian.

WordPress, the theme, the Local site, the domain and the droplet have not been created. Real configuration remains blank. No WordPress or DigitalOcean deployment has taken place.

The initial commit `08b85df` was pushed to `main` at `https://github.com/AigarsPeda/baltic-vending-solutions.git`. The English translations, style guide and WordPress plan are subsequent working-tree changes. Check `git status`, `git log -1` and `git remote -v` before committing or publishing further updates.

The owner requires English communication and English project documents. This does not change the proposed public website languages; those still need agreement.

## Files and locations

| Item | Location |
| --- | --- |
| Project | `/Users/aigarspeda/Desktop/Baltic-Vending-Solutions` |
| Client profile | `CLIENT_PROFILE.md` |
| Style guide | `STYLE_GUIDE.md` |
| Plugins, editing and storage | `WORDPRESS_PLAN.md` |
| Equipment research | `docs/equipment-research.md` |
| Development and deployment | `docs/development-and-deployment.md` |
| Planned theme source | `theme/baltic-vending-solutions/`, not yet created |
| Configuration template | `scripts/baltic-vending-solutions.env.example` |
| Private configuration | `scripts/baltic-vending-solutions.env`, ignored by Git |
| Checks | `scripts/tests/smoke.py` |

The JanogaGo handoff provided a workflow example. Its server address, domain, SSH details, WordPress record IDs, customer achievements and food catalogue do not belong to this project. Scripts must not target JanogaGo by default. The original Pages specifications remain in `/Users/aigarspeda/Desktop/JanogaGo-doc/automati/`; the equipment notes provide a portable summary.

## Content and design basis

Read `CLIENT_PROFILE.md`, `STYLE_GUIDE.md` and `WORDPRESS_PLAN.md`. The primary audiences are businesses selling their own products and entrepreneurs starting with one machine. These come from the owner's brief and research hypotheses, rather than validated customer interviews.

The offer is equipment purchase or rental plus custom wrapping. Do not carry over JanogaGo's managed catering promises. The legal business, territory, prices, logo, contacts, rental terms and service responsibilities remain to be established. Latvian as the primary website language and a possible English version are recommendations, not confirmed requirements.

The owner has clarified that both local Pages specifications constitute our proposal. They are the primary basis for product content. Our Compact configuration includes Vision AI in the proposal; the manufacturer lists it as an option. The manufacturer's general range does not override our offer. Smart Fridge Series needs a precise model reference. Do not publish estimated dimensions or guaranteed profitability. Label wrapping demonstrations as visualisations until real customer examples are available.

The working visual direction uses light backgrounds, charcoal text, a deep petrol accent, IBM Plex Sans typography, full machine images and readable comparison layouts. The style guide specifies implementation rules and accessible colour pairings. It is a design baseline, not an existing approved brand identity.

## Planned plugins and editing architecture

Use Polylang for languages, Rank Math SEO for search metadata/sitemaps, Site Kit for Google service connections and WP Mail SMTP for mail delivery. The screenshot supplied by the owner is a plugin reference, not evidence that these are installed on this project. Verify versions and compatibility at installation. Complete Site Kit authorisation on the public production domain. Keep a single Analytics tag owner and leave tracking disabled until consent integration is configured and verified.

The baseline is a hybrid theme with native Gutenberg pages, WordPress menus and per-language block-widget areas for shared header/footer content. Polylang Pro with a block theme and the Site Editor is an optional route; do not assume free Polylang provides its documented Pro translation features. All authored copy and images must remain visually editable in either route. Plan a Gutenberg Quote Form block and private submission handling in a small site-functionality plugin; WP Mail SMTP provides transport, not the form. See `WORDPRESS_PLAN.md` for storage mappings and acceptance checks.

## WordPress development principles

- Keep all visitor-facing content, including shared header/footer copy, form labels and feedback, editable through Gutenberg and native WordPress visual controls. No routine edits may require code, HTML or a shortcode-only workflow.
- Store authored content in WordPress records/options and images as Media Library attachments in the configured uploads directory. Support native upload, metadata/image editing, selection, page removal and permanent deletion.
- Initial content setup must preserve later editor changes and deliberately deleted content.
- Store content photography in uploads with Media Library records. Keep theme code and necessary interface assets in the repository.
- Apply the style guide through shared theme tokens and editor styles. Editors should see the same typography and content hierarchy as visitors.
- Verify enquiry saving, validation feedback and email delivery. Make the recipient editable in WordPress.

## Script behaviour

`sync-code-to-local.sh` copies the theme into Local. `sync-code-to-droplet.sh` copies the theme to the server and archives its previous version. `sync-plugins-to-droplet.sh` copies plugins and mu-plugins after a server backup. `sync-uploads-to-droplet.sh` copies files without deletion. `push-db-to-droplet.sh` and `pull-db-from-droplet.sh` replace the destination's complete database after a backup. All support `--help` and `--dry-run`; database dry-run checks availability.

Four server/database scripts were adapted from JanogaGo. Previous project settings were removed; shared configuration validation, database preflight and Local socket parameters were added. Theme and backup locations come from this project's configuration. Git ignores the real `.env`, SQL dumps and keys.

The isolated script checks passed during initial setup. They cover Bash syntax, help, blank configuration, the default configuration filename, dry-run without changes, invalid paths and backup ordering. Server checks use mocked SSH and WP-CLI. Installed rsync was tested against a temporary Local structure, including copying, obsolete theme-file deletion and an SSH key path containing a space.

The scripts have not been tested against this project's real Local site or droplet. They do not install a server. Complete migration needs WordPress and server configuration, the theme, plugins, uploads and the database. Git push is not website deployment. The deployment document records the sequence.

## What worked

Our proposal's Pages tables supplied the initial product parameters. Boost's website added platform and manufacturer information. JanogaGo's general synchronisation scripts provided a reusable starting point. GitHub access and the first push were verified.

## Limitations and unsuccessful approaches

The first GitHub request could not resolve the hostname inside the restricted network environment. An authorised network request succeeded. Mentioning Boost does not establish distributor status, package inclusions or local service coverage. JanogaGo's selective homepage and food helpers need adaptation to the new data model before reuse.

The style guide's palette and font are proposed choices because no Baltic Vending Solutions logo or established identity has been supplied. No website layout has yet been implemented or visually tested.

## Next steps

1. Review the English documents and working style guide; record supplied brand assets as they become available.
2. Confirm the legal name, contacts, website languages, territory and purchase/rental/service terms.
3. Confirm the precise equipment models, proposed configuration and photography rights.
4. Create a separate Local site, fill its runtime fields, install the planned plugin baseline and verify the native editing/language architecture before building the page layouts.
5. Build the theme, editable product content and core pages using the client profile and style guide.
6. Run the editing, media lifecycle, multilingual SEO and enquiry acceptance checks in `WORDPRESS_PLAN.md`, alongside desktop/mobile and keyboard checks.
7. Prepare this project's droplet, domain, HTTPS, SMTP and backup plan, then configure the remote fields.
8. Review dry-run output, perform an authorised first complete migration and record the actual server state and recovery backup paths here.
