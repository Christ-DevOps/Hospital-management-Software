# Stage B verification report

**Branch:** `feat/schema-sectors`  
**Base tag:** `demo-checkpoint` (`1eaa4a7`)  
**Date:** 2026-07-21  

## Decision statement

**Present `demo-checkpoint`** for the 22 Aug 2026 demo.

Stage B schema + sector model are implemented on this branch but **not verified** against a live MySQL instance in this environment (Docker Desktop unavailable; no local `mysql` client). Do not merge to main until the checklist below passes.

## Row counts (fill after migration)

| Table | Before | After | Notes |
|-------|--------|-------|-------|
| his_patients | | | |
| his_laboratory (with pat_id) | | | unmatched legacy rows keep `pat_id` NULL |
| his_prescriptions (with pat_id) | | | |
| his_medical_records (with pat_id) | | | |
| his_surgery (with pat_id/doc_id) | | | |
| his_payrolls (with doc_id) | | | |
| his_appointments | | | FKs added |
| his_vitals (with pat_id) | | | |
| nex_users | | | should equal admins + docs |
| nex_user_assignments | | | |
| nex_sectors | | | expect 10 |
| nex_facilities | | | expect 1 (HQ) |

SQL sketch:

```sql
SELECT COUNT(*) FROM his_patients;
SELECT COUNT(*) FROM his_laboratory WHERE pat_id IS NOT NULL;
SELECT COUNT(*) FROM nex_users;
SELECT COUNT(*) FROM nex_user_assignments;
```

## Login matrix (fill after migration)

| Account | Identifier | Password | Legacy login | Sector picker | Notes |
|---------|------------|----------|--------------|---------------|-------|
| Admin | admin@mail.com | Password@123 | ☐ | ☐ | system admin → all sectors |
| Doctor | YDS7L | password | ☐ | ☐ | mapped from doc_dept |
| Doctor | BKTWQ | password | ☐ | ☐ | Laboratory |
| Doctor | 5VIFT | password | ☐ | ☐ | Accounting |

Auth gate rule: every migrated admin/doctor must still log in with current password before retiring old login tables.

## PHP files touched (Stage B)

### Migrations / docs
- `Database/migrations/2026_08_21_stage_b_relational.sql`
- `Database/migrations/2026_08_21_stage_b_sectors.sql`
- `Database/migrations/2026_08_21_stage_b_down.sql`
- `docs/STAGE_B_MIGRATION.md`
- `docs/STAGE_B_VERIFICATION.md`
- `SETUP.md`
- `backend/.env.example`

### Sector model / auth
- `backend/assets/inc/nex_sectors.php`
- `backend/admin/index.php`
- `backend/doc/index.php`
- `backend/admin/assets/inc/checklogin.php`
- `backend/doc/assets/inc/checklogin.php`
- `backend/admin/assets/inc/sidebar.php`
- `backend/doc/assets/inc/sidebar.php`
- `backend/admin/his_admin_sector_picker.php`
- `backend/admin/his_admin_sector_dashboard.php`
- `backend/admin/his_admin_manage_nex_users.php`
- `backend/doc/his_doc_sector_picker.php`
- `backend/doc/his_doc_sector_dashboard.php`

### Clinical writes now set `pat_id` / `doc_id`
- `backend/admin/his_admin_add_single_lab_test.php`
- `backend/admin/his_admin_add_single_pres.php`
- `backend/admin/his_admin_add_single_patient_medical_record.php`
- `backend/admin/his_admin_theatre_single_patient.php`
- `backend/doc/his_doc_add_single_lab_test.php`
- `backend/doc/his_doc_add_single_pres.php`

## Status

| Checkpoint | Result |
|------------|--------|
| Stage A tagged `demo-checkpoint` | **Yes** |
| Stage B branch created | **Yes** (`feat/schema-sectors`) |
| Stage B verified on main/demo | **No** |
| **What to present 22 Aug** | **`demo-checkpoint`** |
