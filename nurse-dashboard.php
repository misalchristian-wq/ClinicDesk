<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | Nurse Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    :root {
      --clinic-primary: #0f766e;
      --clinic-secondary: #14b8a6;
      --clinic-accent: #0ea5e9;
      --clinic-bg: #eef8fb;
      --clinic-light: #f0fdfa;
      --clinic-card: rgba(255, 255, 255, 0.96);
      --clinic-border: #d9eef0;
      --clinic-text: #16323f;
      --clinic-muted: #6b7d87;
      --clinic-shadow: 0 12px 32px rgba(15, 118, 110, 0.10);
      --clinic-radius: 22px;
    }

    * {
      box-sizing: border-box;
    }

    body {
      min-height: 100vh;
      margin: 0;
      background:
        radial-gradient(circle at top left, rgba(20,184,166,0.16), transparent 25%),
        radial-gradient(circle at top right, rgba(14,165,233,0.12), transparent 25%),
        linear-gradient(135deg, #eef8fb, #f8fcfd);
      font-family: Arial, sans-serif;
      color: var(--clinic-text);
      overflow-x: hidden;
    }

    .wrapper {
      max-width: 1250px;
      margin: 28px auto;
      padding: 20px;
    }

    .header-box {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white;
      padding: 34px;
      border-radius: 28px;
      margin-bottom: 24px;
      box-shadow: 0 16px 38px rgba(15, 118, 110, 0.22);
      position: relative;
      overflow: hidden;
    }

    .header-box::before {
      content: "";
      position: absolute;
      top: -90px;
      right: -80px;
      width: 230px;
      height: 230px;
      background: rgba(255, 255, 255, 0.16);
      border-radius: 50%;
      filter: blur(4px);
    }

    .header-box::after {
      content: "";
      position: absolute;
      bottom: -110px;
      left: -80px;
      width: 220px;
      height: 220px;
      background: rgba(255, 255, 255, 0.10);
      border-radius: 50%;
      filter: blur(4px);
    }

    .header-content,
    .header-actions {
      position: relative;
      z-index: 2;
    }

    .header-icon {
      width: 62px;
      height: 62px;
      border-radius: 20px;
      background: rgba(255, 255, 255, 0.18);
      border: 2px solid rgba(255, 255, 255, 0.35);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 30px;
      margin-right: 16px;
      box-shadow: 0 0 28px rgba(255, 255, 255, 0.20);
      flex-shrink: 0;
    }

    .header-box h1 {
      font-size: 38px;
      font-weight: 900;
      margin-bottom: 8px;
      text-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    }

    .header-box p {
      font-size: 15px;
      color: rgba(255, 255, 255, 0.92);
    }

    .header-box strong {
      color: white;
    }

    .btn-logout {
      background: white;
      color: var(--clinic-primary);
      border: none;
      border-radius: 15px;
      padding: 11px 18px;
      font-weight: 800;
      box-shadow: 0 12px 28px rgba(0,0,0,0.12);
    }

    .btn-logout:hover {
      background: #ecfeff;
      color: var(--clinic-primary);
    }

    .summary-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 18px;
      margin-bottom: 24px;
    }

    .summary-card {
      background: var(--clinic-card);
      border: 1px solid var(--clinic-border);
      border-radius: var(--clinic-radius);
      padding: 20px;
      box-shadow: var(--clinic-shadow);
    }

    .summary-label {
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      color: var(--clinic-muted);
      font-weight: 800;
      margin-bottom: 8px;
    }

    .summary-value {
      font-size: 28px;
      font-weight: 900;
      color: var(--clinic-primary);
      margin-bottom: 0;
    }

    .summary-helper {
      font-size: 13px;
      color: var(--clinic-muted);
      margin-top: 4px;
      margin-bottom: 0;
    }

    .section-title {
      margin-bottom: 16px;
    }

    .section-title h3 {
      font-weight: 900;
      color: var(--clinic-primary);
      margin-bottom: 4px;
    }

    .section-title p {
      color: var(--clinic-muted);
      margin-bottom: 0;
      font-size: 14px;
    }

    .dashboard-card {
      background: var(--clinic-card);
      border: 1px solid var(--clinic-border);
      border-radius: var(--clinic-radius);
      box-shadow: var(--clinic-shadow);
      padding: 24px;
      height: 100%;
      display: flex;
      flex-direction: column;
      transition: 0.2s ease;
      position: relative;
      overflow: hidden;
    }

    .dashboard-card::before {
      content: "";
      position: absolute;
      top: -40px;
      right: -40px;
      width: 110px;
      height: 110px;
      background: rgba(20, 184, 166, 0.10);
      border-radius: 50%;
    }

    .dashboard-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 16px 40px rgba(15, 118, 110, 0.15);
    }

    .card-icon {
      width: 54px;
      height: 54px;
      border-radius: 17px;
      background: var(--clinic-light);
      color: var(--clinic-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 26px;
      margin-bottom: 16px;
      border: 1px solid var(--clinic-border);
      position: relative;
      z-index: 2;
    }

    .dashboard-card h4 {
      font-weight: 900;
      color: var(--clinic-primary);
      margin-bottom: 10px;
      position: relative;
      z-index: 2;
    }

    .dashboard-card p {
      font-size: 0.92rem;
      color: var(--clinic-muted);
      line-height: 1.6;
      position: relative;
      z-index: 2;
    }

    .btn-green {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white;
      font-weight: 900;
      border: none;
      border-radius: 14px;
      padding: 11px 14px;
      box-shadow: 0 12px 24px rgba(15, 118, 110, 0.18);
      position: relative;
      z-index: 2;
    }

    .btn-green:hover {
      color: white;
      transform: translateY(-1px);
      box-shadow: 0 14px 30px rgba(15, 118, 110, 0.22);
    }

    .btn-outline-clinic {
      border: 1px solid var(--clinic-primary);
      color: var(--clinic-primary);
      background: white;
      font-weight: 900;
      border-radius: 14px;
      padding: 11px 14px;
      position: relative;
      z-index: 2;
    }

    .btn-outline-clinic:hover {
      background: var(--clinic-primary);
      color: white;
    }

    .btn-outline-blue {
      border: 1px solid var(--clinic-accent);
      color: var(--clinic-accent);
      background: white;
      font-weight: 900;
      border-radius: 14px;
      padding: 11px 14px;
      position: relative;
      z-index: 2;
    }

    .btn-outline-blue:hover {
      background: var(--clinic-accent);
      color: white;
    }

    @media (max-width: 992px) {
      .summary-grid {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 768px) {
      .wrapper {
        padding: 14px;
        margin: 12px auto;
      }

      .header-box {
        padding: 26px;
      }

      .header-content {
        align-items: flex-start !important;
      }

      .header-icon {
        width: 50px;
        height: 50px;
        font-size: 24px;
      }

      .header-box h1 {
        font-size: 30px;
      }
    }
  </style>
</head>

<body>
<div id="app" class="wrapper">

  <div class="header-box d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div class="header-content d-flex align-items-center">
      <div class="header-icon">⚕</div>

      <div>
        <h1 class="fw-bold mb-2">Clinic Nurse Dashboard</h1>
        <p class="mb-1">
          Review SF8 submissions, monitor student records, and manage nutritional assessment results.
        </p>
        <p class="mb-0">
          Logged in as <strong>{{ fullName }}</strong>
        </p>
      </div>
    </div>

    <div class="header-actions">
      <button class="btn btn-logout" @click="logout">
        Logout
      </button>
    </div>
  </div>

 
  <div class="section-title">
    <h3>Clinic Nurse Tools</h3>
    <p>Select a module below to continue reviewing SF8 uploads and student nutritional records.</p>
  </div>

  <div class="row g-4">
    <div class="col-md-6 col-lg-4">
      <div class="dashboard-card">
        <div class="card-icon">📄</div>

        <h4>SF8 Uploads</h4>

        <p>
          Preview, approve, or reject SF8 Excel files submitted by teachers. Approved files will generate student records.
        </p>

        <a href="nurse-sf8-uploads.php" class="btn btn-green w-100 mt-auto">
          View Uploads
        </a>
      </div>
    </div>

    <div class="col-md-6 col-lg-4">
      <div class="dashboard-card">
        <div class="card-icon">👥</div>

        <h4>Student Dashboard</h4>

        <p>
          View approved student nutritional records, health assessment inputs, prediction results, and recommendations.
        </p>

        <a href="student-dashboard.php" class="btn btn-outline-clinic w-100 mt-auto">
          View Student Dashboard
        </a>
      </div>
    </div>

    <div class="col-md-6 col-lg-4">
      <div class="dashboard-card">
        <div class="card-icon">📊</div>

        <h4>Reports</h4>

        <p>
          Generate and view nutritional monitoring reports for clinic documentation and school health tracking.
        </p>

        <a href="reports.php" class="btn btn-outline-blue w-100 mt-auto">
          View Reports
        </a>
      </div>
    </div>

    <div class="col-md-6 col-lg-4">
    <div class="dashboard-card">
        <div class="card-icon">📊</div>
        <h4>Model Comparison</h4>
        <p>View performance comparison of ML algorithms and test predictions.</p>
        <a href="model-comparison.php" class="btn btn-outline-clinic w-100 mt-auto">Open Comparison</a>
    </div>
</div>
  </div>

</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<script>
const { createApp } = Vue;

createApp({
  data() {
    return {
      fullName: ""
    };
  },

  mounted() {
    const role = localStorage.getItem("active_role");
    const accountId = localStorage.getItem("local_account_id");

    if (role !== "Clinic Nurse" || !accountId) {
      window.location.href = "login.php";
      return;
    }

    this.fullName = localStorage.getItem("local_full_name") || "Clinic Nurse";
  },

  methods: {
    logout() {
      localStorage.removeItem("active_role");
      localStorage.removeItem("local_account_id");
      localStorage.removeItem("local_full_name");
      localStorage.removeItem("local_email");
      localStorage.removeItem("local_role");
      localStorage.removeItem("local_login_time");

      localStorage.removeItem("teacher_uid");
      localStorage.removeItem("teacher_email");
      localStorage.removeItem("teacher_id_token");
      localStorage.removeItem("teacher_refresh_token");
      localStorage.removeItem("teacher_login_time");

      window.location.href = "login.php";
    }
  }
}).mount("#app");
</script>
</body>
</html>