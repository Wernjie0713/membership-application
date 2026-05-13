# Blackwell Members

Laravel 11 membership application built for a backend interview task. The system manages members, addresses, polymorphic documents, referral relationships, promotion rewards, reporting, and exports.

## Overview

This project uses a role-based flow with `silber/bouncer`:

- `admin` users manage the system
- `member` users register, complete their membership profile, refer others, and view their own referral and reward data

The implementation keeps `users` as the login identity and `members` as the business profile.

## Key Features

- Admin dashboard with member, promotion, and reward summary
- Member CRUD with:
  - personal details
  - multiple addresses
  - address types
  - profile image upload
  - proof-of-address upload
- Polymorphic documents for member profile images and address proof files
- Referral system with:
  - unique referral code generation
  - referral code registration
  - referrer display
  - multi-level referral tree
- Promotion reward system with:
  - promotion period management
  - configurable reward tiers
  - scheduled daily reward processing
  - reward reporting and export
- Member list export and reward report export
- Role and permission management using `silber/bouncer`

## Roles

### Admin

- Access admin dashboard
- Manage members
- Manage promotions
- View reward reports
- Export member list and reward reports

### Member

- Register a login account
- Verify email address
- Complete membership onboarding
- Maintain own member profile
- View own referral code
- View own referral tree
- View own rewards

## Registration Flow

This project intentionally uses a two-step registration flow:

1. Public registration creates the login account only
2. The user verifies their email address
3. After sign in, the user completes the membership profile with:
   - personal details
   - addresses
   - uploads
   - optional referral code

This keeps authentication and membership profile completion separate while still ensuring that referral participation and reward eligibility only begin after onboarding is completed.

## Reward Rules

Active promotions use these reward tiers:

- Tier 1: 10 referrals -> USD 100
- Tier 2: 50 referrals -> USD 500
- Tier 3: 100 referrals -> USD 1000
- Tier 4: every 10 referrals beyond 100 -> USD 150 each

Current reward processing rules:

- only active promotions are processed
- processing runs daily through Laravel Scheduler
- only completed and `active` member profiles count as valid referrals
- duplicate rewards are prevented by database uniqueness and service checks

## Tech Stack

- Laravel 11
- Laravel Breeze
- Blade
- Tailwind CSS
- Alpine.js
- `silber/bouncer`
- `maatwebsite/excel`

## Project Structure

- `app/Http/Controllers` - web controllers
- `app/Services` - business logic for members, rewards, reporting, redirects, and referral tree
- `app/Models` - Eloquent models
- `database/migrations` - schema
- `database/seeders` - sample seed data
- `resources/views` - Blade views
- `tests/Feature` - feature tests
- `plan` - implementation planning notes

## Main Screens

- Landing page
- Login and registration
- Member onboarding
- Admin dashboard
- Member list
- Member detail
- Member edit/create
- Promotions
- Reward report
- Member portal dashboard

## Setup

### Requirements

- PHP 8.2+
- Composer
- Node.js + npm
- MySQL or another Laravel-supported database

### Installation

```bash
composer install
npm install
php artisan key:generate
```

Create `.env` from `.env.example`, then update the database configuration.

Windows:

```powershell
copy .env.example .env
```

macOS / Linux:

```bash
cp .env.example .env
```

Update `.env` with your database credentials, then run:

```bash
php artisan migrate --seed
php artisan storage:link
```

### Mail configuration

Email verification and password reset require a working mail configuration in `.env`.

For local development, use a tool such as Mailpit or Mailhog, or configure a real SMTP provider before testing these flows.

### Run locally

For backend:

```bash
php artisan serve
```

For frontend development:

```bash
npm run dev
```

For a production asset build:

```bash
npm run build
```

## Default Seeded Account

Admin login:

- Email: `admin@example.com`
- Password: `password`

## Scheduler

The daily reward processor is:

```bash
php artisan promotions:process-rewards
```

It is registered in the Laravel scheduler, so in a real deployment you should also run:

```bash
php artisan schedule:run
```

or configure a cron entry for Laravel Scheduler.

## Testing

Run the full test suite:

```bash
php artisan test
```

The current suite covers:

- auth flow
- email verification
- password reset
- deactivated-account access blocking
- two-step registration
- member management
- referral tree behavior
- reward processing rules
- reward reporting access
- profile updates
