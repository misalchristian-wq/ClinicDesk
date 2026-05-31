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
</head>
<body>
<div id="app" class="container-custom">
  <div class="header-box no-print">
    <div>
      <h1>🩺 TABLE 1 – Health and Nutrition (A)</h1>
      <p style="margin:4px 0 0; opacity:0.9;">Immunization & Nutritional Status – Editable, with Save & Load from saved reports</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn-refresh" @click="openLoadModal" :disabled="loading">📂 Load from Saved</button>
      <button class="btn-refresh" @click="loadAggregatedData" :disabled="loading">🔄 Load from Records</button>
      <button class="btn-save" @click="saveData" :disabled="saving">{{ saving ? 'Saving...' : '💾 Save' }}</button>
      <button class="btn-save" @click="printForm" style="background:#f0fdfa; color:#0f766e;">🖨️ Print</button>
      <a href="reports.php" class="btn-back">← Back</a>
    </div>
  </div>

  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">{{ message }}</div>

  <!-- Rest of the form (same as before) -->
  <div class="section-card">
    <h2 class="section-title">A. Vaccinated Learners through School-Based Immunization (Grade 7)</h2>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead><tr><th>Vaccine</th><th>Male</th><th>Female</th><th>IP Learners</th></tr></thead>
        <tbody>
          <tr><td class="text-start fw-bold">Tetanus Diphtheria</td>
            <td><input type="number" class="form-control form-control-sm" v-model.number="formData.vaccineTD.male"></td>
            <td><input type="number" class="form-control form-control-sm" v-model.number="formData.vaccineTD.female"></td>
            <td><input type="number" class="form-control form-control-sm" v-model.number="formData.vaccineTD.ip"></td>
          </tr>
          <tr><td class="text-start fw-bold">Human Papilloma Virus</td>
            <td><input type="number" class="form-control form-control-sm" v-model.number="formData.vaccineHPV.male"></td>
            <td><input type="number" class="form-control form-control-sm" v-model.number="formData.vaccineHPV.female"></td>
            <td><input type="number" class="form-control form-control-sm" v-model.number="formData.vaccineHPV.ip"></td>
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
            <td><input type="number" class="form-control form-control-sm" v-model.number="formData.nutritionJHS[status].g7Male"></td>
            <td><input type="number" class="form-control form-control-sm" v-model.number="formData.nutritionJHS[status].g7Female"></td>
            <td class="total-cell">{{ total(formData.nutritionJHS[status], 'g7') }}</td>
            <td><input type="number" class="form-control form-control-sm" v-model.number="formData.nutritionJHS[status].g8Male"></td>
            <td><input type="number" class="form-control form-control-sm" v-model.number="formData.nutritionJHS[status].g8Female"></td>
            <td class="total-cell">{{ total(formData.nutritionJHS[status], 'g8') }}</td>
            <td><input type="number" class="form-control form-control-sm" v-model.number="formData.nutritionJHS[status].g9Male"></td>
            <td><input type="number" class="form-control form-control-sm" v-model.number="formData.nutritionJHS[status].g9Female"></td>
            <td class="total-cell">{{ total(formData.nutritionJHS[status], 'g9') }}</td>
            <td><input type="number" class="form-control form-control-sm" v-model.number="formData.nutritionJHS[status].g10Male"></td>
            <td><input type="number" class="form-control form-control-sm" v-model.number="formData.nutritionJHS[status].g10Female"></td>
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
            <td><input type="number" class="form-control form-control-sm" v-model.number="formData.nutritionSHS[status].g11Male"></td>
            <td><input type="number" class="form-control form-control-sm" v-model.number="formData.nutritionSHS[status].g11Female"></td>
            <td class="total-cell">{{ total(formData.nutritionSHS[status], 'g11') }}</td>
            <td><input type="number" class="form-control form-control-sm" v-model.number="formData.nutritionSHS[status].g12Male"></td>
            <td><input type="number" class="form-control form-control-sm" v-model.number="formData.nutritionSHS[status].g12Female"></td>
            <td class="total-cell">{{ total(formData.nutritionSHS[status], 'g12') }}</td>
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
          <div v-if="savedReportsLoading" class="text-center py-4">
            <div class="spinner-border text-primary"></div> Loading...
          </div>
          <div v-else-if="savedReports.length === 0" class="alert alert-info">
            No saved reports found for this report type.
          </div>
          <div v-else class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr><th>School Year</th><th>Saved By</th><th>Saved At</th><th></th></tr>
              </thead>
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
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
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
    const createGrade = () => ({
      g7Male:0, g7Female:0, g8Male:0, g8Female:0, g9Male:0, g9Female:0, g10Male:0, g10Female:0,
      g11Male:0, g11Female:0, g12Male:0, g12Female:0
    });

    return {
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
      loadModal: null
    };
  },

  mounted() {
    this.loadModal = new bootstrap.Modal(document.getElementById('loadModal'));
    // No auto load – user clicks buttons
  },

  methods: {
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
        const res = await fetch('api/get_report_list.php?report_key=table1_a&cache_buster=' + Date.now());
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
        const response = await fetch('api/get_table1_immunization_nutrition_report.php?cache_buster=' + Date.now());
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
          const total = Number(row.total_immunized || 0);
          if (vaccine.includes('tetanus')) this.formData.vaccineTD.female += total;
          else if (vaccine.includes('hpv') || vaccine.includes('papilloma')) this.formData.vaccineHPV.female += total;
        });

        nutrition.forEach(row => {
          const grade = String(row.grade_level || '').trim();
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
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            report_key: 'table1_a',
            school_year: '2021-2022',
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