// Synthetic browser check: consultation requests are intercepted; no learner data is changed.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const bundled = path.join(os.homedir(), '.cache', 'codex-runtimes', 'codex-primary-runtime', 'dependencies', 'node', 'node_modules', 'playwright');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || (fs.existsSync(bundled) ? bundled : 'playwright'));

const student = { record_id: 42, learner_name: 'Synthetic Learner', grade_level: 'Grade 8', section: 'A',
  bmi_category: 'Normal', age: 14, sex: 'Female', bmi: 19.2, consult_count: 0 };
const entry = { consultation_id: 1, symptoms: 'Fever', care_given: 'Rested in clinic',
  follow_up_date: null, notes: '', recorded_at: '2026-10-04 10:00:00', recorded_by: 'Test Nurse' };

(async () => {
  const browser = await chromium.launch({ channel: 'chrome', headless: true });
  try {
    const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });
    const errors = [];
    let posted;
    let assessmentPosted;
    let saved = false;
    page.on('pageerror', error => errors.push(error.message));
    await page.addInitScript(() => {
      localStorage.setItem('active_role', 'Clinic Nurse');
      localStorage.setItem('local_account_id', '1');
      localStorage.setItem('local_id_token', 'synthetic-token');
    });
    await page.route('**/api/get_students_for_consult.php', route => route.fulfill({ json: { success: true, students: [student] } }));
    await page.route('**/api/get_student_profile.php**', route => route.fulfill({ json: { success: true, student } }));
    await page.route('**/api/get_health_assessment.php**', route => route.fulfill({ json: { success: true, health_input: null } }));
    await page.route('**/api/get_student_prediction.php**', route => route.fulfill({ json: { success: true, prediction: null } }));
    await page.route('**/api/get_consultations.php**', route => route.fulfill({ json: { success: true, consultations: saved ? [entry] : [] } }));
    await page.route('**/api/save_consultation.php', async route => {
      posted = route.request().postDataJSON();
      saved = true;
      await route.fulfill({ json: { success: true, consultation_id: 1 } });
    });
    await page.route('**/api/save_student_health_inputs.php', async route => {
      assessmentPosted = route.request().postDataJSON();
      await route.fulfill({ json: { success: true } });
    });

    await page.goto('http://localhost/ClinicDesk/consultation.php', { waitUntil: 'domcontentloaded' });
    await page.locator('select').first().selectOption('42');
    await page.locator('.form-check').filter({ hasText: 'Frequent headaches' }).locator('input').check();
    await page.getByLabel('Care or action given').fill('Rested in clinic');
    await page.getByRole('button', { name: 'Save Consultation' }).click();
    await page.getByText('Recent consultations').waitFor();
    assert.equal(posted.symptoms, 'Frequent headaches');
    assert.equal(posted.care_given, 'Rested in clinic');
    assert.equal(Object.hasOwn(posted, 'medication'), false);

    saved = false;
    posted = null;
    await page.goto('http://localhost/ClinicDesk/health-assessment-screening.php?record_id=42', { waitUntil: 'domcontentloaded' });
    await page.getByText('Quick Consultation').waitFor();
    await page.getByText('Living environment', { exact: true }).waitFor();
    await page.getByText('Skin condition', { exact: true }).waitFor();
    assert.equal(await page.getByText('Model Measurements').count(), 0);
    await page.getByText('Living environment', { exact: true }).locator('..').locator('select').selectOption('Rural');
    await page.getByText('Skin condition', { exact: true }).locator('..').locator('select').selectOption('Dry Skin');
    await page.getByRole('button', { name: 'Save Health Assessment' }).click();
    assert.equal(assessmentPosted.living_environment, 'Rural');
    assert.equal(assessmentPosted.skin_condition, 'Dry Skin');
    assert.equal(Object.hasOwn(assessmentPosted, 'hemoglobin_g_dl'), false);
    await page.locator('.ill-chip').filter({ hasText: 'Fever' }).locator('input').check();
    await page.getByText('Fever: Paracetamol').waitFor();
    await page.getByLabel('Care or action given').fill('Rested in clinic');
    await page.getByRole('button', { name: 'Save Consultation' }).click();
    await page.getByText('Recent consultations').waitFor();
    assert.equal(posted.symptoms, 'Fever');
    assert.equal(posted.care_given, 'Rested in clinic');
    assert.deepEqual(errors, []);
    console.log('Both consultation forms render and save; symptom suggestions appear separately from care given');
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
