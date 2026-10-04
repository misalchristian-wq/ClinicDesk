const assert = require('node:assert/strict');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const bundled = path.join(os.homedir(), '.cache', 'codex-runtimes', 'codex-primary-runtime', 'dependencies', 'node', 'node_modules', 'playwright');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || (fs.existsSync(bundled) ? bundled : 'playwright'));

(async () => {
  const browser = await chromium.launch({channel:'chrome',headless:true});
  try {
    const page = await browser.newPage();
    const errors=[]; page.on('pageerror',e=>errors.push(e.message));
    await page.addInitScript(() => localStorage.setItem('local_id_token','test-token'));
    await page.route('**/api/get_school_years.php**',r=>r.fulfill({json:{success:true,active:'2026-2027',years:[{year_label:'2026-2027'}]}}));
    await page.route('**/api/get_report_list.php**',r=>r.fulfill({json:{success:true,reports:[{report_id:999,school_year:'2026-2027',saved_by:'Test Nurse',saved_at:'2026-10-04 10:00:00',report_data:{},needs_update:true,current_hash:'test-hash',changes:[{source:'Screening',before:2,after:3}]}]}}));
    await page.route('**/api/get_box1_okd_lhas_report.php**',r=>r.fulfill({json:{success:true,records:[]}}));
    let deleted=false;
    await page.route('**/api/delete_report.php',async r=>{deleted=true; assert.equal(JSON.parse(r.request().postData()).report_id,999); await r.fulfill({json:{success:true,message:'Saved report deleted.'}});});
    await page.goto('http://localhost/ClinicDesk/report-box1-lhas.php',{waitUntil:'domcontentloaded'});
    await page.getByText('Student records changed since this section was saved.').first().waitFor();
    await page.getByRole('button',{name:/Load from Saved/}).click();
    await page.getByText('Screening: 2 → 3').last().waitFor();
    if (process.argv[2]) { await page.waitForTimeout(400); await page.screenshot({path:process.argv[2]}); }
    await page.getByRole('button',{name:'Delete saved section'}).click();
    await page.getByRole('button',{name:'Cancel'}).click();
    assert.equal(deleted,false);
    await page.getByRole('button',{name:'Delete saved section'}).click();
    await page.getByRole('button',{name:'Yes, delete'}).click();
    assert.equal(deleted,true);
    assert.deepEqual(errors,[]);
    console.log('Saved-report status, modal, confirmation, and delete request passed');
  } finally {await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1;});
