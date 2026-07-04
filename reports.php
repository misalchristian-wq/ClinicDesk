<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | Nurse Report Center</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    /* KEEP YOUR EXISTING STYLES – unchanged */
    :root {
      --clinic-primary: #0f766e;
      --clinic-secondary: #14b8a6;
      --clinic-accent: #0ea5e9;
      --clinic-bg: #f5fafb;
      --clinic-soft: #ecfeff;
      --clinic-light: #f0fdfa;
      --clinic-card: #ffffff;
      --clinic-border: #d9eef0;
      --clinic-text: #16323f;
      --clinic-muted: #6b7d87;
      --clinic-shadow: 0 16px 38px rgba(15, 118, 110, 0.08);
      --clinic-radius: 22px;
    }
    * { box-sizing: border-box; }
    body { min-height: 100vh; margin: 0; background: #f5fafb; font-family: 'Plus Jakarta Sans', Arial, sans-serif; color: var(--clinic-text); overflow-x: hidden; }
    .report-shell { min-height: 100vh; }
    .sidebar { background: #ffffff; border-right: 1px solid var(--clinic-border); padding: 26px 22px; position: sticky; top: 0; height: 100vh; overflow-y: auto; }

    /* Unified full-width header (matches other ClinicDesk pages) */
    .page-header {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: #fff; padding: 26px 32px; border-radius: 26px; margin-bottom: 24px;
      box-shadow: 0 16px 38px rgba(15,118,110,0.22);
      display: flex; justify-content: space-between; align-items: center; gap: 18px; flex-wrap: wrap;
      position: relative; overflow: hidden;
    }
    .page-header::before { content:""; position:absolute; top:-70px; right:-50px; width:200px; height:200px; background:rgba(255,255,255,0.10); border-radius:50%; }
    .page-header-left { display: flex; align-items: center; gap: 16px; position: relative; z-index: 2; }
    .page-header-icon { width: 56px; height: 56px; border-radius: 18px; background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.28); display: flex; align-items: center; justify-content: center; font-size: 26px; flex-shrink: 0; }
    .page-header-kicker { font-size: 12px; font-weight: 800; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 3px; }
    .page-header-title { font-size: 26px; font-weight: 800; margin: 0 0 3px; }
    .page-header-sub { font-size: 13px; opacity: 0.92; margin: 0; }
    .page-header-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; position: relative; z-index: 2; }
    .header-prepared { text-align: right; margin-right: 6px; }
    .header-prepared-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.6px; opacity: 0.85; font-weight: 700; }
    .header-prepared-name { font-size: 15px; font-weight: 800; }
    .btn-header { background: #fff; color: var(--clinic-primary); border: none; border-radius: 13px; padding: 9px 15px; font-weight: 800; text-decoration: none; cursor: pointer; font-size: 13px; box-shadow: 0 8px 18px rgba(0,0,0,0.12); }
    .btn-header:hover { background: #ecfeff; color: #0f5b55; }
    .btn-header.soft { background: rgba(255,255,255,0.16); color: #fff; border: 1px solid rgba(255,255,255,0.35); box-shadow: none; }
    .btn-header.soft:hover { background: rgba(255,255,255,0.26); color: #fff; }
    .brand-row { display: flex; align-items: center; gap: 13px; margin-bottom: 26px; }
    .brand-icon { width: 54px; height: 54px; border-radius: 18px; background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary)); color: white; display: flex; align-items: center; justify-content: center; font-size: 26px; box-shadow: 0 14px 28px rgba(15, 118, 110, 0.18); flex-shrink: 0; }
    .brand-title { font-size: 22px; font-weight: 900; color: var(--clinic-primary); line-height: 1; }
    .brand-subtitle { font-size: 12px; color: var(--clinic-muted); margin-top: 4px; font-weight: 700; }
    .side-card { background: linear-gradient(135deg, #ecfeff, #f0fdfa); border: 1px solid var(--clinic-border); border-radius: 22px; padding: 18px; margin-bottom: 22px; }
    .side-label { font-size: 12px; text-transform: uppercase; letter-spacing: 0.8px; color: var(--clinic-muted); font-weight: 900; margin-bottom: 12px; }
    .prepared-name { font-size: 17px; font-weight: 900; color: var(--clinic-text); margin-bottom: 4px; word-break: break-word; }
    .prepared-role { font-size: 13px; color: var(--clinic-muted); font-weight: 700; margin-bottom: 0; }
    .form-label { color: #24404d; font-weight: 800; font-size: 13px; margin-bottom: 7px; }
    .form-control, .form-select { border-radius: 14px; border: 1px solid var(--clinic-border); padding: 11px 13px; font-size: 14px; background: white; color: var(--clinic-text); }
    .form-control:focus, .form-select:focus { border-color: var(--clinic-secondary); box-shadow: 0 0 0 0.2rem rgba(20, 184, 166, 0.16); }
    .filter-group { display: grid; gap: 14px; margin-bottom: 18px; }
    .btn-main { background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary)); color: white; border: none; border-radius: 15px; padding: 11px 16px; font-weight: 900; text-decoration: none; text-align: center; box-shadow: 0 12px 24px rgba(15, 118, 110, 0.16); display: inline-block; cursor: pointer; }
    .btn-main:hover { color: white; transform: translateY(-1px); }
    .btn-soft { background: white; color: var(--clinic-primary); border: 1px solid var(--clinic-border); border-radius: 15px; padding: 11px 16px; font-weight: 900; text-decoration: none; text-align: center; display: inline-block; cursor: pointer; }
    .btn-soft:hover { background: var(--clinic-soft); color: var(--clinic-primary); }
    .side-actions { display: grid; gap: 10px; }
    .main-area { padding: 24px; min-width: 0; max-width: 1400px; margin: 0 auto; }
    .top-toolbar { background: white; border: 1px solid var(--clinic-border); border-radius: 24px; padding: 18px 20px; box-shadow: 0 10px 26px rgba(15, 118, 110, 0.06); display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 24px; }
    .toolbar-kicker { font-size: 12px; font-weight: 900; color: var(--clinic-secondary); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 4px; }
    .toolbar-title { font-size: 28px; font-weight: 900; color: var(--clinic-text); margin-bottom: 2px; }
    .toolbar-subtitle { color: var(--clinic-muted); font-size: 14px; margin-bottom: 0; }
    .date-chip { background: var(--clinic-soft); border: 1px solid var(--clinic-border); color: var(--clinic-primary); border-radius: 999px; padding: 10px 14px; font-weight: 900; font-size: 13px; white-space: nowrap; }
    .report-center-header { background: linear-gradient(135deg, #ffffff, #f0fdfa); border: 1px solid var(--clinic-border); border-radius: 26px; padding: 26px; box-shadow: var(--clinic-shadow); margin-bottom: 24px; }
    .report-center-kicker { color: var(--clinic-secondary); font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 6px; }
    .report-center-title { color: var(--clinic-primary); font-size: 30px; font-weight: 900; margin-bottom: 8px; }
    .report-center-text { color: var(--clinic-muted); font-size: 14px; line-height: 1.6; margin-bottom: 0; }
    .report-module-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin-bottom: 24px; }
    .report-module-card { background: #ffffff; border: 1px solid var(--clinic-border); border-radius: 24px; padding: 22px; box-shadow: var(--clinic-shadow); position: relative; overflow: hidden; min-height: 245px; display: flex; flex-direction: column; transition: 0.2s ease; }
    .report-module-card:hover { transform: translateY(-4px); box-shadow: 0 20px 42px rgba(15, 118, 110, 0.12); }
    .report-module-card::after { content: ""; position: absolute; top: -45px; right: -45px; width: 125px; height: 125px; background: rgba(20, 184, 166, 0.08); border-radius: 50%; }
    .report-module-icon { width: 56px; height: 56px; border-radius: 18px; background: var(--clinic-soft); color: var(--clinic-primary); display: flex; align-items: center; justify-content: center; font-size: 26px; margin-bottom: 15px; position: relative; z-index: 2; }
    .report-module-title { font-size: 18px; font-weight: 900; color: var(--clinic-text); margin-bottom: 8px; position: relative; z-index: 2; }
    .report-module-desc { color: var(--clinic-muted); font-size: 13px; line-height: 1.6; margin-bottom: 18px; position: relative; z-index: 2; }
    .report-module-meta { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 18px; position: relative; z-index: 2; }
    .module-chip { background: #f8fcfd; border: 1px solid var(--clinic-border); color: var(--clinic-primary); border-radius: 999px; padding: 6px 10px; font-size: 11px; font-weight: 900; }
    .report-module-actions { display: grid; gap: 9px; margin-top: auto; position: relative; z-index: 2; }
    .report-paper { background: white; border: 1px solid var(--clinic-border); border-radius: 26px; box-shadow: var(--clinic-shadow); padding: 34px; margin-bottom: 24px; }
    .report-letterhead { display: grid; grid-template-columns: 1fr auto; gap: 16px; align-items: start; border-bottom: 2px solid #d9eef0; padding-bottom: 22px; margin-bottom: 24px; }
    .letterhead-brand { display: flex; gap: 14px; align-items: center; }
    .letterhead-logo { width: 64px; height: 64px; border-radius: 20px; background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary)); color: white; display: flex; align-items: center; justify-content: center; font-size: 30px; flex-shrink: 0; }
    .report-title { font-size: 29px; font-weight: 900; color: var(--clinic-primary); margin-bottom: 4px; }
    .report-subtitle { color: var(--clinic-muted); margin-bottom: 0; font-size: 14px; line-height: 1.5; }
    .report-meta { text-align: right; min-width: 230px; }
    .meta-line { font-size: 13px; color: var(--clinic-muted); margin-bottom: 5px; }
    .meta-line strong { color: var(--clinic-text); }
    .summary-strip { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 24px; }
    .summary-box { border: 1px solid var(--clinic-border); background: #fbfefe; border-radius: 18px; padding: 16px; }
    .summary-label { font-size: 12px; text-transform: uppercase; letter-spacing: 0.6px; color: var(--clinic-muted); font-weight: 900; margin-bottom: 6px; }
    .summary-value { font-size: 28px; font-weight: 900; color: var(--clinic-primary); margin-bottom: 0; }
    .summary-helper { color: var(--clinic-muted); font-size: 12px; margin-top: 3px; margin-bottom: 0; }
    .section-title { font-size: 21px; font-weight: 900; color: var(--clinic-text); margin-bottom: 5px; }
    .section-desc { color: var(--clinic-muted); font-size: 14px; margin-bottom: 0; }
    .report-section { margin-bottom: 26px; }
    .chart-layout { display: grid; grid-template-columns: 1fr; gap: 18px; margin-top: 16px; margin-bottom: 24px; max-width: 500px; margin-left: auto; margin-right: auto; }
    .chart-card { border: 1px solid var(--clinic-border); border-radius: 20px; padding: 18px; background: #fbfefe; }
    .chart-title { font-size: 16px; font-weight: 900; color: var(--clinic-primary); margin-bottom: 12px; text-align: center; }
    .chart-box { height: 300px; }
    .table-responsive { width: 100%; overflow-x: auto; border-radius: 18px; border: 1px solid var(--clinic-border); background: white; margin-top: 16px; }
    .table { margin-bottom: 0; }
    .table th { background: #f1fbfb; color: #24404d; font-weight: 900; white-space: nowrap; border-bottom: 1px solid var(--clinic-border); font-size: 13px; vertical-align: middle; }
    .table td { vertical-align: middle; color: #263f4a; border-color: #e5f0f2; font-size: 13px; white-space: nowrap; }
    .table tbody tr:hover td { background: #fbfefe; }
    .badge { border-radius: 999px; padding: 7px 10px; font-size: 11px; font-weight: 900; }
    .recommendation-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-top: 16px; }
    .recommendation-card { background: #fbfefe; border: 1px solid var(--clinic-border); border-radius: 18px; padding: 16px; }
    .recommendation-title { color: var(--clinic-primary); font-weight: 900; margin-bottom: 8px; font-size: 15px; }
    .recommendation-text { color: var(--clinic-muted); font-size: 13px; line-height: 1.6; margin-bottom: 0; }
    .signature-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 26px; margin-top: 32px; }
    .signature-box { padding-top: 26px; }
    .signature-line { border-top: 1px solid #16323f; padding-top: 8px; text-align: center; font-size: 13px; font-weight: 800; color: var(--clinic-text); }
    .signature-role { text-align: center; font-size: 12px; color: var(--clinic-muted); margin-top: 2px; }
    .small-note { font-size: 0.9rem; color: var(--clinic-muted); line-height: 1.5; }
    .alert { border-radius: 16px; border: none; box-shadow: var(--clinic-shadow); }
    .alert-info { background: #ecfeff; color: #155e75; border: 1px solid #bae6fd; }
    .school-year-select { max-width: 180px; }
    @media (max-width: 1200px) { .report-shell { grid-template-columns: 1fr; } .sidebar { position: static; height: auto; border-right: none; border-bottom: 1px solid var(--clinic-border); } .filter-group { grid-template-columns: repeat(2, 1fr); } .report-module-grid, .summary-strip, .chart-layout, .recommendation-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 768px) { .main-area { padding: 16px; } .sidebar { padding: 18px; } .report-paper { padding: 20px; } .report-letterhead { grid-template-columns: 1fr; } .report-meta { text-align: left; } .filter-group, .report-module-grid, .summary-strip, .chart-layout, .recommendation-grid, .signature-grid { grid-template-columns: 1fr; } .toolbar-title, .report-title, .report-center-title { font-size: 24px; } }
    @media print { body { background: white !important; color: black !important; } .sidebar, .top-toolbar, .page-header, .no-print, .report-module-grid, .report-center-header { display: none !important; } .report-shell { display: block !important; } .main-area { padding: 0 !important; } .report-paper { border: none !important; box-shadow: none !important; border-radius: 0 !important; padding: 0 !important; margin: 0 !important; } .summary-box, .chart-card, .recommendation-card { box-shadow: none !important; border: 1px solid #ccc !important; break-inside: avoid; } .report-letterhead { border-bottom: 2px solid #000 !important; } .report-title, .chart-title, .recommendation-title { color: black !important; } .table th { background: #eeeeee !important; color: black !important; } .table td { color: black !important; } .chart-layout { grid-template-columns: 1fr !important; } .recommendation-grid { grid-template-columns: 1fr 1fr 1fr !important; } @page { size: A4 landscape; margin: 12mm; } }
  </style>
</head>
<body>
<div id="app" class="report-shell">

  <main class="main-area">
    <div class="page-header no-print">
      <div class="page-header-left">
        <div class="page-header-icon">📊</div>
        <div>
          <div class="page-header-kicker">Government School Profile Reports</div>
          <h1 class="page-header-title">Nurse Report Center</h1>
          <p class="page-header-sub">Consolidated report from all saved boxes – with nutritional status chart</p>
        </div>
      </div>

      <div class="page-header-actions">
        <div class="header-prepared">
          <div class="header-prepared-label">Prepared By</div>
          <div class="header-prepared-name">{{ nurseName }}</div>
        </div>
        <button class="btn-header" @click="printReport">🖨️ Print Report</button>
        <button class="btn-header soft" @click="loadRecords">🔄 Refresh Data</button>
        <button class="btn-header soft" @click="resetFilters">Reset Filters</button>
        <a href="nurse-dashboard.php" class="btn-header soft">← Back</a>
      </div>
    </div>

    <!-- Consolidated Report Section (Printable) -->
    <section class="report-paper" id="printSection">
      <div class="report-letterhead">
        <div class="letterhead-brand">
          <div class="letterhead-logo">⚕</div>
          <div>
            <h2 class="report-title">ClinicDesk Consolidated School Report</h2>
            <p class="report-subtitle">Complete school health and nutrition summary – {{ selectedSchoolYear }}</p>
          </div>
        </div>
        <div class="report-meta">
          <div class="meta-line"><strong>School Year:</strong> {{ selectedSchoolYear }}</div>
          <div class="meta-line"><strong>Date Printed:</strong> {{ currentDate }}</div>
          <div class="meta-line"><strong>Prepared By:</strong> {{ nurseName }}</div>
        </div>
      </div>

      <div class="report-section no-print">
        <div class="d-flex gap-2 align-items-center flex-wrap">
          <select v-model="selectedSchoolYear" class="form-select school-year-select" @change="loadConsolidatedReport">
            <option value="2021-2022">2021-2022</option>
            <option value="2022-2023">2022-2023</option>
            <option value="2023-2024">2023-2024</option>
            <option value="2025-2026">2025-2026</option>
            <option value="2027-2028">2027-2028</option>
          </select>
          <button class="btn-main" @click="loadConsolidatedReport" :disabled="consolidatedLoading">🔄 Load Consolidated Report</button>
          <button class="btn-main" @click="sendReportToCloudinary" :disabled="sendingReport">📤 Generate Report</button>
        </div>
        <div v-if="consolidatedLoading" class="alert alert-info mt-3">Loading consolidated report...</div>
        <div v-if="consolidatedError" class="alert alert-danger mt-3">{{ consolidatedError }}</div>
      </div>

      <div v-if="Object.keys(consolidatedReports).length === 0 && !consolidatedLoading && !consolidatedError" class="alert alert-info">
        No saved reports found for {{ selectedSchoolYear }}. Please go to individual report pages and save data first.
      </div>

      <!-- Nutritional Status Chart (only one) -->
      <div v-if="hasNutritionData" class="chart-layout">
        <div class="chart-card">
          <div class="chart-title">📊 Nutritional Status (JHS + SHS)</div>
          <div class="chart-box"><canvas id="nutritionChart"></canvas></div>
        </div>
      </div>

      <!-- BOX 1 – OKD & LHAS -->
      <div v-if="consolidatedReports.box1" class="report-section">
        <div class="report-meta">Saved by {{ consolidatedReports.box1.saved_by }} on {{ consolidatedReports.box1.saved_at }}</div>
        <h2 class="report-title">📋 BOX 1 – OKD and LHAS</h2>
        <div class="mb-3"><strong>Functional Referral Mechanisms:</strong> {{ (consolidatedReports.box1.data.referralMechanisms || []).join(', ') || 'None' }}</div>
        <h5>Junior High School</h5>
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead><tr><th>Screening Type</th><th>Masterlisted</th><th>Screened</th><th>Findings</th><th>Referred School</th><th>Referred LGU</th><th>Referred Private</th><th>Referred Others</th><th>Total Referred</th></td></thead>
            <tbody>
              <tr v-for="(row, idx) in lhasRows" :key="idx">
                <td class="text-start fw-bold">{{ row }}</td>
                <td>{{ consolidatedReports.box1.data.lhasJHS?.[idx]?.masterlisted || 0 }}</td>
                <td>{{ consolidatedReports.box1.data.lhasJHS?.[idx]?.screened || 0 }}</td>
                <td>{{ consolidatedReports.box1.data.lhasJHS?.[idx]?.findings || 0 }}</td>
                <td>{{ consolidatedReports.box1.data.lhasJHS?.[idx]?.referredSchool || 0 }}</td>
                <td>{{ consolidatedReports.box1.data.lhasJHS?.[idx]?.referredLGU || 0 }}</td>
                <td>{{ consolidatedReports.box1.data.lhasJHS?.[idx]?.referredPrivate || 0 }}</td>
                <td>{{ consolidatedReports.box1.data.lhasJHS?.[idx]?.referredOthers || 0 }}</td>
                <td class="total-cell">{{ lhasTotal(consolidatedReports.box1.data.lhasJHS?.[idx]) }}</td>
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
                <td>{{ consolidatedReports.box1.data.lhasSHS?.[idx]?.masterlisted || 0 }}</td>
                <td>{{ consolidatedReports.box1.data.lhasSHS?.[idx]?.screened || 0 }}</td>
                <td>{{ consolidatedReports.box1.data.lhasSHS?.[idx]?.findings || 0 }}</td>
                <td>{{ consolidatedReports.box1.data.lhasSHS?.[idx]?.referredSchool || 0 }}</td>
                <td>{{ consolidatedReports.box1.data.lhasSHS?.[idx]?.referredLGU || 0 }}</td>
                <td>{{ consolidatedReports.box1.data.lhasSHS?.[idx]?.referredPrivate || 0 }}</td>
                <td>{{ consolidatedReports.box1.data.lhasSHS?.[idx]?.referredOthers || 0 }}</td>
                <td class="total-cell">{{ lhasTotal(consolidatedReports.box1.data.lhasSHS?.[idx]) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- BOX 2 & 3 – School Clinic & Water Supply -->
      <div v-if="consolidatedReports.box2_3" class="report-section">
        <div class="report-meta">Saved by {{ consolidatedReports.box2_3.saved_by }} on {{ consolidatedReports.box2_3.saved_at }}</div>
        <h2 class="report-title">🏥 BOX 2 & 3 – School Clinic & Water Supply</h2>
        <p><strong>School Clinic:</strong> {{ consolidatedReports.box2_3.data.hasSchoolClinic || 'Not specified' }}</p>
        <p><strong>Visited by SDO:</strong> {{ consolidatedReports.box2_3.data.visitedBySDO || 'Not specified' }} <span v-if="consolidatedReports.box2_3.data.visitedBySDO === 'Yes'">({{ consolidatedReports.box2_3.data.sdoVisits || 0 }} visits)</span></p>
        <p><strong>Clinic Equipment Status:</strong></p>
        <ul>
          <li v-for="(status, item) in consolidatedReports.box2_3.data.clinicEquipment" :key="item">{{ item }}: {{ status || 'Not specified' }}</li>
        </ul>
        <p><strong>Water Sources:</strong> {{ (consolidatedReports.box2_3.data.waterSources || []).join(', ') || 'None' }}</p>
        <p><strong>Water used for drinking:</strong> {{ consolidatedReports.box2_3.data.waterForDrinking || 'Not specified' }}</p>
      </div>

      <!-- BOX 4 – Mental Health -->
      <div v-if="consolidatedReports.box4" class="report-section">
        <div class="report-meta">Saved by {{ consolidatedReports.box4.saved_by }} on {{ consolidatedReports.box4.saved_at }}</div>
        <h2 class="report-title">🧠 BOX 4 – School Mental Health</h2>
        <p><strong>Guidance Office:</strong> {{ consolidatedReports.box4.data.hasGuidanceOffice || 'Not specified' }}</p>
        <p><strong>Counseling (JHS):</strong> Male {{ consolidatedReports.box4.data.counselingJHS?.male || 0 }}, Female {{ consolidatedReports.box4.data.counselingJHS?.female || 0 }}</p>
        <p><strong>Counseling (SHS):</strong> Male {{ consolidatedReports.box4.data.counselingSHS?.male || 0 }}, Female {{ consolidatedReports.box4.data.counselingSHS?.female || 0 }}</p>
        <p><strong>Vulnerable groups JHS:</strong> Muslim {{ consolidatedReports.box4.data.vulnerableJHS?.muslim || 0 }}, IP {{ consolidatedReports.box4.data.vulnerableJHS?.ip || 0 }}, LWD {{ consolidatedReports.box4.data.vulnerableJHS?.lwd || 0 }}</p>
        <p><strong>Vulnerable groups SHS:</strong> Muslim {{ consolidatedReports.box4.data.vulnerableSHS?.muslim || 0 }}, IP {{ consolidatedReports.box4.data.vulnerableSHS?.ip || 0 }}, LWD {{ consolidatedReports.box4.data.vulnerableSHS?.lwd || 0 }}</p>
        <p><strong>Mental Health Training:</strong> {{ consolidatedReports.box4.data.hasMentalHealthTraining || 'No' }}</p>
        <div v-if="consolidatedReports.box4.data.hasMentalHealthTraining === 'Yes'">
          <p><strong>Trained teachers per topic:</strong></p>
          <ul>
            <li v-for="(count, topic) in consolidatedReports.box4.data.mentalHealthTraining" :key="topic">{{ topic }}: {{ count || 0 }}</li>
          </ul>
        </div>
      </div>

      <!-- BOX 5 & 6 – ARH & Tobacco -->
      <div v-if="consolidatedReports.box5_6" class="report-section">
        <div class="report-meta">Saved by {{ consolidatedReports.box5_6.saved_by }} on {{ consolidatedReports.box5_6.saved_at }}</div>
        <h2 class="report-title">👥 BOX 5 & 6 – ARH & Tobacco Control</h2>
        <p><strong>Pregnant Learners (In School):</strong> G7={{ consolidatedReports.box5_6.data.pregnantLearners?.['In School']?.g7 || 0 }}, G8={{ consolidatedReports.box5_6.data.pregnantLearners?.['In School']?.g8 || 0 }}, G9={{ consolidatedReports.box5_6.data.pregnantLearners?.['In School']?.g9 || 0 }}, G10={{ consolidatedReports.box5_6.data.pregnantLearners?.['In School']?.g10 || 0 }}, G11={{ consolidatedReports.box5_6.data.pregnantLearners?.['In School']?.g11 || 0 }}, G12={{ consolidatedReports.box5_6.data.pregnantLearners?.['In School']?.g12 || 0 }}</p>
        <p><strong>Pregnant Learners (ADM):</strong> G7={{ consolidatedReports.box5_6.data.pregnantLearners?.['On Alternative Delivery Mode (ADM)']?.g7 || 0 }}, etc.</p>
        <p><strong>Support Center:</strong> {{ consolidatedReports.box5_6.data.hasSupportCenter || 'Not specified' }}</p>
        <p><strong>Peer Educators:</strong> {{ consolidatedReports.box5_6.data.peerEducators || 0 }}</p>
        <p><strong>IEC Materials:</strong> {{ (consolidatedReports.box5_6.data.iecMaterials || []).join(', ') || 'None' }}</p>
        <p><strong>Stores Selling:</strong> {{ (consolidatedReports.box5_6.data.storesSelling || []).join(', ') || 'None' }}</p>
        <p><strong>Tobacco Violations – Brought:</strong> JHS {{ consolidatedReports.box5_6.data.tobaccoViolations?.jhs?.brought || 0 }}, SHS {{ consolidatedReports.box5_6.data.tobaccoViolations?.shs?.brought || 0 }}</p>
        <p><strong>Referred to care:</strong> JHS {{ consolidatedReports.box5_6.data.tobaccoViolations?.jhs?.referred || 0 }}, SHS {{ consolidatedReports.box5_6.data.tobaccoViolations?.shs?.referred || 0 }}</p>
      </div>

      <!-- BOX 8 & 9 – Food Handling & Feeding -->
      <div v-if="consolidatedReports.box8_9" class="report-section">
        <div class="report-meta">Saved by {{ consolidatedReports.box8_9.saved_by }} on {{ consolidatedReports.box8_9.saved_at }}</div>
        <h2 class="report-title">🍽️ BOX 8 & 9 – Food Handling & Feeding</h2>
        <p><strong>Canteen:</strong> {{ consolidatedReports.box8_9.data.hasCanteen || 'Not specified' }}</p>
        <div v-if="consolidatedReports.box8_9.data.hasCanteen === 'Yes'">
          <p><strong>Managed by:</strong> {{ consolidatedReports.box8_9.data.canteenManager || 'Not specified' }} <span v-if="consolidatedReports.box8_9.data.canteenManager === 'Others'">({{ consolidatedReports.box8_9.data.canteenManagerOther }})</span></p>
          <p><strong>Sanitary Permit:</strong> {{ consolidatedReports.box8_9.data.sanitaryPermit || 'Not specified' }}</p>
          <p><strong>Health Certificates:</strong> {{ consolidatedReports.box8_9.data.healthCertificates || 'Not specified' }}</p>
        </div>
        <p><strong>Kitchen:</strong> {{ consolidatedReports.box8_9.data.hasKitchen || 'Not specified' }}</p>
        <p><strong>Feeding Fund Sources:</strong> {{ (consolidatedReports.box8_9.data.feedingFundSources || []).join(', ') || 'None' }}</p>
        <p><strong>Agriculture/Fishery Resources:</strong> {{ (consolidatedReports.box8_9.data.agriResources || []).join(', ') || 'None' }}</p>
      </div>

      <!-- BOX 10 & 11 – Waste Management & Menstrual Hygiene -->
      <div v-if="consolidatedReports.box10_11" class="report-section">
        <div class="report-meta">Saved by {{ consolidatedReports.box10_11.saved_by }} on {{ consolidatedReports.box10_11.saved_at }}</div>
        <h2 class="report-title">♻️ BOX 10 & 11 – Waste Management & Menstrual Hygiene</h2>
        <p><strong>SWM Implementation:</strong> {{ (consolidatedReports.box10_11.data.swmImplementation || []).join(', ') || 'None' }}</p>
        <p><strong>Stakeholders:</strong> {{ (consolidatedReports.box10_11.data.stakeholders || []).join(', ') || 'None' }}</p>
        <p><strong>Sanitary Pad Locations:</strong> {{ (consolidatedReports.box10_11.data.sanitaryPadLocations || []).join(', ') || 'None' }} <span v-if="consolidatedReports.box10_11.data.sanitaryPadOther">({{ consolidatedReports.box10_11.data.sanitaryPadOther }})</span></p>
      </div>

      <!-- TABLE 1-A – Immunization & Nutrition -->
      <div v-if="consolidatedReports.table1_a" class="report-section">
        <div class="report-meta">Saved by {{ consolidatedReports.table1_a.saved_by }} on {{ consolidatedReports.table1_a.saved_at }}</div>
        <h2 class="report-title">🩺 TABLE 1-A – Immunization & Nutritional Status</h2>
        <p><strong>Immunization (Td):</strong> Male {{ consolidatedReports.table1_a.data.vaccineTD?.male || 0 }}, Female {{ consolidatedReports.table1_a.data.vaccineTD?.female || 0 }}, IP {{ consolidatedReports.table1_a.data.vaccineTD?.ip || 0 }}</p>
        <p><strong>Immunization (HPV):</strong> Male {{ consolidatedReports.table1_a.data.vaccineHPV?.male || 0 }}, Female {{ consolidatedReports.table1_a.data.vaccineHPV?.female || 0 }}, IP {{ consolidatedReports.table1_a.data.vaccineHPV?.ip || 0 }}</p>
        <h5>Nutritional Status JHS</h5>
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead><tr><th>Status</th><th>G7 M</th><th>G7 F</th><th>G8 M</th><th>G8 F</th><th>G9 M</th><th>G9 F</th><th>G10 M</th><th>G10 F</th></tr></thead>
            <tbody>
              <tr v-for="status in ['Normal','Obese','Overweight','Severely Wasted','Wasted']" :key="status">
                <td class="fw-bold">{{ status }}</td>
                <td>{{ consolidatedReports.table1_a.data.nutritionJHS?.[status]?.g7Male || 0 }}</td>
                <td>{{ consolidatedReports.table1_a.data.nutritionJHS?.[status]?.g7Female || 0 }}</td>
                <td>{{ consolidatedReports.table1_a.data.nutritionJHS?.[status]?.g8Male || 0 }}</td>
                <td>{{ consolidatedReports.table1_a.data.nutritionJHS?.[status]?.g8Female || 0 }}</td>
                <td>{{ consolidatedReports.table1_a.data.nutritionJHS?.[status]?.g9Male || 0 }}</td>
                <td>{{ consolidatedReports.table1_a.data.nutritionJHS?.[status]?.g9Female || 0 }}</td>
                <td>{{ consolidatedReports.table1_a.data.nutritionJHS?.[status]?.g10Male || 0 }}</td>
                <td>{{ consolidatedReports.table1_a.data.nutritionJHS?.[status]?.g10Female || 0 }}</td>
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
                <td>{{ consolidatedReports.table1_a.data.nutritionSHS?.[status]?.g11Male || 0 }}</td>
                <td>{{ consolidatedReports.table1_a.data.nutritionSHS?.[status]?.g11Female || 0 }}</td>
                <td>{{ consolidatedReports.table1_a.data.nutritionSHS?.[status]?.g12Male || 0 }}</td>
                <td>{{ consolidatedReports.table1_a.data.nutritionSHS?.[status]?.g12Female || 0 }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TABLE 1-B – Deworming & WIFA -->
      <div v-if="consolidatedReports.table1_b" class="report-section">
        <div class="report-meta">Saved by {{ consolidatedReports.table1_b.saved_by }} on {{ consolidatedReports.table1_b.saved_at }}</div>
        <h2 class="report-title">💊 TABLE 1-B – Deworming & WIFA</h2>
        <h5>Dewormed Learners</h5>
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead><tr><th>Grade</th><th>SBFP M</th><th>SBFP F</th><th>Other M</th><th>Other F</th></td></thead>
            <tbody>
              <tr v-for="g in [7,8,9,10,11,12]" :key="g">
                <td class="fw-bold">Grade {{ g }}</td>
                <td>{{ consolidatedReports.table1_b.data.dewormed?.[g]?.sbfpMale || 0 }}</td>
                <td>{{ consolidatedReports.table1_b.data.dewormed?.[g]?.sbfpFemale || 0 }}</td>
                <td>{{ consolidatedReports.table1_b.data.dewormed?.[g]?.otherMale || 0 }}</td>
                <td>{{ consolidatedReports.table1_b.data.dewormed?.[g]?.otherFemale || 0 }}</td>
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
                <td>{{ consolidatedReports.table1_b.data.wifa?.[g]?.julSep || 0 }}</td>
                <td>{{ consolidatedReports.table1_b.data.wifa?.[g]?.janMar || 0 }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="signature-grid">
        <div class="signature-box"><div class="signature-line">{{ nurseName }}</div><div class="signature-role">Prepared by / Clinic Nurse</div></div>
        <div class="signature-box"><div class="signature-line">&nbsp;</div><div class="signature-role">Reviewed by / School Administrator</div></div>
      </div>
    </section>

    <!-- Original Student Nutritional Summary (kept but not printed by default) -->
    <div class="report-center-header no-print">
      <div class="report-center-kicker">Report Modules</div>
      <h2 class="report-center-title">Separated Report Sections</h2>
      <p class="report-center-text">Use individual links to edit or view specific reports. Use the consolidated report above for printing all data.</p>
    </div>
    <div class="report-module-grid no-print">
      <div class="report-module-card"><div class="report-module-icon">📋</div><h3 class="report-module-title">OKD and LHAS</h3><p class="report-module-desc">Oplan Kalusugan sa DepEd and Learners Health Assessment and Screening report section.</p><div class="report-module-meta"><span class="module-chip">JHS</span><span class="module-chip">SHS</span></div><div class="report-module-actions"><a href="report-box1-lhas.php" class="btn-main">Open</a></div></div>
      <div class="report-module-card"><div class="report-module-icon">🩺</div><h3 class="report-module-title">Health and Nutrition</h3><p class="report-module-desc">School-based immunization and nutritional status summary.</p><div class="report-module-meta"><span class="module-chip">Immunization</span><span class="module-chip">Nutrition</span></div><div class="report-module-actions"><a href="report-table1-health-nutrition-a.php" class="btn-main">Open</a></div></div>
      <div class="report-module-card"><div class="report-module-icon">💊</div><h3 class="report-module-title">Deworming</h3><p class="report-module-desc">Deworming records and WIFA supplementation data.</p><div class="report-module-meta"><span class="module-chip">Deworming</span><span class="module-chip">WIFA</span></div><div class="report-module-actions"><a href="report-table1-health-nutrition-b.php" class="btn-main">Open</a></div></div>
      <div class="report-module-card"><div class="report-module-icon">🏥</div><h3 class="report-module-title">School Clinic</h3><p class="report-module-desc">School clinic information, equipment, and water supply.</p><div class="report-module-meta"><span class="module-chip">Clinic</span><span class="module-chip">Water</span></div><div class="report-module-actions"><a href="report-box2-box3.php" class="btn-main">Open</a></div></div>
      <div class="report-module-card"><div class="report-module-icon">🧠</div><h3 class="report-module-title">School Mental Health</h3><p class="report-module-desc">Mental health cases, counseling, and teacher training.</p><div class="report-module-meta"><span class="module-chip">Mental Health</span></div><div class="report-module-actions"><a href="report-table2-box4-mental-health.php" class="btn-main">Open</a></div></div>
      <div class="report-module-card"><div class="report-module-icon">👥</div><h3 class="report-module-title">ARH & Tobacco</h3><p class="report-module-desc">Adolescent reproductive health and tobacco control.</p><div class="report-module-meta"><span class="module-chip">ARH</span><span class="module-chip">Tobacco</span></div><div class="report-module-actions"><a href="report-box5-box6.php" class="btn-main">Open</a></div></div>
      <div class="report-module-card"><div class="report-module-icon">🍽️</div><h3 class="report-module-title">Food Handling</h3><p class="report-module-desc">Canteen, kitchen, feeding program, and resources.</p><div class="report-module-meta"><span class="module-chip">Food</span><span class="module-chip">Feeding</span></div><div class="report-module-actions"><a href="report-box8-box9.php" class="btn-main">Open</a></div></div>
      <div class="report-module-card"><div class="report-module-icon">♻️</div><h3 class="report-module-title">Solid Waste Management</h3><p class="report-module-desc">Waste management and menstrual hygiene.</p><div class="report-module-meta"><span class="module-chip">Waste</span><span class="module-chip">Hygiene</span></div><div class="report-module-actions"><a href="report-box10-box11.php" class="btn-main">Open</a></div></div>
    </div>
  </main>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script>
const { createApp } = Vue;

createApp({
  data() {
    return {
      nurseName: "",
      activeRole: "",
      currentDate: new Date().toLocaleDateString(),
      selectedSchoolYear: "2021-2022",
      consolidatedReports: {},
      consolidatedLoading: false,
      consolidatedError: null,
      lhasRows: ["Nutritional Assessment","Health History","Vision Screening","Hearing Screening","Oral Health","CARS","Rapid HEEADSSS"],
      nutritionChart: null,
      sendingReport: false,
      records: [], loading: false, searchTerm: "", riskFilter: "", bmiFilter: "", gradeFilter: "",
      riskChart: null, bmiChart: null
    };
  },
  computed: {
    hasNutritionData() {
      return this.consolidatedReports.table1_a?.data;
    }
  },
  mounted() {
    const role = localStorage.getItem("active_role");
    const accountId = localStorage.getItem("local_account_id");
    if (role !== "Clinic Nurse" && role !== "School Admin") { window.location.href = "login.php"; return; }
    if (!accountId) { window.location.href = "login.php"; return; }
    this.activeRole = role;
    this.nurseName = localStorage.getItem("local_full_name") || role || "ClinicDesk User";
    this.currentDate = new Date().toLocaleDateString("en-US", { weekday: "long", year: "numeric", month: "long", day: "numeric" });
  },
  methods: {
    lhasTotal(row) { if (!row) return 0; return (row.referredSchool||0)+(row.referredLGU||0)+(row.referredPrivate||0)+(row.referredOthers||0); },
    async loadConsolidatedReport() {
      this.consolidatedLoading = true;
      this.consolidatedError = null;
      try {
        const res = await fetch(`api/get_all_reports.php?school_year=${encodeURIComponent(this.selectedSchoolYear)}&cache_buster=${Date.now()}`);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        if (data.success) {
          this.consolidatedReports = data.reports;
          this.$nextTick(() => this.renderNutritionChart());
        } else {
          this.consolidatedError = data.message || "Failed to load reports";
        }
      } catch(e) { this.consolidatedError = "Error loading reports: " + e.message; }
      this.consolidatedLoading = false;
    },
    renderNutritionChart() {
      if (this.nutritionChart) this.nutritionChart.destroy();
      const table1a = this.consolidatedReports.table1_a?.data;
      if (!table1a) return;
      const categories = ['Normal', 'Obese', 'Overweight', 'Severely Wasted', 'Wasted'];
      const jhsTotal = (status) => {
        let sum = 0;
        for (let g of [7,8,9,10]) {
          sum += (table1a.nutritionJHS?.[status]?.[`g${g}Male`] || 0) + (table1a.nutritionJHS?.[status]?.[`g${g}Female`] || 0);
        }
        return sum;
      };
      const shsTotal = (status) => {
        let sum = 0;
        for (let g of [11,12]) {
          sum += (table1a.nutritionSHS?.[status]?.[`g${g}Male`] || 0) + (table1a.nutritionSHS?.[status]?.[`g${g}Female`] || 0);
        }
        return sum;
      };
      const data = categories.map(status => jhsTotal(status) + shsTotal(status));
      const ctx = document.getElementById('nutritionChart')?.getContext('2d');
      if (ctx) {
        this.nutritionChart = new Chart(ctx, {
          type: 'doughnut',
          data: { labels: categories, datasets: [{ data: data, backgroundColor: ['#16a34a','#dc2626','#f97316','#eab308','#8b5cf6'] }] },
          options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
      }
    },
    async sendReportToCloudinary() {
      if (Object.keys(this.consolidatedReports).length === 0) {
        alert('No report data loaded. Please load a consolidated report first.');
        return;
      }
      this.sendingReport = true;
      // Clone the printable section to avoid modifying the live DOM
      const printSection = document.getElementById('printSection').cloneNode(true);
      // Remove any interactive controls inside the clone (optional)
      const noPrintElements = printSection.querySelectorAll('.no-print');
      noPrintElements.forEach(el => el.remove());
      const reportHtml = printSection.outerHTML;
      try {
        const res = await fetch('api/upload_consolidated_report.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            school_year: this.selectedSchoolYear,
            report_html: reportHtml,
            generated_by: this.nurseName
          })
        });
        const data = await res.json();
        if (data.success) {
          alert('Report sent to Cloudinary successfully!\nURL: ' + data.url);
          window.open(data.url, '_blank');
        } else {
          alert('Error: ' + data.message);
        }
      } catch(e) {
        alert('Error: ' + e.message);
      }
      this.sendingReport = false;
    },
    printReport() { window.print(); },
    loadRecords() {},
    resetFilters() {},
    getHeightForAge() { return "-"; },
    getRiskLevel() { return "Low"; },
    getRecommendation() { return ""; },
    getBmiBadge() { return "bg-secondary"; },
    getRiskBadge() { return "bg-secondary"; },
    renderChartsPlaceholder() {}
  }
}).mount("#app");
</script>
</body>
</html>