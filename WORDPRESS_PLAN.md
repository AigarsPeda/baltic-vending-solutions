# WordPress plugins, editing and content storage plan

Project requirements and implementation plan, 2026-10-04. This document defines how Baltic Vending Solutions will use language, Google, SEO and email plugins while keeping website content editable through WordPress's visual interfaces. The first Local implementation now follows this plan. See [local development](docs/local-development.md) for installed versions, editing locations and completed checks. Google and production email configuration remain pending.

## Required editing behaviour

Every visitor-facing heading, paragraph, specification, button label and destination, navigation item, FAQ, form label, feedback message, contact detail and image must have an editing location in WordPress admin. The owner must be able to add, change, reorder and remove content without editing PHP, JSON, CSS, JavaScript or HTML.

Use core Gutenberg blocks for ordinary page content. Theme code controls layout and behaviour; the WordPress database holds the authored content. Starter patterns can provide layouts, but a theme update must not replace saved content, recreate a deleted section or restore an image removed by the editor. Avoid content hidden in template strings, CSS pseudo-elements, theme assets or a separate JSON catalogue.

Images must be managed through Media Library and native Image, Gallery, Cover, Featured Image or logo controls. Routine media work must support upload, selection, removal from a page, permanent deletion and the native image-editing tools. Removing an image from a page and permanently deleting its Media Library record are different actions.

## Planned plugin set

The supplied screenshot shows the intended plugin baseline, not an installation inventory for this new project. Its versions are reference information only. At installation, verify current stable versions, WordPress/PHP requirements and compatibility together, then record the installed versions here.

| Plugin | Responsibility | Project configuration |
| --- | --- | --- |
| Polylang | Language assignments, translation relationships and language navigation | Configure the agreed languages; LV and EN remain the proposed pair. Keep separate editable translations and test equivalent-page switching. |
| Rank Math SEO | Search titles/descriptions, canonical URLs, XML sitemaps and suitable structured data | Edit SEO information per language; verify sitemaps, canonical URLs and translated routes. Keep its Analytics/tag insertion disabled when Site Kit owns that integration. |
| Site Kit by Google | Connect the production site to the owner's Google services | Start with Search Console. Enable Analytics only after the property and consent integration are configured. PageSpeed Insights may be connected; ads and Tag Manager are outside the initial plan. |
| WP Mail SMTP | Deliver WordPress email through the selected SMTP or API provider | Configure the business sender and mailer on production. Route quote notifications and WordPress transactional emails through `wp_mail()`. |
| WP Consent API | Share consent choices with compatible plugins | Active locally. The first-party BVS Cookie banner provides editable LV/EN controls; the API alone does not display a banner. |

Use Rank Math's current native Polylang compatibility rather than copying obsolete compatibility snippets from another project. Its documentation states native support was added in 1.0.276. Confirm the actual installed combination with translated-page checks. [Rank Math compatibility guidance](https://rankmath.com/kb/polylang-compatibility/).

## Language support and theme architecture

The baseline is a custom hybrid theme with core Gutenberg content editing, standard WordPress menus and named block-widget areas for shared header/footer content. This keeps the initial plan compatible with the screenshot's free Polylang approach without assuming a Pro licence.

Use the following editing locations:

- Pages > Edit for homepage sections, model pages, specifications, purchase/rental information, wrapping examples, FAQs, contact copy and policy pages.
- Appearance > Menus for language-specific navigation, quote links and the Polylang menu language switcher.
- Appearance > Widgets for named header/footer content areas per language, using core Heading, Paragraph, Image and Buttons blocks.
- WordPress's native logo/site-identity controls for the logo and site icon.
- Polylang's language assignments and translation links for the corresponding pages and menus.

The theme selects the correct per-language shared content area; its code must not contain the corresponding copy. Create separate areas for each configured language so edits can remain independent. All areas must be exposed in the normal admin interface with clear names. Test native widget-editor previews and published output together before adopting this structure.

A full block theme using Appearance > Editor is an alternative if a Polylang Pro licence is selected. Polylang documents its Site Editor language switcher and translation of navigation, template parts and patterns as Pro features. It also states that templates themselves are not translated. Keep language-specific copy in pages and translatable parts/patterns, not language-neutral templates. No licence purchase is implied by this plan. [Polylang Site Editor guide](https://polylang.pro/documentation/support/guides/site-editor/), [language switcher guide](https://polylang.pro/documentation/support/guides/the-language-switcher/).

WordPress provides the Site Editor with block themes. Do not promise that interface for the baseline hybrid theme; its editing surfaces are the Page editor, Menus, Widgets and native identity settings. [WordPress Site Editor](https://wordpress.org/documentation/article/site-editor/).

Start with ordinary Pages for the two models and their translations. A dedicated equipment post type can be added if the catalogue grows, but it must retain the block editor and standard Featured Image controls. Compare the models with editable native blocks; keep a record of where repeated specifications must be updated. Do not build an uneditable generated catalogue to avoid a small amount of editorial duplication.

## Google integration

Site Kit's setup requires a publicly accessible production environment. Prepare the plugin locally if needed, but complete Google authorisation on the final production domain rather than the Local `.local` address. Use the business owner's accounts and properties, with appropriate administrator access. [Site Kit installation requirements](https://sitekit.withgoogle.com/documentation/getting-started/install/).

Keep one owner for Analytics tag insertion. In this plan, Site Kit owns it; Rank Math and the theme must not add another GA4 tag. If Tag Manager is introduced later, revise the ownership plan first. Google service access and Gmail sending authorisation are separate connections.

The Local implementation uses a first-party BVS Cookie banner block plus WP Consent API. Copy, choices and policy links are translated and visually editable in native Widgets. The first visit offers equal reject/allow controls; saved choices can be reopened from the footer. Site Kit remains the only Analytics tag owner, with server-side opt-in gating. Leave Analytics disconnected until the final production property, consent integration and caching are configured and tested. Verify network requests before a choice and after acceptance, refusal and revocation; Consent Mode alone does not provide a visible banner or eliminate all requests. See [implementation details](docs/cookie-consent.md) and [Site Kit consent documentation](https://sitekit.withgoogle.com/documentation/using-site-kit/consent-mode/).

Search Console and site verification can be prepared independently of enabling visitor Analytics tracking. Do not introduce advertising integrations as part of the initial Google setup.

## Email and quote enquiries

WP Mail SMTP is the mail transport; it does not supply the quote form or store enquiries. Plan a small first-party site-functionality plugin for a Quote Form block, submission handling and private enquiry records. Keep this behaviour outside the theme so changing the theme does not remove the records or their admin access.

The Quote Form block must provide a complete Gutenberg editing experience. Field labels, placeholders, required-field descriptions, submit text, privacy links and success/error messages must be editable visually. No shortcode-only or Custom HTML workflow is acceptable. Store these settings as block content/attributes in WordPress, and keep per-language form content independently editable. Extra custom blocks are justified only where core blocks lack the required behaviour.

Store submissions as private WordPress records with appropriate access controls. Save the enquiry first, then notify the configured recipient through `wp_mail()`. A mail transport failure must not discard a saved enquiry or tell the visitor that saving failed. Record the notification outcome separately so an administrator can identify unsent notifications. Keep validation, spam controls and sensitive records out of public output.

Make the enquiry recipient editable in WordPress settings. Keep it distinct from the public contact address and authenticated From address. Use the selected business sender as From and the visitor's validated address as Reply-To. Account password resets must continue to use the user's own address.

Choose the actual provider after the business mailbox is known. If it is Gmail or Google Workspace, WP Mail SMTP supports a manual Google API setup; its simplified one-click setup requires Pro. Do not assume a paid licence is needed for the manual route or that a Site Kit connection authorises email sending. [WP Mail SMTP Gmail setup](https://wpmailsmtp.com/docs/how-to-set-up-the-gmail-mailer-in-wp-mail-smtp/).

Use Local's mail capture or an intercepted transport for development. Configure and test the real production connection after migration. Keep credentials, OAuth tokens and real enquiries out of Git. During a full database transfer, review mailer settings and reauthorise production connections as needed rather than treating a copied Local database as verified delivery.

## WordPress storage

The WordPress table prefix is configurable; the names below describe record types rather than a required `wp_` prefix.

| Item | Authoritative location | Editing route |
| --- | --- | --- |
| Page copy, specifications and ordinary blocks | WordPress post records and their block content | Pages > Edit, visual block editor |
| Block form settings | Saved block attributes/content; private submission data in WordPress records/meta | Quote Form block controls and restricted enquiry admin |
| Navigation | Core menu records and metadata in the baseline; `wp_navigation` records if the block-theme route is chosen | Appearance > Menus or Site Editor Navigation |
| Baseline shared header/footer blocks | WordPress widget/options storage | Appearance > Widgets, language-specific areas |
| Block-theme overrides, if selected | `wp_template`, `wp_template_part`, `wp_block` and `wp_global_styles` records as appropriate | Site Editor and patterns |
| Logo, gallery and content photographs | Attachment records/meta plus files in WordPress's configured uploads directory | Media Library and native image/logo controls |
| SEO, language and plugin settings | WordPress records, options and plugin-managed tables where the plugin requires them | Relevant WordPress/plugin settings screens |
| Theme defaults and presentation code | `wp-content/themes/baltic-vending-solutions/` | Repository development; no routine content editing here |
| Site functionality | `wp-content/plugins/` or a justified `mu-plugins/` integration | Repository development; authored content stays in WordPress |

WordPress normally stores uploaded files in `wp-content/uploads`, optionally using year/month subdirectories. Resolve locations through WordPress's configured upload APIs, such as `wp_upload_dir()`, instead of hardcoding local filesystem paths into website content. Retain normal core file organisation for this project. [WordPress upload locations](https://developer.wordpress.org/reference/functions/wp_upload_dir/).

Use WordPress media APIs for imports so attachments, metadata and generated image sizes exist. Use attachment references and WordPress image rendering where appropriate. Do not copy a photograph into a theme folder and then pretend it is managed by Media Library. [WordPress attachments](https://developer.wordpress.org/reference/functions/wp_insert_attachment/).

## Media lifecycle

Upload images through Media Library or the native block upload control. Editors must be able to change alt text, title, caption and description; crop, rotate or scale using available core image tools; select another image; and remove it from the authored section. Where WordPress does not provide an in-place file-replacement operation, upload the replacement and select it in the block. An extra replacement plugin is not required for that workflow. [WordPress Media Library](https://wordpress.org/documentation/article/media-library-screen/).

Keep shared media references deliberate. Deleting a shared attachment may affect multiple pages or languages; removing it from one block should not automatically delete the attachment. A missing or deliberately removed attachment must not cause the theme to restore a repository fallback photo. Preserve images still referenced elsewhere when cleaning up a page or translation.

## Migration and release requirements

A complete deployment includes the database, uploads, theme and plugin files. Database-only migration cannot deliver the image files; uploads-only copying does not create Media Library records. After moving Local to DigitalOcean, verify attachments, generated sizes, translated page/menu relationships, SEO settings and editor changes as well as visible page output.

The current upload scripts assume the standard `wp-content/uploads` path. Keep that layout. If a future requirement changes WordPress's upload directory, update and validate the transfer scripts against the configured locations before migration.

Routine theme updates must preserve saved blocks, widgets, menus, attachments and plugin settings. Selective future content sync must also include the relevant shared areas and attachment relationships, not only page body content. Review remote editor changes before overwriting selected content. A full database push replaces all records, including users and private enquiries, and remains a separate authorised operation.

## Acceptance checks before launch

1. Edit headings, equipment facts, buttons, links, FAQs, contacts, shared header/footer content and form messages from the normal visual interfaces; save and verify the published result.
2. Add, reorder and remove a section, then deploy a theme update. Confirm the editor's changes survive and deleted content does not return.
3. Upload a new photo, select it, edit its metadata and crop it with core tools. Remove it from one page without deleting the attachment, then permanently delete an unused test attachment and confirm the expected files/record disappear.
4. Edit each configured language independently; verify translated URLs, navigation, footer content, language switching and shared-media behaviour.
5. Verify translated SEO titles/descriptions, canonical and language links, and sitemap entries with the installed Polylang/Rank Math combination.
6. Save a quote enquiry and confirm its private record. Simulate notification failure and verify the saved record and correct visitor feedback. Test actual inbox delivery and password reset routing on the configured production mailer.
7. Verify one Analytics integration, consent choices and the associated network behaviour. Ensure Local testing has not populated the production Analytics property.
8. Migrate to the test/production destination and repeat the editing/media checks there. Record exact WordPress/plugin versions, chosen Polylang edition, mail provider and Google properties in HANDOFF.
