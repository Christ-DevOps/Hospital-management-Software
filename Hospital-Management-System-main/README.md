# Hospital Management System

Facility management for hospital operations: patients, employees, pharmacy, laboratory, accounting, payroll, surgery/theatre, and reporting.

## Setup

See [SETUP.md](SETUP.md) for installation, PHP extension requirements, database import, and deployment.

## Login URLs

- **Admin:** `backend/admin/index.php`
- **Doctor:** `backend/doc/index.php`

## Default Credentials

| Role | Identifier | Password |
|------|------------|----------|
| Admin | `admin@mail.com` | `Password@123` |
| Doctor | `YDS7L` | `password` |

Change default passwords before deploying to production.

## Security

This project includes CSRF protection, bcrypt password hashing, secure session settings, POST-based deletes, and hardened file uploads. See `backend/security.php` for shared helpers.


## Credentials
 Admin login (newadmin@hospital.local / Admin@Hospital2026)
 Doctor login (DOC001 / Doctor@2026)