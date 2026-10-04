<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | Health and Nutrition Report</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    :root {
      --clinic-primary: #0f766e;
      --clinic-secondary: #14b8a6;
      --clinic-border: #d9eef0;
      --clinic-text: #16323f;
      --clinic-muted: #6b7d87;
      --clinic-shadow: 0 16px 38px rgba(15, 118, 110, 0.08);
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
    .container-custom { max-width: 1400px; margin: 0 auto; padding: 24px 20px; }
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
      box-shadow: 0 16px 38px rgba(15, 118, 110, 0.18);
    }
    .header-box h1 { font-size: 1.6rem; font-weight: 800; margin: 0; }
    .btn-back { background: white; color: var(--clinic-primary); border: none; border-radius: 14px; padding: 10px 20px; font-weight: 700; text-decoration: none; }
    .btn-save { background: white; color: var(--clinic-primary); border: none; border-radius: 14px; padding: 12px 24px; font-weight: 700; cursor: pointer; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
    .btn-save:disabled { opacity: 0.6; cursor: not-allowed; }
    .btn-refresh { background: white; color: var(--clinic-primary); border: none; border-radius: 14px; padding: 12px 24px; font-weight: 700; cursor: pointer; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
    .section-card { background: white; border: 1px solid var(--clinic-border); border-radius: var(--clinic-radius); padding: 24px; margin-bottom: 20px; box-shadow: var(--clinic-shadow); }
    .section-title { font-size: 1.3rem; font-weight: 800; color: var(--clinic-primary); margin-bottom: 16px; }
    .table-responsive { border-radius: 16px; border: 1px solid var(--clinic-border); background: white; margin-bottom: 12px; }
    .table { margin-bottom: 0; font-size: 0.85rem; }
    .table th { background: #f1fbfb; color: #24404d; font-weight: 800; white-space: nowrap; font-size: 0.8rem; text-align: center; vertical-align: middle; }
    .table td { vertical-align: middle; text-align: center; padding: 8px; }
    .total-cell { font-weight: 800; color: var(--clinic-primary); background: #f0fdfa; }
    .alert { border-radius: 14px; border: none; margin-bottom: 16px; }
    input.form-control-sm { width: 80px; display: inline-block; margin: 0 auto; text-align: center; }
    .modal-content { border-radius: var(--clinic-radius); }
    @media (max-width: 768px) { .container-custom { padding: 12px; } .table { font-size: 0.7rem; } input.form-control-sm { width: 60px; } }
  </style>
  <link href="assets/report-guide.css" rel="stylesheet">
  <link href="assets/report-saved.css" rel="stylesheet">
</head>
<body>
<div id="app" class="container-custom">
  <div class="header-box no-print">
    <a href="reports.php" class="btn-back">← Back to Reports</a>
    <div>
      <h1>🩺 TABLE 1 – Health and Nutrition (A)</h1>
      <p style="margin:4px 0 0; opacity:0.9;">Immunization & Nutritional Status – Editable, with Save & Load from saved reports</p>
    </div>
    <div class="d-flex gap-2">
      <select v-model="selectedSchoolYear" class="form-select" style="max-width:180px;border-radius:12px;font-weight:700;" @change="clearReportOnYearChange">
        <option v-for="y in schoolYearOptions" :key="y" :value="y">{{ y }}</option>
      </select>

      <button class="btn-refresh" @click="openLoadModal" :disabled="loading">📂 Load from Saved</button>
      <button class="btn-refresh" @click="loadAggregatedData" :disabled="loading">🔄 Load from Records</button>
      <button class="btn-save" @click="saveData" :disabled="saving">{{ saving ? 'Saving...' : '💾 Save' }}</button>
      <button class="btn-save" @click="printForm" style="background:#f0fdfa; color:#0f766e;">🖨️ Print</button>
    </div>
  </div>

  <div class="report-guide no-print"><strong>From records · Tables 1.A–1.B</strong><p>Choose the school year and select “Load from Records” to count immunization and nutritional status. Review the totals, then Save this year's snapshot.</p><small>Loading replaces the counts currently shown with the latest records for the selected year.</small></div>
  <div v-if="savedReports.length && savedReports[0].needs_update" class="saved-status no-print" role="status"><strong>Student records changed since this section was saved.</strong><ul><li v-for="change in savedReports[0].changes" :key="change.source">{{ change.source }}: {{ change.before }} → {{ change.after }}</li></ul><button type="button" @click="reviewSavedReport(savedReports[0])">Review latest counts</button><span class="ms-2 small">Then save this section to update the report.</span></div>
  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">{{ message }}</div>

  <!-- Rest of the form (same as before) -->
  <div class="section-card">
    <h2 class="section-title">A. Vaccinated Learners through School-Based Immunization (Grade 7)</h2>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead><tr><th>Vaccine</th><th>Male</th><th>Female</th><th>IP Learners</th></tr></thead>
        <tbody>
          <tr><td class="text-start fw-bold">Tetanus Diphtheria</td>
            <td>{{ formData.vaccineTD.male || 0 }}</td>
            <td>{{ formData.vaccineTD.female || 0 }}</td>
            <td>{{ formData.vaccineTD.ip || 0 }}</td>
          </tr>
          <tr><td class="text-start fw-bold">Human Papilloma Virus</td>
            <td>{{ formData.vaccineHPV.male || 0 }}</td>
            <td>{{ formData.vaccineHPV.female || 0 }}</td>
            <td>{{ formData.vaccineHPV.ip || 0 }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="section-card">
    <h2 class="section-title">B. Learners by Nutritional Status – Junior High School</h2>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead>
          <tr><th>Status</th><th>G7 M</th><th>G7 F</th><th>G7 Tot</th><th>G8 M</th><th>G8 F</th><th>G8 Tot</th><th>G9 M</th><th>G9 F</th><th>G9 Tot</th><th>G10 M</th><th>G10 F</th><th>G10 Tot</th></tr>
        </thead>
        <tbody>
          <tr v-for="status in statuses" :key="'jhs-'+status">
            <td class="text-start fw-bold">{{ status }}</td>
            <td>{{ formData.nutritionJHS[status].g7Male || 0 }}</td>
            <td>{{ formData.nutritionJHS[status].g7Female || 0 }}</td>
            <td class="total-cell">{{ total(formData.nutritionJHS[status], 'g7') }}</td>
            <td>{{ formData.nutritionJHS[status].g8Male || 0 }}</td>
            <td>{{ formData.nutritionJHS[status].g8Female || 0 }}</td>
            <td class="total-cell">{{ total(formData.nutritionJHS[status], 'g8') }}</td>
            <td>{{ formData.nutritionJHS[status].g9Male || 0 }}</td>
            <td>{{ formData.nutritionJHS[status].g9Female || 0 }}</td>
            <td class="total-cell">{{ total(formData.nutritionJHS[status], 'g9') }}</td>
            <td>{{ formData.nutritionJHS[status].g10Male || 0 }}</td>
            <td>{{ formData.nutritionJHS[status].g10Female || 0 }}</td>
            <td class="total-cell">{{ total(formData.nutritionJHS[status], 'g10') }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="section-card">
    <h2 class="section-title">B. Learners by Nutritional Status – Senior High School</h2>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead><tr><th>Status</th><th>G11 M</th><th>G11 F</th><th>G11 Tot</th><th>G12 M</th><th>G12 F</th><th>G12 Tot</th></tr></thead>
        <tbody>
          <tr v-for="status in statuses" :key="'shs-'+status">
            <td class="text-start fw-bold">{{ status }}</td>
            <td>{{ formData.nutritionSHS[status].g11Male || 0 }}</td>
            <td>{{ formData.nutritionSHS[status].g11Female || 0 }}</td>
            <td class="total-cell">{{ total(formData.nutritionSHS[status], 'g11') }}</td>
            <td>{{ formData.nutritionSHS[status].g12Male || 0 }}</td>
            <td>{{ formData.nutritionSHS[status].g12Female || 0 }}</td>
            <td class="total-cell">{{ total(formData.nutritionSHS[status], 'g12') }}</td>
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

createApp({
  data() {
    const createGrade = () => ({
      g7Male:0, g7Female:0, g8Male:0, g8Female:0, g9Male:0, g9Female:0, g10Male:0, g10Female:0,
      g11Male:0, g11Female:0, g12Male:0, g12Female:0
    });

    return {
      selectedSchoolYear: '2021-2022',
      schoolYearOptions: ['2021-2022'],
      statuses: ['Normal', 'Obese', 'Overweight', 'Severely Wasted', 'Wasted'],
      formData: {
        vaccineTD: { male: 0, female: 0, ip: 0 },
        vaccineHPV: { male: 0, female: 0, ip: 0 },
        nutritionJHS: {
          Normal: createGrade(), Obese: createGrade(), Overweight: createGrade(),
          'Severely Wasted': createGrade(), Wasted: createGrade()
        },
        nutritionSHS: {
          Normal: createGrade(), Obese: createGrade(), Overweight: createGrade(),
          'Severely Wasted': createGrade(), Wasted: createGrade()
        }
      },
      loading: false,
      saving: false,
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
    // No auto load – user clicks buttons
  },

  watch: { selectedSchoolYear: { immediate: true, handler() { this.reviewedSnapshotHash = ''; this.refreshSavedReports(); } } },
  methods: {
    async refreshSavedReports() {
      const year = this.selectedSchoolYear;
      try {
        const res = await fetch('api/get_report_list.php?report_key=table1_a&school_year=' + encodeURIComponent(year) + '&t=' + Date.now(), {headers:{Authorization:'Bearer ' + localStorage.getItem('local_id_token')}});
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
        const res = await fetch('api/delete_report.php', {method:'POST',headers:{'Content-Type':'application/json',Authorization:'Bearer ' + localStorage.getItem('local_id_token')},body:JSON.stringify({report_id:rep.report_id,report_key:'table1_a',school_year:this.selectedSchoolYear})});
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

    total(obj, grade) {
      return Number(obj[grade + 'Male'] || 0) + Number(obj[grade + 'Female'] || 0);
    },

    normalizeStatus(status) {
      const text = String(status || '').trim().toLowerCase();
      if (text === 'normal') return 'Normal';
      if (text === 'obese') return 'Obese';
      if (text === 'overweight') return 'Overweight';
      if (text === 'severely wasted') return 'Severely Wasted';
      if (text === 'wasted') return 'Wasted';
      return '';
    },

    async openLoadModal() {
      this.savedReportsLoading = true;
      this.savedReports = [];
      try {
        const res = await fetch('api/get_report_list.php?report_key=table1_a&school_year=' + encodeURIComponent(this.selectedSchoolYear) + '&cache_buster=' + Date.now(), {headers:{Authorization:'Bearer ' + localStorage.getItem('local_id_token')}});
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        if (data.success) {
          this.savedReports = data.reports || [];
        } else {
          this.showMessage('danger', data.message || 'Failed to load saved reports.');
        }
      } catch (e) {
        this.showMessage('danger', 'Error loading saved reports: ' + e.message);
      }
      this.savedReportsLoading = false;
      this.loadModal.show();
    },

    loadSelectedReport(reportData) {
      // Deep merge into formData
      Object.assign(this.formData, reportData);
      // Ensure nested objects exist
      if (!this.formData.vaccineTD) this.formData.vaccineTD = { male:0, female:0, ip:0 };
      if (!this.formData.vaccineHPV) this.formData.vaccineHPV = { male:0, female:0, ip:0 };
      this.statuses.forEach(status => {
        if (!this.formData.nutritionJHS[status]) this.formData.nutritionJHS[status] = this.createEmptyGrade();
        if (!this.formData.nutritionSHS[status]) this.formData.nutritionSHS[status] = this.createEmptyGrade();
      });
      this.loadModal.hide();
      this.showMessage('success', 'Report loaded successfully.');
    },

    createEmptyGrade() {
      return { g7Male:0, g7Female:0, g8Male:0, g8Female:0, g9Male:0, g9Female:0, g10Male:0, g10Female:0, g11Male:0, g11Female:0, g12Male:0, g12Female:0 };
    },

    async loadAggregatedData() {
      this.loading = true;
      try {
        const response = await fetch('api/get_table1_immunization_nutrition_report.php?school_year=' + encodeURIComponent(this.selectedSchoolYear) + '&cache_buster=' + Date.now(), {
          headers: { Authorization: 'Bearer ' + localStorage.getItem('local_id_token') }
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const result = await response.json();
        if (!result.success) throw new Error(result.message || 'Failed to load aggregated data');

        const immunization = result.immunization || [];
        const nutrition = result.nutrition || [];

        // Reset
        this.formData.vaccineTD = { male: 0, female: 0, ip: 0 };
        this.formData.vaccineHPV = { male: 0, female: 0, ip: 0 };
        this.statuses.forEach(status => {
          Object.keys(this.formData.nutritionJHS[status]).forEach(k => this.formData.nutritionJHS[status][k] = 0);
          Object.keys(this.formData.nutritionSHS[status]).forEach(k => this.formData.nutritionSHS[status][k] = 0);
        });

        immunization.forEach(row => {
          const vaccine = String(row.vaccine || '').toLowerCase();
          const sex = String(row.sex || '').toLowerCase();
          const total = Number(row.total_immunized || 0);
          const field = sex.startsWith('m') ? 'male' : sex.startsWith('f') ? 'female' : null;
          if (!field) return;
          if (vaccine.includes('tetanus') || vaccine === 'td') this.formData.vaccineTD[field] += total;
          else if (vaccine.includes('hpv') || vaccine.includes('papilloma')) this.formData.vaccineHPV[field] += total;
        });

        nutrition.forEach(row => {
          const grade = String(row.grade_level || '').replace(/\D/g, '');
          const sex = String(row.sex || '').toLowerCase();
          const status = this.normalizeStatus(row.bmi_category);
          const total = Number(row.total || 0);
          if (!status || !grade) return;
          const key = 'g' + grade + (sex === 'male' ? 'Male' : 'Female');
          if (['7','8','9','10'].includes(grade) && this.formData.nutritionJHS[status][key] !== undefined)
            this.formData.nutritionJHS[status][key] += total;
          if (['11','12'].includes(grade) && this.formData.nutritionSHS[status][key] !== undefined)
            this.formData.nutritionSHS[status][key] += total;
        });

        this.showMessage('success', 'Loaded aggregated data from approved records. You can now edit and save.');
      } catch (e) {
        this.showMessage('danger', 'Error loading aggregated data: ' + e.message);
      }
      this.loading = false;
    },

    async saveData() {
      this.saving = true;
      try {
        const res = await fetch('api/save_report.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + localStorage.getItem('local_id_token') },
          body: JSON.stringify({
            report_key: 'table1_a',
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
