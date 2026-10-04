# Baltic Vending Solutions style guide

Working design baseline, 2026-10-04. This guide turns the [client profile](CLIENT_PROFILE.md) into practical rules for the WordPress website. It covers visual design, product presentation and writing. No existing company logo or approved brand palette has been supplied, so the choices below establish a consistent starting point for implementation.

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

## Typography

Use IBM Plex Sans for headings, body text and controls. Its technical character suits equipment specifications, and one family keeps the reading experience consistent. IBM publishes it as an open-source family under the Open Font License and documents extended Latin support. [Official IBM Plex source](https://github.com/IBM/plex).

Self-host the selected WOFF2 files with their licence. Start with weights 400 and 600, use `font-display: swap`, and retain a system sans-serif fallback. Check the actual font files for English and Latvian characters before shipping. Do not make a remote font service a requirement for rendering the page.

| Role | Small screens | Desktop | Weight and line height |
| --- | --- | --- | --- |
| Main heading | 36px | 56px | 600, 1.12 |
| Section heading | 28px | 40px | 600, 1.2 |
| Product/section subheading | 22px | 26px | 600, 1.3 |
| Lead paragraph | 18px | 20px | 400, 1.5 |
| Body | 16px | 18px | 400, 1.6 |
| Labels, specifications, buttons | 16px | 16px | 400 or 600, 1.4 |
| Captions and secondary metadata | 14px | 14px | 400, 1.5 |

Use fluid heading sizes between these endpoints. Keep prose to about 60 to 68 characters per line. Use sentence case and left alignment for headings and explanatory text. Product names keep their official capitalisation. Small uppercase labels are optional and limited to short section identifiers.

Use tabular numerals in specification comparisons. Keep units attached to values, such as `13.3 in` and `+1 to +8 °C`. Mark dimensions consistently as height, width and depth when confirmed. Do not publish estimated measurements to fill an empty row.

## Layout and spacing

Use a maximum content width of 1200px, with 24px side gutters on small screens and 40px on larger screens. At very narrow widths, gutters may reduce to 16px. Use a 12-column desktop grid and a single reading column on mobile. A 24px grid gap is the default.

Base spacing on 4px increments. Use 8px between a label and its field, 16px within related content, 24px between components, and 32 to 48px between larger groups. Section padding is normally 80px on desktop and 48px on mobile. Adjust section spacing for content rather than forcing every section to the same height.

Keep the header about 72px high on desktop and 64px on mobile. The desktop navigation fits one line. Collapse it before labels become cramped. Use concise navigation labels such as Equipment, Purchase & rental, Custom branding and Contact, with "Request a quote" as the primary action.

Use a split hero on desktop: left-aligned copy and actions alongside a full machine image. Give the machine enough vertical room to show its proportions. Use natural content height, with the offer and primary action visible early. Do not force a viewport-height section. On mobile, show the offer and action before the machine image.

Use straight-edged image panels and comparison sections. Buttons and inputs use a consistent 4px radius. Cards are for distinct products or choices, not every paragraph. Group explanatory content with spacing and thin rules. Use shadows only for floating UI such as an open navigation panel.

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

Each product introduction needs a model name, a short suitability explanation, an equipment image, key proposal facts and a route to the detailed page. Use the same fact order for both machines. With two proposed models, a two-column comparison is sufficient; do not add a third placeholder product.

Our local Pages documents define the proposed offer. For Compact Cooler, retain the proposal's 13.3-inch screen and Vision AI configuration. For Smart Fridge, retain its stated features without borrowing the Classic model's 24-inch display or approximate dimensions from other projects. Supporting manufacturer information can explain the platform, but must not silently replace our proposed configuration.

On detailed pages, separate equipment specifications, proposed package inclusions and optional services. Clarify what is included in purchase or rental once terms are agreed. Where a price is unavailable, use "Request a quote" rather than a fabricated starting price or zero amount.

Use a semantic comparison table on desktop. On mobile, group readable values under each parameter with both model names visible, or use model sections that repeat the same labels. Keep all material facts available. Avoid a wide table that forces the whole page to scroll horizontally.

## Photography and custom wrapping

Prioritise authorised equipment photos, original customer projects and accurate renders. Neutral product views establish the machine; location photography explains its footprint and use. Keep lighting and background treatment consistent across the model comparison.

Show custom wrapping through a neutral and branded view of the same model at the same angle. Explain the customer's artwork input, design preparation, approval and application as the actual service scope becomes available. Keep vents, seals, screens and payment hardware visible in examples. A render must not imply a wrapping method that prevents normal operation.

Use "Branding visualisation" as the visible caption for concepts. Use customer logos only with permission. Do not represent JanogaGo artwork as a Baltic Vending Solutions customer case without evidence and approval for that use.

Write useful alt text describing the model, view or wrapping. Decorative duplicates can have empty alt text. Keep captions and labels as editable text rather than baked into images.

## Buttons, navigation and forms

Use one primary action label, "Request a quote", across the header, product details and final enquiry section. These can lead to the same form with model and purchase/rental context prefilled. "View equipment" has a different browsing purpose and is the secondary action. In prose, use underlined links rather than styling every link as a button.

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

Motion is limited to brief state transitions, normally 120 to 180ms. Honour reduced-motion preferences. Keep essential content visible without JavaScript. Avoid autoplay carousels, scroll hijacking, bouncing quote buttons and decorative parallax. An optional 3D viewer needs a static image fallback and accessible controls; it must not block the offer or comparison.

## WordPress implementation and review

Follow [WORDPRESS_PLAN.md](WORDPRESS_PLAN.md) for the plugin baseline and storage rules. All visitor-facing content must be visually editable, including header/footer copy, form labels and feedback, CTA destinations and branding-demo captions. The baseline uses Gutenberg pages, native menus and per-language block-widget areas; a full Site Editor route requires the documented language-plugin edition decision. Do not hide content in PHP, CSS, JSON data files or shortcode-only sections.

Manage content photographs and logos through native Media Library and image controls. Uploads must have attachment records in WordPress's configured media locations. Editors must be able to upload, select, edit metadata, use native image tools, remove an image from a page and permanently delete unused media. Keep fonts and interface code separate from editorial images.

Register the palette, font sizes and spacing through shared theme settings and `theme.json` where appropriate. Reuse the same values in frontend and editor styles. Content templates should keep headings, descriptions, specifications, images, enquiry labels and contact details editable. Keep branding-demo captions editable as well.

Before a layout is ready for review, confirm that both models use the same comparison structure, wrapping concepts are labelled, price and service statements match the offer, and the primary action remains easy to find. Check narrow mobile widths, 200% text zoom, keyboard navigation, error/success states, reduced motion and the chosen website languages. Verify the logo's proportions and colour pairings when actual brand assets are added.
