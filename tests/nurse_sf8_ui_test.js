const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

function pageOptions(file, globals = {}) {
  const html = fs.readFileSync(path.join(__dirname, '..', file), 'utf8');
  const source = [...html.matchAll(/<script>([\s\S]*?)<\/script>/g)].map(match => match[1]).join('\n');
  let options;
  const context = {
    Vue: { createApp(value) { options = value; return { mount() {} }; } },
    ...globals
  };
  vm.runInNewContext(source, context, { filename: file });
  return options;
}

async function testOfflineConflict() {
  const listeners = new Map();
  const modalElement = { addEventListener(name, fn) { listeners.set(name, fn); } };
  const modals = {};
  class Modal {
    constructor(element) { this.id = element.id; this.shown = false; modals[this.id] = this; }
    show() { this.shown = true; }
    hide() { this.shown = false; listeners.get('hidden.bs.modal')?.(); }
  }
  const requests = [];
  const responses = [
    { success: false, requires_override: true, conflict_fingerprint: 'review-token',
      conflicts: [{ lrn: '123', school_year: '2025-2026', changes: [{ field: 'age', existing: 12, incoming: 13 }] }] },
    { success: true, message: 'Saved' }
  ];
  const options = pageOptions('nurse-upload-sf8.php', {
    bootstrap: { Modal },
    document: { getElementById(id) { return { ...modalElement, id }; } },
    localStorage: { getItem() { return 'Clinic Nurse'; } },
    window: { location: {} },
    setTimeout() {},
    clinicSf8Fetch: async (url, request) => {
      requests.push({ url, body: JSON.parse(request.body) });
      return { json: async () => responses.shift() };
    }
  });
  const state = Object.assign(options.data(), options.methods);
  state.parsedData = { upload_id: 42 };
  options.mounted.call(state);
  await state.approveUpload();
  assert.equal(modals.conflictModal.shown, true);
  assert.equal(modals.errorModal.shown, false);
  assert.equal(state.conflicts[0].changes[0].incoming, 13);
  assert.equal(requests[0].body.override_existing, false);
  await state.confirmOverride();
  assert.equal(requests[1].body.override_existing, true);
  assert.equal(requests[1].body.conflict_fingerprint, 'review-token');

  responses.push({ success: false, message: 'Invalid SF8 file' });
  await state.approveUpload();
  assert.equal(modals.errorModal.shown, true);
  assert.equal(state.errorMessage, 'Invalid SF8 file');
  responses.push({ success: false, identity_conflicts: [{ learner_name: 'Learner',
    school_year: '2025-2026', existing_lrn: '123', incoming_lrn: '1230', source: 'Student Information' }] });
  await state.approveUpload();
  assert.equal(modals.identityModal.shown, true);
  assert.equal(state.identityConflicts[0].existing_lrn, '123');
}

function testUploadFilters() {
  const options = pageOptions('nurse-sf8-uploads.php');
  const state = Object.assign(options.data(), options.methods);
  state.uploads = [
    { upload_id: 1, file_name: 'ARH.xlsx', report_code: 'adolescent_reproductive_health_arh', uploaded_by_email: 'nurse@example.test', status: 'Pending' },
    { upload_id: 2, file_name: 'Student.xlsx', report_code: 'students_information', uploaded_by_email: 'teacher@example.test', status: 'Approved' },
    { upload_id: 3, file_name: 'Old.xlsx', report_code: null, uploaded_by_email: 'teacher@example.test', status: 'Pending' }
  ];
  state.selectedReportCode = 'adolescent_reproductive_health_arh';
  assert.deepEqual(Array.from(options.computed.filteredUploads.call(state), row => row.upload_id), [1]);
  state.searchQuery = 'missing';
  assert.equal(options.computed.filteredUploads.call(state).length, 0);
  state.searchQuery = 'arh';
  assert.deepEqual(Array.from(options.computed.filteredUploads.call(state), row => row.upload_id), [1]);
  state.searchQuery = '';
  state.selectedReportCode = '__unspecified__';
  assert.deepEqual(Array.from(options.computed.filteredUploads.call(state), row => row.upload_id), [3]);
}

async function testPreviewConflict() {
  let hiddenHandler;
  const requests = [];
  const responses = [
    { success: false, requires_override: true, conflict_fingerprint: 'preview-token',
      conflicts: [{ lrn: '123', school_year: '2025-2026', changes: [] }] },
    { success: true, message: 'Processed 1 record' }
  ];
  const options = pageOptions('nurse-sf8-preview.php', {
    document: { getElementById() { return { addEventListener(name, callback) { hiddenHandler = callback; } }; } },
    window: { location: {} },
    clinicSf8Fetch: async (url, request) => {
      requests.push({ url, body: JSON.parse(request.body) });
      return { json: async () => responses.shift() };
    }
  });
  const state = Object.assign(options.data(), options.methods);
  state.uploadId = '42';
  state.conflictModal = {
    shown: false,
    show() { this.shown = true; },
    hide() { this.shown = false; hiddenHandler(); }
  };
  await state.approveUpload();
  assert.equal(state.conflictModal.shown, true);
  assert.equal(requests[0].body.override_existing, false);
  await state.confirmOverride();
  assert.equal(requests[1].body.override_existing, true);
  assert.equal(requests[1].body.conflict_fingerprint, 'preview-token');
  responses.push({ success: false, identity_conflicts: [{ learner_name: 'Learner',
    school_year: '2025-2026', existing_lrn: '123', incoming_lrn: '1230', source: 'Student Information' }] });
  state.identityModal = { shown: false, show() { this.shown = true; } };
  await state.approveUpload();
  assert.equal(state.identityModal.shown, true);
}

Promise.resolve().then(testOfflineConflict).then(testPreviewConflict).then(testUploadFilters).then(() => {
  console.log('Nurse SF8 modal and filter tests passed');
}).catch(error => { console.error(error); process.exitCode = 1; });
