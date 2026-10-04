const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const suggestionContext = {};
vm.runInNewContext(fs.readFileSync(path.join(__dirname, '..', 'assets/consultation-suggestions.js'), 'utf8'),
  { window: suggestionContext });
assert.equal(suggestionContext.clinicConsultationSuggestions(['fever'])[0].medicine, 'Paracetamol');
assert.equal(suggestionContext.clinicConsultationSuggestions(['has_headache'])[0].medicine, 'Paracetamol');

function optionsFor(file, globals = {}) {
  const html = fs.readFileSync(path.join(__dirname, '..', file), 'utf8');
  const scripts = [...html.matchAll(/<script>([\s\S]*?)<\/script>/g)].map(match => match[1]).join('\n');
  let options;
  vm.runInNewContext(scripts, {
    Vue: { createApp(value) { options = value; return { mount() {} }; } },
    localStorage: { getItem() { return 'nurse-token'; } },
    setTimeout() {},
    ...globals
  }, { filename: file });
  assert.ok(options, `${file} did not create a Vue app`);
  return options;
}

async function quickConsultationSavesObservedCareOnly() {
  let sent;
  const options = optionsFor('health-assessment-screening.php', {
    fetch: async (url, init) => {
      sent = { url, init };
      return { ok: true, json: async () => ({ success: true }) };
    }
  });
  const state = Object.assign(options.data(), options.methods);
  state.recordId = '42';
  state.consultForm.symptoms.fever = true;
  state.consultForm.careGiven = 'Observed and contacted guardian';
  state.toast = () => {};
  state.loadConsultations = async () => {};
  await state.saveConsultation();
  const body = JSON.parse(sent.init.body);
  assert.equal(sent.url, 'api/save_consultation.php');
  assert.equal(sent.init.headers.Authorization, 'Bearer nurse-token');
  assert.equal(body.symptoms, 'Fever');
  assert.equal(body.care_given, 'Observed and contacted guardian');
  assert.equal(Object.hasOwn(body, 'medication'), false);
  assert.equal(Object.hasOwn(body, 'common_illnesses'), false);
}

async function standaloneConsultationUsesSameContract() {
  let sent;
  const options = optionsFor('consultation.php', {
    fetch: async (url, init) => {
      sent = { url, init };
      return { ok: true, json: async () => ({ success: true }) };
    }
  });
  const state = Object.assign(options.data(), options.methods);
  state.selectedStudentId = '42';
  state.selectedStudent = { record_id: 42 };
  state.selectedSymptoms.has_headache = true;
  state.careGiven = 'Rested in clinic';
  state.loadStudents = async () => {};
  state.loadConsultations = async () => {};
  state.showMessage = () => {};
  await state.saveConsultation();
  const body = JSON.parse(sent.init.body);
  assert.equal(body.symptoms, 'Frequent headaches');
  assert.equal(body.care_given, 'Rested in clinic');
  assert.equal(Object.hasOwn(body, 'bmi_category'), false);
}

const hub = optionsFor('monitoring-hub.php');
const columns = hub.computed.programColumns.call({ activeTab: 'wifa' });
assert.ok(columns.some(column => column.key === 'wifaLastDose'));
assert.ok(columns.every(column => !['wifaDecision', 'wifaReviewDate'].includes(column.key)));

quickConsultationSavesObservedCareOnly()
  .then(standaloneConsultationUsesSameContract)
  .then(() => console.log('Consultation and WIFA UI contract tests passed'))
  .catch(error => { console.error(error); process.exitCode = 1; });
