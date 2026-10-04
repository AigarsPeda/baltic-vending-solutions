# Manufacturer performance figure

Checked on 2026-10-04 at the owner's request. The [Boost inc homepage](https://boostinc.com/) publishes "Average Revenue Uplift" beside a counter configured with `data-end="75"`, `data-prefix="+"` and `data-postfix="%"`. The screenshot supplied by the owner shows the rendered +75% value. The page's text-only extraction initially shows zero because the value is animated.

The BVS homepages use this as a manufacturer-reported average, with Boost inc identified in the explanatory note. At the owner's request, the metric caption now reads only "Average revenue uplift", with a Latvian equivalent. The separate public source-link paragraph was removed at the owner's request; the source is retained in this document. It is not a BVS customer result or a forecast for either proposed machine. The page does not state the sample, period or measurement method for this average; do not infer these or promise the same outcome.

The section appears after the equipment comparison and uses native Group, Columns, Paragraph and Heading blocks. Edit the number, wording and source through the WordPress visual editor. Keep the `bvs-counter` paragraph class for the animation. The theme parses the saved number rather than owning its value. The motion is adapted from `/Users/aigarspeda/Desktop/JanogaGo/theme/janogago/assets/js/counters.js` and runs once when the number enters view.

If Boost changes or removes the figure, update both language versions and the starter importer. Re-check this source before production launch.
