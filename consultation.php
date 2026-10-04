<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | Student Consultation</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    :root {
      --clinic-primary: #0f766e;
      --clinic-secondary: #14b8a6;
      --clinic-border: #d9eef0;
      --clinic-text: #16323f;
      --clinic-muted: #6b7d87;
      --clinic-shadow: 0 12px 32px rgba(15,118,110,0.10);
      --clinic-radius: 22px;
    }
    * { box-sizing: border-box; }
    body {
      min-height: 100vh;
      background: linear-gradient(135deg, #eef8fb, #f8fcfd);
      font-family: 'Plus Jakarta Sans', Arial, sans-serif;
      color: var(--clinic-text);
    }
    .wrapper { max-width: 1200px; margin: 28px auto; padding: 20px; }
    .header-box {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white;
      padding: 34px;
      border-radius: 28px;
      margin-bottom: 24px;
      box-shadow: 0 16px 38px rgba(15,118,110,0.22);
    }
    .btn-back {
      background: white; color: var(--clinic-primary);
      border: none; border-radius: 15px; padding: 11px 18px;
      font-weight: 800; text-decoration: none;
    }
    .btn-back:hover { background: #ecfeff; }
    .btn-green {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white; font-weight: 900; border: none;
      border-radius: 14px; padding: 12px 24px;
    }
    .btn-green:hover { transform: translateY(-1px); }
    .card-box {
      background: white;
      border: 1px solid var(--clinic-border);
      border-radius: var(--clinic-radius);
      box-shadow: var(--clinic-shadow);
      padding: 24px;
      margin-bottom: 24px;
    }
    .form-label { font-weight: 800; margin-bottom: 0.5rem; }
    .form-control, .form-select {
      border-radius: 14px;
      border: 1px solid var(--clinic-border);
      padding: 11px 13px;
    }
    .form-control:focus, .form-select:focus {
      border-color: var(--clinic-secondary);
      box-shadow: 0 0 0 0.2rem rgba(20,184,166,0.16);
    }
    .symptoms-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px,1fr));
      gap: 12px;
      margin-top: 12px;
    }
    .form-check { display: flex; align-items: center; gap: 8px; }
    .alert { border-radius: 16px; }
    .recommendation-card {
      background: #f0fdfa;
      border-left: 4px solid var(--clinic-primary);
      padding: 16px;
      margin-top: 16px;
    }
    @media (max-width: 768px) { .wrapper { padding: 14px; } }
  </style>
</head>
<body>
<div id="app" class="wrapper">

  <div class="header-box d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
      <h1 class="fw-bold mb-2">🩺 Student Consultation</h1>
      <p class="mb-0">Record reported symptoms, care given, and any follow-up.</p>
    </div>
    <div>
      <a href="nurse-dashboard.php" class="btn-back">← Back to Dashboard</a>
    </div>
  </div>

  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">{{ message }}</div>

  <div class="card-box">
    <h4 class="fw-bold mb-3">Select Student</h4>
    <div class="row">
      <div class="col-md-8">
        <select v-model="selectedStudentId" class="form-select" @change="loadStudentData">
          <option value="">-- Select a student --</option>
          <option v-for="s in students" :value="s.record_id">{{ s.learner_name }} ({{ s.grade_level }} - {{ s.section }})</option>
        </select>
      </div>
      <div class="col-md-4">
        <div class="form-control bg-light" v-if="selectedStudent">
          Consultations: <strong>{{ selectedStudent.consult_count || 0 }}</strong>
        </div>
      </div>
    </div>
  </div>

  <div v-if="selectedStudent" class="row">
    <div class="col-md-5">
      <div class="card-box">
        <h4 class="fw-bold">Student Info</h4>
        <p><strong>Name:</strong> {{ selectedStudent.learner_name }}</p>
        <p><strong>Grade & Section:</strong> {{ selectedStudent.grade_level }} - {{ selectedStudent.section }}</p>
        <p><strong>BMI Category:</strong> 
          <span class="badge" :class="getBmiBadge(selectedStudent.bmi_category)">{{ selectedStudent.bmi_category || 'N/A' }}</span>
        </p>
        <p><strong>Total Consultations:</strong> {{ selectedStudent.consult_count || 0 }}</p>
        <hr>
        <h5>📋 Symptoms (Check all that apply)</h5>
        <div class="symptoms-grid">
          <div class="form-check" v-for="sym in symptomList" :key="sym.key">
            <input type="checkbox" class="form-check-input" v-model="selectedSymptoms[sym.key]">
            <label class="form-check-label">{{ sym.label }}</label>
          </div>
        </div>
        <div v-if="medicineSuggestions.length" class="recommendation-card">
          <strong>Suggested medicine for nurse review</strong>
          <div v-for="item in medicineSuggestions" :key="item.symptom" class="mt-2">
            <strong>{{ item.symptom }}: {{ item.medicine }}</strong><br>{{ item.note }}
          </div>
          <small>Suggestions are not prescriptions or a record of care given.</small>
        </div>
        <div class="mt-3">
          <label class="form-label">Additional Notes / Symptoms</label>
          <textarea class="form-control" rows="3" v-model="otherSymptoms" placeholder="Describe other symptoms not listed..."></textarea>
        </div>
      </div>
    </div>

    <div class="col-md-7">
      <div class="card-box">
        <h4 class="fw-bold">📝 Nurse Consultation Record</h4>
        <p class="text-muted">Enter only observations and care that actually happened. Review any symptom-based medicine suggestions separately.</p>
        <div class="mt-3">
          <label class="form-label" for="consultCareGiven">Care or action given</label>
          <textarea id="consultCareGiven" class="form-control" rows="3" v-model="careGiven" placeholder="What did the nurse do during this visit?"></textarea>
        </div>
        <div class="mt-3">
          <label class="form-label" for="consultFollowUp">Follow-up Date (optional)</label>
          <input id="consultFollowUp" type="date" class="form-control" v-model="followUpDate">
        </div>
        <div class="mt-3">
          <label class="form-label" for="consultNotes">Additional notes (optional)</label>
          <textarea id="consultNotes" class="form-control" rows="2" v-model="consultationNotes"></textarea>
        </div>
        
        <button class="btn btn-green w-100 mt-4" @click="saveConsultation" :disabled="saving">
          {{ saving ? 'Saving...' : 'Save Consultation' }}
        </button>
        <div v-if="consultations.length" class="mt-4">
          <h5 class="fw-bold">Recent consultations</h5>
          <div v-for="entry in consultations.slice(0,5)" :key="entry.consultation_id" class="recommendation-card mb-2">
            <strong>{{ entry.recorded_at }} · {{ entry.recorded_by || 'Clinic Nurse' }}</strong>
            <div>Reported: {{ entry.symptoms }}</div>
            <div v-if="entry.care_given">Care given: {{ entry.care_given }}</div>
            <div v-if="entry.follow_up_date">Follow-up: {{ entry.follow_up_date }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div v-if="!selectedStudent && students.length > 0" class="text-center text-muted py-5">
    Select a student from the dropdown above to start consultation.
  </div>
</div>

<script src="assets/consultation-suggestions.js"></script>
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script>
const { createApp } = Vue;

createApp({
  data() {
    return {
      students: [],
      selectedStudentId: '',
      selectedStudent: null,
      selectedSymptoms: {
        has_fatigue: false,
        has_bone_pain: false,
        has_bleeding_gums: false,
        has_pale_skin: false,
        has_night_blindness: false,
        has_low_appetite: false,
        has_headache: false,
        has_dental_problem: false
      },
      otherSymptoms: '',
      careGiven: '',
      followUpDate: '',
      consultationNotes: '',
      consultations: [],
      saving: false,
      message: '',
      messageType: 'success',
      symptomList: [
        { key: 'has_fatigue', label: 'Fatigue' },
        { key: 'has_bone_pain', label: 'Bone pain' },
        { key: 'has_bleeding_gums', label: 'Bleeding gums' },
        { key: 'has_pale_skin', label: 'Pale skin' },
        { key: 'has_night_blindness', label: 'Night blindness' },
        { key: 'has_low_appetite', label: 'Low appetite' },
        { key: 'has_headache', label: 'Frequent headaches' },
        { key: 'has_dental_problem', label: 'Dental problems' }
      ]
    };
  },
  mounted() {
    if (localStorage.getItem('active_role') !== 'Clinic Nurse' || !localStorage.getItem('local_id_token')) {
      location.replace('login.php'); return;
    }
    this.loadStudents();
  },
  computed: {
    medicineSuggestions() {
      return window.clinicConsultationSuggestions(
        Object.keys(this.selectedSymptoms).filter(key => this.selectedSymptoms[key]));
    }
  },
  methods: {
    getBmiBadge(cat) {
      const t = String(cat || '').toLowerCase();
      if (t.includes('normal')) return 'bg-success';
      if (t.includes('underweight') || t.includes('wasted')) return 'bg-warning text-dark';
      if (t.includes('overweight') || t.includes('obese')) return 'bg-danger';
      return 'bg-secondary';
    },
    async loadStudents() {
      try {
        const res = await fetch('api/get_students_for_consult.php', {headers:{Authorization:'Bearer '+localStorage.getItem('local_id_token')}});
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.message || 'Could not load students.');
        this.students = data.students;
        if (this.selectedStudentId) this.selectedStudent = this.students.find(s => String(s.record_id) === String(this.selectedStudentId)) || null;
      } catch(e) { this.showMessage('error', e.message); }
    },
    async loadStudentData() {
      if (!this.selectedStudentId) {
        this.selectedStudent = null;
        this.consultations = [];
        return;
      }
      this.selectedStudent = this.students.find(s => String(s.record_id) === String(this.selectedStudentId)) || null;
      Object.keys(this.selectedSymptoms).forEach(key => this.selectedSymptoms[key] = false);
      this.otherSymptoms = '';
      this.careGiven = '';
      this.followUpDate = '';
      this.consultationNotes = '';
      await this.loadConsultations();
    },
    async loadConsultations() {
      try {
        const res = await fetch('api/get_consultations.php?record_id='+encodeURIComponent(this.selectedStudentId),
          {headers:{Authorization:'Bearer '+localStorage.getItem('local_id_token')}});
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.message || 'Could not load consultations.');
        this.consultations = data.consultations || [];
      } catch(e) { this.showMessage('error', e.message); }
    },
    getSymptomText() {
      let symptoms = [];
      for (const [key, val] of Object.entries(this.selectedSymptoms)) {
        if (val) {
          const label = this.symptomList.find(s => s.key === key)?.label || key;
          symptoms.push(label);
        }
      }
      if (this.otherSymptoms) symptoms.push(this.otherSymptoms);
      return symptoms.join(', ');
    },
    async saveConsultation() {
      if (!this.selectedStudent) {
        this.showMessage('error', 'Please select a student first.');
        return;
      }
      this.saving = true;
      const symptomText = this.getSymptomText();
      if (!symptomText.trim()) { this.showMessage('error', 'Record symptoms or a reason for visit.'); this.saving = false; return; }
      try {
        const res = await fetch('api/save_consultation.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', Authorization:'Bearer '+localStorage.getItem('local_id_token') },
          body: JSON.stringify({
            record_id: this.selectedStudentId,
            symptoms: symptomText,
            care_given: this.careGiven,
            follow_up_date: this.followUpDate,
            notes: this.consultationNotes
          })
        });
        const data = await res.json();
        if (res.ok && data.success) {
          this.showMessage('success', 'Consultation saved successfully!');
          await this.loadStudents();
          await this.loadConsultations();
          Object.keys(this.selectedSymptoms).forEach(k => this.selectedSymptoms[k] = false);
          this.otherSymptoms = '';
          this.careGiven = '';
          this.followUpDate = '';
          this.consultationNotes = '';
        } else {
          this.showMessage('error', data.message || 'Save failed.');
        }
      } catch(e) {
        this.showMessage('error', 'Error: ' + e.message);
      }
      this.saving = false;
    },
    showMessage(type, text) {
      this.messageType = type;
      this.message = text;
      setTimeout(() => { this.message = ''; }, 5000);
    }
  }
}).mount('#app');
</script>
</body>
</html>
