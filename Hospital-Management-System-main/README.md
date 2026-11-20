# NexClinic

Sector-based hospital operations: patients, clinical care, pharmacy, laboratory, accounting, payroll, theatre, and reporting.

## Setup

See [SETUP.md](SETUP.md) for installation, PHP extensions, database import, and deployment.

Role & sector specification: [docs/NEXCLINIC_ROLE_FUNCTIONALITY.md](docs/NEXCLINIC_ROLE_FUNCTIONALITY.md)

Modernisation roadmap & gap register: [docs/NEXCLINIC_ROADMAP.md](docs/NEXCLINIC_ROADMAP.md)

Role-based workspace architecture (mandatory): [docs/NEXCLINIC_WORKSPACES.md](docs/NEXCLINIC_WORKSPACES.md)

## Login URLs

- **Admin:** `backend/admin/index.php`
- **Staff / Doctor:** `backend/doc/index.php`

## Default Credentials

| Role | Identifier | Password |
|------|------------|----------|
| Admin | `newadmin@hospital.local` | `Admin@Hospital2026` |
| Doctor | `DOC001` | `Doctor@2026` |

Change default passwords before production.

## Security

CSRF protection, bcrypt password hashing, secure sessions, POST-based deletes, and hardened uploads. See `backend/security.php`.
