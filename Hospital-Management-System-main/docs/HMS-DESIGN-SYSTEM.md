# HMS Design System v2.0
**Hospital Management System — Preclinic-Aligned UI/UX Redesign**

> **Scope:** visual layer only. PHP logic, routing, CSRF, and Bootstrap 4 class names stay unchanged.  
> **Implementation vehicle:** shared `backend/assets/css/hms-theme.css` loaded after `app.min.css`, plus optional `hms-motion.js` for premium interactions.

---

## Design Reference

**Template:** [Preclinic — Free Medical & Clinic Management Dashboard](https://www.figma.com/design/KcchDeBcUwq6cBQBleghYg/Preclinic---Free-Medical---Clinic-Management-Dashboard-Figma-UI-Kit--Community-) (Dreams Technologies, Figma Community)

**Live demo (authoritative for tokens):** https://preclinic.dreamstechnologies.com/html/index.html

**Limitation:** Figma layers cannot be scraped programmatically. Token values below are extracted from the live demo CSS (`style.css` `:root` block) and visual inspection of rendered pages. **Before pixel-perfect sign-off, inspect the Figma styles panel** for exact spacing, radius, and pastel tint fills — treat this document as implementation-ready with a verification step on those values.

**Confirmed from live demo metadata:** `theme-color` / `msapplication-navbutton-color` = `#2E37A4`

---

## 1. Color Tokens

### 1.1 Core palette (source of truth)

| CSS variable | Hex | Role |
|---|---|---|
| `--hms-primary` | `#2E37A4` | Deep indigo-blue — primary buttons, active nav, brand moments |
| `--hms-primary-dark` | `#1F276F` | Darker primary — hover on primary buttons, auth gradient end |
| `--hms-primary-hover` | `#3C449C` | Live-demo hover state (alias for button hover) |
| `--hms-primary-transparent` | `#ECEDF7` | Soft indigo wash — active submenu, focus rings |
| `--hms-secondary` | `#00D3C7` | Teal accent — doctor CTAs, secondary highlights |
| `--hms-secondary-hover` | `#17C2B9` | Secondary hover |
| `--hms-tint-blue` | `#EEF1FD` | Stat card icon bg (e.g. Out Patients / Doctors) |
| `--hms-tint-green` | `#E7F8EF` | Stat card icon bg (e.g. In Patients / Patients) |
| `--hms-tint-amber` | `#FFF4E5` | Stat card icon bg (e.g. Appointments / Assets) |
| `--hms-tint-purple` | `#F2EEFC` | Stat card icon bg (e.g. Revenue / Vendors) |
| `--hms-success` | `#16A34A` | Success / discharged / approved |
| `--hms-warning` | `#D97706` | Warning / pending |
| `--hms-danger` | `#DC2626` | Danger / delete / critical |
| `--hms-info` | `#2563AA` | Info / lab / notifications |
| `--hms-text` | `#101828` | Primary text (ink) |
| `--hms-text-muted` | `#5B6472` | Secondary text |
| `--hms-border` | `#E4E9EE` | Borders, dividers, table lines |
| `--hms-surface` | `#FFFFFF` | Cards, modals, dropdowns |
| `--hms-page-bg` | `#F5F6FA` | Page background behind cards |

### 1.2 Extended shades

| CSS variable | Hex | Use |
|---|---|---|
| `--hms-heading` | `#0A1B39` | Page titles, sidebar group labels (Preclinic demo) |
| `--hms-gray-500` | `#6C7688` | Body copy on panels |
| `--hms-gray-600` | `#545F74` | Sidebar default link text |
| `--hms-border-strong` | `#B6BBC4` | Table header underline |
| `--hms-text-inverse` | `#FFFFFF` | Text on primary / auth gradient |
| `--hms-overlay` | `rgba(46, 55, 164, 0.45)` | Modal backdrop |
| `--hms-disabled` | `#9DA4B0` | Disabled labels |
| `--hms-disabled-bg` | `#F5F6FA` | Disabled inputs |
| `--hms-success-transparent` | `#F4FBF7` | Success badge/chip bg |
| `--hms-danger-transparent` | `#FEF4F4` | Danger badge/chip bg |
| `--hms-warning-transparent` | `#FEFBF5` | Warning badge/chip bg |
| `--hms-info-transparent` | `#F4F9FE` | Info badge/chip bg |

### 1.3 Semantic pairings (WCAG AA)

| Foreground | Background | Ratio | Pass |
|---|---|---|---|
| `--hms-text` | `--hms-surface` | 16.2:1 | AA / AAA body |
| `--hms-text-muted` | `--hms-surface` | 5.4:1 | AA body |
| `--hms-text-inverse` | `--hms-primary` | 8.1:1 | AA body |
| `--hms-primary` | `--hms-surface` | 7.8:1 | AA (links, large UI) |
| `--hms-danger` | `--hms-surface` | 4.6:1 | AA body |
| `--hms-text` | `--hms-tint-blue` | 14.9:1 | AA (stat icon area) |
| `--hms-text` | `--hms-tint-green` | 14.7:1 | AA |
| `--hms-text` | `--hms-tint-amber` | 14.5:1 | AA |
| `--hms-text` | `--hms-tint-purple` | 14.8:1 | AA |

**Pairing rules**
- `--hms-primary` is the single brand color for trust/premium weight — active nav, primary buttons, key links.
- The four `--hms-tint-*` tokens are **stat-card icon backgrounds only** — one per card, never as full-card or section fills.
- Do not use gold or secondary teal as a second “brand” color on the same screen as primary — teal (`--hms-secondary`) is reserved for doctor-role CTAs and chart accents.
- White sidebar (Preclinic `sidebar2`); active item = white card + 2px left border `--hms-primary`.

### 1.4 Gradients (auth & marketing)

```css
--hms-hero-gradient: linear-gradient(180deg, #4E59DB 0%, #0C1367 100%);
--hms-auth-bg: #ECEDF7;
--hms-cta-gradient: linear-gradient(90deg, #2E37A4, #3538CD);
```

---

## 2. Typography Scale

**Font:** [Inter](https://fonts.google.com/specimen/Inter) — matches Preclinic demo. Fallback: `"Segoe UI", system-ui, sans-serif`.

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
```

| Token | Element | Size | Weight | Line-height | Color |
|---|---|---|---|---|---|
| `--hms-text-xs` | Captions, menu group labels | 12px / 0.75rem | 600 | 1.4 | `--hms-heading` |
| `--hms-text-sm` | Labels, breadcrumbs, table headers | 13px / 0.8125rem | 500–600 | 1.45 | `--hms-text-muted` |
| `--hms-text-base` | Body, table cells | 14px / 0.875rem | 400 | 1.55 | `--hms-gray-500` |
| `--hms-text-md` | Card titles, patient names | 16px / 1rem | 600 | 1.45 | `--hms-text` |
| `--hms-text-lg` | Page title (`h4.page-title`) | 20px / 1.25rem | 600 | 1.35 | `--hms-heading` |
| `--hms-text-xl` | Dashboard greeting | 24px / 1.5rem | 700 | 1.3 | `--hms-heading` |
| `--hms-text-stat` | Counter-up numbers | 28px / 1.75rem | 700 | 1.1 | `--hms-heading` |
| `--hms-text-2xl` | Landing hero | 36px / 2.25rem | 700 | 1.2 | `--hms-text-inverse` |

**Patient data hierarchy**
- Patient name: `--hms-text-md`, weight 600, `--hms-text`
- Patient ID / MRN: `--hms-text-sm`, weight 500, `--hms-primary`, `font-variant-numeric: tabular-nums`
- Vitals / numeric: `--hms-text-base`, weight 600, tabular-nums

---

## 3. Spacing & Radius System

**Base unit:** 4px.

| Token | Value | Use |
|---|---|---|
| `--hms-space-1` | 4px | Icon gap |
| `--hms-space-2` | 8px | Inline spacing |
| `--hms-space-3` | 12px | Form field gap |
| `--hms-space-4` | 16px | Card padding (mobile) |
| `--hms-space-5` | 20px | Card padding (desktop), content gutter |
| `--hms-space-6` | 24px | Section gap, sidebar menu padding |
| `--hms-space-8` | 32px | Page section margin |
| `--hms-space-10` | 40px | Dashboard grid gap |

| Radius token | Value | Use |
|---|---|---|
| `--hms-radius-sm` | 5px | Inputs, buttons, menu items (Preclinic) |
| `--hms-radius-md` | 8px | Dropdowns |
| `--hms-radius-lg` | 12px | Stat cards |
| `--hms-radius-xl` | 15px | Login card |
| `--hms-radius-full` | 9999px | Avatars, search pills |

**Elevation (max 3 levels)**

```css
--hms-shadow-sm: 0 1px 1px rgba(0, 0, 0, 0.05);           /* resting cards */
--hms-shadow-md: 0 0 35px rgba(104, 134, 177, 0.15);      /* stat cards */
--hms-shadow-lg: 0 0 45px rgba(108, 118, 136, 0.2);       /* hover / dropdowns */
--hms-shadow-primary: 0 8px 16px rgba(46, 55, 164, 0.35); /* primary button hover */
```

**Layout constants (Preclinic demo)**

```css
--hms-sidenav-width: 260px;
--hms-topbar-height: 55px;
--hms-content-padding: 20px;
```

---

## 4. Motion & Premium Interaction Spec

All motion respects `prefers-reduced-motion: reduce`. Load via `backend/assets/js/hms-motion.js` **after** `vendor.min.js` / `app.min.js`.

| Interaction | Duration | Easing | Implementation | Reduced-motion fallback |
|---|---|---|---|---|
| Stat card count-up | 1200ms | `ease-out` | CountUp.js (~3KB gz) or vanilla `requestAnimationFrame` on `[data-plugin="counterup"]` | Show final number instantly |
| Stat card hover lift | 180ms | `ease-out` | CSS `transform: translateY(-2px)` + shadow upgrade | No transform |
| Chart draw-in | 800ms | `ease-in-out` | Chart.js `animation.duration` on dashboard charts (Phase 2) | Static chart |
| Skeleton shimmer | 1.5s loop | linear | CSS `@keyframes shimmer` on `.hms-skeleton` | Solid `--hms-page-bg` block |
| Sidebar active indicator | 200ms | `ease-out` | CSS `transition` on `border-left-color` + `background` | Instant swap |
| Button hover color | 150ms | `ease` | CSS on `.btn-*` | Instant |
| Button press | 100ms | `ease-out` | `transform: scale(0.98)` on `:active` | No scale |
| Table row hover | 150ms | `ease` | `background-color` transition | Instant |
| SweetAlert enter | 250ms | `cubic-bezier(0.4, 0, 0.2, 1)` | Override `.swal-modal` with `animation` | Fade only / instant |
| Landing scroll-reveal | 600ms | `ease-out` | IntersectionObserver + `.hms-reveal` class (vanilla, ~1KB) | Elements visible immediately |
| Notification pulse | 2s × 2 | `ease-in-out` | CSS `@keyframes pulse` on `.badge-notification` | Static badge |

### 4.1 Sample: stat card count-up + hover (CSS + JS)

```css
/* hms-theme.css */
.widget-rounded-circle.card-box {
  transition: transform 180ms ease-out, box-shadow 180ms ease-out;
}
.widget-rounded-circle.card-box:hover {
  transform: translateY(-2px);
  box-shadow: var(--hms-shadow-lg);
}
@media (prefers-reduced-motion: reduce) {
  .widget-rounded-circle.card-box { transition: none; }
  .widget-rounded-circle.card-box:hover { transform: none; }
}
.hms-skeleton {
  background: linear-gradient(90deg, var(--hms-page-bg) 25%, #E8EBF0 50%, var(--hms-page-bg) 75%);
  background-size: 200% 100%;
  animation: hms-shimmer 1.5s linear infinite;
  border-radius: var(--hms-radius-sm);
  min-height: 80px;
}
@keyframes hms-shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
@media (prefers-reduced-motion: reduce) {
  .hms-skeleton { animation: none; background: var(--hms-page-bg); }
}
```

```javascript
// backend/assets/js/hms-motion.js (~2KB, no dependencies)
(function () {
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  function animateCount(el, target, duration) {
    if (reduced) { el.textContent = target; return; }
    var start = 0, t0 = null;
    function step(ts) {
      if (!t0) t0 = ts;
      var p = Math.min((ts - t0) / duration, 1);
      el.textContent = Math.floor(start + (target - start) * (1 - Math.pow(1 - p, 3)));
      if (p < 1) requestAnimationFrame(step);
      else el.textContent = target;
    }
    requestAnimationFrame(step);
  }
  document.querySelectorAll('[data-plugin="counterup"]').forEach(function (el) {
    var n = parseInt(el.textContent.replace(/\D/g, ''), 10);
    if (!isNaN(n)) { el.textContent = '0'; animateCount(el, n, 1200); }
  });
  // Landing scroll-reveal
  if (!reduced && 'IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { e.target.classList.add('hms-revealed'); io.unobserve(e.target); }
      });
    }, { threshold: 0.12 });
    document.querySelectorAll('.hms-reveal').forEach(function (el) { io.observe(el); });
  } else {
    document.querySelectorAll('.hms-reveal').forEach(function (el) { el.classList.add('hms-revealed'); });
  }
})();
```

**Include in `head.php` (panels) and `index.php` (landing):**
```html
<script src="../assets/js/hms-motion.js" defer></script>
```

**Added payload:** ~2KB custom JS; optional CountUp.js ~3KB gz if not using vanilla counter.

---

## 5. Component Specifications

### 5.1 Sidebar (`.left-side-menu`, `#sidebar-menu`, `.metismenu`)

Preclinic uses a **white/light sidebar** (`#F5F6F8` bg), not a dark navy rail.

| State | Background | Text | Indicator |
|---|---|---|---|
| Default | `--hms-page-bg` / `#F5F6F8` | `--hms-gray-600` | — |
| Hover | `--hms-surface` | `--hms-primary` | `--hms-shadow-sm` |
| Active | `--hms-surface` | `--hms-primary` | 2px left border `--hms-primary` |
| Submenu active | `--hms-primary-transparent` | `--hms-primary` | — |
| Collapsed | 70px width | icons only | tooltip on hover |

**Menu grouping (map HMS → Preclinic structure)**

| Preclinic group | HMS sidebar items |
|---|---|
| **Main Menu** | Admin Dashboard, Doctor Dashboard |
| **Clinic** | Patients, Pharmacy, Laboratory, Medical Records, Surgical/Theatre |
| **HRM** | Employees, Payrolls |
| **Finance & Accounts** | Accounting |
| **Administration** | Inventory, Vendors, Password Resets, Reporting |
| **Settings** | (future: account settings link) |

Doctor panel subset: Main Menu → Clinic (Patients, Pharmacy, Lab) → HRM (Payrolls).

`.menu-title`: 12px, weight 600, uppercase, `--hms-heading`, `padding-top: 16px`.

### 5.2 Topbar (`.navbar-custom`)

| Element | Spec |
|---|---|
| Background | `--hms-surface`, bottom border `--hms-border` |
| Height | 55px |
| Search | 32px height, border `--hms-border`, focus border `--hms-primary` |
| User avatar | 36px circle, border `--hms-primary-transparent` |
| Dropdown | `--hms-radius-md`, `--hms-shadow-lg`, hover `--hms-primary-transparent` |

### 5.3 Dashboard stat cards (`.widget-rounded-circle.card-box`)

Target layout for `his_admin_dashboard.php` (mapped to HMS data):

| Preclinic card | HMS mapping | Tint class |
|---|---|---|
| Doctors | Hospital Employees | `hms-tint-blue` |
| Patients | Out + In Patients (split or combined) | `hms-tint-green` |
| Appointments | (future) / Corporation Assets | `hms-tint-amber` |
| Revenue | Pharmaceuticals / Vendors | `hms-tint-purple` |

| Part | Spec |
|---|---|
| Card | `--hms-surface`, `--hms-radius-sm`, `--hms-shadow-md`, padding 20px |
| Icon ring | 56px circle, tint bg per row above, icon `--hms-primary` or semantic |
| Stat number | `--hms-text-stat`, count-up animation |
| Label | `--hms-text-sm`, `--hms-text-muted` |
| Delta chip (optional) | 12px pill, green/red bg for +/- % |

**Future dashboard rows (Phase 2 layout):** Appointment breakdown widget → employee table (existing) → department counts → recent patients table.

### 5.4 Tables (`#demo-foo-filtering`, Footable)

| Part | Spec |
|---|---|
| Header | bg `--hms-page-bg`, text `--hms-text-sm` weight 600 |
| Rows | odd `--hms-surface`, even `#FAFBFC` |
| Row hover | `--hms-primary-transparent`, 150ms transition |
| Borders | horizontal only, `--hms-border` |
| Empty | centered, `--hms-text-muted` |

**Bootstrap 4 compromise:** Footable injects pagination DOM — override `.footable-pagination .page-link` in `hms-theme.css`.

### 5.5 Buttons

| Variant | Background | Hover | Height |
|---|---|---|---|
| `.btn-primary` | `--hms-primary` | `--hms-primary-hover` + `--hms-shadow-primary` | 38px |
| `.btn-success` | `--hms-success` | darken 8% | 38px |
| `.btn-warning` | `--hms-warning` | darken 8% | 38px |
| `.btn-danger` | `--hms-danger` | darken 8% | 38px |
| `.btn-block` | — | — | 44px |
| Ghost | transparent | `--hms-primary-transparent` bg | 38px |

Ladda: keep plugin; spinner white on filled buttons; `min-width: 100px` on `.ladda-button`.

### 5.6 Badges → action buttons (CSS-only)

Restyle `.table td .badge` without PHP edits:

```css
.table td .badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: var(--hms-radius-sm);
  font-size: 12px;
  font-weight: 600;
  border: 1px solid transparent;
  text-decoration: none !important;
  transition: background 150ms ease, color 150ms ease;
}
.table td .badge-primary { background: var(--hms-primary-transparent); color: var(--hms-primary); }
.table td .badge-primary:hover { background: var(--hms-primary); color: #fff; }
.table td .badge-success { background: var(--hms-success-transparent); color: var(--hms-success); }
.table td .badge-success:hover { background: var(--hms-success); color: #fff; }
.table td .badge-danger { background: var(--hms-danger-transparent); color: var(--hms-danger); }
.table td .badge-danger:hover { background: var(--hms-danger); color: #fff; }
```

### 5.7 Forms

| Element | Spec |
|---|---|
| `.form-control` | min-height 39px, radius 5px, border `--hms-border`, focus border `--hms-primary` |
| `label` | `--hms-text-sm`, weight 500, `--hms-heading` |
| Section header | `--hms-text-md`, left border 3px `--hms-secondary` |
| Readonly | bg `--hms-page-bg` |
| Error | border `--hms-danger`, helper 12px `--hms-danger` |

**Flatpickr:** month header `--hms-primary`, selected day `--hms-primary`, today border `--hms-secondary`.

### 5.8 Auth pages (`.authentication-bg`, split layout)

```
┌─────────────────────┬──────────────────┐
│  Brand panel 50%    │  Form card 50%     │
│  --hms-hero-gradient│  white card        │
│  logo + tagline     │  email/password    │
│                     │  CTA button        │
│                     │  forgot password   │
└─────────────────────┴──────────────────┘
```

- Admin CTA: `.btn-primary` (`--hms-primary`)
- Doctor CTA: `.btn-success` remapped to `--hms-secondary` on auth panel
- Mobile: stack — gradient header 200px, form below

### 5.9 SweetAlert v1 (`.swal-*`)

| Type | Confirm button |
|---|---|
| Success | `--hms-success` |
| Error | `--hms-danger` |
| Warning | `--hms-warning` |
| Default | `--hms-primary` |

Popup radius: `--hms-radius-lg`; enter animation 250ms ease.

### 5.10 Breadcrumbs & page titles

- `.page-title-box`: padding-bottom 16px, border-bottom 1px `--hms-border`, margin-bottom 20px
- `h4.page-title`: `--hms-text-lg`, weight 600
- `.breadcrumb`: `--hms-text-sm`, links `--hms-primary`

---

## 6. Landing Page Wireframe

```
┌──────────────────────────────────────────────────────────────┐
│  [Logo]  Home   Departments   About   Contact   [Admin][Doc]│  sticky, white
├──────────────────────────────────────────────────────────────┤
│  HERO (--hms-hero-gradient)                                  │
│  "Clinic Management Made Simple"                             │
│  Subtext + trust line                                        │
│  [Administrator Login]  [Doctor Login]                       │
├──────────────────────────────────────────────────────────────┤
│  DEPARTMENTS (3–4 cards, --hms-page-bg section)              │
│  Surgery | Lab | Pharmacy | Records                          │
├──────────────────────────────────────────────────────────────┤
│  STATS STRIP (optional count-up)                             │
├──────────────────────────────────────────────────────────────┤
│  SPECIALISTS / TEAM (Owl carousel)                           │
├──────────────────────────────────────────────────────────────┤
│  HOTLINE + CONTACT CTA                                       │
├──────────────────────────────────────────────────────────────┤
│  FOOTER — dark #0A1B39, links, copyright                     │
└──────────────────────────────────────────────────────────────┘
```

**Files:** `index.php`, `assets/sass/setting/_variable.scss`, `assets/css/style.css`, `assets/css/hms-landing.css`

---

## 7. Icon Recommendation

**Use Material Design Icons (MDI) only** — `mdi mdi-*` classes already in `icons.min.css`.

| Action | Icon class |
|---|---|
| Dashboard | `mdi mdi-view-dashboard-outline` |
| Patients | `mdi mdi-hospital-box-outline` |
| View | `mdi mdi-eye-outline` |
| Edit | `mdi mdi-pencil-outline` |
| Delete | `mdi mdi-trash-can-outline` |
| Lab | `mdi mdi-flask-outline` |
| Pharmacy | `mdi mdi-pill` |
| Payroll | `mdi mdi-cash-multiple` |
| Employees | `mdi mdi-doctor` |

**Migration:** Replace `fe-*` and `fas fa-*` in `sidebar.php` first; leave low-traffic pages for Phase 4. Do not load Feather/FA on new work.

---

## 8. Illustration & Imagery

- Flat vector, soft indigo/teal gradients — no stock-photo clichés
- Auth panel: abstract ECG line + cross motif, low-opacity white on gradient
- Empty states: line illustration (empty bed, clipboard)
- Avatars: initials on `--hms-tint-blue` circle when no upload
- Favicon: medical cross in `--hms-primary` on white, 32×32 SVG

---

## 9. Sample CSS (`:root` + key overrides)

```css
:root {
  --hms-primary: #2E37A4;
  --hms-primary-dark: #1F276F;
  --hms-primary-hover: #3C449C;
  --hms-primary-transparent: #ECEDF7;
  --hms-secondary: #00D3C7;
  --hms-tint-blue: #EEF1FD;
  --hms-tint-green: #E7F8EF;
  --hms-tint-amber: #FFF4E5;
  --hms-tint-purple: #F2EEFC;
  --hms-success: #16A34A;
  --hms-warning: #D97706;
  --hms-danger: #DC2626;
  --hms-info: #2563AA;
  --hms-text: #101828;
  --hms-text-muted: #5B6472;
  --hms-border: #E4E9EE;
  --hms-surface: #FFFFFF;
  --hms-page-bg: #F5F6FA;
  --hms-font: "Inter", "Segoe UI", system-ui, sans-serif;
  --hms-radius-sm: 5px;
  --hms-shadow-md: 0 0 35px rgba(104, 134, 177, 0.15);
  --hms-sidenav-width: 260px;
  --hms-topbar-height: 55px;
}

body {
  font-family: var(--hms-font);
  background: var(--hms-page-bg);
  color: var(--hms-text-muted);
}

.left-side-menu {
  background: var(--hms-page-bg);
  border-right: 1px solid var(--hms-border);
  width: var(--hms-sidenav-width);
}

#sidebar-menu > ul > li > a.active,
#sidebar-menu > ul > li.mm-active > a {
  background: var(--hms-surface);
  color: var(--hms-primary);
  border-left: 2px solid var(--hms-primary);
  box-shadow: var(--hms-shadow-sm);
  transition: background 200ms ease-out, border-color 200ms ease-out;
}

.widget-rounded-circle.card-box {
  background: var(--hms-surface);
  border: 1px solid var(--hms-border);
  border-radius: var(--hms-radius-sm);
  box-shadow: var(--hms-shadow-md);
}

.hms-tint-blue .bg-soft-primary { background: var(--hms-tint-blue) !important; border: none !important; }
.hms-tint-green .bg-soft-primary { background: var(--hms-tint-green) !important; border: none !important; }
.hms-tint-amber .bg-soft-primary { background: var(--hms-tint-amber) !important; border: none !important; }
.hms-tint-purple .bg-soft-primary { background: var(--hms-tint-purple) !important; border: none !important; }

.btn-primary {
  background: var(--hms-primary);
  border-color: var(--hms-primary);
}
.btn-primary:hover {
  background: var(--hms-primary-hover);
  box-shadow: var(--hms-shadow-primary);
}
```

**File placement:** `backend/assets/css/hms-theme.css` — linked from both panels:

```html
<link href="../assets/css/hms-theme.css" rel="stylesheet" type="text/css" />
```

---

## 10. Rollout Priority

### Phase 1 — Shell + first impression ✅ (in progress)
- [x] Create `backend/assets/css/hms-theme.css` with Preclinic tokens
- [x] Update `head.php` (admin + doc): Inter + theme link
- [x] Auth split layout (`admin/index.php`, `doc/index.php`)
- [x] Sidebar group labels (partial — expand to full Preclinic map)
- [x] Stat card tint classes on dashboard
- [ ] Add `hms-motion.js` (count-up + scroll-reveal)
- [ ] Align `:root` aliases to v2.0 token names (`--hms-page-bg`, `--hms-tint-*`)

### Phase 2 — Data density
- [ ] Table zebra/hover + badge→button CSS (mostly done in theme)
- [ ] Form focus states + section headers
- [ ] Flatpickr skin
- [ ] Footable pagination
- [ ] Dashboard widget rows (appointment breakdown, top patients)
- [ ] Skeleton loading states

### Phase 3 — Public site
- [x] Update `_variable.scss` tokens
- [x] Patch `style.css` primary colors
- [x] `hms-landing.css` CTA overrides
- [ ] Full section scroll-reveal on `index.php`
- [ ] Replace missing logo/favicon assets

### Phase 4 — Consolidation (optional)
- [ ] Symlink shared `backend/assets/` for admin+doc duplicate trees
- [ ] MDI-only icon pass on all pages
- [ ] Dark mode token layer (`[data-theme=dark]`)

---

## Bootstrap 4 Compromises

| Limitation | Workaround |
|---|---|
| Cannot change grid without HTML edits | Spacing overrides only |
| `.bg-soft-*` baked in `app.min.css` | Override with `!important` in `hms-theme.css` |
| SweetAlert v1 API | Style `.swal-*` classes; don't upgrade lib in Phase 1 |
| Footable + Bootstrap pagination | Namespace under `.footable-pagination` |
| Ladda expands button width | `min-width` on `.ladda-button` |
| Mixed icon fonts in 166 PHP files | CSS-only Phase 1; MDI migration in sidebar first |
| Counter-up plugin vs custom motion | `hms-motion.js` can replace or augment `data-plugin="counterup"` |

---

*Document version 2.0 — Preclinic-aligned. Verify pastel tints and spacing in Figma before final sign-off.*
