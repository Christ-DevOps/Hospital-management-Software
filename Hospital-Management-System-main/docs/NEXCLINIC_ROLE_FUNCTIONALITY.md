# NexClinic — Role & Functionality Analysis (per Sector)

**Purpose:** Define every role's responsibilities and concrete functionalities BEFORE implementing dashboards, so access boundaries, workflows, and screens are clear and non-overlapping.

**Rule of thumb:** each role sees only its own sector's work; shared data (the patient record) is read by many but written by few.

**Status:** Spec locked for implementation. Stage A demo presents classic admin/doctor portals; sector dashboards roll out per build order below.

---

## Cross-cutting requirements (apply to ALL roles/forms)

**Security & regulation (every form):**
- CSRF token on every state-changing form.
- Prepared statements for all DB access (no string-interpolated SQL).
- Server-side validation on every field + `escape_output()` on every echo.
- Server-side authorization: logged-in user's sector assignment must permit the action (re-checked on the server).
- Input constraints enforced server-side: required fields, formats, allowed value sets.
- Sensitive actions written to the audit log (who, what, when). Superadmin actions especially heavily audited.

**Date & time formats (everywhere):**
- Dates: `DATE` type, display `DD/MM/YYYY`.
- Times/timestamps: `DATETIME`/`TIMESTAMP`, display `HH:MM` or `DD/MM/YYYY HH:MM`.
- Date inputs: Flatpickr; time: time picker or validated `HH:MM`.
- Store canonical values in DB; format only at display time.

**Notifications (email):**
- Appointment book/confirm → email patient and/or doctor.
- Account create/invite and password reset → email.
- Lab-result-ready and appointment reminders → email where address exists.
- Email failures must not roll back the underlying action; log the failure.

---

## Authority model — TWO separate axes

**Axis 1 — Platform tier (power over the SYSTEM):**
| Tier | Who | Power |
|------|-----|--------|
| `superadmin` | Owner + trusted IT | Platform control, mint Admins/superadmins, facilities, sector catalogue, SMTP, full audit |
| `admin` | Hospital directors / senior management | Create standard staff, assign sectors, cross-sector oversight, KPIs |
| `standard` | Everyone else | Power only from sector assignment |

**Axis 2 — Sector assignment (WHAT work you do):**
- Assignment = `(facility, sector, role)` with `role` = `lead` | `staff`
- Clinical ranks: `chief` | `doctor` | `nurse`

**Account creation (tiered):**
- Superadmin → creates Admins (and other superadmins); may also create staff.
- Admin → creates standard staff + assignments; **cannot** create Admins/Superadmins.
- Sector leads / standard staff → cannot create accounts.
- Every create/assignment change audited.

---

## Sectors & roles (summary)

| # | Sector / tier | Writes | Must not |
|---|---------------|--------|----------|
| 1a | Superadmin | Facilities, sectors, admins, system config, full audit | Clinical authorship |
| 1b | Admin (hospital) | Staff accounts, assignments, oversight reports | Platform tech settings; Admin minting |
| 2 | Reception | Patient demographics, appointments, check-in | Clinical notes, Rx, lab, finance, HR |
| 3a | Clinical chief | Own clinical work + read-all review notes | Edit others' authored notes; accounts |
| 3b | Clinical doctor | Own notes, Rx, lab orders, admission/discharge | Other doctors' patients; stock/payroll |
| 4 | Nurse (clinical) | Vitals; view care instructions | Prescribe, diagnose, finance |
| 5 | Pharmacy | Catalogue, stock, dispense | Write Rx, clinical notes |
| 6 | Laboratory | Results against requests | Order tests, prescribe |
| 7 | Accounting | Invoices, payments, AP/AR | Clinical, dispense, HR |
| 8 | HR | Staff records, attendance, leave | Clinical, login accounts |
| 9 | Payroll | Salary runs, payslips | Clinical, patient billing |
| 10 | Inventory | Supplies, equipment, vendors, movements | Drug dispense, clinical |
| 11 | Theatre | Surgery cases / schedule | (often clinical scope) |

### Data ownership spine

| Data | Written by | Read by |
|------|------------|---------|
| Patient demographics | Reception | Clinical + billing |
| Clinical notes | Doctor | Doctor, Nurse (view); Chief (all, read) |
| Vitals | Nurse / Doctor | Clinical |
| Prescriptions | Doctor | Pharmacy |
| Dispensing / drug stock | Pharmacy | Pharmacy, reports |
| Lab requests | Doctor | Laboratory |
| Lab results | Laboratory | Doctor |
| Appointments | Reception (+ Doctor) | Reception, Doctor |
| Invoices / payments | Accounting | Accounting, reports |
| Staff / attendance / leave | HR | HR, System |
| Payroll | Payroll | Payroll, System |
| Supplies / equipment / vendors | Inventory | Inventory, System |
| Standard accounts | Admin | Admin, Superadmin |
| Admin accounts | Superadmin | Superadmin |
| Facilities, sectors, config | Superadmin | Superadmin |
| Audit log | System | Superadmin (full), Admin (ops subset) |

---

## Suggested build order

1. **Reception** — patients + appointments (+ emails)
2. **Clinical (Doctor)** — history, notes, Rx, lab orders
3. **Pharmacy** + **Laboratory** — close the loop
4. **Accounting** — invoices/payments
5. **HR / Payroll / Inventory** — back office
6. **System Admin** account-creation + assignment — seed one Superadmin; build early enough to staff other dashboards

*Seed at least one Superadmin directly so the creation chain can start.*

---

## Demo note (July 2026)

Until sector dashboards ship, present **Stage A**: classic admin + doctor portals with Cameroon patient fields and appointments. Do not claim full sector isolation in the live demo until Axis 1/2 enforcement is verified.
