// Synthetic browser check: no live learner data is read or changed.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const bundled = path.join(os.homedir(), '.cache', 'codex-runtimes', 'codex-primary-runtime', 'dependencies', 'node', 'node_modules', 'playwright');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || (fs.existsSync(bundled) ? bundled : 'playwright'));

const student = {record_id:999999,lrn:'999999999999',learner_name:'Synthetic Learner',school_year:'2026-2027',
  school_name:'Synthetic School',school_id:'123456',district:'North',division:'Sample',region:'XI',
  grade_level:'Grade 8',section:'A',track_strand:'Science',sex:'Female',age:'14',birthdate:'2012-01-01',
  weight_kg:'46',height_m:'1.550',height_squared:'2.4025',bmi:'19.1',bmi_category:'Normal',
  height_for_age:'Normal',profile_status:'Complete',upload_id:12,date_saved:'2026-07-01 10:00:00',remarks:'Review at next visit'};
const profile = {success:true,student,
  health_assessment:{diet_type:'Balanced',sun_exposure:'Low',exercise_level:'Light',has_fatigue:'Yes',
    has_known_allergy:'Yes',allergy_details:'Peanuts',needs_followup:'Yes',clinic_notes:'Review appetite',updated_at:'2026-08-01 10:00:00'},
  predictions:[{prediction_id:1,predicted_deficiency:'Vitamin D Deficiency',predicted_risk_level:'Moderate',
    confidence_score:'0.8500',algorithm_used:'Decision Tree',prediction_date:'2026-08-01 10:01:00',
    recommendation_text:'Clinic follow-up',recommended_foods:'Milk',intervention_type:'Nutrition counseling'}],
  consultations:[{consultation_id:1,symptoms:'Headache',care_given:'Rested in clinic',follow_up_date:'2026-08-03',notes:'Guardian informed',recorded_at:'2026-08-01 11:00:00',recorded_by:'Test Nurse'}],
  arh_records:[{arh_record_id:1,pregnancy_status:'Not Pregnant',delivery_mode:'',peer_educator:1,
    remarks:'Peer educator',date_saved:'2026-07-02 10:00:00'}],
  immunization_records:[{immunization_id:1,vaccine:'HPV',dose:'1',immunized:1,remarks:'First dose'}],
  screening_records:[{okd_lhas_id:1,screening_type:'Vision Screening',masterlisted:1,screened:1,
    findings:1,referred_school:1,referred_lgu:0,referred_private:0,referred_others:0,remarks:'Check vision'}],
  tobacco_records:[{tobacco_id:1,violation_type:'None',referred_to_care:0,remarks:''}],
  feeding_measurements:[{measurement_id:1,measured_on:'2026-09-01',weight_kg:'47',bmi:null,
    progress_status:'Improving',notes:'Weight improved',recorded_by:'Test Nurse'}]};
const program = {success:true,sf8_baseline:{wifa:1,wifa_date:'2026-07-15',dewormed_sbfp:1,dewormed_other:0},
  wifa_events:[{wifa_event_id:1,event_date:'2026-07-22',outcome:'Given',reason_code:'',remarks:'',recorded_by:'Test Nurse'}],
  deworming_events:[{deworming_event_id:1,event_date:'2026-07-12',channel:'SBFP',outcome:'Given',remarks:''}],event_audit:[]};

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
    await page.route('**/api/get_student_complete_profile.php**', route=>route.fulfill({json:profile}));
    await page.route('**/api/health_program_monitoring.php**', route=>route.fulfill({json:program}));
    await page.goto('http://localhost/ClinicDesk/student-profile.php?record_id=999999',{waitUntil:'domcontentloaded'});
    await page.getByText('Synthetic Learner').first().waitFor({timeout:30000});
    assert.equal(await page.getByRole('tab').count(),6);
    assert.equal(await page.getByText('999999999999').count()>0,true);
    assert.equal(await page.getByRole('button',{name:'Print Record'}).isEnabled(),true);
    await page.getByRole('tab',{name:'Health Assessment'}).click();
    await page.getByText('Peanuts').waitFor();
    await page.getByText('Possible vitamin D-related concern').waitFor();
    await page.getByText('Rested in clinic').waitFor();
    await page.getByRole('tab',{name:'WIFA & Deworming'}).click();
    await page.getByRole('cell',{name:'2026-07-22'}).waitFor();
    assert.equal(await page.getByRole('button',{name:'Nurse Review'}).count(),0);
    await page.getByText('Deworming History').waitFor();
    await page.getByRole('tab',{name:'Feeding Program'}).click();
    await page.getByText('Weight improved').waitFor();
    await page.getByRole('tab',{name:'Immunization & Screening'}).click();
    await page.getByText('HPV').waitFor();
    await page.getByText('Vision Screening').waitFor();
    await page.getByRole('tab',{name:'ARH & Tobacco'}).click();
    await page.getByText('Peer educator').last().waitFor();
    if (process.argv[2]) await page.screenshot({path:path.join(process.argv[2],'student-profile-other.png'),fullPage:true});
    await page.evaluate(() => {window.__printCalled=false;window.print=()=>{window.__printCalled=true;};});
    await page.getByRole('button',{name:'Print Record'}).click();
    assert.equal(await page.evaluate(()=>window.__printCalled),true);
    await page.emulateMedia({media:'print'});
    for (const selector of ['.profile-print-title','.summary-grid','.profile-panel[aria-label="Health assessment"]',
      '.profile-panel[aria-label="Feeding program"]','.profile-panel[aria-label="Immunization and screening"]']) {
      assert.notEqual(await page.locator(selector).evaluate(el=>getComputedStyle(el).display),'none');
    }
    assert.equal(await page.locator('.profile-tabs').evaluate(el=>getComputedStyle(el).display),'none');
    await page.emulateMedia({media:'screen'});
    await page.setViewportSize({width:390,height:844});
    assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth),true);
    assert.equal(await page.locator('.profile-tabs').evaluate(el=>el.scrollWidth<=el.clientWidth),true);
    if (process.argv[2]) await page.screenshot({path:path.join(process.argv[2],'student-profile-mobile.png'),fullPage:true});
    await page.goto('http://localhost/ClinicDesk/student-profile.php?record_id=999999&from=directory&edit=1',{waitUntil:'domcontentloaded'});
    await page.locator('#editModal.show').waitFor({timeout:30000});
    assert.equal(await page.getByRole('link',{name:'Back to Student Directory'}).count(),1);
    assert.deepEqual(errors,[]);
    console.log('Student profile tabs, linked records, print layout, and mobile layout passed');
  } finally { await browser.close(); }
})().catch(error=>{console.error(error);process.exitCode=1;});
