# Cookie banner and Analytics consent

Implemented on Local, 2026-10-04. Google services remain disconnected.

## Appearance and editing

The banner follows the owner's JanogaGo reference: serif heading, readable copy on the left and two equally styled outlined buttons on the right. Its dark teal background `#16282d`, white text and teal top border match Baltic Vending Solutions and distinguish it from the pale grey page, following the owner's subsequent visibility request. Mobile places the buttons beneath the copy. Playfair Display and Manrope are self-hosted inside the site plugin with their Open Font Licences; these fonts apply only to the banner.

Edit **Appearance > Widgets > Cookie banner — LV / EN**. The **BVS Cookie banner** block exposes the title, description, action labels, footer settings label and privacy-link label on the visual canvas. The privacy URL and storage-error message are in Block settings. Edited copy is saved in WordPress's native `widget_block` option, with widget placement in `sidebars_widgets`. As with other native blocks, WordPress may omit attributes that equal the registered defaults; the editor and renderer apply those defaults consistently. Privacy text remains native page blocks. Code sync does not replace these edits.

`scripts/local/setup-cookies.php` adds missing language widgets and a cookie explanation to the existing privacy drafts. It preserves existing banner copy. New installations run it through the one-time seed; existing installations can run it separately after syncing the theme and plugin and activating WP Consent API.

## Visitor behaviour

- First visit offers **Reject analytics** and **Allow analytics**, without a close link. Analytics stays denied until explicit acceptance. Before a confirmed choice, public visits create no cookies or consent storage, including WP Consent API defaults or Polylang’s language cookie. Language links work through their URLs. The optional public WordPress emoji detector is disabled to avoid its sessionStorage cache; native browser emoji rendering remains available.
- A versioned `bvs_consent` cookie remembers the choice for up to 180 days, shared across page languages. It uses `SameSite=Lax`, the whole site path and `Secure` on HTTPS.
- **Cookie settings** in the footer or **Change cookie settings** on the privacy page reopens the banner. A saved choice can be changed or closed without changing it, including with Escape. Keyboard focus returns to the control used to open it.
- Invalid, expired or incompatible choices show the first-visit banner again. Failed cookie storage shows an editable error rather than claiming the choice was saved.
- Changes propagate to other open tabs. Rejection/withdrawal clears existing GA cookies. Marketing consent remains denied.

The banner does not require a Google connection to appear. Browsing and necessary cookies remain available when Analytics is rejected.

The [detailed privacy pages](privacy-page.md) describe the storage used after confirmation. `bvs_consent` lasts up to 180 days, WP Consent API's `wp_consent_*` cookies last 30 days and refresh during later visits with a valid choice, and Polylang's `pll_language` cookie lasts up to one year. `bvs_consent_changed` is briefly set and immediately removed from local storage to notify other open tabs.

## Google integration

WP Consent API 2.1.0 is installed and active. Its cookie-persisting setter is used only after a confirmed choice exists; unknown choices use opt-in’s default denial and stale API permissions are cleared. The BVS plugin declares opt-in and shares functional, preferences, statistics and marketing choices through that API. The API provides an integration layer; it does not display a banner by itself. [WP Consent API documentation](https://wordpress.org/plugins/wp-consent-api/).

Site Kit owns Analytics tag insertion. A BVS server filter blocks its Analytics tag without a valid acceptance, for logged-in users, and in every non-production environment. Ads, AdSense and Tag Manager tags are blocked for this initial scope. No custom GA tag is inserted, and Rank Math Analytics remains unused.

When production has Site Kit's Analytics snippet configured, a saved choice reloads the page so server-gated tags can start or unload. If Site Kit Consent Mode is enabled, use its integration with WP Consent API; avoid introducing a second tag owner or conflicting consent configuration. [Site Kit consent documentation](https://sitekit.withgoogle.com/documentation/using-site-kit/consent-mode/).

## Before production Analytics

1. Connect the owner's production Google property through Site Kit and confirm the final domain and environment type.
2. Configure page caching to bypass requests with `bvs_consent` and ensure accepted responses never enter a shared anonymous cache. Check CDN and server caches as well as WordPress plugins. Purge cached pages after consent/tag configuration changes.
3. Test a clean browser before a choice, rejection, acceptance, withdrawal, expiry and multiple tabs. Verify no Analytics requests before acceptance or after withdrawal. These checks need the real production configuration; Local checks do not verify Google delivery.
4. Complete the business's privacy notice and cookie description using the actual production services. Current privacy pages explicitly remain development drafts.

## Checks

```bash
./scripts/wp-local.sh eval-file scripts/tests/consent.php
NODE_PATH=/path/to/playwright/node_modules node scripts/tests/cookies.cjs
```

The PHP checks validate consent data, Local tag blocking, API integration and translated widget storage without changing saved content. Browser checks cover both languages, 1440/375/320 pixel layouts, first-visit controls, zero cookies/storage before confirmation, saved choices, keyboard reopening/closing, withdrawal, invalid/expired cookies and cross-tab updates. They also assert that Local makes no Google Analytics or Tag Manager requests after either choice. A heading edit was also saved through the visual Widget editor, checked on the frontend and restored. Screenshots are saved in ignored `output/`.
