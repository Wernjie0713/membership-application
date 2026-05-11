# Phase 4: Referral Engine

## Goal
Build referral tracking, tree calculation, and referral-based search support.

## Referral Rules
- Each member may refer multiple members.
- Each member may be referred by at most one member.
- Referral code lookup must validate against an existing member.

## Referral Tree
- Return all descendants for a selected member.
- Calculate level relative to the current member.
- Example behavior:
  - A -> B is level 1
  - A -> C, D is level 2
  - A -> E is level 3

## Search Support
- Allow search by referral code.
- Allow filtering by referrer and related referral data.

## Deliverables
- Service or query layer for tree traversal.
- Reusable tree data for member detail pages.

