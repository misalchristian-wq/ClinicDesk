
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | Deworming & WIFA Report</title>
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
    .container-custom { max-width: 1400px; margin: 0 auto; padding: 24px 20px; }
    .header-box { background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary)); color: white; padding: 24px 28px; border-radius: 24px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; box-shadow: 0 16px 38px rgba(15, 118, 110, 0.18); }
    .header-box h1 { font-size: 1.6rem; font-weight: 800; margin: 0; }
    .btn-back { background: white; color: var(--clinic-primary); border: none; border-radius: 14px; padding: 10px 20px; font-weight: 700; text-decoration: none; }
    .btn-save { background: white; color: var(--clinic-primary); border: none; border-radius: 14px; padding: 12px 24px; font-weight: 700; cursor: pointer; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
    .btn-save:disabled { opacity: 0.6; cursor: not-allowed; }
    .section-card { background: white; border: 1px solid var(--clinic-border); border-radius: var(--clinic-radius); padding: 24px; margin-bottom: 20px; box-shadow: var(--clinic-shadow); }
    .section-title { font-size: 1.3rem; font-weight: 800; color: var(--clinic-primary); margin-bottom: 16px; }
    .table-responsive { border-radius: 16px; border: 1px solid var(--clinic-border); background: white; margin-bottom: 12px; }
    .table { margin-bottom: 0; font-size: 0.85rem; }
    .table th { background: #f1fbfb; color: #24404d; font-weight: 800; white-space: nowrap; font-size: 0.8rem; text-align: center; vertical-align: middle; }
    .table td { vertical-align: middle; text-align: center; padding: 8px; }
    .total-cell { font-weight: 800; color: var(--clinic-primary); background: #f0fdfa; }
    .alert { border-radius: 14px; border: none; margin-bottom: 16px; }
    @media (max-width: 768px) { .container-custom { padding: 12px; } .table { font-size: 0.7rem; } }
  </style>
</head>
<body>
<div id="app" class="container-custom">
  <div class="header-box no-print">
    <div>
      <h1>💊 TABLE 1 – Health and Nutrition (B)</h1>
      <p style="margin:4px 0 0; opacity:0.9;">Deworming & Weekly Iron Folic Acid (WIFA)</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn-save" @click="loadDewormingWifaData" :disabled="saving">🔄 Refresh</button>
      <button class="btn-save" @click="saveData" :disabled="saving">{{ saving ? 'Saving...' : '💾 Save' }}</button>
      <button class="btn-save" @click="printForm" style="background:#f0fdfa; color:#0f766e;">🖨️ Print</button>
      <a href="reports.php" class="btn-back">← Back</a>
    </div>
  </div>

  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">{{ message }}</div>

  <div class="section-card">
    <h2 class="section-title">C. Number of Learners Dewormed</h2>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Grade</th>
            <th>SBFP Male</th>
            <th>SBFP Female</th>
            <th>SBFP Total</th>
            <th>Other Male</th>
            <th>Other Female</th>
            <th>Other Total</th>
          </tr>
        </thead>
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
        <thead>
          <tr>
            <th>Grade</th>
            <th>Jul–Sep 2024</th>
            <th>Jan–Mar 2025</th>
          </tr>
        </thead>
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
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script>
const { createApp } = Vue;

function createDewormed() {
  return { sbfpMale: 0, sbfpFemale: 0, otherMale: 0, otherFemale: 0 };
}

function createWifa() {
  return { julSep: 0, janMar: 0 };
}

createApp({
  data() {
    return {
      grades: [7, 8, 9, 10, 11, 12],
      formData: {
        dewormed: {
          7: createDewormed(), 8: createDewormed(), 9: createDewormed(),
          10: createDewormed(), 11: createDewormed(), 12: createDewormed()
        },
        wifa: {
          7: createWifa(), 8: createWifa(), 9: createWifa(),
          10: createWifa(), 11: createWifa(), 12: createWifa()
        }
      },
      saving: false,
      message: '',
      messageType: 'success'
    };
  },

  mounted() {
    this.loadDewormingWifaData();
  },

  methods: {
    resetData() {
      this.grades.forEach(g => {
        this.formData.dewormed[g] = createDewormed();
        this.formData.wifa[g] = createWifa();
      });
    },

    dewormTotal(grade, type) {
      const row = this.formData.dewormed[grade] || createDewormed();

      if (type === 'sbfp') {
        return Number(row.sbfpMale || 0) + Number(row.sbfpFemale || 0);
      }

      return Number(row.otherMale || 0) + Number(row.otherFemale || 0);
    },

    async loadDewormingWifaData() {
      this.resetData();

      try {
        const response = await fetch('api/get_table1_deworming_wifa_report.php?cache_buster=' + Date.now());
        const result = await response.json();

        if (!result.success) {
          this.messageType = 'danger';
          this.message = result.message || 'Failed to load Deworming/WIFA data.';
          return;
        }

        const records = result.records || [];

        records.forEach(row => {
          const grade = String(row.grade_level || '').trim();
          const sex = String(row.sex || '').trim().toLowerCase();

          if (!this.formData.dewormed[grade]) return;

          const sbfp = Number(row.sbfp_total || 0);
          const other = Number(row.other_total || 0);
          const wifaJulSep = Number(row.wifa_jul_sep || 0);
          const wifaJanMar = Number(row.wifa_jan_mar || 0);

          if (sex === 'male') {
            this.formData.dewormed[grade].sbfpMale += sbfp;
            this.formData.dewormed[grade].otherMale += other;
          } else if (sex === 'female') {
            this.formData.dewormed[grade].sbfpFemale += sbfp;
            this.formData.dewormed[grade].otherFemale += other;

            this.formData.wifa[grade].julSep += wifaJulSep;
            this.formData.wifa[grade].janMar += wifaJanMar;
          }
        });

        this.messageType = 'success';
        this.message = 'Deworming/WIFA data loaded successfully.';

      } catch (e) {
        this.messageType = 'danger';
        this.message = 'Error loading Deworming/WIFA data: ' + e.message;
      }

      setTimeout(() => { this.message = ''; }, 5000);
    },

    async saveData() {
      this.saving = true;

      try {
        const res = await fetch('api/save_table1_health_nutrition_b.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({
            school_year: '2021-2022',
            saved_by: localStorage.getItem('nurse_email') || 'Clinic Nurse',
            report_data: this.formData
          })
        });

        const result = await res.json();

        this.messageType = result.success ? 'success' : 'danger';
        this.message = result.message || 'Saved.';

      } catch(e) {
        this.messageType = 'danger';
        this.message = 'Error: ' + e.message;
      }

      this.saving = false;
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

