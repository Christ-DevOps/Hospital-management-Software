# NexClinic — Setup Guide

## Requirements

- PHP 8.0+ with extensions: `mysqli`, `mysqlnd`, `session`, `openssl`, `mbstring`, `fileinfo`
- MySQL 5.7+ or MariaDB 10.3+
- Apache 2.4+ (recommended) or Nginx + PHP-FPM

## Quick Start (XAMPP / WAMP on Windows)

1. **Enable mysqli** in `php.ini`:
   ```ini
   extension=mysqli
   ```
   Restart Apache.

2. **Copy project** to your web root (use the inner folder containing `index.php`):
   ```
   Hospital-Management-System-main/Hospital-Management-System-main/
   ```

3. **Create database** and import schema:
   ```bash
   mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS \`hospital-management\`;"
   mysql -u root -p hospital-management < Database/hmisphp.sql
   ```

   For existing databases imported from the old dump, also run:
   ```bash
   mysql -u root -p hospital-management < Database/migrations/001_align_schema_and_passwords.sql
   ```

   **Stage A (demo-checkpoint)** — Cameroon patient fields + appointments:
   ```bash
   mysql -u root -p hospital-management < Database/migrations/2026_08_20_stage_a_additive.sql
   ```

   **Stage B (`feat/schema-sectors` only)** — backup first, then:
   ```bash
   mysqldump -u root -p hospital-management > backup_pre_stage_b.sql
   mysql -u root -p hospital-management < Database/migrations/2026_08_21_stage_b_relational.sql
   mysql -u root -p hospital-management < Database/migrations/2026_08_21_stage_b_sectors.sql
   ```
   Reverse: `Database/migrations/2026_08_21_stage_b_down.sql` (prefer restore from backup).
   See `docs/STAGE_B_VERIFICATION.md`.

   **Prescription → pharmacy dispense** (structured drug lines + stock deduction):
   ```bash
   mysql -u root -p hospital-management < Database/migrations/2026_08_07_prescription_dispense.sql
   ```

4. **Configure environment** — copy and edit:
   ```bash
   cp backend/.env.example backend/.env
   ```

5. **Ensure upload directories are writable**:
   - `backend/admin/assets/images/users/`
   - `backend/doc/assets/images/users/`

6. **Access the application**:
   - Landing: `http://localhost/Hospital-Management-System-main/`
   - Admin: `http://localhost/Hospital-Management-System-main/backend/admin/`
   - Doctor: `http://localhost/Hospital-Management-System-main/backend/doc/`

## Default Credentials

| Role | Identifier | Password |
|------|------------|----------|
| Admin | `newadmin@hospital.local` | `Admin@Hospital2026` |
| Doctor | `DOC001 / Doctor@2026` |

Change these immediately after first login in production.

## PHP Built-in Server (development only)

Run from the **inner** project folder (the one that contains `index.php` and `backend/`):

```bash
cd Hospital-Management-System-main
php -S 127.0.0.1:6000
```

Then open:

- Landing: `http://127.0.0.1:6000/`
- Admin login: `http://127.0.0.1:6000/backend/admin/index.php`
- Create admin: `http://127.0.0.1:6000/backend/admin/his_admin_register.php`
- Doctor login: `http://127.0.0.1:6000/backend/doc/index.php`

Patient registration pages require login first (Admin → Patients → Register, or Doctor → Register Patient).

## Production Checklist

- [ ] HTTPS enabled (`.htaccess` redirects HTTP to HTTPS when Apache `mod_rewrite` is on)
- [ ] `backend/.env` uses a dedicated DB user (not `root`)
- [ ] `display_errors = Off` in production `php.ini`
- [ ] Default passwords changed
- [ ] `backend/.env` is not web-accessible (blocked by `backend/.htaccess`)

## Docker (optional)

```bash
docker compose up -d
```

Then import the database into the MySQL container and open `http://localhost:8080`.

## Troubleshooting

| Error | Fix |
|-------|-----|
| `Class "mysqli" not found` | Enable `extension=mysqli` in `php.ini` and restart the web server |
| `Call to undefined method mysqli_stmt::get_result()` | Enable `mysqlnd` (bundled with most PHP builds) |
| Database connection failed | Check `backend/.env` credentials and that the database exists |
| Login fails on fresh import | Re-run `Database/migrations/001_align_schema_and_passwords.sql` |
