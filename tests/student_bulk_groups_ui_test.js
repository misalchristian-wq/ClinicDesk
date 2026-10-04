// Synthetic browser check: bulk group UI does not touch live learner data.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const bundled = path.join(os.homedir(), '.cache', 'codex-runtimes', 'codex-primary-runtime', 'dependencies', 'node', 'node_modules', 'playwright');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || (fs.existsSync(bundled) ? bundled : 'playwright'));

(async () => {
  const browser = await chromium.launch({channel:'chrome', headless:true});
  try {
    const page = await browser.newPage({viewport:{width:1440,height:900}});
    const errors = [];
    let submitted;
    page.on('pageerror', error => errors.push(error.message));
    await page.addInitScript(() => {
      localStorage.setItem('active_role','Clinic Nurse');
      localStorage.setItem('local_account_id','1');
      localStorage.setItem('local_id_token','synthetic-token');
      localStorage.setItem('local_full_name','Test Nurse');
    });
    const records = [
      {record_id:901,lrn:'901',learner_name:'Alpha Learner',school_year:'2025-2026',grade_level:'7',section:'A',sex:'Female',age:'13',bmi:'20',bmi_category:'Normal',height_for_age:'Normal',is_muslim:0,is_pwd:0,is_ip:0},
      {record_id:902,lrn:'902',learner_name:'Beta Learner',school_year:'2025-2026',grade_level:'Grade 11',section:'B',sex:'Male',age:'17',bmi:'21',bmi_category:'Normal',height_for_age:'Normal',is_muslim:0,is_pwd:0,is_ip:0},
      {record_id:903,lrn:'903',learner_name:'Older Learner',school_year:'2024-2025',grade_level:'8',section:'C',sex:'Female',age:'14',bmi:'20',bmi_category:'Normal',height_for_age:'Normal',is_muslim:0,is_pwd:0,is_ip:0}
    ];
    await page.route('**/api/get_student_records.php**', route => route.fulfill({json:{success:true,records}}));
    await page.route('**/api/get_school_years.php**', route => route.fulfill({json:{success:true,active:'2025-2026'}}));
    await page.route('**/api/health_program_monitoring.php**', route => route.fulfill({json:{success:true,learners:[]}}));
    await page.route('**/api/bulk_update_student_groups.php', async route => {
      submitted = route.request().postDataJSON();
      await route.fulfill({json:{success:true,updated:2,message:'2 learner record(s) updated.'}});
    });
    await page.goto('http://localhost/ClinicDesk/student-directory.php',{waitUntil:'domcontentloaded'});
    await page.getByRole('checkbox',{name:'Select Alpha Learner'}).waitFor({timeout:30000});
    assert.equal(await page.locator('.summary-card').filter({hasText:'Junior High'}).locator('strong').textContent(),'1');
    assert.equal(await page.locator('.summary-card').filter({hasText:'Senior High'}).locator('strong').textContent(),'1');
    assert.equal(await page.getByText('Older Learner').count(),0);
    assert.equal(await page.locator('canvas').count(),0);
    assert.match(await page.getByRole('link',{name:'Edit'}).first().getAttribute('href'),/edit=1/);
    await page.locator('.grade-chip').filter({hasText:'Grade 11'}).click();
    assert.equal(await page.getByRole('checkbox',{name:'Select Alpha Learner'}).count(),0);
    await page.locator('.grade-chip').filter({hasText:'Grade 11'}).click();
    await page.getByLabel('School year').selectOption('2024-2025');
    assert.equal(await page.locator('.summary-card').filter({hasText:'Total students'}).locator('strong').textContent(),'1');
    await page.getByLabel('School year').selectOption('2025-2026');
    if (process.argv[2]) await page.screenshot({path:process.argv[2],fullPage:true});
    await page.getByRole('checkbox',{name:'Select Alpha Learner'}).check();
    await page.getByRole('checkbox',{name:'Select Beta Learner'}).check();
    await page.getByRole('button',{name:'Edit groups (2)'}).click();
    if (process.argv[3]) await page.screenshot({path:process.argv[3]});
    await page.getByLabel('Muslim',{exact:true}).selectOption('yes');
    await page.getByLabel('Person with disability (PWD)').selectOption('no');
    await page.getByRole('button',{name:'Save selected learners'}).click();
    await page.getByText('2 learner record(s) updated.').waitFor();
    assert.deepEqual(submitted, {record_ids:[901,902],changes:{is_muslim:true,is_pwd:false}});
    assert.equal(await page.getByRole('button',{name:'Edit groups (0)'}).isDisabled(),true);
    await page.setViewportSize({width:390,height:844});
    assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),true);
    assert.equal(await page.locator('.directory-table .student-col').first().evaluate(el=>getComputedStyle(el).position),'sticky');
    await page.goto('http://localhost/ClinicDesk/student-dashboard.php',{waitUntil:'domcontentloaded'});
    assert.match(page.url(),/student-directory\.php/);
    assert.deepEqual(errors,[]);
    console.log('Student bulk group selection and save UI passed');
  } finally { await browser.close(); }
})().catch(error => {console.error(error);process.exitCode=1;});
