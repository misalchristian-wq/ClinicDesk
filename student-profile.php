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

    * {
      box-sizing: border-box;
    }

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

    .wrapper {
      max-width: 1450px;
      margin: 28px auto;
      padding: 20px;
    }

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

    .header-box::before {
      content: "";
      position: absolute;
      top: -90px;
      right: -80px;
      width: 230px;
      height: 230px;
      background: rgba(255, 255, 255, 0.16);
      border-radius: 50%;
      filter: blur(4px);
    }

    .header-box::after {
      content: "";
      position: absolute;
      bottom: -110px;
      left: -80px;
      width: 220px;
      height: 220px;
      background: rgba(255, 255, 255, 0.10);
      border-radius: 50%;
      filter: blur(4px);
    }

    .header-content,
    .header-actions {
      position: relative;
      z-index: 2;
    }

    .header-icon {
      width: 62px;
      height: 62px;
      border-radius: 20px;
      background: rgba(255, 255, 255, 0.18);
      border: 2px solid rgba(255, 255, 255, 0.35);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 30px;
      margin-right: 16px;
      box-shadow: 0 0 28px rgba(255, 255, 255, 0.20);
      flex-shrink: 0;
    }

    .header-box h1 {
      font-size: 38px;
      font-weight: 900;
      margin-bottom: 8px;
      text-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    }

    .header-box p {
      font-size: 15px;
      color: rgba(255, 255, 255, 0.92);
    }

    .btn-back {
      background: white;
      color: var(--clinic-primary);
      border: none;
      border-radius: 15px;
      padding: 11px 18px;
      font-weight: 800;
      box-shadow: 0 12px 28px rgba(0,0,0,0.12);
      text-decoration: none;
      display: inline-block;
    }

    .btn-back:hover {
      background: #ecfeff;
      color: var(--clinic-primary);
    }

    .btn-green {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white;
      font-weight: 900;
      border: none;
      border-radius: 14px;
      padding: 11px 16px;
      box-shadow: 0 12px 24px rgba(15, 118, 110, 0.18);
      text-decoration: none;
      display: inline-block;
    }

    .btn-green:hover {
      color: white;
      transform: translateY(-1px);
      box-shadow: 0 14px 30px rgba(15, 118, 110, 0.22);
    }

    .btn-outline-clinic {
      border: 1px solid var(--clinic-primary);
      color: var(--clinic-primary);
      background: white;
      font-weight: 900;
      border-radius: 14px;
      padding: 10px 14px;
      text-decoration: none;
      display: inline-block;
    }

    .btn-outline-clinic:hover {
      background: var(--clinic-primary);
      color: white;
    }

    .main-grid {
      display: grid;
      grid-template-columns: 350px 1fr;
      gap: 24px;
      align-items: start;
    }

    .card-box {
      background: var(--clinic-card);
      border: 1px solid var(--clinic-border);
      border-radius: var(--clinic-radius);
      box-shadow: var(--clinic-shadow);
      padding: 24px;
      color: var(--clinic-text);
    }

    .card-box h4 {
      color: var(--clinic-primary);
      font-weight: 900;
    }

    .profile-card {
      position: sticky;
      top: 20px;
    }

    .avatar {
      width: 96px;
      height: 96px;
      border-radius: 30px;
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 34px;
      font-weight: 900;
      margin-bottom: 16px;
      box-shadow: 0 14px 28px rgba(15, 118, 110, 0.20);
    }

    .student-name {
      font-size: 24px;
      font-weight: 900;
      color: var(--clinic-text);
      margin-bottom: 4px;
    }

    .muted-text {
      color: var(--clinic-muted);
      font-size: 14px;
      line-height: 1.5;
    }

    .badge {
      border-radius: 999px;
      padding: 8px 12px;
      font-size: 12px;
      font-weight: 800;
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      padding: 12px 0;
      border-bottom: 1px solid var(--clinic-border);
    }

    .info-row:last-child {
      border-bottom: none;
    }

    .info-label {
      color: var(--clinic-muted);
      font-size: 13px;
      font-weight: 700;
    }

    .info-value {
      color: var(--clinic-text);
      font-size: 13px;
      font-weight: 800;
      text-align: right;
      word-break: break-word;
    }

    .summary-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
      margin-bottom: 24px;
    }

    .summary-card {
      background: var(--clinic-card);
      border: 1px solid var(--clinic-border);
      border-radius: 20px;
      padding: 18px;
      box-shadow: var(--clinic-shadow);
      position: relative;
      overflow: hidden;
    }

    .summary-card::after {
      content: "";
      position: absolute;
      top: -35px;
      right: -35px;
      width: 95px;
      height: 95px;
      background: rgba(20, 184, 166, 0.10);
      border-radius: 50%;
    }

    .summary-label {
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: var(--clinic-muted);
      font-weight: 800;
      margin-bottom: 6px;
      position: relative;
      z-index: 2;
    }

    .summary-value {
      font-size: 26px;
      font-weight: 900;
      color: var(--clinic-primary);
      margin-bottom: 0;
      position: relative;
      z-index: 2;
    }

    .summary-helper {
      color: var(--clinic-muted);
      font-size: 12px;
      margin-top: 4px;
      margin-bottom: 0;
      position: relative;
      z-index: 2;
    }

    .section-title {
      margin-bottom: 16px;
    }

    .section-title h3 {
      color: var(--clinic-primary);
      font-weight: 900;
      margin-bottom: 4px;
    }

    .section-title p {
      color: var(--clinic-muted);
      font-size: 14px;
      margin-bottom: 0;
    }

    .info-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 14px;
    }

    .info-box {
      background: #f8fcfd;
      border: 1px solid var(--clinic-border);
      border-radius: 16px;
      padding: 15px;
      min-height: 86px;
    }

    .info-box-label {
      font-size: 12px;
      color: var(--clinic-muted);
      text-transform: uppercase;
      font-weight: 800;
      letter-spacing: 0.5px;
      margin-bottom: 6px;
    }

    .info-box-value {
      font-size: 15px;
      font-weight: 800;
      color: var(--clinic-text);
      word-break: break-word;
      margin-bottom: 0;
    }

    .assessment-card {
      background: linear-gradient(135deg, #ecfeff, #f0fdfa);
      border: 1px solid var(--clinic-border);
      border-left: 6px solid var(--clinic-secondary);
      border-radius: 20px;
      padding: 22px;
      box-shadow: var(--clinic-shadow);
    }

    .assessment-icon {
      width: 54px;
      height: 54px;
      border-radius: 18px;
      background: white;
      border: 1px solid var(--clinic-border);
      color: var(--clinic-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 27px;
      flex-shrink: 0;
      box-shadow: 0 10px 20px rgba(15, 118, 110, 0.08);
    }

    .recommendation-box {
      background: #f8fcfd;
      border: 1px solid var(--clinic-border);
      border-radius: 16px;
      padding: 16px;
    }

    .alert {
      border-radius: 16px;
      border: none;
      box-shadow: var(--clinic-shadow);
    }

    .alert-info {
      background: #ecfeff;
      color: #155e75;
      border: 1px solid #bae6fd;
    }

    .alert-success {
      background: #dcfce7;
      color: #166534;
      border: 1px solid #bbf7d0;
    }

    .alert-danger {
      background: #fee2e2;
      color: #991b1b;
      border: 1px solid #fecaca;
    }

    .small-note {
      font-size: 0.9rem;
      color: var(--clinic-muted);
      line-height: 1.5;
    }

    .prediction-result {
      margin-top: 8px;
    }

    @media (max-width: 1200px) {
      .main-grid {
        grid-template-columns: 1fr;
      }

      .profile-card {
        position: static;
      }

      .summary-grid {
        grid-template-columns: repeat(2, 1fr);
      }

      .info-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 768px) {
      .wrapper {
        padding: 14px;
        margin: 12px auto;
      }

      .header-box {
        padding: 26px;
      }

      .header-content {
        align-items: flex-start !important;
      }

      .header-icon {
        width: 50px;
        height: 50px;
        font-size: 24px;
      }

      .header-box h1 {
        font-size: 30px;
      }

      .summary-grid,
      .info-grid {
        grid-template-columns: 1fr;
      }

      .assessment-card .d-flex {
        align-items: flex-start !important;
      }
    }
  </style>
</head>

<body>
<div id="app" class="wrapper">

  <div class="header-box d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div class="header-content d-flex align-items-center">
      <div class="header-icon">👤</div>

      <div>
        <h1>Student Profile</h1>
        <p class="mb-1">
          View individual student information, nutritional records, and assessment summary.
        </p>
        <p class="mb-0">
          Clinic Nurse: <strong>{{ nurseName }}</strong>
        </p>
      </div>
    </div>

    <div class="header-actions d-flex gap-2 flex-wrap">
      <a href="student-dashboard.php" class="btn btn-back">
        Back to Student Dashboard
      </a>
    </div>
  </div>

  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">
    {{ message }}
  </div>

  <div v-if="loading" class="alert alert-info">
    Loading student profile...
  </div>

  <div v-if="!loading" class="main-grid">

    <!-- LEFT PROFILE CARD -->
    <div class="profile-card card-box">
      <div class="avatar">{{ initials }}</div>

      <div class="student-name">{{ displayValue(student.learner_name) }}</div>

      <div class="muted-text mb-3">
        {{ displayValue(student.grade_level) }} - {{ displayValue(student.section) }}
        <br>
        {{ displayValue(student.sex) }} · {{ displayValue(student.age) }} years old
      </div>

      <div class="d-flex gap-2 flex-wrap mb-4">
        <span class="badge" :class="getBmiBadge(student.bmi_category)">
          {{ displayValue(student.bmi_category, "For Review") }}
        </span>

        <span class="badge" :class="getRiskBadge(riskLevel)">
          {{ riskLevel }} Risk
        </span>
      </div>

      <div class="info-row">
        <div class="info-label">Record ID</div>
        <div class="info-value">{{ displayValue(student.record_id) }}</div>
      </div>

      <div class="info-row">
        <div class="info-label">School Year</div>
        <div class="info-value">{{ displayValue(student.school_year) }}</div>
      </div>

      <div class="info-row">
        <div class="info-label">BMI</div>
        <div class="info-value">{{ displayValue(student.bmi) }}</div>
      </div>

      <div class="info-row">
        <div class="info-label">Weight</div>
        <div class="info-value">{{ displayValue(student.weight_kg) }} kg</div>
      </div>

      <div class="info-row">
        <div class="info-label">Height</div>
        <div class="info-value">{{ displayValue(student.height_m) }} m</div>
      </div>

      <div class="info-row">
        <div class="info-label">Height-for-Age</div>
        <div class="info-value">{{ heightForAge }}</div>
      </div>

      <div class="mt-4">
        <a :href="'health-assessment-screening.php?record_id=' + student.record_id" class="btn btn-green w-100">
          Open Health Assessment
        </a>
      </div>
    </div>

    <!-- RIGHT CONTENT -->
    <div>

      <!-- SUMMARY CARDS -->
      <div class="summary-grid">
        <div class="summary-card">
          <div class="summary-label">BMI</div>
          <p class="summary-value">{{ displayValue(student.bmi) }}</p>
          <p class="summary-helper">{{ displayValue(student.bmi_category, "For Review") }}</p>
        </div>

        <div class="summary-card">
          <div class="summary-label">Weight</div>
          <p class="summary-value">{{ displayValue(student.weight_kg) }}</p>
          <p class="summary-helper">Kilograms</p>
        </div>

        <div class="summary-card">
          <div class="summary-label">Height</div>
          <p class="summary-value">{{ displayValue(student.height_m) }}</p>
          <p class="summary-helper">Meters</p>
        </div>

        <div class="summary-card">
          <div class="summary-label">Risk Level</div>
          <p class="summary-value" style="font-size: 22px;">{{ riskLevel }}</p>
          <p class="summary-helper">Based on BMI and HFA</p>
        </div>
      </div>

      <!-- HEALTH ASSESSMENT CARD -->
      <div class="assessment-card mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <div class="d-flex gap-3 align-items-center">
            <div class="assessment-icon">🩺</div>

            <div>
              <h4 class="fw-bold mb-1">Health Assessment Screening</h4>
              <p class="small-note mb-0">
                Open the separate health assessment page to monitor symptoms, meal plan calendar,
                daily progress, and follow-up status until the student becomes healthier.
              </p>
            </div>
          </div>

          <a :href="'health-assessment-screening.php?record_id=' + student.record_id" class="btn btn-green">
            Open Screening
          </a>
        </div>
      </div>

      <!-- ML PREDICTION BUTTON -->
      <div class="card-box mb-4" v-if="student.record_id">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <div>
            <h3 class="fw-bold mb-1">🔮 Vitamin Deficiency Prediction</h3>
            <p class="small-note mb-0">
              Generate an AI-based prediction of possible vitamin deficiencies using the trained machine learning model.
            </p>
          </div>
          <button class="btn btn-green" @click="generatePrediction" :disabled="predictionLoading">
            {{ predictionLoading ? 'Analyzing...' : 'Generate Prediction' }}
          </button>
        </div>
      </div>

      <!-- GENERAL INFORMATION -->
      <div class="card-box mb-4">
        <div class="section-title">
          <h3>General Information</h3>
          <p>Basic learner information extracted from the approved SF8 record.</p>
        </div>

        <div class="info-grid">
          <div class="info-box">
            <div class="info-box-label">Learner Name</div>
            <p class="info-box-value">{{ displayValue(student.learner_name) }}</p>
          </div>

          <div class="info-box">
            <div class="info-box-label">Birthdate</div>
            <p class="info-box-value">{{ displayValue(student.birthdate) }}</p>
          </div>

          <div class="info-box">
            <div class="info-box-label">Age</div>
            <p class="info-box-value">{{ displayValue(student.age) }}</p>
          </div>

          <div class="info-box">
            <div class="info-box-label">Sex</div>
            <p class="info-box-value">{{ displayValue(student.sex) }}</p>
          </div>

          <div class="info-box">
            <div class="info-box-label">Grade Level</div>
            <p class="info-box-value">{{ displayValue(student.grade_level) }}</p>
          </div>

          <div class="info-box">
            <div class="info-box-label">Section</div>
            <p class="info-box-value">{{ displayValue(student.section) }}</p>
          </div>
        </div>
      </div>

      <!-- SCHOOL INFORMATION -->
      <div class="card-box mb-4">
        <div class="section-title">
          <h3>School Information</h3>
          <p>School and academic details connected to this student record.</p>
        </div>

        <div class="info-grid">
          <div class="info-box">
            <div class="info-box-label">School Name</div>
            <p class="info-box-value">{{ displayValue(student.school_name) }}</p>
          </div>

          <div class="info-box">
            <div class="info-box-label">School ID</div>
            <p class="info-box-value">{{ displayValue(student.school_id) }}</p>
          </div>

          <div class="info-box">
            <div class="info-box-label">District</div>
            <p class="info-box-value">{{ displayValue(student.district) }}</p>
          </div>

          <div class="info-box">
            <div class="info-box-label">Division</div>
            <p class="info-box-value">{{ displayValue(student.division) }}</p>
          </div>

          <div class="info-box">
            <div class="info-box-label">Region</div>
            <p class="info-box-value">{{ displayValue(student.region) }}</p>
          </div>

          <div class="info-box">
            <div class="info-box-label">School Year</div>
            <p class="info-box-value">{{ displayValue(student.school_year) }}</p>
          </div>
        </div>
      </div>

      <!-- HEALTH MEASUREMENTS -->
      <div class="card-box mb-4">
        <div class="section-title">
          <h3>Health Measurements</h3>
          <p>Physical measurements and nutritional classification of the student.</p>
        </div>

        <div class="info-grid">
          <div class="info-box">
            <div class="info-box-label">Weight</div>
            <p class="info-box-value">{{ displayValue(student.weight_kg) }} kg</p>
          </div>

          <div class="info-box">
            <div class="info-box-label">Height</div>
            <p class="info-box-value">{{ displayValue(student.height_m) }} m</p>
          </div>

          <div class="info-box">
            <div class="info-box-label">Height Squared</div>
            <p class="info-box-value">{{ displayValue(student.height_squared) }}</p>
          </div>

          <div class="info-box">
            <div class="info-box-label">BMI</div>
            <p class="info-box-value">{{ displayValue(student.bmi) }}</p>
          </div>

          <div class="info-box">
            <div class="info-box-label">BMI Category</div>
            <p class="info-box-value">
              <span class="badge" :class="getBmiBadge(student.bmi_category)">
                {{ displayValue(student.bmi_category, "For Review") }}
              </span>
            </p>
          </div>

          <div class="info-box">
            <div class="info-box-label">Height-for-Age</div>
            <p class="info-box-value">{{ heightForAge }}</p>
          </div>
        </div>
      </div>

      <!-- NUTRITIONAL ASSESSMENT -->
      <div class="card-box mb-4">
        <div class="section-title">
          <h3>Nutritional Assessment</h3>
          <p>Interpretation of the student's current nutritional condition.</p>
        </div>

        <div class="info-grid">
          <div class="info-box">
            <div class="info-box-label">Risk Level</div>
            <p class="info-box-value">
              <span class="badge" :class="getRiskBadge(riskLevel)">
                {{ riskLevel }}
              </span>
            </p>
          </div>

          <div class="info-box">
            <div class="info-box-label">Nutritional Status</div>
            <p class="info-box-value">{{ displayValue(student.bmi_category, "For Review") }}</p>
          </div>

          <div class="info-box">
            <div class="info-box-label">Remarks</div>
            <p class="info-box-value">{{ displayValue(student.remarks) }}</p>
          </div>
        </div>
      </div>

      <!-- RECOMMENDATION -->
      <div class="card-box">
        <div class="section-title">
          <h3>Recommendation</h3>
          <p>Basic recommendation based on the current nutritional status.</p>
        </div>

        <div class="recommendation-box">
          <h5 class="fw-bold text-success mb-2">Suggested Action</h5>
          <p class="mb-0 small-note">
            {{ recommendation }}
          </p>
        </div>
      </div>

    </div>
  </div>

  <!-- PREDICTION MODAL -->
  <div class="modal fade" id="predictionModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header" :class="predictionResult && predictionResult.predicted_risk_level === 'High' ? 'bg-danger' : 'bg-success'">
          <h5 class="modal-title fw-bold text-white">
            <i class="bi bi-robot me-2"></i>ML Prediction Result
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div v-if="predictionLoading" class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Sending data to ML model...</p>
          </div>
          <div v-else-if="predictionError" class="alert alert-danger">
            <strong>Error:</strong> {{ predictionError }}
          </div>
          <div v-else-if="predictionResult" class="prediction-result">
            <div class="row mb-3">
              <div class="col-md-6">
                <div class="border rounded p-3 bg-light">
                  <small class="text-muted">Predicted Deficiency</small>
                  <h3 class="mb-0 fw-bold text-primary">{{ predictionResult.predicted_deficiency || 'N/A' }}</h3>
                </div>
              </div>
              <div class="col-md-6">
                <div class="border rounded p-3 bg-light">
                  <small class="text-muted">Risk Level</small>
                  <h3 class="mb-0 fw-bold" :class="{
                    'text-danger': predictionResult.predicted_risk_level === 'High',
                    'text-warning': predictionResult.predicted_risk_level === 'Moderate',
                    'text-success': predictionResult.predicted_risk_level === 'Low'
                  }">{{ predictionResult.predicted_risk_level || 'N/A' }}</h3>
                </div>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <div class="border rounded p-3 bg-light">
                  <small class="text-muted">Confidence Score</small>
                  <h4 class="mb-0">{{ (predictionResult.confidence_score * 100).toFixed(1) }}%</h4>
                </div>
              </div>
              <div class="col-md-6">
                <div class="border rounded p-3 bg-light">
                  <small class="text-muted">Algorithm Used</small>
                  <h4 class="mb-0">{{ predictionResult.algorithm_used || 'Random Forest' }}</h4>
                </div>
              </div>
            </div>
            <div class="alert alert-info mt-2">
              <strong>📋 Recommendation:</strong><br>
              {{ predictionResult.recommendation_text || 'No recommendation available.' }}
            </div>
            <div class="alert alert-success mt-2" v-if="predictionResult.recommended_foods">
              <strong>🍎 Recommended Foods:</strong><br>
              {{ predictionResult.recommended_foods }}
            </div>
            <div class="alert alert-secondary mt-2" v-if="predictionResult.intervention_type">
              <strong>🏥 Intervention Type:</strong><br>
              {{ predictionResult.intervention_type }}
            </div>
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
    return {
      nurseName: "",
      recordId: "",
      loading: false,
      message: "",
      messageType: "success",

      // Prediction modal data
      predictionLoading: false,
      predictionError: null,
      predictionResult: null,

      student: {
        record_id: "",
        learner_name: "",
        birthdate: "",
        age: "",
        sex: "",
        school_name: "",
        school_id: "",
        district: "",
        division: "",
        region: "",
        grade_level: "",
        section: "",
        track_strand: "",
        school_year: "",
        weight_kg: "",
        height_m: "",
        height_squared: "",
        bmi: "",
        bmi_category: "",
        height_for_age: "",
        height_for_age_status: "",
        remarks: "",
        risk_level: "",
        recommendation: ""
      }
    };
  },

  computed: {
    initials() {
      if (!this.student.learner_name) return "?";

      return this.student.learner_name
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map(part => part.charAt(0).toUpperCase())
        .join("");
    },

    heightForAge() {
      return this.student.height_for_age_status ||
             this.student.height_for_age ||
             this.student.hfa_status ||
             "-";
    },

    riskLevel() {
      if (this.student.risk_level) {
        return this.student.risk_level;
      }

      const bmiCategory = String(this.student.bmi_category || "").toLowerCase();
      const hfa = String(this.heightForAge || "").toLowerCase();

      if (
        bmiCategory.includes("severely") ||
        bmiCategory.includes("obese") ||
        hfa.includes("severely")
      ) {
        return "High";
      }

      if (
        bmiCategory.includes("underweight") ||
        bmiCategory.includes("overweight") ||
        hfa.includes("stunted")
      ) {
        return "Moderate";
      }

      if (
        bmiCategory.includes("normal") &&
        (hfa.includes("normal") || hfa === "-")
      ) {
        return "Low";
      }

      return "For Review";
    },

    recommendation() {
      if (this.student.recommendation) {
        return this.student.recommendation;
      }

      const bmiCategory = String(this.student.bmi_category || "").toLowerCase();

      if (this.riskLevel === "High") {
        return "Priority clinic follow-up is recommended. The student should be monitored closely and may need parent or guardian notification.";
      }

      if (bmiCategory.includes("underweight")) {
        return "Monitor weight regularly and encourage balanced meals with protein-rich food, fruits, vegetables, and healthy snacks.";
      }

      if (bmiCategory.includes("overweight") || bmiCategory.includes("obese")) {
        return "Encourage healthy food choices, regular physical activity, and routine monitoring of BMI and lifestyle habits.";
      }

      if (this.riskLevel === "Moderate") {
        return "Continue regular monitoring and schedule a follow-up assessment to check if the student's nutritional status improves.";
      }

      if (this.riskLevel === "Low") {
        return "Continue routine nutritional monitoring and maintain balanced meals and healthy habits.";
      }

      return "For clinic review. Additional health assessment screening may be needed to provide a more accurate recommendation.";
    }
  },

  mounted() {
    const role = localStorage.getItem("active_role");
    const accountId = localStorage.getItem("local_account_id");

    if (role !== "Clinic Nurse" || !accountId) {
      window.location.href = "login.php";
      return;
    }

    this.nurseName = localStorage.getItem("local_full_name") || "Clinic Nurse";

    const params = new URLSearchParams(window.location.search);
    this.recordId = params.get("record_id") || "";

    if (!this.recordId) {
      this.showMessage("error", "No student record ID provided.");
      return;
    }

    this.loadStudentProfile();
  },

  methods: {
    showMessage(type, text) {
      this.messageType = type;
      this.message = text;

      setTimeout(() => {
        this.message = "";
      }, 5000);
    },

    async loadStudentProfile() {
      this.loading = true;
      this.message = "";

      try {
        const response = await fetch("api/get_student_profile.php?record_id=" + encodeURIComponent(this.recordId) + "&cache_buster=" + Date.now());
        const text = await response.text();
        console.log("Student profile raw response:", text);

        let result;
        try {
          result = JSON.parse(text);
        } catch (jsonError) {
          this.showMessage("error", "Student profile API did not return JSON. Check api/get_student_profile.php.");
          this.loading = false;
          return;
        }

        if (result.success) {
          this.student = result.student || result.profile || result.record || {};
        } else {
          this.showMessage("error", result.message || "Failed to load student profile.");
        }
      } catch (error) {
        this.showMessage("error", "Error loading student profile: " + error.message);
      }

      this.loading = false;
    },

    async generatePrediction() {
      if (!this.student.record_id) {
        this.showMessage("error", "No student record ID found.");
        return;
      }

      this.predictionLoading = true;
      this.predictionError = null;
      this.predictionResult = null;

      // Show modal immediately with loading state
      const modalElement = document.getElementById("predictionModal");
      const modal = new bootstrap.Modal(modalElement);
      modal.show();

      try {
        const response = await fetch("api/generate_student_prediction.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ record_id: this.student.record_id })
        });

        const data = await response.json();

        if (data.success) {
          this.predictionResult = data.prediction;
        } else {
          this.predictionError = data.message || "Prediction failed. Make sure the ML API is running.";
        }
      } catch (error) {
        this.predictionError = "Network error: " + error.message;
      } finally {
        this.predictionLoading = false;
      }
    },

    displayValue(value, fallback = "-") {
      if (value === null || value === undefined || value === "") {
        return fallback;
      }
      return value;
    },

    getBmiBadge(category) {
      const text = String(category || "").toLowerCase();

      if (text.includes("normal")) return "bg-success";
      if (text.includes("severely")) return "bg-danger";
      if (text.includes("underweight")) return "bg-warning text-dark";
      if (text.includes("overweight")) return "bg-warning text-dark";
      if (text.includes("obese")) return "bg-danger";

      return "bg-secondary";
    },

    getRiskBadge(risk) {
      if (risk === "Low") return "bg-success";
      if (risk === "Moderate") return "bg-warning text-dark";
      if (risk === "High") return "bg-danger";
      return "bg-primary";
    }
  }
}).mount("#app");
</script>
</body>
</html>