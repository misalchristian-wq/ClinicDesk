// Synthetic browser smoke test. Uses no live learner data and writes screenshots
// only when an output directory is supplied as the first argument.
const assert = require('node:assert/strict');
const path = require('node:path');
const fs = require('node:fs');
const os = require('node:os');
const bundledPlaywright = path.join(os.homedir(), '.cache', 'codex-runtimes', 'codex-primary-runtime', 'dependencies', 'node', 'node_modules', 'playwright');
const playwrightModule = process.env.PLAYWRIGHT_MODULE || (fs.existsSync(bundledPlaywright) ? bundledPlaywright : 'playwright');
const { chromium } = require(playwrightModule);

(async () => {
  const browser = await chromium.launch({ channel: 'chrome', headless: true });
  try {
    const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
    const errors = [];
    page.on('pageerror', error => errors.push(error.stack || error.message));
    await page.addInitScript(() => {
      localStorage.setItem('active_role', 'Clinic Nurse');
      localStorage.setItem('local_account_id', '1');
      localStorage.setItem('local_full_name', 'Test Nurse');
      localStorage.setItem('local_id_token', 'synthetic-test-token');
    });
    await page.route('**/api/get_student_complete_profile.php**', route => route.fulfill({ json: {
      success: true, student: { record_id: 999999, learner_name: 'Synthetic Learner', lrn: '999999999999',
        grade_level: 'Grade 8', section: 'A', sex: 'Female', age: '14', school_year: '2026-2027',
        bmi: '19.1', bmi_category: 'Normal', height_for_age: 'Normal', weight_kg: '46', height_m: '1.55' },
      health_assessment:null,predictions:[],arh_records:[],immunization_records:[],screening_records:[],
      tobacco_records:[],feeding_measurements:[]
    } }));
    await page.route('**/api/get_school_years.php**', route => route.fulfill({ json: {
      success: true, active: '2026-2027', years: [{ year_label: '2026-2027', is_active: 1 }]
    } }));
    await page.route('**/api/get_student_records.php**', route => route.fulfill({ json: {
      success: true, records: [{ record_id: 999999, learner_name: 'Synthetic Learner', lrn: '999999999999',
        grade_level: 'Grade 8', section: 'A', sex: 'Female', age: '14', school_year: '2026-2027',
        bmi: '19.1', bmi_category: 'Normal', height_for_age: 'Normal' }]
    } }));
    await page.route('**/api/health_program_monitoring.php**', route => route.fulfill({ json:
      route.request().url().includes('scope=followups') ? { success: true, learners: [
        { record_id: 999999, lrn: '999999999999', learner_name: 'Synthetic Learner', grade_level: 'Grade 8', section: 'A',
          sex: 'Female', school_year: '2026-2027', sf8_wifa: 1, sf8_wifa_date: '2026-07-15',
          last_given_date: '2026-07-22' }
      ] } : {
      success: true, sf8_baseline: { wifa: 1, wifa_date: '2026-07-15', dewormed_sbfp: 1, dewormed_other: 0 },
      wifa_events: [{ wifa_event_id: 1, event_date: '2026-07-22', outcome: 'Given', reason_code: '', remarks: '', recorded_by: 'Test Nurse' }],
      deworming_events: [], event_audit: []
    } }));
    await page.goto('http://localhost/ClinicDesk/student-profile.php?record_id=999999', { waitUntil: 'domcontentloaded' });
    await page.getByRole('tab', { name: 'WIFA & Deworming' }).click();
    await page.getByText('WIFA Iron Sulfate Dates', { exact: true }).waitFor({ timeout: 30000 });
    assert.equal(await page.getByText('2026-07-15').count()>0, true);
    if (process.argv[2]) await page.screenshot({ path: path.join(process.argv[2], 'wifa-profile.png'), fullPage: true });
    await page.getByRole('button', { name: 'Record Intake Date' }).click();
    await page.getByRole('heading', { name: 'Record Iron Sulfate Intake' }).waitFor();
    assert.equal(await page.getByLabel('Date taken').count(), 1);
    await page.waitForTimeout(400);
    if (process.argv[2]) await page.screenshot({ path: path.join(process.argv[2], 'wifa-modal.png') });
    assert.deepEqual(errors, []);
    console.log('Synthetic WIFA profile and modal browser check passed');
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
