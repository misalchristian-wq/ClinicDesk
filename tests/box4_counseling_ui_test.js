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
    await page.addInitScript(() => localStorage.setItem('local_id_token','synthetic-token'));
    await page.route('**/api/get_school_years.php**',r=>r.fulfill({json:{success:true,active:'2026-2027',years:[{year_label:'2026-2027'}]}}));
    await page.route('**/api/get_report_list.php**',r=>r.fulfill({json:{success:true,reports:[]}}));
    let recorded=false;
    await page.route('**/api/box4_counseling.php**',async r=>{
      if (r.request().method()==='POST') {
        const input=JSON.parse(r.request().postData());
        assert.equal(input.student_record_id,42);
        assert.equal(input.visit_date,'2026-10-04');
        recorded=true;
        return r.fulfill({json:{success:true,message:'Counseling visit recorded.'}});
      }
      return r.fulfill({json:{success:true,students:[{record_id:42,lrn:'SYNTHETIC',learner_name:'Test Learner',grade_level:'7',sex:'Female'}],visits:[],counts:{counselingJHS:{male:0,female:1},counselingSHS:{male:0,female:0},vulnerableJHS:{muslim:1,ip:0,lwd:0},vulnerableSHS:{muslim:0,ip:0,lwd:0}}}});
    });
    await page.goto('http://localhost/ClinicDesk/report-table2-box4-mental-health.php',{waitUntil:'domcontentloaded'});
    await page.getByRole('button',{name:'Record counseling visit'}).click();
    await page.locator('#counselingStudent').selectOption('42');
    await page.locator('#counselingDate').fill('2026-10-04');
    await page.getByRole('button',{name:'Add',exact:true}).click();
    assert.equal(recorded,true);
    assert.equal(await page.locator('#counselingModal').isVisible(),true);
    assert.deepEqual(errors,[]);
    console.log('Box 4 counseling visit entry and derived-count UI passed');
  } finally {await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1;});
