<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | Health Assessment</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root {
      --p:  #0f766e;
      --p2: #14b8a6;
      --acc:#0ea5e9;
      --bg: #eef8fb;
      --card:#ffffff;
      --bdr:#d9eef0;
      --txt:#16323f;
      --mut:#6b7d87;
      --sh: 0 8px 28px rgba(15,118,110,0.09);
      --r:  20px;
    }
    *{box-sizing:border-box;margin:0;padding:0;}
    body{min-height:100vh;background:radial-gradient(circle at 10% 0%,rgba(20,184,166,.14),transparent 30%),radial-gradient(circle at 90% 0%,rgba(14,165,233,.10),transparent 30%),linear-gradient(160deg,#eef8fb,#f8fcfd);font-family:'Plus Jakarta Sans',system-ui,sans-serif;color:var(--txt);}
    .wrap{max-width:1480px;margin:0 auto;padding:24px 20px 60px;}

    /* ── header ── */
    .page-header{background:linear-gradient(135deg,var(--p),var(--p2));color:#fff;padding:28px 32px;border-radius:26px;margin-bottom:24px;box-shadow:0 16px 40px rgba(15,118,110,.22);display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;position:relative;overflow:hidden;}
    .page-header::before{content:'';position:absolute;top:-70px;right:-60px;width:200px;height:200px;background:rgba(255,255,255,.12);border-radius:50%;}
    .ph-icon{width:56px;height:56px;border-radius:16px;background:rgba(255,255,255,.18);border:2px solid rgba(255,255,255,.3);display:flex;align-items:center;justify-content:center;font-size:26px;flex-shrink:0;}
    .page-header h1{font-size:26px;font-weight:900;margin-bottom:3px;}
    .page-header p{font-size:13.5px;color:rgba(255,255,255,.88);margin:0;}
    .btn-back{background:#fff;color:var(--p);border:none;border-radius:12px;padding:9px 16px;font-weight:800;font-size:13px;text-decoration:none;box-shadow:0 6px 18px rgba(0,0,0,.10);}
    .btn-back:hover{background:#ecfeff;color:var(--p);}

    /* ── layout ── */
    .canvas{display:grid;grid-template-columns:320px 1fr;gap:20px;align-items:start;}
    @media(max-width:1100px){.canvas{grid-template-columns:1fr;}}

    /* ── sidebar ── */
    .sidebar{position:sticky;top:20px;display:flex;flex-direction:column;gap:16px;}
    .card{background:var(--card);border:1px solid var(--bdr);border-radius:var(--r);box-shadow:var(--sh);padding:22px;}

    /* profile */
    .avatar{width:80px;height:80px;border-radius:22px;background:linear-gradient(135deg,var(--p),var(--p2));color:#fff;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:900;margin-bottom:14px;box-shadow:0 10px 22px rgba(15,118,110,.22);}
    .s-name{font-size:20px;font-weight:900;margin-bottom:3px;}
    .s-sub{font-size:12.5px;color:var(--mut);line-height:1.5;margin-bottom:14px;}
    .vitals{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:16px;}
    .vital-box{background:#f0fdfa;border:1px solid #99f6e4;border-radius:12px;padding:10px 12px;text-align:center;}
    .vital-box .vb-val{font-size:18px;font-weight:900;color:var(--p);line-height:1;}
    .vital-box .vb-lbl{font-size:11px;color:var(--mut);margin-top:3px;font-weight:600;}
    .bmi-badge{display:inline-block;border-radius:999px;padding:5px 12px;font-size:12px;font-weight:800;margin-top:6px;}

    /* ── prediction panel (sidebar) ── */
    .predict-card{background:linear-gradient(145deg,#f0fdfa,#fff);border:1px solid #99f6e4;}
    .predict-card .pc-label{font-size:11px;font-weight:700;color:var(--mut);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;}
    .predict-card .pc-value{font-size:22px;font-weight:900;color:var(--p);line-height:1.1;margin-bottom:10px;}
    .risk-pill{display:inline-flex;align-items:center;gap:6px;border-radius:999px;padding:6px 14px;font-size:13px;font-weight:800;}
    .risk-high{background:#fee2e2;color:#991b1b;}
    .risk-moderate{background:#fef3c7;color:#92400e;}
    .risk-low{background:#dcfce7;color:#166534;}
    .conf-bar-wrap{background:#e5f5f0;border-radius:8px;height:8px;overflow:hidden;margin:8px 0;}
    .conf-bar{height:100%;background:linear-gradient(90deg,var(--p),var(--p2));border-radius:8px;transition:width .6s ease;}
    .food-list{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;}
    .food-tag{background:#f0fdfa;border:1px solid #99f6e4;border-radius:8px;padding:3px 9px;font-size:11.5px;color:var(--p);font-weight:600;}
    .btn-predict{width:100%;background:linear-gradient(135deg,var(--p),var(--p2));color:#fff;border:none;border-radius:12px;padding:11px;font-weight:800;font-size:13.5px;cursor:pointer;box-shadow:0 8px 18px rgba(15,118,110,.18);transition:transform .15s,box-shadow .15s;}
    .btn-predict:hover:not(:disabled){transform:translateY(-1px);box-shadow:0 12px 24px rgba(15,118,110,.22);}
    .btn-predict:disabled{opacity:.6;cursor:not-allowed;transform:none;}
    .predict-spinner{display:inline-block;width:14px;height:14px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;margin-right:6px;}
    @keyframes spin{to{transform:rotate(360deg);}}
    .algo-chip{background:#f0fdfa;border:1px solid #99f6e4;border-radius:8px;padding:4px 10px;font-size:11.5px;color:var(--p);font-weight:700;display:inline-block;margin-top:6px;}
    .predict-date{font-size:11px;color:var(--mut);margin-top:4px;}
    .no-predict{text-align:center;padding:14px 0;color:var(--mut);font-size:13px;}
    .no-predict i{font-size:28px;display:block;margin-bottom:6px;color:#b2d8d8;}

    /* ── main area ── */
    .main-col{display:flex;flex-direction:column;gap:16px;}

    /* section cards */
    .sec-head{display:flex;align-items:center;gap:10px;margin-bottom:16px;}
    .sec-icon{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,var(--p),var(--p2));color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;}
    .sec-head h3{font-size:15px;font-weight:800;color:var(--p);margin:0;}
    .sec-head p{font-size:12.5px;color:var(--mut);margin:0;}

    /* form controls */
    .form-label{font-size:13px;font-weight:700;color:var(--txt);margin-bottom:5px;display:block;}
    .form-control,.form-select{border-radius:11px;border:1px solid var(--bdr);padding:9px 12px;font-size:13.5px;background:#fff;color:var(--txt);font-family:inherit;width:100%;}
    .form-control:focus,.form-select:focus{border-color:var(--p2);box-shadow:0 0 0 3px rgba(20,184,166,.14);outline:none;}
    textarea.form-control{resize:vertical;min-height:72px;}

    /* symptom grid */
    .sym-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:8px;}
    .sym-chip{display:flex;align-items:center;gap:8px;background:#f8fcfd;border:1.5px solid var(--bdr);border-radius:10px;padding:9px 12px;cursor:pointer;transition:border-color .15s,background .15s;user-select:none;}
    .sym-chip:hover{border-color:var(--p2);background:#f0fdfa;}
    .sym-chip.active{border-color:var(--p);background:#e0f7f4;}
    .sym-chip input[type=checkbox]{accent-color:var(--p);width:15px;height:15px;flex-shrink:0;}
    .sym-chip span{font-size:13px;font-weight:600;color:var(--txt);}

    /* illness chips for consultation */
    .ill-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:8px;}
    .ill-chip{display:flex;align-items:center;gap:8px;background:#f8fcfd;border:1.5px solid var(--bdr);border-radius:10px;padding:8px 11px;cursor:pointer;transition:border-color .15s,background .15s;user-select:none;}
    .ill-chip:hover{border-color:var(--acc);background:#f0f9ff;}
    .ill-chip.active{border-color:var(--acc);background:#e0f2fe;}
    .ill-chip input[type=checkbox]{accent-color:var(--acc);width:15px;height:15px;flex-shrink:0;}
    .ill-chip span{font-size:12.5px;font-weight:600;color:var(--txt);}

    /* medication card */
    .med-card{background:#f0fdfa;border-left:3px solid var(--p);border-radius:0 12px 12px 0;padding:14px 16px;margin-top:12px;}
    .med-card h5{font-size:13px;font-weight:800;color:var(--p);margin-bottom:8px;}
    .med-item{font-size:13px;color:var(--txt);line-height:1.6;padding:3px 0;}

    /* toggle row for sections */
    .toggle-head{display:flex;justify-content:space-between;align-items:center;cursor:pointer;-webkit-tap-highlight-color:transparent;}
    .toggle-head .chevron{color:var(--mut);transition:transform .2s;}
    .toggle-head .chevron.open{transform:rotate(180deg);}

    /* family history */
    .fam-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:8px;}
    .fam-chip{display:flex;align-items:center;gap:8px;background:#f8fcfd;border:1.5px solid var(--bdr);border-radius:10px;padding:9px 12px;cursor:pointer;user-select:none;transition:border-color .15s,background .15s;}
    .fam-chip.active{border-color:#f59e0b;background:#fffbeb;}
    .fam-chip input[type=checkbox]{accent-color:#d97706;width:15px;height:15px;flex-shrink:0;}
    .fam-chip span{font-size:13px;font-weight:600;color:var(--txt);}

    /* alert */
    .alert{border-radius:14px;border:none;padding:12px 16px;font-size:13.5px;font-weight:600;margin-bottom:16px;}
    .alert-success{background:#dcfce7;color:#166534;}
    .alert-danger{background:#fee2e2;color:#991b1b;}

    /* buttons */
    .btn-primary-action{background:linear-gradient(135deg,var(--p),var(--p2));color:#fff;border:none;border-radius:12px;padding:11px 24px;font-weight:800;font-size:14px;cursor:pointer;box-shadow:0 8px 18px rgba(15,118,110,.18);transition:transform .15s;}
    .btn-primary-action:hover:not(:disabled){transform:translateY(-1px);}
    .btn-primary-action:disabled{opacity:.6;cursor:not-allowed;transform:none;}
    .btn-secondary-action{background:#fff;color:var(--p);border:1.5px solid var(--p2);border-radius:12px;padding:10px 20px;font-weight:700;font-size:13.5px;cursor:pointer;transition:background .15s;}
    .btn-secondary-action:hover{background:#f0fdfa;}

    /* divider */
    .divider{height:1px;background:var(--bdr);margin:14px 0;}

    /* toast */
    .toast-wrap{position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none;}
    .toast-item{background:#fff;border-radius:14px;padding:13px 18px;box-shadow:0 12px 32px rgba(0,0,0,.14);border-left:4px solid var(--p);font-size:13.5px;font-weight:600;color:var(--txt);max-width:340px;pointer-events:all;animation:slideIn .3s ease;}
    .toast-item.error{border-color:#dc2626;color:#991b1b;}
    .toast-item.success{border-color:#16a34a;color:#166534;}
    @keyframes slideIn{from{transform:translateX(120%);opacity:0;}to{transform:translateX(0);opacity:1;}}

    /* recommendation text box */
    .rec-box{background:#f8fcfd;border:1px solid #99f6e4;border-radius:12px;padding:12px 14px;font-size:13px;color:var(--txt);line-height:1.7;margin-top:8px;}
    .int-badge{display:inline-block;background:#e0f7f4;color:var(--p);border-radius:8px;padding:3px 10px;font-size:12px;font-weight:700;margin-top:6px;}

    /* no-save note */
    .save-row{display:flex;justify-content:flex-end;gap:10px;align-items:center;flex-wrap:wrap;}
    .save-hint{font-size:12px;color:var(--mut);}

    @media(max-width:640px){
      .sym-grid{grid-template-columns:1fr 1fr;}
      .page-header h1{font-size:20px;}
      .save-row{justify-content:stretch;}
      .btn-primary-action,.btn-secondary-action{width:100%;}
    }
  </style>
</head>
<body>
<div id="app" class="wrap">

  <!-- Toast notifications -->
  <div class="toast-wrap">
    <div v-for="t in toasts" :key="t.id" :class="['toast-item', t.type]">{{ t.msg }}</div>
  </div>

  <!-- Header -->
  <div class="page-header">
    <div style="display:flex;align-items:center;gap:14px;position:relative;z-index:2;">
      <div class="ph-icon">🩺</div>
      <div>
        <h1>Health Assessment</h1>
        <p>{{ student.learner_name || 'Loading student...' }} &nbsp;·&nbsp; Nurse: {{ nurseName }}</p>
      </div>
    </div>
    <a href="student-dashboard.php" class="btn-back" style="position:relative;z-index:2;">← Dashboard</a>
  </div>

  <div class="canvas">

    <!-- ══════════ SIDEBAR ══════════ -->
    <div class="sidebar">

      <!-- Student profile -->
      <div class="card">
        <div class="avatar">{{ initials }}</div>
        <div class="s-name">{{ student.learner_name || '—' }}</div>
        <div class="s-sub">{{ student.grade_level || '—' }} – {{ student.section || '—' }} &nbsp;·&nbsp; {{ student.sex || '—' }} &nbsp;·&nbsp; {{ student.age || '—' }} yrs</div>
        <div class="vitals">
          <div class="vital-box">
            <div class="vb-val">{{ student.bmi || '—' }}</div>
            <div class="vb-lbl">BMI</div>
          </div>
          <div class="vital-box">
            <div class="vb-val">{{ student.weight_kg || '—' }}</div>
            <div class="vb-lbl">kg</div>
          </div>
          <div class="vital-box">
            <div class="vb-val">{{ student.height_m || '—' }}</div>
            <div class="vb-lbl">m</div>
          </div>
          <div class="vital-box">
            <div class="vb-val" style="font-size:13px;">{{ heightForAge }}</div>
            <div class="vb-lbl">HFA</div>
          </div>
        </div>
        <div>
          <span class="bmi-badge" :style="bmiBadgeStyle">{{ student.bmi_category || 'For Review' }}</span>
        </div>
      </div>

      <!-- ML Prediction panel -->
      <div class="card predict-card">
        <div class="sec-head" style="margin-bottom:14px;">
          <div class="sec-icon">🤖</div>
          <div>
            <h3 style="margin:0;">ML Prediction</h3>
            <p style="margin:0;">Nutritional deficiency & risk</p>
          </div>
        </div>

        <!-- No prediction yet -->
        <div v-if="!prediction && !predicting" class="no-predict">
          <i class="bi bi-robot"></i>
          Save the health assessment first, then run the prediction.
        </div>

        <!-- Loading -->
        <div v-if="predicting" style="text-align:center;padding:18px 0;">
          <div style="display:inline-block;width:32px;height:32px;border:3px solid #d1fae5;border-top-color:var(--p);border-radius:50%;animation:spin .7s linear infinite;"></div>
          <p style="margin-top:10px;font-size:13px;color:var(--mut);">Analysing health data...</p>
        </div>

        <!-- Result -->
        <div v-if="prediction && !predicting">
          <div class="pc-label">Predicted deficiency</div>
          <div class="pc-value">{{ prediction.predicted_deficiency || '—' }}</div>

          <div class="pc-label">Risk level</div>
          <div class="risk-pill" :class="riskClass" style="margin-bottom:12px;">
            <i class="bi" :class="riskIcon"></i>
            {{ prediction.predicted_risk_level || '—' }}
          </div>

          <div class="pc-label">Confidence</div>
          <div class="conf-bar-wrap"><div class="conf-bar" :style="{width: confPct + '%'}"></div></div>
          <div style="font-size:12px;color:var(--mut);margin-bottom:12px;">{{ confPct }}%</div>

          <div v-if="prediction.recommendation_text">
            <div class="pc-label">Recommendation</div>
            <div class="rec-box">{{ prediction.recommendation_text }}</div>
          </div>

          <div v-if="prediction.recommended_foods">
            <div class="pc-label" style="margin-top:10px;">Recommended foods</div>
            <div class="food-list">
              <span class="food-tag" v-for="food in foodList" :key="food">{{ food }}</span>
            </div>
          </div>

          <div v-if="prediction.intervention_type">
            <span class="int-badge">{{ prediction.intervention_type }}</span>
          </div>

          <div class="divider"></div>
          <div class="algo-chip"><i class="bi bi-cpu me-1"></i>{{ prediction.algorithm_used || 'ML Model' }}</div>
          <div class="predict-date" v-if="prediction.prediction_date">{{ formatDate(prediction.prediction_date) }}</div>
        </div>

        <!-- Run button -->
        <div style="margin-top:14px;">
          <button class="btn-predict" @click="runPrediction" :disabled="predicting || !assessmentSaved">
            <span v-if="predicting"><span class="predict-spinner"></span>Running...</span>
            <span v-else><i class="bi bi-stars me-1"></i>{{ prediction ? 'Re-run Prediction' : 'Run ML Prediction' }}</span>
          </button>
          <p v-if="!assessmentSaved" style="font-size:11.5px;color:var(--mut);text-align:center;margin-top:6px;">Save assessment first to enable prediction</p>
        </div>
      </div>

    </div><!-- /sidebar -->

    <!-- ══════════ MAIN COLUMN ══════════ -->
    <div class="main-col">

      <!-- ── Quick Consultation ── -->
      <div class="card">
        <div class="sec-head">
          <div class="sec-icon" style="background:linear-gradient(135deg,#0ea5e9,#38bdf8);">💊</div>
          <div>
            <h3>Quick Consultation</h3>
            <p>Select illnesses for instant medication guidance, then save the record.</p>
          </div>
        </div>

        <label class="form-label">Common Illnesses</label>
        <div class="ill-grid">
          <label class="ill-chip" :class="{active: consultForm.illnesses[ill.key]}" v-for="ill in commonIllnesses" :key="ill.key">
            <input type="checkbox" v-model="consultForm.illnesses[ill.key]">
            <span>{{ ill.label }}</span>
          </label>
        </div>

        <div style="margin-top:14px;">
          <label class="form-label">Additional notes / other symptoms</label>
          <textarea class="form-control" rows="2" v-model="consultForm.notes" placeholder="Describe other symptoms..."></textarea>
        </div>

        <div class="med-card" v-if="medicationRecommendation.length">
          <h5><i class="bi bi-capsule me-1"></i>Medication Recommendation</h5>
          <div class="med-item" v-for="m in medicationRecommendation" :key="m.label">
            <strong>{{ m.label }}:</strong> {{ m.med }}
          </div>
        </div>

        <div style="margin-top:14px;display:flex;justify-content:flex-end;">
          <button class="btn-primary-action" @click="saveConsultation" :disabled="consultSaving" style="background:linear-gradient(135deg,#0ea5e9,#38bdf8);">
            <span v-if="consultSaving"><span class="predict-spinner"></span>Saving...</span>
            <span v-else><i class="bi bi-save me-1"></i>Save Consultation</span>
          </button>
        </div>
      </div>

      <!-- ── Lifestyle & General Health ── -->
      <div class="card">
        <div class="sec-head">
          <div class="sec-icon">🥗</div>
          <div><h3>Lifestyle & General Health</h3><p>These fields feed directly into the ML prediction.</p></div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;">
          <div>
            <label class="form-label">Diet type</label>
            <select class="form-select" v-model="form.diet_type">
              <option value="">Select</option>
              <option>Balanced</option><option>Vegetarian</option>
              <option>High protein</option><option>Low calorie</option><option>Other</option>
            </select>
          </div>
          <div>
            <label class="form-label">Sun exposure</label>
            <select class="form-select" v-model="form.sun_exposure">
              <option value="">Select</option>
              <option>Low</option><option>Moderate</option><option>High</option>
            </select>
          </div>
          <div>
            <label class="form-label">Exercise level</label>
            <select class="form-select" v-model="form.exercise_level">
              <option value="">Select</option>
              <option>Sedentary</option><option>Light</option><option>Moderate</option><option>Active</option>
            </select>
          </div>
          <div>
            <label class="form-label">Immunization status</label>
            <select class="form-select" v-model="form.immunization_updated">
              <option>Unknown</option><option>Up to date</option>
              <option>Partial</option><option>Not started</option>
            </select>
          </div>
          <div>
            <label class="form-label">Known allergy</label>
            <select class="form-select" v-model="form.has_known_allergy">
              <option>No</option><option>Yes</option>
            </select>
          </div>
          <div v-if="form.has_known_allergy === 'Yes'">
            <label class="form-label">Allergy details</label>
            <input type="text" class="form-control" v-model="form.allergy_details" placeholder="e.g. peanuts, pollen">
          </div>
        </div>
      </div>

      <!-- ── Observed Symptoms ── -->
      <div class="card">
        <div class="toggle-head sec-head" @click="showSymptoms = !showSymptoms" style="margin-bottom:0;cursor:pointer;">
          <div style="display:flex;align-items:center;gap:10px;">
            <div class="sec-icon">📋</div>
            <div><h3>Observed Symptoms</h3><p>{{ activeSymptomCount }} symptom{{ activeSymptomCount !== 1 ? 's' : '' }} checked &nbsp;·&nbsp; Used in ML model</p></div>
          </div>
          <i class="bi bi-chevron-down chevron" :class="{open: showSymptoms}"></i>
        </div>
        <div v-show="showSymptoms" class="sym-grid" style="margin-top:16px;">
          <label class="sym-chip" :class="{active: form[sym.key]}" v-for="sym in symptomFields" :key="sym.key">
            <input type="checkbox" v-model="form[sym.key]">
            <span>{{ sym.label }}</span>
          </label>
        </div>
      </div>

      <!-- ── Family History ── -->
      <div class="card">
        <div class="toggle-head sec-head" @click="showFamily = !showFamily" style="margin-bottom:0;cursor:pointer;">
          <div style="display:flex;align-items:center;gap:10px;">
            <div class="sec-icon" style="background:linear-gradient(135deg,#f59e0b,#fbbf24);">👨‍👩‍👧</div>
            <div><h3>Family History</h3><p>Hereditary conditions</p></div>
          </div>
          <i class="bi bi-chevron-down chevron" :class="{open: showFamily}"></i>
        </div>
        <div v-show="showFamily" class="fam-grid" style="margin-top:16px;">
          <label class="fam-chip" :class="{active: form.family_history_diabetes}">
            <input type="checkbox" v-model="form.family_history_diabetes"><span>Diabetes</span>
          </label>
          <label class="fam-chip" :class="{active: form.family_history_heart_disease}">
            <input type="checkbox" v-model="form.family_history_heart_disease"><span>Heart Disease</span>
          </label>
          <label class="fam-chip" :class="{active: form.family_history_anemia}">
            <input type="checkbox" v-model="form.family_history_anemia"><span>Anemia</span>
          </label>
        </div>
      </div>

      <!-- ── Medical Conditions & Follow-up ── -->
      <div class="card">
        <div class="sec-head">
          <div class="sec-icon" style="background:linear-gradient(135deg,#7c3aed,#a78bfa);">📝</div>
          <div><h3>Medical Conditions & Follow-up</h3><p>Existing conditions, notes, and referral flags.</p></div>
        </div>
        <div style="display:grid;gap:12px;">
          <div>
            <label class="form-label">Existing medical condition</label>
            <textarea class="form-control" rows="2" v-model="form.existing_medical_condition" placeholder="e.g. asthma, hypertension"></textarea>
          </div>
          <div>
            <label class="form-label">Clinic notes / remarks</label>
            <textarea class="form-control" rows="2" v-model="form.clinic_notes"></textarea>
          </div>
          <div style="display:flex;gap:16px;flex-wrap:wrap;">
            <label class="sym-chip" :class="{active: form.needs_followup}" style="flex:1;min-width:140px;">
              <input type="checkbox" v-model="form.needs_followup"><span>Needs Follow-up</span>
            </label>
            <label class="sym-chip" :class="{active: form.needs_referral}" style="flex:1;min-width:140px;">
              <input type="checkbox" v-model="form.needs_referral"><span>Needs Referral</span>
            </label>
          </div>
        </div>
      </div>

      <!-- ── Save bar ── -->
      <div class="card" style="padding:16px 22px;">
        <div class="save-row">
          <span class="save-hint" v-if="assessmentSaved">✅ Assessment saved — you can now run the ML prediction.</span>
          <span class="save-hint" v-else>Fill in the form above, then save.</span>
          <button class="btn-primary-action" @click="saveHealthAssessment" :disabled="saving">
            <span v-if="saving"><span class="predict-spinner"></span>Saving...</span>
            <span v-else><i class="bi bi-floppy me-1"></i>Save Health Assessment</span>
          </button>
        </div>
      </div>

    </div><!-- /main-col -->
  </div><!-- /canvas -->
</div><!-- /app -->

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script>
const { createApp } = Vue;

createApp({
  data() {
    return {
      nurseName: '',
      recordId: '',
      student: {},
      saving: false,
      consultSaving: false,
      predicting: false,
      assessmentSaved: false,
      prediction: null,
      toasts: [],
      toastId: 0,
      showSymptoms: true,
      showFamily: false,

      commonIllnesses: [
        { key: 'fever',       label: 'Fever',       med: 'Paracetamol 500mg every 6h as needed. Monitor temperature.' },
        { key: 'headache',    label: 'Headache',    med: 'Paracetamol or Ibuprofen 200–400mg. Rest in a quiet room.' },
        { key: 'cough',       label: 'Cough',       med: 'Carbocisteine (productive) or Dextromethorphan (dry). Warm honey-lemon drink.' },
        { key: 'colds',       label: 'Colds',       med: 'Antihistamine (Loratadine or Cetirizine). Increase fluid intake. Rest.' },
        { key: 'sore_throat', label: 'Sore Throat', med: 'Warm salt-water gargle. Lozenges. Paracetamol for pain.' },
        { key: 'stomachache', label: 'Stomachache', med: 'Antacid (Kremil-S). Avoid spicy food. Consult if severe.' },
      ],
      consultForm: { illnesses: { fever:false, headache:false, cough:false, colds:false, sore_throat:false, stomachache:false }, notes: '' },

      symptomFields: [
        { key:'has_fatigue',           label:'Fatigue' },
        { key:'has_bone_pain',         label:'Bone pain' },
        { key:'has_bleeding_gums',     label:'Bleeding gums' },
        { key:'has_pale_skin',         label:'Pale skin' },
        { key:'has_night_blindness',   label:'Night blindness' },
        { key:'has_low_appetite',      label:'Low appetite' },
        { key:'has_irregular_meals',   label:'Irregular meals' },
        { key:'has_weight_changes',    label:'Unexplained weight changes' },
        { key:'has_headache',          label:'Frequent headaches' },
        { key:'has_poor_concentration',label:'Poor concentration' },
        { key:'has_vision_problem',    label:'Vision problems' },
        { key:'has_hearing_problem',   label:'Hearing problems' },
        { key:'has_dental_problem',    label:'Dental problems' },
        { key:'has_skin_problem',      label:'Skin problems' },
        { key:'has_breathing_problem', label:'Breathing difficulties' },
        { key:'has_recent_illness',    label:'Recent illness' },
        { key:'has_current_medication',label:'Current medication' },
      ],

      form: {
        diet_type:'', sun_exposure:'', exercise_level:'',
        has_fatigue:false, has_bone_pain:false, has_bleeding_gums:false, has_pale_skin:false,
        has_night_blindness:false, has_low_appetite:false, has_irregular_meals:false, has_weight_changes:false,
        has_headache:false, has_poor_concentration:false, has_vision_problem:false, has_hearing_problem:false,
        has_dental_problem:false, has_skin_problem:false, has_breathing_problem:false,
        has_recent_illness:false, has_current_medication:false,
        immunization_updated:'Unknown', has_known_allergy:'No', allergy_details:'',
        family_history_diabetes:false, family_history_heart_disease:false, family_history_anemia:false,
        existing_medical_condition:'', needs_followup:false, needs_referral:false, clinic_notes:''
      }
    };
  },

  computed: {
    initials() {
      if (!this.student.learner_name) return '?';
      return this.student.learner_name.split(' ').filter(Boolean).slice(0,2).map(p=>p[0].toUpperCase()).join('');
    },
    heightForAge() {
      return this.student.height_for_age_status || this.student.height_for_age || this.student.hfa_status || '—';
    },
    bmiBadgeStyle() {
      const cat = (this.student.bmi_category || '').toLowerCase();
      if (cat.includes('severely') || cat.includes('obese')) return 'background:#fee2e2;color:#991b1b;';
      if (cat.includes('wasted') || cat.includes('overweight')) return 'background:#fef3c7;color:#92400e;';
      if (cat.includes('normal')) return 'background:#dcfce7;color:#166534;';
      return 'background:#f1f5f9;color:#475569;';
    },
    activeSymptomCount() {
      return this.symptomFields.filter(s => this.form[s.key]).length;
    },
    medicationRecommendation() {
      return this.commonIllnesses.filter(i => this.consultForm.illnesses[i.key]);
    },
    riskClass() {
      const r = (this.prediction?.predicted_risk_level || '').toLowerCase();
      return r === 'high' ? 'risk-high' : r === 'moderate' ? 'risk-moderate' : 'risk-low';
    },
    riskIcon() {
      const r = (this.prediction?.predicted_risk_level || '').toLowerCase();
      return r === 'high' ? 'bi-exclamation-triangle-fill' : r === 'moderate' ? 'bi-exclamation-circle-fill' : 'bi-check-circle-fill';
    },
    confPct() {
      const c = parseFloat(this.prediction?.confidence_score || 0);
      return Math.round((c > 1 ? c : c * 100));
    },
    foodList() {
      return (this.prediction?.recommended_foods || '').split(',').map(f=>f.trim()).filter(Boolean);
    }
  },

  async mounted() {
    const role = localStorage.getItem('active_role');
    const acct = localStorage.getItem('local_account_id');
    if (role !== 'Clinic Nurse' || !acct) { window.location.href = 'login.php'; return; }
    this.nurseName = localStorage.getItem('local_full_name') || 'Clinic Nurse';

    const params = new URLSearchParams(window.location.search);
    this.recordId = params.get('record_id');
    if (!this.recordId) { this.toast('error', 'No student record ID provided.'); return; }

    await this.loadStudentProfile();
    await this.loadHealthAssessment();
    await this.loadLatestPrediction();
  },

  methods: {
    toast(type, msg, duration = 4000) {
      const id = ++this.toastId;
      this.toasts.push({ id, type, msg });
      setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, duration);
    },

    async loadStudentProfile() {
      try {
        const res = await fetch(`api/get_student_profile.php?record_id=${this.recordId}&t=${Date.now()}`);
        const d = await res.json();
        if (d.success) this.student = d.student || {};
        else this.toast('error', d.message || 'Failed to load student.');
      } catch(e) { this.toast('error', 'Network error: ' + e.message); }
    },

    async loadHealthAssessment() {
      try {
        const res = await fetch(`api/get_health_assessment.php?record_id=${this.recordId}`);
        const d = await res.json();
        if (d.success && d.health_input) {
          Object.keys(this.form).forEach(key => {
            if (d.health_input.hasOwnProperty(key)) {
              const val = d.health_input[key];
              this.form[key] = typeof this.form[key] === 'boolean'
                ? (val === 'Yes' || val === 1 || val === true)
                : (val !== null ? val : '');
            }
          });
          this.assessmentSaved = true;
        }
      } catch(e) { console.warn('No existing assessment', e); }
    },

    async loadLatestPrediction() {
      try {
        const res = await fetch(`api/get_student_prediction.php?record_id=${this.recordId}`);
        const d = await res.json();
        if (d.success && d.prediction) this.prediction = d.prediction;
      } catch(e) { console.warn('No prior prediction', e); }
    },

    async saveHealthAssessment() {
      this.saving = true;
      const payload = { record_id: this.recordId, ...this.form };
      this.symptomFields.forEach(s => { payload[s.key] = payload[s.key] ? 'Yes' : 'No'; });
      ['family_history_diabetes','family_history_heart_disease','family_history_anemia','needs_followup','needs_referral']
        .forEach(k => { payload[k] = payload[k] ? 'Yes' : 'No'; });
      try {
        const res = await fetch('api/save_student_health_inputs.php', {
          method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload)
        });
        const d = await res.json();
        if (d.success) {
          this.assessmentSaved = true;
          this.toast('success', '✅ Health assessment saved. You can now run the ML prediction.');
        } else {
          this.toast('error', d.message || 'Save failed.');
        }
      } catch(e) { this.toast('error', 'Network error: ' + e.message); }
      this.saving = false;
    },

    async saveConsultation() {
      const selected = this.commonIllnesses.filter(i => this.consultForm.illnesses[i.key]);
      if (!selected.length && !this.consultForm.notes.trim()) {
        this.toast('error', 'Select at least one illness or add notes.');
        return;
      }
      this.consultSaving = true;
      try {
        const res = await fetch('api/save_consultation.php', {
          method:'POST', headers:{'Content-Type':'application/json'},
          body: JSON.stringify({
            record_id: parseInt(this.recordId),
            common_illnesses: selected.map(i=>i.label).join(', '),
            symptoms: this.consultForm.notes,
            medication: selected.map(i=>`${i.label}: ${i.med}`).join('\n'),
            notes: this.consultForm.notes
          })
        });
        const d = await res.json();
        if (d.success) {
          this.toast('success', '✅ Consultation saved.');
          Object.keys(this.consultForm.illnesses).forEach(k => this.consultForm.illnesses[k] = false);
          this.consultForm.notes = '';
        } else {
          this.toast('error', d.message || 'Save failed.');
        }
      } catch(e) { this.toast('error', 'Network error: ' + e.message); }
      this.consultSaving = false;
    },

    async runPrediction() {
      if (!this.assessmentSaved) { this.toast('error', 'Save the health assessment first.'); return; }
      this.predicting = true;
      this.prediction = null;
      try {
        const res = await fetch('api/generate_student_prediction.php', {
          method:'POST', headers:{'Content-Type':'application/json'},
          body: JSON.stringify({ record_id: this.recordId })
        });
        const d = await res.json();
        if (d.success) {
          this.prediction = d.prediction;
          this.toast('success', `🤖 Prediction complete — ${d.prediction.predicted_deficiency}`);
        } else {
          this.toast('error', d.message || 'Prediction failed. Is the Flask ML server running on port 5001?');
        }
      } catch(e) { this.toast('error', 'Network error: ' + e.message); }
      this.predicting = false;
    },

    formatDate(dt) {
      if (!dt) return '';
      return new Date(dt).toLocaleDateString('en-PH', { year:'numeric', month:'short', day:'numeric', hour:'2-digit', minute:'2-digit' });
    }
  }
}).mount('#app');
</script>
</body>
</html>