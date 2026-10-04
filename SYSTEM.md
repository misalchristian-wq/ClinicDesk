# ClinicDesk — System Definition

*Studied from the codebase on 2026-09-29. Verified against source; see `knowledge.md` for the detailed issue list.*

---

## 1. What the system is

**ClinicDesk — Student Nutritional Monitoring System** is a web-based school clinic management system for Philippine public schools. It digitizes the DepEd SF8 health forms workflow end to end:

> Teachers upload accomplished SF8 Excel files → the Clinic Nurse reviews/approves them → parsed learner health data lands in a central database → the system classifies nutritional status (WHO standards), runs ML-based micronutrient-deficiency predictions with recommendations, and generates official DepEd consolidated reports per school year.

Target stack environment: XAMPP (Apache + PHP 8.2 + MariaDB) with a local Python/Flask ML service. The repository is the `htdocs` app itself (flat PHP pages, no framework).

---

## 2. Users and roles

| Role | Auth backend | Landing page | Capabilities |
|---|---|---|---|
| **Teacher** | Firebase Auth (email/password) | `teacher-dashboard.php` | Upload SF8 files (per category), view own upload status, teacher reports |
| **Clinic Nurse** | Local account (MySQL `local_accounts`) | `nurse-dashboard.php` | Approve/reject uploads, manage health records, school years, consultations, SF8 previews, DepEd reports |
| **School Admin** | Local account | `school-admin-dashboard.php` | View school-wide analytics/reports |
| **IT Admin** | Local account | `user-management.php` | Manage all accounts (Firebase users + local accounts) |

Role selection happens on the login page. SF8 uploads, nurse previews, and approvals now enforce server-side Firebase/local JWT authentication. Other areas still rely on client-side `localStorage` role checks (remaining security debt).

---

## 3. Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│ Presentation  —  PHP pages + Vue 3 (CDN) + Bootstrap 5          │
│   login, dashboards (4 roles), records, reports, uploads        │
└──────────────┬──────────────────────────────────────────────────┘
               │ fetch() JSON
┌──────────────▼──────────────────────────────────────────────────┐
│ API layer     —  api/*.php (flat scripts, mysqli)               │
│   auth · uploads · parsers (PhpSpreadsheet) · records           │
│   reports · predictions · school years · account mgmt           │
└───────┬───────────────┬───────────────┬────────────────────────┘
        │               │               │
┌───────▼──────┐ ┌──────▼───────┐ ┌─────▼──────────────────────┐
│ MySQL        │ │ Flask ML     │ │ Cloud services             │
│ `clinicdesk` │ │ 127.0.0.1:   │ │ · Cloudinary (xlsx storage)│
│ (db.php)     │ │ 5001 /predict│ │ · Firebase Auth (teachers) │
└──────────────┘ └──────────────┘ │ · Gmail SMTP (PHPMailer)   │
                                  └────────────────────────────┘
```

**Third-party libraries** (`composer.json`): `kreait/firebase-php` (teacher account admin), `phpoffice/phpspreadsheet` (SF8 `.xlsx` parsing), `phpmailer/phpmailer` (email notifications). Front-end uses Vue 3 + Bootstrap 5 from CDNs; charts via Chart.js on dashboard pages.

---

## 4. Core data flow

### 4.1 SF8 upload → record (the main pipeline)
1. **Upload** (`nurse-upload-sf8.php` / `teacher-upload-sf8.php` → secure PHP upload endpoints): the server validates and encrypts the `.xlsx` with AES-256-GCM before signed Cloudinary raw storage; a row is created in `sf8_uploads` with `status = 'Pending'`, `file_type = 'sf8_enc_v1'`, and its report code.
2. **Preview** (`nurse-sf8-preview.php` → `api/parse_sf8_from_upload.php`): the file is downloaded from Cloudinary, authenticated/decrypted on the server into a private temporary file, and parsed by category-specific parsers:
   - `sf8_parser.php` — Student Information (Nutritional Status sheet; computes BMI when missing)
   - `arh_parser.php` — Adolescent Reproductive Health
   - `tobacco_parser.php` — Comprehensive Tobacco Control
   - `deworming_wifa_parser.php` — Deworming & WIFA
   - `immunization_parser.php` — Immunization
   - `okd_lhas_parser.php` — OKD & LHAS screenings
3. **Approval** (`api/approve_sf8_upload.php`): nurse approves → rows are matched by LRN, school year, and any category-specific key; a category-first upload creates a provisional learner and links the saved health rows to it. Confirmed overrides update the existing row. The upload flips to `Approved`, and the notification email is sent (`send_email_notification.php`). Rejection path exists (`reject_sf8_upload.php`).
4. **Correction cycle**: manual add/edit via `add_manual_student.php`, `record_categories.php` + `health-records-manager.php`, deletions with password confirmation (`delete_*.php`).

### 4.2 Nutritional classification (domain rules)
- **WHO/DepEd standard** (`api/who_classifier.php` + `api/who_reference.json`): BMI-for-age and Height-for-Age thresholds extracted from the SF8 helper table, keyed by age-in-months (60–228) and sex.
  - BMI categories: `Severely Wasted`, `Wasted`, `Normal`, `Overweight`, `Obese`
  - HFA categories: `Severely Stunted`, `Stunted`, `Normal`, `Tall`
- **Legacy adult-BMI cutoffs** still exist in `sf8_parser.php::classifyBmi` and several dashboards (see `knowledge.md`, Data correctness #1) — the two paths can disagree for adolescents.
- **School years** are a nurse-managed reference table (`school_years`, exactly one `is_active = 1`); pages send `school_year` but several report endpoints ignore it (known gap).

### 4.3 ML prediction & recommendations
1. Nurse opens a student's health assessment (`health-assessment-screening.php`) and records the 18 inputs in the symptom CSV, including living environment and skin condition, through `api/save_student_health_inputs.php`. New columns are added by `migrations/20261004_symptom_model_inputs.sql`.
2. `api/generate_student_prediction.php` requires a nurse JWT and the 18 dataset inputs. The CSV's nineteenth column, Predicted Deficiency, is a training label and never collected from the nurse. No laboratory measurement is required for this model.
3. **Two Flask apps exist**:
   - `ml_model/app.py` auto-loads the newest `ml_model/version_*/` (`model.joblib` and metadata), validates all 18 inputs, returns a screening flag, and derives priority by rules. The source CSV and reproducible training script are in `ml_model/data/` and `ml_model/train_symptom_model.py`.
   - `ml_service/ml_api.py` — older variant hard-coding a `Decision_Tree_model.joblib` path; the training `version_20260709_152358` belongs to `ml_model/`.
4. Result saved to `prediction_results` (dataset class, priority, model score, algorithm) + `recommendations` (text, foods, intervention type), rendered as a prompt for nurse review. Source holdout accuracy was 32.8%; the 37 school-age holdout rows had 21.62% accuracy. This is not a validated clinical model.
5. `nurse-prediction-settings.php` lets a Clinic Nurse check status and activate the local Flask service after re-entering her own password. The service binds to `127.0.0.1:5001`; Windows runtime setup is documented in `ml_model/FEATURE_AUDIT.md`.
6. The Kaggle source links, downloadable CSV columns, age ranges, unavailable third source, and reasons for excluding unused fields are documented in `ml_model/FEATURE_AUDIT.md`. The current artifact has no reproducible training pipeline or school-age validation in this repository.

### 4.4 DepEd reporting
- Interactive per-section report pages (`report-box*.php`, `report-table1-*.php`) load, aggregate, and save JSON snapshots (`report_saved_data`, `box1_okd_lhas_reports`).
- `consolidated-report.php` / `reports.php` aggregate across sections for a school year.
- **Offline generator**: `report_generator/generate_deped_report.py` fills the official DepEd Part IX template (Tables 1.A–1.D, Box 1, Boxes 5–6) from a JSON export, preserving the template's formulas; uploads back via `api/upload_consolidated_report.php` into `generated_reports`.

---

## 5. Database schema (`clinicdesk`, MariaDB)

**Core upload/record tables** (FKs: records → `sf8_uploads.upload_id` ON DELETE CASCADE; → `sf8_student_records.record_id` ON DELETE SET NULL):

| Table | Purpose |
|---|---|
| `sf8_uploads` | Upload register: file, Cloudinary URL, uploader, status (Pending/Approved/Rejected), purpose code, reviewer |
| `sf8_student_records` | Learner master record: unique LRN and school year, Provisional/Complete status, name, school info, sex, birthdate, weight/height/height², BMI, `bmi_category`, `height_for_age` |
| `arh_records`, `tobacco_control_records`, `deworming_wifa_records`, `immunization_records`, `okd_lhas_records` | Per-category SF8 sheets |
| `student_health_inputs` | Assessment questionnaire per student (symptoms, lifestyle, family history) |
| `prediction_results` → `recommendations` | ML output + intervention text (1:N) |

**Admin/reference tables:** `local_accounts` (bcrypt roles), `firebase_accounts` (mirrors Firebase users; **unused by the app**), `school_years` (active-year flag), `report_saved_data` + `box1_okd_lhas_reports` (report snapshots), `generated_reports` (consolidated xlsx registry).

Use `schema.sql` for a schema-only installation; local database dumps such as `db.sql` and `clinicdesk.sql` are ignored because they may contain learner records. `school_years.sql` is a standalone migration.

---

## 6. Page inventory (by concern)

- **Auth/session**: `login.php`, `dashboard-redirect.php`, `api/local_login.php`, `api/auth.php` (incomplete)
- **Dashboards**: teacher / nurse / school-admin, `health-analytics.php`, `user-management.php`
- **Upload & review**: `teacher-upload-sf8.php`, `nurse-upload-sf8.php`, `nurse-sf8-uploads.php`, `nurse-sf8-preview.php`, `nurse-school-years.php`
- **Records & care**: `health-records-manager.php`, `student-health-monitoring.php`, `student-profile.php`, `consultation.php`, `health-assessment-screening.php`, `nutritional-monitoring.php`, `parent-consent.php`
- **Reports**: `reports.php`, `view-reports.php`, `consolidated-report.php`, `teacher-reports.php`, the nine `report-box*/table1-*` pages, `model-comparison.php`
- **API**: ~70 scripts in `api/` (parsers, CRUD, report getters/savers, ML bridge, account mgmt, email, Firebase admin)

---

## 7. Known limitations (top-level; details in `knowledge.md`)

1. **Security debt**: most non-SF8 API endpoints still lack server-side authorization (CORS `*`, client-only role checks); committed Cloudinary/Firebase/Gmail secrets; destructive endpoints verify passwords against the *hash* as plaintext; SQL identifier injection in `delete_category_record.php`. Prediction endpoints now require nurse authorization; SF8 cloud transfers verify TLS, and `test_env.php` returns 404.
2. **Data correctness**: adult BMI cut-offs mixed with WHO tables; `height_for_age` stores raw height instead of category; LRN unique-across-years blocks returning learners; several report endpoints ignore `school_year`; Box 1 loader mis-buckets JHS/SHS; consultation writes pollute LHAS counters.
3. **Prediction limitation**: the current symptom model has a reproducible training pipeline but has no independent validation on local learners; its school-age source holdout performance is poor. Do not use its output as a diagnosis or automatic treatment decision.
4. **Doc/code mismatches** (thesis manuscript): CSV vs `.xlsx`, Random Forest vs actual best model, tokenization/audit logs claimed but unimplemented.

---

## 8. In one sentence

ClinicDesk is a three-tier (Vue/PHP/MySQL + Flask ML) school-clinic information system that ingests DepEd SF8 Excel uploads, curates learner health records through a nurse approval workflow, classifies nutritional status against WHO age/sex standards, predicts micronutrient deficiencies with recommendations via a trained classifier, and produces official DepEd consolidated health reports per school year.


## 9. Encrypted SF8 uploads (2026-10-02)

New teacher and nurse uploads are sent to ClinicDesk first, validated as SF8 XLSX files (maximum 10 MiB, recognized A1 report code, active school year), encrypted with AES-256-GCM, and uploaded as signed Cloudinary raw assets with random identifiers ending in .xlsx.enc. Cloudinary stores ciphertext; the original name stays in sf8_uploads. The existing file_type column records sf8_enc_v1, so this feature requires no schema migration.

Nurse preview and approval require a valid server-verified Clinic Nurse token. Each operation downloads the encrypted asset, verifies its GCM authentication tag, decrypts into a temporary file outside the web root, and passes the workbook to the existing category parser. Temporary files are removed after use. Preview does not save learner records. Approval parses the stored workbook again and saves its records to the existing database tables; it does not trust browser-supplied record arrays. Database field encryption is outside this change.

Teacher authentication uses the existing Firebase ID token. Local login now issues a one-hour JWT, and protected SF8 endpoints re-read the account role/status from MySQL. Existing local users must sign in again after this change. Most other endpoints still have the authorization debt listed in knowledge.md. The old save_sf8_upload and approve_local_upload routes delegate to the secure flow; plaintext URL-only registrations are no longer accepted. The nurse upload page now uses encrypted cloud storage and needs an Internet connection. Closing its preview leaves the encrypted upload Pending in SF8 Uploads.

### Installation and keys

Run from the project root:

~~~powershell
C:\xampp\php\php.exe tools/setup_sf8_security.php --enable-xampp-zip
~~~

The setup creates C:\xampp\clinicdesk-private\sf8-security.json outside Apache's web root. It generates separate encryption and login-signing secrets, imports the existing Cloudinary credentials without printing them, and retains existing keys on subsequent runs. The optional ZIP flag enables the existing PHP ZIP extension and keeps a php.ini backup. Restart Apache after changing php.ini. Do not replace or delete this private configuration: losing its encryption keys makes stored encrypted uploads unreadable. Keep a secure backup with the database backup.

Deployment can override CLINICDESK_SECURITY_CONFIG with an absolute private configuration path, or set SF8_ENCRYPTION_KEY (base64 encoding of 32 random bytes), LOCAL_JWT_SECRET (at least 32 characters), CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY, and CLOUDINARY_API_SECRET in the server environment. No secrets belong in Git, browser JavaScript, database upload rows, or Cloudinary assets. The versioned envelope contains only a public key identifier, nonce, authentication tag, and ciphertext. Rotation must retain old keys in sf8_encryption_keys while changing active_sf8_key_id. TLS verification remains enabled; CURL_CA_BUNDLE can supply an alternate CA bundle.

### Compatibility and checks

Older plaintext cloud uploads remain readable by the nurse preview/approval paths. They are not retroactively encrypted; their cloud copies remain plaintext until separately migrated or removed. Malformed, altered, oversized, or unknown-key encrypted files fail closed and never fall back to plaintext.

~~~powershell
C:\xampp\php\php.exe tests/sf8_encryption_test.php
C:\xampp\php\php.exe tests/sf8_cloud_test.php
C:\xampp\php\php.exe -d sys_temp_dir=$env:TEMP tests/sf8_integration_test.php
node tests/sf8_security_test.js
~~~

The first test uses synthetic learner data and covers encryption, workbook parsing, tampering, invalid keys, legacy reads, and cloud URL validation. The cloud test temporarily uploads a synthetic encrypted workbook, verifies downloaded ciphertext and decrypted parser output, and removes the test asset. The Windows/XAMPP integration test creates an isolated MariaDB database and API server in the temporary directory, checks login and role denial, then exercises upload → preview → approval through the actual PHP routes and verifies the saved learner row. It uses CREATE TABLE definitions from schema.sql without importing learner data, and removes its temporary services, database, and cloud asset afterward. Run it only against the intended configured Cloudinary account. Setup accepts --verify-cloud to run this check after configuration.

## 10. Category-first learners and duplicate prevention (2026-10-03)

Student Information creates or completes one learner master row per LRN and school year. It does not create empty health rows. If a category file arrives first, approval creates a **Provisional** learner identity and stores only the health rows actually present. Later Student Information or manual entry completes that same learner row. Category records link by exact LRN and school year.

Approval validates required row keys and repeated entries. Existing rows with the same category key are compared in the nurse override modal and updated in place only after confirmation. If the same learner name and school year appear with another LRN, approval stops and displays an LRN comparison modal; names alone never cause an automatic merge. Correct the workbook or the historical category record. A category LRN correction in Records Manager must point to an existing master row with the same learner name. Changing a master LRN needs administrator review because it can affect multiple linked records.

Apply `migrations/20261003_category_first_learners.sql` in new environments. The optional `tools/archive_empty_section_placeholders.php` tool archives legacy automatically generated empty health rows before removing them. It preserves populated rows. Back up the database before running either operation.

## 11. WIFA and deworming monitoring (2026-10-03)

The SF8 Deworming & WIFA workbook and its one-row-per-learner import remain unchanged. `wifa_date` is shown as the imported starting date, not a recurring schedule. Blank or `0000-00-00` dates remain unknown. The nurse records each actual iron sulfate intake date in `wifa_events`; deworming uses its separate `deworming_events` table. One learner/date is unique in each table, and corrections append to `health_program_event_audit`. The system also rejects a nurse entry on the same date already recorded as WIFA given in the SF8 baseline.

The [student profile](student-profile.php) and [combined monitoring page](monitoring-hub.php) show the SF8 starting date and nurse-recorded intake dates. New nurse WIFA reviews and decisions are no longer accepted; historical `wifa_reviews` rows remain stored but are not part of the date-only workflow. ClinicDesk does not calculate the next intake date. Only an authenticated Clinic Nurse may access the monitoring API.

The Table 1.C/D page, its aggregate endpoint, and the downloadable DepEd workbook combine imported baseline values with actual "Given" events. They count each learner once per deworming channel and once per WIFA reporting period, even if multiple dates are recorded. Imported WIFA flags without valid dates are disclosed on the table page and excluded from period counts. The selected school year is enforced by the APIs.

## 12. Combined student monitoring and feeding progress (2026-10-03)

`monitoring-hub.php` is the nurse's primary Student Health & Nutrition Monitoring page. It follows the Nutritional Monitoring design and has tabs for Nutrition, Feeding Program, WIFA, Deworming, ARH, Immunization, OKD & LHAS, and Tobacco Control. Each tab shows program-specific counts and charts, a searchable school-year learner list, 10/15/20-row pagination, and links to the student profile or category records manager. WIFA and deworming counts distinguish a recorded administration from no recorded dose; an absent record is not treated as proof that care was declined. The nurse dashboard links to this combined page. `nutritional-monitoring.php` redirects to it.

`student-directory.php` is the nurse's student-only roster. It shows school-year counts for Grades 7–10 and 11–12, grade-by-grade counts, search and filters, 10/15/20-row pagination, bulk Muslim/PWD/IP group editing, manual student addition, and View/Edit links to the individual profile. `student-dashboard.php` redirects to the directory for older links. These are students on record; the database does not currently track a separate enrollment status. The nurse dashboard links directly to the directory.

The downloadable DepEd workbook renders selected answer boxes as `/` and leaves unchecked boxes blank rather than writing Excel booleans. The SDO visit validation formula checks the slash; numeric report values and totals remain numeric.

`api/get_program_monitoring.php` returns a nurse-only, school-year snapshot linked by master record ID or, for historical unlinked SF8 rows, by matching LRN and school year. `api/get_monitoring_data.php` now requires a nurse token and a selected school year. The category manager accepts `?category=` so each tab can open the matching editor.

The additive `migrations/20261003_feeding_progress.sql` creates `feeding_measurements` and `feeding_measurement_audit`. A nurse can record weight, BMI, or both on an actual date, add an optional progress assessment, and correct an entry with an audit trail. One learner/date is unique. “Recovered” requires a nurse note. The SF8 weight and BMI remain the imported baseline, and the trend charts display them separately from dated follow-ups. The system does not calculate recovery from a BMI threshold or invent a BMI when only weight is entered.

Install `migrations/20261003_wifa_monitoring.sql` on an existing database. It only creates new tables; it does not backfill or alter existing SF8 dates. Fresh installations using `schema.sql` include the same tables. Verify with `node tests/wifa_monitoring_ui_test.js`, the bundled Python runtime running `tests/wifa_report_test.py`, and `php tests/sf8_integration_test.php` in the configured test environment.
