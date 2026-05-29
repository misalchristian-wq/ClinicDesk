<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | ARH & Tobacco Control Report</title>
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
    .container-custom { max-width: 1100px; margin: 0 auto; padding: 24px 20px; }
    .header-box { background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary)); color: white; padding: 24px 28px; border-radius: 24px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; box-shadow: 0 16px 38px rgba(15, 118, 110, 0.18); }
    .header-box h1 { font-size: 1.6rem; font-weight: 800; margin: 0; }
    .btn-back { background: white; color: var(--clinic-primary); border: none; border-radius: 14px; padding: 10px 20px; font-weight: 700; text-decoration: none; }
    .btn-save { background: white; color: var(--clinic-primary); border: none; border-radius: 14px; padding: 12px 24px; font-weight: 700; cursor: pointer; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
    .btn-save:disabled { opacity: 0.6; cursor: not-allowed; }
    .section-card { background: white; border: 1px solid var(--clinic-border); border-radius: var(--clinic-radius); padding: 24px; margin-bottom: 20px; box-shadow: var(--clinic-shadow); }
    .section-title { font-size: 1.3rem; font-weight: 800; color: var(--clinic-primary); margin-bottom: 16px; }
    .subsection-title { font-size: 1.05rem; font-weight: 700; color: var(--clinic-text); margin: 16px 0 10px; padding-bottom: 8px; border-bottom: 2px solid #ecfeff; }
    .radio-group, .checkbox-group { display: flex; flex-wrap: wrap; gap: 16px; margin: 8px 0 16px; }
    .form-check { display: flex; align-items: center; gap: 6px; }
    .form-check-input:checked { background-color: var(--clinic-primary); border-color: var(--clinic-primary); }
    .form-control { border-radius: 12px; border: 1px solid var(--clinic-border); padding: 10px 14px; max-width: 250px; }
    .table-responsive { border-radius: 16px; border: 1px solid var(--clinic-border); background: white; margin-bottom: 12px; }
    .table { margin-bottom: 0; font-size: 0.85rem; }
    .table th { background: #f1fbfb; color: #24404d; font-weight: 800; font-size: 0.8rem; text-align: center; }
    .table td { vertical-align: middle; text-align: center; padding: 8px; }
    .total-cell { font-weight: 800; color: var(--clinic-primary); background: #f0fdfa; }
    .alert { border-radius: 14px; border: none; margin-bottom: 16px; }
    @media (max-width: 768px) { .container-custom { padding: 12px; } }
  </style>
</head>

<body>
<div id="app" class="container-custom">
  <div class="header-box no-print">
    <div>
      <h1>👥 BOX 5 & 6 – ARH & Tobacco Control</h1>
      <p style="margin:4px 0 0; opacity:0.9;">Adolescent Reproductive Health and Comprehensive Tobacco Control</p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
      <button class="btn-save" @click="loadBox5Box6Data" :disabled="saving">🔄 Refresh</button>
      <button class="btn-save" @click="saveData" :disabled="saving">{{ saving ? 'Saving...' : '💾 Save' }}</button>
      <button class="btn-save" @click="printForm" style="background:#f0fdfa; color:#0f766e;">🖨️ Print</button>
      <a href="reports.php" class="btn-back">← Back</a>
    </div>
  </div>

  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">
    {{ message }}
  </div>

  <div class="section-card">
    <h2 class="section-title">BOX 5 – Adolescent Reproductive Health</h2>

    <h4 class="subsection-title">1. Number of Pregnant Learners</h4>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Status</th>
            <th v-for="g in grades" :key="'head-'+g">G{{ g }}</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="status in pregnancyStatuses" :key="status">
            <td class="text-start fw-bold">{{ status }}</td>
            <td v-for="g in grades" :key="'preg-'+status+g">
              {{ formData.pregnantLearners[status]['g' + g] || 0 }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mb-3">
      <label class="fw-bold">2. Functional learner support center?</label>
      <div class="radio-group">
        <label class="form-check">
          <input type="radio" class="form-check-input" value="Yes" v-model="formData.hasSupportCenter"> Yes
        </label>
        <label class="form-check">
          <input type="radio" class="form-check-input" value="No" v-model="formData.hasSupportCenter"> No
        </label>
      </div>
    </div>

    <div class="mb-3">
      <label class="fw-bold">3. Number of learners trained as peer educators for ASRH</label>
      <div class="form-control bg-light">{{ formData.peerEducators || 0 }}</div>
    </div>
  </div>

  <div class="section-card">
    <h2 class="section-title">BOX 6 – Comprehensive Tobacco Control</h2>

    <div class="mb-3">
      <label class="fw-bold">1. IEC Materials displayed</label>
      <div class="checkbox-group">
        <label class="form-check">
          <input type="checkbox" class="form-check-input" value="No Smoking Signages" v-model="formData.iecMaterials">
          No Smoking Signages
        </label>
        <label class="form-check">
          <input type="checkbox" class="form-check-input" value="Poster prohibiting cigarette sales" v-model="formData.iecMaterials">
          Poster prohibiting cigarette sales
        </label>
      </div>
    </div>

    <div class="mb-3">
      <label class="fw-bold">2. Stores within 100 meters selling:</label>
      <div class="checkbox-group">
        <label class="form-check">
          <input type="checkbox" class="form-check-input" value="Tobacco products" v-model="formData.storesSelling">
          Tobacco products
        </label>
        <label class="form-check">
          <input type="checkbox" class="form-check-input" value="Vape/e-cigarettes" v-model="formData.storesSelling">
          Vape/e-cigarettes
        </label>
      </div>
    </div>

    <h4 class="subsection-title">3. Learners recorded bringing tobacco/vape products</h4>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Category</th>
            <th>JHS</th>
            <th>SHS</th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <td class="text-start fw-bold">Brought products</td>
            <td>{{ formData.tobaccoViolations.jhs.brought || 0 }}</td>
            <td>{{ formData.tobaccoViolations.shs.brought || 0 }}</td>
          </tr>
          <tr>
            <td class="text-start fw-bold">Referred to care</td>
            <td>{{ formData.tobaccoViolations.jhs.referred || 0 }}</td>
            <td>{{ formData.tobaccoViolations.shs.referred || 0 }}</td>
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
    return {
      grades: [7, 8, 9, 10, 11, 12],
      pregnancyStatuses: ["In School", "On Alternative Delivery Mode (ADM)"],

      formData: {
        pregnantLearners: {
          "In School": { g7: 0, g8: 0, g9: 0, g10: 0, g11: 0, g12: 0 },
          "On Alternative Delivery Mode (ADM)": { g7: 0, g8: 0, g9: 0, g10: 0, g11: 0, g12: 0 }
        },
        hasSupportCenter: "",
        peerEducators: 0,
        iecMaterials: [],
        storesSelling: [],
        tobaccoViolations: {
          jhs: { brought: 0, referred: 0 },
          shs: { brought: 0, referred: 0 }
        }
      },

      saving: false,
      message: "",
      messageType: "success"
    };
  },

  mounted() {
    this.loadBox5Box6Data();
  },

  methods: {
    resetFetchedData() {
      this.pregnancyStatuses.forEach(status => {
        this.grades.forEach(g => {
          this.formData.pregnantLearners[status]["g" + g] = 0;
        });
      });

      this.formData.peerEducators = 0;
      this.formData.tobaccoViolations.jhs.brought = 0;
      this.formData.tobaccoViolations.jhs.referred = 0;
      this.formData.tobaccoViolations.shs.brought = 0;
      this.formData.tobaccoViolations.shs.referred = 0;
    },

    async loadBox5Box6Data() {
      this.resetFetchedData();

      try {
        const response = await fetch("api/get_box5_box6_report.php?cache_buster=" + Date.now());
        const result = await response.json();

        if (!result.success) {
          this.messageType = "danger";
          this.message = result.message || "Failed to load report data.";
          return;
        }

        const arh = result.arh || [];
        const tobacco = result.tobacco || [];

        arh.forEach(row => {
          const grade = String(row.grade_level || "").trim();
          const deliveryMode = String(row.delivery_mode || "").trim().toLowerCase();
          const total = Number(row.total || 0);

          if (!this.grades.includes(Number(grade))) return;

          if (deliveryMode === "in school") {
            this.formData.pregnantLearners["In School"]["g" + grade] += total;
          } else if (deliveryMode === "adm" || deliveryMode.includes("alternative")) {
            this.formData.pregnantLearners["On Alternative Delivery Mode (ADM)"]["g" + grade] += total;
          }
        });

        this.formData.peerEducators = Number(result.peer_educators || 0);

        tobacco.forEach(row => {
          if (row.level_group === "jhs") {
            this.formData.tobaccoViolations.jhs.brought = Number(row.brought || 0);
            this.formData.tobaccoViolations.jhs.referred = Number(row.referred || 0);
          }

          if (row.level_group === "shs") {
            this.formData.tobaccoViolations.shs.brought = Number(row.brought || 0);
            this.formData.tobaccoViolations.shs.referred = Number(row.referred || 0);
          }
        });

        await this.loadSavedBox5Box6();

        this.messageType = "success";
        this.message = "ARH and Tobacco data loaded successfully.";

      } catch (error) {
        this.messageType = "danger";
        this.message = "Error loading data: " + error.message;
      }

      setTimeout(() => {
        this.message = "";
      }, 5000);
    },

    async loadSavedBox5Box6() {
      try {
        const response = await fetch("api/get_box5_box6_saved_report.php?school_year=2021-2022&cache_buster=" + Date.now());
        const result = await response.json();

        if (result.success && result.has_saved && result.report_data) {
          this.formData.hasSupportCenter = result.report_data.hasSupportCenter || "";
          this.formData.iecMaterials = result.report_data.iecMaterials || [];
          this.formData.storesSelling = result.report_data.storesSelling || [];
        }
      } catch (error) {
        console.log("No saved Box 5/6 data loaded:", error.message);
      }
    },

    async saveData() {
      this.saving = true;

      try {
        const res = await fetch("api/save_box5_box6.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            school_year: "2021-2022",
            saved_by: localStorage.getItem("nurse_email") || "Clinic Nurse",
            report_data: this.formData
          })
        });

        const result = await res.json();

        this.messageType = result.success ? "success" : "danger";
        this.message = result.message || "Saved.";
      } catch (e) {
        this.messageType = "danger";
        this.message = "Error: " + e.message;
      }

      this.saving = false;

      setTimeout(() => {
        this.message = "";
      }, 5000);
    },

    printForm() {
      window.print();
    }
  }
}).mount("#app");
</script>
</body>
</html>