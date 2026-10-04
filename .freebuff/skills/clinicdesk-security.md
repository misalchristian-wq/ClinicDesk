---
name: clinicdesk-security
description: Use when touching api/*.php endpoints, login/auth flows, password or account handling, secrets, file uploads, or anything serving student health data (PII of minors) in ClinicDesk. Encodes the security rules new code must follow and the known debt it must not replicate.
---

# Security rules for ClinicDesk

ClinicDesk handles health records of minors (Data Privacy Act territory). The codebase
carries serious security debt — see `knowledge.md` → Security. New code must not replicate
it, and any touched area should leave it better, not worse.

## Non-negotiables for new code

1. **Every new `api/*.php` endpoint authenticates.** Current endpoints are callable by
   anyone (CORS `*`, role checks only in client `localStorage`). When you add or modify an
   endpoint, wire the shape `api/auth.php` was built for:
   - issue a signed token at login (`local_login.php` — set `LOCAL_JWT_SECRET` via env),
   - read `Authorization: Bearer` from the request,
   - call `authenticate()` then `requireRole([...])` at the top, before any DB work.
   If the bootstrap isn't in place yet, at minimum restrict with an explicit role check
   against `local_accounts` and leave a `// TODO(security): requireRole` marker.
2. **Password checks: `password_verify()` against the caller's own account — only.**
   Never compare submitted passwords to a stored hash as plaintext
   (`$input === $storedHash` in `delete_*.php` is a vulnerability: a leaked hash works as
   a password). Never accept another account's password for destructive actions.
3. **No client-trusted roles.** `local_login.php` accepts a client-chosen role — never
   extend this pattern. The role must come from the server-side account record.
4. **SQL identifiers never come from input.** Table/pk names must resolve through
   `api/record_categories.php::clinicRecordCategories()` (the allowlist). Query *values*
   use prepared statements (`bind_param`) as the codebase already does.
5. **TLS stays on.** Never ship `CURLOPT_SSL_VERIFYPEER = false` (it exists in
   `approve_sf8_upload.php`, `parse_sf8_from_upload.php`, `generate_student_prediction.php`
   — legacy debt). Point cURL at a real CA bundle or the default.
6. **No secrets in the repo.** `cloudinary_config.php`, `firebase-service-account.json`,
   `mail_config.php` currently hold live credentials; `test_env.php` prints `LOCAL_JWT_SECRET`.
   Never add new secrets to files, never echo them in responses or logs. Read from
   environment variables (`getenv(...)`) with a clear failure message if unset.
7. **PII of minors**: endpoints returning student data (`get_student_records.php` et al.)
   must not be left unauthenticated when touched. Minimize fields returned; never log
   LRN/name/health values. Remember uploads live on Cloudinary — anyone with the URL can
   fetch them, so don't treat the Cloudinary URL as access control.
8. **Uploads**: browser-side unsigned Cloudinary presets can't be trusted. When touching
   upload code, constrain the preset server-side (allowed format `xlsx`, folder, size cap)
   and validate `report_purpose` against the known category list before parsing.

## Remediation order (if asked to fix the debt)

1. Delete `test_env.php`, rotate Cloudinary/Firebase/Gmail secrets, move to env vars.
2. Issue JWT in `local_login.php` + create `api/bootstrap.php` (it's referenced but missing),
   then add `authenticate()/requireRole()` to every endpoint, starting with destructive
   ones (`delete_*.php`, `approve_*.php`, `reject_sf8_upload.php`).
3. Fix the three destructive endpoints' password verification (`password_verify` only).
4. Fix the identifier injection in `delete_category_record.php` via the category map.
5. Tighten CORS from `*` to the app origin once pages send credentials.

## Quick review checklist for a security-sensitive diff

- [ ] Endpoint has `authenticate()` + `requireRole()` (or documented TODO marker)
- [ ] No `$input === $storedHash`, no client-chosen role, no identifier interpolation
- [ ] No secret printed, logged, or hardcoded; env var read with safe failure
- [ ] `SSL_VERIFYPEER` not disabled; no new `CURLOPT` without a reason
- [ ] Error responses leak nothing (no paths, no SQL, no stack) — JSON `success=false` only
- [ ] Verified via the `clinicdesk-verify` skill (curl happy path + unauthorized-path test)
