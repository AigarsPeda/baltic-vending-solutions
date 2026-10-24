# Homepage review, 24 October 2026

Scope: Latvian and English homepages, shared navigation and enquiry flow. This is a heuristic review and browser verification, not a user study or a measured conversion test.

## Evaluation basis

- [NN/g homepage principles](https://www.nngroup.com/articles/homepage-design-principles/): explain the offer early and provide clear, descriptive routes to the main tasks.
- [GOV.UK headings](https://design-system.service.gov.uk/styles/headings/): use meaningful headings with a consistent semantic hierarchy.
- [Home Office layout and typography](https://design.homeoffice.gov.uk/accessibility/page-structure/layout-typography): left-align reading content and preserve a logical order as the screen narrows.
- [NN/g audience-based navigation](https://www.nngroup.com/articles/audience-based-navigation/): overlapping audience labels can make choices unclear. Applied here to the two homepage starting points, which now describe visitor tasks.

## Findings and changes

| Area | Finding | Result |
| --- | --- | --- |
| First impression | The product type, purchase/rental offer and next steps are explicit. | Retained the approved hero and real product image. |
| Audience routes | The earlier role labels overlapped and provided no next step. | Retained the approved two routes for existing products and a first sales point, linking to equipment and enquiries. |
| Equipment comparison | A long shared note and unfamiliar software name slowed reading. | Short paragraphs with a limited reading width; Vendlive identified as a management dashboard and Vision AI as camera-based recognition. |
| Payments and management | Unequal visual weight and detached row labels complicated scanning. | Equal-width columns, aligned headings, explanations immediately below labels, and separate model/terms notes. |
| Purchase/rental | The original introduction repeated the options and mixed them with operating details. The later bullet list left excess space to the right. | Direct ownership/use distinction followed by three parallel planning topics, each with a heading and description. Columns stack on narrower screens. |
| Rental FAQ | “Not yet published” sounded like unfinished editorial content. | Explains that price, availability and duration are specified in the individual quote. |
| Enquiry | Optional fields made the first step longer. | Native expandable optional fields; required fields remain visible and labelled. |

## Verification

Both homepages have one H1 and a logical heading hierarchy. WordPress's installed block validator accepts all 137 blocks on each page, with no Custom HTML or freeform blocks. Local checks at 320, 375, 768 and 1440 pixels found no horizontal page/table overflow or broken in-page anchors. Desktop/mobile layouts were inspected visually. Keyboard disclosure, mobile navigation, form labels and the existing WordPress enquiry integration checks passed.

All authored page content, links, images and form labels remain editable through WordPress. Prices, service inclusions and real public contact details were not invented. The owner-requested contact placeholders remain. The droplet is still a staging preview with outbound email unconfigured; enquiries save in WordPress.

Release evidence and private before/after content snapshots are in ignored `output/homepage-release/`. Deployment transfers the two homepage bodies, existing menu order/classes, theme CSS and quote-block changes. It does not replace the server database. See `droplet-deployment.md` for the release backup and live verification.

## Full-page readability follow-up

Reviewed both homepages from hero through footer, retaining the approved content and visual direction. This pass changes presentation only; the page bodies and native WordPress blocks are unchanged.

- Limited full-width section introductions to 65ch, left aligned. FAQ answers use a 70ch maximum.
- Corrected Core block-gap interference: product and purchase-option descriptions sit 12px below their headings, payment copy 16px, and process numbers/headings/descriptions use consistent 12px gaps.
- Removed the extra 24px gap before each subsequent FAQ row; each row retains 20px vertical padding and its separator.
- Stacked the process and contact columns at 960px and below. At 820px, the former narrow contact form caused unequal label wrapping and left its introduction in a cramped column.
- Aligned form controls when paired labels wrap, and increased the contact reassurance sentence to 16px.
- Allowed desktop navigation to wrap when visitor text-spacing overrides need more room. This fixes the observed overflow at 1181–1280px with increased letter/word spacing.

Rationale: [NN/g proximity](https://www.nngroup.com/articles/gestalt-proximity/) supports keeping a heading close to its explanation and separating independent topics. [W3C text-spacing guidance](https://www.w3.org/WAI/WCAG21/Understanding/text-spacing.html) concerns accommodating visitor overrides, not prescribing default paragraph gaps.

Verification: both languages checked at 320, 820 and 1440px with no horizontal overflow or oversized text blocks. A temporary local-only stylesheet tested line-height 1.5, paragraph spacing 2em, letter spacing .12em and word spacing .16em at 320, 820, 1181, 1280 and 1440px in both languages. No page/header overflow remained; an expanded mobile FAQ remained legible. The temporary stylesheet hook was removed before deployment. This is focused verification, not a complete WCAG conformance audit.

Typography remains self-hosted Plex regular/semibold with swap. Existing heading, body, label and metadata roles remain. The typography scan reports only existing type-ramp documentation advisories: 20/24px are established lead/subheading roles; 34px is the narrow-phone H1; 13/17/19px are legacy caption/identity roles; 64–96/72px belong to the unused performance section. This pass adds no new type sizes or font assets. Evidence: `output/readability-review/`.
