<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | Teacher View Reports</title>
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
      --clinic-shadow: 0 16px 38px rgba(15, 118, 110, 0.08);
      --clinic-radius: 22px;
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

    .teacher-report-shell {
      min-height: 100vh;
      display: grid;
      grid-template-columns: 310px 1fr;
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
      margin-bottom: 26px;
    }

    .brand-icon {
      width: 54px;
      height: 54px;
      border-radius: 18px;
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 26px;
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

    .teacher-card {
      background: linear-gradient(135deg, #ecfeff, #f0fdfa);
      border: 1px solid var(--clinic-border);
      border-radius: 22px;
      padding: 18px;
      margin-bottom: 22px;
    }

    .teacher-avatar {
      width: 54px;
      height: 54px;
      border-radius: 18px;
      background: white;
      border: 1px solid var(--clinic-border);
      color: var(--clinic-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 25px;
      margin-bottom: 12px;
    }

    .teacher-email {
      font-size: 14px;
      font-weight: 900;
      color: var(--clinic-text);
      margin-bottom: 4px;
      word-break: break-all;
    }

    .teacher-role {
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

    .filter-group {
      display: grid;
      gap: 14px;
      margin-bottom: 18px;
    }

    .form-label {
      color: #24404d;
      font-weight: 800;
      font-size: 13px;
      margin-bottom: 7px;
    }

    .form-control,
    .form-select {
      border-radius: 14px;
      border: 1px solid var(--clinic-border);
      padding: 11px 13px;
      font-size: 14px;
      background: white;
      color: var(--clinic-text);
    }

    .form-control:focus,
    .form-select:focus {
      border-color: var(--clinic-secondary);
      box-shadow: 0 0 0 0.2rem rgba(20, 184, 166, 0.16);
    }

    .btn-main {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white;
      border: none;
      border-radius: 15px;
      padding: 11px 16px;
      font-weight: 900;
      text-decoration: none;
      text-align: center;
      box-shadow: 0 12px 24px rgba(15, 118, 110, 0.16);
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
      border-radius: 15px;
      padding: 11px 16px;
      font-weight: 900;
      text-decoration: none;
      text-align: center;
      display: inline-block;
    }

    .btn-soft:hover {
      background: var(--clinic-soft);
      color: var(--clinic-primary);
    }

    .side-actions {
      display: grid;
      gap: 10px;
    }

    .main-area {
      padding: 28px;
      min-width: 0;
    }

    .top-toolbar {
      background: white;
      border: 1px solid var(--clinic-border);
      border-radius: 24px;
      padding: 18px 20px;
      box-shadow: 0 10px 26px rgba(15, 118, 110, 0.06);
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      flex-wrap: wrap;
      margin-bottom: 24px;
    }

    .toolbar-kicker {
      font-size: 12px;
      font-weight: 900;
      color: var(--clinic-secondary);
      text-transform: uppercase;
      letter-spacing: 0.8px;
      margin-bottom: 4px;
    }

    .toolbar-title {
      font-size: 28px;
      font-weight: 900;
      color: var(--clinic-text);
      margin-bottom: 2px;
    }

    .toolbar-subtitle {
      color: var(--clinic-muted);
      font-size: 14px;
      margin-bottom: 0;
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

    .report-paper {
      background: white;
      border: 1px solid var(--clinic-border);
      border-radius: 26px;
      box-shadow: var(--clinic-shadow);
      padding: 34px;
      margin-bottom: 24px;
    }

    .report-letterhead {
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 16px;
      align-items: start;
      border-bottom: 2px solid #d9eef0;
      padding-bottom: 22px;
      margin-bottom: 24px;
    }

    .letterhead-brand {
      display: flex;
      gap: 14px;
      align-items: center;
    }

    .letterhead-logo {
      width: 64px;
      height: 64px;
      border-radius: 20px;
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 30px;
      flex-shrink: 0;
    }

    .report-title {
      font-size: 29px;
      font-weight: 900;
      color: var(--clinic-primary);
      margin-bottom: 4px;
    }

    .report-subtitle {
      color: var(--clinic-muted);
      margin-bottom: 0;
      font-size: 14px;
      line-height: 1.5;
    }

    .report-meta {
      text-align: right;
      min-width: 250px;
    }

    .meta-line {
      font-size: 13px;
      color: var(--clinic-muted);
      margin-bottom: 5px;
    }

    .meta-line strong {
      color: var(--clinic-text);
    }

    .readonly-banner {
      background: #eff6ff;
      border: 1px solid #bfdbfe;
      border-left: 6px solid var(--clinic-accent);
      color: #1e3a8a;
      border-radius: 18px;
      padding: 15px 18px;
      margin-bottom: 24px;
      font-size: 14px;
      line-height: 1.6;
    }

    .summary-strip {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 14px;
      margin-bottom: 24px;
    }

    .summary-box {
      border: 1px solid var(--clinic-border);
      background: #fbfefe;
      border-radius: 18px;
      padding: 16px;
    }

    .summary-label {
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      color: var(--clinic-muted);
      font-weight: 900;
      margin-bottom: 6px;
    }

    .summary-value {
      font-size: 28px;
      font-weight: 900;
      color: var(--clinic-primary);
      margin-bottom: 0;
    }

    .summary-helper {
      color: var(--clinic-muted);
      font-size: 12px;
      margin-top: 3px;
      margin-bottom: 0;
    }

    .section-title {
      font-size: 21px;
      font-weight: 900;
      color: var(--clinic-text);
      margin-bottom: 5px;
    }

    .section-desc {
      color: var(--clinic-muted);
      font-size: 14px;
      margin-bottom: 0;
    }

    .report-section {
      margin-bottom: 26px;
    }

    .chart-layout {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 18px;
      margin-top: 16px;
    }

    .chart-card {
      border: 1px solid var(--clinic-border);
      border-radius: 20px;
      padding: 18px;
      background: #fbfefe;
    }

    .chart-title {
      font-size: 16px;
      font-weight: 900;
      color: var(--clinic-primary);
      margin-bottom: 12px;
    }

    .chart-box {
      height: 260px;
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

    .recommendation-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 14px;
      margin-top: 16px;
    }

    .recommendation-card {
      background: #fbfefe;
      border: 1px solid var(--clinic-border);
      border-radius: 18px;
      padding: 16px;
    }

    .recommendation-title {
      color: var(--clinic-primary);
      font-weight: 900;
      margin-bottom: 8px;
      font-size: 15px;
    }

    .recommendation-text {
      color: var(--clinic-muted);
      font-size: 13px;
      line-height: 1.6;
      margin-bottom: 0;
    }

    .signature-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 26px;
      margin-top: 32px;
    }

    .signature-box {
      padding-top: 26px;
    }

    .signature-line {
      border-top: 1px solid #16323f;
      padding-top: 8px;
      text-align: center;
      font-size: 13px;
      font-weight: 800;
      color: var(--clinic-text);
    }

    .signature-role {
      text-align: center;
      font-size: 12px;
      color: var(--clinic-muted);
      margin-top: 2px;
    }

    .small-note {
      font-size: 0.9rem;
      color: var(--clinic-muted);
      line-height: 1.5;
    }

    .alert {
      border-radius: 16px;
      border: none;
      box-shadow: var(--clinic-shadow);
    }

    .alert-info {
      background: #ecfeff;
      color: #155e75;
      border: 1px solid #bae6fd;
    }

    @media (max-width: 1200px) {
      .teacher-report-shell {
        grid-template-columns: 1fr;
      }

      .sidebar {
        position: static;
        height: auto;
        border-right: none;
        border-bottom: 1px solid var(--clinic-border);
      }

      .filter-group {
        grid-template-columns: repeat(2, 1fr);
      }

      .summary-strip,
      .chart-layout,
      .recommendation-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 768px) {
      .main-area {
        padding: 16px;
      }

      .sidebar {
        padding: 18px;
      }

      .report-paper {
        padding: 20px;
      }

      .report-letterhead {
        grid-template-columns: 1fr;
      }

      .report-meta {
        text-align: left;
      }

      .filter-group,
      .summary-strip,
      .chart-layout,
      .recommendation-grid,
      .signature-grid {
        grid-template-columns: 1fr;
      }

      .toolbar-title,
      .report-title {
        font-size: 24px;
      }
    }

    @media print {
      body {
        background: white !important;
        color: black !important;
        font-family: Arial, sans-serif;
      }

      .sidebar,
      .top-toolbar,
      .no-print {
        display: none !important;
      }

      .teacher-report-shell {
        display: block !important;
      }

      .main-area {
        padding: 0 !important;
      }

      .report-paper {
        border: none !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
      }

      .summary-box,
      .chart-card,
      .recommendation-card {
        box-shadow: none !important;
        border: 1px solid #cccccc !important;
        break-inside: avoid;
      }

      .report-letterhead {
        border-bottom: 2px solid #000 !important;
      }

      .report-title,
      .chart-title,
      .recommendation-title {
        color: black !important;
      }

      .readonly-banner {
        border: 1px solid #888 !important;
        border-left: 4px solid #000 !important;
        background: white !important;
        color: black !important;
      }

      .table th {
        background: #eeeeee !important;
        color: black !important;
      }

      .table td {
        color: black !important;
      }

      .chart-layout {
        grid-template-columns: 1fr 1fr !important;
      }

      .recommendation-grid {
        grid-template-columns: 1fr 1fr 1fr !important;
      }

      @page {
        size: A4 landscape;
        margin: 12mm;
      }
    }
  </style>
</head>

<body>
<div id="app" class="teacher-report-shell">

  <aside class="sidebar no-print">
    <div class="brand-row">
      <div class="brand-icon">📑</div>
      <div>
        <div class="brand-title">ClinicDesk</div>
        <div class="brand-subtitle">Teacher Report View</div>
      </div>
    </div>

    <div class="teacher-card">
      <div class="teacher-avatar">👩‍🏫</div>
      <div class="teacher-email">{{ teacherEmail }}</div>
      <p class="teacher-role">Teacher Account</p>
    </div>

    <div class="side-label">Report Filters</div>

    <div class="filter-group">
      <div>
        <label class="form-label">Search</label>
        <input v-model="searchTerm" type="text" class="form-control" placeholder="Student, grade, section">
      </div>

      <div>
        <label class="form-label">Risk Level</label>
        <select v-model="riskFilter" class="form-select">
          <option value="">All Risk Levels</option>
          <option value="High">High</option>
          <option value="Moderate">Moderate</option>
          <option value="Low">Low</option>
          <option value="For Review">For Review</option>
        </select>
      </div>

      <div>
        <label class="form-label">BMI Category</label>
        <select v-model="bmiFilter" class="form-select">
          <option value="">All BMI Categories</option>
          <option value="Normal">Normal</option>
          <option value="Underweight">Underweight</option>
          <option value="Severely Underweight">Severely Underweight</option>
          <option value="Overweight">Overweight</option>
          <option value="Obese">Obese</option>
        </select>
      </div>

      <div>
        <label class="form-label">Grade</label>
        <select v-model="gradeFilter" class="form-select">
          <option value="">All Grade Levels</option>
          <option value="Grade 7">Grade 7</option>
          <option value="Grade 8">Grade 8</option>
          <option value="Grade 9">Grade 9</option>
          <option value="Grade 10">Grade 10</option>
          <option value="Grade 11">Grade 11</option>
          <option value="Grade 12">Grade 12</option>
        </select>
      </div>
    </div>

    <div class="side-actions">
      <button class="btn-soft" @click="loadRecords">Refresh Data</button>
      <button class="btn-soft" @click="resetFilters">Reset Filters</button>
      <a href="teacher-dashboard.php" class="btn-soft">Back to Dashboard</a>
    </div>
  </aside>

  <main class="main-area">

    <div class="top-toolbar no-print">
      <div>
        <div class="toolbar-kicker">Read-only Teacher Report</div>
        <h1 class="toolbar-title">Generated Nutritional Report</h1>
        <p class="toolbar-subtitle">
          View the nutritional monitoring report generated and approved by the clinic nurse.
        </p>
      </div>

      <div class="date-chip">
        {{ currentDate }}
      </div>
    </div>

    <section class="report-paper">

      <div class="report-letterhead">
        <div class="letterhead-brand">
          <div class="letterhead-logo">⚕</div>

          <div>
            <h2 class="report-title">ClinicDesk Generated Nutritional Report</h2>
            <p class="report-subtitle">
              Student Nutritional Monitoring and Profiling System for School Clinics
            </p>
          </div>
        </div>

        <div class="report-meta">
          <div class="meta-line"><strong>Date Viewed:</strong> {{ currentDate }}</div>
          <div class="meta-line"><strong>Viewed By:</strong> {{ teacherEmail }}</div>
          <div class="meta-line"><strong>Generated By:</strong> Clinic Nurse</div>
          <div class="meta-line"><strong>Total Records:</strong> {{ filteredRecords.length }}</div>
        </div>
      </div>

      <div class="readonly-banner">
        <strong>Teacher View Only:</strong>
        This page allows teachers to view and print reports generated by the clinic nurse. Student assessment editing,
        approval, and health screening actions are restricted to clinic nurse accounts.
      </div>

      <div class="report-section">
        <h3 class="section-title">Report Summary</h3>
        <p class="section-desc">
          This section summarizes student nutritional records using BMI category, height-for-age status,
          and nutritional risk level.
        </p>

        <div class="summary-strip">
          <div class="summary-box">
            <div class="summary-label">Total Students</div>
            <p class="summary-value">{{ filteredRecords.length }}</p>
            <p class="summary-helper">Included records</p>
          </div>

          <div class="summary-box">
            <div class="summary-label">High Risk</div>
            <p class="summary-value">{{ highRiskCount }}</p>
            <p class="summary-helper">Priority follow-up</p>
          </div>

          <div class="summary-box">
            <div class="summary-label">Moderate Risk</div>
            <p class="summary-value">{{ moderateRiskCount }}</p>
            <p class="summary-helper">Needs monitoring</p>
          </div>

          <div class="summary-box">
            <div class="summary-label">Low Risk</div>
            <p class="summary-value">{{ lowRiskCount }}</p>
            <p class="summary-helper">Routine monitoring</p>
          </div>
        </div>
      </div>

      <div class="report-section">
        <h3 class="section-title">Visual Summary</h3>
        <p class="section-desc">
          These charts provide a visual overview of student risk levels and BMI categories.
        </p>

        <div class="chart-layout">
          <div class="chart-card">
            <div class="chart-title">Risk Level Distribution</div>
            <div class="chart-box">
              <canvas id="riskChart"></canvas>
            </div>
          </div>

          <div class="chart-card">
            <div class="chart-title">BMI Category Distribution</div>
            <div class="chart-box">
              <canvas id="bmiChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      <div class="report-section">
        <h3 class="section-title">Student Nutritional Records</h3>
        <p class="section-desc">
          Detailed student nutritional records included in the nurse-generated report.
        </p>

        <div v-if="loading" class="alert alert-info mt-3">
          Loading report records...
        </div>

        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Grade/Section</th>
                <th>Age</th>
                <th>Sex</th>
                <th>Weight</th>
                <th>Height</th>
                <th>BMI</th>
                <th>BMI Category</th>
                <th>Height-for-Age</th>
                <th>Risk Level</th>
                <th>Recommendation</th>
              </tr>
            </thead>

            <tbody>
              <tr v-if="filteredRecords.length === 0 && !loading">
                <td colspan="12" class="text-center text-muted p-4">
                  No report records found.
                </td>
              </tr>

              <tr v-for="(record, index) in filteredRecords" :key="record.record_id || index">
                <td>{{ index + 1 }}</td>
                <td class="fw-bold">{{ record.learner_name || "-" }}</td>
                <td>{{ record.grade_level || "-" }} - {{ record.section || "-" }}</td>
                <td>{{ record.age || "-" }}</td>
                <td>{{ record.sex || "-" }}</td>
                <td>{{ record.weight_kg || "-" }}</td>
                <td>{{ record.height_m || "-" }}</td>
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

      <div class="report-section">
        <h3 class="section-title">Nurse Recommended Actions</h3>
        <p class="section-desc">
          Recommended school and classroom support actions based on nutritional risk level.
        </p>

        <div class="recommendation-grid">
          <div class="recommendation-card">
            <div class="recommendation-title">High Risk Students</div>
            <p class="recommendation-text">
              Coordinate with the clinic nurse for follow-up schedules and assist in notifying advisers or guardians
              when monitoring support is needed.
            </p>
          </div>

          <div class="recommendation-card">
            <div class="recommendation-title">Moderate Risk Students</div>
            <p class="recommendation-text">
              Encourage consistent attendance, observe student participation, and remind students to follow nutrition guidance.
            </p>
          </div>

          <div class="recommendation-card">
            <div class="recommendation-title">Low Risk Students</div>
            <p class="recommendation-text">
              Continue routine classroom observation and promote healthy food choices, hydration, and hygiene practices.
            </p>
          </div>
        </div>
      </div>

      <div class="signature-grid">
        <div class="signature-box">
          <div class="signature-line">Clinic Nurse</div>
          <div class="signature-role">Generated by / School Clinic</div>
        </div>

        <div class="signature-box">
          <div class="signature-line">{{ teacherEmail }}</div>
          <div class="signature-role">Viewed by / Teacher</div>
        </div>
      </div>

    </section>

  </main>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<script>
const { createApp } = Vue;

createApp({
  data() {
    return {
      teacherEmail: "",
      currentDate: new Date().toLocaleDateString(),

      records: [],
      loading: false,

      searchTerm: "",
      riskFilter: "",
      bmiFilter: "",
      gradeFilter: "",

      riskChart: null,
      bmiChart: null
    };
  },

  computed: {
    filteredRecords() {
      return this.records.filter(record => {
        const search = this.searchTerm.toLowerCase();

        const matchesSearch =
          !search ||
          String(record.learner_name || "").toLowerCase().includes(search) ||
          String(record.grade_level || "").toLowerCase().includes(search) ||
          String(record.section || "").toLowerCase().includes(search);

        const matchesRisk =
          !this.riskFilter || this.getRiskLevel(record) === this.riskFilter;

        const matchesBmi =
          !this.bmiFilter || String(record.bmi_category || "") === this.bmiFilter;

        const matchesGrade =
          !this.gradeFilter || String(record.grade_level || "") === this.gradeFilter;

        return matchesSearch && matchesRisk && matchesBmi && matchesGrade;
      });
    },

    highRiskCount() {
      return this.filteredRecords.filter(record => this.getRiskLevel(record) === "High").length;
    },

    moderateRiskCount() {
      return this.filteredRecords.filter(record => this.getRiskLevel(record) === "Moderate").length;
    },

    lowRiskCount() {
      return this.filteredRecords.filter(record => this.getRiskLevel(record) === "Low").length;
    },

    forReviewCount() {
      return this.filteredRecords.filter(record => this.getRiskLevel(record) === "For Review").length;
    },

    normalBmiCount() {
      return this.filteredRecords.filter(record => String(record.bmi_category || "") === "Normal").length;
    },

    underweightCount() {
      return this.filteredRecords.filter(record => String(record.bmi_category || "") === "Underweight").length;
    },

    severelyUnderweightCount() {
      return this.filteredRecords.filter(record => String(record.bmi_category || "") === "Severely Underweight").length;
    },

    overweightCount() {
      return this.filteredRecords.filter(record => String(record.bmi_category || "") === "Overweight").length;
    },

    obeseCount() {
      return this.filteredRecords.filter(record => String(record.bmi_category || "") === "Obese").length;
    }
  },

  watch: {
    filteredRecords: {
      handler() {
        this.$nextTick(() => {
          this.renderCharts();
        });
      },
      deep: true
    }
  },

  mounted() {
    const activeRole = localStorage.getItem("active_role");
    const token = localStorage.getItem("teacher_id_token");
    const email = localStorage.getItem("teacher_email");

    if (activeRole !== "Teacher" || !token || !email) {
      window.location.href = "login.php";
      return;
    }

    this.teacherEmail = email;

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
        /*
          If you later create a teacher-only report API, change this to:
          api/get_teacher_reports.php?teacher_email=...
        */

        const response = await fetch("api/get_student_records.php?cache_buster=" + Date.now());
        const text = await response.text();

        let result;

        try {
          result = JSON.parse(text);
        } catch (jsonError) {
          alert("Report API did not return JSON. Check api/get_student_records.php.");
          this.loading = false;
          return;
        }

        if (result.success) {
          this.records = result.records || [];

          this.$nextTick(() => {
            this.renderCharts();
          });
        } else {
          alert(result.message || "Failed to load report records.");
        }

      } catch (error) {
        alert("Error loading report records: " + error.message);
      }

      this.loading = false;
    },

    resetFilters() {
      this.searchTerm = "";
      this.riskFilter = "";
      this.bmiFilter = "";
      this.gradeFilter = "";
    },

    printReport() {
      this.$nextTick(() => {
        window.print();
      });
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