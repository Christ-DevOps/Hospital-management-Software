# NexClinic — Role-Based Experience Architecture

**Status:** MANDATORY. Set by product owner 2026-08-07.
**Scope:** Governs every dashboard, sidebar, navigation, and homepage in the platform.
**Related:** [NEXCLINIC_ROLE_FUNCTIONALITY.md](NEXCLINIC_ROLE_FUNCTIONALITY.md) (permissions & data ownership) · [NEXCLINIC_ROADMAP.md](NEXCLINIC_ROADMAP.md) (build order & data dependencies)

---

## 0. The mandate

NexClinic Enterprise is **not a single dashboard shown to different users**. It is a collection of specialised workspaces.

Every role owns: dashboard · navigation · homepage · sidebar · widgets · KPIs · shortcuts · colours · workflow · notifications · reports · quick actions.

> **Never reuse the same dashboard across multiple roles by simply hiding menu items.**
> Each workspace must be independently designed.

**UX principle:** the software should feel like multiple specialised products sharing one backend. The Receptionist should never feel like a Doctor. The Doctor should never feel like an Accountant. The Pharmacist should never feel like an Administrator.

Every workspace must immediately communicate its purpose through layout, navigation, terminology, widgets, dashboards, and workflow.

---

## 1. Current state — measured 2026-08-07

The codebase today is the precise anti-pattern this mandate forbids.

| Fact | Value |
|------|-------|
| Sidebars in the entire platform | **2** (`backend/admin/assets/inc/sidebar.php`, `backend/doc/assets/inc/sidebar.php`) |
| Role/sector awareness in the admin sidebar | **0** occurrences of `nex_has_sector` / `nex_active_sector` / `nex_platform_tier` across 263 lines |
| Sidebar sections shown to every admin-portal user | Main Menu · Clinic · HRM · Finance & Accounts · Administration · Settings |
| Sector dashboards that include the shared sidebar | all of them, reception included ([dashboard:54](../backend/admin/reception/his_admin_reception_dashboard.php:54)) |

**A receptionist currently sees HRM, Finance, Administration, and Settings menus.** Not hidden — present and clickable. That is both a UX failure against this mandate and an authorization surface (see F1/F2 in the roadmap).

`nex_sector_modules()` in `nex_sectors.php` is the only role-aware navigation data that exists, and nothing renders a sidebar from it.

---

## 2. Workspace catalogue

### 2.1 Reception — *Patient Intake & Flow Management*

**Users:** Receptionists
**Objectives:** fast registration · appointment scheduling · queue management · arrival tracking · referral handling · walk-in registration · emergency registration
**UI philosophy:** simple · fast · minimal typing · large action buttons · queue-focused

| Widget | Status |
|--------|--------|
| Today's Appointments | ✅ built |
| Current Queue | ✅ built |
| Patients Waiting | ✅ built |
| Average Registration Time | ✅ built |
| Walk-ins | ✅ built |
| Emergencies | ✅ built |
| Missed Appointments | ✅ built |
| Quick Patient Search | ✅ built (2026-08-07) |
| Quick Registration | ⚠️ link only — not an inline quick-create |
| **Upcoming Appointments** | ❌ only today's schedule exists |
| **Quick Appointment** | ❌ no inline booking on the dashboard |
| **Referral handling** | ❌ no referral concept anywhere in the schema |
| **Role-specific sidebar** | ❌ uses the shared 6-section admin sidebar |

### 2.2 Doctor — *Clinical Care*

**Users:** Doctors
**Objectives:** consult · review history · prescribe · order labs · review results · follow-ups
**UI philosophy:** information-rich · timeline-oriented · fast access to records · minimal navigation

Today's Patients · Critical Patients · Current Queue · Recent Admissions · Pending Laboratory Results · Pending Radiology Results · Upcoming Follow-ups · Recent Consultations · Clinical Alerts · Quick Prescription · Quick Consultation · Patient Timeline

### 2.3 Nursing — *Patient Monitoring*

**Users:** Nurses
**Objectives:** monitor patients · medication administration · vitals · shift management · transfers · ward monitoring
**UI philosophy:** task-oriented · timeline · checklists · alerts first

Assigned Patients · Medication Schedule · Vitals Due · Critical Patients · Ward Occupancy · Transfer Requests · Shift Notes · Pending Tasks · Patient Alerts

### 2.4 Laboratory — *Laboratory Operations*

**Users:** Laboratory technicians
**Objectives:** receive requests · track samples · validate results · release reports
**UI philosophy:** workflow pipeline · status indicators · processing queue

Pending Samples · Samples Collected · Tests in Progress · Results Awaiting Validation · Critical Results · Machine Status · Laboratory Revenue · Turnaround Time · Quality Alerts

### 2.5 Pharmacy — *Medication Management*

**Users:** Pharmacists
**Objectives:** dispense · inventory control · supplier management · stock forecasting
**UI philosophy:** inventory-first · quick dispensing · barcode ready

Pending Prescriptions · Low Stock · Expiring Drugs · Today's Sales · Drug Consumption · Supplier Orders · Inventory Value · Revenue

### 2.6 Accounting — *Financial Operations*

**Users:** Cashiers · Accountants · Finance managers
**Objectives:** billing · payments · insurance · financial reporting
**UI philosophy:** financial dashboard · charts · invoices · receipts

Revenue Today · Outstanding Bills · Insurance Claims · Cash Flow · Daily Transactions · Payment Methods · Financial Alerts · Monthly Revenue

### 2.7 Administration — *Hospital Operations*

**Users:** Hospital administrators
**Objectives:** manage activities · monitor departments · manage employees · monitor KPIs
**UI philosophy:** operations centre · business intelligence · real-time monitoring

Hospital Activity · Department Performance · Bed Occupancy · Staff Attendance · Appointments · Admissions · Discharges · Critical Alerts · Inventory Alerts · Revenue Summary · Operational KPIs

### 2.8 Super Admin — *Platform Administration*

**Users:** Platform owners · IT administrators
**Objectives:** manage platform · hospital configuration · security · system monitoring · audit
**UI philosophy:** enterprise SaaS · infrastructure monitoring · configuration-first

Hospitals · Users · Licenses · System Health · Database Status · Storage Usage · Audit Logs · Security Events · Backups · API Integrations · Platform Analytics

---

## 3. Open reconciliation — needs a decision

### 3.1 Eight workspaces vs eleven sectors

This mandate names **8** workspaces. `NEXCLINIC_ROLE_FUNCTIONALITY.md` defines **11** sectors, and `nex_sectors.php` ships 10 module entries. The unmatched ones:

| Sector (existing) | Workspace in this mandate | Resolution needed |
|---|---|---|
| HR | — | fold into Administration, or its own workspace? |
| Payroll | — | fold into Accounting or Administration? |
| Inventory | — | Pharmacy covers drugs; who owns equipment/supplies? |
| Theatre | — | fold into Doctor/Nursing, or its own? |
| Clinical (single sector) | **split** into Doctor + Nursing | matches existing `clinical_rank` (chief/doctor/nurse) — the authz kernel already distinguishes nurses in `prescription.create` |

The Clinical split is already supported by the data model. The other four are genuine product decisions and currently have pages built under `backend/admin/{hr,payroll,inventory,theatre}/` with no workspace defined for them.

### 3.2 Colour system

"Colours (where appropriate)" per workspace needs a decision against `docs/HMS-DESIGN-SYSTEM.md`, which currently defines one palette. Recommendation: one shared palette with a **per-workspace accent** — enough to make a workspace instantly recognisable without eight unrelated visual languages to maintain, and it keeps the shared components in `nex_dashboard_ui.php` usable everywhere.

---

## 4. Data dependencies — what each workspace needs that does not exist

**This is the critical planning fact: most of these dashboards cannot be built yet, because the data behind them does not exist.** Building the widget first produces a fake dashboard.

| Workspace | Widgets blocked | Blocked on |
|---|---|---|
| Reception | Upcoming Appointments, Referrals | small schema add (referrals table) |
| Doctor | Pending Radiology Results | **no radiology domain at all** |
| Doctor | Critical Patients, Clinical Alerts | no acuity/severity field |
| Doctor | Recent Admissions, Follow-ups | no encounter spine (T3.3) |
| Nursing | Assigned Patients | nurse↔patient assignment (`nex_care_relationship` may cover) |
| Nursing | Medication Schedule | **no MAR** (medication administration record) |
| Nursing | Ward Occupancy | **no wards, no beds** |
| Nursing | Shift Notes, Pending Tasks | **no shift model** |
| Laboratory | Turnaround Time | no sample-received / result-released timestamps |
| Laboratory | Machine Status | no analyzer/equipment status link |
| Laboratory | Laboratory Revenue | **Phase B (billing)** |
| Pharmacy | Low Stock, Expiring Drugs, Inventory Value | **Phase P (batches/lots/expiry, stock ledger)** |
| Pharmacy | Today's Sales, Revenue | **Phase B** |
| Accounting | *every widget* | **Phase B** |
| Administration | Bed Occupancy | no wards/beds |
| Administration | Staff Attendance | no attendance model |
| Administration | Revenue Summary | **Phase B** |
| Super Admin | Licenses, System Health, Storage, Backups, API Integrations | none exist |

**Consequence:** the workspace mandate does not replace the roadmap's build order — it *depends* on it. Reception is buildable now. Accounting is not buildable until Phase B. Pharmacy's inventory widgets are not buildable until Phase P.

---

## 5. Implementation approach

Building eight independently-designed workspaces by hand-writing eight sidebars and eight layouts would triple the duplication problem the roadmap already flags (T2.1). The mandate says *never reuse the same dashboard* — it does not say never share a layout primitive. The distinction matters:

- **Shared:** the shell mechanics — layout grid, card component, table styling, the KPI card in `nex_dashboard_ui.php`.
- **Per-workspace and never shared:** which widgets appear, their order, terminology, quick actions, sidebar contents, accent colour, homepage.

### 5.1 The workspace shell

One resolver, driven by the user's sector and platform tier:

```
backend/assets/inc/nex_workspace.php
  nex_workspace_for_user()      → 'reception' | 'doctor' | 'nursing' | ...
  nex_workspace_nav($ws)        → sidebar sections for THAT workspace only
  nex_workspace_quick_actions($ws)
  nex_workspace_theme($ws)      → accent colour, icon, label
```

Then `sidebar.php` renders **only** what `nex_workspace_nav()` returns. A receptionist's sidebar contains reception items and nothing else — not hidden, **not emitted**. This closes the UX mandate and shrinks the authorization surface at the same time.

### 5.2 Per-workspace pages

Each workspace keeps its own dashboard file and its own domain module, following the pattern reception established:

```
backend/admin/{workspace}/            ← pages
backend/assets/inc/nex_{workspace}.php ← queries, KPIs, workflow
```

`nex_reception.php` is the reference implementation.

### 5.3 Order of work

1. **Build the shell** (`nex_workspace.php` + role-resolved `sidebar.php`). One change, fixes the mandate violation for every workspace at once, and unblocks all eight.
2. **Retrofit reception** onto the shell and close its 4 remaining widget gaps (§2.1). Proves the pattern.
3. **Doctor and Nursing** — highest daily usage; most widgets buildable against existing clinical data.
4. **Then follow the roadmap build order** — Accounting after Phase B, Pharmacy's inventory widgets after Phase P, since §4 shows their dashboards are otherwise empty shells.
5. **Super Admin last** — its widgets depend on infrastructure that does not exist yet.

---

## 6. Definition of done — per workspace

A workspace ships only when all of the following are true:

1. Own dashboard file, own domain module, no SQL in the page.
2. Own sidebar contents from `nex_workspace_nav()` — other workspaces' items are **not emitted**, not CSS-hidden.
3. Own homepage/landing route for that role.
4. Own quick actions and terminology.
5. Own accent colour per §3.2.
6. `nex_authorize()` on every entry point.
7. Legacy root/`doc/` duplicates of its pages deleted.
8. Role×page test proving another role gets 403.
