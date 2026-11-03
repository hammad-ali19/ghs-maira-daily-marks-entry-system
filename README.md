# School Daily Test Marks Management System

A Laravel-based school management system for recording and tracking daily test marks at **Government High School (GHS) Maira**.

## Overview

This application allows administrators and teachers to manage classes, sections, subjects, teachers, students, and daily test marks. It supports student enrollment history preservation, test results entry and editing, printable test reports, and PDF/CSV exports.

## Features

- **Authentication & Roles**: Admin and Teacher roles with role-based access control and active status checks.
- **Teacher Management**: Create, edit, deactivate, and delete teacher accounts (admin only).
- **Classes & Sections**: Create and configure school classes (e.g. KG, Prep, 1st–10th) and sections (e.g. A, B, Default).
- **Subject Catalogue**: Manage a reusable global subject catalogue.
- **Teacher Assignments**: Assign teaching faculty to subjects within class sections.
- **Student Management**: Enroll students with B-Form number and required photograph; preserve class history when students change sections.
- **Daily Tests & Marks Entry**: Create tests, enter and edit obtained marks for students.
- **Student Test History**: View a student's complete test history across all classes attended.
- **Reports & Exports**: Preview, download PDF (via `barryvdh/laravel-dompdf`), and download CSV reports for each test.
- **Dashboard**: Role-aware dashboard with school statistics and recent activities.

## Tech Stack

- **Framework**: Laravel 13 (PHP 8.3+)
- **Database**: SQLite (`database/database.sqlite`)
- **Frontend**: Blade + Tailwind CSS v4 (`@tailwindcss/vite` with custom Pakistani school green theme)
- **PDF Generation**: `barryvdh/laravel-dompdf`
- **Testing**: Pest v5 (31 tests, 87 assertions, 100% passing)

## Getting Started

### Requirements

- PHP 8.3+
- Composer
- Node.js & npm (for frontend assets)
- SQLite extension enabled

### Installation

```bash
# Clone the repository
git clone <repository-url>
cd ghs-maira-quick-test-record-system

# Install PHP dependencies
composer install

# Install JS dependencies
npm install

# Copy environment file and generate app key
cp .env.example .env
php artisan key:generate

# Create the SQLite database file (if not exists)
touch database/database.sqlite

# Run migrations
php artisan migrate --force

# Build frontend assets
npm run build

# Seed the database (creates default admin account)
php artisan db:seed
```

### Default Admin Account

After seeding:

- **Email**: `admin@ghsmaira.edu.pk`
- **Password**: `Admin123!`

### Running the Application

```bash
# Start the Laravel development server
php artisan serve

# (Optional) Start Vite for asset watching during development
npm run dev
```

Then visit `http://127.0.0.1:8000/login` and log in with the admin credentials above.

### Creating the Initial Administrator

You can also create an admin interactively:

```bash
php artisan app:make-admin
```

Or with options:

```bash
php artisan app:make-admin --name="Headmaster" --email="admin@ghsmaira.edu.pk" --password="Password123!"
```

## Roles & Permissions

### Administrator (`role = 'admin'`)

Full administrative authority across the school:

- Create, edit, deactivate, and reset teacher passwords
- Create and configure school classes and sections
- Assign class incharge teachers to class sections
- Manage the global subject catalogue
- Enroll, edit, and manage students across all classes
- View test results, student histories, and generate downloadable reports
- Access the administrator dashboard with school statistics

### Teacher (`role = 'teacher'`)

- No public registration; accounts are created by an administrator
- View classes, sections, students, and subjects
- **As Class Incharge**: Enroll students, assign subjects and teaching faculty, enter and edit test marks
- **As Teaching Faculty**: Enter daily test marks for assigned class-subject combinations; edit marks they are authorized to manage
- View complete test history of any student across all classes
- Preview printable test reports and download PDF/CSV test reports

## Running Tests

```bash
php artisan test
```

Expected: **31 tests passed, 87 assertions**.

## Project Structure

- `routes/web.php` — Application routes with authentication and role middleware
- `app/Http/Controllers/` — Controllers for teachers, classes, sections, subjects, students, tests, reports, etc.
- `app/Models/` — Eloquent models (User, SchoolClass, ClassSection, Student, Subject, Test, etc.)
- `database/migrations/` — Database schema migrations
- `database/seeders/` — Database seeders
- `resources/views/` — Blade templates
- `tests/` — Pest feature and unit tests

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
