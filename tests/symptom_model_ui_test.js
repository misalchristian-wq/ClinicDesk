// Synthetic UI check: no learner data is read or written.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const bundled = path.join(os.homedir(), '.cache', 'codex-runtimes', 'codex-primary-runtime', 'dependencies', 'node', 'node_modules', 'playwright');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || (fs.existsSync(bundled) ? bundled : 'playwright'));

(async () => {
  const browser = await chromium.launch({ channel: 'chrome', headless: true });
  try {
    const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });
    const errors = [];
    let sent;
    page.on('pageerror', error => errors.push(error.message));
    await page.addInitScript(() => localStorage.setItem('local_id_token', 'synthetic-token'));
    await page.route('**/api/get_model_comparison.php', route => route.fulfill({ json: {
      success: true, metrics: [{ model: 'Random Forest (symptom CSV)', accuracy: 0.328,
        precision: 0.2098, recall: 0.2238, f1_score: 0.1901 }] } }));
    await page.route('**/api/test_model_prediction.php', route => {
      sent = route.request().postDataJSON();
      return route.fulfill({ json: { success: true, predicted_deficiency: 'No Deficiency',
        predicted_risk_level: 'Low', confidence_score: 0.31, recommendation_text: 'Nurse review',
        recommended_foods: 'Vegetables' } });
    });
    await page.goto('http://localhost/ClinicDesk/model-comparison.php', { waitUntil: 'domcontentloaded' });
    await page.getByText('Living environment', { exact: true }).waitFor();
    await page.getByText('Skin condition', { exact: true }).waitFor();
    await page.getByText('Age', { exact: true }).locator('..').locator('input').fill('16');
    await page.getByText('Gender', { exact: true }).locator('..').locator('select').selectOption('Female');
    await page.getByText('Diet type', { exact: true }).locator('..').locator('select').selectOption('Vegetarian');
    await page.getByText('Living environment', { exact: true }).locator('..').locator('select').selectOption('Rural');
    await page.getByText('Skin condition', { exact: true }).locator('..').locator('select').selectOption('Normal');
    await page.getByRole('button', { name: 'Get Prediction' }).click();
    await page.getByText('No concern flagged by this model').waitFor();
    const expected = ['Age','Gender','Diet Type','Living Environment','Night Blindness','Dry Eyes',
      'Bleeding Gums','Fatigue','Tingling Sensation','Low Sun Exposure','Reduced Memory Capacity',
      'Shortness of Breath','Loss of Appetite','Fast Heart Rate','Brittle Nails','Weight Loss',
      'Reduced Wound Healing Capacity','Skin Condition'];
    assert.deepEqual(Object.keys(sent).sort(), expected.sort());
    assert.equal(sent['Living Environment'], 'Rural');
    assert.equal(sent['Skin Condition'], 'Normal');
    assert.deepEqual(errors, []);
    console.log('Model tester sends all 18 CSV predictors without lab fields');
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
