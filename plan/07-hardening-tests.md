# Phase 7: Hardening and Testing

## Goal
Stabilize the application with validation, seeding, and automated tests.

## Validation
- Ensure critical fields are unique and properly formatted.
- Validate file size and allowed MIME types.
- Verify referral code existence when provided.

## Seeding
- Seed address types such as residential and correspondence.
- Seed sample promotions and sample reward achievers.
- Seed sample members for demo/testing.

## Tests
- Member create/update/delete flows.
- Referral code generation and referral lookup.
- Referral tree levels.
- Reward calculation and duplicate prevention.
- Export filtering.

## Acceptance Criteria
- All major pages work end to end.
- Referral and reward logic produces consistent results.
- Reports and exports match filtered data.

