<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ClinicDesk | Prediction Settings</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    body{min-height:100vh;background:radial-gradient(circle at 10% 0%,rgba(20,184,166,.14),transparent 30%),linear-gradient(160deg,#eef8fb,#f8fcfd);font-family:'Plus Jakarta Sans',system-ui,sans-serif;color:#16323f}
    .wrap{max-width:960px;margin:auto;padding:28px 20px 64px}
    .hero{background:linear-gradient(135deg,#0f766e,#14b8a6);color:#fff;border-radius:26px;padding:30px;display:flex;justify-content:space-between;align-items:center;gap:16px;box-shadow:0 16px 40px rgba(15,118,110,.18)}
    .hero h1{font-size:28px;font-weight:800;margin:0 0 6px}.hero p{margin:0;opacity:.9}
    .back{background:#fff;color:#0f766e;border-radius:12px;padding:10px 16px;text-decoration:none;font-weight:800;white-space:nowrap}
    .panel{background:#fff;border:1px solid #d9eef0;border-radius:20px;padding:28px;margin-top:24px;box-shadow:0 8px 28px rgba(15,118,110,.09)}
    .state{display:flex;align-items:center;gap:13px;padding:16px;border-radius:14px;background:#f1f5f9;color:#475569;font-weight:700}
    .state.ready{background:#dcfce7;color:#166534}.state.starting{background:#fef3c7;color:#92400e}.state.offline{background:#fef2f2;color:#991b1b}
    .state i{font-size:25px}.action{border:0;border-radius:12px;background:linear-gradient(135deg,#0f766e,#14b8a6);color:#fff;font-weight:800;padding:11px 22px}
    .action:disabled{opacity:.55}.muted{color:#64748b;font-size:14px}.modal-content{border:1px solid #d9eef0;border-radius:20px}.modal-header{border-bottom:1px solid #d9eef0}.modal-footer{border-top:1px solid #d9eef0}
    @media(max-width:650px){.hero{align-items:flex-start;flex-direction:column}.panel{padding:20px}}
  </style>
</head>
<body>
  <main class="wrap">
    <header class="hero">
      <div><h1><i class="bi bi-cpu me-2"></i>Prediction Service</h1><p>Check and activate the model used by student health screening.</p></div>
      <a href="nurse-dashboard.php" class="back"><i class="bi bi-arrow-left me-1"></i>Back to Dashboard</a>
    </header>
    <section class="panel">
      <h2 class="h5 fw-bold mb-3">Model status</h2>
      <div id="serviceState" class="state"><i class="bi bi-hourglass-split"></i><span>Checking model status...</span></div>
      <p id="modelVersion" class="muted mt-3 mb-0"></p>
      <div id="notice" class="alert d-none mt-3 mb-0" role="alert"></div>
      <div class="d-flex gap-2 flex-wrap mt-4">
        <button id="activateButton" class="action" type="button" disabled data-bs-toggle="modal" data-bs-target="#passwordModal"><i class="bi bi-power me-1"></i>Activate Prediction</button>
        <button id="refreshButton" class="btn btn-outline-secondary fw-bold" type="button">Refresh status</button>
      </div>
      <p class="muted mt-3 mb-0">Activation uses your Clinic Nurse account password. Once ready, return to a student's health assessment to run a prediction.</p>
    </section>
  </main>

  <div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
      <form id="activationForm">
        <div class="modal-header"><h2 class="modal-title fs-5 fw-bold" id="passwordTitle">Activate Prediction Service</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <div class="modal-body">
          <p class="muted">Confirm your identity to start the model on this computer.</p>
          <label for="accountPassword" class="form-label fw-bold">Clinic Nurse password</label>
          <input id="accountPassword" class="form-control" type="password" autocomplete="current-password" required>
          <div id="modalError" class="text-danger small mt-2" role="alert"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Cancel</button><button id="confirmButton" type="submit" class="action">Activate</button></div>
      </form>
    </div></div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const token = localStorage.getItem('local_id_token');
    if (localStorage.getItem('active_role') !== 'Clinic Nurse' || !token) location.replace('login.php');
    const stateBox = document.getElementById('serviceState');
    const activateButton = document.getElementById('activateButton');
    const notice = document.getElementById('notice');
    const modalElement = document.getElementById('passwordModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    let poll = null;
    function showNotice(message, error = false) {
      notice.textContent = message;
      notice.className = 'alert mt-3 mb-0 ' + (error ? 'alert-danger' : 'alert-info');
    }
    function renderStatus(data) {
      const state = ['ready','starting','offline'].includes(data.state) ? data.state : 'offline';
      stateBox.className = 'state ' + state;
      stateBox.querySelector('i').className = 'bi ' + (state === 'ready' ? 'bi-check-circle-fill' : state === 'starting' ? 'bi-hourglass-split' : 'bi-exclamation-circle-fill');
      stateBox.querySelector('span').textContent = data.message || 'Model status unavailable.';
      activateButton.disabled = state !== 'offline';
      document.getElementById('modelVersion').textContent = state === 'ready' ? `${data.model || 'Model'} · ${data.version || ''}` : '';
      if (poll) clearTimeout(poll);
      if (state === 'starting') poll = setTimeout(loadStatus, 2000);
    }
    async function loadStatus() {
      try {
        const res = await fetch('api/prediction_service.php', {headers:{Authorization:`Bearer ${token}`}, cache:'no-store'});
        if (res.status === 401) { location.replace('login.php'); return; }
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Status check failed.');
        renderStatus(data);
      } catch (error) { renderStatus({state:'offline',message:'Could not check the model service.'}); showNotice(error.message, true); }
    }
    document.getElementById('refreshButton').addEventListener('click', loadStatus);
    modalElement.addEventListener('hidden.bs.modal', () => {document.getElementById('activationForm').reset();document.getElementById('modalError').textContent = '';});
    document.getElementById('activationForm').addEventListener('submit', async event => {
      event.preventDefault();
      const password = document.getElementById('accountPassword').value;
      const confirm = document.getElementById('confirmButton');
      confirm.disabled = true;
      document.getElementById('modalError').textContent = '';
      try {
        const res = await fetch('api/prediction_service.php', {method:'POST',headers:{'Content-Type':'application/json',Authorization:`Bearer ${token}`},body:JSON.stringify({password})});
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Activation failed.');
        modal.hide();
        renderStatus(data);
        showNotice(data.message || 'Model activation requested.');
      } catch (error) {document.getElementById('modalError').textContent = error.message;}
      finally {document.getElementById('accountPassword').value = '';confirm.disabled = false;}
    });
    loadStatus();
  </script>
</body>
</html>
