# Baltic Vending Solutions style guide

Working design baseline, 2026-10-04. This guide turns the [client profile](CLIENT_PROFILE.md) into practical rules for the WordPress website. It covers visual design, product presentation and writing. The owner selected a generated text-only wordmark and plain B favicon during local development. The palette below provides the implementation baseline.

## Design purpose

The site should help a business owner decide whether an equipment discussion is worthwhile. Within the first screen, visitors should understand that we offer machines for purchase or rental and can wrap them in their brand. The next sections should help them compare equipment, understand their responsibilities and request a quote.

Use a practical industrial character: clear proportions, precise alignment, visible equipment and readable information. Product photography should provide most of the visual interest. Keep the page calm enough for a buyer to scan, share with a colleague and return to later.

The first release prioritises businesses with their own products and entrepreneurs starting with one machine. Write for both commercial decision-makers and the people checking the technical details. This is a WordPress marketing website; use native blocks, shared theme styles and modest JavaScript where needed.

## Colour palette

Use cool neutrals with deep petrol as the single brand accent. Charcoal anchors headings and the footer. Reserve semantic colours for feedback, not marketing decoration.

| Token | Value | Use |
| --- | --- | --- |
| `canvas` | `#F3F6F6` | Page background and neutral machine-image backgrounds |
| `surface` | `#FFFFFF` | Reading areas, forms and alternating sections |
| `ink` | `#16282D` | Headings, body copy, dark footer |
| `muted` | `#506166` | Supporting text, captions and helper copy |
| `accent` | `#00675F` | Primary buttons, text links and selected controls |
| `accent-hover` | `#00534D` | Hover/active primary action |
| `accent-soft` | `#E4F1EF` | Quiet selected backgrounds; use ink or accent text |
| `divider` | `#CBD5D6` | Decorative separators and section rules |
| `control-border` | `#7E9094` | Input boundaries and outlined interactive controls |
| `error` | `#A32727` | Validation errors paired with explanatory text |
| `success` | `#216744` | Saved-enquiry confirmation paired with explanatory text |

Use white text on solid accent buttons. Use ink text on white or canvas. Use accent links on light backgrounds with underlines in prose. On dark sections, use white headings and light supporting text; primary buttons should use a white background with accent text. Do not place the normal accent button directly on ink, where its boundary has little contrast.

`divider` is decorative. It must not be the only visible boundary of a form input or the only cue for a selected state. Use `control-border` for those boundaries and text or an icon as well as colour for status.

Keep the accent consistent across the page. Customer wraps may introduce their own colours within the machine image; the surrounding website interface stays in this palette. Change tokens centrally when a supplied identity requires a different accent.

Base colour checks use solid, opaque colours. Recheck any overlays or image backgrounds in the actual layout.

| Checked pairing | Contrast |
| --- | --- |
| ink on canvas | 14.05:1 |
| muted on canvas | 5.96:1 |
| white on accent | 6.76:1 |
| white on accent-hover | 8.96:1 |
| accent on accent-soft | 5.84:1 |
| control-border on canvas | 3.07:1 |
| error on canvas | 6.72:1 |
| success on canvas | 6.26:1 |

## Logo and favicon

Use the text-only Baltic Vending Solutions wordmark in dark ink on a light background. Preserve its two-line layout, proportions and transparent background. The favicon is a plain white typographic B on teal; do not use the earlier machine-shaped symbols. Both languages share the same identity. Manage the logo and Site Icon through native WordPress settings and Media Library, as described in [brand assets](docs/brand-assets.md).

## Typography

Use IBM Plex Sans for headings, body text and controls. Its technical character suits equipment specifications, and one family keeps the reading experience consistent. IBM publishes it as an open-source family under the Open Font License and documents extended Latin support. [Official IBM Plex source](https://github.com/IBM/plex).

Self-host the selected WOFF2 files with their licence. Start with weights 400 and 600, use `font-display: swap`, and retain a system sans-serif fallback. Check the actual font files for English and Latvian characters before shipping. Do not make a remote font service a requirement for rendering the page.

The cookie banner follows the owner’s JanogaGo reference layout, with the site’s dark ink `#16282d` background and white text, a 28px Playfair Display heading and Manrope body/control text. Its fonts are self-hosted and scoped to this component. Use a teal top border and subtle upward shadow to separate the banner from the page. Use square outlined reject/allow buttons with equal emphasis. Show only these choices on first visit; offer close without changing when reopening an existing choice. On mobile, place the actions below the copy, retaining readable labels and a scrollable banner on short screens.

| Role | Small screens | Desktop | Weight and line height |
| --- | --- | --- | --- |
| Main heading | 36px | 56px | 600, 1.12 |
| Section heading | 28px | 40px | 600, 1.2 |
| Product/section subheading | 22px | 26px | 600, 1.3 |
| Lead paragraph | 18px | 20px | 400, 1.5 |
| Body | 16px | 18px | 400, 1.6 |
| Labels, specifications, buttons | 16px | 16px | 400 or 600, 1.4 |
| Captions and secondary metadata | 14px | 14px | 400, 1.5 |

Use fluid heading sizes between these endpoints. Keep prose to about 60 to 68 characters per line. Use sentence case and left alignment for headings and explanatory text. Main headings should express one statement without a period; rewrite two-sentence headings as a single statement. Product names keep their official capitalisation. Do not add small marketing labels above the main headings or repeat manufacturer labels above model names. The owner removed these from the first draft. Useful numbered process steps may remain.

Use tabular numerals in specification comparisons. Keep units attached to values, such as `13.3 in` and `+1 to +8 °C`. Mark dimensions consistently as height, width and depth when confirmed. Do not publish estimated measurements to fill an empty row.

## Layout and spacing

Use a maximum content width of 1200px, with 24px side gutters on small screens and 40px on larger screens. At very narrow widths, gutters may reduce to 16px. Use a 12-column desktop grid and a single reading column on mobile. A 24px grid gap is the default.

Base spacing on 4px increments. Use 8px between a label and its field, 16px within related content, 24px between components, and 32 to 48px between larger groups. Section padding is normally 80px on desktop and 48px on mobile. Adjust section spacing for content rather than forcing every section to the same height.

Keep the header about 72px high on desktop and 64px on mobile. The desktop navigation fits one line. Collapse it before labels become cramped. Use concise navigation labels such as Equipment, Purchase & rental, Custom branding and Contact, with "Request a quote" as the primary action.

Use a split hero on desktop: left-aligned copy and actions alongside a full machine image. Give the machine enough vertical room to show its proportions. The owner requested a taller homepage hero: at desktop widths of 1000px and above, use a 720px minimum section height, 80px vertical padding and a 560px image area, with the two columns vertically centred. Keep the offer and primary action visible early. Do not force a viewport-height section; mobile and product-detail heroes retain their natural height. On mobile, show the offer and action before the machine image.

Use straight-edged image panels and comparison sections. Buttons and inputs use a consistent 4px radius. Cards are for distinct products or choices, not every paragraph. Group explanatory content with spacing and thin rules. Use shadows only for floating UI such as an open navigation panel.

Keep the footer compact. Group the company name and description with a 12px gap; align quote, privacy and cookie-settings controls together on the right on desktop. The owner removed the local-development notice from both translated footer widgets. Stack the identity and utility controls with left alignment on mobile, allowing links to wrap naturally. Override default WordPress block margins within the footer so they do not add unintended gaps.

## Homepage sequence

1. Introduce equipment for purchase or rental and custom branding. Offer "Request a quote" and the secondary action "View equipment".
2. Compare the two proposed machines using the same information order and image treatment.
3. Explain purchase and rental as commercial choices, including only agreed terms.
4. Show how wrapping adapts the equipment to a customer's identity.
5. Explain the enquiry-to-installation process and identify responsibilities at each step.
6. Answer the common questions that block a buying decision.
7. Present the enquiry form and verified contact information.

Add genuine project examples or customer references when available. An empty logo wall or invented statistics should not occupy a section. This sequence is a starting layout; product information and confirmed commercial terms determine its final length.

## Equipment and comparison

Show both machines at a consistent image scale and angle, with their complete bodies visible. Do not stretch an image or crop the payment screen, doors or base. Show products stocked inside only where the image accurately represents a suitable configuration.

The homepage comparison introduces shared suitability once, then shows each machine's photo, model name, short distinguishing features, key proposal facts and a model-specific link to the detailed page. Use the same order for both machines. Save longer product explanations for the detail pages. With two proposed models, a two-column comparison is sufficient; do not add a third placeholder product.

Our local Pages documents define the proposed offer. For Compact Cooler, retain the proposal's 13.3-inch screen and Vision AI configuration. For Smart Fridge, retain its stated features without borrowing the Classic model's 24-inch display or approximate dimensions from other projects. Supporting manufacturer information can explain the platform, but must not silently replace our proposed configuration.

On detailed pages, separate equipment specifications, proposed package inclusions and optional services. Clarify what is included in purchase or rental once terms are agreed. Where a price is unavailable, use "Request a quote" rather than a fabricated starting price or zero amount.

Use a semantic comparison table on desktop. On mobile, group readable values under each parameter with both model names visible, or use model sections that repeat the same labels. Keep all material facts available. Avoid a wide table that forces the whole page to scroll horizontally.

## Photography and custom wrapping

Prioritise authorised equipment photos, original customer projects and accurate renders. Neutral product views establish the machine; location photography explains its footprint and use. Keep lighting and background treatment consistent across the model comparison.

Show custom wrapping through a neutral and branded view of the same model at the same angle. Explain the customer's artwork input, design preparation, approval and application as the actual service scope becomes available. Keep vents, seals, screens and payment hardware visible in examples. A render must not imply a wrapping method that prevents normal operation.

Use "Branding visualisation" as the visible caption for concepts. Use customer logos only with permission. The owner identified JāņogaGO as the first client on 2026-10-04 and supplied its machine photo for the wrapping section. The owner subsequently removed the public caption; retain the descriptive alt text. Keep concept renders separate from real client examples, and do not add outcomes or testimonials without evidence.

Write useful alt text describing the model, view or wrapping. Decorative duplicates can have empty alt text. Keep captions and labels as editable text rather than baked into images.

The homepage wrapping offer is a service overview. Its headline focuses on making the customer’s brand stand out, with the final brand phrase on its own line in light teal #a5d1cb using native inline colour formatting. Name the wrapping service directly in the introductory text. Group the heading, introduction and two unnumbered artwork/approval details on the left. Show the supplied JāņogaGO client photo without a visible caption on the right, preserving the full machine and transparency. Stack text and image on mobile. The following project-start section uses three photo cards at the owner’s request. Each card has an image, step number, action heading and short explanation on a canvas background. Use the real equipment photograph for model selection and local Unsplash photos for planning and discussion. Keep image areas, heading starts and description starts aligned across desktop cards. Use shared CSS grid rows through the card and copy groups so the tallest heading determines the description baseline, rather than reserving a fixed number of lines. Stack the cards in sequence on mobile. Keep the wrapping section as a distinct split layout. Use the shared purchase/rental quote button immediately above this section; do not repeat the same button inside the wrapping section.

Explain payments and management through the buyer's daily tasks. Pair a pale teal payment panel with stock, sales and remote-pricing rows, using the actual proposed model capabilities. Keep these as native editable blocks. Avoid simulated dashboards with invented numbers. Manufacturer feature headings can inform the hierarchy; copy and colours should belong to Baltic Vending Solutions.

Purchase and rental headings use matching 28px teal outline icons: a shopping bag for purchase and a calendar with a clock for rental. Keep a 12px gap between each icon and its heading. These are decorative theme SVGs tied to semantic heading classes, so the readable labels remain native editable text and the icons follow the correct option if columns move.

## Buttons, navigation and forms

Use one primary action label, "Request a quote", across the header, product details and final enquiry section. These can lead to the same form with model and purchase/rental context prefilled. "View equipment" has a different browsing purpose and is the secondary action. In prose, use underlined links rather than styling every link as a button. Avoid repeating the same quote action in neighbouring sections. Payments ends with its capability note; purchase and rental share one quote button below both options, with the interest selected in the form.

Buttons are at least 48px high, with 20 to 24px horizontal padding and a 600-weight label. Make icon-only controls at least 44 by 44px and give them an accessible name. Primary and secondary actions need visible hover, focus, pressed and loading states. Do not rely on a hover effect to reveal necessary information.

Inputs are at least 48px high, with labels above them and errors below them. Use the control-border token for a visible boundary. Give labels, helper copy and placeholders sufficient contrast. Placeholders may show an example but do not replace labels.

The first enquiry form should collect contact name, business name if available, email, product type, intended location and interest in purchase, rental or advice. Phone can be optional. Allow a visitor who has not selected equipment or secured a location to explain that. State required fields in text; do not rely on colour or an asterisk alone.

On validation errors, preserve entered values and identify the affected fields. On success, show a confirmation in the form's context without moving the page unexpectedly. Distinguish a saved enquiry from delivery of an internal notification. Do not promise a response time until the business has agreed one. Explain personal-data use beside the form and link to the completed privacy notice.

A focused control uses a 3px accent outline with a 3px offset on light backgrounds; use a white outline on ink. Keep it visible outside the element and clear of sticky headers or overlays. The mobile menu must support keyboard operation, Escape and focus return to its trigger.

## Voice and terminology

Use plain commercial language. Explain the benefit through a task or decision: "Check stock remotely" or "Choose equipment for your product range." Explain Vendlive and Vision AI when introduced rather than expecting a new entrepreneur to know the names.

| Use | Avoid |
| --- | --- |
| "Equipment to buy or rent" | "Revolutionise your business" |
| "Custom wrapping in your brand colours" | "Limitless branding possibilities" |
| "Tell us what you plan to sell" | "Unlock your entrepreneurial potential" |
| "We will prepare a proposal for your configuration" | "Guaranteed passive income" |

Use "equipment" as the broad catalogue term and "machine" in ordinary explanations. Use "smart fridge" when explaining the actual product type. Keep "purchase", "rental" and "custom branding" consistent. Separate the customer's daily operating duties from services provided by Baltic Vending Solutions.

The English copy examples in this guide establish meaning and tone. They do not confirm an English-only public website. If Latvian and English are implemented, translate the same intent naturally and allow room for longer labels.

## Accessibility and motion

Design toward WCAG 2.2 AA. Normal text needs at least 4.5:1 contrast; large text has a 3:1 threshold. Use 4.5:1 for all guide text pairings so typography size is not needed to justify contrast. Relevant control boundaries and state indicators need 3:1 against adjacent colours. [W3C text contrast](https://www.w3.org/WAI/WCAG22/Understanding/contrast-minimum.html), [W3C non-text contrast](https://www.w3.org/WAI/WCAG22/Understanding/non-text-contrast.html).

Use semantic headings, visible keyboard focus, descriptive link names and a skip link. Validate reflow, zoom, screen-reader labels and form errors during implementation. Palette checks do not establish that the entire website meets WCAG.

Motion is limited to brief state transitions, normally 120 to 180ms. FAQ answers follow the JanogaGo reference with a 340ms opening slide and a faster 220ms closing slide, accompanied by a teal plus/minus indicator. Equipment comparison buttons may reveal a brief teal tile-and-light treatment over their matching photo, as selected by the owner from the CodePen reference. Keep this treatment confined to the selected product, run it once per hover or focus, and retain static feedback with reduced motion. Each comparison photo links to the matching translated product page through the native image-block link control. Keep an inset focus outline visible on image links. The detail button also carries its own filled teal tile pattern and light sweep, so its hover feedback is visible independently of the photo. Honour reduced-motion preferences with immediate state changes. Keep essential content accessible without JavaScript. Avoid autoplay carousels, scroll hijacking, bouncing quote buttons and decorative parallax. An optional 3D viewer needs a static image fallback and accessible controls; it must not block the offer or comparison.

The manufacturer performance section uses one scroll-triggered counter, counting to its editable value over one second. Animate it only once per page load. Screen readers receive the final value throughout; reduced motion and disabled JavaScript show the final value immediately. Keep the manufacturer attribution in the explanatory note and record its source in project documentation, and distinguish reported averages from forecasts for a specific installation.

Same-page section links scroll smoothly to their destination through native browser behavior. Preserve URL fragments, keyboard activation and browser history. Use immediate scrolling for reduced-motion preferences.

## WordPress implementation and review

Privacy notices use a single reading column capped at 840px, with 16px body text at 1.75 line height. Use clear section headings, short paragraphs and lists for cookie names and lifetimes. Keep the site's IBM Plex Sans typography. Show unresolved business details in a visible draft notice and in the relevant paragraph. Place the native cookie-settings button beside the explanation of changing a choice. On mobile, reduce outer spacing without shrinking the text.

Follow [WORDPRESS_PLAN.md](WORDPRESS_PLAN.md) for the plugin baseline and storage rules. All visitor-facing content must be visually editable, including header/footer copy, form labels and feedback, CTA destinations and branding-demo captions. The baseline uses Gutenberg pages, native menus and per-language block-widget areas; a full Site Editor route requires the documented language-plugin edition decision. Do not hide content in PHP, CSS, JSON data files or shortcode-only sections.

Manage content photographs and logos through native Media Library and image controls. Uploads must have attachment records in WordPress's configured media locations. Editors must be able to upload, select, edit metadata, use native image tools, remove an image from a page and permanently delete unused media. Keep fonts and interface code separate from editorial images.

Register the palette, font sizes and spacing through shared theme settings and `theme.json` where appropriate. Reuse the same values in frontend and editor styles. Content templates should keep headings, descriptions, specifications, images, enquiry labels and contact details editable. Keep branding-demo captions editable as well.

Before a layout is ready for review, confirm that both models use the same comparison structure, wrapping concepts are labelled, price and service statements match the offer, and the primary action remains easy to find. Check narrow mobile widths, 200% text zoom, keyboard navigation, error/success states, reduced motion and the chosen website languages. Verify the logo's proportions and colour pairings when actual brand assets are added.
