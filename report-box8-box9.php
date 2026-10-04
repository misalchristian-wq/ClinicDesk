<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | Food Handling & Feeding Program</title>
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
    .radio-group { display: flex; flex-wrap: wrap; gap: 16px; margin: 8px 0 16px; }
    .form-check { display: flex; align-items: center; gap: 6px; }
    .form-check-input:checked { background-color: var(--clinic-primary); border-color: var(--clinic-primary); }
    .checkbox-group { display: flex; flex-wrap: wrap; gap: 16px; margin: 8px 0 16px; }
    .conditional-section { background: #fbfefe; border: 1px solid var(--clinic-border); border-radius: 14px; padding: 16px; margin-top: 12px; }
    .form-control { border-radius: 12px; border: 1px solid var(--clinic-border); padding: 10px 14px; }
    .form-control:focus { border-color: var(--clinic-secondary); box-shadow: 0 0 0 0.2rem rgba(20,184,166,0.12); }
    .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .alert { border-radius: 14px; border: none; margin-bottom: 16px; }
    .modal-content { border-radius: var(--clinic-radius); }
    .school-year-select { max-width: 200px; }
    @media (max-width: 768px) { .container-custom { padding: 12px; } .form-grid-2 { grid-template-columns: 1fr; } }
  </style>
  <link href="assets/report-guide.css" rel="stylesheet">
  <link href="assets/report-saved.css" rel="stylesheet">
</head>
<body>
<div id="app" class="container-custom">
  <div class="header-box no-print">
    <a href="reports.php" class="btn-back">← Back to Reports</a>
    <div>
      <h1>🍽️ BOXES 7–9 – Drug Education, Food & Feeding</h1>
      <p style="margin:4px 0 0; opacity:0.9;">School-wide program answers for the official report</p>
    </div>
    <div class="d-flex gap-2 align-items-center flex-wrap">
      <select v-model="selectedSchoolYear" class="form-select school-year-select" @change="clearReportOnYearChange(); openLoadModal()">
        <option v-for="y in schoolYearOptions" :key="y" :value="y">{{ y }}</option>
      </select>
      <button class="btn-refresh" @click="openLoadModal" :disabled="saving">📂 Load from Saved</button>
      <button class="btn-save" @click="saveData" :disabled="saving">{{ saving ? 'Saving...' : '💾 Save' }}</button>
      <button class="btn-save" @click="printForm" style="background:#f0fdfa; color:#0f766e;">🖨️ Print</button>
    </div>
  </div>

  <div class="report-guide no-print"><strong>School answers · Boxes 7–9</strong><p>Choose the school year, enter drug-education, canteen, kitchen, feeding-fund and agriculture information, then Save. Use “Load from Saved” to revise the answer.</p><small>Individual feeding measurements remain in student monitoring; this section records school program resources.</small></div>
  <div v-if="savedReports.length && savedReports[0].needs_update" class="saved-status no-print" role="status"><strong>Student records changed since this section was saved.</strong><ul><li v-for="change in savedReports[0].changes" :key="change.source">{{ change.source }}: {{ change.before }} → {{ change.after }}</li></ul><button type="button" @click="reviewSavedReport(savedReports[0])">Review latest counts</button><span class="ms-2 small">Then save this section to update the report.</span></div>
  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">{{ message }}</div>

  <div class="section-card">
    <h2 class="section-title">Box 7 · Preventive drug education</h2>
    <label class="fw-bold">Does the school implement a preventive drug education program?</label>
    <div class="radio-group mb-3">
      <label class="form-check"><input type="radio" class="form-check-input" value="Yes" v-model="formData.drugEducation"> Yes</label>
      <label class="form-check"><input type="radio" class="form-check-input" value="No" v-model="formData.drugEducation"> No</label>
    </div>
    <label class="fw-bold">Program components</label>
    <div class="checkbox-group mb-3">
      <label class="form-check" v-for="component in drugComponents" :key="component"><input type="checkbox" class="form-check-input" :value="component" v-model="formData.drugComponents"> {{ component }}</label>
    </div>
    <label class="fw-bold">Learners trained in life skills for drug prevention in the previous school year</label>
    <div class="form-grid-2 mt-2">
      <div v-for="grade in [7,8,9,10,11,12]" :key="grade"><label class="fw-bold">Grade {{ grade }}</label><input type="number" min="0" class="form-control" v-model.number="formData.drugLifeSkills['g'+grade]"></div>
    </div>
  </div>

  <!-- BOX 8 -->
  <div class="section-card">
    <h2 class="section-title">BOX 8 – Food Handling</h2>
    <div class="mb-3">
      <label class="fw-bold">1. Does the school have a canteen?</label>
      <div class="radio-group">
        <label class="form-check"><input type="radio" class="form-check-input" value="Yes" v-model="formData.hasCanteen"> Yes</label>
        <label class="form-check"><input type="radio" class="form-check-input" value="No" v-model="formData.hasCanteen"> No</label>
      </div>
    </div>
    <div v-if="formData.hasCanteen === 'Yes'" class="conditional-section">
      <label class="fw-bold">Managed by:</label>
      <div class="radio-group">
        <label class="form-check"><input type="radio" class="form-check-input" value="School" v-model="formData.canteenManager"> School</label>
        <label class="form-check"><input type="radio" class="form-check-input" value="Teacher-Coop" v-model="formData.canteenManager"> Teacher-Coop</label>
        <label class="form-check"><input type="radio" class="form-check-input" value="Others" v-model="formData.canteenManager"> Others</label>
      </div>
      <div v-if="formData.canteenManager === 'Others'" class="mt-2">
        <input type="text" class="form-control" placeholder="Specify other manager" v-model="formData.canteenManagerOther">
      </div>
      <div class="form-grid-2 mt-3">
        <div>
          <label class="fw-bold">Sanitary Permit</label>
          <div class="radio-group">
            <label class="form-check"><input type="radio" class="form-check-input" value="Yes" v-model="formData.sanitaryPermit"> Yes</label>
            <label class="form-check"><input type="radio" class="form-check-input" value="No" v-model="formData.sanitaryPermit"> No</label>
          </div>
        </div>
        <div>
          <label class="fw-bold">Food handlers have health certificates</label>
          <div class="radio-group">
            <label class="form-check"><input type="radio" class="form-check-input" value="Yes" v-model="formData.healthCertificates"> Yes</label>
            <label class="form-check"><input type="radio" class="form-check-input" value="No" v-model="formData.healthCertificates"> No</label>
          </div>
        </div>
      </div>
    </div>
    <div class="mb-3 mt-3">
      <label class="fw-bold">2. Does the school have a kitchen?</label>
      <div class="radio-group">
        <label class="form-check"><input type="radio" class="form-check-input" value="Yes" v-model="formData.hasKitchen"> Yes</label>
        <label class="form-check"><input type="radio" class="form-check-input" value="No" v-model="formData.hasKitchen"> No</label>
      </div>
    </div>
  </div>

  <!-- BOX 9 -->
  <div class="section-card">
    <h2 class="section-title">BOX 9 – Feeding Program</h2>
    <div class="mb-3">
      <label class="fw-bold">1. Sources of funding for feeding program</label>
      <div class="checkbox-group">
        <label class="form-check" v-for="fund in feedingFundSources" :key="fund">
          <input type="checkbox" class="form-check-input" :value="fund" v-model="formData.feedingFundSources"> {{ fund }}
        </label>
      </div>
    </div>
    <div class="mb-3">
      <label class="fw-bold">2. Available agriculture/fishery resources</label>
      <div class="checkbox-group">
        <label class="form-check" v-for="res in agriResources" :key="res">
          <input type="checkbox" class="form-check-input" :value="res" v-model="formData.agriResources"> {{ res }}
        </label>
      </div>
    </div>
  </div>

  <!-- LOAD MODAL -->
  <div class="modal fade saved-modal" id="loadModal" tabindex="-1" data-bs-backdrop="static" aria-labelledby="savedModalTitle">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
      <div class="modal-header"><div><h5 class="modal-title fw-bold" id="savedModalTitle">Saved report · {{ selectedSchoolYear }}</h5><small>Review, update, or delete this section's saved answers.</small></div><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <div v-if="savedReportsLoading" class="text-center py-4"><div class="spinner-border text-success" role="status"></div> Loading saved section...</div>
        <div v-else-if="savedReports.length === 0" class="alert alert-info">No saved section for this school year. Complete the form and choose Save.</div>
        <div v-else><div v-for="rep in savedReports" :key="rep.report_id" class="saved-card">
          <div class="d-flex justify-content-between flex-wrap gap-2"><div><strong>{{ rep.school_year }}</strong><div class="text-muted small">Saved by {{ rep.saved_by }} · {{ rep.saved_at }}</div></div><span :class="['badge', rep.needs_update ? 'bg-warning text-dark' : 'bg-success']">{{ rep.needs_update ? 'Needs update' : 'Current' }}</span></div>
          <div v-if="rep.needs_update" class="saved-diff"><strong>Student records changed since this report was saved.</strong><ul><li v-for="change in rep.changes" :key="change.source">{{ change.source }}: {{ change.before }} → {{ change.after }}</li></ul><small>Review the latest counts, then save this section. School answers are kept.</small></div>
          <div class="saved-actions"><button type="button" class="btn-clinic" @click="reviewSavedReport(rep)">{{ rep.needs_update ? 'Review changes' : 'Load answers' }}</button><button type="button" class="btn btn-outline-danger" @click="pendingDeleteId=rep.report_id">Delete saved section</button></div>
          <div v-if="pendingDeleteId===rep.report_id" class="alert alert-danger mt-3 mb-0"><strong>Delete the saved {{ selectedSchoolYear }} section?</strong><p class="mb-2">The saved answers will be archived; student records stay in place.</p><button type="button" class="btn btn-danger btn-sm me-2" @click="deleteSavedReport(rep)">Yes, delete</button><button type="button" class="btn btn-outline-secondary btn-sm" @click="pendingDeleteId=null">Cancel</button></div>
        </div></div>
      </div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button></div>
    </div></div>
  </div>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const { createApp } = Vue;

createApp({
  data() {
    return {
      drugComponents: ['Curriculum integration','Extra-curricular activities','Barangay Anti-Drug Abuse Council partnership'],
      feedingFundSources: ['School MOOE','School Canteen Fund','LGU Fund','PTA Fund','Barangay Fund','Private Individual/Sector Fund','SBFP'],
      agriResources: ['Gulayan sa Paaralan','Fish Pond','Agricultural Crops','Livestock'],
      selectedSchoolYear: "2021-2022",
      schoolYearOptions: ["2021-2022"],
      formData: {
        drugEducation: '', drugComponents: [],
        drugLifeSkills: {g7:0,g8:0,g9:0,g10:0,g11:0,g12:0},
        hasCanteen: '', canteenManager: '', canteenManagerOther: '',
        sanitaryPermit: '', healthCertificates: '', hasKitchen: '',
        feedingFundSources: [], agriResources: []
      },
      saving: false,
      loading: false,
      message: '',
      messageType: 'success',
      savedReports: [],
      savedReportsLoading: false,
      loadModal: null, pendingDeleteId: null, reviewedSnapshotHash: ''
    };
  },

  mounted() {
    this.loadSchoolYearOptions();
    this.loadModal = new bootstrap.Modal(document.getElementById('loadModal'));
  },

  watch: { selectedSchoolYear: { immediate: true, handler() { this.reviewedSnapshotHash = ''; this.refreshSavedReports(); } } },
  methods: {
    async refreshSavedReports() {
      const year = this.selectedSchoolYear;
      try {
        const res = await fetch('api/get_report_list.php?report_key=box8_9&school_year=' + encodeURIComponent(year) + '&t=' + Date.now(), {headers:{Authorization:'Bearer ' + localStorage.getItem('local_id_token')}});
        const result = await res.json();
        if (res.ok && result.success && this.selectedSchoolYear === year) this.savedReports = result.reports;
      } catch (e) { console.warn('Could not check saved report status', e); }
    },
    async reviewSavedReport(rep) {
      this.loadSelectedReport(rep.report_data);

      if (this.messageType === 'danger') { this.reviewedSnapshotHash = ''; return; }
      this.reviewedSnapshotHash = rep.current_hash;
      this.showMessage('success', rep.needs_update ? 'Latest student counts loaded. Review school answers, then Save.' : 'Saved answers loaded.');
    },
    async deleteSavedReport(rep) {
      try {
        const res = await fetch('api/delete_report.php', {method:'POST',headers:{'Content-Type':'application/json',Authorization:'Bearer ' + localStorage.getItem('local_id_token')},body:JSON.stringify({report_id:rep.report_id,report_key:'box8_9',school_year:this.selectedSchoolYear})});
        const result = await res.json();
        if (!res.ok || !result.success) throw new Error(result.message || 'Could not delete saved section.');
        this.savedReports = this.savedReports.filter(row => row.report_id !== rep.report_id);
        this.pendingDeleteId = null;
        this.showMessage('success', result.message);
        this.loadModal.hide();
      } catch (e) { this.showMessage('danger', e.message); }
    },
    clearReportOnYearChange() { this.formData = this.$options.data.call(this).formData; },
    async loadSchoolYearOptions() {
      try {
        const res = await fetch('api/get_school_years.php?t=' + Date.now());
        const data = await res.json();
        if (data.success && Array.isArray(data.years) && data.years.length) {
          this.schoolYearOptions = data.years.map(y => y.year_label);
          // Default to the active year if present, else the first option.
          const requestedYear = new URLSearchParams(window.location.search).get('school_year');
          if (requestedYear && this.schoolYearOptions.includes(requestedYear)) {
            this.selectedSchoolYear = requestedYear;
          } else if (data.active && this.schoolYearOptions.includes(data.active)) {
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
        const url = `api/get_report_list.php?report_key=box8_9&school_year=${this.selectedSchoolYear}&cache_buster=${Date.now()}`;
        const res = await fetch(url, {headers:{Authorization:'Bearer ' + localStorage.getItem('local_id_token')}});
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
      if (!this.formData.feedingFundSources) this.formData.feedingFundSources = [];
      if (!this.formData.agriResources) this.formData.agriResources = [];
      this.loadModal.hide();
      this.showMessage('success', `Loaded saved report for ${this.selectedSchoolYear}. You can now edit and save.`);
    },

    async loadAggregatedData() {
      this.loading = true;
      this.showMessage('info', 'No aggregated data available for Box 8/9. Use "Load from Saved" to view previously saved reports.');
      this.loading = false;
    },

    async saveData() {
      this.saving = true;
      try {
        const res = await fetch('api/save_report.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + localStorage.getItem('local_id_token') },
          body: JSON.stringify({
            report_key: 'box8_9',
            school_year: this.selectedSchoolYear,
            saved_by: localStorage.getItem('local_full_name') || 'Clinic Nurse',
            report_data: this.formData, source_review_token: this.reviewedSnapshotHash
          })
        });
        const result = await res.json();
        if (!res.ok) throw new Error(result.message || 'Could not save report.');
        this.showMessage(result.success ? 'success' : 'danger', result.message || (result.success ? 'Saved.' : 'Save failed.'));
        if (result.success) this.refreshSavedReports();
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
<script src="assets/table-pagination.js" defer></script>
</body>
</html>
