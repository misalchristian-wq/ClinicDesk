<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | Health Assessment & Consultation</title>
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
      --clinic-card: #ffffff;
      --clinic-border: #d9eef0;
      --clinic-text: #16323f;
      --clinic-muted: #6b7d87;
      --clinic-shadow: 0 12px 32px rgba(15, 118, 110, 0.10);
      --clinic-radius: 22px;
    }
    * { box-sizing: border-box; }
    body {
      min-height: 100vh;
      margin: 0;
      background: radial-gradient(circle at top left, rgba(20,184,166,0.16), transparent 25%),
                  radial-gradient(circle at top right, rgba(14,165,233,0.12), transparent 25%),
                  linear-gradient(135deg, #eef8fb, #f8fcfd);
      font-family: 'Plus Jakarta Sans', Arial, sans-serif;
      color: var(--clinic-text);
      overflow-x: hidden;
    }
    .wrapper { max-width: 1500px; margin: 28px auto; padding: 20px; }
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
    .btn-back { background: white; color: var(--clinic-primary); border: none; border-radius: 15px; padding: 11px 18px; font-weight: 800; box-shadow: 0 12px 28px rgba(0,0,0,0.12); text-decoration: none; }
    .btn-back:hover { background: #ecfeff; color: var(--clinic-primary); }
    .main-grid { display: grid; grid-template-columns: 360px 1fr; gap: 24px; align-items: start; }
    .card-box { background: var(--clinic-card); border: 1px solid var(--clinic-border); border-radius: var(--clinic-radius); box-shadow: var(--clinic-shadow); padding: 24px; }
    .profile-card { position: sticky; top: 20px; }
    .avatar { width: 96px; height: 96px; border-radius: 30px; background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary)); color: white; display: flex; align-items: center; justify-content: center; font-size: 34px; font-weight: 900; margin-bottom: 16px; box-shadow: 0 14px 28px rgba(15, 118, 110, 0.20); }
    .student-name { font-size: 24px; font-weight: 900; color: var(--clinic-text); margin-bottom: 4px; }
    .muted-text { color: var(--clinic-muted); font-size: 14px; line-height: 1.5; }
    .badge { border-radius: 999px; padding: 8px 12px; font-size: 12px; font-weight: 800; }
    .info-row { display: flex; justify-content: space-between; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--clinic-border); }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: var(--clinic-muted); font-size: 13px; font-weight: 700; }
    .info-value { color: var(--clinic-text); font-size: 13px; font-weight: 800; text-align: right; }
    .section-title { margin-bottom: 16px; }
    .section-title h3 { color: var(--clinic-primary); font-weight: 900; margin-bottom: 4px; }
    .section-title p { color: var(--clinic-muted); font-size: 14px; margin-bottom: 0; }
    .form-group { margin-bottom: 1rem; }
    .form-label { font-weight: 800; color: var(--clinic-text); margin-bottom: 0.25rem; }
    .form-control, .form-select { border-radius: 14px; border: 1px solid var(--clinic-border); padding: 11px 13px; font-size: 14px; background: white; }
    .form-control:focus, .form-select:focus { border-color: var(--clinic-secondary); box-shadow: 0 0 0 0.2rem rgba(20,184,166,0.16); }
    .btn-green { background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary)); color: white; font-weight: 900; border: none; border-radius: 14px; padding: 11px 16px; box-shadow: 0 12px 24px rgba(15,118,110,0.18); }
    .btn-green:hover { color: white; transform: translateY(-1px); }
    .symptoms-grid, .illness-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px,1fr)); gap: 12px; margin-top: 8px; }
    .form-check { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
    .form-check-input { width: 18px; height: 18px; margin-top: 0; }
    .form-check-label { font-size: 14px; font-weight: 500; }
    .medication-card { background: #f0fdfa; border-left: 4px solid var(--clinic-primary); padding: 16px; margin-top: 16px; border-radius: 12px; }
    .alert { border-radius: 16px; border: none; box-shadow: var(--clinic-shadow); }
    .alert-info { background: #ecfeff; color: #155e75; border: 1px solid #bae6fd; }
    .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .small-note { font-size: 0.9rem; color: var(--clinic-muted); line-height: 1.5; }
    .toggle-btn { cursor: pointer; user-select: none; }
    @media (max-width: 1200px) { .main-grid { grid-template-columns: 1fr; } .profile-card { position: static; } }
    @media (max-width: 768px) { .wrapper { padding: 14px; margin: 12px auto; } .header-box { padding: 26px; } .header-icon { width: 50px; height: 50px; font-size: 24px; } .header-box h1 { font-size: 30px; } }
  </style>
</head>

<body>
<div id="app" class="wrapper">

  <div class="header-box d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div class="header-content d-flex align-items-center">
      <div class="header-icon">🩺</div>
      <div>
        <h1>Health Assessment & Consultation</h1>
        <p class="mb-1">Update learner's health history, perform consultation, and get medication recommendations.</p>
        <p class="mb-0">Clinic Nurse: <strong>{{ nurseName }}</strong></p>
      </div>
    </div>
    <div class="header-actions">
      <a href="student-dashboard.php" class="btn btn-back">← Back to Dashboard</a>
    </div>
  </div>

  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">{{ message }}</div>

  <div class="main-grid">
    <!-- Left profile card -->
    <div class="profile-card card-box">
      <div class="avatar">{{ initials }}</div>
      <div class="student-name">{{ student.learner_name || '—' }}</div>
      <div class="muted-text mb-3">{{ student.grade_level || '—' }} - {{ student.section || '—' }} · {{ student.sex || '—' }} · {{ student.age || '—' }} yrs</div>
      <div class="info-row"><div class="info-label">BMI</div><div class="info-value">{{ student.bmi || '—' }}</div></div>
      <div class="info-row"><div class="info-label">BMI Category</div><div class="info-value">{{ student.bmi_category || '—' }}</div></div>
      <div class="info-row"><div class="info-label">Height‑for‑Age</div><div class="info-value">{{ heightForAge }}</div></div>
    </div>

    <!-- Right content -->
    <div>
      <!-- CONSULTATION SECTION -->
      <div class="card-box mb-4">
        <div class="section-title">
          <h3>🩺 Quick Consultation</h3>
          <p>Select common illnesses, get medication recommendation, and save consultation record.</p>
        </div>
        
        <div class="row">
          <div class="col-md-6">
            <label class="form-label">Common Illnesses (Select all that apply)</label>
            <div class="illness-grid">
              <div class="form-check" v-for="illness in commonIllnesses" :key="illness.key">
                <input type="checkbox" class="form-check-input" v-model="consultForm.illnesses[illness.key]">
                <label class="form-check-label">{{ illness.label }}</label>
              </div>
            </div>
          </div>
          
          <div class="col-md-6">
            <label class="form-label">Additional Symptoms / Notes</label>
            <textarea class="form-control" rows="3" v-model="consultForm.notes" placeholder="Describe other symptoms not listed..."></textarea>
          </div>
        </div>
        
        <!-- Medication Recommendation Card -->
        <div class="medication-card" v-if="medicationRecommendation">
          <h5 class="text-primary mb-2"><i class="bi bi-capsule"></i> Recommended Medication</h5>
          <p class="mb-0" style="white-space: pre-line;">{{ medicationRecommendation }}</p>
        </div>
        
        <div class="mt-3">
          <button class="btn btn-green w-100" @click="saveConsultation" :disabled="consultSaving">
            {{ consultSaving ? 'Saving...' : '💾 Save Consultation' }}
          </button>
        </div>
      </div>

      <!-- HEALTH ASSESSMENT FORM -->
      <form @submit.prevent="saveHealthAssessment">
        <div class="card-box mb-4">
          <div class="section-title">
            <h3>Lifestyle & General Health</h3>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label class="form-label">Diet Type</label>
                <select v-model="form.diet_type" class="form-select">
                  <option value="">Select</option>
                  <option value="Balanced">Balanced</option>
                  <option value="Vegetarian">Vegetarian</option>
                  <option value="High protein">High protein</option>
                  <option value="Low calorie">Low calorie</option>
                  <option value="Other">Other</option>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label class="form-label">Sun Exposure</label>
                <select v-model="form.sun_exposure" class="form-select">
                  <option value="">Select</option>
                  <option value="Low">Low</option>
                  <option value="Moderate">Moderate</option>
                  <option value="High">High</option>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label class="form-label">Exercise Level</label>
                <select v-model="form.exercise_level" class="form-select">
                  <option value="">Select</option>
                  <option value="Sedentary">Sedentary</option>
                  <option value="Light">Light</option>
                  <option value="Moderate">Moderate</option>
                  <option value="Active">Active</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Toggleable Observed Symptoms Section -->
        <div class="card-box mb-4">
          <div class="section-title d-flex justify-content-between align-items-center" @click="showSymptoms = !showSymptoms" style="cursor: pointer;">
            <div>
              <h3 class="mb-0">📋 Observed Symptoms</h3>
              <p class="mb-0">Check all that apply</p>
            </div>
            <i class="bi" :class="showSymptoms ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
          </div>
          <div v-show="showSymptoms" class="symptoms-grid mt-3">
            <div class="form-check" v-for="sym in symptomFields" :key="sym.key">
              <input type="checkbox" class="form-check-input" v-model="form[sym.key]">
              <label class="form-check-label">{{ sym.label }}</label>
            </div>
          </div>
        </div>

        <div class="card-box mb-4">
          <div class="section-title">
            <h3>Immunization & Allergies</h3>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label">Immunization Status</label>
                <select v-model="form.immunization_updated" class="form-select">
                  <option value="Unknown">Unknown</option>
                  <option value="Up to date">Up to date</option>
                  <option value="Partial">Partial</option>
                  <option value="Not started">Not started</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label">Known Allergy</label>
                <select v-model="form.has_known_allergy" class="form-select">
                  <option value="No">No</option>
                  <option value="Yes">Yes</option>
                </select>
              </div>
              <div v-if="form.has_known_allergy === 'Yes'" class="mt-2">
                <label class="form-label">Allergy Details</label>
                <input type="text" class="form-control" v-model="form.allergy_details" placeholder="e.g., peanuts, pollen">
              </div>
            </div>
          </div>
        </div>

        <div class="card-box mb-4">
          <div class="section-title">
            <h3>Family History</h3>
          </div>
          <div class="row">
            <div class="col-md-4"><div class="form-check"><input type="checkbox" class="form-check-input" v-model="form.family_history_diabetes"> <label>Diabetes</label></div></div>
            <div class="col-md-4"><div class="form-check"><input type="checkbox" class="form-check-input" v-model="form.family_history_heart_disease"> <label>Heart Disease</label></div></div>
            <div class="col-md-4"><div class="form-check"><input type="checkbox" class="form-check-input" v-model="form.family_history_anemia"> <label>Anemia</label></div></div>
          </div>
        </div>

        <div class="card-box mb-4">
          <div class="section-title">
            <h3>Medical Conditions & Follow‑up</h3>
          </div>
          <div class="form-group">
            <label class="form-label">Existing Medical Condition (if any)</label>
            <textarea class="form-control" rows="2" v-model="form.existing_medical_condition" placeholder="e.g., asthma, hypertension"></textarea>
          </div>
          <div class="row mt-3">
            <div class="col-md-6"><div class="form-check"><input type="checkbox" class="form-check-input" v-model="form.needs_followup"> <label>Needs Follow‑up</label></div></div>
            <div class="col-md-6"><div class="form-check"><input type="checkbox" class="form-check-input" v-model="form.needs_referral"> <label>Needs Referral</label></div></div>
          </div>
          <div class="form-group mt-3">
            <label class="form-label">Clinic Notes / Remarks</label>
            <textarea class="form-control" rows="2" v-model="form.clinic_notes"></textarea>
          </div>
        </div>

        <div class="card-box mb-4">
          <div class="d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-green" :disabled="saving">
              {{ saving ? 'Saving...' : 'Save Health Assessment' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

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
      message: '',
      messageType: 'success',
      showSymptoms: false,
      
      commonIllnesses: [
        { key: 'fever', label: 'Fever', med: 'Paracetamol 500mg every 6 hours as needed. Monitor temperature.' },
        { key: 'headache', label: 'Headache', med: 'Paracetamol or Ibuprofen 200-400mg. Rest in a quiet room.' },
        { key: 'cough', label: 'Cough', med: 'Carbocisteine for productive cough or Dextromethorphan for dry cough. Warm honey lemon drink.' },
        { key: 'colds', label: 'Colds', med: 'Antihistamine (Loratadine or Cetirizine). Increase fluid intake. Rest.' },
        { key: 'sore_throat', label: 'Sore Throat', med: 'Warm salt water gargle. Lozenges. Paracetamol for pain.' },
        { key: 'stomachache', label: 'Stomachache', med: 'Antacid (Kremil-S). Avoid spicy food. Consult if severe.' }
      ],
      
      consultForm: {
        illnesses: {
          fever: false,
          headache: false,
          cough: false,
          colds: false,
          sore_throat: false,
          stomachache: false
        },
        notes: ''
      },

      symptomFields: [
        { key: 'has_fatigue', label: 'Fatigue' },
        { key: 'has_bone_pain', label: 'Bone pain' },
        { key: 'has_bleeding_gums', label: 'Bleeding gums' },
        { key: 'has_pale_skin', label: 'Pale skin' },
        { key: 'has_night_blindness', label: 'Night blindness' },
        { key: 'has_low_appetite', label: 'Low appetite' },
        { key: 'has_irregular_meals', label: 'Irregular meals' },
        { key: 'has_weight_changes', label: 'Unexplained weight changes' },
        { key: 'has_headache', label: 'Frequent headaches' },
        { key: 'has_poor_concentration', label: 'Poor concentration' },
        { key: 'has_vision_problem', label: 'Vision problems' },
        { key: 'has_hearing_problem', label: 'Hearing problems' },
        { key: 'has_dental_problem', label: 'Dental problems' },
        { key: 'has_skin_problem', label: 'Skin problems' },
        { key: 'has_breathing_problem', label: 'Breathing difficulties' },
        { key: 'has_recent_illness', label: 'Recent illness' },
        { key: 'has_current_medication', label: 'Current medication' }
      ],

      form: {
        diet_type: '',
        sun_exposure: '',
        exercise_level: '',
        has_fatigue: false,
        has_bone_pain: false,
        has_bleeding_gums: false,
        has_pale_skin: false,
        has_night_blindness: false,
        has_low_appetite: false,
        has_irregular_meals: false,
        has_weight_changes: false,
        has_headache: false,
        has_poor_concentration: false,
        has_vision_problem: false,
        has_hearing_problem: false,
        has_dental_problem: false,
        has_skin_problem: false,
        has_breathing_problem: false,
        has_recent_illness: false,
        has_current_medication: false,
        immunization_updated: 'Unknown',
        has_known_allergy: 'No',
        allergy_details: '',
        family_history_diabetes: false,
        family_history_heart_disease: false,
        family_history_anemia: false,
        existing_medical_condition: '',
        needs_followup: false,
        needs_referral: false,
        clinic_notes: ''
      }
    };
  },

  computed: {
    initials() {
      if (!this.student.learner_name) return '?';
      return this.student.learner_name.split(' ').filter(Boolean).slice(0,2).map(p => p[0].toUpperCase()).join('');
    },
    heightForAge() {
      return this.student.height_for_age_status || this.student.height_for_age || this.student.hfa_status || '-';
    },
    medicationRecommendation() {
      let selected = [];
      for (const [key, val] of Object.entries(this.consultForm.illnesses)) {
        if (val) {
          const illness = this.commonIllnesses.find(i => i.key === key);
          if (illness) selected.push(`• ${illness.label}: ${illness.med}`);
        }
      }
      if (selected.length === 0) return null;
      return selected.join('\n');
    }
  },

  async mounted() {
    const role = localStorage.getItem('active_role');
    const accountId = localStorage.getItem('local_account_id');
    if (role !== 'Clinic Nurse' || !accountId) {
      window.location.href = 'login.php';
      return;
    }
    this.nurseName = localStorage.getItem('local_full_name') || 'Clinic Nurse';

    const params = new URLSearchParams(window.location.search);
    this.recordId = params.get('record_id');
    if (!this.recordId) {
      this.showMessage('error', 'No student record ID provided.');
      return;
    }

    await this.loadStudentProfile();
    await this.loadHealthAssessment();
  },

  methods: {
    showMessage(type, text) {
      this.messageType = type;
      this.message = text;
      setTimeout(() => { this.message = ''; }, 5000);
    },

    async loadStudentProfile() {
      try {
        const res = await fetch(`api/get_student_profile.php?record_id=${this.recordId}&cache_buster=${Date.now()}`);
        const data = await res.json();
        if (data.success) this.student = data.student || {};
        else this.showMessage('error', data.message || 'Failed to load student profile');
      } catch (e) {
        this.showMessage('error', 'Error loading student: ' + e.message);
      }
    },

    async loadHealthAssessment() {
      try {
        const res = await fetch(`api/get_health_assessment.php?record_id=${this.recordId}`);
        const data = await res.json();
        if (data.success && data.health_input) {
          Object.keys(this.form).forEach(key => {
            if (data.health_input.hasOwnProperty(key)) {
              const val = data.health_input[key];
              if (typeof this.form[key] === 'boolean') {
                this.form[key] = (val === 'Yes' || val === 1 || val === true);
              } else {
                this.form[key] = val !== null ? val : '';
              }
            }
          });
        }
      } catch (e) {
        console.warn('No existing health assessment', e);
      }
    },

    async saveHealthAssessment() {
      this.saving = true;
      const payload = { record_id: this.recordId, ...this.form };
      const symptomKeys = this.symptomFields.map(s => s.key);
      symptomKeys.forEach(key => { payload[key] = payload[key] ? 'Yes' : 'No'; });
      payload.family_history_diabetes = payload.family_history_diabetes ? 'Yes' : 'No';
      payload.family_history_heart_disease = payload.family_history_heart_disease ? 'Yes' : 'No';
      payload.family_history_anemia = payload.family_history_anemia ? 'Yes' : 'No';
      payload.needs_followup = payload.needs_followup ? 'Yes' : 'No';
      payload.needs_referral = payload.needs_referral ? 'Yes' : 'No';

      try {
        const res = await fetch('api/save_student_health_inputs.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
          this.showMessage('success', 'Health assessment saved successfully.');
        } else {
          this.showMessage('error', data.message || 'Save failed.');
        }
      } catch (e) {
        this.showMessage('error', 'Network error: ' + e.message);
      }
      this.saving = false;
    },

    async saveConsultation() {
  const selectedIllnesses = [];
  for (const [key, val] of Object.entries(this.consultForm.illnesses)) {
    if (val) {
      const illness = this.commonIllnesses.find(i => i.key === key);
      if (illness) selectedIllnesses.push(illness.label);
    }
  }
  
  if (selectedIllnesses.length === 0 && !this.consultForm.notes) {
    this.showMessage('error', 'Please select at least one illness or add notes.');
    return;
  }

  this.consultSaving = true;
  
  const payload = {
    record_id: parseInt(this.recordId),
    common_illnesses: selectedIllnesses.join(', '),
    symptoms: this.consultForm.notes,
    medication: this.medicationRecommendation || '',
    notes: this.consultForm.notes
  };
  
  console.log('Sending payload:', JSON.stringify(payload));
  
  try {
    const res = await fetch('api/save_consultation.php', {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(payload)
    });
    
    const text = await res.text();
    console.log('Raw response:', text);
    
    let data;
    try {
      data = JSON.parse(text);
    } catch(e) {
      console.error('JSON parse error:', e);
      this.showMessage('error', 'Server error: ' + text.substring(0, 200));
      this.consultSaving = false;
      return;
    }
    
    if (data.success) {
      this.showMessage('success', data.message);
      // Reset form
      Object.keys(this.consultForm.illnesses).forEach(k => this.consultForm.illnesses[k] = false);
      this.consultForm.notes = '';
    } else {
      this.showMessage('error', data.message || 'Save failed.');
    }
  } catch (e) {
    console.error('Fetch error:', e);
    this.showMessage('error', 'Network error: ' + e.message);
  }
  this.consultSaving = false;
}
  }
}).mount('#app');
</script>
</body>
</html>