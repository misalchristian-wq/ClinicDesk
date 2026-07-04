<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | Student Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
      max-width: 1500px;
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

    .btn-back {
      background: white;
      color: var(--clinic-primary);
      border: none;
      border-radius: 15px;
      padding: 11px 18px;
      font-weight: 800;
      box-shadow: 0 12px 28px rgba(0,0,0,0.12);
      text-decoration: none;
    }

    .btn-back:hover {
      background: #ecfeff;
      color: var(--clinic-primary);
    }

    .summary-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 18px;
      margin-bottom: 24px;
    }

    .summary-card {
      background: var(--clinic-card);
      border: 1px solid var(--clinic-border);
      border-radius: var(--clinic-radius);
      padding: 20px;
      box-shadow: var(--clinic-shadow);
      position: relative;
      overflow: hidden;
    }

    .summary-card::after {
      content: "";
      position: absolute;
      top: -35px;
      right: -35px;
      width: 95px;
      height: 95px;
      background: rgba(20, 184, 166, 0.10);
      border-radius: 50%;
    }

    .summary-label {
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      color: var(--clinic-muted);
      font-weight: 800;
      margin-bottom: 8px;
      position: relative;
      z-index: 2;
    }

    .summary-value {
      font-size: 30px;
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

    .card {
      background: var(--clinic-card);
      border: 1px solid var(--clinic-border);
      border-radius: var(--clinic-radius);
      box-shadow: var(--clinic-shadow);
      color: var(--clinic-text);
    }

    .card h4 {
      color: var(--clinic-primary);
      font-weight: 900;
    }

    .filter-card {
      margin-bottom: 24px;
    }

    .form-label {
      color: #24404d;
      font-weight: 800;
      font-size: 14px;
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

    .btn-green {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white;
      font-weight: 900;
      border: none;
      border-radius: 14px;
      padding: 11px 14px;
      box-shadow: 0 12px 24px rgba(15, 118, 110, 0.18);
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
      padding: 10px 14px;
    }

    .btn-outline-clinic:hover {
      background: var(--clinic-primary);
      color: white;
    }

    .chart-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 24px;
      margin-bottom: 24px;
    }

    .chart-box {
      height: 280px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .table-responsive {
      width: 100%;
      overflow-x: auto;
      border-radius: 16px;
      border: 1px solid var(--clinic-border);
      background: white;
    }

    .table {
      margin-bottom: 0;
      color: var(--clinic-text);
    }

    .table th {
      background: #e8f7f5;
      color: #24404d;
      font-weight: 900;
      white-space: nowrap;
      border-bottom: 1px solid var(--clinic-border);
      font-size: 14px;
      vertical-align: middle;
    }

    .table td {
      vertical-align: middle;
      color: #263f4a;
      background: white;
      border-color: #e5f0f2;
      font-size: 14px;
      white-space: nowrap;
    }

    .table tbody tr:hover td {
      background: #f7fcfd;
    }

    .badge {
      border-radius: 999px;
      padding: 8px 11px;
      font-size: 12px;
      font-weight: 800;
    }

    .student-cell {
      display: flex;
      align-items: center;
      gap: 10px;
      min-width: 250px;
    }

    .student-avatar {
      width: 42px;
      height: 42px;
      border-radius: 14px;
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 900;
      flex-shrink: 0;
    }

    .student-name {
      font-weight: 900;
      color: var(--clinic-text);
      margin-bottom: 2px;
    }

    .student-sub {
      color: var(--clinic-muted);
      font-size: 12px;
    }

    .health-note {
      background: #f8fcfd;
      border: 1px solid var(--clinic-border);
      border-radius: 16px;
      padding: 14px;
      margin-top: 18px;
    }

    .health-note-title {
      font-weight: 900;
      color: var(--clinic-primary);
      margin-bottom: 4px;
    }

    .health-note-text {
      color: var(--clinic-muted);
      font-size: 14px;
      line-height: 1.5;
      margin-bottom: 0;
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

    .alert-danger {
      background: #fee2e2;
      color: #991b1b;
      border: 1px solid #fecaca;
    }

    .alert-success {
      background: #dcfce7;
      color: #166534;
      border: 1px solid #bbf7d0;
    }

    .small-note {
      font-size: 0.9rem;
      color: var(--clinic-muted);
      line-height: 1.5;
    }

    .action-btn {
      min-width: 115px;
      font-size: 13px;
      padding: 8px 12px;
    }

    @media (max-width: 1100px) {
      .summary-grid {
        grid-template-columns: repeat(2, 1fr);
      }

      .chart-grid {
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

      .summary-grid {
        grid-template-columns: 1fr;
      }
    }
    .cd-modal-overlay {
      position: fixed; inset: 0; background: rgba(15, 50, 63, 0.45);
      display: flex; align-items: flex-start; justify-content: center;
      z-index: 1080; padding: 30px 16px; overflow-y: auto;
    }
    .cd-modal {
      background: #fff; border-radius: 20px; width: 100%; max-width: 760px;
      box-shadow: 0 24px 60px rgba(0,0,0,0.25); overflow: hidden; margin: auto;
    }
    .cd-modal-head {
      display: flex; align-items: center; justify-content: space-between;
      padding: 18px 24px; background: linear-gradient(135deg, var(--clinic-primary, #0f766e), var(--clinic-secondary, #14b8a6));
      color: #fff;
    }
    .cd-modal-close { background: transparent; border: none; color: #fff; font-size: 1.6rem; line-height: 1; cursor: pointer; }
    .cd-modal-body { padding: 22px 24px; max-height: 65vh; overflow-y: auto; }
    .cd-modal-foot { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; border-top: 1px solid #e6f1f2; }
  </style>
</head>

<body>
<div id="app" class="wrapper">

  <div class="header-box d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div class="header-content d-flex align-items-center">
      <div class="header-icon">🩺</div>

      <div>
        <h1 class="fw-bold mb-2">Student Health Monitoring</h1>
        <p class="mb-1">
          Monitor approved student nutritional records, BMI status, risk level, and follow-up needs.
        </p>
        <p class="mb-0">
          Clinic Nurse: <strong>{{ nurseName }}</strong>
        </p>
      </div>
    </div>

    <div class="header-actions">
      <a href="nurse-dashboard.php" class="btn btn-back">
        Back to Dashboard
      </a>
    </div>
  </div>

  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">
    {{ message }}
  </div>

  <div class="summary-grid">
    <div class="summary-card">
      <div class="summary-label">Total Students</div>
      <p class="summary-value">{{ filteredRecords.length }}</p>
      <p class="summary-helper">Currently displayed records</p>
    </div>

    <div class="summary-card">
      <div class="summary-label">High Risk</div>
      <p class="summary-value">{{ highRiskCount }}</p>
      <p class="summary-helper">Needs immediate attention</p>
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

  <div class="card p-4 filter-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
      <div>
        <h4 class="mb-1">Student Record Filters</h4>
        <p class="small-note mb-0">
          Search and filter approved student records based on grade level, risk level, and BMI category.
        </p>
      </div>

      <button class="btn btn-outline-clinic" @click="resetFilters">
        Reset Filters
      </button>
    </div>

    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Search Student</label>
        <input v-model="searchTerm" type="text" class="form-control" placeholder="Search by name, grade, or section">
      </div>

      <div class="col-md-3">
        <label class="form-label">Risk Level</label>
        <select v-model="riskFilter" class="form-select">
          <option value="">All Risk Levels</option>
          <option value="High">High</option>
          <option value="Moderate">Moderate</option>
          <option value="Low">Low</option>
          <option value="For Review">For Review</option>
        </select>
      </div>

      <div class="col-md-3">
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

      <div class="col-md-2">
        <label class="form-label">Grade</label>
        <select v-model="gradeFilter" class="form-select">
          <option value="">All</option>
          <option value="Grade 7">Grade 7</option>
          <option value="Grade 8">Grade 8</option>
          <option value="Grade 9">Grade 9</option>
          <option value="Grade 10">Grade 10</option>
          <option value="Grade 11">Grade 11</option>
          <option value="Grade 12">Grade 12</option>
        </select>
      </div>
    </div>
  </div>

  <div class="chart-grid">
    <div class="card p-4">
      <h4 class="fw-bold mb-3">Nutritional Risk Overview</h4>
      <div class="chart-box">
        <canvas id="riskChart"></canvas>
      </div>
    </div>

    <div class="card p-4">
      <h4 class="fw-bold mb-3">BMI Category Overview</h4>
      <div class="chart-box">
        <canvas id="bmiChart"></canvas>
      </div>
    </div>

    <div class="card p-4">
      <h4 class="fw-bold mb-3">Height-for-Age Overview</h4>
      <div class="chart-box">
        <canvas id="hfaChart"></canvas>
      </div>
    </div>
  </div>

  <div class="card p-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
      <div>
        <h4 class="mb-1">Student Health Records</h4>
        <p class="small-note mb-0">
          Use the action button on the right side to open the individual student profile.
        </p>
      </div>

      <div class="d-flex gap-2">
        <button class="btn btn-outline-clinic" @click="openAddModal">
          + Add Student
        </button>
        <button class="btn btn-green" @click="loadRecords">
          Refresh
        </button>
      </div>
    </div>

    <div v-if="loading" class="alert alert-info">
      Loading student health records...
    </div>

    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead>
          <tr>
            <th>Student</th>
            <th>Grade</th>
            <th>BMI</th>
            <th>BMI Category</th>
            <th>Height-for-Age</th>
            <th>Risk Level</th>

            <th style="text-align: center;">Action</th>
          </tr>
        </thead>

        <tbody>
          <tr v-if="filteredRecords.length === 0 && !loading">
            <td colspan="8" class="text-center text-muted p-4">
              No student health records found.
            </td>
          </tr>

          <tr v-for="record in filteredRecords" :key="record.record_id">
            <td>
              <div class="student-cell">
                <div class="student-avatar">
                  {{ getInitials(record.learner_name) }}
                </div>

                <div>
                  <div class="student-name">{{ record.learner_name || "-" }}</div>
                  <div class="student-sub">
                    {{ record.sex || "-" }} | {{ record.age || "-" }} years old
                  </div>
                </div>
              </div>
            </td>

            <td>{{ record.grade_level || "-" }} - {{ record.section || "-" }}</td>

            <td class="fw-bold">{{ record.bmi || "-" }}</td>

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

          

            <td class="text-center">
              <a :href="'student-profile.php?record_id=' + record.record_id" class="btn btn-outline-clinic btn-sm action-btn">
                View Profile
              </a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="health-note">
      <div class="health-note-title">Monitoring Note</div>
      <p class="health-note-text">
        Students marked as High Risk should be prioritized for clinic follow-up. Moderate Risk students should be monitored regularly, while Low Risk students may continue routine health monitoring.
      </p>
    </div>
  </div>

  <!-- ADD STUDENT MODAL (Vue-controlled) -->
  <div v-if="showAddModal" class="cd-modal-overlay" @click.self="closeAddModal">
    <div class="cd-modal">
      <div class="cd-modal-head">
        <h5 class="fw-bold mb-0">Add Student Manually</h5>
        <button class="cd-modal-close" @click="closeAddModal">&times;</button>
      </div>

      <div class="cd-modal-body">
        <div v-if="addError" class="alert alert-danger py-2">{{ addError }}</div>

        <div class="alert alert-info py-2">
          Active School Year: <strong>{{ activeSchoolYear || "not set" }}</strong>.
          BMI, BMI category, and height-for-age are computed automatically.
        </div>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">LRN <span class="text-danger">*</span></label>
            <input v-model.trim="addForm.lrn" class="form-control" placeholder="12-digit LRN">
          </div>
          <div class="col-md-6">
            <label class="form-label">Learner's Name <span class="text-danger">*</span></label>
            <input v-model.trim="addForm.learner_name" class="form-control" placeholder="Last, First, M.I.">
          </div>

          <div class="col-md-4">
            <label class="form-label">Sex <span class="text-danger">*</span></label>
            <select v-model="addForm.sex" class="form-select">
              <option value="">Select</option>
              <option>Male</option>
              <option>Female</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Birthdate</label>
            <input v-model="addForm.birthdate" type="date" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label">Age (years) <span class="text-danger">*</span></label>
            <input v-model.number="addForm.age" type="number" min="5" max="19" class="form-control">
          </div>

          <div class="col-md-6">
            <label class="form-label">Weight (kg) <span class="text-danger">*</span></label>
            <input v-model.number="addForm.weight_kg" type="number" step="0.01" min="0" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="form-label">Height (m) <span class="text-danger">*</span></label>
            <input v-model.number="addForm.height_m" type="number" step="0.001" min="0" class="form-control" placeholder="e.g. 1.52">
          </div>

          <div class="col-md-4">
            <label class="form-label">Grade Level</label>
            <input v-model.trim="addForm.grade_level" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label">Section</label>
            <input v-model.trim="addForm.section" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label">Track/Strand</label>
            <input v-model.trim="addForm.track_strand" class="form-control">
          </div>

          <div class="col-md-6">
            <label class="form-label">School Name</label>
            <input v-model.trim="addForm.school_name" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="form-label">School ID</label>
            <input v-model.trim="addForm.school_id" class="form-control">
          </div>

          <div class="col-md-4">
            <label class="form-label">District</label>
            <input v-model.trim="addForm.district" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label">Division</label>
            <input v-model.trim="addForm.division" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label">Region</label>
            <input v-model.trim="addForm.region" class="form-control">
          </div>

          <div class="col-12">
            <label class="form-label">Remarks</label>
            <input v-model.trim="addForm.remarks" class="form-control">
          </div>
        </div>
      </div>

      <div class="cd-modal-foot">
        <button class="btn btn-outline-clinic" @click="closeAddModal" :disabled="addSaving">Cancel</button>
        <button class="btn btn-green" @click="submitAddStudent" :disabled="addSaving">
          {{ addSaving ? "Saving..." : "Save Student" }}
        </button>
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
      nurseName: "",
      records: [],

      searchTerm: "",
      riskFilter: "",
      bmiFilter: "",
      gradeFilter: "",

      loading: false,
      message: "",
      messageType: "success",

      // Manual add-student modal
      showAddModal: false,
      addSaving: false,
      addError: "",
      activeSchoolYear: "",
      addForm: {
        lrn: "", learner_name: "", sex: "", birthdate: "", age: "",
        weight_kg: "", height_m: "", grade_level: "", section: "", track_strand: "",
        school_name: "", school_id: "", district: "", division: "", region: "", remarks: ""
      },

      riskChart: null,
      bmiChart: null,
      hfaChart: null
    };
  },

  computed: {
    filteredRecords() {
      return this.records.filter(record => {
        const search = this.searchTerm.toLowerCase();

        const learnerName = String(record.learner_name || "").toLowerCase();
        const gradeLevel = String(record.grade_level || "").toLowerCase();
        const section = String(record.section || "").toLowerCase();

        const matchesSearch =
          !search ||
          learnerName.includes(search) ||
          gradeLevel.includes(search) ||
          section.includes(search);

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
    },

    hfaCount() {
      // Counts by height-for-age category (handles field-name variants).
      const tally = { "Severely Stunted": 0, "Stunted": 0, "Normal": 0, "Tall": 0 };
      this.filteredRecords.forEach(r => {
        const h = String(this.getHeightForAge(r) || "").toLowerCase();
        if (h.includes("severely")) tally["Severely Stunted"]++;
        else if (h.includes("stunted")) tally["Stunted"]++;
        else if (h.includes("tall")) tally["Tall"]++;
        else if (h.includes("normal")) tally["Normal"]++;
      });
      return tally;
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
    const role = localStorage.getItem("active_role");
    const accountId = localStorage.getItem("local_account_id");

    if (role !== "Clinic Nurse" || !accountId) {
      window.location.href = "login.php";
      return;
    }

    this.nurseName = localStorage.getItem("local_full_name") || "Clinic Nurse";

    this.loadRecords();
    this.loadActiveSchoolYear();
  },

  methods: {
    showMessage(type, text) {
      this.messageType = type;
      this.message = text;

      setTimeout(() => {
        this.message = "";
      }, 5000);
    },

    async loadActiveSchoolYear() {
      try {
        const res = await fetch("api/get_school_years.php?t=" + Date.now());
        const data = await res.json();
        if (data.success && data.active) this.activeSchoolYear = data.active;
      } catch (e) {
        console.warn("Could not load active school year", e);
      }
    },

    resetAddForm() {
      this.addForm = {
        lrn: "", learner_name: "", sex: "", birthdate: "", age: "",
        weight_kg: "", height_m: "", grade_level: "", section: "", track_strand: "",
        school_name: "", school_id: "", district: "", division: "", region: "", remarks: ""
      };
      this.addError = "";
    },

    openAddModal() {
      this.resetAddForm();
      this.showAddModal = true;
    },

    closeAddModal() {
      if (this.addSaving) return;
      this.showAddModal = false;
    },

    async submitAddStudent() {
      this.addError = "";

      // Basic client-side required check (server re-validates everything).
      const f = this.addForm;
      const required = { LRN: f.lrn, "Learner's Name": f.learner_name, Sex: f.sex,
        Age: f.age, Weight: f.weight_kg, Height: f.height_m };
      const missing = Object.keys(required).filter(k => required[k] === "" || required[k] === null);
      if (missing.length) {
        this.addError = "Please fill: " + missing.join(", ") + ".";
        return;
      }
      if (!this.activeSchoolYear) {
        this.addError = "No active school year is set. Set one in School Year Settings first.";
        return;
      }

      this.addSaving = true;
      try {
        const payload = { ...f, school_year: this.activeSchoolYear };
        const res = await fetch("api/add_manual_student.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
          this.showAddModal = false;
          this.showMessage("success",
            `Student added: ${data.record.learner_name} (BMI ${data.record.bmi}, ${data.record.bmi_category}).`);
          this.loadRecords();
        } else {
          this.addError = data.message || "Could not add student.";
        }
      } catch (e) {
        this.addError = "Network error: " + e.message;
      }
      this.addSaving = false;
    },

    async loadRecords() {
      this.loading = true;
      this.message = "";

      try {
        const response = await fetch("api/get_student_records.php?cache_buster=" + Date.now());
        const text = await response.text();

        console.log("Student records raw response:", text);

        let result;

        try {
          result = JSON.parse(text);
        } catch (jsonError) {
          this.showMessage("error", "Student records API did not return JSON. Check api/get_student_records.php.");
          this.loading = false;
          return;
        }

        if (result.success) {
          this.records = result.records || [];

          this.$nextTick(() => {
            this.renderCharts();
          });
        } else {
          this.showMessage("error", result.message || "Failed to load student records.");
        }

      } catch (error) {
        this.showMessage("error", "Error loading student records: " + error.message);
      }

      this.loading = false;
    },

    resetFilters() {
      this.searchTerm = "";
      this.riskFilter = "";
      this.bmiFilter = "";
      this.gradeFilter = "";
    },

    getInitials(name) {
      if (!name) return "?";

      return name
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map(part => part.charAt(0).toUpperCase())
        .join("");
    },

    getHeightForAge(record) {
      return record.height_for_age_status ||
             record.height_for_age ||
             record.hfa_status ||
             "-";
    },

    getRiskLevel(record) {
      if (record.risk_level) {
        return record.risk_level;
      }

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
      if (record.recommendation) {
        return record.recommendation;
      }

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
      this.renderHfaChart();
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
            backgroundColor: [
              "#dc2626",
              "#f59e0b",
              "#16a34a",
              "#0ea5e9"
            ],
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
          labels: [
            "Normal",
            "Underweight",
            "Severely Underweight",
            "Overweight",
            "Obese"
          ],
          datasets: [{
            label: "Students",
            data: [
              this.normalBmiCount,
              this.underweightCount,
              this.severelyUnderweightCount,
              this.overweightCount,
              this.obeseCount
            ],
            backgroundColor: [
              "#16a34a",
              "#f59e0b",
              "#dc2626",
              "#f97316",
              "#991b1b"
            ],
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
    },

    renderHfaChart() {
      const ctx = document.getElementById("hfaChart");
      if (!ctx) return;
      if (this.hfaChart) this.hfaChart.destroy();

      const t = this.hfaCount;
      this.hfaChart = new Chart(ctx, {
        type: "bar",
        data: {
          labels: ["Severely Stunted", "Stunted", "Normal", "Tall"],
          datasets: [{
            label: "Students",
            data: [t["Severely Stunted"], t["Stunted"], t["Normal"], t["Tall"]],
            backgroundColor: ["#dc2626", "#f59e0b", "#16a34a", "#0ea5e9"],
            borderRadius: 10
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
      });
    }
  }
}).mount("#app");
</script>
</body>
</html>