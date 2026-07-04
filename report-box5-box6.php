<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | ARH & Tobacco Control Report</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    :root {
      --clinic-primary: #0f766e; --clinic-secondary: #14b8a6; --clinic-border: #d9eef0;
      --clinic-text: #16323f; --clinic-muted: #6b7d87; --clinic-shadow: 0 16px 38px rgba(15, 118, 110, 0.08); --clinic-radius: 22px;
    }
    * { box-sizing: border-box; }
    body { min-height: 100vh; margin: 0; background: #f5fafb; font-family: 'Plus Jakarta Sans', Arial, sans-serif; color: var(--clinic-text); }
    .container-custom { max-width: 1100px; margin: 0 auto; padding: 24px 20px; }
    .header-box { background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary)); color: white; padding: 24px 28px; border-radius: 24px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; box-shadow: 0 16px 38px rgba(15, 118, 110, 0.18); }
    .header-box h1 { font-size: 1.6rem; font-weight: 800; margin: 0; }
    .btn-back, .btn-refresh {
      background: white; color: var(--clinic-primary); border: none; border-radius: 14px;
      padding: 12px 20px; font-weight: 700; text-decoration: none; display: inline-block;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1); cursor: pointer;
    }
    .btn-back:hover, .btn-refresh:hover { background: #ecfeff; color: var(--clinic-primary); }
    .btn-refresh:disabled { opacity: 0.6; cursor: not-allowed; }
    .section-card { background: white; border: 1px solid var(--clinic-border); border-radius: var(--clinic-radius); padding: 24px; margin-bottom: 20px; box-shadow: var(--clinic-shadow); }
    .section-title { font-size: 1.3rem; font-weight: 800; color: var(--clinic-primary); margin-bottom: 16px; }
    .subsection-title { font-size: 1.05rem; font-weight: 700; color: var(--clinic-text); margin: 16px 0 10px; padding-bottom: 8px; border-bottom: 2px solid #ecfeff; }
    .radio-group, .checkbox-group { display: flex; flex-wrap: wrap; gap: 16px; margin: 8px 0 16px; }
    .form-check { display: flex; align-items: center; gap: 6px; }
    .form-check-input:checked { background-color: var(--clinic-primary); border-color: var(--clinic-primary); }
    .form-control { border-radius: 12px; border: 1px solid var(--clinic-border); padding: 10px 14px; max-width: 250px; }
    .table-responsive { border-radius: 16px; border: 1px solid var(--clinic-border); background: white; margin-bottom: 12px; }
    .table { margin-bottom: 0; font-size: 0.85rem; }
    .table th { background: #f1fbfb; color: #24404d; font-weight: 800; font-size: 0.8rem; text-align: center; }
    .table td { vertical-align: middle; text-align: center; padding: 8px; }
    .total-cell { font-weight: 800; color: var(--clinic-primary); background: #f0fdfa; }
    .alert { border-radius: 14px; border: none; margin-bottom: 16px; }
    .modal-content { border-radius: var(--clinic-radius); }
    .school-year-select { max-width: 200px; }
    @media (max-width: 768px) { .container-custom { padding: 12px; } }
  </style>
</head>

<body>
<div id="app" class="container-custom">
  <div class="header-box no-print">
    <div>
      <h1>👥 BOX 5 & 6 – ARH & Tobacco Control</h1>
      <p style="margin:4px 0 0; opacity:0.9;">Adolescent Reproductive Health and Comprehensive Tobacco Control – Read‑Only</p>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
      <select v-model="selectedSchoolYear" class="form-select school-year-select" @change="openLoadModal">
        <option v-for="y in schoolYearOptions" :key="y" :value="y">{{ y }}</option>
      </select>
      <button class="btn-refresh" @click="openLoadModal" :disabled="loading">📂 Load from Saved</button>
      <button class="btn-refresh" @click="loadBox5Box6Data" :disabled="loading">🔄 Load from Records</button>
      <button class="btn-refresh" @click="printForm" style="background:#f0fdfa; color:#0f766e;">🖨️ Print</button>
      <a href="reports.php" class="btn-back">← Back</a>
    </div>
  </div>

  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">
    {{ message }}
  </div>

  <div class="section-card">
    <h2 class="section-title">BOX 5 – Adolescent Reproductive Health</h2>

    <h4 class="subsection-title">1. Number of Pregnant Learners</h4>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead>
          <tr><th>Status</th><th v-for="g in grades" :key="'head-'+g">G{{ g }}</th></tr>
        </thead>
        <tbody>
          <tr v-for="status in pregnancyStatuses" :key="status">
            <td class="text-start fw-bold">{{ status }}</td>
            <td v-for="g in grades" :key="'preg-'+status+g">
              {{ formData.pregnantLearners[status]['g' + g] || 0 }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mb-3">
      <label class="fw-bold">2. Functional learner support center?</label>
      <div class="radio-group">
        <label class="form-check">
          <input type="radio" class="form-check-input" value="Yes" v-model="formData.hasSupportCenter" disabled> Yes
        </label>
        <label class="form-check">
          <input type="radio" class="form-check-input" value="No" v-model="formData.hasSupportCenter" disabled> No
        </label>
      </div>
    </div>

    <div class="mb-3">
      <label class="fw-bold">3. Number of learners trained as peer educators for ASRH</label>
      <div class="form-control bg-light">{{ formData.peerEducators || 0 }}</div>
    </div>
  </div>

  <div class="section-card">
    <h2 class="section-title">BOX 6 – Comprehensive Tobacco Control</h2>

    <div class="mb-3">
      <label class="fw-bold">1. IEC Materials displayed</label>
      <div class="checkbox-group">
        <label class="form-check">
          <input type="checkbox" class="form-check-input" value="No Smoking Signages" v-model="formData.iecMaterials" disabled>
          No Smoking Signages
        </label>
        <label class="form-check">
          <input type="checkbox" class="form-check-input" value="Poster prohibiting cigarette sales" v-model="formData.iecMaterials" disabled>
          Poster prohibiting cigarette sales
        </label>
      </div>
    </div>

    <div class="mb-3">
      <label class="fw-bold">2. Stores within 100 meters selling:</label>
      <div class="checkbox-group">
        <label class="form-check">
          <input type="checkbox" class="form-check-input" value="Tobacco products" v-model="formData.storesSelling" disabled>
          Tobacco products
        </label>
        <label class="form-check">
          <input type="checkbox" class="form-check-input" value="Vape/e-cigarettes" v-model="formData.storesSelling" disabled>
          Vape/e-cigarettes
        </label>
      </div>
    </div>

    <h4 class="subsection-title">3. Learners recorded bringing tobacco/vape products</h4>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead><tr><th>Category</th><th>JHS</th><th>SHS</th></tr></thead>
        <tbody>
          <tr><td class="text-start fw-bold">Brought products</td>
            <td>{{ formData.tobaccoViolations.jhs.brought || 0 }}</td>
            <td>{{ formData.tobaccoViolations.shs.brought || 0 }}</td>
          </tr>
          <tr><td class="text-start fw-bold">Referred to care</td>
            <td>{{ formData.tobaccoViolations.jhs.referred || 0 }}</td>
            <td>{{ formData.tobaccoViolations.shs.referred || 0 }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- LOAD MODAL (Saved reports) -->
  <div class="modal fade" id="loadModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title fw-bold">📂 Load Saved Report – {{ selectedSchoolYear }}</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div v-if="savedReportsLoading" class="text-center py-4"><div class="spinner-border text-primary"></div> Loading...</div>
          <div v-else-if="savedReports.length === 0" class="alert alert-info">No saved reports for {{ selectedSchoolYear }}.</div>
          <div v-else class="table-responsive">
            <table class="table table-bordered">
              <thead><tr><th>Saved By</th><th>Saved At</th><th></th></tr></thead>
              <tbody>
                <tr v-for="(rep, idx) in savedReports" :key="idx">
                  <td>{{ rep.saved_by }}</td>
                  <td>{{ rep.saved_at }}</td>
                  <td><button class="btn btn-sm btn-success" @click="loadSelectedReport(rep.report_data)">Load</button></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
      </div>
    </div>
  </div>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const { createApp } = Vue;

createApp({
  data() {
    return {
      grades: [7, 8, 9, 10, 11, 12],
      pregnancyStatuses: ["In School", "On Alternative Delivery Mode (ADM)"],
      selectedSchoolYear: "2021-2022",
      schoolYearOptions: ["2021-2022"],
      formData: {
        pregnantLearners: {
          "In School": { g7: 0, g8: 0, g9: 0, g10: 0, g11: 0, g12: 0 },
          "On Alternative Delivery Mode (ADM)": { g7: 0, g8: 0, g9: 0, g10: 0, g11: 0, g12: 0 }
        },
        hasSupportCenter: "",
        peerEducators: 0,
        iecMaterials: [],
        storesSelling: [],
        tobaccoViolations: {
          jhs: { brought: 0, referred: 0 },
          shs: { brought: 0, referred: 0 }
        }
      },
      loading: false,
      message: "",
      messageType: "success",
      savedReports: [],
      savedReportsLoading: false,
      loadModal: null
    };
  },

  mounted() {
    this.loadSchoolYearOptions();
    this.loadModal = new bootstrap.Modal(document.getElementById('loadModal'));
  },

  methods: {
    async loadSchoolYearOptions() {
      try {
        const res = await fetch('api/get_school_years.php?t=' + Date.now());
        const data = await res.json();
        if (data.success && Array.isArray(data.years) && data.years.length) {
          this.schoolYearOptions = data.years.map(y => y.year_label);
          // Default to the active year if present, else the first option.
          if (data.active && this.schoolYearOptions.includes(data.active)) {
            this.selectedSchoolYear = data.active;
          } else if (!this.schoolYearOptions.includes(this.selectedSchoolYear)) {
            this.selectedSchoolYear = this.schoolYearOptions[0];
          }
        }
      } catch (e) { console.warn('Could not load school years', e); }
    },

    resetFetchedData() {
      this.pregnancyStatuses.forEach(status => {
        this.grades.forEach(g => { this.formData.pregnantLearners[status]["g" + g] = 0; });
      });
      this.formData.peerEducators = 0;
      this.formData.tobaccoViolations.jhs.brought = 0;
      this.formData.tobaccoViolations.jhs.referred = 0;
      this.formData.tobaccoViolations.shs.brought = 0;
      this.formData.tobaccoViolations.shs.referred = 0;
    },

    async openLoadModal() {
      this.savedReportsLoading = true;
      try {
        const url = `api/get_report_list.php?report_key=box5_6&school_year=${this.selectedSchoolYear}&cache_buster=${Date.now()}`;
        const res = await fetch(url);
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        this.savedReports = data.success ? data.reports : [];
      } catch(e) {
        this.showMessage('danger', 'Error loading saved reports: ' + e.message);
      }
      this.savedReportsLoading = false;
      this.loadModal.show();
    },

    loadSelectedReport(reportData) {
      Object.assign(this.formData, reportData);
      // Ensure nested structures exist
      if (!this.formData.pregnantLearners) this.formData.pregnantLearners = { ...this.formData.pregnantLearners };
      if (!this.formData.tobaccoViolations) this.formData.tobaccoViolations = { jhs:{brought:0,referred:0}, shs:{brought:0,referred:0} };
      this.loadModal.hide();
      this.showMessage('success', `Loaded saved report for ${this.selectedSchoolYear}.`);
    },

    async loadBox5Box6Data() {
      this.loading = true;
      this.resetFetchedData();
      try {
        const url = `api/get_box5_box6_report.php?school_year=${encodeURIComponent(this.selectedSchoolYear)}&cache_buster=${Date.now()}`;
        const response = await fetch(url);
        const result = await response.json();
        if (!result.success) throw new Error(result.message || "Failed to load report data.");

        const arh = result.arh || [];
        const tobacco = result.tobacco || [];

        arh.forEach(row => {
          const grade = String(row.grade_level || "").trim();
          const deliveryMode = String(row.delivery_mode || "").trim().toLowerCase();
          const total = Number(row.total || 0);
          if (!this.grades.includes(Number(grade))) return;
          if (deliveryMode === "in school") {
            this.formData.pregnantLearners["In School"]["g" + grade] += total;
          } else if (deliveryMode === "adm" || deliveryMode.includes("alternative")) {
            this.formData.pregnantLearners["On Alternative Delivery Mode (ADM)"]["g" + grade] += total;
          }
        });

        this.formData.peerEducators = Number(result.peer_educators || 0);

        tobacco.forEach(row => {
          if (row.level_group === "jhs") {
            this.formData.tobaccoViolations.jhs.brought = Number(row.brought || 0);
            this.formData.tobaccoViolations.jhs.referred = Number(row.referred || 0);
          }
          if (row.level_group === "shs") {
            this.formData.tobaccoViolations.shs.brought = Number(row.brought || 0);
            this.formData.tobaccoViolations.shs.referred = Number(row.referred || 0);
          }
        });

        this.showMessage('success', `Loaded aggregated data for ${this.selectedSchoolYear}.`);
      } catch (error) {
        this.showMessage('danger', 'Error loading data: ' + error.message);
      }
      this.loading = false;
    },

    showMessage(type, text) {
      this.messageType = type;
      this.message = text;
      setTimeout(() => { this.message = ''; }, 5000);
    },

    printForm() {
      window.print();
    }
  }
}).mount("#app");
</script>
</body>
</html>