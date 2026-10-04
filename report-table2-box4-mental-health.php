<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | School Mental Health Report</title>
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
    .container-custom { max-width: 1000px; margin: 0 auto; padding: 24px 20px; }
    .header-box { background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary)); color: white; padding: 24px 28px; border-radius: 24px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; box-shadow: 0 16px 38px rgba(15, 118, 110, 0.18); }
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
    .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
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
      <h1>🧠 TABLE 2 & BOX 4 – Mental Health</h1>
      <p style="margin:4px 0 0; opacity:0.9;">Confirmed cases, guidance counseling, vulnerable groups and training</p>
    </div>
    <div class="d-flex gap-2">
      <select v-model="selectedSchoolYear" class="form-select" style="max-width:180px;border-radius:12px;font-weight:700;" @change="clearReportOnYearChange">
        <option v-for="y in schoolYearOptions" :key="y" :value="y">{{ y }}</option>
      </select>

      <button class="btn-refresh" @click="openLoadModal" :disabled="saving">📂 Load from Saved</button>
      <button class="btn-save" @click="saveData" :disabled="saving">{{ saving ? 'Saving...' : '💾 Save' }}</button>
      <button class="btn-save" @click="printForm" style="background:#f0fdfa; color:#0f766e;">🖨️ Print</button>
    </div>
  </div>
  <div class="report-guide no-print"><strong>Student records and school answers · Table 2 and Box 4</strong><p>Record counseling visits below. Counseling and vulnerable-group totals update from those learners; enter the school answers and confirmed cases, then Save.</p><small>Count suicide-related cases only when confirmed through the school's official reporting process.</small></div>
  <div v-if="savedReports.length && savedReports[0].needs_update" class="saved-status no-print" role="status"><strong>Student records changed since this section was saved.</strong><ul><li v-for="change in savedReports[0].changes" :key="change.source">{{ change.source }}: {{ change.before }} → {{ change.after }}</li></ul><button type="button" @click="reviewSavedReport(savedReports[0])">Review latest counts</button><span class="ms-2 small">Then save this section to update the report.</span></div>
  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">{{ message }}</div>

  <div class="section-card">
    <div class="mb-3">
      <label class="fw-bold">1. Does the school have a guidance office or care center?</label>
      <div class="radio-group">
        <label class="form-check"><input type="radio" class="form-check-input" value="Yes" v-model="formData.hasGuidanceOffice"> Yes</label>
        <label class="form-check"><input type="radio" class="form-check-input" value="No" v-model="formData.hasGuidanceOffice"> No</label>
      </div>
    </div>
  </div>

  <div class="section-card">
    <h2 class="section-title">2. Number of learners who sought guidance counseling</h2>
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3 no-print"><button type="button" class="btn btn-success" @click="openCounselingModal">+ Record counseling visit</button><span class="text-muted small">Each learner counts once for this school year.</span></div>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead><tr><th>Level</th><th>Male</th><th>Female</th><th>Total</th></tr></thead>
        <tbody>
          <tr>
            <td class="text-start fw-bold">Junior High School</td>
            <td>{{ formData.counselingJHS.male }}</td>
            <td>{{ formData.counselingJHS.female }}</td>
            <td class="total-cell">{{ (formData.counselingJHS.male||0) + (formData.counselingJHS.female||0) }}</td>
          </tr>
          <tr>
            <td class="text-start fw-bold">Senior High School</td>
            <td>{{ formData.counselingSHS.male }}</td>
            <td>{{ formData.counselingSHS.female }}</td>
            <td class="total-cell">{{ (formData.counselingSHS.male||0) + (formData.counselingSHS.female||0) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="section-card">
    <h2 class="section-title">2.a Vulnerable groups among learners seeking counseling</h2>
    <p class="text-muted">Count only learners who sought counseling during this school year. Student group labels in the roster identify membership; they do not confirm a counseling visit.</p>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead><tr><th>Level</th><th>Muslim</th><th>IP</th><th>Learners with Disabilities</th></tr></thead>
        <tbody>
          <tr>
            <td class="text-start fw-bold">JHS</td>
            <td>{{ formData.vulnerableJHS.muslim }}</td>
            <td>{{ formData.vulnerableJHS.ip }}</td>
            <td>{{ formData.vulnerableJHS.lwd }}</td>
          </tr>
          <tr>
            <td class="text-start fw-bold">SHS</td>
            <td>{{ formData.vulnerableSHS.muslim }}</td>
            <td>{{ formData.vulnerableSHS.ip }}</td>
            <td>{{ formData.vulnerableSHS.lwd }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="section-card">
    <h2 class="section-title">3. Teacher trainings/activities on mental health</h2>
    <div class="radio-group mb-3">
      <label class="form-check"><input type="radio" class="form-check-input" value="Yes" v-model="formData.hasMentalHealthTraining"> Yes</label>
      <label class="form-check"><input type="radio" class="form-check-input" value="No" v-model="formData.hasMentalHealthTraining"> No</label>
    </div>
    <div v-if="formData.hasMentalHealthTraining === 'Yes'" class="conditional-section form-grid-2">
      <div v-for="topic in topics" :key="topic.key">
        <label class="fw-bold">{{ topic.label }}</label>
        <input type="number" min="0" class="form-control" v-model.number="formData.mentalHealthTraining[topic.key]">
      </div>
    </div>
  </div>

  <div class="section-card">
    <h2 class="section-title">Table 2 · Confirmed suicide-related cases</h2>
    <p class="text-muted">Enter cases within the selected school year. Separate learners from school personnel and cases inside from cases outside the school.</p>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead><tr><th>Case</th><th>Elementary learners</th><th>Elementary personnel</th><th>JHS learners</th><th>JHS personnel</th><th>SHS learners</th><th>SHS personnel</th></tr></thead>
        <tbody><tr v-for="caseType in caseRows" :key="caseType.key">
          <td class="text-start fw-bold">{{ caseType.label }}</td>
          <td v-for="group in caseGroups" :key="group.key"><input type="number" min="0" class="form-control" :aria-label="caseType.label + ', ' + group.label" v-model.number="formData.mentalHealthCases[caseType.key][group.key]"></td>
        </tr></tbody>
      </table>
    </div>
  </div>

  <div class="modal fade" id="counselingModal" tabindex="-1" aria-labelledby="counselingModalTitle">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
      <div class="modal-header" style="background:linear-gradient(135deg,#0f766e,#14b8a6);color:white"><h5 class="modal-title fw-bold" id="counselingModalTitle">Counseling visits · {{ selectedSchoolYear }}</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <label class="fw-bold mb-2" for="counselingSearch">Find student by name or LRN</label>
        <input id="counselingSearch" class="form-control mb-3" style="max-width:none" v-model="counselingSearch" @input="searchCounselingStudents" placeholder="Search students">
        <div class="row g-2 align-items-end"><div class="col-md-7"><label class="fw-bold mb-2" for="counselingStudent">Student</label><select id="counselingStudent" class="form-select" v-model="counselingStudentId"><option value="">Choose a student</option><option v-for="student in counselingStudents" :key="student.record_id" :value="student.record_id">{{ student.learner_name }} · {{ student.lrn }} · Grade {{ student.grade_level }}</option></select></div><div class="col-md-3"><label class="fw-bold mb-2" for="counselingDate">Visit date</label><input id="counselingDate" type="date" class="form-control" v-model="counselingDate"></div><div class="col-md-2"><button type="button" class="btn btn-success w-100" @click="recordCounselingVisit">Add</button></div></div>
        <div v-if="counselingError" class="alert alert-danger mt-3">{{ counselingError }}</div>
        <h6 class="fw-bold mt-4">Recorded visits</h6><p v-if="!counselingVisits.length" class="text-muted">No counseling visits recorded for this school year.</p>
        <div v-else class="table-responsive" style="max-height:260px;overflow:auto"><table class="table"><thead><tr><th>Student</th><th>Grade</th><th>Date</th><th>Action</th></tr></thead><tbody><tr v-for="visit in counselingVisits" :key="visit.visit_id"><td>{{ visit.learner_name }}</td><td>{{ visit.grade_level }}</td><td>{{ visit.visit_date }}</td><td><button v-if="pendingRemoveVisitId!==visit.visit_id" type="button" class="btn btn-sm btn-outline-danger" @click="pendingRemoveVisitId=visit.visit_id">Remove</button><span v-else><span class="small me-2">Remove this visit?</span><button type="button" class="btn btn-sm btn-danger me-1" @click="removeCounselingVisit(visit)">Yes</button><button type="button" class="btn btn-sm btn-outline-secondary" @click="pendingRemoveVisitId=null">Cancel</button></span></td></tr></tbody></table></div>
      </div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button></div>
    </div></div>
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
const blankCaseCounts = () => ({elemLearners:0,elemPersonnel:0,jhsLearners:0,jhsPersonnel:0,shsLearners:0,shsPersonnel:0});
createApp({
  data() {
    return {
      caseRows: [
        {key:'deathInside',label:'Deaths by suicide within school'},
        {key:'deathOutside',label:'Deaths by suicide outside school'},
        {key:'attemptInside',label:'Attempted suicide within school'},
        {key:'attemptOutside',label:'Attempted suicide outside school'}
      ],
      caseGroups: [
        {key:'elemLearners',label:'Elementary learners'}, {key:'elemPersonnel',label:'Elementary personnel'},
        {key:'jhsLearners',label:'JHS learners'}, {key:'jhsPersonnel',label:'JHS personnel'},
        {key:'shsLearners',label:'SHS learners'}, {key:'shsPersonnel',label:'SHS personnel'}
      ],
      selectedSchoolYear: "2021-2022",
      schoolYearOptions: ["2021-2022"],
      topics: [
        { key: 'bullying', label: 'Bullying' },
        { key: 'mentalHealth', label: 'Mental Health/Psychosocial Issues' },
        { key: 'suicidePrevention', label: 'Suicide Prevention' },
        { key: 'selfCare', label: 'Self-Care' },
        { key: 'psychologicalFirstAid', label: 'Psychological First Aid' },
        { key: 'crisisResponse', label: 'Mental Health Crisis Response' }
      ],
      formData: {
        mentalHealthCases: {deathInside:blankCaseCounts(),deathOutside:blankCaseCounts(),attemptInside:blankCaseCounts(),attemptOutside:blankCaseCounts()},
        hasGuidanceOffice: '',
        counselingJHS: { male: 0, female: 0 },
        counselingSHS: { male: 0, female: 0 },
        vulnerableJHS: { muslim: 0, ip: 0, lwd: 0 },
        vulnerableSHS: { muslim: 0, ip: 0, lwd: 0 },
        hasMentalHealthTraining: '',
        mentalHealthTraining: { bullying:0, mentalHealth:0, suicidePrevention:0, selfCare:0, psychologicalFirstAid:0, crisisResponse:0 }
      },
      saving: false,
      loading: false,
      message: '',
      messageType: 'success',
      savedReports: [],
      savedReportsLoading: false,
      loadModal: null, pendingDeleteId: null, reviewedSnapshotHash: '',
      counselingModal: null, counselingSearch: '', counselingStudents: [], counselingVisits: [], counselingStudentId: '', counselingDate: '', counselingError: '', pendingRemoveVisitId: null
    };
  },
  mounted() {
    this.loadSchoolYearOptions();
    this.loadModal = new bootstrap.Modal(document.getElementById('loadModal'));
    this.counselingModal = new bootstrap.Modal(document.getElementById('counselingModal'));
  },
  watch: { selectedSchoolYear: { immediate: true, handler() { this.reviewedSnapshotHash = ''; this.refreshSavedReports(); } } },
  methods: {
    async refreshSavedReports() {
      const year = this.selectedSchoolYear;
      try {
        const res = await fetch('api/get_report_list.php?report_key=box4&school_year=' + encodeURIComponent(year) + '&t=' + Date.now(), {headers:{Authorization:'Bearer ' + localStorage.getItem('local_id_token')}});
        const result = await res.json();
        if (res.ok && result.success && this.selectedSchoolYear === year) this.savedReports = result.reports;
      } catch (e) { console.warn('Could not check saved report status', e); }
    },
    async reviewSavedReport(rep) {
      this.loadSelectedReport(rep.report_data);
      await this.loadAggregatedData();

      if (this.messageType === 'danger') { this.reviewedSnapshotHash = ''; return; }
      this.reviewedSnapshotHash = rep.current_hash;
      this.showMessage('success', rep.needs_update ? 'Latest student counts loaded. Review school answers, then Save.' : 'Saved answers loaded.');
    },
    async deleteSavedReport(rep) {
      try {
        const res = await fetch('api/delete_report.php', {method:'POST',headers:{'Content-Type':'application/json',Authorization:'Bearer ' + localStorage.getItem('local_id_token')},body:JSON.stringify({report_id:rep.report_id,report_key:'box4',school_year:this.selectedSchoolYear})});
        const result = await res.json();
        if (!res.ok || !result.success) throw new Error(result.message || 'Could not delete saved section.');
        this.savedReports = this.savedReports.filter(row => row.report_id !== rep.report_id);
        this.pendingDeleteId = null;
        this.showMessage('success', result.message);
        this.loadModal.hide();
      } catch (e) { this.showMessage('danger', e.message); }
    },
    clearReportOnYearChange() { this.formData = this.$options.data.call(this).formData; this.loadAggregatedData(); },
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
      await this.loadAggregatedData();
    },

    async openLoadModal() {
      this.savedReportsLoading = true;
      try {
        const res = await fetch('api/get_report_list.php?report_key=box4&school_year=' + encodeURIComponent(this.selectedSchoolYear) + '&cache_buster=' + Date.now(), {headers:{Authorization:'Bearer ' + localStorage.getItem('local_id_token')}});
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
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
      // Ensure nested objects exist
      if (!this.formData.counselingJHS) this.formData.counselingJHS = { male:0, female:0 };
      if (!this.formData.counselingSHS) this.formData.counselingSHS = { male:0, female:0 };
      if (!this.formData.vulnerableJHS) this.formData.vulnerableJHS = { muslim:0, ip:0, lwd:0 };
      if (!this.formData.vulnerableSHS) this.formData.vulnerableSHS = { muslim:0, ip:0, lwd:0 };
      if (!this.formData.mentalHealthTraining) this.formData.mentalHealthTraining = { bullying:0, mentalHealth:0, suicidePrevention:0, selfCare:0, psychologicalFirstAid:0, crisisResponse:0 };
      this.loadModal.hide();
      this.showMessage('success', 'Report loaded successfully.');
    },
    async loadAggregatedData() {
      this.loading = true;
      try {
        const response = await fetch('api/box4_counseling.php?school_year=' + encodeURIComponent(this.selectedSchoolYear) + '&q=' + encodeURIComponent(this.counselingSearch) + '&t=' + Date.now(), {headers:{Authorization:'Bearer ' + localStorage.getItem('local_id_token')}});
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || 'Could not load counseling visits.');
        Object.assign(this.formData, result.counts);
        this.counselingStudents = result.students;
        this.counselingVisits = result.visits;
      } catch(e) {
        this.showMessage('danger', e.message);
      }
      this.loading = false;
    },
    openCounselingModal() { this.counselingError=''; this.loadAggregatedData(); this.counselingModal.show(); },
    searchCounselingStudents() { this.loadAggregatedData(); },
    async recordCounselingVisit() {
      this.counselingError='';
      try {
        const response = await fetch('api/box4_counseling.php', {method:'POST',headers:{'Content-Type':'application/json',Authorization:'Bearer ' + localStorage.getItem('local_id_token')},body:JSON.stringify({school_year:this.selectedSchoolYear,student_record_id:this.counselingStudentId,visit_date:this.counselingDate})});
        const result = await response.json(); if (!response.ok || !result.success) throw new Error(result.message || 'Could not record visit.');
        this.counselingStudentId=''; await this.loadAggregatedData(); this.refreshSavedReports();
      } catch(e) { this.counselingError=e.message; }
    },
    async removeCounselingVisit(visit) {
      try {
        const response=await fetch('api/box4_counseling.php',{method:'DELETE',headers:{'Content-Type':'application/json',Authorization:'Bearer ' + localStorage.getItem('local_id_token')},body:JSON.stringify({school_year:this.selectedSchoolYear,visit_id:visit.visit_id})});
        const result=await response.json(); if (!response.ok || !result.success) throw new Error(result.message || 'Could not remove visit.');
        this.pendingRemoveVisitId=null; await this.loadAggregatedData(); this.refreshSavedReports();
      } catch(e) { this.counselingError=e.message; }
    },
    async saveData() {
      this.saving = true;
      try {
        const res = await fetch('api/save_report.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + localStorage.getItem('local_id_token') },
          body: JSON.stringify({
            report_key: 'box4',
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
