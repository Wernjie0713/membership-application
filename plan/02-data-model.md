# Phase 2: Data Model

## Goal
Define the database structure and Eloquent relationships for members, addresses, documents, promotions, and rewards.

## Tables
- `members`
- `address_types`
- `addresses`
- `documents`
- `promotions`
- `promotion_reward_tiers`
- `reward_achievers`

## Relationship Rules
- A member can have many addresses.
- A member can have many documents.
- An address belongs to one member and one address type.
- A document uses a polymorphic `documentable` relation.
- A member can refer many members and belong to one referrer member.
- A promotion can have many reward tiers.

## Design Notes
- Add indexes for email, referral code, promotion dates, and foreign keys.
- Store file metadata for uploads in the documents table.
- Keep reward records idempotent with unique constraints where needed.

