const assert = require('node:assert/strict');
const { execFileSync } = require('node:child_process');
const vm = require('node:vm');

const page = execFileSync('C:/xampp/php/php.exe', ['teacher-upload-sf8.php'], { encoding: 'utf8' });
const scripts = [...page.matchAll(/<script(?:\s[^>]*)?>([\s\S]*?)<\/script>/g)].map(match => match[1]);
const appScript = scripts.find(script => script.includes('identifyReportType(rows)'));
assert.ok(appScript, 'Upload page classifier was not rendered.');
let app;
vm.runInNewContext(appScript, {
  Vue: { createApp: options => { app = options; return { mount() {} }; } },
  window: {}, localStorage: {}, console
});
const state = app.data();
const set = (rows, cell, value) => {
  const [, letters, row] = cell.match(/^([A-Z]+)(\d+)$/);
  let col = 0;
  for (const letter of letters) col = col * 26 + letter.charCodeAt(0) - 64;
  rows[Number(row) - 1] ??= [];
  rows[Number(row) - 1][col - 1] = value;
};
for (const [code, signature] of Object.entries(state.reportSignatures)) {
  const rows = [];
  set(rows, 'A1', 'wrong_report_code');
  for (const [cell, text] of Object.entries({ B9: 'LRN', C9: "Learner's Name", G9: 'Sex', H9: 'Birthdate', I9: 'Age', ...signature.columns })) set(rows, cell, text);
  assert.equal(app.methods.identifyReportType.call(state, rows)?.code, code, `${code} with wrong A1`);
  set(rows, 'A1', '');
  assert.equal(app.methods.identifyReportType.call(state, rows)?.code, code, `${code} with blank A1`);
  set(rows, 'J9', 'unrelated header');
  assert.equal(app.methods.identifyReportType.call(state, rows), null, `${code} with unsupported columns`);
}
console.log('PASS: browser detects all SF8 types from headers, ignores wrong/blank A1, and rejects unsupported columns.');
