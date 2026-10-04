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
  <link href="assets/report-guide.css" rel="stylesheet">
  <link href="assets/report-saved.css" rel="stylesheet">
</head>
<body>
<div id="app" class="container-custom">
  <div class="header-box no-print">
    <a href="reports.php" class="btn-back">← Back to Reports</a>
    <div>
      <h1>💊 TABLE 1 – Health and Nutrition (B)</h1>
      <p style="margin:4px 0 0; opacity:0.9;">Deworming & Weekly Iron Folic Acid (WIFA) – Read‑Only</p>
    </div>
    <div class="d-flex gap-2">
      <select v-model="selectedSchoolYear" class="form-select" style="max-width:180px;border-radius:12px;font-weight:700;" @change="clearReportOnYearChange">
        <option v-for="y in schoolYearOptions" :key="y" :value="y">{{ y }}</option>
      </select>

      <button class="btn-refresh" @click="openLoadModal" :disabled="loading">📂 Load from Saved</button>
      <button class="btn-refresh" @click="loadDewormingWifaData" :disabled="loading">🔄 Load from Records</button>
      <button class="btn-refresh" @click="saveData" :disabled="saving" style="background:#0f766e;color:#fff;">{{ saving ? 'Saving...' : '💾 Save' }}</button>
      <button class="btn-refresh" @click="printForm" style="background:#f0fdfa; color:#0f766e;">🖨️ Print</button>
    </div>
  </div>

  <div class="report-guide no-print"><strong>From records · Tables 1.C–1.D</strong><p>Choose the school year and select “Load from Records” to count deworming and WIFA from SF8 plus nurse-recorded events. Review the periods, then Save.</p><small>A learner is counted once per relevant deworming channel or WIFA period.</small></div>
  <div v-if="savedReports.length && savedReports[0].needs_update" class="saved-status no-print" role="status"><strong>Student records changed since this section was saved.</strong><ul><li v-for="change in savedReports[0].changes" :key="change.source">{{ change.source }}: {{ change.before }} → {{ change.after }}</li></ul><button type="button" @click="reviewSavedReport(savedReports[0])">Review latest counts</button><span class="ms-2 small">Then save this section to update the report.</span></div>
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
    <div v-if="unknownWifaCount" class="alert alert-warning py-2">
      {{ unknownWifaCount }} SF8 WIFA record{{ unknownWifaCount === 1 ? '' : 's' }} have no valid date and are excluded from period totals.
    </div>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Grade</th>
            <th v-for="p in wifaPeriods" :key="'wh-'+p">{{ p }}</th>
            <th v-if="wifaPeriods.length === 0">WIFA (no data)</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="g in grades" :key="'wifa-'+g">
            <td class="text-start fw-bold">Grade {{ g }}</td>
            <td v-for="p in wifaPeriods" :key="'wc-'+g+'-'+p">
              {{ (formData.wifa[g] && formData.wifa[g][p]) || 0 }}
            </td>
            <td v-if="wifaPeriods.length === 0">0</td>
          </tr>
        </tbody>
      </table>
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

function createDewormed() { return { sbfpMale:0, sbfpFemale:0, otherMale:0, otherFemale:0 }; }
function createWifa() { return {}; }  // period_label -> count, filled dynamically

// Derive a period label like "Jul–Sep 2026" from a date string.
function wifaPeriodLabel(dateStr) {
  if (!dateStr) return null;
  const d = new Date(String(dateStr).replace(" ", "T"));
  if (isNaN(d.getTime())) return null;
  const m = d.getMonth(); // 0-11
  const y = d.getFullYear();
  const quarters = [
    { label: "Jan–Mar", months: [0,1,2] },
    { label: "Apr–Jun", months: [3,4,5] },
    { label: "Jul–Sep", months: [6,7,8] },
    { label: "Oct–Dec", months: [9,10,11] },
  ];
  const q = quarters.find(q => q.months.includes(m));
  return q ? `${q.label} ${y}` : null;
}

createApp({
  data() {
    return {
      selectedSchoolYear: "2021-2022",
      schoolYearOptions: ["2021-2022"],
      grades: [7,8,9,10,11,12],
      wifaPeriods: [],   // dynamic column labels detected from the data
      unknownWifaCount: 0,
      formData: {
        dewormed: { 7:createDewormed(),8:createDewormed(),9:createDewormed(),10:createDewormed(),11:createDewormed(),12:createDewormed() },
        wifa: { 7:createWifa(),8:createWifa(),9:createWifa(),10:createWifa(),11:createWifa(),12:createWifa() }
      },
      saving: false, loading: false, message: '', messageType: 'success',
      savedReports: [], savedReportsLoading: false, loadModal: null, pendingDeleteId: null, reviewedSnapshotHash: ''
    };
  },
  mounted() {
    this.loadSchoolYearOptions(); this.loadModal = new bootstrap.Modal(document.getElementById('loadModal')); },
  watch: { selectedSchoolYear: { immediate: true, handler() { this.reviewedSnapshotHash = ''; this.refreshSavedReports(); } } },
  methods: {
    async refreshSavedReports() {
      const year = this.selectedSchoolYear;
      try {
        const res = await fetch('api/get_report_list.php?report_key=table1_b&school_year=' + encodeURIComponent(year) + '&t=' + Date.now(), {headers:{Authorization:'Bearer ' + localStorage.getItem('local_id_token')}});
        const result = await res.json();
        if (res.ok && result.success && this.selectedSchoolYear === year) this.savedReports = result.reports;
      } catch (e) { console.warn('Could not check saved report status', e); }
    },
    async reviewSavedReport(rep) {
      this.loadSelectedReport(rep.report_data);
      await this.loadDewormingWifaData();
      if (this.messageType === 'danger') { this.reviewedSnapshotHash = ''; return; }
      this.reviewedSnapshotHash = rep.current_hash;
      this.showMessage('success', rep.needs_update ? 'Latest student counts loaded. Review school answers, then Save.' : 'Saved answers loaded.');
    },
    async deleteSavedReport(rep) {
      try {
        const res = await fetch('api/delete_report.php', {method:'POST',headers:{'Content-Type':'application/json',Authorization:'Bearer ' + localStorage.getItem('local_id_token')},body:JSON.stringify({report_id:rep.report_id,report_key:'table1_b',school_year:this.selectedSchoolYear})});
        const result = await res.json();
        if (!res.ok || !result.success) throw new Error(result.message || 'Could not delete saved section.');
        this.savedReports = this.savedReports.filter(row => row.report_id !== rep.report_id);
        this.pendingDeleteId = null;
        this.showMessage('success', result.message);
        this.loadModal.hide();
      } catch (e) { this.showMessage('danger', e.message); }
    },
    clearReportOnYearChange() { this.formData = this.$options.data.call(this).formData; this.wifaPeriods = []; this.unknownWifaCount = 0; },
    async saveData() {
      this.saving = true;
      try {
        const res = await fetch('api/save_report.php', {
          method: 'POST', headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + localStorage.getItem('local_id_token') },
          body: JSON.stringify({
            report_key: 'table1_b', school_year: this.selectedSchoolYear,
            saved_by: localStorage.getItem('local_full_name') || 'Clinic Nurse',
            report_data: this.formData, source_review_token: this.reviewedSnapshotHash
          })
        });
        const result = await res.json();
        if (!res.ok) throw new Error(result.message || 'Could not save report.');
        this.showMessage(result.success ? 'success' : 'danger', result.message || (result.success ? 'Saved.' : 'Save failed.'));
        if (result.success) this.refreshSavedReports();
      } catch(e) { this.showMessage('danger', 'Error: ' + e.message); }
      this.saving = false;
    },

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

    dewormTotal(grade,type) {
      const row = this.formData.dewormed[grade] || createDewormed();
      return type === 'sbfp' ? (row.sbfpMale+row.sbfpFemale) : (row.otherMale+row.otherFemale);
    },
    async openLoadModal() {
      this.savedReportsLoading = true;
      try {
        const res = await fetch('api/get_report_list.php?report_key=table1_b&school_year='+encodeURIComponent(this.selectedSchoolYear)+'&cache_buster='+Date.now(), {headers:{Authorization:'Bearer ' + localStorage.getItem('local_id_token')}});
        if(!res.ok) throw new Error('HTTP '+res.status);
        const data = await res.json();
        this.savedReports = data.success ? data.reports : [];
      } catch(e) { this.showMessage('danger','Error: '+e.message); }
      this.savedReportsLoading = false;
      this.loadModal.show();
    },
    loadSelectedReport(reportData) {
      Object.assign(this.formData, reportData);
      this.unknownWifaCount = 0;
      this.grades.forEach(g=>{ if(!this.formData.dewormed[g]) this.formData.dewormed[g]=createDewormed(); if(!this.formData.wifa[g]) this.formData.wifa[g]=createWifa(); });

      // Rebuild WIFA period columns from the saved data's keys.
      const periodsSet = new Set();
      this.grades.forEach(g=>{ Object.keys(this.formData.wifa[g]||{}).forEach(k=>periodsSet.add(k)); });
      const qOrder = {"Jan–Mar":1,"Apr–Jun":2,"Jul–Sep":3,"Oct–Dec":4};
      this.wifaPeriods = Array.from(periodsSet).sort((a,b)=>{
        const [qa,ya]=a.split(' '); const [qb,yb]=b.split(' ');
        return (Number(ya)-Number(yb)) || ((qOrder[qa]||0)-(qOrder[qb]||0));
      });

      this.loadModal.hide();
      this.showMessage('success','Report loaded.');
    },
    async loadDewormingWifaData() {
      this.loading = true;
      try {
        const res = await fetch('api/get_deworming_wifa_raw.php?school_year='+encodeURIComponent(this.selectedSchoolYear)+'&cache_buster='+Date.now(), {
          headers: { Authorization: 'Bearer ' + localStorage.getItem('local_id_token') }
        });
        const result = await res.json();
        if(!result.success) throw new Error(result.message);

        // Reset tables.
        this.grades.forEach(g=>{ this.formData.dewormed[g]=createDewormed(); this.formData.wifa[g]=createWifa(); });
        const periodsSet = new Set();
        const dewormedKeys = new Set();
        const wifaKeys = new Set();
        const unknownDates = new Set();
        const countDeworming = (row, channel) => {
          const gNum = parseInt(String(row.grade_level||'').replace(/\D/g,''),10);
          const sex = String(row.sex||'').toLowerCase();
          if(!this.grades.includes(gNum) || !['male','female'].includes(sex)) return;
          const key = [row.learner_key, channel, gNum, sex].join('|');
          if(dewormedKeys.has(key)) return;
          dewormedKeys.add(key);
          const field = channel === 'SBFP' ? (sex === 'male' ? 'sbfpMale' : 'sbfpFemale')
            : (sex === 'male' ? 'otherMale' : 'otherFemale');
          this.formData.dewormed[gNum][field] += 1;
        };
        const countWifa = (row, date) => {
          const gNum = parseInt(String(row.grade_level||'').replace(/\D/g,''),10);
          if(!this.grades.includes(gNum) || String(row.sex||'').toLowerCase() !== 'female') return;
          const label = wifaPeriodLabel(date);
          if(!label) { unknownDates.add(row.learner_key); return; }
          const key = [row.learner_key, label, gNum].join('|');
          if(wifaKeys.has(key)) return;
          wifaKeys.add(key);
          periodsSet.add(label);
          this.formData.wifa[gNum][label] = (this.formData.wifa[gNum][label] || 0) + 1;
        };

        (result.records||[]).forEach(row=>{
          if(Number(row.dewormed_sbfp||0) === 1) countDeworming(row, 'SBFP');
          if(Number(row.dewormed_other||0) === 1) countDeworming(row, 'Other');
          if(Number(row.wifa||0) === 1) countWifa(row, row.wifa_date);
        });
        (result.wifa_events||[]).forEach(row=>countWifa(row, row.event_date));
        (result.deworming_events||[]).forEach(row=>countDeworming(row, row.channel));
        this.unknownWifaCount = unknownDates.size;

        // Sort detected periods chronologically (by year then quarter order).
        const qOrder = {"Jan–Mar":1,"Apr–Jun":2,"Jul–Sep":3,"Oct–Dec":4};
        this.wifaPeriods = Array.from(periodsSet).sort((a,b)=>{
          const [qa,ya]=a.split(' '); const [qb,yb]=b.split(' ');
          return (Number(ya)-Number(yb)) || ((qOrder[qa]||0)-(qOrder[qb]||0));
        });

        this.showMessage('success','Loaded from records. '+(result.records||[]).length+' rows.');
      } catch(e) { this.showMessage('danger','Error: '+e.message); }
      this.loading = false;
    },
    showMessage(type,text) { this.messageType=type; this.message=text; setTimeout(()=>this.message='',5000); },
    printForm() { window.print(); }
  }
}).mount("#app");
</script>
<script src="assets/table-pagination.js" defer></script>
</body>
</html>
