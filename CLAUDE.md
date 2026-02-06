# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Multi-tenant property management system built with Laravel 12. Supports 6 user roles: Tenant, Property Manager, Owner, Caretaker, Vendor, and Super Admin. Core features include Mpesa payment processing, service request workflows, water tracking, and automated notifications.

## Development Commands

All commands should be run from the `property-manager-app/` directory.

### Initial Setup
```bash
composer run setup  # Installs deps, creates .env, generates key, runs migrations, builds assets
```

### Development
```bash
composer run dev  # Starts PHP server, queue listener, log watcher (pail), and Vite dev server
```

### Testing
```bash
composer test                           # Run all tests
php artisan test --filter=TestName      # Run a single test
php artisan test tests/Feature/ExampleTest.php  # Run a specific test file
```

Tests use SQLite in-memory database (configured in `phpunit.xml`). Test suites are in `tests/Unit/` and `tests/Feature/`.

### Database
```bash
php artisan migrate              # Run migrations
php artisan migrate:fresh --seed # Reset DB and seed roles/permissions
php artisan db:seed --class=RolePermissionSeeder  # Re-seed roles only
```

### Frontend Only
```bash
npm run dev    # Start Vite dev server
npm run build  # Production build
```

## Coding Conventions

- Use MySQL for database (not SQLite)
- Use Filament 5.x for admin panel
- Always include pagination for list endpoints
- Version APIs in the URL path (`/v1/`, `/v2/`)

## Architecture

### Tech Stack
- **Backend:** Laravel 12, PHP 8.2+, Spatie Laravel Permission
- **Frontend:** Blade templates, Tailwind CSS 4, Vite 7
- **Database:** MySQL (SQLite used for testing only)
- **Admin:** Filament 5.x
- **Storage:** Cloudinary for images/videos, AWS S3 optional
- **Auth:** Laravel Sanctum (API-ready)

### Key Directories (within property-manager-app/)
- `app/Models/` - Eloquent models (User, Property, Unit, Tenancy, Payment, etc.)
- `app/Services/` - Business logic (MpesaMessageParserService, PaymentAllocationService)
- `app/Http/Controllers/` - Web controllers
- `app/Http/Requests/` - Form request validation
- `database/migrations/` - Schema definitions
- `database/seeders/` - RolePermissionSeeder, SampleDataSeeder

### Core Models & Relationships
- **Property** → has many Units → each Unit has Tenancies
- **Tenancy** links tenant User to a Unit with rent snapshot
- **Payment** → has Allocations (rent/water split)
- **ServiceRequest** → has ServiceRequestMedia (Cloudinary uploads)
- **MpesaMessage** → parsed and matched to create Payments

### Key Services
- **PaymentAllocationService** - Handles payment allocation logic with DB transactions. Creates Payment, Allocation, and Balance records atomically.
- **MpesaMessageParserService** - Parses raw M-Pesa SMS text to extract amount, transaction ID, and timestamp.
- **TenantInviteService** - Manages tenant invitation workflow.

### Business Logic

**Payment Allocation Order** (implemented in `PaymentAllocationService`):
1. Rent arrears (oldest first)
2. Current rent
3. Water arrears
4. Current water
5. Advance → applied to next month's rent

**Mpesa Flow:** `new` → `parsed` → `matched` OR `needs_review` → `approved`/`rejected`

### Authorization
Uses Spatie Laravel Permission. Roles seeded via `RolePermissionSeeder`:
- `tenant`, `property_manager`, `owner`, `caretaker`, `vendor`, `super_admin`

Key distinction: Property Managers cannot create properties (only owners can). Every record should be scoped by `property_id` for multi-tenancy.

## Reference Documentation

See `AGENTS.MD` for the complete project specification including:
- Full data model (24+ tables)
- Role & permission matrix
- Feature specs by role
- 5-phase implementation plan
- Acceptance criteria checklist
