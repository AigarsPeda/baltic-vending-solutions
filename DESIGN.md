---
name: Baltic Vending Solutions
description: Practical industrial equipment, clear information and cobalt controls.
colors:
  accent: "#2457e6"
  accent-hover: "#1c43b5"
  accent-soft: "#eaf0fd"
  accent-light: "#dce5ff"
  branding-highlight: "#b1263d"
  canvas: "#f3f6f6"
  surface: "#ffffff"
  ink: "#16282d"
  dark: "#182338"
  muted: "#506166"
  divider: "#cbd5d6"
  control-border: "#7e9094"
  error: "#a32727"
  success: "#216744"
typography:
  display:
    fontFamily: "Plex, 'Segoe UI', sans-serif"
    fontSize: "clamp(36px, 4.3vw, 56px)"
    fontWeight: 600
    lineHeight: 1.12
    letterSpacing: "-.025em"
  headline:
    fontFamily: "Plex, 'Segoe UI', sans-serif"
    fontSize: "clamp(28px, 3.2vw, 40px)"
    fontWeight: 600
    lineHeight: 1.2
    letterSpacing: "-.025em"
  title:
    fontFamily: "Plex, 'Segoe UI', sans-serif"
    fontSize: "clamp(22px, 2vw, 26px)"
    fontWeight: 600
    lineHeight: 1.2
    letterSpacing: "-.025em"
  body:
    fontFamily: "Plex, 'Segoe UI', sans-serif"
    fontSize: "18px"
    fontWeight: 400
    lineHeight: 1.6
  body-mobile:
    fontFamily: "Plex, 'Segoe UI', sans-serif"
    fontSize: "16px"
    fontWeight: 400
    lineHeight: 1.6
  label:
    fontFamily: "Plex, 'Segoe UI', sans-serif"
    fontSize: "16px"
    fontWeight: 600
    lineHeight: 1.4
  caption:
    fontFamily: "Plex, 'Segoe UI', sans-serif"
    fontSize: "14px"
    fontWeight: 400
    lineHeight: 1.5
  editor-body:
    fontFamily: "Plex, 'Segoe UI', sans-serif"
    fontSize: "16px"
    fontWeight: 400
    lineHeight: 1.5
  editor-button:
    fontFamily: "Plex, 'Segoe UI', sans-serif"
    fontSize: "15px"
    fontWeight: 600
    lineHeight: 1.4
rounded:
  square: "0px"
  control: "4px"
spacing:
  label: "8px"
  related: "16px"
  component: "24px"
  group: "32px"
  wide-group: "48px"
components:
  button-primary:
    backgroundColor: "{colors.accent}"
    textColor: "{colors.surface}"
    typography: "{typography.label}"
    rounded: "{rounded.control}"
    padding: "14px 24px"
  button-primary-hover:
    backgroundColor: "{colors.accent-hover}"
    textColor: "{colors.surface}"
  button-outline:
    backgroundColor: "transparent"
    textColor: "{colors.ink}"
    typography: "{typography.label}"
    rounded: "{rounded.control}"
    padding: "14px 24px"
  editor-button:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    typography: "{typography.editor-button}"
    rounded: "{rounded.control}"
    padding: "10px 14px"
  editor-button-selected:
    backgroundColor: "{colors.accent}"
    textColor: "{colors.surface}"
  editor-input:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    rounded: "{rounded.control}"
    padding: "8px 12px"
    height: "44px"
  quote-container:
    backgroundColor: "{colors.surface}"
    rounded: "{rounded.square}"
    padding: "32px"
---

# Design System: Baltic Vending Solutions

## Overview

**Creative North Star: "Practical industrial equipment"**

The established BVS interface uses clear proportions, precise alignment, visible equipment and readable information. Full machine images carry the visual interest. IBM Plex Sans, cobalt blue controls and cool neutral backgrounds connect the marketing pages and wrapping tools.

The palette was updated to the owner-selected cobalt on 2026-10-05; other implemented design conventions were recorded on 2026-10-04. [STYLE_GUIDE.md](STYLE_GUIDE.md) remains the detailed brand authority; this file does not replace its logo, writing or content rules. The wrapping editor extends that identity. Its page composition and editing workflow are recorded separately in [docs/design-editor.surface.md](docs/design-editor.surface.md).

**Key Characteristics:**

- Equipment remains complete and legible in its image area.
- White and cool neutral areas separate reading, controls and equipment.
- Cobalt marks actions, links and selected controls.
- Straight panels, thin rules and small control corners organise the interface.

## Colors

The existing palette uses cool neutrals and one cobalt blue brand accent. The canonical palette lives in `theme/baltic-vending-solutions/theme.json`. WordPress emits its named CSS presets for the public site and block editor. Shared theme aliases and plugin controls consume those presets; frontmatter records their current values.

### Primary

Cobalt accent identifies primary actions, text links and selected controls. Its darker hover variant gives feedback; the soft variant supports quiet hover and selected backgrounds.

### Neutral

White surface holds forms and tools. Cool canvas sits behind machine imagery and the editor stage. Ink supplies headings and body text; dark supplies the blue-charcoal marketing sections, footer and cookie surfaces; muted supports helper text. Divider separates groups. Control border supplies visible input and outlined-button boundaries.

Error and success colours pair with explanatory text in feedback. They are semantic colours, not additional brand accents.

**The Control Boundary Rule.** Use control border for inputs and outlined controls. Divider alone is a decorative separator, not an interactive boundary.

**The Customer Colour Rule.** Customer artwork may change the machine's colours. Keep the surrounding BVS interface in the established palette.

## Typography

IBM Plex Sans is self-hosted under the CSS family name `Plex`, using regular and semibold files with `font-display: swap`. Headings, body text and controls share it. Preserve Latvian characters and the system fallback.

The display, headline and title roles are fluid, semibold and left-aligned. Shared body text becomes the mobile body role at 781px and below. Lead copy is larger than body text; captions and helper text use the caption role. The editor uses its own body and button roles for tool density, with 17px semibold legends and a 22px preview heading.

Keep sentence case, descriptive action labels and units attached to specification values. The established guide limits prose to about 60 to 68 characters per line; individual implemented lead and helper blocks use their own bounds. Do not introduce marketing labels above headings.

The cookie banner is a documented exception in STYLE_GUIDE.md, with scoped Playfair Display and Manrope. It is not a typography source for new pages or editor tools.

## Layout

Shared content uses a 1200px maximum width with 40px outer gutters, reducing to 24px and then 16px at very narrow widths. Sections normally use 80px vertical padding on desktop and 48px on smaller screens. Larger split sections use 48px gaps; process and related groups also use 24px gaps. The system is based on 4px increments.

The desktop homepage hero has a 720px minimum height at 1000px and above, with a 560px machine image area. It returns to natural content height on mobile. The current header is 88px tall on wide screens and collapses navigation at 1180px into a 72px minimum-height header. These are implemented measurements, not a new global header specification.

Forms move from two columns to one at 781px. Footer utility controls stack and wrap on mobile. Privacy pages use a narrower 840px reading column. The wrap editor has a separate 1360px outer bound and a desktop tools-plus-preview grid; its mobile rearrangement belongs to its page brief.

## Elevation & Depth

Most content is flat. Background tones, thin borders and spacing define groups. Product imagery and the 3D model supply object depth. The editor’s flat panel has transparent hardware cut-outs and no rectangular shadow. Floating UI and the cookie banner retain the exceptions already described in STYLE_GUIDE.md.

## Shapes

Image panels, reading areas and workbench containers are straight-edged. Buttons and inputs use the control radius. Thin dividers organise content without wrapping each paragraph in a card. The editor's front template cuts out the supplied machine's unwrapped hardware regions with transparent pixels. The page background shows through these regions, and the template has no rectangular shadow.

## Components

### Buttons

Primary buttons use cobalt with white text, then the darker cobalt hover. Outline buttons use control-border strokes and ink text, with soft cobalt hover. Shared actions have a 48px minimum height; editor buttons use 44px. The editor's selected panel, drawing toggle, machine mode and pan buttons expose `aria-pressed` and use filled cobalt. Keep Rotate machine and Draw on machine separate so drawing does not rotate the object. Labelled zoom buttons surround a percentage output. Move zoomed panel appears above 100% flat zoom and changes to Continue editing while pan mode is active. Disabled editor actions reduce opacity and stop accepting input.

Visible focus uses a 3px cobalt outline with a 3px offset. Dark sections use white focus outlines. Underlined text actions remain underlined. The dark branding section reverses its primary button to white with darker cobalt text.

### Cards / Containers

Product and process groups use straight edges, neutral image areas, spacing and thin rules. The quote form uses a divider border with 32px padding, reducing on mobile. The editor has white tools and a cool neutral stage divided by a border. Do not apply its workbench layout to unrelated marketing content.

### Inputs / Fields

White inputs use control-border strokes, ink text and the control radius. Quote fields have a 48px minimum height. Editor text, colour and select controls are 44px high; range inputs have a 44px minimum height. The drawing selector keeps exact and smoothed freehand, line, rectangle, ellipse and text in one group. Show fill controls for rectangles and ellipses, and text with its size control when the text tool is selected. Labels, native range outputs, file helper text and visible uploaded filenames explain control state. Error and success feedback contains text as well as colour.

### Navigation

The native WordPress navigation uses semibold labels, an underlined hover and the shared focus outline. The fourth editor destination is included in the existing header layout; navigation collapses at the implemented breakpoint before labels crowd. Both languages share the wordmark and favicon.

### Disclosures

The shared FAQ uses a plus/minus indicator and bounded open/close motion. The editor uses native `details` and `summary` for drawing tools. Keep the drawing disclosure native and keyboard-operable, with a 44px minimum-height summary. A labelled corner button switches the mobile preview between 3D and 2D.

### Equipment and flat wrap preview

Keep the three panel selectors on one row with compact labels Priekša / Kreisais / Labais or Front / Left / Right; retain full names for accessibility and tooltips. Align the desktop panel-selector row with the machine preview toolbar while keeping mobile spacing unchanged. Use the supplied complete machine, preserving hardware and proportions. The flat panel supports direct branding edits, and the 3D machine supports inspection and drawing in its explicit Draw on machine mode. Drawing targets only actual wrap panels; hardware and transparent front cut-outs stay protected. Both views share the design. The Eraser removes the topmost painted drawing or logo in either view, including rotated/transparent artwork, with undo/redo. Preserve backgrounds and hardware; empty clicks create no history entry. Keep an accessible, pressed-state Eraser icon directly before undo/redo on desktop and mobile; it toggles erasing on the visible preview without opening drawing tools. On phones up to 340px wide, give the preview title its own row above the icon group and view switch. Undo and redo use 44px arrow-icon controls beside desktop preview modes or in the mobile preview header. Keep their translated accessible names, tooltips and disabled states; they remain available in 2D view and outside the drawing disclosure. Keep machine zoom separate from flat zoom. In the two-column desktop preview, align both zoom groups on shared grid rows beneath the visual content, with hints on the following row. Keep desktop 2D panels at their original column width, capped at 200px, and centre them. Use an 18px gap below the flat-panel heading to shift the image down by 6px; preserve proportions and mobile sizing. The flat panel scrolls when enlarged and offers an explicit pan mode for mouse, touch and arrow-key movement without changing artwork. On mobile, show the 3D machine straight on above the tool fields by default. Saved designs resume their last camera angle and zoom without a visible transition; older drafts without camera data keep the normal opening view independently of panel selection. Reset view returns to the front on mobile; explicit side selection remains available. Place its 44px reset icon in the upper-right corner of the mobile model viewport, with a translated accessible name and tooltip; keep zoom on its own row. On desktop, place the same reset icon first in the machine preview toolbar, before rotation and drawing modes. Use a corner button labelled for the destination view. The preview spans the viewport width; its heading, controls and tool fields retain readable insets. The active preview stays sticky within the tools on tall screens and returns to normal flow on short screens. Graphics failure selects the editable flat panel and disables the unavailable 3D switch; desktop retains the neutral still. No-JavaScript visitors get an explanation and can attach artwork to the quote form. Start again uses the existing red error colour with white text and a darker hover state. Align it to the far right of desktop actions and last in the full-width mobile stack; retain its confirmation. The editor's full behavior, keyboard controls and export boundaries belong to its page brief.

## Do's and Don'ts

### Do:

- **Do** reuse the established Plex, cobalt and cool neutral palette.
- **Do** preserve the complete machine and identify visual mockups as visualisations.
- **Do** pair selected and feedback colours with semantic state or explanatory text.
- **Do** retain visible focus, native labels and reduced-motion behavior.
- **Do** keep customer artwork colours within the machine or flat editing panel.

### Don't:

- **Don't** copy the Boost or JanogaGo identity into BVS controls.
- **Don't** replace control boundaries with decorative divider strokes.
- **Don't** turn every paragraph into a raised card.
- **Don't** add marketing labels above headings or use decorative parallax and autoplay carousels.
- **Don't** present the wrap mockup as a dimensioned print template or a completed customer project.

The first navigation destination, the design editor, is highlighted through the native menu class `bvs-design-menu`. Use cobalt text on an accent-soft background with 4px corners and darker cobalt text on hover. The desktop highlight preserves the existing text spacing; in the mobile drawer it fills the link row. It shares the brand palette rather than introducing another accent.

The owner-selected exception for the wrapping headline is `branding-highlight` #B1263D. Apply it only to the final brand phrase, using the named WordPress preset. Other dark-surface emphasis retains accent-light.
