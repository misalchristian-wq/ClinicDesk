---
name: clinicdesk-verify
description: Use after making any change in ClinicDesk to verify it. Covers PHP syntax checks, JSON endpoint smoke tests, DB schema validation against clinicdesk.sql, Flask ML service checks, and the Vue-page pitfalls to look for on report/dashboard pages.
---

# Verifying ClinicDesk changes

ClinicDesk is a flat PHP app (XAMPP target) with no test suite and no build step.
Verification is manual and procedural — do each applicable step below after a change.

## 1. PHP syntax (always)

Run `php -l` on every PHP file you touched:

```bash
php -l api/your_file.php
```

A missing `php` on PATH is common on Windows/XAMPP — use the XAMPP binary
(`C:\xampp\php\php.exe -l ...`) rather than skipping this step.

## 2. Endpoint smoke test

Every `api/*.php` script must return JSON — never HTML, warnings, or a blank body.
With Apache running (or `php -S localhost:8080` from the repo root):

```bash
curl -s -X POST http://localhost:8080/api/<script>.php \
  -H "Content-Type: application/json" \
  -d '{"record_id": 1}' | python -m json.tool
```

Check:
- `Content-Type` ends up `application/json` (headers are set at the top of each script)
- Body parses as JSON and has a `success` key
- Error paths return JSON too (scripts `die(json_encode([...]))` on failure) —
  trigger one on purpose (bad id, missing field), not just the happy path
- `display_errors` is off in the scripts; if you see PHP notices inside the JSON, fix the source notice

If MySQL isn't running, `db.php` dies with a JSON connection error — that's expected;
start MariaDB from XAMPP control panel before testing data-dependent endpoints.

## 3. DB schema sanity

- Confirm new/changed tables against `clinicdesk.sql` (authoritative dump) or the
  `CREATE TABLE IF NOT EXISTS` snippets (`school_years.sql`, `report_saved_data`,
  `generated_reports` at the bottom of the dump).
- FKs follow the pattern: record tables → `sf8_uploads.upload_id` ON DELETE CASCADE;
  → `sf8_student_records.record_id` ON DELETE SET NULL. `upload_id = 0` inserts are invalid.
- If you changed a parser's stored columns, eyeball the affected rows
  (e.g. `bmi_category`/`height_for_age` must hold category strings, never raw numbers).

## 4. Flask ML service (only if prediction flow touched)

```bash
curl -s http://127.0.0.1:5001/health        # ml_service/ml_api.py
curl -s http://127.0.0.1:5000/health        # ml_model/app.py, if used
```

- `ml_model/app.py` is the maintained service (auto-loads newest `ml_model/version_*/`).
- `ml_service/ml_api.py` hardcodes `MODEL_DIR` and a Decision Tree path — if that
  directory doesn't exist it will not boot; don't treat its crash as a new regression.
- After a `/predict` call, check `ml_curl_log.txt` (repo root) for the HTTP code and response.

## 5. Vue page pitfalls (report-box*/table1-*, dashboards)

Pages are single PHP files with inline Vue 3 (CDN, Options API). After edits, open the
page with the browser preview and check the console via `preview_logs`, plus:

- **`data()` must declare everything the template binds** — a known bug class
  (`report-table1-health-nutrition-a.php` binds `schoolYearOptions`/`selectedSchoolYear`
  before `loadSchoolYearOptions()` sets them). New reactive fields go in `data()`.
- Modal bindings: `@change` on year selects must not pop modals (`openLoadModal()` bound
  to `@change` is a known bug in box5/6, box8/9, box10/11).
- Match table `colspan` to the actual column count.
- Filter values against current category names: BMI buckets are `Wasted`/`Severely Wasted`
  (not "Underweight"); HFA buckets are `Stunted`/`Severely Stunted`.
- Verify the page sends `school_year` and that the endpoint actually filters by it.

## 6. Report out a warning you can't fix

If verification surfaces a pre-existing bug outside the change scope, add it to
`knowledge.md` under the matching section instead of silently leaving it.
