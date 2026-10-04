// Tokens are sent only to the same-origin SF8 APIs, never to Cloudinary.
function clinicSf8TokenExpiresSoon(token) {
  try {
    const payload = JSON.parse(atob(token.split('.')[1].replace(/-/g, '+').replace(/_/g, '/')));
    return typeof payload.exp === 'number' && payload.exp * 1000 <= Date.now() + 60000;
  } catch (_) {
    return false;
  }
}

async function clinicRefreshTeacherToken() {
  const refreshToken = localStorage.getItem('teacher_refresh_token');
  const apiKey = window.clinicFirebaseApiKey;
  if (!refreshToken || !apiKey) throw new Error('Please sign in again to access secure SF8 uploads.');
  let response;
  try {
    response = await fetch('https://securetoken.googleapis.com/v1/token?key=' + encodeURIComponent(apiKey), {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ grant_type: 'refresh_token', refresh_token: refreshToken })
    });
  } catch (_) {
    throw new Error('Could not renew your teacher session. Check your connection and try again.');
  }
  const data = await response.json().catch(() => ({}));
  if (!response.ok || !data.id_token || !data.refresh_token) {
    localStorage.removeItem('teacher_id_token');
    localStorage.removeItem('teacher_refresh_token');
    throw new Error('Your teacher session has expired. Please sign in again.');
  }
  localStorage.setItem('teacher_id_token', data.id_token);
  localStorage.setItem('teacher_refresh_token', data.refresh_token);
  return data.id_token;
}

async function clinicSf8Fetch(url, options = {}) {
  const target = new URL(url, window.location.href);
  if (target.origin !== window.location.origin) throw new Error('Secure requests must stay within ClinicDesk.');
  const { authRole, ...requestOptions } = options;
  const role = authRole || localStorage.getItem('active_role');
  let token = role === 'Teacher' ? localStorage.getItem('teacher_id_token')
    : role === 'Clinic Nurse' ? localStorage.getItem('local_id_token') : null;
  if (role === 'Teacher' && localStorage.getItem('teacher_refresh_token') && (!token || clinicSf8TokenExpiresSoon(token))) {
    token = await clinicRefreshTeacherToken();
  }
  if (!token) throw new Error('Please sign in again to access secure SF8 uploads.');
  const send = currentToken => {
    const headers = new Headers(requestOptions.headers || {});
    headers.set('Authorization', 'Bearer ' + currentToken);
    return fetch(target.href, { ...requestOptions, headers, cache: 'no-store', credentials: 'same-origin' });
  };
  let response = await send(token);
  if (response.status === 401 && role === 'Teacher' && localStorage.getItem('teacher_refresh_token')) {
    token = await clinicRefreshTeacherToken();
    response = await send(token);
  }
  return response;
}
