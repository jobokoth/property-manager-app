# GEMINI.md

This document provides a comprehensive overview of the Property Manager project, its structure, and how to get it running.

## Project Overview

This is a web application for managing rental properties, built using the **Laravel** framework (version 12) with PHP 8.2.

The application appears to be a multi-faceted property management system with features for handling properties, units, tenancies, payments, and service requests. It includes role-based access control, distinguishing between regular users (likely property managers or owners) and tenants.

### Key Technologies

*   **Backend:** PHP / Laravel 12
*   **Frontend:** JavaScript (ES6), likely using Vite for asset bundling as indicated by `vite.config.js`. CSS is managed via `resources/css/app.css`.
*   **Database:** A relational database is used, managed via Laravel's Eloquent ORM and migrations. The specific database (e.g., MySQL, PostgreSQL) is configured in the `.env` file.
*   **Authentication:** Laravel Sanctum is used for API token authentication, and standard session-based authentication for web routes.
*   **Permissions:** `spatie/laravel-permission` is used for managing user roles and permissions.
*   **File Storage:** The application is configured to use Cloudinary for file/media storage, as evidenced by the `config/cloudinary.php` file and the `cloudinary-labs/cloudinary-laravel` dependency. It also uses AWS S3 for file storage.

### Core Application Models

*   `Property`: Represents a building or a collection of units.
*   `Unit`: A single rentable space within a `Property`.
*   `Tenancy`: The agreement linking a `User` (tenant) to a `Unit`.
*   `Payment`: Records of payments made by tenants.
*   `ServiceRequest`: Requests for maintenance or other services made by tenants.
*   `User`: Represents all users of the system, including admins, property owners, and tenants.
*   `WaterReading`: Records water meter readings.

## Building and Running the Project

This project uses `composer` for PHP dependencies and `npm` for JavaScript dependencies. The `composer.json` file contains convenient scripts for common tasks.

### 1. Initial Setup

To set up the project for the first time, run the following command from the `property-manager-app` directory. This will install all dependencies, create a `.env` file, generate an application key, run database migrations, and build frontend assets.

```bash
composer run setup
```

**Note:** You will need to create a database and configure its credentials in the `.env` file *before* running the setup script's migration step.

### 2. Running for Development

To start the development environment, which includes the web server, queue worker, log watcher, and Vite asset server, run:

```bash
cd property-manager-app
composer run dev
```

This will make the application accessible at the URL provided by the `php artisan serve` command (usually `http://127.0.0.1:8000`).

### 3. Running Tests

To execute the project's test suite (PHPUnit), run:

```bash
cd property-manager-app
composer test
```

## Development Conventions

*   **Routing:** Web routes are defined in `routes/web.php`. The application uses resource controllers for major features (Properties, Units, Tenancies, etc.).
*   **Database Migrations:** Database schema changes are managed through migration files located in `database/migrations`.
*   **Models & Relationships:** Eloquent models are located in `app/Models`. They contain the business logic and relationships (e.g., `Property` `hasMany` `Unit`).
*   **Views:** Frontend views are written in Blade templates and are located in `resources/views`.
*   **Static Assets:** Frontend assets (CSS, JS) are located in `resources/css` and `resources/js` and compiled into the `public/build` directory by Vite.
*   **Permissions:** User roles and permissions should be managed using the Spatie Laravel Permission package. Seeder files like `database/seeders/RolePermissionSeeder.php` are likely used to define the initial set of roles.
