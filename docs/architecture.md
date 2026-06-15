# Extended Architecture & Structure

- **Project Folder Structure:**
	- `app/`: Controllers, Models, Services, Traits, Jobs, Observers, Notifications
	- `routes/`: API and web route files, grouped by module and version (e.g., api_v1.php)
	- `config/tables.php`: All table names must be referenced from here, never hardcoded
	- `app/Traits/`: Common reusable traits
	- `app/Jobs/`: Queued jobs for background processing
	- `app/Observers/`: Model observers for event handling
	- `app/Notifications/`: Notification classes for email, SMS, etc.
	- `app/Mail/`: Email templates and logic
	- `app/Middleware/`: Custom and core middleware for route protection
	- `resources/views/`: Blade templates, organized by module
	- `resources/views/emails/`: Centralized email templates with common header/footer
	- `resources/css/style.css`: All custom CSS
	- `resources/js/`: All custom JS
	- `database/migrations/`: All DB schema changes via migrations
	- `database/seeders/`: Seed data for testing and setup

- **Middleware:** Use middleware for authentication, authorization, and back navigation prevention after logout.

- **Traits:** Place reusable logic in traits under `app/Traits/` and use them across models/controllers as needed.

- **Jobs & Observers:** Use jobs for background tasks (e.g., emails, notifications) and observers for model event handling.

- **Notifications:** Centralize notification logic in `app/Notifications/` and always use lang files for messages.

- **2FA:** Implement 2FA logic in user profile, with enable/disable options and secure DB storage.

- **API Versioning:** Maintain separate route files for each API version. Document version changes in project_journey.md.

- **Third-Party Integrations:** Use service classes for third-party APIs. Always use sandbox/test environments for development.

- **Database:** All schema changes must use migrations. Never edit DB directly.

- **Security:** Use Laravel features to prevent SQL injection, XSS, and CSRF. Remove tokens on logout.

- **Responsiveness:** Ensure all views are responsive using Tailwind CSS and custom CSS.

---
# Architecture

This document describes the high-level architecture of the project, including its main components and their interactions.

## Overview
- Laravel-based backend (MVC structure)
- RESTful API endpoints
- Database migrations and seeders
- Service providers and middleware

## Main Components
- Controllers: Handle HTTP requests and responses
- Models: Represent database entities
- Views: Blade templates for frontend (if any)
- Routes: Define API and web endpoints
- Services: Business logic and integrations

## Folder Structure
- `app/`: Application core (Controllers, Models, Services, etc.)
- `config/`: Configuration files
- `database/`: Migrations, seeders, factories
- `public/`: Entry point and assets
- `resources/`: Views, CSS, JS
- `routes/`: Route definitions
- `storage/`: Logs, cache, file uploads
- `tests/`: Unit and feature tests

## Data Flow
1. Request enters via `public/index.php`
2. Routed to appropriate controller
3. Controller interacts with models/services
4. Response returned to client

## Key Technologies
- Laravel PHP Framework
- MySQL (or compatible DB)
- Composer (dependency management)
- Vite/Tailwind (frontend assets)
