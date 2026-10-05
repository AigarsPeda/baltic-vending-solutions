# Logo and favicon

Created on 2026-10-04 using the built-in image-generation tool. The owner's selected direction is a text-only wordmark with a plain typographic B as the favicon. Earlier machine-shaped symbols are not installed.

## Files and WordPress settings

- Logo: `output/brand/baltic-vending-solutions-wordmark.png`, transparent PNG, native Media Library attachment 103.
- Current favicon: `output/brand/baltic-vending-solutions-favicon-cobalt.png`, white B on cobalt, native Media Library attachment 206. The earlier teal asset remains in the library for recovery. Updated on 2026-10-05 with the built-in image tool, preserving the white B.
- Edit prompt: "Recolor this existing company website icon. Replace the green background with a flat #2457E6 blue background. Preserve the white capital letter B, its shape and positioning. Square opaque PNG."
- Header previews: `output/brand/header-{lv,en}-{1440,375,320}.png`.

The header uses WordPress's `custom_logo` theme setting. Replace it under **Appearance > Customize > Site Identity**. Replace the favicon under **Settings > General > Site Icon**, or the Site Identity panel. Uploads, attachment records, alt text and image sizes use native WordPress storage and APIs. The theme contains no asset URLs or attachment IDs. Both languages share this identity; logo links lead to their respective homepages.

The current icon has native 32, 64, 180, 192 and 270 pixel Site Icon sizes; larger requests use the source attachment. There is no separate hardcoded favicon tag. The raster icon is independent of CSS presets; replace it natively when changing the palette again. Media can be replaced or deleted using the standard Media Library controls. Source exports and browser previews are in ignored `output/`; transfer the database and uploads with the site. Code-only sync does not install these settings or files.

## Original prompt set

### Reference lockup generation

Use case: logo-brand. Design one polished logo for Baltic Vending Solutions, a B2B supplier of premium smart vending equipment. Transparent background. Horizontal lockup: a minimal teal #00675F vending cabinet symbol on the left, and precise bold dark ink #16282D humanist sans serif type on the right reading exactly "Baltic Vending" above "Solutions". The icon must NOT be a letter B or any monogram. Create one upright cabinet silhouette with softly squared corners, a single large clean negative-space product window, two understated horizontal shelf lines inside the window, and a small square payment terminal on the right side. Elegant flat geometric design, consistent deliberate proportions, no individual tiny product boxes, no clutter. It should be recognisable as smart vending equipment without becoming a detailed illustration. Lettering must be excellent and legible at a website header width of 240px. Two text lines together equal icon height. Closely frame artwork on a wide shallow canvas, minimal transparent padding above/below, aspect ratio near 4:1. Solid flat colours only, no gradients, texture, glow or shadows, no mockups or extra text.

### Selected text-only wordmark edit

Edit target: supplied Baltic Vending Solutions logo. Remove the entire teal vending-machine icon on the left. Keep ONLY the existing exact bold humanist sans-serif wordmark, dark ink #16282D, with "Baltic Vending" on the first line and "Solutions" on the second. Preserve the lettering design and left alignment. This is a pure typographic logo: no icon, no monogram, no decorations. Reframe the resulting image tightly around the two text lines, with just 2% transparent padding. A wide shallow canvas with approx 3.6:1 aspect ratio and no big empty margins. Genuinely transparent background. Exact words must be retained. Flat solid dark ink, no texture, no gradient, no shadows, no mockup.

### Selected plain B favicon

Use case: logo-brand. Create a square favicon for Baltic Vending Solutions. A single plain uppercase letter "B" in white, matching the bold sans-serif B in the supplied wordmark reference. Normal typographic B with two clean open counters, no vending shelves, no decorative monogram, no machine icon, no extra text. Center it on a completely flat solid deep teal #00675F background that fills the square edge to edge. Letter height 72% of canvas. Balanced safe margins. Very crisp, legible at 16px and 32px. No gradients, texture, shadows, outline or mockup.

## Verification

Both languages were checked at 1440, 1024, 980, 768, 375 and 320 pixel widths. Logo and favicon URLs load successfully, alt text names the company, the language-specific home link is correct, and the logo fits beside the mobile menu without overlap or horizontal overflow. The dark wordmark is intended for a light header background.

