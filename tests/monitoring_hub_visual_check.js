// Synthetic browser check; no live learner data is read or changed.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const bundled = path.join(os.homedir(), '.cache', 'codex-runtimes', 'codex-primary-runtime', 'dependencies', 'node', 'node_modules', 'playwright');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || (fs.existsSync(bundled) ? bundled : 'playwright'));

const records = [
  {record_id:1,lrn:'999999999991',learner_name:'Synthetic Ana',grade_level:'Grade 8',section:'A',sex:'Female',
    weight_kg:'42',bmi:'17.5',bmi_category:'Wasted',height_for_age:'Normal',predicted_risk_level:'Moderate',
    feeding_required:'Yes',date_saved:'2026-07-01 10:00:00'},
  {record_id:2,lrn:'999999999992',learner_name:'Synthetic Bea',grade_level:'Grade 8',section:'B',sex:'Female',
    weight_kg:'48',bmi:'20',bmi_category:'Normal',height_for_age:'Normal',predicted_risk_level:'Low',
    feeding_required:'No',date_saved:'2026-07-01 10:00:00'},
  {record_id:3,lrn:'999999999993',learner_name:'Synthetic Carl',grade_level:'Grade 9',section:'A',sex:'Male',
    weight_kg:'51',bmi:'21',bmi_category:'Normal',height_for_age:'Normal',predicted_risk_level:'Low',
    feeding_required:'No',date_saved:'2026-07-01 10:00:00'}
];
const empty = () => ({wifa_baseline:null,wifa_events:[],deworming_baseline:null,
  deworming_events:[],arh:null,immunizations:[],screenings:[],tobacco:null,feeding_measurements:[]});
const programs = {1:{...empty(),wifa_baseline:{wifa:1,wifa_date:'2026-07-15'},wifa_events:[{event_date:'2026-07-22',outcome:'Given'}],
  deworming_baseline:{dewormed_sbfp:1,dewormed_other:0},arh:{pregnancy_status:'Not Pregnant',peer_educator:1},
  immunizations:[{vaccine:'HPV',dose:'1',immunized:1}],screenings:[{screening_type:'Oral Health',screened:1}],
  feeding_measurements:[{measurement_id:7,measured_on:'2026-09-01',weight_kg:'43',bmi:null,progress_status:'Improving'}]},
  2:empty(),3:empty()};

(async () => {
  const browser = await chromium.launch({ channel:'chrome', headless:true });
  try {
    const page = await browser.newPage({ viewport:{width:1440,height:900} });
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    await page.addInitScript(() => {
      localStorage.setItem('active_role','Clinic Nurse');
      localStorage.setItem('local_account_id','1');
      localStorage.setItem('local_id_token','synthetic-token');
    });
    await page.route('**/api/get_school_years.php**', route => route.fulfill({json:{success:true,active:'2026-2027',years:[{year_label:'2026-2027'}]}}));
    await page.route('**/api/get_monitoring_data.php**', route => route.fulfill({json:{success:true,records,summary:{}}}));
    await page.route('**/api/get_program_monitoring.php**', route => route.fulfill({json:{success:true,programs}}));
    await page.route('**/api/feeding_monitoring.php**', route => route.fulfill({json:{success:true,student:records[0],measurements:[
      {measurement_id:7,measured_on:'2026-09-01',weight_kg:'43',bmi:null,progress_status:'Improving',notes:'Follow-up'}
    ]}}));
    await page.goto('http://localhost/ClinicDesk/monitoring-hub.php',{waitUntil:'domcontentloaded'});
    await page.getByRole('tab',{name:'Nutrition'}).waitFor({timeout:30000});
    await page.getByText('Synthetic Ana').waitFor();
    assert.equal(await page.locator('.program-tab').count(),8);
    assert.equal(await page.locator('.chart-card canvas').count(),3);
    assert.equal(await page.getByRole('columnheader',{name:'Program details'}).count(),0);
    assert.equal(await page.getByRole('columnheader',{name:'Height for age'}).count(),1);
    assert.equal(await page.getByRole('button',{name:'View / edit'}).count(),3);
    assert.equal(await page.locator('.monitor-table td:first-child').first().evaluate(el=>getComputedStyle(el).position),'sticky');
    assert.equal(await page.locator('.program-tabs').evaluate(el=>el.scrollWidth<=el.clientWidth),true);
    if(process.argv[2])await page.screenshot({path:path.join(process.argv[2],'monitoring-nutrition.png'),fullPage:true});
    await page.getByRole('tab',{name:'WIFA'}).click();
    assert.equal(await page.locator('.chart-card canvas').count(),2);
    assert.deepEqual(await page.locator('.stat-number').allTextContents(),['2','1','1','1']);
    assert.equal(await page.getByText('Synthetic Carl').count(),0);
    await page.getByRole('tab',{name:'Feeding Program'}).click();
    await page.getByRole('button',{name:'Track progress'}).first().click();
    await page.getByText('SF8 baseline:',{exact:false}).waitFor();
    await page.locator('#feedingWeight').fill('43.5');
    assert.equal(await page.locator('#feedingBmi').inputValue(),'');
    if(process.argv[2])await page.screenshot({path:path.join(process.argv[2],'monitoring-feeding-modal.png')});
    await page.getByRole('button',{name:'Close'}).click();
    const expectedColumns={'Deworming':['SBFP dose','Other dose','Last given dose','Last source'],
      'ARH':['Pregnancy status','Delivery mode','Peer educator'],
      'Immunization':['Vaccine','Dose','Immunized'],
      'OKD & LHAS':['Screening type','Screened','Findings','Referral'],
      'Tobacco Control':['Violation type','Referred to care']};
    for(const label of Object.keys(expectedColumns)) {
      await page.getByRole('tab',{name:label}).click();
      assert.equal(await page.locator('.chart-card canvas').count(),2);
      assert.deepEqual(await page.locator('.monitor-table thead th').allTextContents(),
        ['Student','Grade / Section',...expectedColumns[label],'Actions']);
      assert.equal(await page.getByRole('button',{name:'View / edit'}).count(),3);
    }
    await page.setViewportSize({width:390,height:844});
    await page.getByRole('tab',{name:'Nutrition'}).click();
    assert.equal(await page.getByText('Synthetic Ana').count(),1);
    assert.equal(await page.locator('.program-tabs').evaluate(el=>el.scrollWidth<=el.clientWidth),true);
    const frozenStudent=await page.locator('.table-card .table-scroll').evaluate(el=>{
      const cell=el.querySelector('tbody tr:nth-child(2) td:first-child');
      const before=cell.getBoundingClientRect().left;
      const overflow=el.scrollWidth>el.clientWidth;
      el.scrollLeft=160;
      return overflow && Math.abs(cell.getBoundingClientRect().left-before)<2;
    });
    assert.equal(frozenStudent,true);
    if(process.argv[2])await page.screenshot({path:path.join(process.argv[2],'monitoring-mobile.png')});
    assert.deepEqual(errors,[]);
    console.log('Merged monitoring tabs, charts, WIFA counts, and feeding modal passed');
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode=1; });
