<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | Deworming & WIFA Report (Read‑Only)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    /* same as before – keep your styles */
    :root { --clinic-primary: #0f766e; --clinic-secondary: #14b8a6; --clinic-border: #d9eef0; --clinic-text: #16323f; --clinic-muted: #6b7d87; --clinic-shadow: 0 16px 38px rgba(15, 118, 110, 0.08); --clinic-radius: 22px; }
    * { box-sizing: border-box; }
    body { min-height: 100vh; margin: 0; background: #f5fafb; font-family: 'Plus Jakarta Sans', Arial, sans-serif; color: var(--clinic-text); }
    .container-custom { max-width: 1400px; margin: 0 auto; padding: 24px 20px; }
    .header-box { background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary)); color: white; padding: 24px 28px; border-radius: 24px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; box-shadow: 0 16px 38px rgba(15, 118, 110, 0.18); }
    .header-box h1 { font-size: 1.6rem; font-weight: 800; margin: 0; }
    .btn-back, .btn-refresh { background: white; color: var(--clinic-primary); border: none; border-radius: 14px; padding: 12px 20px; font-weight: 700; text-decoration: none; display: inline-block; box-shadow: 0 8px 20px rgba(0,0,0,0.1); cursor: pointer; }
    .btn-back:hover, .btn-refresh:hover { background: #ecfeff; color: var(--clinic-primary); }
    .btn-refresh:disabled { opacity: 0.6; cursor: not-allowed; }
    .section-card { background: white; border: 1px solid var(--clinic-border); border-radius: var(--clinic-radius); padding: 24px; margin-bottom: 20px; box-shadow: var(--clinic-shadow); }
    .section-title { font-size: 1.3rem; font-weight: 800; color: var(--clinic-primary); margin-bottom: 16px; }
    .table-responsive { border-radius: 16px; border: 1px solid var(--clinic-border); background: white; margin-bottom: 12px; }
    .table { margin-bottom: 0; font-size: 0.85rem; }
    .table th { background: #f1fbfb; color: #24404d; font-weight: 800; white-space: nowrap; font-size: 0.8rem; text-align: center; vertical-align: middle; }
    .table td { vertical-align: middle; text-align: center; padding: 8px; }
    .total-cell { font-weight: 800; color: var(--clinic-primary); background: #f0fdfa; }
    .alert { border-radius: 14px; border: none; margin-bottom: 16px; }
    .modal-content { border-radius: var(--clinic-radius); }
    @media (max-width: 768px) { .container-custom { padding: 12px; } .table { font-size: 0.7rem; } }
  </style>
</head>
<body>
<div id="app" class="container-custom">
  <div class="header-box no-print">
    <div>
      <h1>💊 TABLE 1 – Health and Nutrition (B)</h1>
      <p style="margin:4px 0 0; opacity:0.9;">Deworming & Weekly Iron Folic Acid (WIFA) – Read‑Only</p>
    </div>
    <div class="d-flex gap-2">
      <select v-model="selectedSchoolYear" class="form-select" style="max-width:180px;border-radius:12px;font-weight:700;">
        <option v-for="y in schoolYearOptions" :key="y" :value="y">{{ y }}</option>
      </select>

      <button class="btn-refresh" @click="openLoadModal" :disabled="loading">📂 Load from Saved</button>
      <button class="btn-refresh" @click="loadDewormingWifaData" :disabled="loading">🔄 Load from Records</button>
      <button class="btn-refresh" @click="printForm" style="background:#f0fdfa; color:#0f766e;">🖨️ Print</button>
      <a href="reports.php" class="btn-back">← Back</a>
    </div>
  </div>

  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">{{ message }}</div>

  <div class="section-card">
    <h2 class="section-title">C. Number of Learners Dewormed</h2>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead><tr><th>Grade</th><th>SBFP Male</th><th>SBFP Female</th><th>SBFP Total</th><th>Other Male</th><th>Other Female</th><th>Other Total</th></tr></thead>
        <tbody>
          <tr v-for="g in grades" :key="'deworm-'+g">
            <td class="text-start fw-bold">Grade {{ g }}</td>
            <td>{{ formData.dewormed[g]?.sbfpMale || 0 }}</td>
            <td>{{ formData.dewormed[g]?.sbfpFemale || 0 }}</td>
            <td class="total-cell">{{ dewormTotal(g, 'sbfp') }}</td>
            <td>{{ formData.dewormed[g]?.otherMale || 0 }}</td>
            <td>{{ formData.dewormed[g]?.otherFemale || 0 }}</td>
            <td class="total-cell">{{ dewormTotal(g, 'other') }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="section-card">
    <h2 class="section-title">D. Weekly Iron Folic Acid (WIFA) – Female Learners</h2>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead><tr><th>Grade</th><th>Jul–Sep 2024</th><th>Jan–Mar 2025</th></tr></thead>
        <tbody>
          <tr v-for="g in grades" :key="'wifa-'+g">
            <td class="text-start fw-bold">Grade {{ g }}</td>
            <td>{{ formData.wifa[g]?.julSep || 0 }}</td>
            <td>{{ formData.wifa[g]?.janMar || 0 }}</td>
          </tr>
        </tbody>
      </table>
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
                  <td>{{ rep.school_year }}</td>
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

function createDewormed() { return { sbfpMale:0, sbfpFemale:0, otherMale:0, otherFemale:0 }; }
function createWifa() { return { julSep:0, janMar:0 }; }

createApp({
  data() {
    return {
      selectedSchoolYear: "2021-2022",
      schoolYearOptions: ["2021-2022"],
      grades: [7,8,9,10,11,12],
      formData: {
        dewormed: { 7:createDewormed(),8:createDewormed(),9:createDewormed(),10:createDewormed(),11:createDewormed(),12:createDewormed() },
        wifa: { 7:createWifa(),8:createWifa(),9:createWifa(),10:createWifa(),11:createWifa(),12:createWifa() }
      },
      loading: false, message: '', messageType: 'success',
      savedReports: [], savedReportsLoading: false, loadModal: null
    };
  },
  mounted() {
    this.loadSchoolYearOptions(); this.loadModal = new bootstrap.Modal(document.getElementById('loadModal')); },
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

    dewormTotal(grade,type) {
      const row = this.formData.dewormed[grade] || createDewormed();
      return type === 'sbfp' ? (row.sbfpMale+row.sbfpFemale) : (row.otherMale+row.otherFemale);
    },
    async openLoadModal() {
      this.savedReportsLoading = true;
      try {
        const res = await fetch('api/get_report_list.php?report_key=table1_b&school_year='+encodeURIComponent(this.selectedSchoolYear)+'&cache_buster='+Date.now());
        if(!res.ok) throw new Error('HTTP '+res.status);
        const data = await res.json();
        this.savedReports = data.success ? data.reports : [];
      } catch(e) { this.showMessage('danger','Error: '+e.message); }
      this.savedReportsLoading = false;
      this.loadModal.show();
    },
    loadSelectedReport(reportData) {
      Object.assign(this.formData, reportData);
      this.grades.forEach(g=>{ if(!this.formData.dewormed[g]) this.formData.dewormed[g]=createDewormed(); if(!this.formData.wifa[g]) this.formData.wifa[g]=createWifa(); });
      this.loadModal.hide();
      this.showMessage('success','Report loaded.');
    },
    async loadDewormingWifaData() {
      this.loading = true;
      try {
        const res = await fetch('api/get_table1_deworming_wifa_report.php?school_year='+encodeURIComponent(this.selectedSchoolYear)+'&cache_buster='+Date.now());
        const result = await res.json();
        if(!result.success) throw new Error(result.message);
        this.grades.forEach(g=>{ this.formData.dewormed[g]=createDewormed(); this.formData.wifa[g]=createWifa(); });
        (result.records||[]).forEach(row=>{
          const g=row.grade_level, sex=row.sex?.toLowerCase();
          if(!this.formData.dewormed[g]) return;
          if(sex==='male') {
            this.formData.dewormed[g].sbfpMale += Number(row.sbfp_total||0);
            this.formData.dewormed[g].otherMale += Number(row.other_total||0);
          } else if(sex==='female') {
            this.formData.dewormed[g].sbfpFemale += Number(row.sbfp_total||0);
            this.formData.dewormed[g].otherFemale += Number(row.other_total||0);
            this.formData.wifa[g].julSep += Number(row.wifa_jul_sep||0);
            this.formData.wifa[g].janMar += Number(row.wifa_jan_mar||0);
          }
        });
        this.showMessage('success','Loaded from records.');
      } catch(e) { this.showMessage('danger','Error: '+e.message); }
      this.loading = false;
    },
    showMessage(type,text) { this.messageType=type; this.message=text; setTimeout(()=>this.message='',5000); },
    printForm() { window.print(); }
  }
}).mount("#app");
</script>
</body>
</html>