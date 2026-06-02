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
      <p class="mb-0">Select a student, perform health assessment, and get automatic recommendations</p>
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
        <div class="mt-3">
          <label class="form-label">Additional Notes / Symptoms</label>
          <textarea class="form-control" rows="3" v-model="otherSymptoms" placeholder="Describe other symptoms not listed..."></textarea>
        </div>
      </div>
    </div>

    <div class="col-md-7">
      <div class="card-box">
        <h4 class="fw-bold">📝 Assessment & Recommendations</h4>
        
        <div class="recommendation-card" v-if="generatedRecommendation">
          <h5 class="text-primary">💊 Recommendation</h5>
          <p>{{ generatedRecommendation.recommendation }}</p>
          <hr>
          <h5>🍎 Meal Plan</h5>
          <p>{{ generatedRecommendation.meal_plan }}</p>
          <hr>
          <h5>💊 Medicine / Supplement</h5>
          <p>{{ generatedRecommendation.medicine }}</p>
        </div>
        
        <div v-else class="text-center text-muted py-4">
          <i class="bi bi-robot"></i> Select symptoms above to generate recommendations
        </div>
        
        <div class="mt-3">
          <label class="form-label">Follow-up Date (optional)</label>
          <input type="date" class="form-control" v-model="followUpDate">
        </div>
        
        <button class="btn btn-green w-100 mt-4" @click="saveConsultation" :disabled="saving">
          {{ saving ? 'Saving...' : '✅ Save Consultation & Generate Report' }}
        </button>
      </div>
    </div>
  </div>

  <div v-if="!selectedStudent && students.length > 0" class="text-center text-muted py-5">
    Select a student from the dropdown above to start consultation.
  </div>
</div>

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
      followUpDate: '',
      saving: false,
      message: '',
      messageType: 'success',
      generatedRecommendation: null,
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
    this.loadStudents();
  },
  watch: {
    selectedSymptoms: {
      handler() { this.generateRecommendationLocal(); },
      deep: true
    },
    otherSymptoms() { this.generateRecommendationLocal(); },
    selectedStudent() { this.generateRecommendationLocal(); }
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
        const res = await fetch('api/get_students_for_consult.php');
        const data = await res.json();
        if (data.success) this.students = data.students;
      } catch(e) { console.error(e); }
    },
    async loadStudentData() {
      if (!this.selectedStudentId) {
        this.selectedStudent = null;
        return;
      }
      const student = this.students.find(s => s.record_id == this.selectedStudentId);
      if (student) {
        this.selectedStudent = student;
        // Also load existing health assessment if any
        try {
          const res = await fetch(`api/get_health_assessment.php?record_id=${this.selectedStudentId}`);
          const data = await res.json();
          if (data.success && data.health_input) {
            // Pre-fill symptoms if they exist
            Object.keys(this.selectedSymptoms).forEach(key => {
              if (data.health_input[key] === 'Yes' || data.health_input[key] === 1) {
                this.selectedSymptoms[key] = true;
              }
            });
            if (data.health_input.symptoms) this.otherSymptoms = data.health_input.symptoms;
          }
        } catch(e) { console.log('No existing assessment'); }
      }
    },
    generateRecommendationLocal() {
      const bmi = this.selectedStudent?.bmi_category || 'Normal';
      const symptomText = this.getSymptomText();
      
      let recommendation = '';
      let medicine = '';
      let mealPlan = '';
      
      if (bmi.includes('Underweight') || bmi.includes('Wasted')) {
        recommendation = 'Student is underweight. Needs high-calorie, protein-rich diet.';
        mealPlan = '3 main meals + 2 snacks: eggs, milk, meat, fish, nuts, legumes, rice.';
        medicine = 'Multivitamins with iron (consult physician for dosage).';
      } else if (bmi.includes('Overweight') || bmi.includes('Obese')) {
        recommendation = 'Student is overweight/obese. Needs weight management.';
        mealPlan = 'Low-calorie, high-fiber: vegetables, fruits, lean meat, whole grains. Avoid sugary drinks.';
        medicine = 'No medication. Focus on diet and exercise.';
      } else {
        recommendation = 'BMI is normal. Maintain healthy lifestyle.';
        mealPlan = 'Balanced diet with fruits, vegetables, protein, and carbohydrates.';
        medicine = 'No medication needed.';
      }
      
      if (symptomText.includes('fatigue') || symptomText.includes('pale')) {
        recommendation += ' Possible anemia. Increase iron-rich foods.';
        medicine = 'Iron supplements (Ferrous sulfate 200mg daily).';
        mealPlan += ' Include red meat, liver, spinach, beans.';
      }
      if (symptomText.includes('bone pain') || symptomText.includes('night blindness')) {
        recommendation += ' Possible Vitamin D or A deficiency.';
        medicine = 'Vitamin D3 (800 IU daily) or Vitamin A supplement.';
        mealPlan += ' Add carrots, squash, eggs, milk. Sun exposure.';
      }
      if (symptomText.includes('bleeding gums')) {
        recommendation += ' Possible Vitamin C deficiency.';
        medicine = 'Vitamin C supplement (500mg daily for 2 weeks).';
        mealPlan += ' Increase citrus fruits: oranges, guava, bell peppers.';
      }
      
      this.generatedRecommendation = { recommendation, medicine, meal_plan: mealPlan };
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
      try {
        const res = await fetch('api/save_consultation.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            record_id: this.selectedStudentId,
            symptoms: symptomText,
            bmi_category: this.selectedStudent.bmi_category || 'Normal'
          })
        });
        const data = await res.json();
        if (data.success) {
          this.showMessage('success', 'Consultation saved successfully!');
          // Reload student to update consult count
          this.loadStudents();
          this.loadStudentData();
          // Optionally reset symptoms after save
          Object.keys(this.selectedSymptoms).forEach(k => this.selectedSymptoms[k] = false);
          this.otherSymptoms = '';
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