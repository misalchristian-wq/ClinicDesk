---
name: sf8-category
description: Use when adding or changing an SF8 upload category, parser, approval flow, record category, or DepEd report section (report-box* / report-table1*) in ClinicDesk. Covers the parser → approve → records-manager → report wiring and the WHO classifier / school-year domain rules that new code must honor.
---

# SF8 category work in ClinicDesk

ClinicDesk ingests DepEd SF8 Excel sheets. Every record category follows the same
five-point wiring. When you touch one part, check the other four.

## The wiring (all five, in order)

1. **Parser** — `api/<category>_parser.php`
   - Load the sheet with `PhpOffice\PhpSpreadsheet\IOFactory` (see `api/sf8_parser.php`).
   - Cells are read with `getCellValue($sheet, "B10")` style calls; numerics go through `cleanNumeric()`.
   - Return shape: `["header" => [...school fields...], "students" => [row, ...]]`.
   - Dispatch happens in `api/parse_sf8_from_upload.php`; register the new `report_purpose` there.
2. **Approval** — `api/approve_sf8_upload.php`
   - Inserts parsed rows with FK `upload_id` (NOT NULL — never 0) and optionally links
     `student_record_id` to `sf8_student_records`.
   - Duplicate check intent is unique per `(lrn, school_year)` per `api/check_duplicate_students.php`.
   - Sets `sf8_uploads.status = 'Approved'` with a save/skip summary in `remarks`.
3. **Record category registry** — `api/record_categories.php::clinicRecordCategories()`
   - Maps category key → `table`, `pk`, `fields`. The generic CRUD
     (`get_category_records.php`, `add_category_record.php`, `update_category_records.php`,
     `delete_category_record.php`) and `health-records-manager.php` are driven entirely by this map.
   - The `pk` from this map is the ONLY safe source for SQL identifiers — never interpolate
     client input into `ORDER BY`/column names (that is the `delete_category_record.php` injection bug).
4. **DB table** — follow the `school_years.sql` pattern: `CREATE TABLE IF NOT EXISTS`,
   `upload_id` FK → `sf8_uploads` ON DELETE CASCADE, `student_record_id` FK →
   `sf8_student_records` ON DELETE SET NULL, a per-upload unique key, `created_at` default.
5. **Report page** — `report-box*.php` / `report-table1-*.php`
   - Vue 3 (CDN) in a single PHP file; loads via `api/get_*_report.php`, saves JSON via
     `api/save_report.php` into `report_saved_data` keyed `(report_key, school_year)`.
   - Add the report_key to the report list endpoints so it shows up in `reports.php`.

## Domain rules that MUST be honored (existing code violates several — do not repeat)

- **WHO classifier only**: classify BMI and height-for-age with `api/who_classifier.php`
  (`whoBmiCategory`, `whoHeightForAge`, `whoAgeToMonths` + `api/who_reference.json`).
  Never use adult BMI cut-offs (`< 18.5` etc.) — `sf8_parser.php::classifyBmi` is legacy.
  Categories: BMI `Severely Wasted|Wasted|Normal|Overweight|Obese`;
  HFA `Severely Stunted|Stunted|Normal|Tall`. (Dashboards still test for "Underweight" — a bug.)
- **height_for_age stores the CATEGORY, not the raw height.** The sample DB has
  `height_for_age = '1.524'` everywhere — that is the bug, don't copy it.
- **Filter every query by `school_year`.** Join `sf8_student_records.school_year` or scope by
  the active year from `api/get_school_years.php`. Several endpoints are year-blind; new code must not be.
- **Age arrives as text** (e.g. `'20'`, sometimes garbage). Parse defensively; `whoAgeToMonths`
  clamps to 60–228 months.
- **Dates may be Excel serials** — convert with `excelDateToYMD()`. `wifa_date = '0000-00-00'` exists; guard for it.
- **Counting rules**: exclude `violation_type = 'None'` from tobacco counts; "brought" means a real violation.
  OKD/LHAS `masterlisted`/`screened` counters must not be incremented on consultation saves.
- **API shape**: every `api/*.php` returns JSON with a `success` boolean and dies with JSON on
  error (no HTML leaks into responses). Include `db.php` via `include __DIR__ . "/../db.php"`.
- **Auth**: there is currently no server-side auth on endpoints (known debt). When adding an
  endpoint, note the gap and keep it ready for `authenticate()`/`requireRole()` from `api/auth.php`.

## Checklist before finishing

- [ ] Parser registered in `parse_sf8_from_upload.php` dispatch
- [ ] Approval branch inserts with correct table + FK `upload_id`
- [ ] Category added to `clinicRecordCategories()` (with `pk` from the map only)
- [ ] Report getter/saver filter by `school_year`
- [ ] WHO categories used, not adult cut-offs
- [ ] `php -l` on every changed PHP file; JSON responses verified (see clinicdesk-verify skill)
