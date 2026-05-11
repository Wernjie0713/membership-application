# Phase 5: Promotions and Rewards

## Goal
Implement promotion setup and automated reward calculation for referral milestones.

## Promotion Setup
- Admin can create promotions with start date, end date, and status.
- Promotions define one or more reward tiers.

## Reward Rules
- Tier 1: 10 referrals -> USD 100
- Tier 2: 50 referrals -> USD 500
- Tier 3: 100 referrals -> USD 1000
- Tier 4: every 10 referrals beyond 100 -> USD 150 each

## Reward Processing
- Run a daily scheduled command.
- Count valid referrals only within active promotion windows.
- Store earned rewards in the reward achiever table.
- Prevent duplicate reward rows for the same member, promotion, and tier threshold.

## Deliverables
- Promotion CRUD or admin management screens.
- Scheduled command for reward calculation.
- Persisted reward history for reporting.

