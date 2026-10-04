# Payments and product management

Checked and implemented locally on 4 October 2026. The LV/EN homepages now contain a `payments-management` section after the shopping-experience section. Its original copy explains the customer's payment and the operator's stock, sales and pricing tasks. Public copy has no source links, as requested by the owner.

## Evidence and scope

Our local Pages proposal documents remain the primary configuration source. Compact Cooler specifies a contactless terminal, Vendlive inventory and remote product/price management, and Vision AI recognition. Smart Fridge specifies Payter, NFC, Visa, Mastercard, Apple Pay, Google Pay, and stock, purchase and temperature telemetry.

The manufacturer's [payments page](https://boostinc.com/payments/) corroborates contactless cards and digital wallets. Its [software page](https://boostinc.com/software/) corroborates remote stock and pricing functions. The [equipment page](https://boostinc.com/pos/) describes Vision AI monitoring and mismatches between products taken and paid for. It does not establish a universal automatic-charging or door-unlocking sequence, so the new section describes tapping the terminal without inventing that sequence.

The copy identifies Compact's catalogue/price controls and Smart's telemetry separately. It does not imply that every extended platform integration, licence or payment method is included in every local configuration. Final software access, payment setup and fees belong in the quote. Daily restocking remains an operational task.

## Design and editing

Boost's concise feature headings and emphasis on operational visibility informed the section. The BVS version uses its established Plex typography and teal palette, with a pale payment panel beside readable management rows. It contains no imitation dashboard, invented operational figures, copied slogans or unsupported ranking claim.

Edit the section in the native WordPress homepage editor, in both translations. All 25 blocks are standard core blocks. `scripts/local/operations-content.php` supplies the same content to new seeds; code sync preserves later saved editorial changes. A full database/uploads migration carries the existing section to DigitalOcean.

Both languages passed layout checks at 1440/768/375/320 pixels, quote-button navigation and native block validation. Screenshots are in ignored `output/operations-*`.

The independent finish review returned **ship** after correcting the Latvian payment wording and limiting the supporting model note to 75 characters per line. Both findings were resolved in the final desktop/mobile screenshots.
