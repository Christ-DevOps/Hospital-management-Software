# Hospital Management System — Setup Guide

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
| Admin | `admin@mail.com` | `Password@123` |
| Doctor | `YDS7L` | `password` |

Change these immediately after first login in production.

## PHP Built-in Server (development only)

```bash
cd Hospital-Management-System-main
php -S localhost:8080
```

Visit `http://localhost:8080/`

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
