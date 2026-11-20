# NexClinic — Modernisation Roadmap

**Branch:** `feat/schema-sectors`
**Date:** 2026-08-07
**Purpose:** Assess the codebase against current standards for a commercial hospital operations system, and fix the order of work. Companion to [NEXCLINIC_ROLE_FUNCTIONALITY.md](NEXCLINIC_ROLE_FUNCTIONALITY.md), which defines *what* each sector does; this document defines *what must be built before that is sellable*.

**UI architecture is governed by [NEXCLINIC_WORKSPACES.md](NEXCLINIC_WORKSPACES.md)** (mandatory, 2026-08-07): every role gets an independently designed workspace — dashboard, sidebar, homepage, quick actions — never a shared dashboard with items hidden. That mandate *depends on* this roadmap's order: Accounting's workspace cannot be built before Phase B, and Pharmacy's inventory widgets cannot be built before Phase P, because the data behind them does not exist. See its §4 dependency matrix.

**Build order (set 2026-08-07):** Reception → Insurance & billing → Auditing → Pharmacy inventory. Sequenced by buyer value, not by technical layer.

**Justification standard:** every item below is argued against the six questions — does it change *operations*, *workflow*, *roles*, *dashboards*, *reports*, or *analytics*? Items that answer none of the six do not enter a phase.

---

## 1. Baseline — measured, not estimated

| Measure | Value |
|---------|-------|
| PHP files | 285 (~31,200 LOC excl. vendor) |
| Pages in `backend/admin/` root | 107 |
| Pages in `backend/admin/{sector}/` | 93 across 10 sector folders |
| Pages in `backend/doc/` | 50 |
| Sector pages calling an authorize function | **2 of 93** (both in `reception/`) |
| Prepared statements / raw `query()` | 271 / 12 |
| String-concatenated SQL found | 0 |
| Automated tests | 0 (one syntax linter: `backend/scripts/lint_sector_pages.php`) |
| CI pipelines | 0 |
| Dependency manifest (`composer.json`) | none |

### The pattern reception established is the right one

`backend/admin/reception/` is the first sector built to a repeatable shape, and the shape is sound:

- **`backend/admin/_init.php`** — one bootstrap for every page (session, config, security, paths, sectors, login check). Replaces the four hand-copied `include()` lines at the top of every legacy page.
- **`backend/assets/inc/nex_reception.php`** — the domain module. ~24 functions holding queries, KPIs, queue logic, and state transitions. **This is the first place in the codebase where data access lives outside the page**, and it is what makes the sector testable.
- **`his_admin_reception_action.php`** — one POST endpoint, one `switch`, authorize-then-act per branch, flash-redirect out. Correct shape: no state change happens in a GET.
- **`nex_dashboard_ui.php`** — shared KPI/alert components against the design system, so eleven sectors do not grow eleven card markups.
- **`his_visits`** — real `DATETIME` columns, real FKs to `his_patients`/`his_appointments`, indexed for the queue. Materially better than the legacy tables around it.

**Every later phase should copy this structure.** It is the template, not a one-off.

### Two findings against it

> **F1 — reception authorization fails open.**
> `nex_reception.php:499` — `nex_reception_authorize()` returns `true` when `$_SESSION['nex_user_id']` is empty, falling back to "portal login is enough."
> A legacy admin or doctor session has no `nex_user_id` unless `nex_bootstrap_session_from_legacy()` matched a `nex_users` row. That is the normal state today. **So in the deployment that exists right now, every reception authorization call returns `true` before reaching the kernel** — and `nex_authz.php`'s default-deny never runs.
> The three policies (`reception.dashboard`, `visit.checkin`, `visit.manage`) are correctly defined in `nex_policies()`. They are simply not reached. The shim is a deliberate compatibility choice, but it must not be the state at sale; it needs a sunset that is enforced, not intended.

> **F2 — the sector move copied pages, it did not retire them.**
> 93 pages now exist under sector folders while 107 remain in `backend/admin/` root, and `backend/doc/` still holds 50. The old URLs still resolve and still carry no authorization. Moving `his_admin_manage_patient.php` into `clinical/` does not stop anyone from requesting the root copy.
> Of the 93 sector pages, **91 call no authorize function at all.**

Neither finding is a design error. Both are the same gap: the kernel is written, the sectors are laid out, and the wiring between them is only 2 pages deep.

---

## 2. Gap register

### Tier 1 — blocks selling to a real hospital

| # | Gap | Evidence | Consequence |
|---|-----|----------|-------------|
| T1.1 | Authorization enforced on 2 of 200 admin pages | §1, F1 + F2 | Any authenticated user reads every patient record |
| T1.2 | No billing or insurance domain | one `invoice` match repo-wide, in a payroll file; `his_accounts` = 3 demo rows, `acc_amount varchar(200)` | A hospital that cannot invoice will not buy an operations system |
| T1.3 | No login hardening | no MFA, lockout, rate-limit, idle timeout, or password policy | Credential stuffing is unopposed |
| T1.4 | Permanent SHA1 fallback | `backend/security.php:151` | Unbounded downgrade path |
| T1.5 | No backup / restore / DR procedure | nothing in `SETUP.md` beyond a pre-Stage-B `mysqldump` | First question every hospital IT director asks |
| T1.6 | Audit is decision-level, not change-level | `nex_audit_access()` logs grant/deny; no before/after values on writes | Regulatory and malpractice inquiries need the second one |
| T1.7 | No encryption at rest, no key management | — | Standard procurement checklist item |

### Tier 2 — blocks the product from scaling

**T2.1 — Triple duplication.** Root (107) + sectors (93) + doc portal (50). Every feature exists two or three times; every fix must be applied two or three times; every authorization gap must be closed two or three times. This is the largest structural tax in the repo and it compounds with each sector built.

**T2.2 — No API layer.** Pages are `session_start()` → SQL → HTML. `nex_reception.php` is the first break in that pattern and shows the way out. No integration point yet for mobile, patient portal, lab analyzers, ministry feeds, or AI.

**T2.3 — No tests, no CI, no migration runner.** `lint_sector_pages.php` checks syntax only — it cannot tell you a page lost its authorization. Migrations are hand-pasted `mysql <` commands with no record of which database received which.

**T2.4 — No environment separation or observability.** No staging, structured logging, error aggregation, or uptime signal.

### Tier 3 — the data model (gates reporting, billing, and AI alike)

| # | Gap | Evidence |
|---|-----|----------|
| T3.1 | Clinical content as HTML blobs | `lab_pat_results`, `pres_ins`, `mdr_pat_prescr` are `longtext` holding `<ul><li>` — seed rows still carry pasted Google search URLs |
| T3.2 | No coded vocabularies | no ICD-10/11, SNOMED, LOINC, ATC/RxNorm; `pat_ailment` is free-text |
| T3.3 | No encounter spine | `his_visits` is the first piece; nothing yet binds notes + orders + results + charges |
| T3.4 | Money as `varchar` | `acc_amount`, `pay_emp_salary`, `phar_qty`, `eqp_qty` |
| T3.5 | Dates as `varchar`, age stored not derived | `pat_dob varchar(200)` = `02/02/1992` (ambiguous MM/DD); `pat_age` already stale in seed data |
| T3.6 | Core tables are `latin1` | `his_patients`, `his_medical_records`, `his_prescriptions`, `his_laboratory` vs. a `utf8mb4` connection — guaranteed mojibake on French and Cameroonian names |
| T3.7 | Denormalised patient copies | name/age/address re-copied into prescriptions, lab, and medical records |
| T3.8 | No pharmacy batch / lot / expiry | `his_pharmaceuticals` has 6 columns and no lot or expiry — see Phase P |
| T3.9 | No stock ledger, no double-entry | quantities are overwritten in place; no movement history |

---

## 3. Strategy — strangle, do not rewrite

A framework rewrite would burn a year re-deriving domain work that already exists: the two-axis authority model, sector boundaries, the data-ownership spine, care relationships, and the audit design. That work is the asset.

Reception has now proved the incremental path: sector folder + domain module + `_init.php` + action handler. Each phase below is a **vertical slice** — schema, domain module, pages, authorization policies, and audit coverage shipped together for one domain. A slice is not done when the screens render; it is done when the wrong role is provably refused.

**Definition of done, every phase:**
1. Schema migration with typed columns and real FKs.
2. Domain module in `backend/assets/inc/` — no SQL in pages.
3. Pages under `backend/admin/{sector}/`, built on `_init.php`.
4. `nex_authorize()` called on every entry point, with policies added to `nex_policies()`.
5. **The legacy root and `doc/` copies deleted** — a slice that leaves its duplicates behind has not closed anything.
6. Role×page test proving the denials.

---

## 4. Phases

### Phase R — Reception *(in progress)*

*Six questions: operations, workflow, roles, dashboards.*

The queue spine, KPIs, check-in/walk-in/emergency, and no-show handling are built. To finish:

1. **Close F1.** Make `nex_reception_authorize()` delegate unconditionally to `nex_authorize()`. Requires every reception user to have a `nex_users` row — so seed/backfill that first, then remove the shim. Until it is removed, reception has no enforcement.
2. **Close F2 for reception.** Delete the root copies of `his_admin_add_appointment.php`, `his_admin_appointments.php`, `his_admin_manage_appointment.php`, `his_admin_register_patient.php`, `his_admin_update_single_patient.php`, and the two stray `his_admin_reception_*.php` files in root. Verify no `href` still points at them.
3. **Test harness.** Authenticate as each role, assert 200/403 across reception pages. This is the artifact that makes phases B/A/P safe; build it here, extend it per phase.
4. Register/lookup flow against `his_visits`, and appointment emails via the existing `notification_events.php`.

**Exit:** a reception clerk cannot open a clinical page, a doctor cannot open the reception queue, and both denials are in `nex_audit_log`.

---

### Phase B — Insurance & billing

*Six questions: operations, reports, analytics. **The single largest gap between this system and a sellable one.***

Nothing exists today: one `invoice` match in the whole repo, and it is in a payroll file.

**Schema (all `DECIMAL`, never `varchar`):**

| Table | Holds |
|-------|-------|
| `nex_tariffs` | priced service catalogue — consultation, lab test, procedure, drug, bed-day |
| `nex_payers` | cash, CNPS, mutuelles, private insurers, employer schemes |
| `nex_patient_coverage` | patient ↔ payer, policy number, validity dates, coverage rate |
| `nex_invoices` | header — patient, visit, payer split, status |
| `nex_invoice_lines` | one line per tariff item, with the coverage split resolved at billing time |
| `nex_payments` | cash, mobile money (MTN MoMo / Orange Money), bank, payer settlement |
| `nex_claims` | payer submission, status, rejection reason, settlement date |

**Workflow to wire:** charges accrue against the visit (Phase R's `his_visits`) as each sector acts — consultation, lab order, prescription dispense, bed-day. Reception or cashier settles at discharge. This is what makes `his_visits` load-bearing rather than a queue widget.

**Why it ranks here:** it converts NexClinic from a record-keeping tool into the system the hospital's revenue passes through. That is the difference between a nice-to-have and a line item in next year's budget. It also produces the first numbers a director actually wants on a dashboard: revenue per sector, outstanding receivables, claim rejection rate, payer mix.

**Do not skip:** the money columns must be `DECIMAL(14,2)` with an explicit currency (XAF), and every posting immutable — corrections are reversing entries, never `UPDATE`s. Retrofitting that is a data migration you will not enjoy.

**Exit:** a patient can be invoiced across sectors for one visit, part-covered by a payer, part-paid in cash, with a claim raised and reconciled.

---

### Phase A — Auditing

*Six questions: roles, reports. The compliance gate.*

`nex_audit_log` and `nex_audit_access()` are well built and log **decisions** — who was granted or denied what. Missing is the **change record**: what the value was before, what it became, and who changed it.

1. **Change-level auditing.** `nex_audit_change(table, pk, before, after)` on every write in every domain module. Because Phase R put writes in modules rather than pages, there is now one place per domain to add it — this is the payoff for that structure.
2. **Immutability.** Append-only. No `UPDATE`/`DELETE` grant on `nex_audit_log` for the application DB user. Consider a hash chain over `(prev_hash, row)` so tampering is detectable.
3. **Retention & privacy.** Define how long audit rows live and who may read them; `detail` currently holds a raw `json_encode($ctx)` that can carry patient identifiers, which makes the audit log itself sensitive data.
4. **Audit reporting.** Superadmin: full log, break-glass review, override history. Admin: operations subset. Per the ownership spine in the role spec. Without a reader, the log is write-only and nobody trusts what they cannot inspect.
5. **Break-glass review workflow.** `break_glass` is recorded; nothing surfaces it for review. An emergency override nobody reviews is just an unlogged bypass with extra steps.

**Why after billing:** money movement is the highest-value thing to audit, so build the change-log once the financial tables exist and cover both at the same time.

**Exit:** any record's full change history is reconstructible, and a superadmin can answer "who saw this patient's file last month" from a screen.

---

### Phase P — Pharmacy inventory

*Six questions: operations, workflow, reports, analytics. Also patient safety.*

Today `his_pharmaceuticals` is six columns — name, barcode, description, `phar_qty varchar(200)`, category, vendor. **No lot, no expiry, no movement history.** Quantity is overwritten in place, so there is no way to answer "where did 200 units go."

1. **`nex_drug_batches`** — lot number, expiry date, quantity received, quantity remaining, unit cost, supplier. Dispensing picks a batch (FEFO — first-expiry-first-out), not a global counter.
2. **`nex_stock_movements`** — append-only ledger: receipt, dispense, transfer, adjustment, expiry write-off, return. Current stock becomes a *sum over the ledger*, not a mutable column. Same discipline as Phase B's postings.
3. **Dispensing decrements stock.** Today `prescription.dispense` is a policy with no inventory behind it. Closing the Rx → dispense → stock loop is the point of the phase.
4. **Expiry and reorder alerts** on the pharmacy dashboard via `nex_alert_panel()` — expiring within 90 days, below reorder level, stock-out.
5. **Purchase orders and goods receipt** against `his_vendor`.
6. **Typed columns:** quantities `INT`, costs `DECIMAL`. Lines up with Phase B so dispensed drugs bill correctly.

**Why it earns its place:** expired-stock waste and stock-outs are money and mortality in a regional hospital, and both are invisible today. It is also the phase with the clearest measurable before/after for a buyer — wastage down, stock-out days down.

**Exit:** every unit in the pharmacy is traceable to a batch and a movement, dispensing draws down real stock, and expiring lots are flagged before they expire.

---

### Cross-cutting — runs alongside, not after

These have no phase of their own because every phase needs them:

- **`composer.json` + PSR-4 autoloading** — do it at the start of Phase B; the domain modules are already shaped for it.
- **Migration runner with an applied-migrations table** — 11 migrations now exist with no record of which database has which. Do this before Phase B adds seven tables.
- **Delete duplicates as each sector is finished** (F2). Not a cleanup task — an authorization task.
- **Role×page test harness** — built in Phase R, extended by every phase after.
- **`utf8mb4` conversion** — before billing writes patient names onto invoices.

### Later — API, then AI

JSON API over the finished model (§5 depends on it). `efile_fhir.php` becomes conformant rather than a shaped subset.

---

## 5. AI — where it pays, and what it requires

**Prerequisite, stated plainly:** AI cannot help until the data is structured and coded. A model pointed at `<ul><li>Quinine sulfate</li></ul>` in a `longtext` column produces a demo, not a product. **Structured, coded data is the AI strategy**; the model is the last component, not the first. Phases B, A, and P each move data from blobs and `varchar` into typed, coded, ledgered form — that work *is* the AI groundwork, which is why none of it is deferred for AI's sake.

### Ranked by buyer value against regional-hospital economics

| # | Capability | Why it earns its place |
|---|-----------|------------------------|
| 1 | **Ambient clinical documentation** — speech (FR/EN, incl. code-switching) → structured note with ICD-10 candidates | Largest time-return in healthcare AI, *and* self-reinforcing: the cheapest route to the structured data everything else needs. Clinician confirms before save, always. |
| 2 | **Natural-language reporting** — "malaria admissions by division, Q2" → text-to-SQL over a read-only, schema-restricted view | High value to whoever signs the purchase order; near-zero clinical risk. Becomes far more valuable once Phase B makes revenue queryable. |
| 3 | **Coding assist** — suggest ICD-10/LOINC from note text; human accepts or rejects | Makes ministry reporting and epidemiological analysis possible at all; also drives Phase B billing accuracy |
| 4 | **Paper-record OCR intake** | Target hospitals hold years of paper. Bulk digitisation is a sellable migration service and a moat against foreign systems that assume you are already digital. |
| 5 | **Demand and stock forecasting** on Phase P's movement ledger | Only possible once stock has a history. Directly reduces stock-outs and expiry waste. |
| 6 | **Patient messaging in local languages** — SMS/WhatsApp reminders, lab-ready, prep instructions | Cheap, measurable against no-show rate — which Phase R now actually tracks. `notification_events.php` plumbing exists. |

### Do not build these with a language model

- **Drug-interaction and dose checking must be deterministic** — a rules engine over a drug knowledge base, naturally sited in Phase P. Use a model only to *explain* an alert in plain language. A hallucinated "no interaction found" is a dead patient.
- **Early-warning scores** (NEWS2, sepsis) are arithmetic over vitals. Ship the rules; more accurate and more defensible.
- **Never auto-diagnose or auto-prescribe.** Suggestion plus human confirmation, or it does not ship.

### Four constraints specific to this market

1. **Connectivity.** If the internet drops, the hospital keeps running. Every AI feature degrades to "unavailable" without blocking the workflow beneath it. Architectural requirement, not a feature flag.
2. **Data residency.** Sending Cameroonian patient data to a foreign endpoint is a legal and trust question to answer *before* building. De-identify before the call, or run smaller models locally.
3. **Cost per encounter.** Price per-encounter AI cost against what a regional hospital can pay. This kills more health-AI features than accuracy ever does.
4. **Log every AI suggestion and whether the clinician accepted it.** Both the liability defence and the quality dataset. Phase A is where that capability gets built — design `nex_audit_change` so an AI actor is representable from the start.

---

## 6. Summary

- Reception set the right pattern: `_init.php`, a domain module holding the SQL, one POST action handler, shared UI, a typed and FK'd table. **Copy it for B, A, and P.**
- The gap is not design, it is wiring: 2 of 200 admin pages enforce authorization, and the reception shim (F1) means even those two are inert in today's deployment.
- Moving a page into a sector folder does not close a hole while the root copy still answers (F2). Deleting duplicates is authorization work, not tidying.
- Billing is the largest distance between this and a system a hospital pays for.
- Build the role×page test harness in Phase R. Every phase after it is safer and faster for having it.
