<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | School Clinic & Water Supply</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    /* (keep your existing styles – unchanged) */
    :root { --clinic-primary: #0f766e; --clinic-secondary: #14b8a6; --clinic-border: #d9eef0; --clinic-text: #16323f; --clinic-muted: #6b7d87; --clinic-shadow: 0 16px 38px rgba(15,118,110,0.08); --clinic-radius: 22px; }
    * { box-sizing: border-box; }
    body { min-height: 100vh; margin: 0; background: #f5fafb; font-family: 'Plus Jakarta Sans', Arial, sans-serif; color: var(--clinic-text); }
    .container-custom { max-width: 1000px; margin: 0 auto; padding: 24px 20px; }
    .header-box { background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary)); color: white; padding: 24px 28px; border-radius: 24px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; box-shadow: 0 16px 38px rgba(15,118,110,0.18); }
    .header-box h1 { font-size: 1.6rem; font-weight: 800; margin: 0; }
    .btn-back { background: white; color: var(--clinic-primary); border: none; border-radius: 14px; padding: 10px 20px; font-weight: 700; text-decoration: none; }
    .btn-save, .btn-refresh { background: white; color: var(--clinic-primary); border: none; border-radius: 14px; padding: 12px 24px; font-weight: 700; cursor: pointer; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
    .btn-save:disabled, .btn-refresh:disabled { opacity: 0.6; cursor: not-allowed; }
    .section-card { background: white; border: 1px solid var(--clinic-border); border-radius: var(--clinic-radius); padding: 24px; margin-bottom: 20px; box-shadow: var(--clinic-shadow); }
    .section-title { font-size: 1.3rem; font-weight: 800; color: var(--clinic-primary); margin-bottom: 16px; }
    .subsection-title { font-size: 1.05rem; font-weight: 700; color: var(--clinic-text); margin: 16px 0 10px; padding-bottom: 8px; border-bottom: 2px solid #ecfeff; }
    .radio-group { display: flex; flex-wrap: wrap; gap: 16px; margin: 8px 0 16px; }
    .form-check { display: flex; align-items: center; gap: 6px; }
    .form-check-input:checked { background-color: var(--clinic-primary); border-color: var(--clinic-primary); }
    .conditional-section { background: #fbfefe; border: 1px solid var(--clinic-border); border-radius: 14px; padding: 16px; margin-top: 12px; }
    .form-control { border-radius: 12px; border: 1px solid var(--clinic-border); padding: 10px 14px; max-width: 250px; }
    .checkbox-group { display: flex; flex-wrap: wrap; gap: 16px; margin: 8px 0 16px; }
    .table-responsive { border-radius: 16px; border: 1px solid var(--clinic-border); background: white; margin-bottom: 12px; }
    .table { margin-bottom: 0; font-size: 0.85rem; }
    .table th { background: #f1fbfb; color: #24404d; font-weight: 800; font-size: 0.8rem; text-align: center; }
    .table td { vertical-align: middle; text-align: center; padding: 8px; }
    .alert { border-radius: 14px; border: none; margin-bottom: 16px; }
    .modal-content { border-radius: var(--clinic-radius); }
    @media (max-width: 768px) { .container-custom { padding: 12px; } }
  </style>
</head>
<body>
<div id="app" class="container-custom">
  <div class="header-box no-print">
    <div>
      <h1>🏥 BOX 2 & 3 – School Clinic & Water Supply</h1>
      <p style="margin:4px 0 0; opacity:0.9;">Clinic infrastructure, equipment, and water availability</p>
    </div>
    <div class="d-flex gap-2">
      <select v-model="selectedSchoolYear" class="form-select" style="max-width:180px;border-radius:12px;font-weight:700;">
        <option v-for="y in schoolYearOptions" :key="y" :value="y">{{ y }}</option>
      </select>

      <button class="btn-refresh" @click="openLoadModal" :disabled="saving">📂 Load from Saved</button>
      <button class="btn-refresh" @click="loadAggregatedData" :disabled="loading">🔄 Load from Records</button>
      <button class="btn-save" @click="saveData" :disabled="saving">{{ saving ? 'Saving...' : '💾 Save' }}</button>
      <button class="btn-save" @click="printForm" style="background:#f0fdfa; color:#0f766e;">🖨️ Print</button>
      <a href="reports.php" class="btn-back">← Back</a>
    </div>
  </div>
  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">{{ message }}</div>

  <!-- BOX 2 -->
  <div class="section-card">
    <h2 class="section-title">BOX 2 – School Clinic</h2>
    <div class="mb-3">
      <label class="fw-bold">1. Does the school have a designated school clinic?</label>
      <div class="radio-group">
        <label class="form-check"><input type="radio" class="form-check-input" value="Yes" v-model="formData.hasSchoolClinic"> Yes</label>
        <label class="form-check"><input type="radio" class="form-check-input" value="No" v-model="formData.hasSchoolClinic"> No</label>
      </div>
    </div>
    <div class="mb-3">
      <label class="fw-bold">2. Was the school visited by SDO Health Personnel?</label>
      <div class="radio-group">
        <label class="form-check"><input type="radio" class="form-check-input" value="Yes" v-model="formData.visitedBySDO"> Yes</label>
        <label class="form-check"><input type="radio" class="form-check-input" value="No" v-model="formData.visitedBySDO"> No</label>
      </div>
      <div v-if="formData.visitedBySDO === 'Yes'" class="conditional-section">
        <label class="fw-bold">Number of visits:</label>
        <input type="number" min="0" class="form-control" v-model.number="formData.sdoVisits">
      </div>
    </div>
    <h4 class="subsection-title">3. Clinic Infrastructure / Equipment / Materials</h4>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead><tr><th>Item</th><th>Status</th></tr></thead>
        <tbody>
          <tr v-for="item in clinicItems" :key="item">
            <td class="text-start fw-bold">{{ item }}</td>
            <td>
              <div class="radio-group justify-content-center">
                <label class="form-check"><input type="radio" :name="'clinic-'+item" value="Functional" v-model="formData.clinicEquipment[item]"> Functional</label>
                <label class="form-check"><input type="radio" :name="'clinic-'+item" value="Non-functional" v-model="formData.clinicEquipment[item]"> Non-functional</label>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- BOX 3 -->
  <div class="section-card">
    <h2 class="section-title">BOX 3 – Availability of Water Supply</h2>
    <div class="mb-3">
      <label class="fw-bold">1. Water supply sources</label>
      <div class="checkbox-group">
        <label class="form-check" v-for="src in waterSources" :key="src">
          <input type="checkbox" class="form-check-input" :value="src" v-model="formData.waterSources"> {{ src }}
        </label>
      </div>
    </div>
    <div class="mb-3">
      <label class="fw-bold">2. Is the water source used for drinking?</label>
      <div class="radio-group">
        <label class="form-check"><input type="radio" class="form-check-input" value="Yes" v-model="formData.waterForDrinking"> Yes</label>
        <label class="form-check"><input type="radio" class="form-check-input" value="No" v-model="formData.waterForDrinking"> No</label>
      </div>
    </div>
  </div>

  <!-- LOAD MODAL -->
  <div class="modal fade" id="loadModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title fw-bold">📂 Load Saved Report</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div v-if="savedReportsLoading" class="text-center py-4"><div class="spinner-border text-primary"></div> Loading...</div>
          <div v-else-if="savedReports.length === 0" class="alert alert-info">No saved reports found for this report type.</div>
          <div v-else class="table-responsive">
            <table class="table table-bordered">
              <thead><tr><th>School Year</th><th>Saved By</th><th>Saved At</th><th></th></tr></thead>
              <tbody>
                <tr v-for="(rep, idx) in savedReports" :key="idx">
                  <td>{{ rep.school_year }}</td><td>{{ rep.saved_by }}</td><td>{{ rep.saved_at }}</td>
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
      selectedSchoolYear: "2021-2022",
      schoolYearOptions: ["2021-2022"],
      clinicItems: ['Bathroom','Hospital/Clinic Bed','Dental Chair','First Aid Kit','Height Tool','Weighing Scale','Autoclave/Sterilizer','BP Apparatus','Nebulizer'],
      waterSources: ['Piped water','Water Well','Rainwater Catchment','Natural Source'],
      formData: {
        hasSchoolClinic: '', visitedBySDO: '', sdoVisits: 0,
        clinicEquipment: {},
        waterSources: [], waterForDrinking: ''
      },
      saving: false, loading: false, message: '', messageType: 'success',
      savedReports: [], savedReportsLoading: false, loadModal: null
    };
  },
  mounted() {
    this.loadSchoolYearOptions();
    this.clinicItems.forEach(item => { if (!this.formData.clinicEquipment[item]) this.formData.clinicEquipment[item] = ''; });
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

    async openLoadModal() {
      this.savedReportsLoading = true;
      try {
        const res = await fetch('api/get_report_list.php?report_key=box2_3&school_year=' + encodeURIComponent(this.selectedSchoolYear) + '&cache_buster=' + Date.now());
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        this.savedReports = data.success ? data.reports : [];
      } catch(e) { this.showMessage('danger', 'Error loading saved reports: ' + e.message); }
      this.savedReportsLoading = false;
      this.loadModal.show();
    },
    loadSelectedReport(reportData) {
      Object.assign(this.formData, reportData);
      if (!this.formData.clinicEquipment) this.formData.clinicEquipment = {};
      this.clinicItems.forEach(item => { if (!this.formData.clinicEquipment[item]) this.formData.clinicEquipment[item] = ''; });
      if (!Array.isArray(this.formData.waterSources)) this.formData.waterSources = [];
      this.loadModal.hide();
      this.showMessage('success', 'Report loaded successfully.');
    },
    async loadAggregatedData() {
      this.loading = true;
      try {
        // If you have an endpoint that returns aggregated data for Box2/3, call it here.
        // For now, show a placeholder message.
        this.showMessage('info', 'No aggregated data endpoint yet – please save manually.');
      } catch(e) { this.showMessage('danger', e.message); }
      this.loading = false;
    },
    async saveData() {
      this.saving = true;
      try {
        const res = await fetch('api/save_report.php', {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            report_key: 'box2_3', school_year: this.selectedSchoolYear,
            saved_by: localStorage.getItem('local_full_name') || 'Clinic Nurse',
            report_data: this.formData
          })
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const result = await res.json();
        this.showMessage(result.success ? 'success' : 'danger', result.message || (result.success ? 'Saved.' : 'Save failed.'));
      } catch(e) { this.showMessage('danger', 'Error: ' + e.message); }
      this.saving = false;
    },
    showMessage(type, text) { this.messageType = type; this.message = text; setTimeout(() => { this.message = ''; }, 5000); },
    printForm() { window.print(); }
  }
}).mount("#app");
</script>
</body>
</html>