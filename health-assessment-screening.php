<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | Health Assessment Screening</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
      max-width: 1500px;
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
      background: rgba(255,255,255,0.16);
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
      background: rgba(255,255,255,0.10);
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
      background: rgba(255,255,255,0.18);
      border: 2px solid rgba(255,255,255,0.35);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 30px;
      margin-right: 16px;
      box-shadow: 0 0 28px rgba(255,255,255,0.20);
      flex-shrink: 0;
    }

    .header-box h1 {
      font-size: 38px;
      font-weight: 900;
      margin-bottom: 8px;
    }

    .header-box p {
      font-size: 15px;
      color: rgba(255,255,255,0.92);
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
    }

    .btn-back:hover {
      background: #ecfeff;
      color: var(--clinic-primary);
    }

    .main-grid {
      display: grid;
      grid-template-columns: 360px 1fr;
      gap: 24px;
      align-items: start;
    }

    .card-box {
      background: var(--clinic-card);
      border: 1px solid var(--clinic-border);
      border-radius: var(--clinic-radius);
      box-shadow: var(--clinic-shadow);
      padding: 24px;
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
    }

    .summary-label {
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: var(--clinic-muted);
      font-weight: 800;
      margin-bottom: 6px;
    }

    .summary-value {
      font-size: 26px;
      font-weight: 900;
      color: var(--clinic-primary);
      margin-bottom: 0;
    }

    .summary-helper {
      color: var(--clinic-muted);
      font-size: 12px;
      margin-top: 4px;
      margin-bottom: 0;
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

    .screening-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 14px;
    }

    .screening-item {
      background: #f8fcfd;
      border: 1px solid var(--clinic-border);
      border-radius: 16px;
      padding: 14px;
    }

    .screening-item label {
      color: var(--clinic-text);
      font-weight: 800;
      font-size: 14px;
      margin-bottom: 8px;
    }

    .form-control,
    .form-select {
      border-radius: 14px;
      border: 1px solid var(--clinic-border);
      padding: 11px 13px;
      font-size: 14px;
      background: white;
      color: var(--clinic-text);
    }

    .form-control:focus,
    .form-select:focus {
      border-color: var(--clinic-secondary);
      box-shadow: 0 0 0 0.2rem rgba(20,184,166,0.16);
    }

    .btn-green {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white;
      font-weight: 900;
      border: none;
      border-radius: 14px;
      padding: 11px 16px;
      box-shadow: 0 12px 24px rgba(15,118,110,0.18);
    }

    .btn-green:hover {
      color: white;
      transform: translateY(-1px);
      box-shadow: 0 14px 30px rgba(15,118,110,0.22);
    }

    .btn-outline-clinic {
      border: 1px solid var(--clinic-primary);
      color: var(--clinic-primary);
      background: white;
      font-weight: 900;
      border-radius: 14px;
      padding: 10px 14px;
    }

    .btn-outline-clinic:hover {
      background: var(--clinic-primary);
      color: white;
    }

    .meal-calendar {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 14px;
    }

    .meal-day {
      background: #f8fcfd;
      border: 1px solid var(--clinic-border);
      border-radius: 18px;
      padding: 14px;
      min-height: 260px;
      display: flex;
      flex-direction: column;
    }

    .meal-day.active {
      border-color: var(--clinic-secondary);
      box-shadow: 0 10px 24px rgba(20,184,166,0.16);
      background: var(--clinic-light);
    }

    .day-name {
      font-weight: 900;
      color: var(--clinic-primary);
      margin-bottom: 8px;
    }

    .meal-list {
      padding-left: 18px;
      margin-bottom: 12px;
      color: var(--clinic-text);
      font-size: 13px;
      line-height: 1.6;
    }

    .meal-status {
      margin-top: auto;
    }

    .progress-list {
      display: grid;
      gap: 12px;
    }

    .progress-item {
      background: #f8fcfd;
      border: 1px solid var(--clinic-border);
      border-radius: 16px;
      padding: 14px;
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 12px;
      align-items: center;
    }

    .progress-title {
      font-weight: 900;
      color: var(--clinic-text);
      margin-bottom: 3px;
    }

    .progress-desc {
      color: var(--clinic-muted);
      font-size: 13px;
      margin-bottom: 0;
    }

    .chart-box {
      height: 280px;
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

      .meal-calendar {
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
      .screening-grid,
      .meal-calendar {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
<div id="app" class="wrapper">

  <div class="header-box d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div class="header-content d-flex align-items-center">
      <div class="header-icon">🩺</div>

      <div>
        <h1>Health Assessment Screening</h1>
        <p class="mb-1">
          Monitor student health progress, meal plan compliance, and follow-up status until improvement.
        </p>
        <p class="mb-0">
          Clinic Nurse: <strong>{{ nurseName }}</strong>
        </p>
      </div>
    </div>

    <div class="header-actions">
      <a href="student-dashboard.php" class="btn btn-back">
        Back to Student Dashboard
      </a>
    </div>
  </div>

  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">
    {{ message }}
  </div>

  <div class="main-grid">

    <div class="profile-card card-box">
      <div class="avatar">{{ initials }}</div>

      <div class="student-name">{{ student.learner_name }}</div>
      <div class="muted-text mb-3">
        {{ student.grade_level }} - {{ student.section }} · {{ student.sex }} · {{ student.age }} years old
      </div>

      <div class="d-flex gap-2 flex-wrap mb-4">
        <span class="badge" :class="getBmiBadge(student.bmi_category)">
          {{ student.bmi_category }}
        </span>
        <span class="badge" :class="getRiskBadge(student.risk_level)">
          {{ student.risk_level }} Risk
        </span>
      </div>

      <div class="info-row">
        <div class="info-label">BMI</div>
        <div class="info-value">{{ student.bmi }}</div>
      </div>

      <div class="info-row">
        <div class="info-label">Weight</div>
        <div class="info-value">{{ student.weight_kg }} kg</div>
      </div>

      <div class="info-row">
        <div class="info-label">Height</div>
        <div class="info-value">{{ student.height_m }} m</div>
      </div>

      <div class="info-row">
        <div class="info-label">Height-for-Age</div>
        <div class="info-value">{{ student.height_for_age }}</div>
      </div>

      <div class="info-row">
        <div class="info-label">Current Goal</div>
        <div class="info-value">{{ healthPlan.goal }}</div>
      </div>

      <div class="info-row">
        <div class="info-label">Follow-up</div>
        <div class="info-value">{{ healthPlan.follow_up_date }}</div>
      </div>

      <div class="mt-4">
        <button class="btn btn-green w-100" @click="markFollowUpDone">
          Update Follow-up Status
        </button>
      </div>
    </div>

    <div>
      <div class="summary-grid">
        <div class="summary-card">
          <div class="summary-label">Meal Compliance</div>
          <p class="summary-value">{{ mealCompliance }}%</p>
          <p class="summary-helper">Completed planned meals</p>
        </div>

        <div class="summary-card">
          <div class="summary-label">Screening Score</div>
          <p class="summary-value">{{ screeningScore }}%</p>
          <p class="summary-helper">Health indicators checked</p>
        </div>

        <div class="summary-card">
          <div class="summary-label">Progress Status</div>
          <p class="summary-value" style="font-size: 22px;">{{ healthPlan.status }}</p>
          <p class="summary-helper">Current monitoring status</p>
        </div>

        <div class="summary-card">
          <div class="summary-label">Days Monitored</div>
          <p class="summary-value">{{ daysMonitored }}</p>
          <p class="summary-helper">Active plan duration</p>
        </div>
      </div>

      <div class="card-box mb-4">
        <div class="section-title">
          <h3>Health Assessment Screening</h3>
          <p>Update the student’s health indicators based on clinic observation and interview.</p>
        </div>

        <div class="screening-grid">
          <div class="screening-item">
            <label>Observed Symptoms / Notes</label>
            <textarea v-model="screening.symptoms" rows="3" class="form-control" placeholder="Example: fatigue, pale skin, low appetite"></textarea>
          </div>

          <div class="screening-item">
            <label>Appetite Level</label>
            <select v-model="screening.appetite" class="form-select">
              <option value="Good">Good</option>
              <option value="Fair">Fair</option>
              <option value="Poor">Poor</option>
            </select>
          </div>

          <div class="screening-item">
            <label>Energy Level</label>
            <select v-model="screening.energy" class="form-select">
              <option value="Active">Active</option>
              <option value="Moderate">Moderate</option>
              <option value="Weak">Weak</option>
            </select>
          </div>

          <div class="screening-item">
            <label>Meal Completion</label>
            <select v-model="screening.meal_completion" class="form-select">
              <option value="Complete">Complete</option>
              <option value="Partial">Partial</option>
              <option value="Poor">Poor</option>
            </select>
          </div>

          <div class="screening-item">
            <label>Water Intake</label>
            <select v-model="screening.water_intake" class="form-select">
              <option value="Adequate">Adequate</option>
              <option value="Low">Low</option>
            </select>
          </div>

          <div class="screening-item">
            <label>Clinic Follow-up Needed?</label>
            <select v-model="screening.follow_up_needed" class="form-select">
              <option value="Yes">Yes</option>
              <option value="No">No</option>
            </select>
          </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
          <button class="btn btn-outline-clinic" @click="resetScreening">
            Reset
          </button>

          <button class="btn btn-green" @click="saveScreening">
            Save Screening Update
          </button>
        </div>
      </div>

      <div class="card-box mb-4">
        <div class="section-title">
          <h3>Meal Plan Calendar</h3>
          <p>Recommended meals for each day. The clinic nurse can mark progress daily.</p>
        </div>

        <div class="meal-calendar">
          <div
            class="meal-day"
            v-for="(day, index) in mealPlan"
            :key="index"
            :class="{ active: day.status === 'Completed' }"
          >
            <div class="day-name">{{ day.day }}</div>

            <ul class="meal-list">
              <li><strong>Breakfast:</strong> {{ day.breakfast }}</li>
              <li><strong>Lunch:</strong> {{ day.lunch }}</li>
              <li><strong>Snack:</strong> {{ day.snack }}</li>
              <li><strong>Dinner:</strong> {{ day.dinner }}</li>
            </ul>

            <div class="meal-status">
              <span class="badge mb-2" :class="day.status === 'Completed' ? 'bg-success' : 'bg-warning text-dark'">
                {{ day.status }}
              </span>

              <button class="btn btn-outline-clinic w-100 btn-sm" @click="toggleMealStatus(index)">
                {{ day.status === 'Completed' ? 'Mark Pending' : 'Mark Completed' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4 mb-4">
        <div class="col-lg-6">
          <div class="card-box h-100">
            <h4 class="mb-3">Progress Overview</h4>
            <div class="chart-box">
              <canvas id="progressChart"></canvas>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card-box h-100">
            <h4 class="mb-3">Monitoring Checklist</h4>

            <div class="progress-list">
              <div class="progress-item" v-for="item in monitoringChecklist" :key="item.title">
                <div>
                  <div class="progress-title">{{ item.title }}</div>
                  <p class="progress-desc">{{ item.description }}</p>
                </div>

                <span class="badge" :class="item.done ? 'bg-success' : 'bg-warning text-dark'">
                  {{ item.done ? 'Done' : 'Pending' }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card-box">
        <div class="section-title">
          <h3>Recommendation and Follow-up Plan</h3>
          <p>Suggested intervention based on current nutritional status and health screening.</p>
        </div>

        <div class="alert alert-info mb-3">
          <strong>Recommended Action:</strong> {{ healthPlan.recommendation }}
        </div>

        <div class="alert alert-success mb-0">
          <strong>Follow-up Advice:</strong> {{ healthPlan.follow_up_advice }}
        </div>
      </div>
    </div>
  </div>

</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<script>
const { createApp } = Vue;

createApp({
  data() {
    return {
      nurseName: "",
      recordId: "",
      progressChart: null,

      student: {
        record_id: "",
        learner_name: "Sample Student",
        grade_level: "Grade 7",
        section: "A",
        sex: "Female",
        age: 13,
        weight_kg: 35,
        height_m: 1.45,
        bmi: 16.6,
        bmi_category: "Underweight",
        height_for_age: "Normal",
        risk_level: "Moderate"
      },

      screening: {
        symptoms: "Low appetite and occasional fatigue.",
        appetite: "Fair",
        energy: "Moderate",
        meal_completion: "Partial",
        water_intake: "Adequate",
        follow_up_needed: "Yes"
      },

      healthPlan: {
        goal: "Improve weight and meal consistency",
        status: "Improving",
        follow_up_date: "After 2 weeks",
        recommendation: "Provide balanced meals with protein, iron-rich food, fruits, vegetables, and healthy snacks. Encourage regular breakfast and hydration.",
        follow_up_advice: "Monitor meal completion daily and recheck weight after two weeks. Refer to clinic nurse if fatigue or poor appetite continues."
      },

      mealPlan: [
        {
          day: "Monday",
          breakfast: "Egg, rice, banana",
          lunch: "Chicken, vegetables, rice",
          snack: "Milk and bread",
          dinner: "Fish, soup, rice",
          status: "Completed"
        },
        {
          day: "Tuesday",
          breakfast: "Oatmeal, milk, fruit",
          lunch: "Egg, monggo, rice",
          snack: "Banana or sandwich",
          dinner: "Chicken soup, rice",
          status: "Pending"
        },
        {
          day: "Wednesday",
          breakfast: "Rice, egg, fruit",
          lunch: "Fish, vegetables, rice",
          snack: "Milk and crackers",
          dinner: "Vegetable soup, rice",
          status: "Pending"
        },
        {
          day: "Thursday",
          breakfast: "Bread, peanut butter, milk",
          lunch: "Chicken, squash, rice",
          snack: "Fruit and yogurt",
          dinner: "Egg, vegetables, rice",
          status: "Pending"
        },
        {
          day: "Friday",
          breakfast: "Oatmeal, banana, milk",
          lunch: "Fish, monggo, rice",
          snack: "Sandwich",
          dinner: "Chicken, vegetables, rice",
          status: "Pending"
        },
        {
          day: "Saturday",
          breakfast: "Egg, rice, fruit",
          lunch: "Vegetable soup, rice",
          snack: "Milk and bread",
          dinner: "Fish, rice, vegetables",
          status: "Pending"
        },
        {
          day: "Sunday",
          breakfast: "Rice, egg, milk",
          lunch: "Chicken, vegetables, rice",
          snack: "Fruit",
          dinner: "Soup, rice, fish",
          status: "Pending"
        }
      ]
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

    completedMeals() {
      return this.mealPlan.filter(day => day.status === "Completed").length;
    },

    mealCompliance() {
      return Math.round((this.completedMeals / this.mealPlan.length) * 100);
    },

    screeningScore() {
      let score = 0;

      if (this.screening.appetite === "Good") score += 20;
      if (this.screening.appetite === "Fair") score += 10;

      if (this.screening.energy === "Active") score += 20;
      if (this.screening.energy === "Moderate") score += 10;

      if (this.screening.meal_completion === "Complete") score += 20;
      if (this.screening.meal_completion === "Partial") score += 10;

      if (this.screening.water_intake === "Adequate") score += 20;

      if (this.screening.follow_up_needed === "No") score += 20;
      if (this.screening.follow_up_needed === "Yes") score += 10;

      return score;
    },

    daysMonitored() {
      return this.completedMeals;
    },

    monitoringChecklist() {
      return [
        {
          title: "Meal plan started",
          description: "Student has an assigned weekly meal plan.",
          done: this.mealPlan.length > 0
        },
        {
          title: "Daily meal monitoring",
          description: "At least one daily meal progress has been updated.",
          done: this.completedMeals > 0
        },
        {
          title: "Health screening updated",
          description: "Symptoms, appetite, energy, and water intake were checked.",
          done: this.screening.symptoms.trim() !== ""
        },
        {
          title: "Follow-up plan assigned",
          description: "Student has a follow-up recommendation.",
          done: this.healthPlan.follow_up_date !== ""
        }
      ];
    }
  },

  watch: {
    mealCompliance() {
      this.renderProgressChart();
    },

    screeningScore() {
      this.renderProgressChart();
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

    /*
      Later, replace temporary student data with:
      this.loadStudentHealthPlan();
    */

    this.$nextTick(() => {
      this.renderProgressChart();
    });
  },

  methods: {
    showMessage(type, text) {
      this.messageType = type;
      this.message = text;

      setTimeout(() => {
        this.message = "";
      }, 5000);
    },

    async loadStudentHealthPlan() {
      /*
        Future API integration:

        const response = await fetch("api/get_health_assessment_screening.php?record_id=" + encodeURIComponent(this.recordId));
        const result = await response.json();

        if (result.success) {
          this.student = result.student;
          this.screening = result.screening;
          this.healthPlan = result.health_plan;
          this.mealPlan = result.meal_plan;
          this.$nextTick(() => this.renderProgressChart());
        }
      */
    },

    resetScreening() {
      this.screening = {
        symptoms: "",
        appetite: "Fair",
        energy: "Moderate",
        meal_completion: "Partial",
        water_intake: "Adequate",
        follow_up_needed: "Yes"
      };
    },

    saveScreening() {
      this.showMessage("success", "Health screening update saved temporarily. Database saving will be connected later.");
      this.renderProgressChart();
    },

    markFollowUpDone() {
      this.healthPlan.status = "Follow-up Updated";
      this.showMessage("success", "Follow-up status updated temporarily.");
    },

    toggleMealStatus(index) {
      this.mealPlan[index].status =
        this.mealPlan[index].status === "Completed" ? "Pending" : "Completed";

      this.renderProgressChart();
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
    },

    renderProgressChart() {
      const ctx = document.getElementById("progressChart");

      if (!ctx) return;

      if (this.progressChart) {
        this.progressChart.destroy();
      }

      this.progressChart = new Chart(ctx, {
        type: "doughnut",
        data: {
          labels: ["Meal Compliance", "Screening Score", "Remaining"],
          datasets: [{
            data: [
              this.mealCompliance,
              this.screeningScore,
              Math.max(0, 100 - Math.round((this.mealCompliance + this.screeningScore) / 2))
            ],
            backgroundColor: ["#14b8a6", "#0ea5e9", "#e5e7eb"],
            borderWidth: 0
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: "65%",
          plugins: {
            legend: {
              position: "bottom"
            }
          }
        }
      });
    }
  }
}).mount("#app");
</script>
</body>
</html>