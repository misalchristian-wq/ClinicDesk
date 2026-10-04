const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
(async () => {
  const storage = new Map();
  const calls = [];
  let rejectTeacherOnce = false;
  const context = vm.createContext({ URL, Headers, URLSearchParams, atob,
    window: { location: { href: 'https://clinic.example/ClinicDesk/nurse-upload-sf8.php', origin: 'https://clinic.example' }, clinicFirebaseApiKey: 'synthetic-public-key' },
    localStorage: { getItem: key => storage.get(key) || null, setItem: (key, value) => storage.set(key, value), removeItem: key => storage.delete(key) },
    fetch: async (url, options) => {
      calls.push({ url, options });
      if (url.startsWith('https://securetoken.googleapis.com/')) {
        assert.equal(options.body.get('grant_type'), 'refresh_token');
        assert.equal(options.body.get('refresh_token'), 'synthetic-refresh-token');
        return { ok: true, json: async () => ({ id_token: 'renewed-teacher-token', refresh_token: 'rotated-refresh-token' }) };
      }
      if (rejectTeacherOnce) { rejectTeacherOnce = false; return { status: 401 }; }
      return { ok: true, status: 200 };
    }
  });
  vm.runInContext(fs.readFileSync('assets/sf8-security.js', 'utf8'), context);
  storage.set('active_role', 'Clinic Nurse');
  storage.set('local_id_token', 'synthetic-nurse-token');
  const form = { synthetic: true };
  await context.clinicSf8Fetch('api/upload_sf8_local.php', { method: 'POST', body: form });
  assert.equal(calls[0].options.headers.get('Authorization'), 'Bearer synthetic-nurse-token');
  assert.equal(calls[0].options.headers.has('Content-Type'), false);
  assert.equal(calls[0].options.body, form);
  assert.equal(calls[0].options.cache, 'no-store');
  assert.equal(calls[0].options.credentials, 'same-origin');
  await assert.rejects(context.clinicSf8Fetch('https://api.cloudinary.com/v1_1/demo/raw/upload'), /within ClinicDesk/);
  assert.equal(calls.length, 1);
  storage.set('active_role', 'Teacher');
  storage.set('teacher_id_token', 'synthetic-teacher-token');
  await context.clinicSf8Fetch('api/upload_sf8.php');
  assert.equal(calls[1].options.headers.get('Authorization'), 'Bearer synthetic-teacher-token');
  storage.set('teacher_refresh_token', 'synthetic-refresh-token');
  const expired = 'header.' + Buffer.from(JSON.stringify({ exp: 1 })).toString('base64url') + '.signature';
  storage.set('teacher_id_token', expired);
  await context.clinicSf8Fetch('api/upload_sf8.php', { authRole: 'Teacher' });
  assert.equal(calls[2].url.startsWith('https://securetoken.googleapis.com/'), true);
  assert.equal(calls[3].options.headers.get('Authorization'), 'Bearer renewed-teacher-token');
  assert.equal(calls[3].options.authRole, undefined);
  assert.equal(storage.get('teacher_refresh_token'), 'rotated-refresh-token');
  storage.set('teacher_refresh_token', 'synthetic-refresh-token');
  rejectTeacherOnce = true;
  await context.clinicSf8Fetch('api/upload_sf8.php', { authRole: 'Teacher' });
  assert.equal(calls.at(-1).options.headers.get('Authorization'), 'Bearer renewed-teacher-token');
  storage.delete('teacher_id_token');
  storage.delete('teacher_refresh_token');
  await assert.rejects(context.clinicSf8Fetch('api/upload_sf8.php'), /sign in again/);
  storage.set('active_role', 'School Admin');
  await assert.rejects(context.clinicSf8Fetch('api/parse_sf8_from_upload.php'), /sign in again/);
  console.log('PASS: secure browser requests select the right login token, preserve multipart uploads, and never send tokens to Cloudinary.');
})().catch(error => { console.error(error.message); process.exitCode = 1; });
