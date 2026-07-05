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
</head>
<body>
<div id="app" class="container-custom">

  <div class="header-box no-print">
    <div>
      <h1>📋 BOX 1 – Oplan Kalusugan sa DepEd (OKD) and LHAS</h1>
      <p style="margin:4px 0 0; opacity:0.9;">Junior High School & Senior High School (Read‑Only)</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <select v-model="selectedSchoolYear" class="form-select" style="max-width:180px;border-radius:12px;font-weight:700;">
        <option v-for="y in schoolYearOptions" :key="y" :value="y">{{ y }}</option>
      </select>

      <button class="btn-refresh" @click="openLoadModal" :disabled="loading">📂 Load from Saved</button>
      <button class="btn-refresh" @click="loadAggregatedData" :disabled="loading">🔄 Load from Records</button>
      <button class="btn-refresh" @click="saveData" :disabled="saving" style="background:#0f766e;color:#fff;">{{ saving ? 'Saving...' : '💾 Save' }}</button>
      <button class="btn-refresh" @click="printForm" style="background:#f0fdfa; color:#0f766e;">🖨️ Print</button>
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
  <div class="modal fade" id="loadModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title fw-bold">📂 Load Saved Report</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div v-if="savedReportsLoading" class="text-center py-4"><div class="spinner-border text-primary"></div> Loading...</div>
          <div v-else-if="savedReports.length === 0" class="alert alert-info">No saved reports found for this report type.</div>
          <div v-else class="table-responsive">
            <table class="table table-bordered">
              <thead><tr><th>School Year</th><th>Saved By</th><th>Saved At</th><th></th></tr></thead>
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
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
      </div>
    </div>
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
        referralMechanisms: [],
        lhasJHS: rows.map(() => blankLhasRow()),
        lhasSHS: rows.map(() => blankLhasRow())
      },

      savedReports: [],
      savedReportsLoading: false,
      loadModal: null
    };
  },

  mounted() {
    this.loadSchoolYearOptions();
    this.loadModal = new bootstrap.Modal(document.getElementById('loadModal'));
  },

  methods: {
    async saveData() {
      this.saving = true;
      try {
        const res = await fetch('api/save_report.php', {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            report_key: 'box1', school_year: this.selectedSchoolYear,
            saved_by: localStorage.getItem('local_full_name') || 'Clinic Nurse',
            report_data: this.formData
          })
        });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const result = await res.json();
        this.showMessage(result.success ? 'success' : 'danger', result.message || (result.success ? 'Saved.' : 'Save failed.'));
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
          if (data.active && this.schoolYearOptions.includes(data.active)) {
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
        const res = await fetch('api/get_report_list.php?report_key=box1&school_year=' + encodeURIComponent(this.selectedSchoolYear) + '&cache_buster=' + Date.now());
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
        const response = await fetch("api/get_box1_okd_lhas_report.php?school_year=" + encodeURIComponent(this.selectedSchoolYear) + "&cache_buster=" + Date.now());
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
</body>
</html>