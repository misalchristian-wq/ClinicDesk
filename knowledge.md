# Known issues and security debt

Found by reading the code. Verify against the live repo before acting, since files may have changed. Ordered by severity within each section.

## Contents
- Security
- Data correctness
- Missing or broken pieces
- Front-end bugs
- Doc/code mismatches

## Security

1. **Committed secrets.** Cloudinary API secret (`cloudinary_config.php`), Firebase service-account private key (`firebase-service-account.json`), Gmail app password (`mail_config.php`). Rotate all three, move to env vars, and keep them out of version control. Do not reproduce values.
2. **No server-side authorization.** Most endpoints still lack server-side authorization. As of 2026-10-02, SF8 upload, preview, and approval routes authenticate Firebase/local JWT tokens and enforce server-side roles; `bootstrap.php` exists and local login issues JWTs. Other routes still rely on client-side `localStorage`. Fixing this means: issue a signed token in `local_login.php`, send it as `Authorization: Bearer` from the pages, and call `authenticate()` + `requireRole([...])` at the top of each endpoint.
3. **Destructive endpoints accept any account's password** (`delete_sf8_upload.php`, `delete_student_record.php`, `delete_category_record.php`) and also compare against the stored hash as plaintext (`$input === $storedHash`), so a leaked hash acts as a password. Verify the calling user's own account with `password_verify` only.
4. **SQL identifier injection.** `delete_category_record.php` interpolates client-supplied `pk`. Use the category map's pk.
5. **Login rate limiting remains absent.** Local login now issues a signed token, checks the selected role against the server account role, and protected SF8 routes re-read the account role/status. Other pages still trust client-side role checks.
6. **cURL `CURLOPT_SSL_VERIFYPEER = false`** remains in `generate_student_prediction.php`. SF8 preview/approval now use the shared storage helper with peer/host verification and a CA bundle.
7. **Teacher upload uses an unsigned Cloudinary preset** from the browser; anyone with the preset name can upload. Constrain the preset (allowed formats, folder, size) in Cloudinary.
8. **Configuration exposure resolved for `test_env.php` (2026-10-02).** It now returns HTTP 404 and never reads or prints secrets.
9. Student health data (PII of minors) is served without auth from `get_student_records.php`; treat with Data Privacy Act obligations in mind.
10. **Error responses leak paths/stack traces** (found by live smoke test 2026-09-29). When MySQL is down, endpoints whose JSON headers are emitted before `include db.php` (e.g. `api/local_login.php`, `api/get_school_years.php`) return raw HTML fatal errors with full `C:\xampp\htdocs\ClinicDesk\...` paths and stack traces; endpoints that wrap the include in try/catch (e.g. `generate_student_prediction.php`) leak `file`/`line` inside the JSON body instead. Fix: render errors as JSON only (`success=false`, generic message), log details server-side.

## Data correctness

1. **Adult BMI cut-offs** are used in several places instead of the WHO age/sex tables (see domain-rules). Results differ for adolescents.
2. **Height-for-age is stored as the raw height** for uploaded students. The DB sample shows `height_for_age = '1.524'` for every learner, so HFA-based risk and charts read as "unknown". `sf8_parser` copies column O verbatim through `cleanNumeric`; compute the category with `whoHeightForAge` instead.
3. **`height_squared` is inconsistent with height** in some seeded rows (for example height 1.55 with squared 2.3226). Recompute on insert.
4. **LRN uniqueness conflict**: `idx_unique_lrn` blocks multi-year records, and approval checks existing LRNs across all years, so the second school year cannot be approved for returning learners. Intended behaviour (per `check_duplicate_students.php`) is unique per `(lrn, school_year)`.
5. **Year-blind report endpoints**: `get_box1_okd_lhas_report.php`, `get_table1_immunization_nutrition_report.php`, `get_table1_deworming_wifa_report.php`, `get_student_records.php`, `get_students_for_consult.php`, `get_generated_reports.php`, `get_report_list.php` (ignores the `school_year` parameter pages send), `export_training_dataset.php`.
6. **Box 1 loader puts all LHAS counts under SHS** and zeros JHS. Group by grade like `generate_deped_report.php` does (ELEM/JHS/SHS from `grade_level`).
7. **Table 1.A "Load from Records" adds all immunization totals to `female`** and never fills `male` or IP. It also does not filter by grade 7, though the section is labelled Grade 7.
8. **Program tables link to students by first LRN match**, ignoring year, and stay `student_record_id = NULL` if the student file is approved later.
9. **Consultation rows** are written to `okd_lhas_records` with `lrn = record_id`, no year/grade, and `masterlisted/screened` accumulate on every save, inflating LHAS counts.
10. **Manual student insert uses `upload_id = 0`**, violating the FK unless a row with id 0 exists.
11. **Category records lose precision**: `get_category_records.php` casts every numeric-looking value to float, so long LRNs and leading zeros can be altered in the UI.
12. **Historical WIFA dates**: `wifa_date` `0000-00-00` exists in the data. The monitoring and Table 1.C/D page now show those imported dates as unknown and exclude them from dated period counts; the source rows remain for nurse review.
13. **Tobacco "brought" counts every row**, including `violation_type = 'None'`. Filter out 'None' before counting.
14. **ARH/tobacco parsers treat "Yes" as 0.**
15. **Age is text**; `whoAgeToMonths` uses `(float)`; non-numeric age becomes 0 and clamps to 60 months.

## Missing or broken pieces

- `api/get_health_assessment.php` now exists; DB table `consultations` remains absent. `api/bootstrap.php` was added for secure SF8 uploads; model artifacts are present locally (the earlier missing-artifact note was incorrect).
- `update_category_records.php` `bulk` branch is an empty stub; the manager's "Apply to selected" therefore reports "No rows to update".
- `approve_local_upload.php` now delegates to the authoritative stored-file approval route for all supported categories; browser-supplied record arrays are no longer accepted.
- `validate_school_year.php` and `check_duplicate_students.php` are written but never included by `save_sf8_upload.php` / `approve_sf8_upload.php`.
- `save_sf8_upload.php` does not enforce the active school year server-side (only the browser does).
- `report-box8-box9.php` has a stray `<tr>` where `</table>` should close the load modal table, which can break the layout.
- `report-table1-health-nutrition-a.php` never defines `schoolYearOptions` / `selectedSchoolYear` in `data()` (it calls `loadSchoolYearOptions` that sets them, but the template binds them before), so the year dropdown can be blank until created dynamically. Add both to `data()`.
- `student-health-monitoring.php` is a copy of the login page with a placeholder Firebase key; likely dead.
- `openLoadModal()` in box8/9, box10/11, box5/6 is bound to `@change` of the year select, so changing year pops the modal.

## Front-end bugs

- `nurse-sf8-preview.php`: `columnMap` key is `ccomprehensive_tobacco_control` (typo), so tobacco previews fall back to raw keys.
- `student-dashboard.php`: `openAddModal()` exists but no button opens the add-student modal; table `colspan` is 8 on a 7-column table.
- `student-dashboard.php` and `health-analytics.php` still test for "Underweight"; current categories are "Wasted"/"Severely Wasted".
- `school-admin-dashboard.php` and `teacher-reports.php` bucket BMI by "Underweight"/"Severely Underweight", so their charts show zeros for wasted learners.
- `reports.php` and `consolidated-report.php` hardcode school-year options (2021-2022, ..., 2027-2028, missing 2024-2025 and 2026-2027) instead of using `get_school_years.php`; `reports.php` default is `2021-2022`.
- `parent-consent.php` hardcodes a school name and principal in sample helpers.
- `model-comparison.php` and the screening page use all 18 predictors from `symptom_based_vitamin_deficiency_dataset_final.csv`; the nineteenth column is the training label. The symptom-only model and its low held-out school-age score are documented in `ml_model/FEATURE_AUDIT.md`.
- `consultation.php` and Quick Consultation on `health-assessment-screening.php` record nurse observations, care given, and follow-up through the consultations API. Selecting reported symptoms shows medicine suggestions for nurse review; suggestions are never auto-saved as care given.

## Doc/code mismatches

- Manuscript says CSV upload and mentions Random Forest as primary; code uses `.xlsx` and whichever model is `best_model.pkl` in the newest `version_*` (sample log shows Decision Tree).
- Manuscript lists an SVM in the Python section and XGBoost elsewhere; verify against `model_comparison.json` before quoting.
- Manuscript says `firebase_accounts` is populated; the app reads users directly from Firebase and never writes that table.
- Manuscript says tokenization, session timeout, and audit logs exist; none are implemented in the uploaded code.

## SF8 encryption verification notes (2026-10-02)

- PHP ZIP was disabled in the current XAMPP configuration even though XLSX parsing requires it; the security setup can enable it with --enable-xampp-zip.
- clinicdesk.sql lacks the program tables' school_year/grade_level columns used by current approval queries; db.sql has them. Reconcile the deployment schema before testing all categories. `db.sql` and `migrations/20261004_consultations.sql` now include consultations; older clinicdesk.sql does not.
- The consolidated Excel export now includes imported WIFA dates and nurse-recorded events, counting each learner once per period. Historical unknown dates remain excluded from dated totals.
- Encryption protects newly uploaded cloud files. Existing cloud plaintext copies, unauthenticated non-SF8 APIs, and committed legacy service credentials remain separate remediation work.
