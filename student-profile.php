<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | Student Profile</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root {
      --clinic-primary: #0f766e;
      --clinic-secondary: #14b8a6;
      --clinic-accent: #0ea5e9;
      --clinic-bg: #eef8fb;
      --clinic-light: #f0fdfa;
      --clinic-card: rgba(255, 255, 255, 0.96);
      --clinic-border: #d9eef0;
      --clinic-text: #16323f;
      --clinic-muted: #6b7d87;
      --clinic-shadow: 0 12px 32px rgba(15, 118, 110, 0.10);
      --clinic-radius: 22px;
    }
    * { box-sizing: border-box; }
    [v-cloak] { display: none !important; }
    body {
      min-height: 100vh;
      margin: 0;
      background:
        radial-gradient(circle at top left, rgba(20,184,166,0.16), transparent 25%),
        radial-gradient(circle at top right, rgba(14,165,233,0.12), transparent 25%),
        linear-gradient(135deg, #eef8fb, #f8fcfd);
      font-family: 'Plus Jakarta Sans', Arial, sans-serif;
      color: var(--clinic-text);
      overflow-x: hidden;
    }
    .wrapper { max-width: 1450px; margin: 28px auto; padding: 20px; }
    .header-box {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white;
      padding: 34px;
      border-radius: 28px;
      margin-bottom: 24px;
      box-shadow: 0 16px 38px rgba(15, 118, 110, 0.22);
      position: relative;
      overflow: hidden;
    }
    .header-box::before { content: ""; position: absolute; top: -90px; right: -80px; width: 230px; height: 230px; background: rgba(255,255,255,0.16); border-radius: 50%; filter: blur(4px); }
    .header-box::after { content: ""; position: absolute; bottom: -110px; left: -80px; width: 220px; height: 220px; background: rgba(255,255,255,0.10); border-radius: 50%; filter: blur(4px); }
    .header-content, .header-actions { position: relative; z-index: 2; }
    .header-icon { width: 62px; height: 62px; border-radius: 20px; background: rgba(255,255,255,0.18); border: 2px solid rgba(255,255,255,0.35); display: flex; align-items: center; justify-content: center; font-size: 30px; margin-right: 16px; box-shadow: 0 0 28px rgba(255,255,255,0.20); flex-shrink: 0; }
    .header-box h1 { font-size: 38px; font-weight: 900; margin-bottom: 8px; text-shadow: 0 4px 16px rgba(0,0,0,0.15); }
    .header-box p { font-size: 15px; color: rgba(255,255,255,0.92); }
    .btn-back, .btn-edit {
      background: white; color: var(--clinic-primary); border: none; border-radius: 15px; padding: 11px 18px; font-weight: 800; box-shadow: 0 12px 28px rgba(0,0,0,0.12); text-decoration: none; display: inline-block;
    }
    .btn-back:hover, .btn-edit:hover { background: #ecfeff; color: var(--clinic-primary); }
    .btn-green {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white; font-weight: 900; border: none; border-radius: 14px; padding: 11px 16px; box-shadow: 0 12px 24px rgba(15, 118, 110, 0.18); text-decoration: none; display: inline-block;
    }
    .btn-green:hover { color: white; transform: translateY(-1px); box-shadow: 0 14px 30px rgba(15, 118, 110, 0.22); }
    .btn-danger-custom {
      background: #dc2626; color: white; font-weight: 900; border: none; border-radius: 14px; padding: 11px 16px; box-shadow: 0 12px 24px rgba(220,38,38,0.18); text-decoration: none; display: inline-block;
    }
    .btn-danger-custom:hover { background: #b91c1c; transform: translateY(-1px); }
    .btn-outline-clinic { border: 1px solid var(--clinic-primary); color: var(--clinic-primary); background: white; font-weight: 900; border-radius: 14px; padding: 10px 14px; text-decoration: none; display: inline-block; }
    .btn-outline-clinic:hover { background: var(--clinic-primary); color: white; }
    .main-grid { display: grid; grid-template-columns: 350px minmax(0, 1fr); gap: 24px; align-items: start; }
    .profile-content { min-width: 0; }
    .card-box { background: var(--clinic-card); border: 1px solid var(--clinic-border); border-radius: var(--clinic-radius); box-shadow: var(--clinic-shadow); padding: 24px; color: var(--clinic-text); }
    .card-box h4 { color: var(--clinic-primary); font-weight: 900; }
    .profile-card { position: sticky; top: 20px; }
    .avatar { width: 96px; height: 96px; border-radius: 30px; background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary)); color: white; display: flex; align-items: center; justify-content: center; font-size: 34px; font-weight: 900; margin-bottom: 16px; box-shadow: 0 14px 28px rgba(15, 118, 110, 0.20); }
    .student-name { font-size: 24px; font-weight: 900; color: var(--clinic-text); margin-bottom: 4px; }
    .muted-text { color: var(--clinic-muted); font-size: 14px; line-height: 1.5; }
    .badge { border-radius: 999px; padding: 8px 12px; font-size: 12px; font-weight: 800; }
    .info-row { display: flex; justify-content: space-between; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--clinic-border); }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: var(--clinic-muted); font-size: 13px; font-weight: 700; }
    .info-value { color: var(--clinic-text); font-size: 13px; font-weight: 800; text-align: right; word-break: break-word; }
    .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
    .summary-card { background: var(--clinic-card); border: 1px solid var(--clinic-border); border-radius: 20px; padding: 18px; box-shadow: var(--clinic-shadow); position: relative; overflow: hidden; }
    .summary-card::after { content: ""; position: absolute; top: -35px; right: -35px; width: 95px; height: 95px; background: rgba(20,184,166,0.10); border-radius: 50%; }
    .summary-label { font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--clinic-muted); font-weight: 800; margin-bottom: 6px; position: relative; z-index: 2; }
    .summary-value { font-size: 26px; font-weight: 900; color: var(--clinic-primary); margin-bottom: 0; position: relative; z-index: 2; }
    .summary-helper { color: var(--clinic-muted); font-size: 12px; margin-top: 4px; margin-bottom: 0; position: relative; z-index: 2; }
    .section-title { margin-bottom: 16px; }
    .section-title h3 { color: var(--clinic-primary); font-weight: 900; margin-bottom: 4px; }
    .section-title p { color: var(--clinic-muted); font-size: 14px; margin-bottom: 0; }
    .info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
    .info-box { background: #f8fcfd; border: 1px solid var(--clinic-border); border-radius: 16px; padding: 15px; min-height: 86px; }
    .info-box-label { font-size: 12px; color: var(--clinic-muted); text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; margin-bottom: 6px; }
    .info-box-value { font-size: 15px; font-weight: 800; color: var(--clinic-text); word-break: break-word; margin-bottom: 0; }
    .assessment-card { background: linear-gradient(135deg, #ecfeff, #f0fdfa); border: 1px solid var(--clinic-border); border-left: 6px solid var(--clinic-secondary); border-radius: 20px; padding: 22px; box-shadow: var(--clinic-shadow); }
    .assessment-icon { width: 54px; height: 54px; border-radius: 18px; background: white; border: 1px solid var(--clinic-border); color: var(--clinic-primary); display: flex; align-items: center; justify-content: center; font-size: 27px; flex-shrink: 0; box-shadow: 0 10px 20px rgba(15, 118, 110, 0.08); }
    .recommendation-box { background: #f8fcfd; border: 1px solid var(--clinic-border); border-radius: 16px; padding: 16px; }
    .alert { border-radius: 16px; border: none; box-shadow: var(--clinic-shadow); }
    .alert-info { background: #ecfeff; color: #155e75; border: 1px solid #bae6fd; }
    .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .small-note { font-size: 0.9rem; color: var(--clinic-muted); line-height: 1.5; }
    .prediction-result { margin-top: 8px; }
    .modal-content { border-radius: 24px; overflow: hidden; }
    .modal-header { background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary)); color: white; }
    .form-control, .form-select { border-radius: 12px; border: 1px solid var(--clinic-border); padding: 10px 14px; }
    .form-control:focus, .form-select:focus { border-color: var(--clinic-secondary); box-shadow: 0 0 0 0.2rem rgba(20,184,166,0.12); }
    .program-summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
    .program-tile { padding: 16px; border: 1px solid var(--clinic-border); border-radius: 16px; background: #f8fcfd; }
    .program-tile strong { display: block; color: var(--clinic-primary); margin-top: 5px; }
    .program-table th { color: var(--clinic-primary); background: var(--clinic-light); font-size: 12px; text-transform: uppercase; letter-spacing: .04em; }
    .program-table td { vertical-align: middle; font-size: 13px; }
    .program-status { display: inline-block; padding: 6px 10px; border-radius: 999px; background: var(--clinic-light); color: var(--clinic-primary); font-weight: 800; }
    .program-empty { padding: 16px; color: var(--clinic-muted); background: #f8fcfd; border: 1px dashed var(--clinic-border); border-radius: 14px; }
    .profile-tabs { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); border: 1px solid var(--clinic-border); border-radius: 16px; overflow: hidden; background: #fff; box-shadow: var(--clinic-shadow); margin-bottom: 22px; }
    .profile-tab { min-width: 0; min-height: 54px; border: 0; border-right: 1px solid var(--clinic-border); border-bottom: 1px solid var(--clinic-border); background: #e8f7f4; color: var(--clinic-primary); padding: 11px 14px; font-size: 13px; font-weight: 800; text-align: center; }
    .profile-tab:hover { background: #d9f2ec; }
    .profile-tab.active { background: #fff; color: var(--clinic-text); box-shadow: inset 0 4px var(--clinic-primary); }
    .profile-tab:focus-visible { outline: 3px solid var(--clinic-secondary); outline-offset: -3px; }
    .profile-tab i { margin-right: 7px; }
    .profile-panel { min-width: 0; }
    .profile-table-wrap { max-width: 100%; overflow-x: auto; border: 1px solid var(--clinic-border); border-radius: 14px; }
    .profile-table { width: max-content; min-width: 100%; margin: 0; font-size: 12px; }
    .profile-table th { background: var(--clinic-light); color: var(--clinic-primary); font-weight: 800; white-space: nowrap; }
    .profile-table td, .profile-table th { padding: 10px 12px; vertical-align: top; min-width: 100px; }
    .profile-table td { white-space: normal; overflow-wrap: anywhere; }
    .profile-table th:first-child, .profile-table td:first-child { position: sticky; left: 0; min-width: 125px; background: #fff; z-index: 1; }
    .profile-table th:first-child { background: var(--clinic-light); z-index: 2; }
    .profile-table tbody tr:hover td:first-child { background: #f8fcfd; }
    .profile-chip { display: inline-block; border-radius: 999px; padding: 5px 9px; background: var(--clinic-light); color: var(--clinic-primary); font-weight: 800; font-size: 11px; }
    .profile-chip.warn { background: #fff4d6; color: #92400e; }
    .profile-chip.muted { background: #f1f5f9; color: #64748b; }
    .profile-section-label { color: var(--clinic-primary); font-size: 15px; font-weight: 800; margin: 18px 0 10px; }
    .profile-print-title { display: none; }
    @media (max-width: 768px) { .program-summary { grid-template-columns: 1fr; } }
    @media (max-width: 1200px) { .main-grid { grid-template-columns: minmax(0, 1fr); } .profile-card { position: static; } .summary-grid { grid-template-columns: repeat(2, 1fr); } .info-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 768px) { .wrapper { padding: 14px; margin: 12px auto; } .header-box { padding: 26px; } .header-content { align-items: flex-start !important; } .header-icon { width: 50px; height: 50px; font-size: 24px; } .header-box h1 { font-size: 30px; } .summary-grid, .info-grid { grid-template-columns: 1fr; } .assessment-card .d-flex { align-items: flex-start !important; } .profile-tabs { grid-template-columns: repeat(2, minmax(0, 1fr)); } .profile-tab { font-size: 12px; padding: 10px 8px; } }
    @media print {
      @page { size: A4; margin: 12mm; }
      body { background: #fff !important; color: #111; font-size: 10pt; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .wrapper { max-width: none; margin: 0; padding: 0; }
      .header-box, .profile-card, .profile-tabs, .no-print, .modal, .alert, .profile-panel .btn, .profile-section button, .profile-section a.btn, .print-action { display: none !important; }
      .profile-print-title { display: block; border-bottom: 2px solid #0f766e; padding-bottom: 8px; margin-bottom: 15px; }
      .profile-print-title h1 { font-size: 18pt; margin: 0 0 4px; color: #0f766e; }
      .main-grid { display: block; }
      .profile-panel, .profile-section { display: block !important; }
      .summary-grid.profile-section { display: grid !important; }
      .card-box, .summary-card, .assessment-card { box-shadow: none; border: 1px solid #b6c9cc; border-radius: 6px; padding: 12px; margin-bottom: 12px !important; break-inside: avoid; }
      .profile-panel .card-box { break-inside: auto; }
      .summary-grid { grid-template-columns: repeat(4, 1fr); gap: 6px; margin-bottom: 12px; }
      .summary-card { padding: 8px; }
      .summary-value { font-size: 16pt; }
      .info-grid { grid-template-columns: repeat(3, 1fr); gap: 6px; }
      .info-box { min-height: 0; padding: 7px; border-radius: 4px; }
      .info-box-label, .summary-label { font-size: 8pt; }
      .info-box-value { font-size: 10pt; }
      .section-title h3 { font-size: 13pt; }
      .section-title p, .small-note { font-size: 8pt; }
      .profile-table-wrap, .table-responsive { overflow: visible !important; }
      .profile-table, .program-table { width: 100% !important; font-size: 8pt; }
      .profile-table th:first-child, .profile-table td:first-child { position: static; }
      .profile-table tr, .program-table tr { break-inside: avoid; }
    }
  </style>
</head>
<body>
<div id="app" class="wrapper" v-cloak>

  <div class="header-box d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div class="header-content d-flex align-items-center">
      <div class="header-icon">👤</div>
      <div>
        <h1>Student Profile</h1>
        <p class="mb-1">View and edit individual student information, nutritional records, and assessment summary.</p>
        <p class="mb-0">Clinic Nurse: <strong>{{ nurseName }}</strong></p>
      </div>
    </div>
    <div class="header-actions d-flex gap-2 flex-wrap">
      <button type="button" class="btn-edit" @click="openEditModal" :disabled="!student.record_id"><i class="bi bi-pencil-square me-1" aria-hidden="true"></i>Edit Student</button>
      <button type="button" class="btn-edit" @click="printProfile" :disabled="!canPrint"><i class="bi bi-printer me-1" aria-hidden="true"></i>Print Record</button>
      <a :href="fromDirectory ? 'student-directory.php' : 'monitoring-hub.php'" class="btn-back">{{ fromDirectory ? 'Back to Student Directory' : 'Back to Monitoring' }}</a>
    </div>
  </div>

  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">{{ message }}</div>
  <div v-if="profileLoadError" class="alert alert-danger">{{ profileLoadError }} <button type="button" class="btn btn-sm btn-outline-danger ms-2" @click="loadStudentProfile">Retry</button></div>
  <div v-if="programLoadError" class="alert alert-danger">{{ programLoadError }} <button type="button" class="btn btn-sm btn-outline-danger ms-2" @click="loadProgram">Retry</button></div>
  <div v-if="loading" class="alert alert-info">Loading student profile...</div>

  <div v-if="!loading && student.record_id" class="main-grid">
    <!-- LEFT PROFILE CARD -->
    <div class="profile-card card-box">
      <div class="avatar">{{ initials }}</div>
      <div class="student-name">{{ displayValue(student.learner_name) }}</div>
      <div class="muted-text mb-3">{{ displayValue(student.grade_level) }} - {{ displayValue(student.section) }}<br>{{ displayValue(student.sex) }} · {{ displayValue(student.age) }} years old</div>
      <div class="d-flex gap-2 flex-wrap mb-4">
        <span class="badge" :class="getBmiBadge(student.bmi_category)">{{ displayValue(student.bmi_category, "For Review") }}</span>
        <span class="badge" :class="getRiskBadge(riskLevel)">{{ riskLevel }} Risk</span>
      </div>
      <div class="info-row"><div class="info-label">Record ID</div><div class="info-value">{{ displayValue(student.record_id) }}</div></div>
      <div class="info-row"><div class="info-label">LRN</div><div class="info-value">{{ displayValue(student.lrn) }}</div></div>
      <div class="info-row"><div class="info-label">School Year</div><div class="info-value">{{ displayValue(student.school_year) }}</div></div>
      <div class="info-row"><div class="info-label">BMI</div><div class="info-value">{{ displayValue(student.bmi) }}</div></div>
      <div class="info-row"><div class="info-label">Weight</div><div class="info-value">{{ displayValue(student.weight_kg) }} kg</div></div>
      <div class="info-row"><div class="info-label">Height</div><div class="info-value">{{ displayValue(student.height_m) }} m</div></div>
      <div class="info-row"><div class="info-label">Height-for-Age</div><div class="info-value">{{ heightForAge }}</div></div>
      <div class="mt-4 d-flex gap-2">
        <a :href="'health-assessment-screening.php?record_id=' + student.record_id" class="btn btn-green w-100">Open Health Assessment</a>
        <button class="btn btn-danger-custom" @click="openDeleteModal">🗑️ Delete</button>
      </div>
    </div>

    <div class="profile-content">
      <div class="profile-print-title"><h1>Individual Student Health Record</h1><div><strong>{{ displayValue(student.learner_name) }}</strong> · LRN {{ displayValue(student.lrn) }} · {{ displayValue(student.school_year) }}</div><div>Printed {{ printedAt }}</div></div>
      <nav class="profile-tabs" role="tablist" aria-label="Student profile sections">
        <button v-for="tab in profileTabs" :key="tab.key" type="button" role="tab" class="profile-tab" :class="{active:activeTab===tab.key}" :aria-selected="activeTab===tab.key" @click="selectTab(tab.key)"><i :class="tab.icon" aria-hidden="true"></i>{{ tab.label }}</button>
      </nav>
      <div class="summary-grid profile-section" v-show="activeTab==='overview'">
        <div class="summary-card"><div class="summary-label">BMI</div><p class="summary-value">{{ displayValue(student.bmi) }}</p><p class="summary-helper">{{ displayValue(student.bmi_category, "For Review") }}</p></div>
        <div class="summary-card"><div class="summary-label">Weight</div><p class="summary-value">{{ displayValue(student.weight_kg) }}</p><p class="summary-helper">Kilograms</p></div>
        <div class="summary-card"><div class="summary-label">Height</div><p class="summary-value">{{ displayValue(student.height_m) }}</p><p class="summary-helper">Meters</p></div>
        <div class="summary-card"><div class="summary-label">Risk Level</div><p class="summary-value" style="font-size: 22px;">{{ riskLevel }}</p><p class="summary-helper">Based on BMI and HFA</p></div>
      </div>

      <div class="assessment-card mb-4 profile-section" v-show="activeTab==='overview'">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <div class="d-flex gap-3 align-items-center"><div class="assessment-icon">🩺</div><div><h4 class="fw-bold mb-1">Health Assessment Screening</h4><p class="small-note mb-0">Open the separate health assessment page to monitor symptoms, meal plan calendar, daily progress, and follow-up status.</p></div></div>
          <a :href="'health-assessment-screening.php?record_id=' + student.record_id" class="btn btn-green">Open Screening</a>
        </div>
      </div>


      <!-- GENERAL INFORMATION -->
      <div class="card-box mb-4 profile-section" v-show="activeTab==='overview'">
        <div class="section-title"><h3>General Information</h3><p>Basic learner information extracted from the approved SF8 record.</p></div>
        <div class="info-grid">
          <div class="info-box"><div class="info-box-label">Learner Name</div><p class="info-box-value">{{ displayValue(student.learner_name) }}</p></div>
          <div class="info-box"><div class="info-box-label">LRN</div><p class="info-box-value">{{ displayValue(student.lrn) }}</p></div>
          <div class="info-box"><div class="info-box-label">Birthdate</div><p class="info-box-value">{{ displayValue(student.birthdate) }}</p></div>
          <div class="info-box"><div class="info-box-label">Age</div><p class="info-box-value">{{ displayValue(student.age) }}</p></div>
          <div class="info-box"><div class="info-box-label">Sex</div><p class="info-box-value">{{ displayValue(student.sex) }}</p></div>
          <div class="info-box"><div class="info-box-label">Grade Level</div><p class="info-box-value">{{ displayValue(student.grade_level) }}</p></div>
          <div class="info-box"><div class="info-box-label">Section</div><p class="info-box-value">{{ displayValue(student.section) }}</p></div>
          <div class="info-box"><div class="info-box-label">Profile status</div><p class="info-box-value">{{ displayValue(student.profile_status) }}</p></div>
          <div class="info-box"><div class="info-box-label">Muslim</div><p class="info-box-value">{{ Number(student.is_muslim) ? 'Yes' : 'No' }}</p></div>
          <div class="info-box"><div class="info-box-label">Person with disability (PWD)</div><p class="info-box-value">{{ Number(student.is_pwd) ? 'Yes' : 'No' }}</p></div>
          <div class="info-box"><div class="info-box-label">Indigenous People (IP)</div><p class="info-box-value">{{ Number(student.is_ip) ? 'Yes' : 'No' }}</p></div>
          <div class="info-box"><div class="info-box-label">Record source</div><p class="info-box-value">{{ student.upload_id ? 'SF8 upload #' + student.upload_id : 'Manual entry' }}</p></div>
          <div class="info-box"><div class="info-box-label">Date saved</div><p class="info-box-value">{{ displayValue(student.date_saved) }}</p></div>
        </div>
      </div>

      <!-- SCHOOL INFORMATION -->
      <div class="card-box mb-4 profile-section" v-show="activeTab==='overview'">
        <div class="section-title"><h3>School Information</h3><p>School and academic details connected to this student record.</p></div>
        <div class="info-grid">
          <div class="info-box"><div class="info-box-label">School Name</div><p class="info-box-value">{{ displayValue(student.school_name) }}</p></div>
          <div class="info-box"><div class="info-box-label">School ID</div><p class="info-box-value">{{ displayValue(student.school_id) }}</p></div>
          <div class="info-box"><div class="info-box-label">District</div><p class="info-box-value">{{ displayValue(student.district) }}</p></div>
          <div class="info-box"><div class="info-box-label">Division</div><p class="info-box-value">{{ displayValue(student.division) }}</p></div>
          <div class="info-box"><div class="info-box-label">Region</div><p class="info-box-value">{{ displayValue(student.region) }}</p></div>
          <div class="info-box"><div class="info-box-label">School Year</div><p class="info-box-value">{{ displayValue(student.school_year) }}</p></div>
          <div class="info-box"><div class="info-box-label">Track / Strand</div><p class="info-box-value">{{ displayValue(student.track_strand) }}</p></div>
        </div>
      </div>

      <!-- HEALTH MEASUREMENTS -->
      <div class="card-box mb-4 profile-section" v-show="activeTab==='overview'">
        <div class="section-title"><h3>Health Measurements</h3><p>Physical measurements and nutritional classification of the student.</p></div>
        <div class="info-grid">
          <div class="info-box"><div class="info-box-label">Weight</div><p class="info-box-value">{{ displayValue(student.weight_kg) }} kg</p></div>
          <div class="info-box"><div class="info-box-label">Height</div><p class="info-box-value">{{ displayValue(student.height_m) }} m</p></div>
          <div class="info-box"><div class="info-box-label">Height Squared</div><p class="info-box-value">{{ displayValue(student.height_squared) }}</p></div>
          <div class="info-box"><div class="info-box-label">BMI</div><p class="info-box-value">{{ displayValue(student.bmi) }}</p></div>
          <div class="info-box"><div class="info-box-label">BMI Category</div><p class="info-box-value"><span class="badge" :class="getBmiBadge(student.bmi_category)">{{ displayValue(student.bmi_category, "For Review") }}</span></p></div>
          <div class="info-box"><div class="info-box-label">Height-for-Age</div><p class="info-box-value">{{ heightForAge }}</p></div>
        </div>
      </div>

      <!-- WIFA AND DEWORMING MONITORING -->
      <div id="wifa-monitoring" class="card-box mb-4 profile-section" v-show="activeTab==='wifa'">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
          <div class="section-title mb-0"><h3>WIFA Iron Sulfate Dates</h3><p>Record the dates iron sulfate was taken. The SF8 date remains the imported starting record.</p></div>
          <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-green" @click="openWifaModal()" :disabled="programLoading || !program">Record Intake Date</button>
          </div>
        </div>
        <div v-if="programLoading" class="alert alert-info">Loading WIFA history...</div>
        <template v-if="program">
          <div class="program-summary mb-3">
            <div class="program-tile"><span class="info-box-label">SF8 starting date</span><strong>{{ Number(program.sf8_baseline?.wifa || 0) === 1 ? (program.sf8_baseline?.wifa_date || 'Date unknown') : 'Not recorded' }}</strong></div>
            <div class="program-tile"><span class="info-box-label">Last recorded intake</span><strong>{{ lastWifaDose() }}</strong></div>
            <div class="program-tile"><span class="info-box-label">Nurse-recorded dates</span><strong>{{ program.wifa_events.length }}</strong></div>
          </div>
          <p v-if="program.sf8_baseline?.remarks" class="small-note">SF8 remarks: {{ program.sf8_baseline.remarks }}</p>
          <div class="table-responsive" v-if="program.wifa_events.length">
            <table class="table table-bordered program-table mb-3">
              <thead><tr><th>Iron sulfate intake date</th><th>Recorded by</th><th class="print-action">Action</th></tr></thead>
              <tbody><tr v-for="event in program.wifa_events" :key="event.wifa_event_id">
                <td>{{ event.event_date }}</td>
                <td>{{ event.recorded_by || 'Clinic Nurse' }}</td>
                <td class="print-action"><button type="button" class="btn btn-sm btn-outline-clinic" @click="openWifaModal(event)">Correct</button></td>
              </tr></tbody>
            </table>
          </div>
          <div v-else class="program-empty mb-3">No additional iron sulfate intake dates recorded. The SF8 starting date remains above.</div>
        </template>
      </div>

      <div id="deworming-monitoring" class="card-box mb-4 profile-section" v-show="activeTab==='wifa'">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
          <div class="section-title mb-0"><h3>Deworming History</h3><p>Record actual dates without changing the imported SF8 summary.</p></div>
          <button type="button" class="btn btn-green" @click="openDewormingModal()" :disabled="programLoading || !program">Record Deworming</button>
        </div>
        <template v-if="program">
          <p class="small-note">SF8 baseline: SBFP {{ Number(program.sf8_baseline?.dewormed_sbfp || 0) === 1 ? 'Yes' : 'No' }} · Other {{ Number(program.sf8_baseline?.dewormed_other || 0) === 1 ? 'Yes' : 'No' }}. The SF8 row has no deworming date.</p>
          <div v-if="program.deworming_events.length" class="table-responsive"><table class="table table-bordered program-table mb-0">
            <thead><tr><th>Date</th><th>Source</th><th>Result</th><th>Remarks</th><th class="print-action">Action</th></tr></thead>
            <tbody><tr v-for="event in program.deworming_events" :key="event.deworming_event_id">
              <td>{{ event.event_date }}</td><td>{{ event.channel }}</td><td>{{ event.outcome }}</td><td>{{ event.remarks || '—' }}</td>
              <td class="print-action"><button type="button" class="btn btn-sm btn-outline-clinic" @click="openDewormingModal(event)">Correct</button></td>
            </tr></tbody>
          </table></div>
          <div v-else class="program-empty">No dated deworming entries yet.</div>
          <p v-if="program.event_audit.some(item => item.action === 'Corrected')" class="small-note mt-3 mb-0">Corrections are retained in the monitoring audit history.</p>
        </template>
      </div>

      <!-- NUTRITIONAL ASSESSMENT -->
      <div class="card-box mb-4 profile-section" v-show="activeTab==='overview'">
        <div class="section-title"><h3>Nutritional Assessment</h3><p>Interpretation of the student's current nutritional condition.</p></div>
        <div class="info-grid">
          <div class="info-box"><div class="info-box-label">Risk Level</div><p class="info-box-value"><span class="badge" :class="getRiskBadge(riskLevel)">{{ riskLevel }}</span></p></div>
          <div class="info-box"><div class="info-box-label">Nutritional Status</div><p class="info-box-value">{{ displayValue(student.bmi_category, "For Review") }}</p></div>
          <div class="info-box"><div class="info-box-label">Remarks</div><p class="info-box-value">{{ displayValue(student.remarks) }}</p></div>
        </div>
      </div>

      <!-- RECOMMENDATION -->
      <div class="card-box profile-section" v-show="activeTab==='overview'">
        <div class="section-title"><h3>Recommendation</h3><p>Basic recommendation based on the current nutritional status.</p></div>
        <div class="recommendation-box"><h5 class="fw-bold text-success mb-2">Suggested Action</h5><p class="mb-0 small-note">{{ recommendation }}</p></div>
      </div>

      <section class="profile-panel" v-show="activeTab==='assessment'" aria-label="Health assessment">
        <div class="card-box mb-4">
          <div class="section-title"><h3>Clinic Health Assessment</h3><p>Recorded lifestyle, symptoms, medical background, and nurse follow-up.</p></div>
          <div v-if="!healthAssessment" class="program-empty">No clinic health assessment has been recorded for this learner.</div>
          <template v-else>
            <div v-for="group in assessmentGroups" :key="group.title">
              <h4 class="profile-section-label">{{ group.title }}</h4>
              <div class="info-grid">
                <div v-for="field in group.fields" :key="field.key" class="info-box"><div class="info-box-label">{{ field.label }}</div><p class="info-box-value">{{ displayValue(healthAssessment[field.key]) }}</p></div>
              </div>
            </div>
            <p class="small-note mt-3 mb-0">Last updated: {{ displayValue(healthAssessment.updated_at) }}</p>
          </template>
          <a :href="'health-assessment-screening.php?record_id=' + student.record_id" class="btn btn-outline-clinic mt-3 no-print">Open Health Assessment</a>
        </div>
        <div class="card-box mb-4">
          <div class="section-title"><h3>Prediction &amp; Recommendations</h3><p>Saved model results and their associated recommendations; these support nurse review.</p></div>
          <div v-if="!predictions.length" class="program-empty">No prediction has been saved for this learner.</div>
          <div v-for="prediction in predictions" :key="prediction.prediction_id" class="recommendation-box mb-3">
            <div class="d-flex justify-content-between gap-2 flex-wrap"><strong>{{ screeningFlag(prediction.predicted_deficiency) }}</strong><span class="small-note">{{ displayValue(prediction.prediction_date) }}</span></div>
            <div class="info-grid mt-3">
              <div class="info-box"><div class="info-box-label">Risk level</div><p class="info-box-value">{{ displayValue(prediction.predicted_risk_level) }}</p></div>
              <div class="info-box"><div class="info-box-label">Model score</div><p class="info-box-value">{{ confidenceLabel(prediction.confidence_score) }}</p></div>
              <div class="info-box"><div class="info-box-label">Algorithm</div><p class="info-box-value">{{ displayValue(prediction.algorithm_used) }}</p></div>
            </div>
            <p class="small-note mt-3 mb-1"><strong>Recommendation:</strong> {{ displayValue(prediction.recommendation_text) }}</p>
            <p class="small-note mb-1"><strong>Recommended foods:</strong> {{ displayValue(prediction.recommended_foods) }}</p>
            <p class="small-note mb-0"><strong>Intervention:</strong> {{ displayValue(prediction.intervention_type) }}</p>
          </div>
        </div>
        <div class="card-box mb-4">
          <div class="section-title"><h3>Consultation History</h3><p>Reported symptoms and care documented by the nurse.</p></div>
          <div v-if="!consultations.length" class="program-empty">No consultations have been recorded for this learner.</div>
          <div v-else class="profile-table-wrap"><table class="table profile-table"><thead><tr><th>Date</th><th>Reported symptoms</th><th>Care given</th><th>Follow-up</th><th>Notes</th><th>Recorded by</th></tr></thead><tbody>
            <tr v-for="entry in consultations" :key="entry.consultation_id"><td>{{ displayValue(entry.recorded_at) }}</td><td>{{ displayValue(entry.symptoms) }}</td><td>{{ displayValue(entry.care_given) }}</td><td>{{ displayValue(entry.follow_up_date) }}</td><td>{{ displayValue(entry.notes) }}</td><td>{{ displayValue(entry.recorded_by) }}</td></tr>
          </tbody></table></div>
        </div>
      </section>

      <section class="profile-panel" v-show="activeTab==='feeding'" aria-label="Feeding program">
        <div class="card-box mb-4">
          <div class="section-title"><h3>Feeding Program</h3><p>SF8 baseline and separate dated follow-up measurements.</p></div>
          <div class="info-grid mb-3">
            <div class="info-box"><div class="info-box-label">SF8 weight</div><p class="info-box-value">{{ withUnit(student.weight_kg, 'kg') }}</p></div>
            <div class="info-box"><div class="info-box-label">SF8 BMI</div><p class="info-box-value">{{ displayValue(student.bmi) }}</p></div>
            <div class="info-box"><div class="info-box-label">Latest nurse assessment</div><p class="info-box-value">{{ displayValue(feedingMeasurements[0]?.progress_status, 'Not assessed') }}</p></div>
          </div>
          <div v-if="!feedingMeasurements.length" class="program-empty">No feeding follow-up measurements have been recorded.</div>
          <div v-else class="profile-table-wrap"><table class="table profile-table"><thead><tr><th>Date</th><th>Weight (kg)</th><th>BMI</th><th>Nurse assessment</th><th>Notes</th><th>Recorded by</th></tr></thead><tbody>
            <tr v-for="entry in feedingMeasurements" :key="entry.measurement_id"><td>{{ displayValue(entry.measured_on) }}</td><td>{{ displayValue(entry.weight_kg) }}</td><td>{{ displayValue(entry.bmi) }}</td><td>{{ displayValue(entry.progress_status, 'Not assessed') }}</td><td>{{ displayValue(entry.notes) }}</td><td>{{ displayValue(entry.recorded_by) }}</td></tr>
          </tbody></table></div>
          <a href="monitoring-hub.php#feeding" class="btn btn-outline-clinic mt-3 no-print">Open Feeding Monitoring</a>
        </div>
      </section>

      <section class="profile-panel" v-show="activeTab==='immunization'" aria-label="Immunization and screening">
        <div class="card-box mb-4">
          <div class="section-title"><h3>Immunization</h3><p>Each vaccine and dose recorded for this learner.</p></div>
          <div v-if="!immunizations.length" class="program-empty">No immunization record has been linked to this learner.</div>
          <div v-else class="profile-table-wrap"><table class="table profile-table"><thead><tr><th>Vaccine</th><th>Dose</th><th>Immunized</th><th>Remarks</th></tr></thead><tbody>
            <tr v-for="entry in immunizations" :key="entry.immunization_id"><td>{{ displayValue(entry.vaccine) }}</td><td>{{ displayValue(entry.dose) }}</td><td><span class="profile-chip" :class="Number(entry.immunized)===1?'':'muted'">{{ yesNo(entry.immunized) }}</span></td><td>{{ displayValue(entry.remarks) }}</td></tr>
          </tbody></table></div>
        </div>
        <div class="card-box mb-4">
          <div class="section-title"><h3>OKD &amp; LHAS Screening</h3><p>Screening results, findings, referrals, and remarks.</p></div>
          <div v-if="!screenings.length" class="program-empty">No OKD or LHAS screening record has been linked to this learner.</div>
          <div v-else class="profile-table-wrap"><table class="table profile-table"><thead><tr><th>Screening</th><th>Masterlisted</th><th>Screened</th><th>Findings</th><th>Referred to</th><th>Remarks</th></tr></thead><tbody>
            <tr v-for="entry in screenings" :key="entry.okd_lhas_id"><td>{{ displayValue(entry.screening_type) }}</td><td>{{ yesNo(entry.masterlisted) }}</td><td>{{ yesNo(entry.screened) }}</td><td>{{ yesNo(entry.findings) }}</td><td>{{ referralText(entry) }}</td><td>{{ displayValue(entry.remarks) }}</td></tr>
          </tbody></table></div>
        </div>
      </section>

      <section class="profile-panel" v-show="activeTab==='other'" aria-label="ARH and tobacco control">
        <div class="card-box mb-4">
          <div class="section-title"><h3>Adolescent Reproductive Health</h3><p>Approved ARH records for the same learner and school year.</p></div>
          <div v-if="!arhRecords.length" class="program-empty">No ARH record has been linked to this learner.</div>
          <div v-else class="profile-table-wrap"><table class="table profile-table"><thead><tr><th>Recorded</th><th>Pregnancy status</th><th>Delivery mode</th><th>Peer educator</th><th>Remarks</th></tr></thead><tbody>
            <tr v-for="entry in arhRecords" :key="entry.arh_record_id"><td>{{ displayValue(entry.date_saved) }}</td><td>{{ displayValue(entry.pregnancy_status) }}</td><td>{{ displayValue(entry.delivery_mode) }}</td><td>{{ yesNo(entry.peer_educator) }}</td><td>{{ displayValue(entry.remarks) }}</td></tr>
          </tbody></table></div>
        </div>
        <div class="card-box mb-4">
          <div class="section-title"><h3>Tobacco Control</h3><p>Recorded violations and referrals to care.</p></div>
          <div v-if="!tobaccoRecords.length" class="program-empty">No tobacco control record has been linked to this learner.</div>
          <div v-else class="profile-table-wrap"><table class="table profile-table"><thead><tr><th>Violation type</th><th>Referred to care</th><th>Remarks</th></tr></thead><tbody>
            <tr v-for="entry in tobaccoRecords" :key="entry.tobacco_id"><td>{{ displayValue(entry.violation_type) }}</td><td>{{ yesNo(entry.referred_to_care) }}</td><td>{{ displayValue(entry.remarks) }}</td></tr>
          </tbody></table></div>
        </div>
      </section>
    </div>
  </div>

  <!-- WIFA INTAKE DATE MODAL -->
  <div class="modal fade" id="wifaEventModal" tabindex="-1" data-bs-backdrop="static" aria-labelledby="wifaEventTitle">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
      <div class="modal-header"><h5 class="modal-title fw-bold" id="wifaEventTitle">{{ wifaForm.event_id ? 'Correct Intake Date' : 'Record Iron Sulfate Intake' }}</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <div v-if="programError" class="alert alert-danger">{{ programError }}</div>
        <p class="small-note">Enter the date the student actually took iron sulfate. This page does not schedule the next intake.</p>
        <div class="row g-3">
          <div class="col-12"><label class="form-label" for="wifaTakenDate">Date taken</label><input id="wifaTakenDate" v-model="wifaForm.event_date" type="date" class="form-control"></div>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal" :disabled="programSaving">Cancel</button><button type="button" class="btn btn-green" @click="saveWifaEvent" :disabled="programSaving">{{ programSaving ? 'Saving...' : 'Save Date' }}</button></div>
    </div></div>
  </div>

  <!-- DEWORMING EVENT MODAL -->
  <div class="modal fade" id="dewormingEventModal" tabindex="-1" data-bs-backdrop="static" aria-labelledby="dewormingEventTitle">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
      <div class="modal-header"><h5 class="modal-title fw-bold" id="dewormingEventTitle">{{ dewormingForm.event_id ? 'Correct Deworming Entry' : 'Record Deworming' }}</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <div v-if="programError" class="alert alert-danger">{{ programError }}</div>
        <div class="row g-3">
          <div class="col-12"><label class="form-label">Actual date</label><input v-model="dewormingForm.event_date" type="date" class="form-control"></div>
          <div class="col-6"><label class="form-label">Source</label><select v-model="dewormingForm.channel" class="form-select"><option>SBFP</option><option>Other</option></select></div>
          <div class="col-6"><label class="form-label">Deworming</label><select v-model="dewormingForm.outcome" class="form-select"><option>Given</option><option>Not given</option></select></div>
          <div class="col-12"><label class="form-label">Remarks</label><textarea v-model="dewormingForm.remarks" rows="2" class="form-control" placeholder="Required when not given"></textarea></div>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal" :disabled="programSaving">Cancel</button><button type="button" class="btn btn-green" @click="saveDewormingEvent" :disabled="programSaving">{{ programSaving ? 'Saving...' : 'Save Entry' }}</button></div>
    </div></div>
  </div>

  <!-- EDIT STUDENT MODAL -->
  <div class="modal fade" id="editModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-bold">✏️ Edit Student Information</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Learner Name</label><input type="text" class="form-control" v-model="editForm.learner_name"></div>
            <div class="col-md-3"><label class="form-label">Birthdate</label><input type="date" class="form-control" v-model="editForm.birthdate"></div>
            <div class="col-md-3"><label class="form-label">Age (years)</label><input type="number" step="0.01" min="2" max="19" class="form-control" v-model="editForm.age"></div>
            <div class="col-md-3"><label class="form-label">Sex</label><select class="form-select" v-model="editForm.sex"><option value="Male">Male</option><option value="Female">Female</option></select></div>
            <div class="col-md-3"><label class="form-label">Grade Level</label><input type="text" class="form-control" v-model="editForm.grade_level"></div>
            <div class="col-md-3"><label class="form-label">Section</label><input type="text" class="form-control" v-model="editForm.section"></div>
            <div class="col-md-3"><label class="form-label">Weight (kg)</label><input type="number" step="0.01" class="form-control" v-model="editForm.weight_kg"></div>
            <div class="col-md-3"><label class="form-label">Height (m)</label><input type="number" step="0.001" class="form-control" v-model="editForm.height_m"></div>
            <div class="col-md-6"><label class="form-label">BMI Category</label><select class="form-select" v-model="editForm.bmi_category"><option value="">Auto-calc from weight/height</option><option value="Severely Wasted">Severely Wasted</option><option value="Wasted">Wasted</option><option value="Normal">Normal</option><option value="Overweight">Overweight</option><option value="Obese">Obese</option></select><small class="text-muted">Leave empty to recalculate automatically.</small></div>
            <div class="col-md-6"><label class="form-label">Height-for-Age</label><input type="text" class="form-control" :value="heightForAge" readonly aria-describedby="hfaHelp"><div id="hfaHelp" class="form-text">Recalculated from sex, age, and standing height when you save. Ages outside 24–228 months remain unclassified.</div></div>
            <div class="col-12"><label class="form-label">Remarks</label><textarea class="form-control" rows="2" v-model="editForm.remarks"></textarea></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-green" @click="saveStudentEdit" :disabled="editSaving">{{ editSaving ? 'Saving...' : 'Save Changes' }}</button>
        </div>
      </div>
    </div>
  </div>

  <!-- DELETE STUDENT MODAL (password required) -->
  <div class="modal fade" id="deleteModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title fw-bold">⚠️ Delete Student Record</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete <strong>{{ student.learner_name }}</strong>?</p>
          <p class="text-danger">This action cannot be undone. All related health records will also be deleted.</p>
          <label class="form-label">Enter your account password to confirm:</label>
          <input type="password" class="form-control" v-model="deletePassword" placeholder="Password" @keyup.enter="confirmDeleteStudent">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" @click="confirmDeleteStudent" :disabled="deleting">{{ deleting ? 'Deleting...' : 'Delete Permanently' }}</button>
        </div>
      </div>
    </div>
  </div>

  <!-- PREDICTION MODAL (unchanged) -->
  <div class="modal fade" id="predictionModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header" :class="predictionResult && predictionResult.predicted_risk_level === 'High' ? 'bg-danger' : 'bg-success'">
          <h5 class="modal-title fw-bold text-white"><i class="bi bi-robot me-2"></i>ML Prediction Result</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div v-if="predictionLoading" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Sending data to ML model...</p></div>
          <div v-else-if="predictionError" class="alert alert-danger"><strong>Error:</strong> {{ predictionError }}</div>
          <div v-else-if="predictionResult" class="prediction-result">
            <div class="row mb-3">
              <div class="col-md-6"><div class="border rounded p-3 bg-light"><small class="text-muted">Screening flag for nurse review</small><h3 class="mb-0 fw-bold text-primary">{{ screeningFlag(predictionResult.predicted_deficiency) }}</h3></div></div>
              <div class="col-md-6"><div class="border rounded p-3 bg-light"><small class="text-muted">Screening priority</small><h3 class="mb-0 fw-bold" :class="{'text-danger': predictionResult.predicted_risk_level === 'High','text-warning': predictionResult.predicted_risk_level === 'Moderate','text-success': predictionResult.predicted_risk_level === 'Low'}">{{ predictionResult.predicted_risk_level || 'N/A' }}</h3></div></div>
            </div>
            <div class="row mb-3">
              <div class="col-md-6"><div class="border rounded p-3 bg-light"><small class="text-muted">Model score</small><h4 class="mb-0">{{ (predictionResult.confidence_score * 100).toFixed(1) }}%</h4></div></div>
              <div class="col-md-6"><div class="border rounded p-3 bg-light"><small class="text-muted">Algorithm Used</small><h4 class="mb-0">{{ predictionResult.algorithm_used || 'Random Forest' }}</h4></div></div>
            </div>
            <div class="alert alert-info mt-2"><strong>📋 Recommendation:</strong><br>{{ predictionResult.recommendation_text || 'No recommendation available.' }}</div>
            <div class="alert alert-success mt-2" v-if="predictionResult.recommended_foods"><strong>🍎 Recommended Foods:</strong><br>{{ predictionResult.recommended_foods }}</div>
            <div class="alert alert-secondary mt-2" v-if="predictionResult.intervention_type"><strong>🏥 Intervention Type:</strong><br>{{ predictionResult.intervention_type }}</div>
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
const profileTabs = [
  {key:'overview',label:'Overview',icon:'bi bi-person-vcard'},
  {key:'assessment',label:'Health Assessment',icon:'bi bi-clipboard2-pulse'},
  {key:'wifa',label:'WIFA & Deworming',icon:'bi bi-calendar2-heart'},
  {key:'feeding',label:'Feeding Program',icon:'bi bi-graph-up-arrow'},
  {key:'immunization',label:'Immunization & Screening',icon:'bi bi-shield-check'},
  {key:'other',label:'ARH & Tobacco',icon:'bi bi-heart-pulse'}
];
const assessmentGroups = [
  {title:'Lifestyle & Nutrition',fields:[
    {key:'diet_type',label:'Diet type'},{key:'sun_exposure',label:'Sun exposure'},
    {key:'exercise_level',label:'Exercise level'},{key:'immunization_updated',label:'Immunization status'}]},
  {title:'Symptoms & Health Concerns',fields:[
    {key:'symptoms',label:'Other symptoms'},{key:'has_fatigue',label:'Fatigue'},
    {key:'has_bone_pain',label:'Bone pain'},{key:'has_bleeding_gums',label:'Bleeding gums'},
    {key:'has_pale_skin',label:'Pale skin'},{key:'has_night_blindness',label:'Night blindness'},
    {key:'has_low_appetite',label:'Low appetite'},{key:'has_irregular_meals',label:'Irregular meals'},
    {key:'has_weight_changes',label:'Weight changes'},{key:'has_headache',label:'Headache'},
    {key:'has_poor_concentration',label:'Poor concentration'},{key:'has_vision_problem',label:'Vision concern'},
    {key:'has_hearing_problem',label:'Hearing concern'},{key:'has_dental_problem',label:'Dental concern'},
    {key:'has_skin_problem',label:'Skin concern'},{key:'has_breathing_problem',label:'Breathing concern'},
    {key:'has_recent_illness',label:'Recent illness'},{key:'has_current_medication',label:'Current medication'}]},
  {title:'Medical & Family History',fields:[
    {key:'has_known_allergy',label:'Known allergy'},{key:'allergy_details',label:'Allergy details'},
    {key:'family_history_diabetes',label:'Family history: diabetes'},
    {key:'family_history_heart_disease',label:'Family history: heart disease'},
    {key:'family_history_anemia',label:'Family history: anemia'},
    {key:'existing_medical_condition',label:'Existing medical condition'}]},
  {title:'Clinic Follow-up',fields:[
    {key:'needs_followup',label:'Needs follow-up'},{key:'needs_referral',label:'Needs referral'},
    {key:'clinic_notes',label:'Clinic notes'}]}
];
createApp({
  data() {
    return {
      profileTabs, assessmentGroups, activeTab:'overview', profileLoadError:'', profileLoaded:false, printedAt:'',
      healthAssessment:null, predictions:[], consultations:[], arhRecords:[], immunizations:[], screenings:[], tobaccoRecords:[], feedingMeasurements:[],
      nurseName: "",
      recordId: "",
      fromDirectory: false,
      openEditOnLoad: false,
      loading: false,
      message: "",
      messageType: "success",
      predictionLoading: false,
      predictionError: null,
      predictionResult: null,
      program: null,
      programLoading: false,
      programLoadError: '',
      programError: '',
      programSaving: false,
      wifaModal: null,
      dewormingModal: null,
      wifaForm: { event_id: null, event_date: '' },
      dewormingForm: { event_id: null, event_date: '', channel: 'SBFP', outcome: 'Given', remarks: '' },
      editForm: {
        learner_name: "", birthdate: "", age: "", sex: "", grade_level: "", section: "",
        weight_kg: "", height_m: "", bmi_category: "", height_for_age: "", remarks: ""
      },
      editSaving: false,
      editModal: null,
      deletePassword: "",
      deleting: false,
      deleteModal: null,
      student: {
        record_id: "", learner_name: "", birthdate: "", age: "", sex: "", school_name: "", school_id: "",
        district: "", division: "", region: "", grade_level: "", section: "", track_strand: "", school_year: "",
        weight_kg: "", height_m: "", height_squared: "", bmi: "", bmi_category: "", height_for_age: "",
        height_for_age_status: "", remarks: "", risk_level: "", recommendation: ""
      }
    };
  },
  computed: {
    initials() { if (!this.student.learner_name) return "?"; return this.student.learner_name.split(" ").filter(Boolean).slice(0,2).map(p=>p[0].toUpperCase()).join(""); },
    canPrint() { return this.profileLoaded && !!this.program && !this.loading && !this.programLoading && !this.profileLoadError && !this.programLoadError; },
    heightForAge() { return this.student.height_for_age_status || this.student.height_for_age || this.student.hfa_status || "-"; },
    riskLevel() {
      if (this.student.risk_level) return this.student.risk_level;
      const b = String(this.student.bmi_category || "").toLowerCase(), h = String(this.heightForAge || "").toLowerCase();
      if (b.includes("severely") || b.includes("obese") || h.includes("severely")) return "High";
      if (b.includes("wasted") || b.includes("overweight") || h.includes("stunted")) return "Moderate";
      if (b.includes("normal") && (h.includes("normal") || h === "-")) return "Low";
      return "For Review";
    },
    recommendation() {
      if (this.student.recommendation) return this.student.recommendation;
      const b = String(this.student.bmi_category || "").toLowerCase();
      if (this.riskLevel === "High") return "Priority clinic follow-up is recommended. The student should be monitored closely and may need parent or guardian notification.";
      if (b.includes("wasted")) return "Monitor weight regularly and encourage balanced meals with protein-rich food, fruits, vegetables, and healthy snacks.";
      if (b.includes("overweight") || b.includes("obese")) return "Encourage healthy food choices, regular physical activity, and routine monitoring of BMI and lifestyle habits.";
      if (this.riskLevel === "Moderate") return "Continue regular monitoring and schedule a follow-up assessment to check if the student's nutritional status improves.";
      if (this.riskLevel === "Low") return "Continue routine nutritional monitoring and maintain balanced meals and healthy habits.";
      return "For clinic review. Additional health assessment screening may be needed to provide a more accurate recommendation.";
    }
  },
  mounted() {
    const role = localStorage.getItem("active_role");
    const accountId = localStorage.getItem("local_account_id");
    if (role !== "Clinic Nurse" || !accountId) { window.location.href = "login.php"; return; }
    this.nurseName = localStorage.getItem("local_full_name") || "Clinic Nurse";
    const params = new URLSearchParams(window.location.search);
    this.recordId = params.get("record_id") || "";
    this.fromDirectory = params.get('from') === 'directory';
    this.openEditOnLoad = this.fromDirectory && params.get('edit') === '1';
    if (!this.recordId) { this.showMessage("error", "No student record ID provided."); return; }
    const hash = window.location.hash;
    if (hash === '#wifa-monitoring' || hash === '#deworming-monitoring') this.activeTab = 'wifa';
    else { const selected = hash.replace('#profile-', ''); if (this.profileTabs.some(tab => tab.key === selected)) this.activeTab = selected; }
    this.loadStudentProfile();
    this.loadProgram();
    this.editModal = new bootstrap.Modal(document.getElementById('editModal'));
    this.deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    this.wifaModal = new bootstrap.Modal(document.getElementById('wifaEventModal'));
    this.dewormingModal = new bootstrap.Modal(document.getElementById('dewormingEventModal'));
  },
  methods: {
    lastWifaDose() {
      if (!this.program) return 'None recorded';
      const baseline = this.program.sf8_baseline;
      const dates = [Number(baseline?.wifa || 0) === 1 ? baseline?.wifa_date : null,
        ...(this.program.wifa_events || []).filter(event => event.outcome === 'Given').map(event => event.event_date)];
      return dates.filter(Boolean).sort().pop() || 'None recorded';
    },
    selectTab(key) { this.activeTab = key; history.replaceState(null, '', '#profile-' + key); },
    printProfile() { if (!this.canPrint) return; this.printedAt = new Date().toLocaleString('en-PH', {timeZone:'Asia/Manila',dateStyle:'medium',timeStyle:'short'}); this.$nextTick(() => window.print()); },
    withUnit(value, unit) { return value===null || value===undefined || value==='' ? '—' : value + ' ' + unit; },
    yesNo(value) { return value===null || value===undefined || value==='' ? '—' : Number(value)===1 ? 'Yes' : 'No'; },
    referralText(entry) { const fields=[['referred_school','School'],['referred_lgu','LGU'],
      ['referred_private','Private'],['referred_others','Other']];
      const selected=fields.filter(([key])=>Number(entry[key])===1).map(([,label])=>label);
      return selected.length ? selected.join(', ') : 'None recorded'; },
    confidenceLabel(value) { const n=Number(value); return value===null || value===undefined || value==='' || !Number.isFinite(n) ? '—' : (n*100).toFixed(1)+'%'; },
    screeningFlag(value) { return ({'Iron':'Possible iron-related concern','Vitamin A':'Possible vitamin A-related concern','Vitamin B12':'Possible vitamin B12-related concern','Vitamin C':'Possible vitamin C-related concern','Vitamin D':'Possible vitamin D-related concern','Zinc':'Possible zinc-related concern','No Deficiency':'No concern flagged by this model','Iron Deficiency':'Possible iron-related concern','Vitamin D Deficiency':'Possible vitamin D-related concern','Severe Malnutrition':'Nutrition concern needing prompt review','Normal':'No concern flagged by earlier model'})[value] || 'For nurse review'; },
    async loadProgram() {
      this.programLoading = true; this.programLoadError = ''; this.program = null;
      try {
        const res = await fetch('api/health_program_monitoring.php?record_id=' + encodeURIComponent(this.recordId), {
          headers: { Authorization: 'Bearer ' + localStorage.getItem('local_id_token') }
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Monitoring history could not be loaded.');
        this.program = data;
      } catch (error) { this.programLoadError = error.message; }
      this.programLoading = false;
    },
    openWifaModal(event = null) {
      this.programError = '';
      this.wifaForm = event ? { event_id: event.wifa_event_id, event_date: event.event_date }
        : { event_id: null, event_date: '' };
      this.wifaModal.show();
    },
    openDewormingModal(event = null) {
      this.programError = '';
      this.dewormingForm = event ? { event_id: event.deworming_event_id, event_date: event.event_date,
        channel: event.channel, outcome: event.outcome, remarks: event.remarks || '' }
        : { event_id: null, event_date: '', channel: 'SBFP', outcome: 'Given', remarks: '' };
      this.dewormingModal.show();
    },
    async submitProgram(action, form, modal) {
      this.programSaving = true; this.programError = '';
      try {
        const response = await fetch('api/health_program_monitoring.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + localStorage.getItem('local_id_token') },
          body: JSON.stringify({ action, record_id: this.recordId, ...form })
        });
        const data = await response.json();
        if (!data.success) throw new Error(data.message || 'Could not save the entry.');
        modal.hide();
        this.showMessage('success', data.message);
        await this.loadProgram();
      } catch (error) { this.programError = error.message; }
      this.programSaving = false;
    },
    async saveWifaEvent() {
      await this.submitProgram(this.wifaForm.event_id ? 'update_wifa' : 'add_wifa', this.wifaForm, this.wifaModal);
    },
    async saveDewormingEvent() {
      await this.submitProgram(this.dewormingForm.event_id ? 'update_deworming' : 'add_deworming', this.dewormingForm, this.dewormingModal);
    },
    showMessage(type, text) { this.messageType = type; this.message = text; setTimeout(() => { this.message = ""; }, 5000); },
    async loadStudentProfile() {
      this.loading = true; this.profileLoadError = ''; this.profileLoaded = false;
      try {
        const res = await fetch('api/get_student_complete_profile.php?record_id=' + encodeURIComponent(this.recordId) + '&t=' + Date.now(), {
          headers: { Authorization: 'Bearer ' + localStorage.getItem('local_id_token') }
        });
        const result = await res.json();
        if (!result.success) throw new Error(result.message || 'The learner profile could not be loaded.');
        this.student = result.student || {};
        this.healthAssessment = result.health_assessment || null;
        this.predictions = result.predictions || [];
        this.consultations = result.consultations || [];
        this.arhRecords = result.arh_records || [];
        this.immunizations = result.immunization_records || [];
        this.screenings = result.screening_records || [];
        this.tobaccoRecords = result.tobacco_records || [];
        this.feedingMeasurements = result.feeding_measurements || [];
        this.editForm = {
          learner_name: this.student.learner_name || '', birthdate: this.student.birthdate || '',
          age: this.student.age || '', sex: this.student.sex || '',
          grade_level: this.student.grade_level || '', section: this.student.section || '',
          weight_kg: this.student.weight_kg || '', height_m: this.student.height_m || '',
          bmi_category: this.student.bmi_category || '', height_for_age: this.student.height_for_age || '',
          remarks: this.student.remarks || ''
        };
        this.profileLoaded = true;
        if (this.openEditOnLoad) {
          this.openEditOnLoad = false;
          await this.$nextTick();
          this.editModal.show();
        }
        if (window.location.hash === '#deworming-monitoring' || window.location.hash === '#wifa-monitoring') {
          await this.$nextTick(); document.querySelector(window.location.hash)?.scrollIntoView({block:'start'});
        }
      } catch(e) { this.profileLoadError = e.message; }
      finally { this.loading = false; }
    },
    openEditModal() { this.editModal.show(); },
    async saveStudentEdit() {
      this.editSaving = true;
      try {
        const payload = { record_id: this.recordId, ...this.editForm };
        const res = await fetch("api/update_student_profile.php", { method: "POST", headers: {
          "Content-Type": "application/json", "Authorization": "Bearer " + localStorage.getItem("local_id_token")
        }, body: JSON.stringify(payload) });
        const data = await res.json();
        if (data.success) {
          this.editModal.hide();
          this.showMessage("success", data.message);
          await this.loadStudentProfile();
        } else {
          this.showMessage("error", data.message || "Update failed.");
        }
      } catch(e) { this.showMessage("error", "Error: " + e.message); }
      this.editSaving = false;
    },
    openDeleteModal() { this.deletePassword = ""; this.deleteModal.show(); },
    async confirmDeleteStudent() {
      if (!this.deletePassword.trim()) { this.showMessage("error", "Please enter your password."); return; }
      this.deleting = true;
      try {
        const res = await fetch("api/delete_student_record.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ record_id: this.recordId, password: this.deletePassword })
        });
        const data = await res.json();
        if (data.success) {
          this.deleteModal.hide();
          this.showMessage("success", data.message);
          setTimeout(() => { window.location.href = this.fromDirectory ? "student-directory.php" : "monitoring-hub.php"; }, 1500);
        } else {
          this.showMessage("error", data.message || "Delete failed.");
        }
      } catch(e) { this.showMessage("error", "Error: " + e.message); }
      this.deleting = false;
    },
    async generatePrediction() {
      if (!this.student.record_id) { this.showMessage("error", "No student record ID found."); return; }
      this.predictionLoading = true; this.predictionError = null; this.predictionResult = null;
      const modalEl = document.getElementById("predictionModal");
      const modal = new bootstrap.Modal(modalEl);
      modal.show();
      try {
        const res = await fetch("api/generate_student_prediction.php", { method: "POST", headers: { "Content-Type": "application/json", "Authorization": `Bearer ${localStorage.getItem('local_id_token') || ''}` }, body: JSON.stringify({ record_id: this.student.record_id }) });
        const data = await res.json();
        if (data.success) { this.predictionResult = data.prediction; await this.loadStudentProfile(); }
        else { this.predictionError = data.message || "Prediction failed. Make sure the ML API is running."; }
      } catch(e) { this.predictionError = "Network error: " + e.message; }
      this.predictionLoading = false;
    },
    displayValue(val, fallback="-") { return (val===null||val===undefined||val==="") ? fallback : val; },
    getBmiBadge(cat) { const t=String(cat||"").toLowerCase(); if(t.includes("normal")) return "bg-success"; if(t.includes("severely wasted")) return "bg-danger"; if(t.includes("wasted") || t.includes("overweight")) return "bg-warning text-dark"; if(t.includes("obese")) return "bg-danger"; return "bg-secondary"; },
    getRiskBadge(r) { if(r==="Low") return "bg-success"; if(r==="Moderate") return "bg-warning text-dark"; if(r==="High") return "bg-danger"; return "bg-primary"; }
  }
}).mount("#app");
</script>
</body>
</html>
