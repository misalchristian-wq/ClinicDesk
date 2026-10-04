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
    <a href="monitoring-hub.php" class="btn-back" style="position:relative;z-index:2;">← Monitoring</a>
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
          <div class="pc-label">Screening flag for nurse review</div>
          <div class="pc-value">{{ screeningFlag }}</div>

          <div class="pc-label">Screening priority</div>
          <div class="risk-pill" :class="riskClass" style="margin-bottom:12px;">
            <i class="bi" :class="riskIcon"></i>
            {{ prediction.predicted_risk_level || '—' }}
          </div>

          <div class="pc-label">Model score</div>
          <div class="conf-bar-wrap"><div class="conf-bar" :style="{width: confPct + '%'}"></div></div>
          <div style="font-size:12px;color:var(--mut);margin-bottom:12px;">{{ confPct }}%</div>
          <p style="font-size:11.5px;color:#9a3412;margin:0 0 12px;">Experimental symptom screening only: the source holdout had 21.62% accuracy among 37 school-age rows. Review the learner independently before any care decision.</p>

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
          <button class="btn-predict" @click="runPrediction" :disabled="predicting || !assessmentSaved || predictionMissing.length">
            <span v-if="predicting"><span class="predict-spinner"></span>Running...</span>
            <span v-else><i class="bi bi-stars me-1"></i>{{ prediction ? 'Re-run Prediction' : 'Run ML Prediction' }}</span>
          </button>
          <p v-if="!assessmentSaved" style="font-size:11.5px;color:var(--mut);text-align:center;margin-top:6px;">Save assessment first to enable prediction</p>
          <p v-else-if="predictionMissing.length" style="font-size:11.5px;color:#9a3412;text-align:center;margin-top:6px;">Still needed: {{ predictionMissing.join(', ') }}.</p>
          <p style="font-size:11.5px;text-align:center;margin-top:8px;"><a href="nurse-prediction-settings.php" style="color:var(--p);font-weight:700;">Check prediction service</a></p>
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
            <p>Record the learner's report and the care actually given.</p>
          </div>
        </div>

        <label class="form-label">Reported symptoms</label>
        <div class="ill-grid">
          <label class="ill-chip" :class="{active: consultForm.symptoms[ill.key]}" v-for="ill in consultationSymptoms" :key="ill.key">
            <input type="checkbox" v-model="consultForm.symptoms[ill.key]">
            <span>{{ ill.label }}</span>
          </label>
        </div>

        <div v-if="consultationSuggestions.length" class="rec-box" style="margin-top:14px;">
          <strong>Suggested medicine for nurse review</strong>
          <div v-for="item in consultationSuggestions" :key="item.symptom" style="margin-top:8px;">
            <strong>{{ item.symptom }}: {{ item.medicine }}</strong><br><span>{{ item.note }}</span>
          </div>
          <small>These suggestions are not prescriptions and are not recorded as care given. Review the learner before entering actual care below.</small>
        </div>

        <div style="margin-top:14px;">
          <label class="form-label">Other symptoms or reason for visit</label>
          <textarea class="form-control" rows="2" v-model="consultForm.otherSymptoms" placeholder="Describe what the learner reported..."></textarea>
        </div>
        <div style="margin-top:14px;">
          <label class="form-label" for="quickConsultCare">Care or action given</label>
          <textarea id="quickConsultCare" class="form-control" rows="2" v-model="consultForm.careGiven" placeholder="Enter only actions the nurse actually performed"></textarea>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:14px;margin-top:14px;">
          <div><label class="form-label" for="quickConsultFollowUp">Follow-up date (optional)</label><input id="quickConsultFollowUp" class="form-control" type="date" v-model="consultForm.followUpDate"></div>
          <div><label class="form-label" for="quickConsultNotes">Additional notes (optional)</label><textarea id="quickConsultNotes" class="form-control" rows="2" v-model="consultForm.notes"></textarea></div>
        </div>

        <div style="margin-top:14px;display:flex;justify-content:flex-end;">
          <button class="btn-primary-action" @click="saveConsultation" :disabled="consultSaving" style="background:linear-gradient(135deg,#0ea5e9,#38bdf8);">
            <span v-if="consultSaving"><span class="predict-spinner"></span>Saving...</span>
            <span v-else><i class="bi bi-save me-1"></i>Save Consultation</span>
          </button>
        </div>
        <div v-if="consultations.length" class="mt-4">
          <h4 style="font-size:15px;font-weight:800;">Recent consultations</h4>
          <div v-for="entry in consultations.slice(0,3)" :key="entry.consultation_id" class="med-card mb-2">
            <strong>{{ entry.recorded_at }} · {{ entry.recorded_by || 'Clinic Nurse' }}</strong>
            <div>Reported: {{ entry.symptoms }}</div>
            <div v-if="entry.care_given">Care given: {{ entry.care_given }}</div>
            <div v-if="entry.follow_up_date">Follow-up: {{ entry.follow_up_date }}</div>
          </div>
        </div>
      </div>

      <!-- ── Lifestyle & General Health ── -->
      <div class="card">
        <div class="sec-head">
          <div class="sec-icon">🥗</div>
          <div><h3>Lifestyle & General Health</h3><p>Complete the dataset fields for symptom-based screening.</p></div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;">
          <div>
            <label class="form-label">Diet type</label>
            <select class="form-select" v-model="form.diet_type">
              <option value="">Select</option>
              <option>Vegetarian</option><option>Non-Vegetarian</option>
            </select>
          </div>
          <div>
            <label class="form-label">Living environment</label>
            <select class="form-select" v-model="form.living_environment">
              <option value="">Select</option><option>Rural</option><option>Urban</option>
            </select>
          </div>
          <div>
            <label class="form-label">Skin condition</label>
            <select class="form-select" v-model="form.skin_condition">
              <option value="">Select</option><option>Normal</option><option>Dry Skin</option>
              <option>Rough Skin</option><option>Pale/Yellow Skin</option>
            </select>
          </div>
          <div>
            <label class="form-label">Low Sun Exposure</label>
            <select class="form-select" v-model="form.sun_exposure">
              <option value="">Select</option>
              <option value="Low">Yes, low sun exposure</option><option value="Moderate">No, moderate sun exposure</option><option value="High">No, high sun exposure</option>
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
          <div>
            <label class="form-label">Allergy details</label>
            <input type="text" class="form-control" v-model="form.allergy_details" :disabled="form.has_known_allergy !== 'Yes'" :placeholder="form.has_known_allergy === 'Yes' ? 'e.g. peanuts, pollen' : 'Not applicable when No'">
          </div>
        </div>
      </div>

      <!-- ── Observed Symptoms ── -->
      <div class="card">
        <div class="sec-head" style="margin-bottom:0;">
          <div style="display:flex;align-items:center;gap:10px;">
            <div class="sec-icon">📋</div>
            <div><h3>Observed Symptoms</h3><p>{{ activeSymptomCount }} symptom{{ activeSymptomCount !== 1 ? 's' : '' }} checked. Model symptoms and other clinical observations are shown together.</p></div>
          </div>
        </div>
        <div class="sym-grid" style="margin-top:16px;">
          <label class="sym-chip" :class="{active: form[sym.key]}" v-for="sym in symptomFields" :key="sym.key">
            <input type="checkbox" v-model="form[sym.key]">
            <span>{{ sym.label }}</span>
          </label>
        </div>
      </div>

      <!-- ── Family History ── -->
      <div class="card">
        <div class="sec-head" style="margin-bottom:0;">
          <div style="display:flex;align-items:center;gap:10px;">
            <div class="sec-icon" style="background:linear-gradient(135deg,#f59e0b,#fbbf24);">👨‍👩‍👧</div>
            <div><h3>Family History</h3><p>Hereditary conditions</p></div>
          </div>
        </div>
        <div class="fam-grid" style="margin-top:16px;">
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

<script src="assets/consultation-suggestions.js"></script>
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

      consultationSymptoms: [
        { key: 'fever', label: 'Fever' }, { key: 'headache', label: 'Headache' },
        { key: 'cough', label: 'Cough' }, { key: 'colds', label: 'Colds' },
        { key: 'sore_throat', label: 'Sore throat' }, { key: 'stomachache', label: 'Stomachache' },
      ],
      consultForm: { symptoms: { fever:false, headache:false, cough:false, colds:false, sore_throat:false, stomachache:false }, otherSymptoms:'', careGiven:'', followUpDate:'', notes:'' },
      consultations: [],

      symptomFields: [
        { key:'has_fatigue',           label:'Fatigue' },
        { key:'has_bone_pain',         label:'Bone pain' },
        { key:'has_bleeding_gums',     label:'Bleeding gums' },
        { key:'has_pale_skin',         label:'Pale skin' },
        { key:'has_night_blindness',   label:'Night blindness' },
        { key:'has_dry_eyes',          label:'Dry eyes' },
        { key:'has_shortness_of_breath',label:'Shortness of breath' },
        { key:'has_fast_heart_rate',   label:'Fast heart rate' },
        { key:'has_brittle_nails',     label:'Brittle nails' },
        { key:'has_weight_loss',       label:'Weight loss' },
        { key:'has_reduced_wound_healing', label:'Reduced wound healing capacity' },
        { key:'has_muscle_weakness',    label:'Muscle weakness' },
        { key:'has_numbness_tingling',  label:'Tingling sensation' },
        { key:'has_memory_problems',    label:'Reduced memory capacity' },
        { key:'has_low_appetite',      label:'Loss of appetite' },
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
        diet_type:'', living_environment:'', skin_condition:'', sun_exposure:'', exercise_level:'',
        has_fatigue:false, has_bone_pain:false, has_bleeding_gums:false, has_pale_skin:false,
        has_night_blindness:false, has_low_appetite:false, has_irregular_meals:false, has_weight_changes:false,
        has_muscle_weakness:false, has_numbness_tingling:false, has_memory_problems:false,
        has_dry_eyes:false, has_shortness_of_breath:false, has_fast_heart_rate:false,
        has_brittle_nails:false, has_weight_loss:false, has_reduced_wound_healing:false,
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
    consultationSuggestions() {
      return window.clinicConsultationSuggestions(
        this.consultationSymptoms.filter(item => this.consultForm.symptoms[item.key]).map(item => item.key));
    },
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
    },
    screeningFlag() {
      const labels = {
        'Iron': 'Possible iron-related concern',
        'Vitamin A': 'Possible vitamin A-related concern',
        'Vitamin B12': 'Possible vitamin B12-related concern',
        'Vitamin C': 'Possible vitamin C-related concern',
        'Vitamin D': 'Possible vitamin D-related concern',
        'Zinc': 'Possible zinc-related concern',
        'No Deficiency': 'No concern flagged by this model',
        'Iron Deficiency': 'Possible iron-related concern from earlier model',
        'Vitamin D Deficiency': 'Possible vitamin D-related concern from earlier model',
        'Severe Malnutrition': 'Nutrition concern from earlier model',
        'Normal': 'No concern flagged by earlier model'
      };
      return labels[this.prediction?.predicted_deficiency] || 'For nurse review';
    },
    predictionMissing() {
      const missing = [];
      if (!Number(this.student.age) || Number(this.student.age) < 5 || Number(this.student.age) > 69) missing.push('age (5-69)');
      if (!['Male','Female'].includes(this.student.sex)) missing.push('sex');
      if (!['Vegetarian','Non-Vegetarian'].includes(this.form.diet_type)) missing.push('diet type');
      if (!['Rural','Urban'].includes(this.form.living_environment)) missing.push('living environment');
      if (!['Normal','Dry Skin','Rough Skin','Pale/Yellow Skin'].includes(this.form.skin_condition)) missing.push('skin condition');
      if (!['Low','Moderate','High'].includes(this.form.sun_exposure)) missing.push('sun exposure');
      return missing;
    }
  },

  watch: {
    form: {
      deep: true,
      handler() { this.assessmentSaved = false; }
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
    await this.loadConsultations();
  },

  methods: {
    authHeaders(extra = {}) {
      return { ...extra, Authorization: `Bearer ${localStorage.getItem('local_id_token') || ''}` };
    },
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
        const res = await fetch(`api/get_health_assessment.php?record_id=${this.recordId}`, {headers: this.authHeaders()});
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
          if (this.form.diet_type && !['Vegetarian','Non-Vegetarian'].includes(this.form.diet_type)) {
            this.form.diet_type = '';
            this.toast('error', 'The earlier diet category is not part of this dataset. Select Vegetarian or Non-Vegetarian before saving.');
          }
          await this.$nextTick();
          this.assessmentSaved = true;
        }
      } catch(e) { console.warn('No existing assessment', e); }
    },

    async loadLatestPrediction() {
      try {
        const res = await fetch(`api/get_student_prediction.php?record_id=${this.recordId}`, {headers:this.authHeaders()});
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
          method:'POST', headers:this.authHeaders({'Content-Type':'application/json'}), body:JSON.stringify(payload)
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
      const selected = this.consultationSymptoms.filter(i => this.consultForm.symptoms[i.key]);
      const symptoms = [...selected.map(i => i.label), this.consultForm.otherSymptoms.trim()].filter(Boolean).join(', ');
      if (!symptoms) {
        this.toast('error', 'Record the symptoms or reason for visit.');
        return;
      }
      this.consultSaving = true;
      try {
        const res = await fetch('api/save_consultation.php', {
          method:'POST', headers:this.authHeaders({'Content-Type':'application/json'}),
          body: JSON.stringify({
            record_id: parseInt(this.recordId),
            symptoms,
            care_given: this.consultForm.careGiven,
            follow_up_date: this.consultForm.followUpDate,
            notes: this.consultForm.notes
          })
        });
        const d = await res.json();
        if (d.success) {
          this.toast('success', '✅ Consultation saved.');
          Object.keys(this.consultForm.symptoms).forEach(k => this.consultForm.symptoms[k] = false);
          this.consultForm.otherSymptoms = '';
          this.consultForm.careGiven = '';
          this.consultForm.followUpDate = '';
          this.consultForm.notes = '';
          await this.loadConsultations();
        } else {
          this.toast('error', d.message || 'Save failed.');
        }
      } catch(e) { this.toast('error', 'Network error: ' + e.message); }
      this.consultSaving = false;
    },

    async loadConsultations() {
      try {
        const res = await fetch(`api/get_consultations.php?record_id=${encodeURIComponent(this.recordId)}`, {headers:this.authHeaders()});
        const data = await res.json();
        if (data.success) this.consultations = data.consultations || [];
      } catch(e) { this.toast('error', 'Consultation history could not be loaded.'); }
    },

    async runPrediction() {
      if (!this.assessmentSaved) { this.toast('error', 'Save the health assessment first.'); return; }
      if (this.predictionMissing.length) { this.toast('error', 'Complete the dataset screening inputs before predicting.'); return; }
      this.predicting = true;
      this.prediction = null;
      try {
        const res = await fetch('api/generate_student_prediction.php', {
          method:'POST', headers:this.authHeaders({'Content-Type':'application/json'}),
          body: JSON.stringify({ record_id: this.recordId })
        });
        const d = await res.json();
        if (d.success) {
          this.prediction = d.prediction;
          this.toast('success', 'Screening result saved for nurse review.');
        } else {
          this.toast('error', d.message || 'Prediction failed. Check Prediction Settings.');
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
