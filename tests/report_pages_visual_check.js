// Open every report section with synthetic school-year data; do not save.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const bundled = path.join(os.homedir(), '.cache', 'codex-runtimes', 'codex-primary-runtime', 'dependencies', 'node', 'node_modules', 'playwright');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || (fs.existsSync(bundled) ? bundled : 'playwright'));

(async()=>{
  const browser=await chromium.launch({channel:'chrome',headless:true});
  try {
    const context=await browser.newContext({viewport:{width:1280,height:800}});
    await context.addInitScript(()=>{
      localStorage.setItem('active_role','Clinic Nurse');
      localStorage.setItem('local_account_id','1');
      localStorage.setItem('local_id_token','synthetic-token');
    });
    await context.route('**/api/get_school_years.php**',route=>route.fulfill({json:{success:true,active:'2026-2027',years:[{year_label:'2026-2027'},{year_label:'2025-2026'}]}}));
    for (const filename of ['report-box1-lhas.php','report-table1-health-nutrition-a.php','report-table1-health-nutrition-b.php',
      'report-box2-box3.php','report-table2-box4-mental-health.php','report-box5-box6.php','report-box8-box9.php','report-box10-box11.php']) {
      process.stdout.write('Checking '+filename+'...\n');
      const page=await context.newPage();
      const errors=[];
      page.on('pageerror',e=>errors.push(e.message));
      await page.goto('http://localhost/ClinicDesk/'+filename,{waitUntil:'domcontentloaded'});
      await page.locator('.report-guide').waitFor({timeout:30000});
      await page.locator('select option[value="2026-2027"]').first().waitFor({state:'attached',timeout:30000});
      assert.equal(await page.locator('.report-guide').isVisible(),true,filename);
      assert.equal(await page.locator('select').first().inputValue(),'2026-2027',filename);
      const back = page.locator('.header-box > .btn-back');
      assert.equal(await back.count(),1,filename+' has one right-side Back button');
      const backBox = await back.boundingBox();
      const titleBox = await page.locator('.header-box h1').boundingBox();
      assert.ok(backBox.x > titleBox.x,filename+' Back button follows the heading on the right');
      if (process.argv[2] && filename === 'report-box1-lhas.php') await page.screenshot({path:path.join(process.argv[2],'report-back-desktop.png')});
      await page.setViewportSize({width:390,height:844});
      assert.equal(await back.isVisible(),true,filename+' Back button is visible on mobile');
      const mobileBack = await back.boundingBox();
      const mobileHeader = await page.locator('.header-box').boundingBox();
      assert.ok(mobileBack.x + mobileBack.width/2 > mobileHeader.x + mobileHeader.width/2,filename+' Back button stays on the right on mobile');
      assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth),true,filename+' fits mobile width');
      if (process.argv[2] && filename === 'report-box1-lhas.php') await page.screenshot({path:path.join(process.argv[2],'report-back-mobile.png')});
      if (filename === 'report-table2-box4-mental-health.php') {
        const guidanceYes = page.locator('input[type="radio"][value="Yes"]').first();
        await guidanceYes.check();
        await page.locator('select').first().selectOption('2025-2026');
        assert.equal(await guidanceYes.isChecked(),false,'switching years clears prior answers');
      }
      assert.deepEqual(errors,[],filename);
      await page.close();
    }
    const linkedYearPage = await context.newPage();
    await linkedYearPage.goto('http://localhost/ClinicDesk/report-box1-lhas.php?school_year=2025-2026',{waitUntil:'domcontentloaded'});
    await linkedYearPage.locator('select').first().waitFor();
    await linkedYearPage.waitForFunction(() => document.querySelector('select')?.value === '2025-2026');
    await linkedYearPage.close();
    console.log('All eight report sections show nurse guidance and the active school year');
  } finally {await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1;});
