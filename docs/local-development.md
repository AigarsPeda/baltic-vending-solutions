# Local development and editing

First local implementation, 2026-10-04.

## Running installation

Start **Baltic Vending Solutions** in Local App. Open [the Latvian homepage](http://baltic-vending-solutions.local/), [the English homepage](http://baltic-vending-solutions.local/en/) or [WordPress admin](http://baltic-vending-solutions.local/wp-admin/). Use the account created during Local setup. No password is stored in this repository.

| Component | Current installation |
| --- | --- |
| WordPress | 7.1.2 |
| PHP | 8.2.29 |
| nginx | 1.26.1 |
| MySQL | 8.4.0 |
| Polylang, free | 3.8.10 |
| Rank Math SEO | 1.0.279 |
| Site Kit by Google | 1.188.0 |
| WP Mail SMTP | 4.10.0 |
| WP Consent API | 2.1.0 |
| BVS theme and site plugin | 0.1.0 |

WordPress lives at `/Users/aigarspeda/Local Sites/baltic-vending-solutions/app/public`. Local runtime ID is `8Xh86i1z8`. The ignored environment file records the PHP executable, runtime INI, WP-CLI PHAR and database socket. Local may change its runtime files after an environment change; update those paths when necessary.

## Editing locations

| Content | Native admin route |
| --- | --- |
| Homepage sections, equipment facts, buttons and FAQs | Pages > Edit, visual block editor |
| Privacy notice, pending details and cookie-settings link | Pages > Privātums un sīkdatnes / Privacy & cookies, visual block editor |
| Equivalent language content | Edit the linked translation under Languages in the page editor |
| Navigation and language switcher | Appearance > Menus, Primary LV / Primary EN |
| Contact menu link | Native Kontakti / Contact item, class `bvs-contact-menu`; the theme targets the current page's `quote` or `design-quote` section, with a translated homepage fallback |
| Public phone/email placeholders | Pages > Homepage, contact introduction, native Group/Paragraph blocks with class `bvs-contact-details`; currently `+371 XX XXX XXX` and `info@example.com` |
| Header quote button | Appearance > Widgets, Header actions — LV / EN |
| Footer text and links | Appearance > Widgets, Footer content — LV / EN |
| Cookie banner text, labels and privacy link | Appearance > Widgets, Cookie banner — LV / EN; edit the BVS Cookie banner block |
| Logo and site identity | Appearance > Customize > Site identity |
| Photos and their metadata | Media > Library; native Image block controls in pages |
| Quote form labels and feedback | Select BVS quote form in the page editor; edit fields in the canvas or Block settings. The homepage Create design label and URL are in Design attachment settings |
| Notification recipient, menu and skip-link labels | Settings > BVS enquiries |
| Saved enquiries and notification outcome | Quote enquiries, administrators only |

The hybrid theme uses these editing interfaces rather than the full Site Editor. Polylang's corresponding Site Editor translation features require the optional Pro route described in WORDPRESS_PLAN.md.

The eight starter pages are two homepages, two Compact Cooler pages, two Smart Fridge pages and two privacy drafts. Their IDs in this installation are EN 7–10 and LV 11–14. Product URLs are `/kompaktais-ledusskapis/`, `/viedais-ledusskapis/`, `/en/compact-cooler/` and `/en/smart-fridge/`.

Polylang's native language settings use Latvia's flag for Latvian and the US flag for English, matching the owner's JanogaGo admin reference. Change these under **Languages > Languages**. The initial scripted setup omitted flag assignments, which caused `lv`/`en` text in the Pages list. `scripts/local/setup-language-flags.php` fills missing flags on existing Local installs while preserving later choices; new seeds include the assignments. This concerns native admin language icons; menu display is configured separately.

Each language's form labels and messages are separate saved block attributes. Do not duplicate a form and keep the same Form ID on the same page. Set a unique ID in the block's Form and feedback panel.

Product facts appear in both homepage comparisons and equipment detail pages. Update both locations and their translations when changing a specification. The source proposal remains authoritative.

## Updating code

```bash
./scripts/sync-code-to-local.sh --dry-run
./scripts/sync-code-to-local.sh
./scripts/sync-site-plugin-to-local.sh --dry-run
./scripts/sync-site-plugin-to-local.sh
```

Only the project's theme or first-party plugin directory is synchronised. Saved WordPress content is unaffected. Edit fonts, colour defaults and layout in the theme; edit ordinary text and images through WordPress.

## Creating another Local installation

1. Create a new WordPress site in Local, using a `.local` domain and a Local environment.
2. Copy the blank environment example and fill in that site's paths and URL.
3. Run both code sync scripts, then activate the theme and first-party plugin.
4. Install the five plugin dependencies and import the initial content once.

```bash
./scripts/wp-local.sh theme activate baltic-vending-solutions
./scripts/wp-local.sh plugin activate bvs-site
./scripts/wp-local.sh plugin install polylang seo-by-rank-math google-site-kit wp-mail-smtp wp-consent-api --activate
./scripts/wp-local.sh eval-file scripts/local/seed.php '/Users/aigarspeda/Desktop/JanogaGo-doc/automati' '/Users/aigarspeda/Desktop/Baltic-Vending-Solutions/output/media'
./scripts/wp-local.sh rewrite flush
```

The seed creates native records and imports the cleaned machine photos through WordPress media APIs. The second argument identifies the folder containing `compact-cooler-unbranded.png` and `smart-fridge-unbranded.png`. The current workspace copies are ignored by Git, so copy them separately or migrate database and uploads together when moving to another installation. See [image edits](image-edits.md) for their provenance and edit prompts. It requires that proposal folder as an argument, so the repository can be moved without hiding a filesystem path in theme code. Local installations with a different domain use their own WordPress URL for generated links.

`bvs_seed_version` prevents a second import. Do not delete that option to refresh content. Use the editor or migrate an existing database and uploads instead. Deleting a section or image is an editorial action that a code update must preserve.

For an existing seeded installation, sync the theme and plugin, activate WP Consent API, then run `./scripts/wp-local.sh eval-file scripts/local/setup-cookies.php` to add missing cookie widgets and the native privacy-page explanation. This setup preserves existing banner edits. See [cookie consent](cookie-consent.md) for behaviour and the production checklist.

Run `./scripts/wp-local.sh eval-file scripts/local/setup-contact-menu.php` to add the translated Contact link before the language switch without replacing menus or page content. Repeated runs reuse the existing item. On mobile, same-page links release the drawer's scroll lock immediately before native anchor navigation. `scripts/tests/contact-navigation.cjs` checks scrolling to each page's form, translated fallbacks and header widths without submitting enquiries.

Run `./scripts/wp-local.sh eval-file scripts/local/setup-contact-details.php` to add the owner-requested phone/email placeholders below both homepage contact introductions. Repeated runs preserve existing contact-detail blocks and later edits. Replace the placeholders through the native page editor when real contact details are supplied. The placeholder text does not create working telephone or email links.

Run `./scripts/wp-local.sh eval-file scripts/local/setup-privacy.php` once to expand existing privacy drafts into the detailed LV/EN notices. New seeds already include these pages. The setup keeps page IDs, routes and translation links, and skips pages marked `_bvs_privacy_version` to preserve later editorial changes. Edit future changes in WordPress. [Privacy-page notes](privacy-page.md) list the business details still pending at the owner's request.

## Enquiries and local email

The form validates required fields, allowed choices, a nonce and a honeypot, with a short per-IP submission limit. Its private records use WordPress posts/meta. Only administrators can read/manage them; they have no public REST endpoint.

The plugin saves the enquiry before calling `wp_mail()`. Notification states distinguish `local_captured`, `failed`, `not_configured` and `accepted_by_transport`. A transport acceptance does not prove inbox delivery. Failed email keeps the record and successful saved feedback.

In a Local environment with a `.local` address, the plugin intercepts all `wp_mail()` calls before SMTP delivery. Captured quote details are available in the private enquiry record. This interception also covers password-reset emails; it does not populate Local's Mailpit inbox. Use Local's admin access during development.

Google authorisation, SMTP/API credentials and real customer data are unnecessary for this version. Set the verified recipient and authenticated business sender on production, complete WP Mail SMTP configuration, and test actual delivery after migration.

## Verification completed

- Eight page routes and equivalent-page language switching.
- Layout reflow at 1440, 768, 375 and 320 pixels, with no horizontal page overflow.
- Mobile menu open/close, Escape and focus return.
- Gutenberg block validity; visual heading and quote-label edits saved and appeared on the frontend, then were restored.
- Native Media Library upload and image-edit controls; temporary media removed after checking.
- Private enquiry saving, local notification capture, invalid payloads and simulated email failure.
- No Analytics network requests during browser checks.
- Cookie choices remembered across languages, rejection/acceptance, reopening, withdrawal, cross-tab updates and expired/invalid preferences; banner layout checked at 1440/375/320 pixels.
- Transfer-script help, blank-variable guards, dry runs, backups and isolated rsync behaviour.

Run consent checks with `NODE_PATH=/path/to/playwright/node_modules node scripts/tests/cookies.cjs` and `./scripts/wp-local.sh eval-file scripts/tests/consent.php`.

Run `NODE_PATH=/path/to/playwright/node_modules node scripts/tests/privacy.cjs` for the translated privacy pages, four viewport widths, no-JavaScript reading and the inline cookie-settings control.

These checks cover the local draft. Full accessibility review, commercial/legal copy, production SMTP, Google consent integration on the final domain, multilingual SEO configuration and migration checks remain before launch.

### Rank Math score visibility

Rank Math was active but its first-run registration screen had not been completed or skipped. That screen prevented the plugin from loading its admin columns and editor controls. On 2026-10-04, its native **Skip Step** action was used with usage tracking off. No external account was connected. The native **SEO Details** column is now visible under Pages, and the **Rank Math** button at the top right of the block editor opens focus-keyword and analysis controls.

The eight authored pages currently have no focus keywords or saved SEO scores. Rank Math now calculates a live editor score, with the Latvian homepage showing 24/100 during verification; list scores can still show N/A until page analysis is saved. Choose each page’s intended focus keyword, review its title and description, then update the page to save analysis. Local remains deliberately blocked from indexing through `blog_public=0`; do not enable indexing to obtain a score. Full Rank Math site/business/schema setup and production multilingual SEO still need review before launch.

References: [missing meta box](https://rankmath.com/kb/why-rank-math-meta-box-is-not-showing/) and [N/A scores](https://rankmath.com/kb/seo-score-not-available/). The visibility fix is a native database setting and must travel with the site export.
