# Property Manager Application - Development Context

## Project Overview

This is a multi-tenant property management system built with **Laravel 12** that enables property managers, owners, tenants, and vendors to manage properties, rentals, payments, and maintenance requests. The system handles Mpesa payment processing, automated rent notifications, water tracking, and service request workflows.

### Core Features
- Multi-role system (Tenant, Property Manager, Owner, Vendor, Super Admin)
- Mpesa payment message ingestion and parsing
- Automated rent and late payment notifications
- Service request management with vendor workflow
- Water consumption tracking
- Monthly statement generation
- Cloudinary integration for media uploads

### Tech Stack
- **Framework**: Laravel 12
- **Authentication**: Laravel Sanctum for API readiness
- **Authorization**: Spatie Laravel Permission package
- **Media Storage**: Cloudinary for images/videos
- **Database**: MySQL (implied from Laravel usage)

## Architecture & Structure

### Domain Modules
The application follows a domain-driven design with the following recommended structure:
- `app/Domain/Properties`
- `app/Domain/Payments`
- `app/Domain/Requests`
- `app/Domain/Vendors`
- `app/Domain/Water`
- `app/Domain/Notifications`
- `app/Domain/Statements`

### Key Principles
- Every record belongs to a **Property/Building/Portfolio** context for multi-tenancy support
- Use Policies + Spatie permissions for authorization (defense in depth)
- Store upload metadata locally; actual files in Cloudinary
- All money allocations are reproducible from transaction + rules

## Roles & Permissions

The system implements role-based access control using Spatie Laravel Permission:

| Role | Key Permissions |
|------|----------------|
| Tenant | Submit service requests, upload Mpesa messages, view statements |
| Property Manager | Manage properties/units/tenants, communicate with users, manage service requests |
| Owner | All PM features + Mpesa ingestion, allocation, financial oversight |
| Vendor | View assigned jobs, submit quotes/schedules/invoices, confirm payments |
| Super Admin | Global access to all properties and system settings |

## Core Data Model

### Identity & Access
- `users` - Core user table with personal information
- `property_user` - Pivot table linking users to properties with relationship types
- Spatie tables for roles/permissions management

### Properties & Tenancy
- `properties` - Properties/buildings managed in the system
- `units` - Individual rental units within properties
- `tenancies` - Links tenants to units with lease terms
- `rent_rules` - Configurable rent policies per property/unit

### Payments & Mpesa
- `mpesa_messages` - Raw Mpesa messages for parsing and processing
- `payments` - Processed payments linked to tenancies
- `allocations` - How payments are distributed (rent vs water)
- `balances` - Monthly rent and water balances per tenancy

### Service Requests
- `service_requests` - Maintenance requests from tenants
- `service_request_media` - Photos/videos attached to requests
- `service_quotes` - Vendor quotes for repair work
- `service_schedules` - Scheduled repair appointments
- `service_invoices` - Completed work invoices
- `vendor_payments` - Payment confirmations

## Building and Running

### Prerequisites
- PHP 8.2+ (required for Laravel 12)
- Composer
- MySQL/MariaDB
- XAMPP/Laravel Valet/Other local development environment

### Setup Instructions
1. Clone the repository
2. Run `composer install`
3. Configure `.env` file with database and Cloudinary credentials
4. Run `php artisan migrate` to create database tables
5. Seed roles and permissions with `php artisan db:seed`
6. Start the development server with `php artisan serve`

### Key Commands
- `php artisan migrate` - Run database migrations
- `php artisan db:seed` - Seed initial data (roles, permissions)
- `php artisan storage:link` - Link storage directory for file access
- `php artisan notifications:rent-due` - Send rent due notifications (scheduled)
- `php artisan notifications:late-payments` - Send late payment notifications (scheduled)

## Development Conventions

### Coding Standards
- Follow PSR-12 coding standards
- Use domain-driven design principles
- Implement proper authorization checks using Laravel Policies
- Use Spatie Laravel Permission for role-based access control

### Testing Practices
- Write feature tests for business logic
- Use Laravel's built-in testing tools
- Test authorization policies for each role
- Test payment allocation logic thoroughly

### File Organization
- Organize code by domain (Properties, Payments, etc.)
- Use Laravel's built-in resource controllers
- Separate API routes from web routes
- Use form request validation for input handling

## Key Business Logic

### Mpesa Payment Processing
1. Tenant uploads Mpesa message text or screenshot
2. System parses transaction details (amount, sender, date)
3. Payment is matched to tenant's active tenancy
4. Allocation algorithm applies payment to rent first, then water surplus

### Automated Notifications
- Rent due notifications sent X days before due date
- Late payment notifications after grace period
- Implemented via Laravel Scheduler for recurring tasks

### Monthly Statements
- Generated per tenancy per month
- Includes opening/closing balances, payments, and allocations
- Available as JSON with optional PDF generation

## Implementation Phases

### Phase 0: Setup
- Laravel 12 installation
- Spatie roles/permissions setup
- Base authentication and user profiles
- Cloudinary integration
- Seeding of roles and permissions

### Phase 1: Properties & Tenancy
- Properties CRUD operations
- Units management
- Tenancy assignment
- Rent rule configuration

### Phase 2: Payment Processing
- Mpesa message upload and parsing
- Payment allocation logic
- Balance tracking

### Phase 3: Water Tracking
- Water consumption monitoring
- Integration with payment allocation

### Phase 4: Service Requests
- Tenant request submission
- Vendor workflow management
- Quote/schedule/invoice cycle

### Phase 5: Notifications & Statements
- Automated notification system
- Monthly statement generation