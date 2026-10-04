const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

function optionsFor(file, globals = {}) {
  const html = fs.readFileSync(path.join(__dirname, '..', file), 'utf8');
  const scripts = [...html.matchAll(/<script>([\s\S]*?)<\/script>/g)].map(match => match[1]).join('\n');
  let options;
  vm.runInNewContext(scripts, {
    Vue: { createApp(value) { options = value; return { mount() {} }; } },
    setTimeout() {},
    ...globals
  }, { filename: file });
  assert.ok(options, `${file} did not create a Vue app`);
  return options;
}

async function reportCountsPeopleOnce() {
  const report = optionsFor('report-table1-health-nutrition-b.php', {
    localStorage: { getItem() { return 'nurse-token'; } },
    fetch: async () => ({ json: async () => ({ success: true, records: [
      { learner_key: '111|2026-2027', grade_level: 'Grade 8', sex: 'Female', dewormed_sbfp: 1, dewormed_other: 0, wifa: 1, wifa_date: '2026-07-15' },
      { learner_key: '222|2026-2027', grade_level: 'Grade 8', sex: 'Female', dewormed_sbfp: 0, dewormed_other: 0, wifa: 1, wifa_date: null }
    ], wifa_events: [
      { learner_key: '111|2026-2027', grade_level: 'Grade 8', sex: 'Female', event_date: '2026-07-22' },
      { learner_key: '111|2026-2027', grade_level: 'Grade 8', sex: 'Female', event_date: '2026-07-29' }
    ], deworming_events: [
      { learner_key: '111|2026-2027', grade_level: 'Grade 8', sex: 'Female', channel: 'SBFP' },
      { learner_key: '222|2026-2027', grade_level: 'Grade 8', sex: 'Female', channel: 'Other' }
    ] }) })
  });
  const state = Object.assign(report.data(), report.methods);
  state.selectedSchoolYear = '2026-2027';
  await state.loadDewormingWifaData();
  assert.equal(state.formData.wifa[8]['Jul–Sep 2026'], 1, 'repeat doses must count one learner');
  assert.equal(state.unknownWifaCount, 1, 'unknown SF8 dates must be disclosed');
  assert.equal(state.formData.dewormed[8].sbfpFemale, 1, 'baseline and event must count once');
  assert.equal(state.formData.dewormed[8].otherFemale, 1);
}

function profileState() {
  const profile = optionsFor('student-profile.php');
  const state = Object.assign(profile.data(), profile.methods);
  state.wifaModal = { show() { this.shown = true; } };
  state.openWifaModal({ wifa_event_id: 7, event_date: '2026-07-22', outcome: 'Given' });
  assert.equal(state.wifaForm.event_id, 7);
  assert.deepEqual(Object.keys(state.wifaForm), ['event_id', 'event_date'], 'WIFA entry only asks for a taken date');
  assert.equal(state.wifaModal.shown, true);
}

reportCountsPeopleOnce().then(profileState).then(() => {
  console.log('WIFA monitoring UI and distinct-learner report tests passed');
}).catch(error => { console.error(error); process.exitCode = 1; });
