# Stage B migration runbook

## Before you start

1. Confirm tag `demo-checkpoint` exists and is the known-good demo fallback.
2. Work only on branch `feat/schema-sectors`.
3. Take a full dump:

```bash
mysqldump -u root -p hospital-management > backup_pre_stage_b.sql
```

## Apply

```bash
mysql -u root -p hospital-management < Database/migrations/2026_08_21_stage_b_relational.sql
mysql -u root -p hospital-management < Database/migrations/2026_08_21_stage_b_sectors.sql
```

## After apply

1. Log in as admin (`admin@mail.com`) and doctor (`YDS7L`) with existing passwords.
2. Confirm sector picker appears when multiple assignments exist.
3. Optionally set `NEX_SECTORS_ENFORCE=1` in `backend/.env` to deny cross-sector URLs.
4. Fill row-count and login matrix in `docs/STAGE_B_VERIFICATION.md`.

## Rollback

Prefer restore from `backup_pre_stage_b.sql`. Emergency SQL-only reverse:

```bash
mysql -u root -p hospital-management < Database/migrations/2026_08_21_stage_b_down.sql
```

## Notes

- Legacy `his_admin` / `his_docs` login paths remain; `nex_users` mirrors password hashes.
- Clinical child tables keep legacy name/number columns for display; new writes also set `pat_id` / `doc_id`.
- Mairie is not a sector.
