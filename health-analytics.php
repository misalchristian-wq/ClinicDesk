<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ClinicDesk | Health Analytics</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    :root {
      --clinic-primary: #0f766e; --clinic-secondary: #14b8a6; --clinic-accent: #0ea5e9;
      --clinic-card: #ffffff; --clinic-border: #d9eef0; --clinic-text: #16323f; --clinic-muted: #6b7d87;
      --clinic-shadow: 0 12px 32px rgba(15, 118, 110, 0.1);
    }
    * { box-sizing: border-box; }
    body {
      margin: 0; min-height: 100vh; color: var(--clinic-text);
      background: linear-gradient(135deg, #eef8fb, #f8fcfd);
      font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
    }
    .wrap { max-width: 1200px; margin: 24px auto; padding: 20px; }
    .header-box {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: #fff; padding: 28px 32px; border-radius: 26px; margin-bottom: 24px;
      box-shadow: 0 16px 38px rgba(15,118,110,0.22);
      display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;
    }
    .header-box h1 { font-size: 1.9rem; font-weight: 800; margin: 0 0 4px; }
    .header-box p { margin: 0; opacity: 0.92; font-size: 0.92rem; }
    .btn-back, .btn-print {
      background: #fff; color: var(--clinic-primary); border: none; border-radius: 13px;
      padding: 10px 18px; font-weight: 700; text-decoration: none; box-shadow: 0 8px 18px rgba(0,0,0,0.12);
    }
    .btn-print { background: #ecfeff; }
    .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
    .summary-card {
      background: var(--clinic-card); border: 1px solid var(--clinic-border);
      border-radius: 18px; padding: 18px 20px; box-shadow: var(--clinic-shadow);
    }
    .summary-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.6px; color: var(--clinic-muted); font-weight: 700; }
    .summary-value { font-size: 2rem; font-weight: 800; color: var(--clinic-primary); margin: 4px 0 0; }
    .summary-helper { font-size: 0.78rem; color: var(--clinic-muted); margin: 0; }
    .card {
      background: var(--clinic-card); border: 1px solid var(--clinic-border);
      border-radius: 20px; box-shadow: var(--clinic-shadow); padding: 24px; margin-bottom: 24px;
    }
    .card h4 { color: var(--clinic-primary); font-weight: 800; font-size: 1.15rem; margin-bottom: 16px; }
    .chart-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 24px; }
    .chart-box { height: 260px; }
    table th { background: #e8f5f6; color: #1e3b44; font-weight: 700; white-space: nowrap; }
    .badge { border-radius: 30px; padding: 5px 11px; font-weight: 600; font-size: 0.72rem; }
    .section-title { font-weight: 800; color: var(--clinic-primary); margin: 20px 0 10px; font-size: 1.05rem; }
    @media (max-width: 992px) { .summary-grid, .chart-grid { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 576px) { .summary-grid, .chart-grid { grid-template-columns: 1fr; } }

    /* Print / PDF */
    @media print {
      body { background: #fff; }
      .no-print { display: none !important; }
      .wrap { margin: 0; max-width: 100%; padding: 0; }
      .card, .summary-card { box-shadow: none; border: 1px solid #ccc; break-inside: avoid; }
      .header-box { background: #0f766e !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
  </style>
</head>
<body>
  <div id="app" class="wrap">

    <div class="header-box">
      <div>
        <h1>Student Health Analytics</h1>
        <p>Consolidated risk, BMI, and height-for-age summary grouped by section. School Year: <strong>{{ activeSchoolYear || "all" }}</strong></p>
      </div>
      <div class="no-print d-flex gap-2">
        <button class="btn-print" @click="exportPDF">Export PDF</button>
        <a href="nurse-dashboard.php" class="btn-back">Back to Dashboard</a>
      </div>
    </div>

    <div v-if="loading" class="card">Loading analytics...</div>

    <div v-else>
      <!-- Top-line summary -->
      <div class="summary-grid">
        <div class="summary-card">
          <div class="summary-label">Total Students</div>
          <p class="summary-value">{{ records.length }}</p>
          <p class="summary-helper">Approved + manual records</p>
        </div>
        <div class="summary-card">
          <div class="summary-label">High Risk</div>
          <p class="summary-value">{{ riskTally.High }}</p>
          <p class="summary-helper">Needs immediate attention</p>
        </div>
        <div class="summary-card">
          <div class="summary-label">Moderate Risk</div>
          <p class="summary-value">{{ riskTally.Moderate }}</p>
          <p class="summary-helper">Regular monitoring</p>
        </div>
        <div class="summary-card">
          <div class="summary-label">Low Risk</div>
          <p class="summary-value">{{ riskTally.Low }}</p>
          <p class="summary-helper">Routine monitoring</p>
        </div>
      </div>

      <!-- Charts -->
      <div class="chart-grid">
        <div class="card mb-0">
          <h4>Risk Distribution</h4>
          <div class="chart-box"><canvas id="riskChart"></canvas></div>
        </div>
        <div class="card mb-0">
          <h4>BMI Category Summary</h4>
          <div class="chart-box"><canvas id="bmiChart"></canvas></div>
        </div>
        <div class="card mb-0">
          <h4>Height-for-Age Summary</h4>
          <div class="chart-box"><canvas id="hfaChart"></canvas></div>
        </div>
      </div>

      <!-- Section / grade breakdown -->
      <div class="card">
        <h4>Breakdown by Section</h4>
        <p class="summary-helper mb-3">Risk and nutritional status counts per grade level and section.</p>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th>Grade &amp; Section</th>
                <th>Total</th>
                <th>High</th>
                <th>Moderate</th>
                <th>Low</th>
                <th>Sev. Wasted</th>
                <th>Wasted</th>
                <th>Normal BMI</th>
                <th>Overweight</th>
                <th>Obese</th>
                <th>Stunted</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="grp in sectionBreakdown" :key="grp.key">
                <td class="fw-semibold">{{ grp.label }}</td>
                <td>{{ grp.total }}</td>
                <td><span class="badge bg-danger" v-if="grp.high">{{ grp.high }}</span><span v-else>0</span></td>
                <td><span class="badge bg-warning text-dark" v-if="grp.moderate">{{ grp.moderate }}</span><span v-else>0</span></td>
                <td><span class="badge bg-success" v-if="grp.low">{{ grp.low }}</span><span v-else>0</span></td>
                <td>{{ grp.sevWasted }}</td>
                <td>{{ grp.wasted }}</td>
                <td>{{ grp.normalBmi }}</td>
                <td>{{ grp.overweight }}</td>
                <td>{{ grp.obese }}</td>
                <td>{{ grp.stunted }}</td>
              </tr>
              <tr v-if="sectionBreakdown.length === 0">
                <td colspan="11" class="text-center text-muted">No records to summarize.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- High-risk student list -->
      <div class="card">
        <h4>High-Risk Students</h4>
        <p class="summary-helper mb-3">Students flagged as high risk, for priority clinic follow-up.</p>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th>Learner</th>
                <th>Grade &amp; Section</th>
                <th>Sex</th>
                <th>BMI</th>
                <th>BMI Category</th>
                <th>Height-for-Age</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in highRiskStudents" :key="r.record_id">
                <td class="fw-semibold">{{ r.learner_name }}</td>
                <td>{{ (r.grade_level || "-") + " - " + (r.section || "-") }}</td>
                <td>{{ r.sex }}</td>
                <td>{{ r.bmi }}</td>
                <td><span class="badge bg-danger">{{ r.bmi_category || "-" }}</span></td>
                <td>{{ heightForAge(r) }}</td>
              </tr>
              <tr v-if="highRiskStudents.length === 0">
                <td colspan="6" class="text-center text-muted">No high-risk students.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
  <script>
    const { createApp } = Vue;
    createApp({
      data() {
        return {
          records: [], loading: true, activeSchoolYear: "",
          riskChart: null, bmiChart: null, hfaChart: null
        };
      },
      computed: {
        riskTally() {
          const t = { High: 0, Moderate: 0, Low: 0, "For Review": 0 };
          this.records.forEach(r => { t[this.riskLevel(r)] = (t[this.riskLevel(r)] || 0) + 1; });
          return t;
        },
        bmiTally() {
          const t = { "Severely Wasted": 0, "Wasted": 0, "Normal": 0, "Overweight": 0, "Obese": 0 };
          this.records.forEach(r => {
            const c = String(r.bmi_category || "").toLowerCase();
            if (c.includes("severely") && c.includes("wast")) t["Severely Wasted"]++;
            else if (c.includes("wast") || c.includes("underweight")) t["Wasted"]++;
            else if (c.includes("obese")) t["Obese"]++;
            else if (c.includes("overweight")) t["Overweight"]++;
            else if (c.includes("normal")) t["Normal"]++;
          });
          return t;
        },
        hfaTally() {
          const t = { "Severely Stunted": 0, "Stunted": 0, "Normal": 0, "Tall": 0 };
          this.records.forEach(r => {
            const h = String(this.heightForAge(r) || "").toLowerCase();
            if (h.includes("severely")) t["Severely Stunted"]++;
            else if (h.includes("stunted")) t["Stunted"]++;
            else if (h.includes("tall")) t["Tall"]++;
            else if (h.includes("normal")) t["Normal"]++;
          });
          return t;
        },
        highRiskStudents() {
          return this.records.filter(r => this.riskLevel(r) === "High");
        },
        sectionBreakdown() {
          const groups = {};
          this.records.forEach(r => {
            const grade = r.grade_level || "-";
            const section = r.section || "-";
            const key = grade + "||" + section;
            if (!groups[key]) {
              groups[key] = {
                key, label: grade + " - " + section, total: 0,
                high: 0, moderate: 0, low: 0,
                sevWasted: 0, wasted: 0, normalBmi: 0, overweight: 0, obese: 0, stunted: 0
              };
            }
            const g = groups[key];
            g.total++;
            const risk = this.riskLevel(r);
            if (risk === "High") g.high++;
            else if (risk === "Moderate") g.moderate++;
            else if (risk === "Low") g.low++;

            const c = String(r.bmi_category || "").toLowerCase();
            if (c.includes("severely") && c.includes("wast")) g.sevWasted++;
            else if (c.includes("wast") || c.includes("underweight")) g.wasted++;
            else if (c.includes("obese")) g.obese++;
            else if (c.includes("overweight")) g.overweight++;
            else if (c.includes("normal")) g.normalBmi++;

            const h = String(this.heightForAge(r) || "").toLowerCase();
            if (h.includes("stunted")) g.stunted++;
          });
          return Object.values(groups).sort((a, b) => a.label.localeCompare(b.label));
        }
      },
      mounted() {
        const role = localStorage.getItem("active_role");
        if (role && role !== "Clinic Nurse") {
          // window.location.href = "login.php";
        }
        this.loadActiveSchoolYear();
        this.loadRecords();
      },
      methods: {
        heightForAge(r) {
          return r.height_for_age_status || r.height_for_age || r.hfa_status || "-";
        },
        riskLevel(r) {
          if (r.risk_level) return r.risk_level;
          const c = String(r.bmi_category || "").toLowerCase();
          const h = String(this.heightForAge(r) || "").toLowerCase();
          if (c.includes("severely") || c.includes("obese") || h.includes("severely")) return "High";
          if (c.includes("wast") || c.includes("underweight") || c.includes("overweight") || h.includes("stunted")) return "Moderate";
          if (c.includes("normal") && (h.includes("normal") || h === "-")) return "Low";
          return "For Review";
        },
        async loadActiveSchoolYear() {
          try {
            const res = await fetch("api/get_school_years.php?t=" + Date.now());
            const data = await res.json();
            if (data.success && data.active) this.activeSchoolYear = data.active;
          } catch (e) { /* ignore */ }
        },
        async loadRecords() {
          this.loading = true;
          try {
            const res = await fetch("api/get_student_records.php?cache_buster=" + Date.now());
            const data = await res.json();
            if (data.success) this.records = data.records || [];
          } catch (e) {
            console.warn("Could not load records", e);
          }
          this.loading = false;
          this.$nextTick(() => this.renderCharts());
        },
        renderCharts() {
          this.renderRisk();
          this.renderBmi();
          this.renderHfa();
        },
        renderRisk() {
          const ctx = document.getElementById("riskChart");
          if (!ctx) return;
          if (this.riskChart) this.riskChart.destroy();
          const t = this.riskTally;
          this.riskChart = new Chart(ctx, {
            type: "doughnut",
            data: {
              labels: ["High", "Moderate", "Low", "For Review"],
              datasets: [{ data: [t.High, t.Moderate, t.Low, t["For Review"]],
                backgroundColor: ["#dc2626", "#f59e0b", "#16a34a", "#0ea5e9"], borderWidth: 0 }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: "62%", plugins: { legend: { position: "bottom" } } }
          });
        },
        renderBmi() {
          const ctx = document.getElementById("bmiChart");
          if (!ctx) return;
          if (this.bmiChart) this.bmiChart.destroy();
          const t = this.bmiTally;
          this.bmiChart = new Chart(ctx, {
            type: "bar",
            data: {
              labels: ["Sev. Wasted", "Wasted", "Normal", "Overweight", "Obese"],
              datasets: [{ label: "Students",
                data: [t["Severely Wasted"], t["Wasted"], t["Normal"], t["Overweight"], t["Obese"]],
                backgroundColor: ["#dc2626", "#f59e0b", "#16a34a", "#f97316", "#991b1b"], borderRadius: 8 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
              scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
          });
        },
        renderHfa() {
          const ctx = document.getElementById("hfaChart");
          if (!ctx) return;
          if (this.hfaChart) this.hfaChart.destroy();
          const t = this.hfaTally;
          this.hfaChart = new Chart(ctx, {
            type: "bar",
            data: {
              labels: ["Sev. Stunted", "Stunted", "Normal", "Tall"],
              datasets: [{ label: "Students",
                data: [t["Severely Stunted"], t["Stunted"], t["Normal"], t["Tall"]],
                backgroundColor: ["#dc2626", "#f59e0b", "#16a34a", "#0ea5e9"], borderRadius: 8 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
              scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
          });
        },
        exportPDF() {
          // Uses the browser's print dialog; the user picks "Save as PDF".
          window.print();
        }
      }
    }).mount("#app");
  </script>
</body>
</html>