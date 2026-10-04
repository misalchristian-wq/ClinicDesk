// Synthetic browser check; no live report data is changed.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const bundled = path.join(os.homedir(), '.cache', 'codex-runtimes', 'codex-primary-runtime', 'dependencies', 'node', 'node_modules', 'playwright');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || (fs.existsSync(bundled) ? bundled : 'playwright'));

(async () => {
  const browser = await chromium.launch({channel:'chrome',headless:true});
  try {
    const page = await browser.newPage({viewport:{width:1440,height:900}});
    const errors=[];
    page.on('pageerror', error=>errors.push(error.message));
    await page.addInitScript(() => {
      localStorage.setItem('active_role','Clinic Nurse');
      localStorage.setItem('local_account_id','1');
      localStorage.setItem('local_id_token','synthetic-token');
      localStorage.setItem('local_full_name','Test Nurse');
    });
    await page.route('**/api/get_school_years.php**', route=>route.fulfill({json:{success:true,active:'2026-2027',years:[{year_label:'2026-2027'}]}}));
    await page.route('**/api/check_report_completeness.php**', route=>route.fulfill({json:{success:true,total:8,saved_count:2,saved:[{key:'box1'},{key:'table1_a'}],missing:[]}}));
    await page.route('**/api/get_all_reports.php**', route=>route.fulfill({json:{success:true,reports:{}}}));
    await page.route('**/api/get_clinic_report_summary.php**', route=>route.fulfill({json:{success:true,metrics:{learners:40,nutrition_followup:5,immunized:8,screened:12,wifa_given:10,dewormed:16,feeding_followup:3}}}));
    await page.goto('http://localhost/ClinicDesk/reports.php',{waitUntil:'domcontentloaded'});
    await page.getByText('2 of 8 sections saved').waitFor({timeout:30000});
    assert.equal(await page.locator('.report-module-card').count(),8);
    assert.equal(await page.locator('.report-module-card .report-status.saved').count(),2);
    assert.equal(await page.locator('.summary-box').count(),7);
    assert.equal(await page.getByText('Distinct learners in 2026-2027').count(),1);
    assert.equal(await page.getByRole('button',{name:'Download Excel template'}).count(),1);
    assert.equal(await page.getByText('No saved school-report sections').count(),1);
    await page.emulateMedia({media:'print'});
    assert.equal(await page.locator('.report-workflow').evaluate(el=>getComputedStyle(el).display),'none');
    assert.notEqual(await page.locator('section[aria-label="Live clinic activity"]').evaluate(el=>getComputedStyle(el).display),'none');
    await page.emulateMedia({media:'screen'});
    if (process.argv[2]) await page.screenshot({path:path.join(process.argv[2],'report-center-desktop.png'),fullPage:true});
    await page.setViewportSize({width:390,height:844});
    assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth),true);
    if (process.argv[2]) await page.screenshot({path:path.join(process.argv[2],'report-center-mobile.png'),fullPage:true});
    assert.deepEqual(errors,[]);
    console.log('Report center workflow, statuses, and responsive layout passed');
  } finally { await browser.close(); }
})().catch(error=>{console.error(error);process.exitCode=1;});
