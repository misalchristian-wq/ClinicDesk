<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ClinicDesk | Parent Consent Form</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    :root {
      --clinic-primary: #0f766e;
      --clinic-secondary: #14b8a6;
      --clinic-border: #d9eef0;
      --clinic-text: #16323f;
    }
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      background: #eef8fb;
      font-family: 'Plus Jakarta Sans', Arial, sans-serif;
      color: var(--clinic-text);
      padding: 20px;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .consent-container {
      max-width: 900px;
      width: 100%;
      margin: 0 auto;
      background: white;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      padding: 25px 35px;
    }
    .header-logo {
      text-align: center;
      margin-bottom: 15px;
    }
    .header-logo .logo-icon {
      width: 55px;
      height: 55px;
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white;
      border-radius: 16px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
      margin-bottom: 8px;
    }
    .header-logo h1 {
      font-size: 22px;
      font-weight: 900;
      color: var(--clinic-primary);
      margin-bottom: 2px;
    }
    .header-logo p {
      font-size: 11px;
      color: #6b7d87;
    }
    .consent-title {
      text-align: center;
      font-size: 16px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin: 10px 0 12px;
      padding-bottom: 6px;
      border-bottom: 2px solid var(--clinic-border);
    }
    .student-info {
      background: #f0fdfa;
      padding: 10px 15px;
      border-radius: 12px;
      margin-bottom: 15px;
      font-size: 13px;
      border-left: 4px solid var(--clinic-primary);
    }
    .student-info span {
      font-weight: 800;
      color: var(--clinic-primary);
    }
    .content {
      font-size: 12px;
      line-height: 1.5;
      text-align: justify;
      margin-bottom: 15px;
    }
    .content p {
      margin-bottom: 8px;
    }
    .content ul {
      margin: 8px 0;
      padding-left: 20px;
    }
    .content li {
      margin-bottom: 3px;
    }
    .signature-section {
      margin: 20px 0 15px;
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      gap: 15px;
    }
    .signature-line {
      flex: 1;
      text-align: center;
      min-width: 120px;
    }
    .signature-line .line {
      border-bottom: 1px solid #333;
      margin: 20px 0 5px;
      width: 100%;
    }
    .signature-line .name {
      font-weight: 700;
      font-size: 11px;
      white-space: nowrap;
      overflow-x: hidden;
      text-overflow: ellipsis;
    }
    .signature-line .role {
      font-size: 10px;
      color: #6b7d87;
    }
    .date-section {
      text-align: right;
      font-size: 11px;
      margin-top: 10px;
      padding-top: 8px;
      border-top: 1px dashed var(--clinic-border);
    }
    .footer-note {
      text-align: center;
      font-size: 9px;
      color: #6b7d87;
      margin-top: 12px;
    }
    .btn-print, .btn-back, .btn-select {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white;
      border: none;
      border-radius: 10px;
      padding: 8px 18px;
      font-weight: 700;
      font-size: 13px;
      cursor: pointer;
    }
    .btn-back {
      background: white;
      color: var(--clinic-primary);
      border: 1px solid var(--clinic-border);
      text-decoration: none;
    }
    .btn-select {
      background: white;
      color: var(--clinic-primary);
      border: 1px solid var(--clinic-border);
      padding: 5px 12px;
      font-size: 11px;
    }
    .form-select-sm {
      font-size: 12px;
      padding: 5px 10px;
      border-radius: 10px;
      border: 1px solid var(--clinic-border);
    }
    @media print {
      body {
        background: white;
        padding: 0;
        margin: 0;
      }
      .consent-container {
        box-shadow: none;
        padding: 15px 20px;
        max-width: 100%;
      }
      .no-print {
        display: none !important;
      }
      .signature-line .line {
        border-bottom: 1px solid #000;
      }
      .btn-print, .btn-back, .btn-select, .no-print {
        display: none;
      }
      .student-info {
        background: none;
        border: 1px solid #ddd;
      }
      @page {
        size: portrait;
        margin: 1cm;
      }
    }
    @media (max-width: 600px) {
      .consent-container { padding: 20px; }
      .signature-section { flex-direction: column; gap: 10px; }
    }
  </style>
</head>
<body>
<div id="app" class="consent-container">
  <!-- BUTTONS (not printed) -->
  <div class="no-print d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
      <button class="btn-print" @click="printForm">🖨️ Print / Save as PDF</button>
      <a href="nurse-dashboard.php" class="btn-back ms-2">← Back</a>
    </div>
  </div>

  <!-- CONSENT FORM (printed area) -->
  <div class="header-logo">
    <div class="logo-icon">⚕</div>
    <h1>ClinicDesk</h1>
    <p>Student Health Information System</p>
  </div>

  <div class="consent-title">PARENTAL CONSENT FORM</div>

  <div class="student-info">
    <strong>Student:</strong> <span>{{ studentName || '_________________________' }}</span> &nbsp;|&nbsp;
    <strong>School:</strong> <span>{{ schoolName || '_________________________' }}</span> &nbsp;|&nbsp;
    <strong>School Year:</strong> <span>{{ schoolYear }}</span>
  </div>

  <div class="content">
    <p>Dear Parent/Guardian,</p>
    <p>Your child is enrolled in the school's health and nutrition monitoring program under <strong>ClinicDesk</strong>. We request your consent to collect basic health information for:</p>
    <ul>
      <li>Nutritional status monitoring (BMI, height, weight)</li>
      <li>Health assessments and consultation tracking</li>
      <li>Health recommendations and follow-ups</li>
      <li>Anonymized school health reports</li>
    </ul>
    <p>All data is confidential and accessible only to authorized clinic personnel. You may withdraw consent at any time.</p>
  </div>

  <div class="signature-section">
    <div class="signature-line">
      <div class="line"></div>
      <div class="name">{{ parentName || '_________________________' }}</div>
      <div class="role">Signature of Parent / Guardian</div>
    </div>
    <div class="signature-line">
      <div class="line"></div>
      <div class="name">{{ nurseName || '_________________________' }}</div>
      <div class="role">Signature of Clinic Nurse</div>
    </div>
    <div class="signature-line">
      <div class="line"></div>
      <div class="name">{{ principalName || '_________________________' }}</div>
      <div class="role">Signature of School Principal</div>
    </div>
  </div>

  <div class="date-section">
    Date: _________________________
  </div>

  <div class="footer-note">
    ClinicDesk – School Health Monitoring System
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
      studentName: '',
      schoolName: '',
      schoolYear: new Date().getFullYear() + '-' + (new Date().getFullYear() + 1),
      parentName: '',
      nurseName: '',
      principalName: ''
    };
  },
  mounted() {
    this.nurseName = localStorage.getItem('local_full_name') || 'Clinic Nurse';
    this.loadStudents();
    
    // Check URL parameters
    const params = new URLSearchParams(window.location.search);
    if (params.get('student_name')) this.studentName = params.get('student_name');
    if (params.get('school_name')) this.schoolName = params.get('school_name');
  },
  methods: {
    async loadStudents() {
      try {
        const res = await fetch('api/get_students_for_consult.php');
        const data = await res.json();
        if (data.success) this.students = data.students;
      } catch(e) { console.log('Error loading students'); }
    },
    async loadStudentInfo() {
      const student = this.students.find(s => s.record_id == this.selectedStudentId);
      if (student) {
        this.studentName = student.learner_name;
        this.schoolName = student.school_name || 'Tubod National High School';
      }
    },
    fillSampleData() {
      this.studentName = 'Juan Dela Cruz';
      this.schoolName = 'Tubod National High School';
      this.parentName = 'Maria Dela Cruz';
      this.principalName = 'Dr. Maria Santos';
    },
    printForm() {
      window.print();
    }
  }
}).mount('#app');
</script>
</body>
</html>