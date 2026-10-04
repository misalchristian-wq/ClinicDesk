<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | OKD and LHAS Report (Read‑Only)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    :root {
      --clinic-primary: #0f766e;
      --clinic-secondary: #14b8a6;
      --clinic-border: #d9eef0;
      --clinic-text: #16323f;
      --clinic-shadow: 0 16px 38px rgba(15,118,110,0.08);
      --clinic-radius: 22px;
    }
    * { box-sizing: border-box; }
    body {
      min-height: 100vh;
      margin: 0;
      background: #f5fafb;
      font-family: 'Plus Jakarta Sans', Arial, sans-serif;
      color: var(--clinic-text);
    }
    .container-custom {
      max-width: 1400px;
      margin: 0 auto;
      padding: 24px 20px;
    }
    .header-box {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white;
      padding: 24px 28px;
      border-radius: 24px;
      margin-bottom: 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 16px;
      box-shadow: 0 16px 38px rgba(15,118,110,0.18);
    }
    .header-box h1 { font-size: 1.6rem; font-weight: 800; margin: 0; }
    .btn-back, .btn-refresh {
      background: white;
      color: var(--clinic-primary);
      border: none;
      border-radius: 14px;
      padding: 12px 20px;
      font-weight: 700;
      text-decoration: none;
      display: inline-block;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
      cursor: pointer;
    }
    .btn-back:hover, .btn-refresh:hover {
      background: #ecfeff;
      color: var(--clinic-primary);
    }
    .btn-refresh:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
    .section-card {
      background: white;
      border: 1px solid var(--clinic-border);
      border-radius: var(--clinic-radius);
      padding: 24px;
      margin-bottom: 20px;
      box-shadow: var(--clinic-shadow);
    }
    .section-title {
      font-size: 1.3rem;
      font-weight: 800;
      color: var(--clinic-primary);
      margin-bottom: 16px;
    }
    .table-responsive {
      border-radius: 16px;
      border: 1px solid var(--clinic-border);
      background: white;
      margin-bottom: 12px;
    }
    .table {
      margin-bottom: 0;
      font-size: 0.85rem;
    }
    .table th {
      background: #f1fbfb;
      color: #24404d;
      font-weight: 800;
      white-space: nowrap;
      font-size: 0.8rem;
      text-align: center;
      vertical-align: middle;
    }
    .table td {
      vertical-align: middle;
      text-align: center;
      padding: 8px;
    }
    .total-cell {
      font-weight: 800;
      color: var(--clinic-primary);
      background: #f0fdfa;
    }
    .checkbox-group {
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
      margin: 8px 0 16px;
    }
    .form-check {
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .alert {
      border-radius: 14px;
      border: none;
      margin-bottom: 16px;
    }
    .modal-content {
      border-radius: var(--clinic-radius);
    }
    @media print {
      body { background: white !important; }
      .no-print { display: none !important; }
      .section-card { border: none !important; box-shadow: none !important; }
    }
  </style>
  <link href="assets/report-guide.css" rel="stylesheet">
  <link href="assets/report-saved.css" rel="stylesheet">
</head>
<body>
<div id="app" class="container-custom">

  <div class="header-box no-print">
    <a href="reports.php" class="btn-back">← Back to Reports</a>
    <div>
      <h1>📋 BOX 1 – Oplan Kalusugan sa DepEd (OKD) and LHAS</h1>
      <p style="margin:4px 0 0; opacity:0.9;">Junior High School & Senior High School (Read‑Only)</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <select v-model="selectedSchoolYear" class="form-select" style="max-width:180px;border-radius:12px;font-weight:700;" @change="clearReportOnYearChange">
        <option v-for="y in schoolYearOptions" :key="y" :value="y">{{ y }}</option>
      </select>

      <button class="btn-refresh" @click="openLoadModal" :disabled="loading">📂 Load from Saved</button>
      <button class="btn-refresh" @click="loadAggregatedData" :disabled="loading">🔄 Load from Records</button>
      <button class="btn-refresh" @click="saveData" :disabled="saving" style="background:#0f766e;color:#fff;">{{ saving ? 'Saving...' : '💾 Save' }}</button>
      <button class="btn-refresh" @click="printForm" style="background:#f0fdfa; color:#0f766e;">🖨️ Print</button>
    </div>
  </div>

  <div class="report-guide no-print"><strong>From records + school answers · Box 1</strong><p>Choose the school year, load screening counts from learner records, select the school's referral concern types, then Save.</p><small>Loading counts replaces the numbers shown, so review the result before saving.</small></div>
  <div v-if="savedReports.length && savedReports[0].needs_update" class="saved-status no-print" role="status"><strong>Student records changed since this section was saved.</strong><ul><li v-for="change in savedReports[0].changes" :key="change.source">{{ change.source }}: {{ change.before }} → {{ change.after }}</li></ul><button type="button" @click="reviewSavedReport(savedReports[0])">Review latest counts</button><span class="ms-2 small">Then save this section to update the report.</span></div>
  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">
    {{ message }}
  </div>

  <div class="section-card">
    <h2 class="section-title">1. Functional referral mechanisms by concern</h2>
    <div class="checkbox-group mb-3">
      <label class="form-check" v-for="item in referralConcernOptions" :key="item"><input type="checkbox" class="form-check-input" :value="item" v-model="formData.referralConcerns"><span>{{ item }}</span></label>
    </div>
    <h3 class="subsection-title">Available referral destinations</h3>
    <div class="checkbox-group">
      <label class="form-check" v-for="item in referralOptions" :key="item">
        <input type="checkbox" class="form-check-input" :value="item" v-model="formData.referralMechanisms">
        <span>{{ item }}</span>
      </label>
    </div>
  </div>

  <div class="section-card">
    <h2 class="section-title">2. Learners Health Assessment and Screening (LHAS) – Junior High School</h2>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead>
          <tr><th>Screening Type</th><th>Masterlisted</th><th>Underwent Screening</th><th>With Findings</th><th>Referred School</th><th>Referred LGU/DOH</th><th>Referred Private</th><th>Referred Others</th><th>Total Referred</th></tr>
        </thead>
        <tbody>
          <tr v-for="(row, rIdx) in lhasRows" :key="'jhs-'+rIdx">
            <td class="text-start fw-bold">{{ row }}</td>
            <td>{{ formData.lhasJHS[rIdx]?.masterlisted || 0 }}</td>
            <td>{{ formData.lhasJHS[rIdx]?.screened || 0 }}</td>
            <td>{{ formData.lhasJHS[rIdx]?.findings || 0 }}</td>
            <td>{{ formData.lhasJHS[rIdx]?.referredSchool || 0 }}</td>
            <td>{{ formData.lhasJHS[rIdx]?.referredLGU || 0 }}</td>
            <td>{{ formData.lhasJHS[rIdx]?.referredPrivate || 0 }}</td>
            <td>{{ formData.lhasJHS[rIdx]?.referredOthers || 0 }}</td>
            <td class="total-cell">{{ lhasTotal(formData.lhasJHS[rIdx]) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="section-card">
    <h2 class="section-title">3. Learners Health Assessment and Screening (LHAS) – Senior High School</h2>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead>
          <tr><th>Screening Type</th><th>Masterlisted</th><th>Underwent Screening</th><th>With Findings</th><th>Referred School</th><th>Referred LGU/DOH</th><th>Referred Private</th><th>Referred Others</th><th>Total Referred</th></tr>
        </thead>
        <tbody>
          <tr v-for="(row, rIdx) in lhasRows" :key="'shs-'+rIdx">
            <td class="text-start fw-bold">{{ row }}</td>
            <td>{{ formData.lhasSHS[rIdx]?.masterlisted || 0 }}</td>
            <td>{{ formData.lhasSHS[rIdx]?.screened || 0 }}</td>
            <td>{{ formData.lhasSHS[rIdx]?.findings || 0 }}</td>
            <td>{{ formData.lhasSHS[rIdx]?.referredSchool || 0 }}</td>
            <td>{{ formData.lhasSHS[rIdx]?.referredLGU || 0 }}</td>
            <td>{{ formData.lhasSHS[rIdx]?.referredPrivate || 0 }}</td>
            <td>{{ formData.lhasSHS[rIdx]?.referredOthers || 0 }}</td>
            <td class="total-cell">{{ lhasTotal(formData.lhasSHS[rIdx]) }}</td>
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

function blankLhasRow() {
  return {
    masterlisted: 0,
    screened: 0,
    findings: 0,
    referredSchool: 0,
    referredLGU: 0,
    referredPrivate: 0,
    referredOthers: 0
  };
}

createApp({
  data() {
    const rows = [
      "Nutritional Assessment",
      "Health History",
      "Vision Screening",
      "Hearing Screening",
      "Oral Health",
      "CARS",
      "Rapid HEEADSSS"
    ];

    return {
      referralConcernOptions: ['Medical, dental and nutritional','Mental health and well-being','Adolescent reproductive health','Drug, substance and tobacco use'],
      saving: false, loading: false,
      message: "",
      messageType: "success",
      selectedSchoolYear: "2021-2022",
      schoolYearOptions: ["2021-2022"],

      referralOptions: [
        "School Clinic",
        "Guidance Office",
        "LGU/DOH",
        "Private Clinic/Hospital",
        "Others"
      ],

      lhasRows: rows,

      formData: {
        referralConcerns: [],
        referralMechanisms: [],
        lhasJHS: rows.map(() => blankLhasRow()),
        lhasSHS: rows.map(() => blankLhasRow())
      },

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
        const res = await fetch('api/get_report_list.php?report_key=box1&school_year=' + encodeURIComponent(year) + '&t=' + Date.now(), {headers:{Authorization:'Bearer ' + localStorage.getItem('local_id_token')}});
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
        const res = await fetch('api/delete_report.php', {method:'POST',headers:{'Content-Type':'application/json',Authorization:'Bearer ' + localStorage.getItem('local_id_token')},body:JSON.stringify({report_id:rep.report_id,report_key:'box1',school_year:this.selectedSchoolYear})});
        const result = await res.json();
        if (!res.ok || !result.success) throw new Error(result.message || 'Could not delete saved section.');
        this.savedReports = this.savedReports.filter(row => row.report_id !== rep.report_id);
        this.pendingDeleteId = null;
        this.showMessage('success', result.message);
        this.loadModal.hide();
      } catch (e) { this.showMessage('danger', e.message); }
    },
    clearReportOnYearChange() { this.formData = this.$options.data.call(this).formData; },
    async saveData() {
      this.saving = true;
      try {
        const res = await fetch('api/save_report.php', {
          method: 'POST', headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + localStorage.getItem('local_id_token') },
          body: JSON.stringify({
            report_key: 'box1', school_year: this.selectedSchoolYear,
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

    lhasTotal(row) {
      if (!row) return 0;
      return Number(row.referredSchool || 0) +
             Number(row.referredLGU || 0) +
             Number(row.referredPrivate || 0) +
             Number(row.referredOthers || 0);
    },

    async openLoadModal() {
      this.savedReportsLoading = true;
      try {
        const res = await fetch('api/get_report_list.php?report_key=box1&school_year=' + encodeURIComponent(this.selectedSchoolYear) + '&cache_buster=' + Date.now(), {headers:{Authorization:'Bearer ' + localStorage.getItem('local_id_token')}});
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
      if (!this.formData.referralMechanisms) this.formData.referralMechanisms = [];
      if (!this.formData.lhasJHS) this.formData.lhasJHS = this.lhasRows.map(() => blankLhasRow());
      if (!this.formData.lhasSHS) this.formData.lhasSHS = this.lhasRows.map(() => blankLhasRow());
      this.loadModal.hide();
      this.showMessage('success', 'Report loaded successfully.');
    },

    async loadAggregatedData() {
      this.loading = true;
      try {
        const response = await fetch("api/get_box1_okd_lhas_report.php?school_year=" + encodeURIComponent(this.selectedSchoolYear) + "&cache_buster=" + Date.now(), {
          headers: { Authorization: "Bearer " + localStorage.getItem("local_id_token") }
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.message || "Failed to load aggregated data");

        // Reset data
        this.formData.lhasJHS = this.lhasRows.map(() => blankLhasRow());
        this.formData.lhasSHS = this.lhasRows.map(() => blankLhasRow());

        const records = result.records || [];
        records.forEach(record => {
          const screeningType = String(record.screening_type || "").trim().toLowerCase();
          const index = this.lhasRows.findIndex(row => row.trim().toLowerCase() === screeningType);
          if (index === -1) return;

          this.formData.lhasJHS[index] = {
            masterlisted: Number(record.jhs_masterlisted || 0),
            screened: Number(record.jhs_screened || 0),
            findings: Number(record.jhs_findings || 0),
            referredSchool: Number(record.jhs_referred_school || 0),
            referredLGU: Number(record.jhs_referred_lgu || 0),
            referredPrivate: Number(record.jhs_referred_private || 0),
            referredOthers: Number(record.jhs_referred_others || 0)
          };

          this.formData.lhasSHS[index] = {
            masterlisted: Number(record.shs_masterlisted || 0),
            screened: Number(record.shs_screened || 0),
            findings: Number(record.shs_findings || 0),
            referredSchool: Number(record.shs_referred_school || 0),
            referredLGU: Number(record.shs_referred_lgu || 0),
            referredPrivate: Number(record.shs_referred_private || 0),
            referredOthers: Number(record.shs_referred_others || 0)
          };
        });

        this.showMessage('success', 'Loaded aggregated data from approved records.');
      } catch (error) {
        this.showMessage('danger', 'Error loading aggregated data: ' + error.message);
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
<script src="assets/table-pagination.js" defer></script>
</body>
</html>
