<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | Principal Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    :root {
      --clinic-primary: #0f766e;
      --clinic-secondary: #14b8a6;
      --clinic-accent: #0ea5e9;
      --clinic-bg: #f5fafb;
      --clinic-soft: #ecfeff;
      --clinic-light: #f0fdfa;
      --clinic-card: #ffffff;
      --clinic-border: #d9eef0;
      --clinic-text: #16323f;
      --clinic-muted: #6b7d87;
      --clinic-shadow: 0 18px 40px rgba(15, 118, 110, 0.10);
      --clinic-radius: 24px;
    }

    * {
      box-sizing: border-box;
    }

    body {
      min-height: 100vh;
      margin: 0;
      background: #f5fafb;
      font-family: 'Plus Jakarta Sans', Arial, sans-serif;
      color: var(--clinic-text);
      overflow-x: hidden;
    }

    .admin-shell {
      min-height: 100vh;
      display: grid;
      grid-template-columns: 300px 1fr;
    }

    .sidebar {
      background: #ffffff;
      border-right: 1px solid var(--clinic-border);
      padding: 26px 22px;
      position: sticky;
      top: 0;
      height: 100vh;
      overflow-y: auto;
    }

    .brand-row {
      display: flex;
      align-items: center;
      gap: 13px;
      margin-bottom: 28px;
    }

    .brand-icon {
      width: 56px;
      height: 56px;
      border-radius: 18px;
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 27px;
      box-shadow: 0 14px 28px rgba(15, 118, 110, 0.18);
      flex-shrink: 0;
    }

    .brand-title {
      font-size: 22px;
      font-weight: 900;
      color: var(--clinic-primary);
      line-height: 1;
    }

    .brand-subtitle {
      font-size: 12px;
      color: var(--clinic-muted);
      margin-top: 4px;
      font-weight: 700;
    }

    .principal-card {
      background: linear-gradient(135deg, #ecfeff, #f0fdfa);
      border: 1px solid var(--clinic-border);
      border-radius: 22px;
      padding: 18px;
      margin-bottom: 24px;
    }

    .principal-avatar {
      width: 58px;
      height: 58px;
      border-radius: 18px;
      background: white;
      border: 1px solid var(--clinic-border);
      color: var(--clinic-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 27px;
      margin-bottom: 12px;
    }

    .principal-name {
      font-size: 16px;
      font-weight: 900;
      color: var(--clinic-text);
      margin-bottom: 3px;
      word-break: break-word;
    }

    .principal-role {
      font-size: 13px;
      color: var(--clinic-muted);
      font-weight: 700;
      margin-bottom: 0;
    }

    .side-label {
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      color: var(--clinic-muted);
      font-weight: 900;
      margin-bottom: 12px;
    }

    .side-menu {
      display: grid;
      gap: 10px;
      margin-bottom: 26px;
    }

    .side-link {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 13px 14px;
      border-radius: 16px;
      color: var(--clinic-text);
      text-decoration: none;
      font-weight: 800;
      transition: 0.2s ease;
      border: 1px solid transparent;
    }

    .side-link:hover {
      background: var(--clinic-soft);
      color: var(--clinic-primary);
      border-color: var(--clinic-border);
    }

    .side-link.active {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white;
      box-shadow: 0 12px 24px rgba(15, 118, 110, 0.18);
    }

    .side-icon {
      width: 34px;
      height: 34px;
      border-radius: 12px;
      background: rgba(15, 118, 110, 0.08);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .side-link.active .side-icon {
      background: rgba(255, 255, 255, 0.18);
    }

    .logout-btn {
      width: 100%;
      border: 1px solid #fecaca;
      background: #fff5f5;
      color: #dc2626;
      border-radius: 16px;
      padding: 12px 14px;
      font-weight: 900;
      margin-top: 10px;
    }

    .logout-btn:hover {
      background: #fee2e2;
      color: #991b1b;
    }

    .main-content {
      padding: 28px;
      min-width: 0;
    }

    .topbar {
      background: white;
      border: 1px solid var(--clinic-border);
      border-radius: 24px;
      padding: 20px 22px;
      box-shadow: 0 10px 26px rgba(15, 118, 110, 0.07);
      margin-bottom: 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 18px;
      flex-wrap: wrap;
    }

    .page-kicker {
      font-size: 13px;
      font-weight: 900;
      color: var(--clinic-secondary);
      text-transform: uppercase;
      letter-spacing: 0.8px;
      margin-bottom: 5px;
    }

    .page-title {
      font-size: 31px;
      font-weight: 900;
      color: var(--clinic-text);
      margin-bottom: 4px;
    }

    .page-subtitle {
      color: var(--clinic-muted);
      font-size: 14px;
      margin-bottom: 0;
      line-height: 1.5;
    }

    .date-chip {
      background: var(--clinic-soft);
      border: 1px solid var(--clinic-border);
      color: var(--clinic-primary);
      border-radius: 999px;
      padding: 10px 14px;
      font-weight: 900;
      font-size: 13px;
      white-space: nowrap;
    }

    .executive-layout {
      display: grid;
      grid-template-columns: 1.15fr 0.85fr;
      gap: 24px;
      margin-bottom: 24px;
    }

    .executive-card {
      background:
        radial-gradient(circle at right top, rgba(20, 184, 166, 0.22), transparent 30%),
        linear-gradient(135deg, #ffffff, #f0fdfa);
      border: 1px solid var(--clinic-border);
      border-radius: 28px;
      padding: 30px;
      box-shadow: var(--clinic-shadow);
      position: relative;
      overflow: hidden;
    }

    .executive-card::after {
      content: "";
      position: absolute;
      right: -80px;
      bottom: -80px;
      width: 220px;
      height: 220px;
      background: rgba(14, 165, 233, 0.10);
      border-radius: 50%;
    }

    .executive-content {
      position: relative;
      z-index: 2;
    }

    .executive-tag {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: white;
      border: 1px solid var(--clinic-border);
      color: var(--clinic-primary);
      padding: 9px 13px;
      border-radius: 999px;
      font-size: 13px;
      font-weight: 900;
      margin-bottom: 18px;
    }

    .executive-title {
      font-size: 34px;
      font-weight: 900;
      color: var(--clinic-primary);
      margin-bottom: 12px;
      max-width: 760px;
    }

    .executive-text {
      color: var(--clinic-muted);
      font-size: 15px;
      line-height: 1.7;
      max-width: 760px;
      margin-bottom: 22px;
    }

    .executive-actions {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
    }

    .btn-main {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white;
      border: none;
      border-radius: 16px;
      padding: 12px 18px;
      font-weight: 900;
      text-decoration: none;
      box-shadow: 0 14px 26px rgba(15, 118, 110, 0.18);
      display: inline-block;
    }

    .btn-main:hover {
      color: white;
      transform: translateY(-1px);
    }

    .btn-soft {
      background: white;
      color: var(--clinic-primary);
      border: 1px solid var(--clinic-border);
      border-radius: 16px;
      padding: 12px 18px;
      font-weight: 900;
      text-decoration: none;
      display: inline-block;
    }

    .btn-soft:hover {
      background: var(--clinic-soft);
      color: var(--clinic-primary);
    }

    .mini-grid {
      display: grid;
      gap: 14px;
    }

    .mini-card {
      background: white;
      border: 1px solid var(--clinic-border);
      border-radius: 22px;
      padding: 20px;
      box-shadow: 0 10px 26px rgba(15, 118, 110, 0.07);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
    }

    .mini-label {
      font-size: 13px;
      color: var(--clinic-muted);
      font-weight: 900;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 4px;
    }

    .mini-value {
      font-size: 25px;
      font-weight: 900;
      color: var(--clinic-primary);
      margin-bottom: 0;
    }

    .mini-icon {
      width: 52px;
      height: 52px;
      border-radius: 18px;
      background: var(--clinic-soft);
      color: var(--clinic-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 25px;
      flex-shrink: 0;
    }

    .summary-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 18px;
      margin-bottom: 24px;
    }

    .summary-card {
      background: white;
      border: 1px solid var(--clinic-border);
      border-radius: 24px;
      padding: 20px;
      box-shadow: 0 12px 28px rgba(15, 118, 110, 0.08);
      position: relative;
      overflow: hidden;
    }

    .summary-card::after {
      content: "";
      position: absolute;
      top: -42px;
      right: -42px;
      width: 120px;
      height: 120px;
      background: rgba(20, 184, 166, 0.08);
      border-radius: 50%;
    }

    .summary-label {
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      color: var(--clinic-muted);
      font-weight: 900;
      margin-bottom: 8px;
      position: relative;
      z-index: 2;
    }

    .summary-value {
      font-size: 31px;
      font-weight: 900;
      color: var(--clinic-primary);
      margin-bottom: 0;
      position: relative;
      z-index: 2;
    }

    .summary-helper {
      font-size: 13px;
      color: var(--clinic-muted);
      margin-top: 4px;
      margin-bottom: 0;
      position: relative;
      z-index: 2;
    }

    .content-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
      margin-bottom: 24px;
    }

    .panel-card {
      background: white;
      border: 1px solid var(--clinic-border);
      border-radius: 24px;
      padding: 24px;
      box-shadow: 0 12px 28px rgba(15, 118, 110, 0.08);
    }

    .panel-title {
      font-size: 21px;
      font-weight: 900;
      color: var(--clinic-text);
      margin-bottom: 5px;
    }

    .panel-desc {
      color: var(--clinic-muted);
      font-size: 14px;
      margin-bottom: 16px;
    }

    .chart-box {
      height: 280px;
    }

    .priority-list {
      display: grid;
      gap: 12px;
    }

    .priority-item {
      background: #fbfefe;
      border: 1px solid var(--clinic-border);
      border-radius: 18px;
      padding: 15px;
      display: flex;
      gap: 12px;
      align-items: flex-start;
    }

    .priority-icon {
      width: 40px;
      height: 40px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 900;
      flex-shrink: 0;
    }

    .priority-icon.high {
      background: #fee2e2;
      color: #dc2626;
    }

    .priority-icon.medium {
      background: #fef3c7;
      color: #b45309;
    }

    .priority-icon.low {
      background: #dcfce7;
      color: #16a34a;
    }

    .priority-title {
      font-weight: 900;
      color: var(--clinic-text);
      margin-bottom: 3px;
      font-size: 14px;
    }

    .priority-text {
      color: var(--clinic-muted);
      font-size: 13px;
      line-height: 1.5;
      margin-bottom: 0;
    }

    .table-card {
      background: white;
      border: 1px solid var(--clinic-border);
      border-radius: 24px;
      padding: 24px;
      box-shadow: 0 12px 28px rgba(15, 118, 110, 0.08);
    }

    .table-responsive {
      width: 100%;
      overflow-x: auto;
      border-radius: 18px;
      border: 1px solid var(--clinic-border);
      background: white;
      margin-top: 16px;
    }

    .table {
      margin-bottom: 0;
    }

    .table th {
      background: #f1fbfb;
      color: #24404d;
      font-weight: 900;
      white-space: nowrap;
      border-bottom: 1px solid var(--clinic-border);
      font-size: 13px;
      vertical-align: middle;
    }

    .table td {
      vertical-align: middle;
      color: #263f4a;
      border-color: #e5f0f2;
      font-size: 13px;
      white-space: nowrap;
    }

    .table tbody tr:hover td {
      background: #fbfefe;
    }

    .badge {
      border-radius: 999px;
      padding: 7px 10px;
      font-size: 11px;
      font-weight: 900;
    }

    .small-note {
      font-size: 0.9rem;
      color: var(--clinic-muted);
      line-height: 1.5;
    }

    @media (max-width: 1200px) {
      .admin-shell {
        grid-template-columns: 1fr;
      }

      .sidebar {
        position: static;
        height: auto;
        border-right: none;
        border-bottom: 1px solid var(--clinic-border);
      }

      .side-menu {
        grid-template-columns: repeat(2, 1fr);
      }

      .executive-layout,
      .content-grid {
        grid-template-columns: 1fr;
      }

      .summary-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 768px) {
      .main-content {
        padding: 16px;
      }

      .sidebar {
        padding: 20px 16px;
      }

      .side-menu,
      .summary-grid {
        grid-template-columns: 1fr;
      }

      .topbar {
        padding: 18px;
      }

      .page-title {
        font-size: 26px;
      }

      .executive-card {
        padding: 24px;
      }

      .executive-title {
        font-size: 27px;
      }
    }
  </style>
</head>

<body>
<div id="app" class="admin-shell">

  <aside class="sidebar">
    <div class="brand-row">
      <div class="brand-icon">🏫</div>
      <div>
        <div class="brand-title">ClinicDesk</div>
        <div class="brand-subtitle">Principal Workspace</div>
      </div>
    </div>

    <div class="principal-card">
      <div class="principal-avatar">👨‍💼</div>
      <div class="principal-name">{{ adminName }}</div>
      <p class="principal-role">School Admin / Principal</p>
    </div>

    <div class="side-label">Navigation</div>

    <div class="side-menu">
      <a href="school-admin-dashboard.php" class="side-link active">
        <span class="side-icon">🏠</span>
        Overview
      </a>

      <a href="reports.php" class="side-link">
        <span class="side-icon">📊</span>
        Reports
      </a>

    
    </div>

    <div class="side-label">Session</div>

    <button class="logout-btn" @click="logout">
      Logout
    </button>
  </aside>

  <main class="main-content">

    <div class="topbar">
      <div>
        <div class="page-kicker">Principal Monitoring Portal</div>
        <h1 class="page-title">School Health Overview</h1>
        <p class="page-subtitle">
          Executive summary of student nutritional records, risk levels, and clinic monitoring progress.
        </p>
      </div>

      <div class="date-chip">
        {{ currentDate }}
      </div>
    </div>

    <div class="executive-layout">
      <section class="executive-card">
        <div class="executive-content">
          <div class="executive-tag">
            <span>📌</span>
            Executive School Health Summary
          </div>

          <h2 class="executive-title">
            Welcome, {{ adminName }}.
          </h2>

          <p class="executive-text">
            This dashboard provides a school-level overview of student nutritional monitoring.
            It helps the principal review high-risk students, monitor report summaries, and support
            school clinic decisions based on approved student health records.
          </p>

          
        </div>
      </section>

      <section class="mini-grid">
        <div class="mini-card">
          <div>
            <div class="mini-label">Dashboard Access</div>
            <p class="mini-value">Read-only</p>
          </div>
          <div class="mini-icon">🔒</div>
        </div>

        <div class="mini-card">
          <div>
            <div class="mini-label">Report Status</div>
            <p class="mini-value">Available</p>
          </div>
          <div class="mini-icon">📄</div>
        </div>

        <div class="mini-card">
          <div>
            <div class="mini-label">Monitoring Scope</div>
            <p class="mini-value">School-wide</p>
          </div>
          <div class="mini-icon">🏫</div>
        </div>
      </section>
    </div>

    <div class="summary-grid">
      <div class="summary-card">
        <div class="summary-label">Total Students</div>
        <p class="summary-value">{{ records.length }}</p>
        <p class="summary-helper">Approved nutritional records</p>
      </div>

      <div class="summary-card">
        <div class="summary-label">High Risk</div>
        <p class="summary-value">{{ highRiskCount }}</p>
        <p class="summary-helper">Priority clinic follow-up</p>
      </div>

      <div class="summary-card">
        <div class="summary-label">Moderate Risk</div>
        <p class="summary-value">{{ moderateRiskCount }}</p>
        <p class="summary-helper">Needs regular monitoring</p>
      </div>

      <div class="summary-card">
        <div class="summary-label">Low Risk</div>
        <p class="summary-value">{{ lowRiskCount }}</p>
        <p class="summary-helper">Routine monitoring</p>
      </div>
    </div>

    <div class="content-grid">
      <div class="panel-card">
        <h2 class="panel-title">Risk Level Distribution</h2>
        <p class="panel-desc">
          Overall student risk classification based on BMI and height-for-age.
        </p>

        <div class="chart-box">
          <canvas id="riskChart"></canvas>
        </div>
      </div>

      <div class="panel-card">
        <h2 class="panel-title">BMI Category Distribution</h2>
        <p class="panel-desc">
          Overview of student BMI categories across approved records.
        </p>

        <div class="chart-box">
          <canvas id="bmiChart"></canvas>
        </div>
      </div>
    </div>

    <div class="content-grid">
      <div class="panel-card">
        <h2 class="panel-title">Principal Action Priorities</h2>
        <p class="panel-desc">
          Suggested administrative support based on student health risk.
        </p>

        <div class="priority-list">
          <div class="priority-item">
            <div class="priority-icon high">!</div>
            <div>
              <div class="priority-title">High Risk Monitoring</div>
              <p class="priority-text">
                Coordinate with the clinic nurse to prioritize students classified as high risk.
              </p>
            </div>
          </div>

          <div class="priority-item">
            <div class="priority-icon medium">2</div>
            <div>
              <div class="priority-title">Parent or Guardian Communication</div>
              <p class="priority-text">
                Support follow-up communication when the clinic nurse recommends additional intervention.
              </p>
            </div>
          </div>

          <div class="priority-item">
            <div class="priority-icon low">✓</div>
            <div>
              <div class="priority-title">Routine School Health Programs</div>
              <p class="priority-text">
                Continue school-wide health promotion, hygiene reminders, and nutrition awareness activities.
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="panel-card">
        <h2 class="panel-title">Administrative Notes</h2>
        <p class="panel-desc">
          Summary notes for report review and decision support.
        </p>

        <div class="priority-list">
          <div class="priority-item">
            <div class="priority-icon low">📊</div>
            <div>
              <div class="priority-title">Review Printed Reports</div>
              <p class="priority-text">
                Use the reports page to generate school-level nutritional monitoring summaries.
              </p>
            </div>
          </div>

          <div class="priority-item">
            <div class="priority-icon medium">👥</div>
            <div>
              <div class="priority-title">Coordinate With Teachers</div>
              <p class="priority-text">
                Teachers may view nurse-generated reports for classroom monitoring and student support.
              </p>
            </div>
          </div>

          <div class="priority-item">
            <div class="priority-icon high">🩺</div>
            <div>
              <div class="priority-title">Clinic Nurse Handles Screening</div>
              <p class="priority-text">
                Health assessment screening and meal plan monitoring remain under clinic nurse access.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="table-card">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h2 class="panel-title mb-1">Recent Student Nutritional Records</h2>
          <p class="panel-desc mb-0">
            Read-only preview of approved student health records.
          </p>
        </div>

        <a href="reports.php" class="btn-main">
          View Full Report
        </a>
      </div>

      <div v-if="loading" class="alert alert-info mt-3">
        Loading school health overview...
      </div>

      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Student</th>
              <th>Grade/Section</th>
              <th>BMI</th>
              <th>BMI Category</th>
              <th>Height-for-Age</th>
              <th>Risk Level</th>
              <th>Recommendation</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="records.length === 0 && !loading">
              <td colspan="8" class="text-center text-muted p-4">
                No approved student records found.
              </td>
            </tr>

            <tr v-for="(record, index) in recentRecords" :key="record.record_id || index">
              <td>{{ index + 1 }}</td>
              <td class="fw-bold">{{ record.learner_name || "-" }}</td>
              <td>{{ record.grade_level || "-" }} - {{ record.section || "-" }}</td>
              <td>{{ record.bmi || "-" }}</td>

              <td>
                <span class="badge" :class="getBmiBadge(record.bmi_category)">
                  {{ record.bmi_category || "For Review" }}
                </span>
              </td>

              <td>{{ getHeightForAge(record) }}</td>

              <td>
                <span class="badge" :class="getRiskBadge(getRiskLevel(record))">
                  {{ getRiskLevel(record) }}
                </span>
              </td>

              <td>{{ getRecommendation(record) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<script>
const { createApp } = Vue;

createApp({
  data() {
    return {
      adminName: "",
      currentDate: "",
      records: [],
      loading: false,

      riskChart: null,
      bmiChart: null
    };
  },

  computed: {
    recentRecords() {
      return this.records.slice(0, 8);
    },

    highRiskCount() {
      return this.records.filter(record => this.getRiskLevel(record) === "High").length;
    },

    moderateRiskCount() {
      return this.records.filter(record => this.getRiskLevel(record) === "Moderate").length;
    },

    lowRiskCount() {
      return this.records.filter(record => this.getRiskLevel(record) === "Low").length;
    },

    forReviewCount() {
      return this.records.filter(record => this.getRiskLevel(record) === "For Review").length;
    },

    normalBmiCount() {
      return this.records.filter(record => String(record.bmi_category || "") === "Normal").length;
    },

    underweightCount() {
      return this.records.filter(record => String(record.bmi_category || "") === "Underweight").length;
    },

    severelyUnderweightCount() {
      return this.records.filter(record => String(record.bmi_category || "") === "Severely Underweight").length;
    },

    overweightCount() {
      return this.records.filter(record => String(record.bmi_category || "") === "Overweight").length;
    },

    obeseCount() {
      return this.records.filter(record => String(record.bmi_category || "") === "Obese").length;
    }
  },

  mounted() {
    const role = localStorage.getItem("active_role");
    const accountId = localStorage.getItem("local_account_id");

    if (role !== "School Admin" || !accountId) {
      window.location.href = "login.php";
      return;
    }

    this.adminName = localStorage.getItem("local_full_name") || "School Admin";

    this.currentDate = new Date().toLocaleDateString("en-US", {
      weekday: "long",
      year: "numeric",
      month: "long",
      day: "numeric"
    });

    this.loadRecords();
  },

  methods: {
    async loadRecords() {
      this.loading = true;

      try {
        const response = await fetch("api/get_student_records.php?cache_buster=" + Date.now());
        const text = await response.text();

        let result;

        try {
          result = JSON.parse(text);
        } catch (jsonError) {
          alert("Student records API did not return JSON. Check api/get_student_records.php.");
          this.loading = false;
          return;
        }

        if (result.success) {
          this.records = result.records || [];

          this.$nextTick(() => {
            this.renderCharts();
          });
        } else {
          alert(result.message || "Failed to load student records.");
        }

      } catch (error) {
        alert("Error loading student records: " + error.message);
      }

      this.loading = false;
    },

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
    },

    getHeightForAge(record) {
      return record.height_for_age_status ||
             record.height_for_age ||
             record.hfa_status ||
             "-";
    },

    getRiskLevel(record) {
      if (record.risk_level) return record.risk_level;

      const bmiCategory = String(record.bmi_category || "").toLowerCase();
      const hfa = String(this.getHeightForAge(record) || "").toLowerCase();

      if (
        bmiCategory.includes("severely") ||
        bmiCategory.includes("obese") ||
        hfa.includes("severely")
      ) {
        return "High";
      }

      if (
        bmiCategory.includes("underweight") ||
        bmiCategory.includes("overweight") ||
        hfa.includes("stunted")
      ) {
        return "Moderate";
      }

      if (
        bmiCategory.includes("normal") &&
        (hfa.includes("normal") || hfa === "-")
      ) {
        return "Low";
      }

      return "For Review";
    },

    getRecommendation(record) {
      if (record.recommendation) return record.recommendation;

      const risk = this.getRiskLevel(record);
      const bmiCategory = String(record.bmi_category || "").toLowerCase();

      if (risk === "High") {
        return "Priority clinic follow-up recommended.";
      }

      if (bmiCategory.includes("underweight")) {
        return "Monitor weight and encourage balanced meals.";
      }

      if (bmiCategory.includes("overweight") || bmiCategory.includes("obese")) {
        return "Encourage healthy diet and physical activity.";
      }

      if (risk === "Moderate") {
        return "Continue monitoring and schedule follow-up.";
      }

      if (risk === "Low") {
        return "Routine monitoring.";
      }

      return "For clinic review.";
    },

    getBmiBadge(category) {
      const text = String(category || "").toLowerCase();

      if (text.includes("normal")) return "bg-success";
      if (text.includes("severely")) return "bg-danger";
      if (text.includes("underweight")) return "bg-warning text-dark";
      if (text.includes("overweight")) return "bg-warning text-dark";
      if (text.includes("obese")) return "bg-danger";

      return "bg-secondary";
    },

    getRiskBadge(risk) {
      if (risk === "Low") return "bg-success";
      if (risk === "Moderate") return "bg-warning text-dark";
      if (risk === "High") return "bg-danger";
      return "bg-primary";
    },

    renderCharts() {
      this.renderRiskChart();
      this.renderBmiChart();
    },

    renderRiskChart() {
      const ctx = document.getElementById("riskChart");

      if (!ctx) return;

      if (this.riskChart) {
        this.riskChart.destroy();
      }

      this.riskChart = new Chart(ctx, {
        type: "doughnut",
        data: {
          labels: ["High", "Moderate", "Low", "For Review"],
          datasets: [{
            data: [
              this.highRiskCount,
              this.moderateRiskCount,
              this.lowRiskCount,
              this.forReviewCount
            ],
            backgroundColor: ["#dc2626", "#f59e0b", "#16a34a", "#0ea5e9"],
            borderWidth: 0
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: "65%",
          plugins: {
            legend: {
              position: "bottom"
            }
          }
        }
      });
    },

    renderBmiChart() {
      const ctx = document.getElementById("bmiChart");

      if (!ctx) return;

      if (this.bmiChart) {
        this.bmiChart.destroy();
      }

      this.bmiChart = new Chart(ctx, {
        type: "bar",
        data: {
          labels: ["Normal", "Underweight", "Severely Underweight", "Overweight", "Obese"],
          datasets: [{
            label: "Students",
            data: [
              this.normalBmiCount,
              this.underweightCount,
              this.severelyUnderweightCount,
              this.overweightCount,
              this.obeseCount
            ],
            backgroundColor: ["#16a34a", "#f59e0b", "#dc2626", "#f97316", "#991b1b"],
            borderRadius: 10
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                precision: 0
              }
            }
          }
        }
      });
    }
  }
}).mount("#app");
</script>
</body>
</html>