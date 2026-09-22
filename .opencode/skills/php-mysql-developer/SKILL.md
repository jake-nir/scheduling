---
name: php-mysql-developer
description: Develop and maintain PHP and MySQL web applications using XAMPP, HTML5, CSS, Bootstrap or Tailwind CSS, JavaScript, and MySQL. Use when building, modifying, debugging, or extending PHP/MySQL systems.
---

# PHP + MySQL Developer

## Technology

Default stack:

- PHP
- MySQL/MariaDB
- XAMPP
- HTML5
- CSS
- Bootstrap or Tailwind CSS
- JavaScript
- PDO or MySQLi

Prefer simple maintainable architecture unless the project specifies otherwise.

## Development Rules

Before changing code:

1. Inspect the existing project structure.
2. Identify the relevant files.
3. Understand existing database tables.
4. Check existing authentication.
5. Check existing role permissions.
6. Preserve working functionality.

Do not unnecessarily rewrite the entire project.

## Database

Use prepared statements.

Never construct SQL queries using raw user input.

Validate:

- IDs
- Dates
- Numeric values
- Required fields
- Enum/status values

Use transactions for multi-step operations where appropriate.

## Authentication

Use secure password hashing:

```php
password_hash()
password_verify()