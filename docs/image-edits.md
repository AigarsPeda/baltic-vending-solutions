# Equipment image edits

Updated 2026-10-04 using the built-in imagegen tool. The owner requested removal of the Boost inc machine logos shown in the screenshots. Packaging brands and touchscreen content remain in the images.

## Saved files

- `output/media/compact-cooler-unbranded.png`
- `output/media/smart-fridge-unbranded.png`

These workspace copies are ignored by Git. The authoritative website assets are native Media Library attachments 54 and 55, with generated sizes under WordPress uploads. They replace the original image blocks on both homepages and all four equipment pages. Original attachments 5 and 6 remain available as source references.

For another installation, migrate the database and uploads together, or supply these cleaned files to the one-time importer. Do not restore the original branded pictures during a theme update.

## Final edit prompts

### Smart Fridge

Use case: precise-object-edit. Asset type: WordPress equipment catalogue photo. Edit target: the attached original front-view black Smart Fridge image. Remove only the white Boost inc manufacturer wordmarks: the small one on the top-left black fascia above the touchscreen and the large one across the upper glass door. Reconstruct the matte black fascia and the glass/product detail beneath the lettering seamlessly. Preserve the exact machine geometry, all shelves, stocked packages, touchscreen content, terminal, vents, camera angle, lighting, soft ground shadow, position, proportions and framing. Keep product packaging brands and functional screen content unchanged. Preserve the existing image transparency and canvas composition. Do not add a replacement logo or text. No arrows or annotations. This is a localised logo removal, not a redesign.

### Compact Cooler

Use case: precise-object-edit. Asset type: WordPress equipment catalogue photo. Edit target: the attached original three-quarter-view black Compact Cooler image. Remove only the white Boost inc manufacturer wordmark across the upper front glass. Seamlessly reconstruct the glass and the snack packaging behind that lettering. Preserve the exact machine geometry, angle, screen, terminal, hinges, shelf arrangement, stocked products and their package branding, vents, wheels, black cabinet finish, lighting, proportions, framing and existing transparency. Preserve the original subtle soft ground shadow without expanding it or creating noisy/ragged edges. Do not change the touchscreen content. Do not add a replacement logo, new text, annotations or arrows. Make a localised logo-removal edit only; do not redesign or invent equipment details.

## Verification

The cleaned images were inspected on the equipment comparison section. Browser checks confirmed every product image loads from an unbranded Media Library filename on all six equipment-related pages. Machine logos are removed from the Smart Fridge fascia/glass and Compact Cooler glass. The source Pages proposal documents and product specifications are unchanged.

The homepage hero now uses the same front-view Smart Fridge photo, native attachment55, in both languages. `scripts/local/setup-home-hero.php` replaces only the homepage hero Image block and preserves the surrounding content. New seeds use Smart Fridge too. The existing image keeps its transparency and responsive presentation.

The homepage machine is displayed25% larger inside its existing frame. A two-axis CSS mask fades the cropped shadow into the page background, removing the visible rectangular image edges while preserving the cabinet. On stacked tablet layouts, the image width is capped at480px so enlargement keeps the whole machine in view. Desktop and mobile captures are in ignored output/home-smart-fridge-*.
