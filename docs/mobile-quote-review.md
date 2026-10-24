# Mobile quote-form review — 24 October 2026

Reviewed the shared native quote block on the home and product pages in Latvian and English. All labels, hints, result messages and optional-field settings remain editable through WordPress; this release changes the block's frontend CSS and JavaScript only.

## Changes

- At widths up to 600px, field groups have 24px separation, text/select controls are at least 52px high, hints/privacy text use 16px type, and the submit button spans the available form width. The editor design checkbox retains its compact size.
- Added space before the expanded optional-field group.
- Submission exposes its busy state to assistive technology. On completion, focus moves to the success/error message, bringing it into view without focusing a text input.
- Editing the form clears stale feedback from the previous enquiry. Errors retain entered values; successful submissions reset them.

## Verification

- Local browser checks at 320, 375, 600 and 820px on LV/EN home and representative product routes, with optional fields expanded: no horizontal overflow, clipped controls or cramped paired fields. Field fonts remain 16px; name/email/phone autocomplete and native email/tel input types were already correct.
- Empty required fields and an invalid email focus the relevant control. Invalid text-file attachments are rejected; a corrupted PNG is rejected by the server. An invalid attachment inside a closed optional group reopens the group and receives focus. Removing it clears validation.
- Real local AJAX submissions saved LV and EN private enquiries. A valid generated PNG was stored with its enquiry. The local mail hook captured notifications without sending email.
- A temporary local-only 503 response tested the fallback error message, focused feedback, retained values, re-enabled button and successful retry. The test hook was removed before deployment.
- The three synthetic local enquiries and generated stored attachment were deleted after checking their exact identities. Existing WordPress integration checks passed.
- Live browser checks cover responsive layout, native validation and optional-field interaction. An isolated CLI save check on the droplet verified private storage and successful save despite suppressed email; the temporary record was immediately deleted. No live email notification was sent, so delivery to the recipient was not tested.
- JavaScript syntax and whitespace checks passed. Deployed CSS/JS hashes match source. Evidence is in ignored `output/mobile-form-review/`.

These are responsive browser checks, not a physical iOS/Android keyboard or device certification.
