## Essential Standards & Best Practices

- **Laravel Version:** Code standards must strictly follow the current Laravel version in use (e.g., Laravel 12/13 best practices). Always refer to the official documentation for updates and conventions.

- **CSS & JS Separation:** All CSS and JS must be maintained in their respective files (e.g., resources/css/style.css and resources/js/). Do not write CSS or JS inside Blade files. Use Vite for bundling and optimization.

- **Security & Performance:**
	- Always protect against SQL injection by using Eloquent ORM or Laravel's query builder. Never use raw queries unless absolutely necessary, and always sanitize inputs.
	- Focus on optimizing queries and code for performance.

- **Responsiveness:** The portal must be fully responsive, ensuring usability across all devices and screen sizes.

- **Validation:** Both client-side (e.g., validate.js) and server-side (Laravel Form Requests) validations are mandatory for all forms and user inputs.

- **Code Simplicity:** CSS, HTML, and PHP code should be simple, clean, and easily understandable. Avoid unnecessary complexity.

- **Database Structure:** All database changes must be managed using Laravel migrations. Never modify the database schema directly.

---

# Features

This document provides an overview of the main features implemented in the project, along with standards and practices to be followed for each.

## User Management
- User registration and login
- Profile management
- Password reset
- 2FA (Two-Factor Authentication):
	- Users can enable/disable 2FA from their profile settings.
	- 2FA status is stored in the database and can be toggled securely.
	- Always provide clear instructions and feedback using lang files for all 2FA actions.

## Notifications
- In-app and email notifications
- Notification preferences
- All notification messages (success/error/info) must be stored in lang/message files for consistency and localization.

## API
- RESTful endpoints for all resources
- API Versioning: Use versioned route files (e.g., routes/api_v1.php) to manage breaking changes and upgrades.
- Always return proper HTTP status codes and structured JSON responses.

## Media Handling
- File uploads and storage
- Image processing (if applicable)
- Ensure all file uploads are validated and sanitized.

## Security
- Authentication and authorization
- Input validation and sanitization (both client-side and server-side)
- Device/User tokens must be removed on logout for security.
- Prevent back navigation after logout to avoid unauthorized access.
- Use Laravel middleware for route protection.

## Admin Panel
- User and content management
- Analytics and reporting
- Common header and footer must be used in all admin views for consistency.
- Email templates should be managed centrally and referenced in all email notifications.

## CSS & JS Standards
- All custom CSS must be written in resources/css/style.css only. Do not use inline CSS in Blade files.
- Tailwind CSS is allowed and can be used alongside custom CSS.
- All custom JS should be in resources/js/ and not inline in Blade files.
- Use Vite for asset bundling and optimization.

## Composer Usage
- Run `composer install` after cloning the project or when dependencies are added to composer.json.
- Run `composer update` only when updating dependencies or after changing composer.json. Always review changes before pushing.

## Validation
- All forms (insert/update) must have both client-side (e.g., validate.js) and server-side (Laravel Form Request classes) validation.
- Use reusable Request classes for validation logic.
- Always sanitize and validate user input before saving to the database.

## SweetAlert Usage
- Use SweetAlert for all user-facing alerts (success, error, confirmation, etc.) for a consistent UI experience.
- Create a common layout/component for displaying messages using SweetAlert.

## Responsive Design
- The portal must be fully responsive and tested on all major devices and browsers.
- Use Tailwind CSS utilities and custom media queries as needed.

## Email Templates
- All email notifications must use standardized templates with a common header and footer.
- Templates should be stored in resources/views/emails/ and referenced in mail logic.

## Lang Files
- All user-facing messages (success, error, info) must be stored in lang/en/messages.php or similar lang files.
- Never hardcode messages in controllers, views, or JS.

## Logout & Token Handling
- On logout, always remove device/user tokens from the database.
- Prevent browser back navigation to protected pages after logout.

## General
- Follow Laravel and PSR-12 coding standards.
- Optimize queries and avoid N+1 problems.
- Use migrations for all DB changes.
- Document all major features and changes in project_journey.md.

Refer to guidelines.md and rules.md for more detailed standards and practices.
