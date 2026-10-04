---
name: clinicdesk-ui
description: Use when editing any ClinicDesk PHP page that renders UI (login, dashboards, report-box*/table1-*, records, uploads, profiles). Encodes the Vue 3 (CDN) + Bootstrap 5 conventions, the design system variables, and the known front-end bugs new UI must not repeat.
---

# UI conventions for ClinicDesk

Every page is a single PHP file: `<head>` CSS → `<body>` HTML → Vue 3 via CDN
(`vue.global.js`, Options API, `createApp({...}).mount("#app")`) → inline `<script>`.
No build step, no components, no router. Follow that shape.

## Design system (copy from any existing page's `:root`)

```css
:root {
  --clinic-primary: #0f766e;   /* teal 700 — headers, buttons, titles */
  --clinic-secondary: #14b8a6; /* teal 500 — gradients, focus rings */
  --clinic-accent: #0ea5e9;
  --clinic-bg: #eef8fb;  --clinic-light: #f0fdfa;
  --clinic-card: rgba(255,255,255,0.96);
  --clinic-border: #d9eef0;  --clinic-text: #16323f;  --clinic-muted: #6b7d87;
  --clinic-shadow: 0 12px 32px rgba(15,118,110,0.10);  --clinic-radius: 22px;
}
```

- Body background: two radial gradients + `linear-gradient(135deg, #eef8fb, #f8fcfd)`.
- Cards/headers: gradient `linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary))`,
  white text, decorative blurred circle `::before`/`::after`, rounded 22–32px.
- Bootstrap 5.3.3 from CDN for grid/modals/alerts; custom `.field-box`/`.form-label`
  styling for form fields (uppercase labels, icon inside the field).
- Responsive breakpoints used: 930px (stack layout, hide hero), 480px (tighter padding).
- Don't invent new palette colors or a dark mode — pages are light-only.

## Vue rules

1. **Declare every template-referenced key in `data()`.** The known bug class:
   `report-table1-health-nutrition-a.php` binds `schoolYearOptions`/`selectedSchoolYear`
   before `loadSchoolYearOptions()` creates them → blank dropdown. New reactive state
   always goes in `data()`.
2. **Modals bind to buttons, not `@change`.** `openLoadModal()` is wrongly bound to the
   year select's `@change` in report-box5/6, box8/9, box10/11 — changing year pops a modal.
   New selects must not trigger modals; use `@click` on the Load button.
3. **`colspan` must match real column count** (student-dashboard has 8 on a 7-column table).
4. **No framework CLI additions.** If a chart is needed, use the same CDN charting the
   dashboards already include (Chart.js). Keep `<script src>`s in the page `<head>`/footer
   as sibling pages do.

## Data conventions the UI must respect

- **Category names are exact strings**: BMI `Severely Wasted | Wasted | Normal | Overweight |
  Obese`; HFA `Severely Stunted | Stunted | Normal | Tall`. Buckets testing for
  "Underweight"/"Severely Underweight" are legacy bugs (student-dashboard,
  health-analytics, school-admin-dashboard, teacher-reports) — never copy them.
- **School years come from `api/get_school_years.php`**, not hardcoded arrays. The
  hardcoded 2021–2028 option lists in `reports.php` / `consolidated-report.php` are a bug
  (they skip 2024-2025 and 2026-2027).
- **Every data fetch carries `school_year`**, and default to the active year on load.
- **ML/gender encoding**: PHP path and `model-comparison.php` disagree on gender encoding —
  when building new prediction UI, reuse the PHP payload shape from
  `api/generate_student_prediction.php` and never call Flask from the browser directly.
- XSS hygiene: never `v-html` user-sourced fields (learner names, remarks come from SF8 files).

## Accessibility & consistency

- `aria-label` on icon-only buttons (eye toggle pattern in `login.php` is the model).
- `:disabled="loading"` on submit buttons with a "Signing in..."-style busy label.
- Role-based landing: teacher → `teacher-dashboard.php`, nurse → `nurse-dashboard.php`,
  admin → `school-admin-dashboard.php`, IT → `user-management.php` (see `dashboard-redirect.php`).
- Keep page `<title>` in the `ClinicDesk | <Page>` format.

## Verify (see clinicdesk-verify skill, §5)

- [ ] Open the page in the browser preview; console clean (`preview_logs`)
- [ ] All `data()` keys bound in template exist; dropdowns populated on load
- [ ] Year select change doesn't pop modals; colspan matches columns
- [ ] Category buckets use current strings; school-year sourced from the API
