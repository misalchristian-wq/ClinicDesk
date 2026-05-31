<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | Waste Management & Menstrual Hygiene</title>
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
    .container-custom { max-width: 900px; margin: 0 auto; padding: 24px 20px; }
    .header-box { background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary)); color: white; padding: 24px 28px; border-radius: 24px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; box-shadow: 0 16px 38px rgba(15, 118, 110, 0.18); }
    .header-box h1 { font-size: 1.6rem; font-weight: 800; margin: 0; }
    .btn-back, .btn-refresh, .btn-save {
      background: white; color: var(--clinic-primary); border: none; border-radius: 14px;
      padding: 12px 20px; font-weight: 700; text-decoration: none; display: inline-block;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1); cursor: pointer;
    }
    .btn-back:hover, .btn-refresh:hover, .btn-save:hover { background: #ecfeff; color: var(--clinic-primary); }
    .btn-save:disabled, .btn-refresh:disabled { opacity: 0.6; cursor: not-allowed; }
    .section-card { background: white; border: 1px solid var(--clinic-border); border-radius: var(--clinic-radius); padding: 24px; margin-bottom: 20px; box-shadow: var(--clinic-shadow); }
    .section-title { font-size: 1.3rem; font-weight: 800; color: var(--clinic-primary); margin-bottom: 16px; }
    .checkbox-group { display: flex; flex-wrap: wrap; gap: 16px; margin: 8px 0 16px; }
    .form-check { display: flex; align-items: center; gap: 6px; }
    .form-check-input:checked { background-color: var(--clinic-primary); border-color: var(--clinic-primary); }
    .conditional-section { background: #fbfefe; border: 1px solid var(--clinic-border); border-radius: 14px; padding: 16px; margin-top: 12px; }
    .form-control { border-radius: 12px; border: 1px solid var(--clinic-border); padding: 10px 14px; }
    .form-control:focus { border-color: var(--clinic-secondary); box-shadow: 0 0 0 0.2rem rgba(20,184,166,0.12); }
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
      <h1>♻️ BOX 10 & 11 – Solid Waste Management & Menstrual Hygiene</h1>
      <p style="margin:4px 0 0; opacity:0.9;">Waste management implementation and menstrual hygiene availability – Editable</p>
    </div>
    <div class="d-flex gap-2 align-items-center flex-wrap">
      <select v-model="selectedSchoolYear" class="form-select school-year-select">
        <option value="2021-2022">2021-2022</option>
        <option value="2022-2023">2022-2023</option>
        <option value="2023-2024">2023-2024</option>
        <option value="2025-2026">2025-2026</option>
        <option value="2027-2028">2027-2028</option>
      </select>
      <button class="btn-refresh" @click="openLoadModal" :disabled="saving">📂 Load from Saved</button>
      <button class="btn-refresh" @click="loadAggregatedData" :disabled="loading">🔄 Load from Records</button>
      <button class="btn-save" @click="saveData" :disabled="saving">{{ saving ? 'Saving...' : '💾 Save' }}</button>
      <button class="btn-save" @click="printForm" style="background:#f0fdfa; color:#0f766e;">🖨️ Print</button>
      <a href="reports.php" class="btn-back">← Back</a>
    </div>
  </div>

  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">{{ message }}</div>

  <!-- BOX 10 -->
  <div class="section-card">
    <h2 class="section-title">BOX 10 – Solid Waste Management</h2>
    <div class="mb-3">
      <label class="fw-bold">1. Solid waste management implementation</label>
      <div class="checkbox-group">
        <label class="form-check" v-for="swm in swmImplementation" :key="swm">
          <input type="checkbox" class="form-check-input" :value="swm" v-model="formData.swmImplementation"> {{ swm }}
        </label>
      </div>
    </div>
    <div class="mb-3">
      <label class="fw-bold">2. Stakeholders engaged</label>
      <div class="checkbox-group">
        <label class="form-check" v-for="stakeholder in stakeholders" :key="stakeholder">
          <input type="checkbox" class="form-check-input" :value="stakeholder" v-model="formData.stakeholders"> {{ stakeholder }}
        </label>
      </div>
    </div>
  </div>

  <!-- BOX 11 -->
  <div class="section-card">
    <h2 class="section-title">BOX 11 – Menstrual Hygiene</h2>
    <div class="mb-3">
      <label class="fw-bold">If sanitary pads are available, where may learners get them?</label>
      <div class="checkbox-group">
        <label class="form-check" v-for="loc in sanitaryPadLocations" :key="loc">
          <input type="checkbox" class="form-check-input" :value="loc" v-model="formData.sanitaryPadLocations"> {{ loc }}
        </label>
      </div>
      <div v-if="formData.sanitaryPadLocations.includes('Others')" class="conditional-section">
        <input type="text" class="form-control" placeholder="Specify other location" v-model="formData.sanitaryPadOther">
      </div>
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
      swmImplementation: ['Composting','Trash collection point','Poster/Slogan contest','Posting signage','Recycling projects','Barangay SWM representative','Use of paper plates/cups','Use of recycled materials as teaching tools','Use of reusable food containers','Waste segregation'],
      stakeholders: ['Barangay','Community leaders','Local business partners','Municipal/City government','Parents'],
      sanitaryPadLocations: ['School Canteen','School Clinic','Guidance Office','Others'],
      selectedSchoolYear: "2021-2022",
      formData: {
        swmImplementation: [], stakeholders: [],
        sanitaryPadLocations: [], sanitaryPadOther: ''
      },
      saving: false,
      loading: false,
      message: '',
      messageType: 'success',
      savedReports: [],
      savedReportsLoading: false,
      loadModal: null
    };
  },

  mounted() {
    this.loadModal = new bootstrap.Modal(document.getElementById('loadModal'));
  },

  methods: {
    async openLoadModal() {
      this.savedReportsLoading = true;
      try {
        const url = `api/get_report_list.php?report_key=box10_11&school_year=${this.selectedSchoolYear}&cache_buster=${Date.now()}`;
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
      if (!this.formData.swmImplementation) this.formData.swmImplementation = [];
      if (!this.formData.stakeholders) this.formData.stakeholders = [];
      if (!this.formData.sanitaryPadLocations) this.formData.sanitaryPadLocations = [];
      this.loadModal.hide();
      this.showMessage('success', `Loaded saved report for ${this.selectedSchoolYear}. You can now edit and save.`);
    },

    async loadAggregatedData() {
      this.loading = true;
      this.showMessage('info', 'No aggregated data available for Box 10/11. Use "Load from Saved" to view previously saved reports.');
      this.loading = false;
    },

    async saveData() {
      this.saving = true;
      try {
        const res = await fetch('api/save_report.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            report_key: 'box10_11',
            school_year: this.selectedSchoolYear,
            saved_by: localStorage.getItem('local_full_name') || 'Clinic Nurse',
            report_data: this.formData
          })
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const result = await res.json();
        this.showMessage(result.success ? 'success' : 'danger', result.message || (result.success ? 'Saved.' : 'Save failed.'));
      } catch(e) {
        this.showMessage('danger', 'Error: ' + e.message);
      }
      this.saving = false;
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