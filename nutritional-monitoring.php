<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | Nutritional Monitoring</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    :root {
      --p:#0f766e; --p2:#14b8a6; --acc:#0ea5e9;
      --bg:#eef8fb; --card:#fff; --bdr:#d9eef0;
      --txt:#16323f; --mut:#6b7d87;
      --sh:0 8px 28px rgba(15,118,110,.09);
      --r:20px;
    }
    *{box-sizing:border-box;margin:0;padding:0;}
    body{min-height:100vh;background:radial-gradient(circle at 8% 0%,rgba(20,184,166,.13),transparent 28%),radial-gradient(circle at 92% 0%,rgba(14,165,233,.10),transparent 28%),linear-gradient(160deg,#eef8fb,#f8fcfd);font-family:'Plus Jakarta Sans',system-ui,sans-serif;color:var(--txt);}
    .wrap{max-width:1600px;margin:0 auto;padding:24px 20px 60px;}

    /* header */
    .page-header{background:linear-gradient(135deg,var(--p),var(--p2));color:#fff;padding:26px 32px;border-radius:26px;margin-bottom:24px;box-shadow:0 16px 40px rgba(15,118,110,.22);display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;position:relative;overflow:hidden;}
    .page-header::before{content:'';position:absolute;top:-60px;right:-50px;width:180px;height:180px;background:rgba(255,255,255,.11);border-radius:50%;}
    .ph-left{display:flex;align-items:center;gap:14px;position:relative;z-index:2;}
    .ph-icon{width:52px;height:52px;border-radius:15px;background:rgba(255,255,255,.18);border:2px solid rgba(255,255,255,.28);display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0;}
    .page-header h1{font-size:24px;font-weight:900;margin-bottom:2px;}
    .page-header p{font-size:13px;color:rgba(255,255,255,.88);margin:0;}
    .btn-back{background:#fff;color:var(--p);border:none;border-radius:11px;padding:8px 15px;font-weight:800;font-size:12.5px;text-decoration:none;box-shadow:0 4px 14px rgba(0,0,0,.09);position:relative;z-index:2;}

    /* stat cards */
    .stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:20px;}
    .stat-card{background:var(--card);border:1px solid var(--bdr);border-radius:16px;padding:16px 18px;box-shadow:var(--sh);cursor:pointer;transition:transform .15s,box-shadow .15s;position:relative;overflow:hidden;}
    .stat-card:hover{transform:translateY(-2px);box-shadow:0 14px 32px rgba(15,118,110,.13);}
    .stat-card.active{border-color:var(--p);box-shadow:0 0 0 2px var(--p),0 8px 24px rgba(15,118,110,.14);}
    .stat-card::after{content:'';position:absolute;bottom:0;left:0;right:0;height:3px;border-radius:0 0 16px 16px;}
    .stat-sev::after{background:#dc2626;}
    .stat-wasted::after{background:#f97316;}
    .stat-normal::after{background:#16a34a;}
    .stat-over::after{background:#d97706;}
    .stat-obese::after{background:#9333ea;}
    .stat-feeding::after{background:#0ea5e9;}
    .stat-assessed::after{background:#0f766e;}
    .stat-total::after{background:#64748b;}
    .sc-num{font-size:32px;font-weight:900;line-height:1;}
    .sc-lbl{font-size:12px;font-weight:700;color:var(--mut);margin-top:4px;}
    .sc-sub{font-size:11px;color:var(--mut);margin-top:2px;}

    /* school year selector */
    .toolbar{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:16px;}
    .toolbar select,.toolbar input{border:1px solid var(--bdr);border-radius:11px;padding:8px 12px;font-size:13.5px;background:#fff;color:var(--txt);font-family:inherit;}
    .toolbar select:focus,.toolbar input:focus{border-color:var(--p2);box-shadow:0 0 0 3px rgba(20,184,166,.13);outline:none;}
    .btn-export{background:linear-gradient(135deg,var(--p),var(--p2));color:#fff;border:none;border-radius:11px;padding:8px 16px;font-weight:800;font-size:13px;cursor:pointer;white-space:nowrap;}
    .btn-export:hover{opacity:.9;}
    .spacer{flex:1;}

    /* tabs */
    .tab-row{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:16px;}
    .tab-btn{background:#fff;border:1px solid var(--bdr);border-radius:10px;padding:7px 14px;font-size:13px;font-weight:700;cursor:pointer;color:var(--mut);transition:all .15s;}
    .tab-btn:hover{border-color:var(--p2);color:var(--p);}
    .tab-btn.active{background:linear-gradient(135deg,var(--p),var(--p2));color:#fff;border-color:transparent;}
    .tab-count{display:inline-block;background:rgba(255,255,255,.25);border-radius:999px;padding:1px 7px;font-size:11px;margin-left:5px;}
    .tab-btn:not(.active) .tab-count{background:#f0fdfa;color:var(--p);}

    /* table card */
    .tcard{background:var(--card);border:1px solid var(--bdr);border-radius:var(--r);box-shadow:var(--sh);overflow:hidden;}
    .tcard-head{padding:16px 20px;border-bottom:1px solid var(--bdr);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;}
    .tcard-title{font-size:15px;font-weight:800;color:var(--p);}
    .tcard-sub{font-size:12.5px;color:var(--mut);margin-top:2px;}
    .table-wrap{overflow-x:auto;}
    table{width:100%;border-collapse:collapse;font-size:13px;}
    thead th{background:#f0fdfa;color:var(--p);font-weight:800;padding:10px 14px;text-align:left;white-space:nowrap;border-bottom:1px solid var(--bdr);}
    tbody tr{border-bottom:1px solid #f0f7f8;transition:background .1s;}
    tbody tr:hover{background:#f8fcfd;}
    tbody td{padding:9px 14px;vertical-align:middle;}
    .learner-cell{display:flex;align-items:center;gap:9px;}
    .av{width:32px;height:32px;border-radius:9px;background:linear-gradient(135deg,var(--p),var(--p2));color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:900;flex-shrink:0;}
    .av-name{font-weight:700;font-size:13px;}
    .av-sub{font-size:11.5px;color:var(--mut);}

    /* badges */
    .badge{display:inline-block;border-radius:999px;padding:3px 9px;font-size:11px;font-weight:800;white-space:nowrap;}
    .b-sev{background:#fee2e2;color:#991b1b;}
    .b-wasted{background:#ffedd5;color:#9a3412;}
    .b-normal{background:#dcfce7;color:#166534;}
    .b-over{background:#fef3c7;color:#92400e;}
    .b-obese{background:#f3e8ff;color:#6b21a8;}
    .b-review{background:#f1f5f9;color:#475569;}
    .b-high{background:#fee2e2;color:#991b1b;}
    .b-moderate{background:#fef3c7;color:#92400e;}
    .b-low{background:#dcfce7;color:#166534;}
    .b-yes{background:#dbeafe;color:#1d4ed8;}
    .b-no{background:#f1f5f9;color:#64748b;}
    .b-feeding{background:#e0f2fe;color:#075985;}

    /* feeding highlight row */
    .feeding-row{background:#f0f9ff !important;}
    .feeding-row:hover{background:#e0f2fe !important;}

    /* chart area */
    .chart-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;}
    @media(max-width:900px){.chart-grid{grid-template-columns:1fr;}}
    .chart-card{background:var(--card);border:1px solid var(--bdr);border-radius:var(--r);box-shadow:var(--sh);padding:20px;}
    .chart-card h4{font-size:14px;font-weight:800;color:var(--p);margin-bottom:14px;}
    .chart-box{position:relative;height:220px;}

    /* feeding program card */
    .feeding-banner{background:linear-gradient(135deg,#0ea5e9,#38bdf8);color:#fff;border-radius:16px;padding:18px 22px;margin-bottom:20px;display:flex;align-items:center;gap:16px;}
    .fb-icon{font-size:36px;flex-shrink:0;}
    .fb-num{font-size:36px;font-weight:900;line-height:1;}
    .fb-lbl{font-size:13px;opacity:.9;}

    /* print */
    @media print{
      .no-print{display:none!important;}
      body{background:#fff;}
      .tcard{box-shadow:none;border:1px solid #ccc;}
    }

    /* empty state */
    .empty{text-align:center;padding:48px 20px;color:var(--mut);}
    .empty i{font-size:40px;display:block;margin-bottom:10px;color:#b2d8d8;}

    /* toast */
    .toast-wrap{position:fixed;top:18px;right:18px;z-index:9999;display:flex;flex-direction:column;gap:8px;pointer-events:none;}
    .toast-item{background:#fff;border-radius:12px;padding:11px 16px;box-shadow:0 10px 28px rgba(0,0,0,.13);border-left:3px solid var(--p);font-size:13px;font-weight:600;pointer-events:all;animation:toastIn .25s ease;}
    .toast-item.error{border-color:#dc2626;color:#991b1b;}
    .toast-item.success{border-color:#16a34a;color:#166534;}
    @keyframes toastIn{from{transform:translateX(110%);opacity:0;}to{transform:none;opacity:1;}}

    @media(max-width:768px){
      .stat-grid{grid-template-columns:repeat(2,1fr);}
      .page-header h1{font-size:20px;}
    }
  </style>
</head>
<body>
<div id="app" class="wrap">

  <!-- Toasts -->
  <div class="toast-wrap">
    <div v-for="t in toasts" :key="t.id" :class="['toast-item',t.type]">{{ t.msg }}</div>
  </div>

  <!-- Header -->
  <div class="page-header no-print">
    <div class="ph-left">
      <div class="ph-icon">📊</div>
      <div>
        <h1>Nutritional Monitoring</h1>
        <p>All students by BMI category · Feeding program list · Health assessment status</p>
      </div>
    </div>
    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;position:relative;z-index:2;">
      <select v-model="schoolYear" @change="loadData" style="border:none;border-radius:11px;padding:8px 14px;font-weight:700;font-size:13px;background:rgba(255,255,255,.18);color:#fff;cursor:pointer;">
        <option v-for="y in schoolYears" :key="y" :value="y">{{ y }}</option>
      </select>
      <a href="nurse-dashboard.php" class="btn-back">← Dashboard</a>
    </div>
  </div>

  <!-- Loading -->
  <div v-if="loading" style="text-align:center;padding:60px 20px;color:var(--mut);">
    <div style="display:inline-block;width:36px;height:36px;border:3px solid #d1fae5;border-top-color:var(--p);border-radius:50%;animation:spin .7s linear infinite;"></div>
    <p style="margin-top:12px;font-size:14px;">Loading student records...</p>
  </div>

  <div v-else>

    <!-- ── Stat cards ── -->
    <div class="stat-grid">
      <div class="stat-card stat-total" :class="{active: activeTab==='all'}" @click="activeTab='all'">
        <div class="sc-num">{{ summary.total }}</div>
        <div class="sc-lbl">All Students</div>
        <div class="sc-sub">This school year</div>
      </div>
      <div class="stat-card stat-sev" :class="{active: activeTab==='Severely Wasted'}" @click="activeTab='Severely Wasted'">
        <div class="sc-num" style="color:#dc2626;">{{ summary.severely_wasted }}</div>
        <div class="sc-lbl">Severely Wasted</div>
        <div class="sc-sub">BMI &lt; 16.0</div>
      </div>
      <div class="stat-card stat-wasted" :class="{active: activeTab==='Wasted'}" @click="activeTab='Wasted'">
        <div class="sc-num" style="color:#ea580c;">{{ summary.wasted }}</div>
        <div class="sc-lbl">Wasted</div>
        <div class="sc-sub">BMI 16.0–18.4</div>
      </div>
      <div class="stat-card stat-normal" :class="{active: activeTab==='Normal'}" @click="activeTab='Normal'">
        <div class="sc-num" style="color:#16a34a;">{{ summary.normal }}</div>
        <div class="sc-lbl">Normal</div>
        <div class="sc-sub">BMI 18.5–24.9</div>
      </div>
      <div class="stat-card stat-over" :class="{active: activeTab==='Overweight'}" @click="activeTab='Overweight'">
        <div class="sc-num" style="color:#d97706;">{{ summary.overweight }}</div>
        <div class="sc-lbl">Overweight</div>
        <div class="sc-sub">BMI 25.0–29.9</div>
      </div>
      <div class="stat-card stat-obese" :class="{active: activeTab==='Obese'}" @click="activeTab='Obese'">
        <div class="sc-num" style="color:#9333ea;">{{ summary.obese }}</div>
        <div class="sc-lbl">Obese</div>
        <div class="sc-sub">BMI ≥ 30.0</div>
      </div>
      <div class="stat-card stat-feeding" :class="{active: activeTab==='feeding'}" @click="activeTab='feeding'">
        <div class="sc-num" style="color:#0284c7;">{{ summary.feeding_required }}</div>
        <div class="sc-lbl">Feeding Program</div>
        <div class="sc-sub">Required attendance</div>
      </div>
      <div class="stat-card stat-assessed" :class="{active: activeTab==='assessed'}" @click="activeTab='assessed'">
        <div class="sc-num" style="color:var(--p);">{{ summary.assessed }}</div>
        <div class="sc-lbl">Assessed</div>
        <div class="sc-sub">Health assessment done</div>
      </div>
    </div>

    <!-- ── Charts ── -->
    <div class="chart-grid no-print">
      <div class="chart-card">
        <h4>BMI Category Distribution</h4>
        <div class="chart-box"><canvas id="bmiChart" role="img" aria-label="BMI category distribution bar chart"></canvas></div>
      </div>
      <div class="chart-card">
        <h4>Risk Level Overview</h4>
        <div class="chart-box"><canvas id="riskChart" role="img" aria-label="Risk level donut chart"></canvas></div>
      </div>
    </div>

    <!-- ── Feeding program banner ── -->
    <div class="feeding-banner no-print" v-if="activeTab === 'feeding' || activeTab === 'all'">
      <div class="fb-icon">🍽️</div>
      <div>
        <div class="fb-num">{{ summary.feeding_required }}</div>
        <div class="fb-lbl">students are required to attend the School Feeding Program<br>
          <small style="opacity:.8;">(Severely Wasted, Wasted, and High ML Risk students)</small>
        </div>
      </div>
    </div>

    <!-- ── Toolbar ── -->
    <div class="toolbar no-print">
      <input v-model="search" type="text" placeholder="🔍  Search by name, grade, section..." style="min-width:220px;">
      <select v-model="gradeFilter">
        <option value="">All Grades</option>
        <option v-for="g in gradeOptions" :key="g" :value="g">{{ g }}</option>
      </select>
      <select v-model="sexFilter">
        <option value="">All Sex</option>
        <option>Male</option>
        <option>Female</option>
      </select>
      <div class="spacer"></div>
      <span style="font-size:13px;color:var(--mut);font-weight:600;">{{ filteredRecords.length }} record{{ filteredRecords.length !== 1 ? 's' : '' }}</span>
      <button class="btn-export" @click="printPage">🖨️ Print</button>
      <button class="btn-export" @click="exportCSV" style="background:linear-gradient(135deg,#16a34a,#22c55e);">⬇️ Export CSV</button>
    </div>

    <!-- ── Tabs ── -->
    <div class="tab-row no-print">
      <button class="tab-btn" :class="{active: activeTab==='all'}" @click="activeTab='all'">
        All <span class="tab-count">{{ summary.total }}</span>
      </button>
      <button class="tab-btn" :class="{active: activeTab==='feeding'}" @click="activeTab='feeding'">
        🍽️ Feeding Program <span class="tab-count">{{ summary.feeding_required }}</span>
      </button>
      <button class="tab-btn" :class="{active: activeTab==='Severely Wasted'}" @click="activeTab='Severely Wasted'">
        Severely Wasted <span class="tab-count">{{ summary.severely_wasted }}</span>
      </button>
      <button class="tab-btn" :class="{active: activeTab==='Wasted'}" @click="activeTab='Wasted'">
        Wasted <span class="tab-count">{{ summary.wasted }}</span>
      </button>
      <button class="tab-btn" :class="{active: activeTab==='Normal'}" @click="activeTab='Normal'">
        Normal <span class="tab-count">{{ summary.normal }}</span>
      </button>
      <button class="tab-btn" :class="{active: activeTab==='Overweight'}" @click="activeTab='Overweight'">
        Overweight <span class="tab-count">{{ summary.overweight }}</span>
      </button>
      <button class="tab-btn" :class="{active: activeTab==='Obese'}" @click="activeTab='Obese'">
        Obese <span class="tab-count">{{ summary.obese }}</span>
      </button>
      <button class="tab-btn" :class="{active: activeTab==='assessed'}" @click="activeTab='assessed'">
        Assessed <span class="tab-count">{{ summary.assessed }}</span>
      </button>
      <button class="tab-btn" :class="{active: activeTab==='high_risk'}" @click="activeTab='high_risk'">
        🔴 High Risk <span class="tab-count">{{ summary.high_risk }}</span>
      </button>
    </div>

    <!-- ── Table ── -->
    <div class="tcard">
      <div class="tcard-head">
        <div>
          <div class="tcard-title">{{ tabLabel }}</div>
          <div class="tcard-sub">{{ filteredRecords.length }} student{{ filteredRecords.length !== 1 ? 's' : '' }} shown</div>
        </div>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Student</th>
              <th>Grade / Section</th>
              <th>Sex</th>
              <th>Age</th>
              <th>BMI</th>
              <th>BMI Category</th>
              <th>Height-for-Age</th>
              <th>Risk Level</th>
              <th>ML Prediction</th>
              <th>Feeding Program</th>
              <th>Assessed</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="filteredRecords.length === 0">
              <td colspan="13" class="empty">
                <i class="bi bi-search"></i>
                No students match this filter.
              </td>
            </tr>
            <tr v-for="(r, idx) in filteredRecords" :key="r.record_id"
                :class="{'feeding-row': r.feeding_required === 'Yes'}">
              <td style="color:var(--mut);font-size:12px;">{{ idx + 1 }}</td>
              <td>
                <div class="learner-cell">
                  <div class="av">{{ initials(r.learner_name) }}</div>
                  <div>
                    <div class="av-name">{{ r.learner_name }}</div>
                    <div class="av-sub">LRN: {{ r.lrn || '—' }}</div>
                  </div>
                </div>
              </td>
              <td>{{ r.grade_level || '—' }} – {{ r.section || '—' }}</td>
              <td>{{ r.sex || '—' }}</td>
              <td>{{ r.age || '—' }}</td>
              <td style="font-weight:800;">{{ r.bmi || '—' }}</td>
              <td><span class="badge" :class="bmiBadge(r.bmi_category)">{{ r.bmi_category || 'For Review' }}</span></td>
              <td>{{ r.height_for_age || '—' }}</td>
              <td><span class="badge" :class="riskBadge(r.predicted_risk_level)">{{ r.predicted_risk_level || 'For Review' }}</span></td>
              <td>
                <span v-if="r.predicted_deficiency" style="font-size:12px;font-weight:600;color:var(--p);">{{ r.predicted_deficiency }}</span>
                <span v-else style="font-size:12px;color:var(--mut);">Not run</span>
              </td>
              <td>
                <span class="badge" :class="r.feeding_required === 'Yes' ? 'b-feeding' : 'b-no'">
                  {{ r.feeding_required === 'Yes' ? '🍽️ Required' : 'Not required' }}
                </span>
              </td>
              <td>
                <span class="badge" :class="r.assessment_date ? 'b-yes' : 'b-no'">
                  {{ r.assessment_date ? '✓ Done' : 'Pending' }}
                </span>
              </td>
              <td>
                <a :href="'student-profile.php?record_id=' + r.record_id"
                   style="background:var(--p);color:#fff;border:none;border-radius:8px;padding:4px 11px;font-size:12px;font-weight:700;text-decoration:none;display:inline-block;">
                  View
                </a>
                <a :href="'health-assessment-screening.php?record_id=' + r.record_id"
                   style="background:#f0fdfa;color:var(--p);border:1px solid var(--bdr);border-radius:8px;padding:4px 11px;font-size:12px;font-weight:700;text-decoration:none;display:inline-block;margin-left:4px;">
                  Assess
                </a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div><!-- /v-else -->

</div><!-- /app -->

<style>@keyframes spin{to{transform:rotate(360deg);}}</style>
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script>
const { createApp } = Vue;

createApp({
  data() {
    return {
      loading: true,
      schoolYear: '',
      schoolYears: [],
      records: [],
      summary: {
        total:0, severely_wasted:0, wasted:0, normal:0,
        overweight:0, obese:0, feeding_required:0, assessed:0,
        high_risk:0, moderate_risk:0, low_risk:0,
      },
      activeTab: 'all',
      search: '',
      gradeFilter: '',
      sexFilter: '',
      charts: {},
      toasts: [],
      toastId: 0,
    };
  },

  computed: {
    gradeOptions() {
      return [...new Set(this.records.map(r => r.grade_level).filter(Boolean))].sort();
    },

    filteredRecords() {
      let list = this.records;

      // tab filter
      if (this.activeTab === 'feeding') {
        list = list.filter(r => r.feeding_required === 'Yes');
      } else if (this.activeTab === 'assessed') {
        list = list.filter(r => !!r.assessment_date);
      } else if (this.activeTab === 'high_risk') {
        list = list.filter(r => (r.predicted_risk_level || '').toLowerCase() === 'high');
      } else if (this.activeTab !== 'all') {
        list = list.filter(r => r.bmi_category === this.activeTab);
      }

      // search
      if (this.search.trim()) {
        const q = this.search.toLowerCase();
        list = list.filter(r =>
          (r.learner_name || '').toLowerCase().includes(q) ||
          (r.grade_level || '').toLowerCase().includes(q) ||
          (r.section || '').toLowerCase().includes(q) ||
          (r.lrn || '').toString().includes(q)
        );
      }

      // grade filter
      if (this.gradeFilter) list = list.filter(r => r.grade_level === this.gradeFilter);

      // sex filter
      if (this.sexFilter) list = list.filter(r => r.sex === this.sexFilter);

      return list;
    },

    tabLabel() {
      const labels = {
        all: 'All Students',
        feeding: '🍽️ Feeding Program — Required Students',
        'Severely Wasted': 'Severely Wasted Students (BMI < 16.0)',
        Wasted: 'Wasted Students (BMI 16.0–18.4)',
        Normal: 'Normal BMI Students',
        Overweight: 'Overweight Students',
        Obese: 'Obese Students',
        assessed: 'Students with Health Assessment',
        high_risk: '🔴 High Risk Students',
      };
      return labels[this.activeTab] || 'Students';
    },
  },

  async mounted() {
    const role = localStorage.getItem('active_role');
    const acct = localStorage.getItem('local_account_id');
    if (role !== 'Clinic Nurse' || !acct) { window.location.href = 'login.php'; return; }

    await this.loadSchoolYears();
    await this.loadData();
  },

  methods: {
    toast(type, msg) {
      const id = ++this.toastId;
      this.toasts.push({ id, type, msg });
      setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 4000);
    },

    async loadSchoolYears() {
      try {
        const res = await fetch(`api/get_school_years.php?t=${Date.now()}`);
        const d = await res.json();
        if (d.success) {
          this.schoolYears = (d.years || []).map(y => y.year_label);
          this.schoolYear = d.active || this.schoolYears[0] || '';
        }
      } catch(e) { console.warn('Could not load school years'); }
    },

    async loadData() {
      this.loading = true;
      try {
        const url = `api/get_monitoring_data.php?school_year=${encodeURIComponent(this.schoolYear)}&t=${Date.now()}`;
        const res = await fetch(url);
        const d = await res.json();
        if (d.success) {
          this.records = d.records;
          this.summary = d.summary;
        } else {
          this.toast('error', d.message || 'Failed to load records.');
        }
      } catch(e) { this.toast('error', 'Network error: ' + e.message); }
      this.loading = false;
      // Wait for v-else to render the canvas elements, then draw charts
      await this.$nextTick();
      await this.$nextTick();
      this.renderCharts();
    },

    renderCharts() {
      // Small timeout ensures the browser has painted the canvas after v-else reveals it
      setTimeout(() => this._drawCharts(), 80);
    },

    _drawCharts() {
      // destroy old
      Object.values(this.charts).forEach(c => { try { c?.destroy(); } catch(e){} });
      this.charts = {};

      // BMI bar
      const bmiCtx = document.getElementById('bmiChart');
      if (bmiCtx) {
        this.charts.bmi = new Chart(bmiCtx, {
          type: 'bar',
          data: {
            labels: ['Severely\nWasted', 'Wasted', 'Normal', 'Overweight', 'Obese'],
            datasets: [{
              label: 'Students',
              data: [
                this.summary.severely_wasted,
                this.summary.wasted,
                this.summary.normal,
                this.summary.overweight,
                this.summary.obese,
              ],
              backgroundColor: ['#dc2626','#f97316','#16a34a','#d97706','#9333ea'],
              borderRadius: 8,
            }]
          },
          options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
          }
        });
      }

      // Risk donut
      const rCtx = document.getElementById('riskChart');
      if (rCtx) {
        this.charts.risk = new Chart(rCtx, {
          type: 'doughnut',
          data: {
            labels: ['High Risk', 'Moderate Risk', 'Low Risk'],
            datasets: [{
              data: [this.summary.high_risk, this.summary.moderate_risk, this.summary.low_risk],
              backgroundColor: ['#dc2626','#f59e0b','#16a34a'],
              borderWidth: 2,
              borderColor: '#fff',
            }]
          },
          options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
              legend: { position: 'bottom', labels: { font: { size: 12 }, padding: 16 } }
            }
          }
        });
      }
    },

    initials(name) {
      if (!name) return '?';
      return name.split(' ').filter(Boolean).slice(0,2).map(p => p[0].toUpperCase()).join('');
    },

    bmiBadge(cat) {
      const c = (cat || '').toLowerCase();
      if (c.includes('severely')) return 'b-sev';
      if (c.includes('wasted'))   return 'b-wasted';
      if (c.includes('normal'))   return 'b-normal';
      if (c.includes('overweight')) return 'b-over';
      if (c.includes('obese'))    return 'b-obese';
      return 'b-review';
    },

    riskBadge(risk) {
      const r = (risk || '').toLowerCase();
      if (r === 'high')     return 'b-high';
      if (r === 'moderate') return 'b-moderate';
      if (r === 'low')      return 'b-low';
      return 'b-review';
    },

    printPage() { window.print(); },

    exportCSV() {
      const cols = ['#','Name','LRN','Grade','Section','Sex','Age','BMI','BMI Category','Height-for-Age','Risk Level','ML Prediction','Feeding Required','Assessed'];
      const rows = this.filteredRecords.map((r, i) => [
        i+1, r.learner_name, r.lrn, r.grade_level, r.section, r.sex, r.age,
        r.bmi, r.bmi_category, r.height_for_age, r.predicted_risk_level,
        r.predicted_deficiency || 'Not run',
        r.feeding_required, r.assessment_date ? 'Done' : 'Pending'
      ]);
      const csv = [cols, ...rows].map(r => r.map(v => `"${(v??'').toString().replace(/"/g,'""')}"`).join(',')).join('\n');
      const blob = new Blob([csv], { type: 'text/csv' });
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = `nutritional_monitoring_${this.schoolYear || 'all'}.csv`;
      a.click();
    },
  }
}).mount('#app');
</script>
</body>
</html>