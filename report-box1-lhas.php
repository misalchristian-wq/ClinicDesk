<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | OKD and LHAS Report</title>
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

    .header-box h1 {
      font-size: 1.6rem;
      font-weight: 800;
      margin: 0;
    }

    .btn-back,
    .btn-save {
      background: white;
      color: var(--clinic-primary);
      border: none;
      border-radius: 14px;
      padding: 12px 20px;
      font-weight: 700;
      text-decoration: none;
      display: inline-block;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }

    .btn-back:hover,
    .btn-save:hover {
      background: #ecfeff;
      color: var(--clinic-primary);
    }

    .btn-save:disabled {
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

    @media print {
      body { background: white !important; }
      .no-print { display: none !important; }
      .section-card { border: none !important; box-shadow: none !important; }
    }
  </style>
</head>

<body>
<div id="app" class="container-custom">

  <div class="header-box no-print">
    <div>
      <h1>📋 BOX 1 – Oplan Kalusugan sa DepEd (OKD) and LHAS</h1>
      <p style="margin:4px 0 0; opacity:0.9;">Junior High School & Senior High School</p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
      <button class="btn-save" @click="loadBox1Data" :disabled="saving">🔄 Refresh</button>
      <button class="btn-save" @click="saveData" :disabled="saving">
        {{ saving ? 'Saving...' : '💾 Save' }}
      </button>
      <button class="btn-save" @click="printForm" style="background:#f0fdfa; color:#0f766e;">🖨️ Print</button>
      <a href="reports.php" class="btn-back">← Back to Reports</a>
    </div>
  </div>

  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">
    {{ message }}
  </div>

  <div class="section-card">
    <h2 class="section-title">1. Functional Referral Mechanism for Learners with Health Concerns</h2>

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
          <tr>
            <th>Screening Type</th>
            <th>Masterlisted</th>
            <th>Underwent Screening</th>
            <th>With Findings</th>
            <th>Referred School</th>
            <th>Referred LGU/DOH</th>
            <th>Referred Private</th>
            <th>Referred Others</th>
            <th>Total Referred</th>
          </tr>
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
          <tr>
            <th>Screening Type</th>
            <th>Masterlisted</th>
            <th>Underwent Screening</th>
            <th>With Findings</th>
            <th>Referred School</th>
            <th>Referred LGU/DOH</th>
            <th>Referred Private</th>
            <th>Referred Others</th>
            <th>Total Referred</th>
          </tr>
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

</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

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
      saving: false,
      message: "",
      messageType: "success",
      schoolYear: "2021-2022",

      referralOptions: [
        "School Clinic",
        "Guidance Office",
        "LGU/DOH",
        "Private Clinic/Hospital",
        "Others"
      ],

      lhasRows: rows,

      formData: {
        referralMechanisms: [],
        lhasJHS: rows.map(() => blankLhasRow()),
        lhasSHS: rows.map(() => blankLhasRow())
      }
    };
  },

  mounted() {
    this.loadBox1Data();
  },

  methods: {
    resetRows() {
      this.formData.lhasJHS = this.lhasRows.map(() => blankLhasRow());
      this.formData.lhasSHS = this.lhasRows.map(() => blankLhasRow());
    },

    lhasTotal(row) {
      if (!row) return 0;

      return Number(row.referredSchool || 0) +
        Number(row.referredLGU || 0) +
        Number(row.referredPrivate || 0) +
        Number(row.referredOthers || 0);
    },

    async loadBox1Data() {
      this.resetRows();

      try {
        const response = await fetch("api/get_box1_okd_lhas_report.php?cache_buster=" + Date.now());
        const result = await response.json();

        if (!result.success) {
  this.messageType = "danger";
  this.message = result.message || "Failed to load Box 1 data.";
  return;
}

const records = result.records || [];

records.forEach(record => {
  const screeningType = String(record.screening_type || "").trim().toLowerCase();

  const index = this.lhasRows.findIndex(row =>
    row.trim().toLowerCase() === screeningType
  );

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

        await this.loadSavedReport();

        this.messageType = "success";
        this.message = "Box 1 data loaded from approved OKD and LHAS records.";

      } catch (error) {
        this.messageType = "danger";
        this.message = "Error loading Box 1 data: " + error.message;
      }
    },

    async loadSavedReport() {
      try {
        const response = await fetch(
          "api/get_box1_okd_lhas_saved_report.php?school_year=" +
          encodeURIComponent(this.schoolYear) +
          "&cache_buster=" +
          Date.now()
        );

        const result = await response.json();

        if (result.success && result.has_saved && result.report_data) {
          this.formData.referralMechanisms = result.report_data.referralMechanisms || [];
        }

      } catch (error) {
        console.log("No saved Box 1 report loaded:", error.message);
      }
    },

    async saveData() {
      this.saving = true;

      try {
        const response = await fetch("api/save_box1_okd_lhas_report.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            school_year: this.schoolYear,
            saved_by: localStorage.getItem("nurse_email") || "Clinic Nurse",
            report_data: {
              referralMechanisms: this.formData.referralMechanisms,
              lhasRows: this.lhasRows,
              lhasJHS: this.formData.lhasJHS,
              lhasSHS: this.formData.lhasSHS
            }
          })
        });

        const result = await response.json();

        if (result.success) {
          this.messageType = "success";
          this.message = result.message;
        } else {
          this.messageType = "danger";
          this.message = result.message || "Failed to save report.";
        }

      } catch (error) {
        this.messageType = "danger";
        this.message = "Error saving report: " + error.message;
      }

      this.saving = false;
    },

    printForm() {
      window.print();
    }
  }
}).mount("#app");
</script>
</body>
</html>