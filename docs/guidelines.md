# Extended Guidelines

- **Variable Naming:** Use meaningful, camelCase for variables, StudlyCase for classes, snake_case for DB columns.

- **Migration & Seeder Practices:**
	- Always use migrations for DB changes
	- Add comments to DB columns where needed
	- Use seeders for test/setup data

- **Git Workflow:**
	- Use feature branches for new work
	- Write clear, descriptive commit messages
	- Never commit sensitive data or .env files
	- Follow a clear branch naming convention (e.g., feature/xyz, bugfix/abc)

- **Helpers & Traits:** Place common functions in helpers (app/helpers.php) or traits (app/Traits/)

- **Logs & API Structure:**
	- Use Laravel logging for errors and important events
	- Structure API responses with status, message, and data fields
	- Always use valid HTTP response codes

- **Token Structure:**
	- Store tokens securely
	- Remove device/user tokens on logout

- **Responsive Design:**
	- Use Tailwind CSS and test on all devices

- **Code Comments:**
	- Add comments for complex logic in code and DB migrations

- **2FA Process:**
	- Document 2FA enable/disable process in user profile
	- Always use lang files for 2FA messages

---
# Guidelines

This document outlines best practices and coding standards for the project.

## Coding Standards
- Follow PSR-12 for PHP code style
- Use meaningful variable and function names
- Keep functions and classes small and focused
- Use Laravel's built-in features where possible

## Git Workflow
- Use feature branches for new work
- Write clear, descriptive commit messages
- Pull and merge main branch regularly

## Testing
- Write unit and feature tests for new code
- Use factories and seeders for test data
- Run tests before pushing changes

## Documentation
- Comment complex logic
- Update this document as standards evolve

## Security
- Validate all user input
- Use Laravel's built-in authentication and authorization
- Store sensitive data in environment variables
