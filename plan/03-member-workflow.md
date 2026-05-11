# Phase 3: Member Workflow

## Goal
Implement full member CRUD, address handling, document uploads, and member search/listing.

## Member List
- Paginated member list page.
- Search by name, email, and referral code.
- Show key summary fields such as status, referral code, and registration date.

## Member Registration
- Registration form for personal details, contact data, and referral code.
- Generate a unique referral code after successful save.
- Allow multiple addresses with selectable address types.
- Allow upload of profile image and proof-of-address document.

## Member Details
- Display personal details.
- Show all addresses and documents.
- Show referrer information.
- Show referral summary and hierarchy entry point.

## Member Editing
- Update personal details.
- Update address records.
- Replace proof-of-address document if needed.
- Keep existing referral relationship unless business rules change.

## Member Deletion
- Remove a member safely.
- Preserve historical reward and referral integrity through soft deletes.

