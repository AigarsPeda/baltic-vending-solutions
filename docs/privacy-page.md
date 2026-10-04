# Privacy pages

Implemented locally on 4 October 2026.

The owner requested a privacy page similar to JanogaGo's and explicitly asked to keep unknown business details marked pending. The drafts are available at [Privātums un sīkdatnes](http://baltic-vending-solutions.local/privatums/) and [Privacy & cookies](http://baltic-vending-solutions.local/en/privacy/).

## Content and editing

Edit both notices under **Pages** in the native visual block editor. They contain eight sections covering the data controller, enquiries, website security, cookie choices, Analytics, data access, retention and individual rights. Headings, paragraphs, lists and the cookie-settings button are core WordPress blocks stored in the page records. LV page 14 is selected under WordPress's privacy-policy setting; EN page 10 remains its linked Polylang translation.

The settings button opens the same language's existing cookie banner. After a saved choice, Escape closes it and returns focus to the button. Without JavaScript, the link points to the cookie explanation.

The page describes this installation's actual behaviour. Public visits persist no cookies or browser storage before confirmation. Analytics is disconnected, Local email is intercepted, and enquiry records are private WordPress records available to administrators. The submission rate-limit window is five minutes; the enquiry record does not store the IP address. Planned DigitalOcean hosting and the future mail provider are described as pending.

## Details still pending

- Legal business name, registration number and registered address.
- Privacy contact email.
- Legal grounds for handling enquiries and security data.
- Retention periods and deletion procedures for enquiries, correspondence, logs and backups. There is currently no automatic enquiry-deletion schedule.
- Final hosting location, mail provider, other processors and any international transfers.
- Actual Google services, data collected and retention configuration before production Analytics is enabled.

These placeholders are visible in both languages. Replace them after the owner supplies the facts, before collecting real customer information. Do not copy JanogaGo's company details or Google configuration.

## Implementation

`scripts/local/privacy-content.php` provides native starter blocks. `scripts/local/setup-privacy.php` upgrades existing privacy drafts once, preserving page IDs, routes and translations. `_bvs_privacy_version` prevents later runs from replacing saved edits. Setup assigns the LV policy when no page is selected or when the selection is WordPress's unchanged starter draft; it preserves a custom selection. New installations receive this content through the normal seed. Theme/plugin syncs do not rewrite page content.

The layout uses an 840px reading column, the established Plex typography, clear section spacing. The highlighted development notice was removed from both languages at the owner's request. Pending legal details remain marked in their relevant sections. It follows the local JanogaGo privacy-page structure without importing the other business's facts.

## Checks and references

`scripts/tests/privacy.cjs` checks both languages at 1440, 768, 375 and 320 pixels, translated links, pending markers, cookie controls, focus return, no initial cookies, no Google requests and reading without JavaScript. Screenshots are saved in ignored `output/`.

The rights wording was checked against the [Latvian Data State Inspectorate's data-subject guidance](https://www.dvi.gov.lv/en/rights-data-subject). The [official GDPR text](https://eur-lex.europa.eu/legal-content/EN/TXT/?uri=CELEX:32016R0679) is the reference for completing the notice after the controller and processing details are confirmed. These drafts do not claim legal approval or production compliance.
