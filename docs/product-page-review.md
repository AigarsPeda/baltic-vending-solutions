# Product-page readability review — 24 October 2026

Scope: Compact Cooler and Smart Fridge in Latvian and English. Keep the homepage's approved visual language and use native WordPress content throughout.

## Changes

- Narrowed specification tables to 1000px while keeping their left edge aligned with section headings. Desktop labels use 30% of the table, bringing values closer to the labels; rows have 16px vertical padding. Phone layouts retain the existing responsive cell widths.
- Matched section spacing to the homepage: 64px on desktop, 48px on tablet/mobile, with 40px mobile hero padding.
- Merged duplicate touchscreen rows, retaining the fuller description and Boost interface details where supplied.
- Clarified that Vision AI uses cameras and that Compact Cooler product/price changes happen in the Vendlive dashboard, consistent with the homepage's confirmed content.
- Shortened the specification qualification to one sentence and displayed it at 16px with a 75ch reading width. Corrected the Latvian Smart Fridge anti-fog wording.
- Enabled the existing editable optional-fields disclosure on the four quote blocks, matching the homepage. Name, email and products remain visible and required. No new plugin behavior or business claims.

Content, links, specifications, images and form labels remain in WordPress blocks. The one-time payload builder is `scripts/local/refine-products.py`; publishing uses `scripts/deploy/update-products.php`, which checks the exact four page bodies against the reviewed snapshot before changing anything. Later editorial changes belong in WordPress.

## Verification

- All four routes checked at 320, 820 and 1440px: one H1 each, no horizontal page overflow or oversized text/cells, aligned specification headings and tables.
- WordPress's installed JavaScript block validator accepts all 21 blocks on each page; no invalid blocks. The temporary local validation route was removed before deployment.
- Product hero quote link reaches the on-page form. Optional fields open from the keyboard; required fields remain name/email/products.
- Desktop English specifications and mobile Latvian specifications visually inspected. Existing contact breakpoint and form-control alignment are shared with the homepage.
- Source passes PHP syntax and whitespace checks. No submission-handling changes; no new live enquiries were sent.

Snapshots, exact deployment payloads, viewport measurements and screenshots are in ignored `output/product-review/`. Deployment changes only the four page bodies and the theme stylesheet; it does not import the database or change menus, settings, other pages or enquiries.
