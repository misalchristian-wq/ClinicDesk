<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | Consolidated Report</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    :root {
      --clinic-primary: #0f766e; --clinic-secondary: #14b8a6; --clinic-border: #d9eef0;
      --clinic-text: #16323f; --clinic-muted: #6b7d87; --clinic-shadow: 0 16px 38px rgba(15, 118, 110, 0.08);
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
    .btn-back, .btn-refresh, .btn-print {
      background: white; color: var(--clinic-primary); border: none; border-radius: 14px;
      padding: 12px 20px; font-weight: 700; text-decoration: none; display: inline-block;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1); cursor: pointer;
    }
    .btn-back:hover, .btn-refresh:hover, .btn-print:hover { background: #ecfeff; color: var(--clinic-primary); }
    .school-year-select { max-width: 200px; }
    .report-section {
      background: white;
      border: 1px solid var(--clinic-border);
      border-radius: var(--clinic-radius);
      padding: 24px;
      margin-bottom: 28px;
      box-shadow: var(--clinic-shadow);
      break-inside: avoid;
      page-break-inside: avoid;
    }
    .report-title {
      font-size: 1.4rem;
      font-weight: 800;
      color: var(--clinic-primary);
      border-left: 6px solid var(--clinic-secondary);
      padding-left: 16px;
      margin-bottom: 20px;
    }
    .report-meta {
      font-size: 0.8rem;
      color: var(--clinic-muted);
      margin-bottom: 16px;
      text-align: right;
    }
    .table-responsive {
      overflow-x: auto;
      border-radius: 16px;
      border: 1px solid var(--clinic-border);
      background: white;
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
    }
    .table td {
      vertical-align: middle;
      text-align: center;
      padding: 8px;
    }
    .alert { border-radius: 14px; margin-bottom: 16px; }
    @media print {
      body { background: white; }
      .no-print { display: none; }
      .report-section { border: 1px solid #ccc; box-shadow: none; page-break-inside: avoid; }
      .table th { background: #e9ecef !important; }
    }
  </style>
</head>
<body>
<div id="app" class="container-custom">
  <div class="header-box no-print">
    <div>
      <h1>📑 Consolidated Report</h1>
      <p style="margin:4px 0 0; opacity:0.9;">All saved reports for the selected school year – printable</p>
    </div>
    <div class="d-flex gap-2 align-items-center flex-wrap">
      <select v-model="selectedSchoolYear" class="form-select school-year-select" @change="loadReports">
        <option value="2021-2022">2021-2022</option>
        <option value="2022-2023">2022-2023</option>
        <option value="2023-2024">2023-2024</option>
        <option value="2025-2026">2025-2026</option>
        <option value="2027-2028">2027-2028</option>
      </select>
      <button class="btn-refresh" @click="loadReports" :disabled="loading">🔄 Refresh</button>
      <button class="btn-print" @click="printReport">🖨️ Print</button>
      <a href="nurse-dashboard.php" class="btn-back">← Back to Dashboard</a>
    </div>
  </div>

  <div v-if="loading" class="alert alert-info">Loading consolidated report...</div>
  <div v-if="error" class="alert alert-danger">{{ error }}</div>

  <!-- BOX 1 – OKD & LHAS -->
  <div v-if="reports.box1" class="report-section">
    <div class="report-meta">Saved by {{ reports.box1.saved_by }} on {{ reports.box1.saved_at }}</div>
    <h2 class="report-title">📋 BOX 1 – OKD and LHAS</h2>
    <div class="mb-3"><strong>Functional Referral Mechanisms:</strong> {{ (reports.box1.data.referralMechanisms || []).join(', ') || 'None' }}</div>
    <h5>Junior High School</h5>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead><tr><th>Screening Type</th><th>Masterlisted</th><th>Screened</th><th>Findings</th><th>Referred School</th><th>Referred LGU</th><th>Referred Private</th><th>Referred Others</th><th>Total Referred</th></tr></thead>
        <tbody>
          <tr v-for="(row, idx) in lhasRows" :key="idx">
            <td class="text-start fw-bold">{{ row }}</td>
            <td>{{ reports.box1.data.lhasJHS?.[idx]?.masterlisted || 0 }}</td>
            <td>{{ reports.box1.data.lhasJHS?.[idx]?.screened || 0 }}</td>
            <td>{{ reports.box1.data.lhasJHS?.[idx]?.findings || 0 }}</td>
            <td>{{ reports.box1.data.lhasJHS?.[idx]?.referredSchool || 0 }}</td>
            <td>{{ reports.box1.data.lhasJHS?.[idx]?.referredLGU || 0 }}</td>
            <td>{{ reports.box1.data.lhasJHS?.[idx]?.referredPrivate || 0 }}</td>
            <td>{{ reports.box1.data.lhasJHS?.[idx]?.referredOthers || 0 }}</td>
            <td class="total-cell">{{ lhasTotal(reports.box1.data.lhasJHS?.[idx]) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <h5>Senior High School</h5>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead><tr><th>Screening Type</th><th>Masterlisted</th><th>Screened</th><th>Findings</th><th>Referred School</th><th>Referred LGU</th><th>Referred Private</th><th>Referred Others</th><th>Total Referred</th></tr></thead>
        <tbody>
          <tr v-for="(row, idx) in lhasRows" :key="idx">
            <td class="text-start fw-bold">{{ row }}</td>
            <td>{{ reports.box1.data.lhasSHS?.[idx]?.masterlisted || 0 }}</td>
            <td>{{ reports.box1.data.lhasSHS?.[idx]?.screened || 0 }}</td>
            <td>{{ reports.box1.data.lhasSHS?.[idx]?.findings || 0 }}</td>
            <td>{{ reports.box1.data.lhasSHS?.[idx]?.referredSchool || 0 }}</td>
            <td>{{ reports.box1.data.lhasSHS?.[idx]?.referredLGU || 0 }}</td>
            <td>{{ reports.box1.data.lhasSHS?.[idx]?.referredPrivate || 0 }}</td>
            <td>{{ reports.box1.data.lhasSHS?.[idx]?.referredOthers || 0 }}</td>
            <td class="total-cell">{{ lhasTotal(reports.box1.data.lhasSHS?.[idx]) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- BOX 2 & 3 – School Clinic & Water Supply -->
  <div v-if="reports.box2_3" class="report-section">
    <div class="report-meta">Saved by {{ reports.box2_3.saved_by }} on {{ reports.box2_3.saved_at }}</div>
    <h2 class="report-title">🏥 BOX 2 & 3 – School Clinic & Water Supply</h2>
    <p><strong>School Clinic:</strong> {{ reports.box2_3.data.hasSchoolClinic || 'Not specified' }}</p>
    <p><strong>Visited by SDO:</strong> {{ reports.box2_3.data.visitedBySDO || 'Not specified' }} <span v-if="reports.box2_3.data.visitedBySDO === 'Yes'">({{ reports.box2_3.data.sdoVisits || 0 }} visits)</span></p>
    <p><strong>Clinic Equipment Status:</strong></p>
    <ul>
      <li v-for="(status, item) in reports.box2_3.data.clinicEquipment" :key="item">{{ item }}: {{ status || 'Not specified' }}</li>
    </ul>
    <p><strong>Water Sources:</strong> {{ (reports.box2_3.data.waterSources || []).join(', ') || 'None' }}</p>
    <p><strong>Water used for drinking:</strong> {{ reports.box2_3.data.waterForDrinking || 'Not specified' }}</p>
  </div>

  <!-- BOX 4 – Mental Health -->
  <div v-if="reports.box4" class="report-section">
    <div class="report-meta">Saved by {{ reports.box4.saved_by }} on {{ reports.box4.saved_at }}</div>
    <h2 class="report-title">🧠 BOX 4 – School Mental Health</h2>
    <p><strong>Guidance Office:</strong> {{ reports.box4.data.hasGuidanceOffice || 'Not specified' }}</p>
    <p><strong>Counseling (JHS):</strong> Male {{ reports.box4.data.counselingJHS?.male || 0 }}, Female {{ reports.box4.data.counselingJHS?.female || 0 }}</p>
    <p><strong>Counseling (SHS):</strong> Male {{ reports.box4.data.counselingSHS?.male || 0 }}, Female {{ reports.box4.data.counselingSHS?.female || 0 }}</p>
    <p><strong>Vulnerable groups JHS:</strong> Muslim {{ reports.box4.data.vulnerableJHS?.muslim || 0 }}, IP {{ reports.box4.data.vulnerableJHS?.ip || 0 }}, LWD {{ reports.box4.data.vulnerableJHS?.lwd || 0 }}</p>
    <p><strong>Vulnerable groups SHS:</strong> Muslim {{ reports.box4.data.vulnerableSHS?.muslim || 0 }}, IP {{ reports.box4.data.vulnerableSHS?.ip || 0 }}, LWD {{ reports.box4.data.vulnerableSHS?.lwd || 0 }}</p>
    <p><strong>Mental Health Training:</strong> {{ reports.box4.data.hasMentalHealthTraining || 'No' }}</p>
    <div v-if="reports.box4.data.hasMentalHealthTraining === 'Yes'">
      <p><strong>Trained teachers per topic:</strong></p>
      <ul>
        <li v-for="(count, topic) in reports.box4.data.mentalHealthTraining" :key="topic">{{ topic }}: {{ count || 0 }}</li>
      </ul>
    </div>
  </div>

  <!-- BOX 5 & 6 – ARH & Tobacco -->
  <div v-if="reports.box5_6" class="report-section">
    <div class="report-meta">Saved by {{ reports.box5_6.saved_by }} on {{ reports.box5_6.saved_at }}</div>
    <h2 class="report-title">👥 BOX 5 & 6 – ARH & Tobacco Control</h2>
    <p><strong>Pregnant Learners (In School):</strong> G7={{ reports.box5_6.data.pregnantLearners?.['In School']?.g7 || 0 }}, G8={{ reports.box5_6.data.pregnantLearners?.['In School']?.g8 || 0 }}, G9={{ reports.box5_6.data.pregnantLearners?.['In School']?.g9 || 0 }}, G10={{ reports.box5_6.data.pregnantLearners?.['In School']?.g10 || 0 }}, G11={{ reports.box5_6.data.pregnantLearners?.['In School']?.g11 || 0 }}, G12={{ reports.box5_6.data.pregnantLearners?.['In School']?.g12 || 0 }}</p>
    <p><strong>Pregnant Learners (ADM):</strong> G7={{ reports.box5_6.data.pregnantLearners?.['On Alternative Delivery Mode (ADM)']?.g7 || 0 }}, ...</p>
    <p><strong>Support Center:</strong> {{ reports.box5_6.data.hasSupportCenter || 'Not specified' }}</p>
    <p><strong>Peer Educators:</strong> {{ reports.box5_6.data.peerEducators || 0 }}</p>
    <p><strong>IEC Materials:</strong> {{ (reports.box5_6.data.iecMaterials || []).join(', ') || 'None' }}</p>
    <p><strong>Stores Selling:</strong> {{ (reports.box5_6.data.storesSelling || []).join(', ') || 'None' }}</p>
    <p><strong>Tobacco Violations – Brought:</strong> JHS {{ reports.box5_6.data.tobaccoViolations?.jhs?.brought || 0 }}, SHS {{ reports.box5_6.data.tobaccoViolations?.shs?.brought || 0 }}</p>
    <p><strong>Referred to care:</strong> JHS {{ reports.box5_6.data.tobaccoViolations?.jhs?.referred || 0 }}, SHS {{ reports.box5_6.data.tobaccoViolations?.shs?.referred || 0 }}</p>
  </div>

  <!-- BOX 8 & 9 – Food Handling & Feeding -->
  <div v-if="reports.box8_9" class="report-section">
    <div class="report-meta">Saved by {{ reports.box8_9.saved_by }} on {{ reports.box8_9.saved_at }}</div>
    <h2 class="report-title">🍽️ BOX 8 & 9 – Food Handling & Feeding</h2>
    <p><strong>Canteen:</strong> {{ reports.box8_9.data.hasCanteen || 'Not specified' }}</p>
    <div v-if="reports.box8_9.data.hasCanteen === 'Yes'">
      <p><strong>Managed by:</strong> {{ reports.box8_9.data.canteenManager || 'Not specified' }} <span v-if="reports.box8_9.data.canteenManager === 'Others'">({{ reports.box8_9.data.canteenManagerOther }})</span></p>
      <p><strong>Sanitary Permit:</strong> {{ reports.box8_9.data.sanitaryPermit || 'Not specified' }}</p>
      <p><strong>Health Certificates:</strong> {{ reports.box8_9.data.healthCertificates || 'Not specified' }}</p>
    </div>
    <p><strong>Kitchen:</strong> {{ reports.box8_9.data.hasKitchen || 'Not specified' }}</p>
    <p><strong>Feeding Fund Sources:</strong> {{ (reports.box8_9.data.feedingFundSources || []).join(', ') || 'None' }}</p>
    <p><strong>Agriculture/Fishery Resources:</strong> {{ (reports.box8_9.data.agriResources || []).join(', ') || 'None' }}</p>
  </div>

  <!-- BOX 10 & 11 – Waste Management & Menstrual Hygiene -->
  <div v-if="reports.box10_11" class="report-section">
    <div class="report-meta">Saved by {{ reports.box10_11.saved_by }} on {{ reports.box10_11.saved_at }}</div>
    <h2 class="report-title">♻️ BOX 10 & 11 – Waste Management & Menstrual Hygiene</h2>
    <p><strong>SWM Implementation:</strong> {{ (reports.box10_11.data.swmImplementation || []).join(', ') || 'None' }}</p>
    <p><strong>Stakeholders:</strong> {{ (reports.box10_11.data.stakeholders || []).join(', ') || 'None' }}</p>
    <p><strong>Sanitary Pad Locations:</strong> {{ (reports.box10_11.data.sanitaryPadLocations || []).join(', ') || 'None' }} <span v-if="reports.box10_11.data.sanitaryPadOther">({{ reports.box10_11.data.sanitaryPadOther }})</span></p>
  </div>

  <!-- TABLE 1-A – Immunization & Nutrition -->
  <div v-if="reports.table1_a" class="report-section">
    <div class="report-meta">Saved by {{ reports.table1_a.saved_by }} on {{ reports.table1_a.saved_at }}</div>
    <h2 class="report-title">🩺 TABLE 1-A – Immunization & Nutritional Status</h2>
    <p><strong>Immunization (Td):</strong> Male {{ reports.table1_a.data.vaccineTD?.male || 0 }}, Female {{ reports.table1_a.data.vaccineTD?.female || 0 }}, IP {{ reports.table1_a.data.vaccineTD?.ip || 0 }}</p>
    <p><strong>Immunization (HPV):</strong> Male {{ reports.table1_a.data.vaccineHPV?.male || 0 }}, Female {{ reports.table1_a.data.vaccineHPV?.female || 0 }}, IP {{ reports.table1_a.data.vaccineHPV?.ip || 0 }}</p>
    <h5>Nutritional Status JHS</h5>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead><tr><th>Status</th><th>G7 M</th><th>G7 F</th><th>G8 M</th><th>G8 F</th><th>G9 M</th><th>G9 F</th><th>G10 M</th><th>G10 F</th></tr></thead>
        <tbody>
          <tr v-for="status in ['Normal','Obese','Overweight','Severely Wasted','Wasted']" :key="status">
            <td class="fw-bold">{{ status }}</td>
            <td>{{ reports.table1_a.data.nutritionJHS?.[status]?.g7Male || 0 }}</td>
            <td>{{ reports.table1_a.data.nutritionJHS?.[status]?.g7Female || 0 }}</td>
            <td>{{ reports.table1_a.data.nutritionJHS?.[status]?.g8Male || 0 }}</td>
            <td>{{ reports.table1_a.data.nutritionJHS?.[status]?.g8Female || 0 }}</td>
            <td>{{ reports.table1_a.data.nutritionJHS?.[status]?.g9Male || 0 }}</td>
            <td>{{ reports.table1_a.data.nutritionJHS?.[status]?.g9Female || 0 }}</td>
            <td>{{ reports.table1_a.data.nutritionJHS?.[status]?.g10Male || 0 }}</td>
            <td>{{ reports.table1_a.data.nutritionJHS?.[status]?.g10Female || 0 }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <h5>Nutritional Status SHS</h5>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead><tr><th>Status</th><th>G11 M</th><th>G11 F</th><th>G12 M</th><th>G12 F</th></tr></thead>
        <tbody>
          <tr v-for="status in ['Normal','Obese','Overweight','Severely Wasted','Wasted']" :key="status">
            <td class="fw-bold">{{ status }}</td>
            <td>{{ reports.table1_a.data.nutritionSHS?.[status]?.g11Male || 0 }}</td>
            <td>{{ reports.table1_a.data.nutritionSHS?.[status]?.g11Female || 0 }}</td>
            <td>{{ reports.table1_a.data.nutritionSHS?.[status]?.g12Male || 0 }}</td>
            <td>{{ reports.table1_a.data.nutritionSHS?.[status]?.g12Female || 0 }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- TABLE 1-B – Deworming & WIFA -->
  <div v-if="reports.table1_b" class="report-section">
    <div class="report-meta">Saved by {{ reports.table1_b.saved_by }} on {{ reports.table1_b.saved_at }}</div>
    <h2 class="report-title">💊 TABLE 1-B – Deworming & WIFA</h2>
    <h5>Dewormed Learners</h5>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead><tr><th>Grade</th><th>SBFP M</th><th>SBFP F</th><th>Other M</th><th>Other F</th></tr></thead>
        <tbody>
          <tr v-for="g in [7,8,9,10,11,12]" :key="g">
            <td class="fw-bold">Grade {{ g }}</td>
            <td>{{ reports.table1_b.data.dewormed?.[g]?.sbfpMale || 0 }}</td>
            <td>{{ reports.table1_b.data.dewormed?.[g]?.sbfpFemale || 0 }}</td>
            <td>{{ reports.table1_b.data.dewormed?.[g]?.otherMale || 0 }}</td>
            <td>{{ reports.table1_b.data.dewormed?.[g]?.otherFemale || 0 }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <h5>WIFA (Female)</h5>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead><tr><th>Grade</th><th>Jul–Sep</th><th>Jan–Mar</th></tr></thead>
        <tbody>
          <tr v-for="g in [7,8,9,10,11,12]" :key="g">
            <td class="fw-bold">Grade {{ g }}</td>
            <td>{{ reports.table1_b.data.wifa?.[g]?.julSep || 0 }}</td>
            <td>{{ reports.table1_b.data.wifa?.[g]?.janMar || 0 }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div v-if="!loading && Object.keys(reports).length === 0 && !error" class="alert alert-warning">
    No saved reports found for {{ selectedSchoolYear }}.
  </div>

</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script>
const { createApp } = Vue;

createApp({
  data() {
    return {
      selectedSchoolYear: "2021-2022",
      reports: {},
      loading: false,
      error: null,
      lhasRows: ["Nutritional Assessment","Health History","Vision Screening","Hearing Screening","Oral Health","CARS","Rapid HEEADSSS"]
    };
  },
  mounted() {
    this.loadReports();
  },
  methods: {
    lhasTotal(row) {
      if (!row) return 0;
      return (row.referredSchool||0)+(row.referredLGU||0)+(row.referredPrivate||0)+(row.referredOthers||0);
    },
    async loadReports() {
      this.loading = true;
      this.error = null;
      try {
        const res = await fetch(`api/get_all_reports.php?school_year=${encodeURIComponent(this.selectedSchoolYear)}&cache_buster=${Date.now()}`);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        if (data.success) {
          this.reports = data.reports;
        } else {
          this.error = data.message || "Failed to load reports";
        }
      } catch (e) {
        this.error = "Error loading reports: " + e.message;
      }
      this.loading = false;
    },
    printReport() {
      window.print();
    }
  }
}).mount("#app");
</script>
</body>
</html>