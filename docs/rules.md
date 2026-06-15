# Extended Rules & Conventions

- **Roles & Permissions:**
	- Define roles and permissions in config or DB
	- Use Laravel policies/gates for access control

- **Calculations:**
	- Centralize business logic in service classes

- **Sandbox Use:**
	- Always use sandbox/test environments for third-party integrations

- **.env Rules:**
	- Never commit .env files
	- Only use env() in config files, not in app logic

- **Files to Ignore:**
	- Add sensitive/config files to .gitignore

- **CDN Usage:**
	- Use CDN for common libraries/assets where possible

- **Optimized Queries:**
	- Avoid N+1 queries, use eager loading
	- Use query builder/Eloquent, avoid raw SQL

- **API & DB Security:**
	- Validate and sanitize all inputs
	- Use Laravel features for security (CSRF, XSS, SQL injection protection)

---
# Rules

This document lists the rules and conventions to be followed in the project.

## General Rules
- Do not commit sensitive information (passwords, API keys)
- All code must be reviewed before merging
- Use environment variables for configuration

## API Rules
- Use RESTful conventions for endpoints
- Return proper HTTP status codes
- Document all endpoints in API docs

## Database Rules
- Use migrations for schema changes
- Use Eloquent relationships for associations
- Avoid raw SQL unless necessary

## Frontend Rules
- Use Blade templates for server-rendered views
- Use Vite for asset bundling
- All custom CSS must be written in public/assets/style.css (or a single main CSS file). Do not use inline CSS or scatter styles across multiple files.
- You may use any CSS framework (e.g., Tailwind, Bootstrap, etc.)
