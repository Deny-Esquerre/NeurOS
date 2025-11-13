## Project Overview

This is a Laravel 12 web application that appears to be an adaptive learning system. It uses FilamentPHP for the admin interface, which is a TALL stack (Tailwind CSS, Alpine.js, Laravel, Livewire) admin panel framework.

The application has features for managing users (students, parents, psychologists), tasks (with categories like reading comprehension, mathematics, and recreation), and monitoring heart rate data. It integrates with Google APIs, likely for Google Fit to collect heart rate data, and potentially with OpenAI for AI-powered features. The application also includes real-time capabilities using Laravel Reverb and Pusher-JS.

## Key Technologies

*   **Backend:** PHP 8.2, Laravel 12
*   **Frontend:** Vite, Tailwind CSS, Alpine.js (via Filament), Livewire (via Filament)
*   **Admin Panel:** FilamentPHP 3.2
*   **Database:** Not specified, but likely MySQL or PostgreSQL (based on standard Laravel practice).
*   **Real-time:** Laravel Reverb, Pusher-JS
*   **APIs:** Google API Client, OpenAI

## Building and Running

### Setup
To set up the project for the first time, run the following command:
```bash
composer run-script setup
```
This will install composer and npm dependencies, create a `.env` file, generate an application key, run database migrations, and build the frontend assets.

### Development
To start the development servers (Laravel server, queue worker, log watcher, and Vite), run:
```bash
composer run-script dev
```

### Testing
To run the test suite, use:
```bash
composer run-script test
```

## Development Conventions

*   The project uses **FilamentPHP** for its admin panel. Development of admin features should be done within the Filament framework.
*   **Roles and permissions** are managed by the `spatie/laravel-permission` package.
*   **Real-time events** are broadcast using Laravel Reverb.
*   The application is structured with different panels for different user roles (Admin, Padre, Psicologo).
*   There is a Python script (`heart_rate_monitor.py`) that likely interacts with the `/api/heart-rate` endpoint.
