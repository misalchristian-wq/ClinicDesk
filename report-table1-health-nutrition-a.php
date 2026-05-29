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
    .btn-back {
      background: white;
      color: var(--clinic-primary);
      border: none;
      border-radius: 14px;
      padding: 10px 20px;
      font-weight: 700;
      text-decoration: none;
    }
    .btn-save {
      background: white;
      color: var(--clinic-primary);
      border: none;
      border-radius: 14px;
      padding: 12px 24px;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    .btn-save:disabled { opacity: 0.6; cursor: not-allowed; }
    .section-card {
      background: white;
      border: 1px solid var(--clinic-border);
      border-radius: var(--clinic-radius);
      padding: 24px;
      margin-bottom: 20px;
      box-shadow: var(--clinic-shadow);
    }
    .section-title { font-size: 1.3rem; font-weight: 800; color: var(--clinic-primary); margin-bottom: 16px; }
    .table-responsive { border-radius: 16px; border: 1px solid var(--clinic-border); background: white; margin-bottom: 12px; }
    .table { margin-bottom: 0; font-size: 0.85rem; }
    .table th { background: #f1fbfb; color: #24404d; font-weight: 800; white-space: nowrap; font-size: 0.8rem; text-align: center; vertical-align: middle; }
    .table td { vertical-align: middle; text-align: center; padding: 8px; }
    .total-cell { font-weight: 800; color: var(--clinic-primary); background: #f0fdfa; }
    .alert { border-radius: 14px; border: none; margin-bottom: 16px; }
    @media (max-width: 768px) {
      .container-custom { padding: 12px; }
      .table { font-size: 0.7rem; }
    }
  </style>
</head>
<body>
<div id="app" class="container-custom">
  <div class="header-box no-print">
    <div>
      <h1>🩺 TABLE 1 – Health and Nutrition (A)</h1>
      <p style="margin:4px 0 0; opacity:0.9;">Immunization & Nutritional Status</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn-save" @click="loadTable1Data" :disabled="saving">🔄 Refresh</button>
      <button class="btn-save" @click="saveData" :disabled="saving">{{ saving ? 'Saving...' : '💾 Save' }}</button>
      <button class="btn-save" @click="printForm" style="background:#f0fdfa; color:#0f766e;">🖨️ Print</button>
      <a href="reports.php" class="btn-back">← Back</a>
    </div>
  </div>

  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">{{ message }}</div>

  <div class="section-card">
    <h2 class="section-title">A. Vaccinated Learners through School-Based Immunization (Grade 7)</h2>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead><tr><th>Vaccine</th><th>Male</th><th>Female</th><th>IP Learners</th></tr></thead>
        <tbody>
          <tr>
            <td class="text-start fw-bold">Tetanus Diphtheria</td>
            <td>{{ formData.vaccineTD.male }}</td>
            <td>{{ formData.vaccineTD.female }}</td>
            <td>{{ formData.vaccineTD.ip }}</td>
          </tr>
          <tr>
            <td class="text-start fw-bold">Human Papilloma Virus</td>
            <td>{{ formData.vaccineHPV.male }}</td>
            <td>{{ formData.vaccineHPV.female }}</td>
            <td>{{ formData.vaccineHPV.ip }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="section-card">
    <h2 class="section-title">B. Learners by Nutritional Status – Junior High School</h2>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead><tr><th>Status</th><th>G7 M</th><th>G7 F</th><th>G7 Tot</th><th>G8 M</th><th>G8 F</th><th>G8 Tot</th><th>G9 M</th><th>G9 F</th><th>G9 Tot</th><th>G10 M</th><th>G10 F</th><th>G10 Tot</th></tr></thead>
        <tbody>
          <tr v-for="status in statuses" :key="'jhs-'+status">
            <td class="text-start fw-bold">{{ status }}</td>
            <td>{{ formData.nutritionJHS[status].g7Male }}</td>
            <td>{{ formData.nutritionJHS[status].g7Female }}</td>
            <td class="total-cell">{{ total(formData.nutritionJHS[status], 'g7') }}</td>
            <td>{{ formData.nutritionJHS[status].g8Male }}</td>
            <td>{{ formData.nutritionJHS[status].g8Female }}</td>
            <td class="total-cell">{{ total(formData.nutritionJHS[status], 'g8') }}</td>
            <td>{{ formData.nutritionJHS[status].g9Male }}</td>
            <td>{{ formData.nutritionJHS[status].g9Female }}</td>
            <td class="total-cell">{{ total(formData.nutritionJHS[status], 'g9') }}</td>
            <td>{{ formData.nutritionJHS[status].g10Male }}</td>
            <td>{{ formData.nutritionJHS[status].g10Female }}</td>
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
            <td>{{ formData.nutritionSHS[status].g11Male }}</td>
            <td>{{ formData.nutritionSHS[status].g11Female }}</td>
            <td class="total-cell">{{ total(formData.nutritionSHS[status], 'g11') }}</td>
            <td>{{ formData.nutritionSHS[status].g12Male }}</td>
            <td>{{ formData.nutritionSHS[status].g12Female }}</td>
            <td class="total-cell">{{ total(formData.nutritionSHS[status], 'g12') }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
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
          Normal: createGrade(),
          Obese: createGrade(),
          Overweight: createGrade(),
          'Severely Wasted': createGrade(),
          Wasted: createGrade()
        },
        nutritionSHS: {
          Normal: createGrade(),
          Obese: createGrade(),
          Overweight: createGrade(),
          'Severely Wasted': createGrade(),
          Wasted: createGrade()
        }
      },
      saving: false,
      message: '',
      messageType: 'success'
    };
  },

  mounted() {
    this.loadTable1Data();
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

    resetFetchedData() {
      this.formData.vaccineTD = { male: 0, female: 0, ip: 0 };
      this.formData.vaccineHPV = { male: 0, female: 0, ip: 0 };

      this.statuses.forEach(status => {
        Object.keys(this.formData.nutritionJHS[status]).forEach(key => {
          this.formData.nutritionJHS[status][key] = 0;
        });

        Object.keys(this.formData.nutritionSHS[status]).forEach(key => {
          this.formData.nutritionSHS[status][key] = 0;
        });
      });
    },

    async loadTable1Data() {
      this.resetFetchedData();

      try {
        const response = await fetch('api/get_table1_immunization_nutrition_report.php?cache_buster=' + Date.now());
        const result = await response.json();

        if (!result.success) {
          this.messageType = 'danger';
          this.message = result.message || 'Failed to load Table 1 data.';
          return;
        }

        const immunization = result.immunization || [];
        const nutrition = result.nutrition || [];

        immunization.forEach(row => {
          const vaccine = String(row.vaccine || '').trim().toLowerCase();
          const gender = String(row.sex || row.gender || '').trim().toLowerCase();
          const total = Number(row.total_immunized || row.total || 0);

          if (vaccine.includes('tetanus')) {
            if (gender === 'male') this.formData.vaccineTD.male += total;
            else if (gender === 'female') this.formData.vaccineTD.female += total;
            else this.formData.vaccineTD.female += total;
          }

          if (vaccine.includes('hpv') || vaccine.includes('papilloma')) {
            if (gender === 'male') this.formData.vaccineHPV.male += total;
            else if (gender === 'female') this.formData.vaccineHPV.female += total;
            else this.formData.vaccineHPV.female += total;
          }
        });

        nutrition.forEach(row => {
          const grade = String(row.grade_level || '').trim();
          const sex = String(row.sex || '').trim().toLowerCase();
          const status = this.normalizeStatus(row.bmi_category);

          if (!status || !grade || !sex) return;

          const total = Number(row.total || 0);
          const key = 'g' + grade + (sex === 'male' ? 'Male' : 'Female');

          if (['7','8','9','10'].includes(grade) && this.formData.nutritionJHS[status][key] !== undefined) {
            this.formData.nutritionJHS[status][key] += total;
          }

          if (['11','12'].includes(grade) && this.formData.nutritionSHS[status][key] !== undefined) {
            this.formData.nutritionSHS[status][key] += total;
          }
        });

        this.messageType = 'success';
        this.message = 'Table 1 data loaded successfully.';

      } catch (e) {
        this.messageType = 'danger';
        this.message = 'Error loading Table 1 data: ' + e.message;
      }

      setTimeout(() => { this.message = ''; }, 5000);
    },

    async saveData() {
      this.saving = true;

      try {
        const res = await fetch('api/save_table1_health_nutrition_a.php', {
          method:'POST',
          headers:{'Content-Type':'application/json'},
          body:JSON.stringify(this.formData)
        });

        const result = await res.json();
        this.messageType = result.success ? 'success' : 'danger';
        this.message = result.message || 'Saved.';

      } catch(e) {
        this.messageType='danger';
        this.message='Error: '+e.message;
      }

      this.saving = false;
      setTimeout(()=>{ this.message=''; }, 5000);
    },

    printForm() {
      window.print();
    }
  }
}).mount("#app");
</script>
</body>
</html>