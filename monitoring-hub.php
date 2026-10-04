<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ClinicDesk | Student Monitoring</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    :root{--p:#0f766e;--p2:#14b8a6;--acc:#0ea5e9;--bg:#eef8fb;--card:#fff;--bdr:#d9eef0;--txt:#16323f;--mut:#6b7d87;--sh:0 8px 28px rgba(15,118,110,.09)}
    [v-cloak]{display:none!important}*{box-sizing:border-box}body{min-height:100vh;background:radial-gradient(circle at 8% 0%,rgba(20,184,166,.13),transparent 28%),radial-gradient(circle at 92% 0%,rgba(14,165,233,.1),transparent 28%),linear-gradient(160deg,#eef8fb,#f8fcfd);font-family:'Plus Jakarta Sans',system-ui,sans-serif;color:var(--txt)}
    .wrap{max-width:1600px;margin:auto;padding:24px 20px 60px}.page-header{background:linear-gradient(135deg,var(--p),var(--p2));color:#fff;padding:26px 32px;border-radius:26px;margin-bottom:24px;box-shadow:0 16px 40px rgba(15,118,110,.22);display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;position:relative;overflow:hidden}.page-header:before{content:'';position:absolute;top:-60px;right:-50px;width:180px;height:180px;background:rgba(255,255,255,.11);border-radius:50%}.ph-left,.ph-actions{display:flex;align-items:center;gap:14px;position:relative;z-index:1}.ph-icon{width:52px;height:52px;border-radius:15px;background:rgba(255,255,255,.18);display:grid;place-items:center;font-size:24px}.page-header h1{font-size:24px;font-weight:800;margin:0 0 2px}.page-header p{font-size:13px;opacity:.9;margin:0}.ph-actions{flex-wrap:wrap}.ph-actions select{background:#fff;border:0;border-radius:10px;padding:9px 12px;font-weight:700;color:var(--p)}.btn-back{background:#fff;color:var(--p);border-radius:10px;padding:9px 14px;font-weight:800;text-decoration:none;font-size:13px}
    .program-tabs{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:0;margin-bottom:20px;border:1px solid var(--bdr);border-radius:12px;overflow:hidden}.program-tab{min-width:0;padding:13px 10px;background:#f8fbfc;border:0;border-right:1px solid var(--bdr);border-bottom:1px solid var(--bdr);color:var(--mut);font-weight:700;font-size:13px;cursor:pointer;white-space:normal}.program-tab.active{background:#fff;color:var(--p);box-shadow:inset 0 3px var(--p)}.program-tab:hover{color:var(--p)}
    .stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;margin-bottom:18px}.stat-card,.chart-card,.table-card{background:#fff;border:1px solid var(--bdr);border-radius:18px;box-shadow:var(--sh)}.stat-card{padding:18px;border-bottom:3px solid var(--p2)}.stat-number{font-size:30px;font-weight:800;line-height:1;color:var(--p)}.stat-label{font-size:12px;font-weight:800;margin-top:6px}.stat-hint{font-size:11px;color:var(--mut)}.chart-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px}.chart-card{padding:20px}.chart-card h2{font-size:15px;font-weight:800;color:var(--p);margin-bottom:14px}.chart-box{height:245px;position:relative}
    .toolbar{display:flex;align-items:end;gap:10px;flex-wrap:wrap;margin:18px 0}.toolbar label{display:block;font-size:11px;font-weight:800;color:var(--mut);margin-bottom:5px}.toolbar input,.toolbar select{border:1px solid var(--bdr);border-radius:10px;padding:9px 12px;background:#fff;color:var(--txt);font-size:13px;min-height:40px}.toolbar input:focus,.toolbar select:focus,.modal-panel input:focus,.modal-panel select:focus,.modal-panel textarea:focus{outline:0;border-color:var(--p2);box-shadow:0 0 0 3px rgba(20,184,166,.13)}.toolbar .search{min-width:250px;flex:1}.toolbar .search input{width:100%}.toolbar .spacer{flex:1}.clinic-btn{border:0;border-radius:10px;padding:10px 15px;font-size:12px;font-weight:800;background:linear-gradient(135deg,var(--p),var(--p2));color:#fff;white-space:nowrap}.clinic-btn:disabled{opacity:.6}.clinic-btn-outline{border:1px solid var(--p);background:#fff;color:var(--p)}.clinic-btn:hover{filter:brightness(.95)}.tab-note{font-size:13px;color:var(--mut);margin:-4px 0 16px}.table-card{overflow:hidden}.table-head{padding:17px 20px;border-bottom:1px solid var(--bdr);display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap}.table-head h2{font-size:16px;color:var(--p);font-weight:800;margin:0}.table-head span{font-size:12px;color:var(--mut)}.table-scroll{overflow-x:auto}.monitor-table{width:100%;border-collapse:collapse;font-size:13px}.monitor-table th{background:#f0fdfa;color:var(--p);padding:11px 14px;white-space:nowrap;font-weight:800}.monitor-table td{padding:12px 14px;border-top:1px solid #eef4f5;vertical-align:middle}.monitor-table tbody tr:hover{background:#f8fcfd}.learner-name{font-weight:800}.learner-sub{font-size:11px;color:var(--mut)}.status-chip{display:inline-block;border-radius:999px;padding:5px 9px;background:#e7f7f4;color:#0f766e;font-size:11px;font-weight:800}.status-chip.muted{background:#f1f5f9;color:#64748b}.status-chip.warn{background:#fff4d6;color:#92400e}.status-chip.danger{background:#fee2e2;color:#991b1b}.action-link{color:var(--p);font-weight:800;text-decoration:none;margin-right:10px;white-space:nowrap}.action-link:hover{text-decoration:underline}.table-footer{border-top:1px solid var(--bdr);padding:12px 18px;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;font-size:12px;color:var(--mut)}.table-footer select{border:1px solid var(--bdr);border-radius:8px;padding:5px}.table-footer button{border:1px solid var(--bdr);background:#fff;color:var(--p);border-radius:8px;padding:6px 10px;font-weight:700}.table-footer button:disabled{opacity:.45}.empty{padding:45px 15px;text-align:center;color:var(--mut)}
    .modal-shade{position:fixed;inset:0;background:rgba(13,45,52,.58);z-index:1000;display:flex;align-items:center;justify-content:center;padding:18px}.modal-panel{background:#fff;border-radius:22px;width:min(800px,100%);max-height:min(92vh,980px);overflow:auto;box-shadow:0 25px 70px rgba(9,44,47,.27)}.modal-head{background:linear-gradient(135deg,var(--p),var(--p2));color:#fff;padding:18px 23px;display:flex;justify-content:space-between;align-items:center}.modal-head h2{font-size:18px;font-weight:800;margin:0}.modal-head button{background:none;border:0;color:#fff;font-size:25px;line-height:1}.modal-body{padding:22px}.modal-foot{border-top:1px solid var(--bdr);padding:15px 22px;display:flex;justify-content:flex-end;gap:10px}.modal-panel label{font-size:12px;font-weight:800;color:var(--txt);margin-bottom:5px}.modal-panel input,.modal-panel select,.modal-panel textarea{width:100%;border:1px solid var(--bdr);border-radius:10px;padding:9px 11px;color:var(--txt);background:#fff}.form-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}.form-grid .wide{grid-column:1/-1}.detail-strip{background:#f0fdfa;border:1px solid var(--bdr);padding:13px 15px;border-radius:12px;margin-bottom:17px;font-size:12px}.history-table{width:100%;font-size:12px}.history-table th,.history-table td{border-bottom:1px solid var(--bdr);padding:8px;text-align:left}.history-table th{color:var(--p)}.trend-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.trend-box{height:160px;position:relative;border:1px solid var(--bdr);border-radius:12px;padding:8px}.form-help{font-size:11px;color:var(--mut);margin-top:5px}
    .monitor-table th:first-child,.monitor-table td:first-child{position:sticky;left:0;min-width:190px;z-index:2;box-shadow:2px 0 5px rgba(15,118,110,.08)}.monitor-table th:first-child{z-index:3;background:#f0fdfa}.monitor-table td:first-child{background:#fff}.monitor-table tbody tr:hover td:first-child{background:#f8fcfd}.monitor-table th:not(:first-child),.monitor-table td:not(:first-child){min-width:105px}.monitor-table td.cell-lines{white-space:pre-line;min-width:135px;max-width:210px}.monitor-table th:last-child,.monitor-table td:last-child{min-width:145px}.table-action{display:inline-flex;align-items:center;justify-content:center;gap:5px;padding:7px 10px;margin:2px 5px 2px 0;border:1px solid var(--p);border-radius:9px;background:#fff;color:var(--p);font:inherit;font-weight:800;font-size:11px;text-decoration:none;cursor:pointer;white-space:nowrap}.table-action:hover,.table-action:focus-visible{background:#e7f7f4;color:#0b5e57}.table-action.primary{background:var(--p);color:#fff}.table-action.primary:hover{background:#0b5e57;color:#fff}
    @media(max-width:900px){.chart-grid,.trend-grid{grid-template-columns:1fr}.ph-actions{width:100%}.program-tabs{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:600px){.wrap{padding:12px}.page-header{padding:20px}.page-header h1{font-size:20px}.form-grid{grid-template-columns:1fr}}
    @media print{.page-header,.program-tabs,.toolbar,.table-footer,.action-link,.table-action,.modal-shade{display:none!important}.wrap{max-width:none}.table-card,.chart-card{box-shadow:none}}
  </style>
</head>
<body>
<div id="app" class="wrap" v-cloak>
  <header class="page-header">
    <div class="ph-left"><div class="ph-icon"><i class="bi bi-clipboard2-pulse"></i></div><div><h1>Student Health &amp; Nutrition Monitoring</h1><p>One view for nutrition, school health programs, and nurse follow-ups</p></div></div>
    <div class="ph-actions"><select v-model="schoolYear" @change="loadData" aria-label="School year"><option v-for="year in schoolYears" :key="year" :value="year">{{ year }}</option></select><a href="nurse-dashboard.php" class="btn-back">← Dashboard</a></div>
  </header>
  <nav class="program-tabs" role="tablist" aria-label="Monitoring programs">
    <button v-for="tab in tabs" :key="tab.key" type="button" role="tab" class="program-tab" :class="{active:activeTab===tab.key}" :aria-selected="activeTab===tab.key" @click="selectTab(tab.key)">{{ tab.label }}</button>
  </nav>
  <div v-if="error" class="alert alert-danger">{{ error }}</div>
  <div v-if="notice" class="alert alert-success">{{ notice }}</div>
  <div v-if="loading" class="empty">Loading monitoring records…</div>
  <main v-else>
    <p class="tab-note">{{ tabNote }}</p>
    <div class="stat-grid"><div v-for="card in tabCards" :key="card.label" class="stat-card"><div class="stat-number">{{ card.value }}</div><div class="stat-label">{{ card.label }}</div><div class="stat-hint">{{ card.hint }}</div></div></div>
    <div class="chart-grid"><section v-for="(model,index) in chartModels" :key="activeTab+'-'+index" class="chart-card" :style="index===2 ? 'grid-column:1/-1' : ''"><h2>{{ model.title }}</h2><div class="chart-box"><canvas :id="'monitorChart'+index" role="img" :aria-label="model.title"></canvas></div></section></div>
    <div class="toolbar">
      <div class="search"><label for="monitorSearch">Find learner</label><input id="monitorSearch" v-model.trim="search" type="search" placeholder="Name, LRN, grade, or section"></div>
      <div><label for="gradeFilter">Grade</label><select id="gradeFilter" v-model="gradeFilter"><option value="">All grades</option><option v-for="grade in gradeOptions" :key="grade">{{ grade }}</option></select></div>
      <div><label for="statusFilter">Status</label><select id="statusFilter" v-model="statusFilter"><option value="">All statuses</option><option v-for="status in statusOptions" :key="status">{{ status }}</option></select></div>
      <div class="spacer"></div>
      <button type="button" class="clinic-btn clinic-btn-outline" @click="loadData">Refresh</button>
      <button type="button" class="clinic-btn clinic-btn-outline" @click="exportCsv">Export CSV</button>
      <button type="button" class="clinic-btn clinic-btn-outline" @click="printPage">Print</button>
      <a :href="managerHref" class="clinic-btn clinic-btn-outline" style="text-decoration:none">Edit category records</a>
      <button type="button" class="clinic-btn" @click="openAddStudent">Add student</button>
    </div>
    <section class="table-card">
      <div class="table-head"><h2>{{ activeTabLabel }} learners</h2><span>{{ filteredRecords.length }} of {{ records.length }} learners · {{ schoolYear }}</span></div>
      <div class="table-scroll"><table class="monitor-table"><thead><tr><th>Student</th><th>Grade / Section</th><th v-for="column in programColumns" :key="column.key">{{ column.label }}</th><th>Actions</th></tr></thead><tbody>
        <tr v-if="pageRecords.length===0"><td :colspan="programColumns.length+3" class="empty">No learners match this view.</td></tr>
        <tr v-for="row in pageRecords" :key="row.record_id"><td><div class="learner-name">{{ row.learner_name }}</div><div class="learner-sub">LRN {{ row.lrn || '—' }}</div></td><td>{{ row.grade_level || '—' }} / {{ row.section || '—' }}</td><td v-for="column in programColumns" :key="column.key" class="cell-lines"><span v-if="column.key==='status'" class="status-chip" :class="statusClass(row)">{{ rowStatus(row) }}</span><template v-else>{{ programCell(row,column.key) }}</template></td><td><button type="button" class="table-action primary" @click="goToProfile(row)"><i class="bi bi-pencil-square" aria-hidden="true"></i>View / edit</button><button v-if="activeTab==='feeding'" type="button" class="table-action" @click="openFeeding(row)"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i>Track progress</button></td></tr>
      </tbody></table></div>
      <div class="table-footer"><div><label for="pageSize">Items per page </label><select id="pageSize" v-model.number="pageSize"><option :value="10">10</option><option :value="15">15</option><option :value="20">20</option></select></div><span>{{ filteredRecords.length ? (page-1)*pageSize+1 : 0 }}–{{ Math.min(page*pageSize,filteredRecords.length) }} of {{ filteredRecords.length }}</span><div><button type="button" @click="page--" :disabled="page<=1">Previous</button> <button type="button" @click="page++" :disabled="page>=totalPages">Next</button></div></div>
    </section>
  </main>

  <div v-if="feedingOpen" class="modal-shade" @click.self="feedingOpen=false"><section class="modal-panel" role="dialog" aria-modal="true" aria-label="Feeding progress"><div class="modal-head"><h2>Feeding progress · {{ feedingStudent?.learner_name }}</h2><button type="button" aria-label="Close" @click="feedingOpen=false">×</button></div><div class="modal-body">
    <div class="detail-strip"><strong>SF8 baseline:</strong> Weight {{ feedingStudent?.weight_kg || 'Unknown' }} kg · BMI {{ feedingStudent?.bmi || 'Unknown' }}. Follow-up entries do not replace the SF8 baseline. “Improving” and “Recovered” are nurse assessments.</div>
    <div v-if="feedingError" class="alert alert-danger">{{ feedingError }}</div><div v-if="feedingNotice" class="alert alert-success">{{ feedingNotice }}</div>
    <div v-if="feedingLoading">Loading follow-up history…</div><template v-else><div class="trend-grid mb-3"><div class="trend-box"><canvas id="weightTrend" aria-label="Weight trend"></canvas></div><div class="trend-box"><canvas id="bmiTrend" aria-label="BMI trend"></canvas></div></div>
    <div class="table-scroll mb-3"><table class="history-table"><thead><tr><th>Date</th><th>Weight (kg)</th><th>BMI</th><th>Nurse assessment</th><th>Action</th></tr></thead><tbody><tr v-if="feedingHistory.length===0"><td colspan="5">No follow-up measurements yet.</td></tr><tr v-for="entry in feedingHistory" :key="entry.measurement_id"><td>{{ entry.measured_on }}</td><td>{{ entry.weight_kg ?? '—' }}</td><td>{{ entry.bmi ?? '—' }}</td><td>{{ entry.progress_status || 'Not assessed' }}</td><td><button type="button" class="action-link" style="border:0;background:none" @click="editFeeding(entry)">Correct</button></td></tr></tbody></table></div></template>
    <form @submit.prevent="saveFeeding"><h3 style="font-size:15px;font-weight:800;color:var(--p)">{{ feedingForm.measurement_id ? 'Correct follow-up' : 'Record follow-up' }}</h3><div class="form-grid"><div><label for="measuredOn">Measurement date *</label><input id="measuredOn" v-model="feedingForm.measured_on" type="date" required :max="today"></div><div><label for="progressStatus">Nurse progress assessment</label><select id="progressStatus" v-model="feedingForm.progress_status"><option value="">Not assessed</option><option>Monitoring</option><option>Improving</option><option>Recovered</option><option>Needs follow-up</option></select></div><div><label for="feedingWeight">Weight (kg)</label><input id="feedingWeight" v-model="feedingForm.weight_kg" type="number" step="0.01" min="2" max="500" placeholder="Optional"></div><div><label for="feedingBmi">BMI</label><input id="feedingBmi" v-model="feedingForm.bmi" type="number" step="0.01" min="5" max="100" placeholder="Optional"></div><div class="wide"><label for="feedingNotes">Notes {{ feedingForm.progress_status==='Recovered' ? '*' : '' }}</label><textarea id="feedingNotes" v-model.trim="feedingForm.notes" rows="2" :required="feedingForm.progress_status==='Recovered'" placeholder="Observation or reason for nurse assessment"></textarea><div class="form-help">Enter weight, BMI, or both. No BMI is calculated from an isolated weight entry.</div></div></div><div class="modal-foot" style="padding:15px 0 0"><button v-if="feedingForm.measurement_id" type="button" class="clinic-btn clinic-btn-outline" @click="resetFeedingForm">New entry</button><button type="submit" class="clinic-btn" :disabled="feedingSaving">{{ feedingSaving ? 'Saving…' : (feedingForm.measurement_id ? 'Save correction' : 'Save follow-up') }}</button></div></form>
  </div></section></div>

    <div v-if="addOpen" class="modal-shade" @click.self="addOpen=false"><section class="modal-panel" role="dialog" aria-modal="true" aria-label="Add student"><div class="modal-head"><h2>Add student manually</h2><button type="button" aria-label="Close" @click="addOpen=false">×</button></div><form @submit.prevent="saveStudent"><div class="modal-body"><div class="detail-strip">New learners are saved to the active school year: <strong>{{ activeSchoolYear || 'Not set' }}</strong>. BMI and height-for-age are calculated from the measurements you enter.</div><div v-if="addError" class="alert alert-danger">{{ addError }}</div><div class="form-grid"><div><label>LRN *</label><input v-model.trim="addForm.lrn" required inputmode="numeric"></div><div><label>Learner's name *</label><input v-model.trim="addForm.learner_name" required></div><div><label>Sex *</label><select v-model="addForm.sex" required><option value="">Select</option><option>Female</option><option>Male</option></select></div><div><label>Age (years) *</label><input v-model="addForm.age" type="number" min="5" max="19" step="0.01" required><div class="form-help">Use a decimal age when known (for example, 12.5). Whole years are treated as the start of that year.</div></div><div><label>Birthdate</label><input v-model="addForm.birthdate" type="date"></div><div><label>Grade level</label><input v-model.trim="addForm.grade_level"></div><div><label>Weight (kg) *</label><input v-model="addForm.weight_kg" type="number" min="0.01" step="0.01" required></div><div><label>Height (m) *</label><input v-model="addForm.height_m" type="number" min="0.01" step="0.001" required></div><div><label>Section</label><input v-model.trim="addForm.section"></div><div><label>Track / strand</label><input v-model.trim="addForm.track_strand"></div><div><label>School name</label><input v-model.trim="addForm.school_name"></div><div><label>School ID</label><input v-model.trim="addForm.school_id"></div><div><label>District</label><input v-model.trim="addForm.district"></div><div><label>Division</label><input v-model.trim="addForm.division"></div><div><label>Region</label><input v-model.trim="addForm.region"></div><div class="wide"><label>Remarks</label><textarea v-model.trim="addForm.remarks" rows="2"></textarea></div></div></div><div class="modal-foot"><button type="button" class="clinic-btn clinic-btn-outline" @click="addOpen=false" :disabled="addSaving">Cancel</button><button type="submit" class="clinic-btn" :disabled="addSaving || !activeSchoolYear">{{ addSaving ? 'Saving…' : 'Save student' }}</button></div></form></section></div>
</div>
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script>
const monitoringTabs = [
  {key:'nutrition',label:'Nutrition'}, {key:'feeding',label:'Feeding Program'},
  {key:'wifa',label:'WIFA'}, {key:'deworming',label:'Deworming'},
  {key:'arh',label:'ARH'}, {key:'immunization',label:'Immunization'},
  {key:'screening',label:'OKD & LHAS'}, {key:'tobacco',label:'Tobacco Control'}
];
const emptyProgram = () => ({wifa_baseline:null,wifa_events:[],deworming_baseline:null,
  deworming_events:[],arh:null,immunizations:[],screenings:[],tobacco:null,feeding_measurements:[]});
const given = events => (events || []).some(event => event.outcome === 'Given');
const last = rows => rows && rows.length ? rows[rows.length - 1] : null;
const recordedWifa = p => Number(p.wifa_baseline?.wifa || 0) === 1 || given(p.wifa_events);
const recordedDeworming = p => Number(p.deworming_baseline?.dewormed_sbfp || 0) === 1 ||
  Number(p.deworming_baseline?.dewormed_other || 0) === 1 || given(p.deworming_events);
const monitoringColors = ['#0f766e','#14b8a6','#0ea5e9','#f59e0b','#ef4444','#8b5cf6','#64748b'];

Vue.createApp({
  data() { return {
    tabs:monitoringTabs,activeTab:'nutrition',schoolYear:'',activeSchoolYear:'',schoolYears:[],
    records:[],programs:{},loading:true,error:'',notice:'',search:'',gradeFilter:'',statusFilter:'',page:1,pageSize:10,
    charts:[],feedingCharts:[],feedingOpen:false,feedingStudent:null,feedingHistory:[],feedingLoading:false,
    feedingSaving:false,feedingError:'',feedingNotice:'',
    feedingForm:{measurement_id:null,measured_on:'',weight_kg:'',bmi:'',progress_status:'',notes:''},
    addOpen:false,addSaving:false,addError:'',
    addForm:{lrn:'',learner_name:'',sex:'',birthdate:'',age:'',weight_kg:'',height_m:'',grade_level:'',section:'',
      track_strand:'',school_name:'',school_id:'',district:'',division:'',region:'',remarks:''}
  }; },
  computed: {
    today() { const now = new Date(); return [now.getFullYear(),String(now.getMonth()+1).padStart(2,'0'),String(now.getDate()).padStart(2,'0')].join('-'); },
    activeTabLabel() { return this.tabs.find(tab => tab.key===this.activeTab)?.label || 'Monitoring'; },
    programColumns() { const columns={
      nutrition:[['weight','Weight (kg)'],['height','Height (m)'],['bmi','BMI'],['status','BMI classification'],['hfa','Height for age'],['risk','Risk level']],
      feeding:[['baselineWeight','SF8 weight (kg)'],['baselineBmi','SF8 BMI'],['latestWeight','Latest weight (kg)'],['latestBmi','Latest BMI'],['status','Nurse assessment'],['measurementDate','Measurement date']],
      wifa:[['wifaDose','Intake recorded'],['wifaStart','SF8 starting date'],['wifaLastDose','Last intake date'],['wifaCount','Additional intake dates']],
      deworming:[['dewormSbfp','SBFP dose'],['dewormOther','Other dose'],['dewormLastDose','Last given dose'],['dewormSource','Last source']],
      arh:[['status','Pregnancy status'],['delivery','Delivery mode'],['peerEducator','Peer educator']],
      immunization:[['vaccine','Vaccine'],['dose','Dose'],['immunized','Immunized']],
      screening:[['screeningType','Screening type'],['screened','Screened'],['finding','Findings'],['referral','Referral']],
      tobacco:[['violation','Violation type'],['referredCare','Referred to care']]
    };return columns[this.activeTab].map(([key,label])=>({key,label})); },
    tabNote() { return {
      nutrition:'SF8 nutritional baseline, health assessment, and current risk view. Height for age uses WHO monthly sex-specific cutoffs when age and height are available.',
      feeding:'Feeding review flags and dated weight or BMI follow-ups. Only a nurse marks progress or recovery.',
      wifa:'Dates when iron sulfate was taken, from the SF8 starting record and nurse entries.',
      deworming:'SF8 deworming flags and dated nurse-recorded doses, separated by source.',
      arh:'Adolescent reproductive health records from the approved SF8 form.',
      immunization:'Vaccine and dose records from the approved SF8 form.',
      screening:'OKD and LHAS screening records and referrals from the approved SF8 form.',
      tobacco:'Tobacco control and referral records from the approved SF8 form.'
    }[this.activeTab]; },
    gradeOptions() { return [...new Set(this.records.map(row=>row.grade_level).filter(Boolean))].sort((a,b)=>String(a).localeCompare(String(b),undefined,{numeric:true})); },
    tabRecords() { return this.activeTab==='wifa' ? this.records.filter(row => row.sex==='Female' ||
      Number(this.program(row).wifa_baseline?.wifa || 0)===1 || this.program(row).wifa_events.length)
      : this.records; },
    statusOptions() { return [...new Set(this.tabRecords.map(row=>this.rowStatus(row)))].sort(); },
    filteredRecords() { const q=this.search.toLowerCase(); return this.tabRecords.filter(row => {
      const found=!q || [row.learner_name,row.lrn,row.grade_level,row.section].some(value=>String(value||'').toLowerCase().includes(q));
      return found && (!this.gradeFilter || row.grade_level===this.gradeFilter) &&
        (!this.statusFilter || this.rowStatus(row)===this.statusFilter);
    }); },
    totalPages() { return Math.max(1,Math.ceil(this.filteredRecords.length/this.pageSize)); },
    pageRecords() { return this.filteredRecords.slice((this.page-1)*this.pageSize,this.page*this.pageSize); },
    managerHref() { const category={nutrition:'nutrition',feeding:'nutrition',wifa:'deworming',deworming:'deworming',
      arh:'arh',immunization:'immunization',screening:'lhas',tobacco:'tobacco'}[this.activeTab];
      return 'health-records-manager.php?category='+encodeURIComponent(category); },
    tabCards() { const rows=this.tabRecords,p=row=>this.program(row),n=fn=>rows.filter(fn).length;
      const card=(label,value,hint)=>({label,value,hint});
      if(this.activeTab==='nutrition') return [card('Students',rows.length,'Selected school year'),
        card('Severely wasted',n(r=>r.bmi_category==='Severely Wasted'),'SF8 BMI category'),
        card('Wasted',n(r=>r.bmi_category==='Wasted'),'SF8 BMI category'),
        card('High risk',n(r=>r.predicted_risk_level==='High'),'Current monitoring risk')];
      if(this.activeTab==='feeding') return [card('Flagged for review',n(r=>r.feeding_required==='Yes'),'BMI or risk flag, not enrollment'),
        card('Follow-ups recorded',n(r=>p(r).feeding_measurements.length>0),'Dated measurement'),
        card('Improving',n(r=>last(p(r).feeding_measurements)?.progress_status==='Improving'),'Nurse assessment'),
        card('Recovered',n(r=>last(p(r).feeding_measurements)?.progress_status==='Recovered'),'Nurse assessment')];
      if(this.activeTab==='wifa') return [card('Learners in view',rows.length,'Female or WIFA-tracked'),
        card('Dose recorded',n(r=>recordedWifa(p(r))),'SF8 or nurse entry'),
        card('No intake date',n(r=>!recordedWifa(p(r))),'No dated record'),
        card('Additional dates',rows.reduce((sum,r)=>sum+p(r).wifa_events.length,0),'Nurse-recorded intakes')];
      if(this.activeTab==='deworming') return [card('Learners',rows.length,'Selected school year'),
        card('Dose recorded',n(r=>recordedDeworming(p(r))),'SF8 or nurse entry'),
        card('No dose recorded',n(r=>!recordedDeworming(p(r))),'May need record review'),
        card('SBFP',n(r=>this.hasDewormingChannel(p(r),'SBFP')),'Source recorded')];
      if(this.activeTab==='arh') return [card('ARH records',n(r=>!!p(r).arh),'Imported records'),
        card('No ARH record',n(r=>!p(r).arh),'No approved SF8 data'),
        card('Pregnancy reported',n(r=>p(r).arh?.pregnancy_status==='Pregnant'),'As entered in SF8'),
        card('Peer educators',n(r=>Number(p(r).arh?.peer_educator||0)===1),'As entered in SF8')];
      if(this.activeTab==='immunization') return [card('Recorded immunized',n(r=>p(r).immunizations.some(x=>Number(x.immunized)===1)),'At least one recorded dose'),
        card('Recorded not immunized',n(r=>p(r).immunizations.length>0&&!p(r).immunizations.some(x=>Number(x.immunized)===1)),'SF8 record exists'),
        card('No record',n(r=>!p(r).immunizations.length),'No approved SF8 data'),
        card('Dose rows',rows.reduce((sum,r)=>sum+p(r).immunizations.length,0),'Multiple rows per learner possible')];
      if(this.activeTab==='screening') return [card('Screened',n(r=>p(r).screenings.some(x=>Number(x.screened)===1)),'At least one screening'),
        card('Recorded not screened',n(r=>p(r).screenings.length>0&&!p(r).screenings.some(x=>Number(x.screened)===1)),'SF8 record exists'),
        card('No record',n(r=>!p(r).screenings.length),'No approved SF8 data'),
        card('Referral recorded',n(r=>this.hasReferral(p(r))),'Any referral destination')];
      return [card('Tobacco records',n(r=>!!p(r).tobacco),'Imported records'),
        card('Violation recorded',n(r=>this.hasViolation(p(r))),'As entered in SF8'),
        card('No violation',n(r=>p(r).tobacco&&!this.hasViolation(p(r))),'As entered in SF8'),
        card('Referred to care',n(r=>Number(p(r).tobacco?.referred_to_care||0)===1),'As entered in SF8')];
    },
    chartModels() { const rows=this.tabRecords,p=row=>this.program(row),count=fn=>rows.filter(fn).length;
      const model=(title,type,labels,values)=>({title,type,labels,values});
      if(this.activeTab==='nutrition') return [
        model('BMI category distribution','bar',['Severely Wasted','Wasted','Normal','Overweight','Obese','For review'],
          ['Severely Wasted','Wasted','Normal','Overweight','Obese','For Review'].map(cat=>count(r=>(r.bmi_category||'For Review')===cat))),
        model('Risk level overview','doughnut',['High','Moderate','Low','For review'],['High','Moderate','Low','For Review'].map(x=>count(r=>r.predicted_risk_level===x))),
        model('Height-for-age distribution','bar',['Severely Stunted','Stunted','Normal','Tall','Unrecorded'],
          ['Severely Stunted','Stunted','Normal','Tall','Unrecorded'].map(x=>count(r=>(r.height_for_age||'Unrecorded')===x)))];
      if(this.activeTab==='feeding') return [
        model('Latest nurse progress assessment','doughnut',['Monitoring','Improving','Recovered','Needs follow-up','Not assessed'],
          ['Monitoring','Improving','Recovered','Needs follow-up','Not assessed'].map(x=>count(r=>(last(p(r).feeding_measurements)?.progress_status||'Not assessed')===x))),
        model('Follow-up entries by learner','bar',['None','One','Two or more'],[
          count(r=>p(r).feeding_measurements.length===0),count(r=>p(r).feeding_measurements.length===1),count(r=>p(r).feeding_measurements.length>=2)])];
      if(this.activeTab==='wifa') return [
        model('Iron sulfate intake recorded','doughnut',['Intake recorded','No intake date'],[
          count(r=>recordedWifa(p(r))),count(r=>!recordedWifa(p(r)))]),
        model('Additional intake dates per learner','bar',['None','One','Two or more'],[
          count(r=>p(r).wifa_events.length===0),count(r=>p(r).wifa_events.length===1),count(r=>p(r).wifa_events.length>=2)])];
      if(this.activeTab==='deworming') return [
        model('Deworming administration recorded','doughnut',['Dose recorded','No dose recorded'],[
          count(r=>recordedDeworming(p(r))),count(r=>!recordedDeworming(p(r)))]),
        model('Recorded source','bar',['SBFP','Other'],['SBFP','Other'].map(x=>count(r=>this.hasDewormingChannel(p(r),x))))];
      if(this.activeTab==='arh') return [
        model('ARH pregnancy status as recorded','bar',['Pregnant','Not Pregnant','Other / blank','No record'],[
          count(r=>p(r).arh?.pregnancy_status==='Pregnant'),count(r=>p(r).arh?.pregnancy_status==='Not Pregnant'),
          count(r=>p(r).arh&&!['Pregnant','Not Pregnant'].includes(p(r).arh.pregnancy_status)),count(r=>!p(r).arh)]),
        model('Peer educator flag','doughnut',['Yes','No','No record'],[
          count(r=>Number(p(r).arh?.peer_educator||0)===1),count(r=>p(r).arh&&Number(p(r).arh.peer_educator||0)!==1),count(r=>!p(r).arh)])];
      if(this.activeTab==='immunization') { const vaccineCounts={};rows.forEach(r=>p(r).immunizations.forEach(x=>{
        if(Number(x.immunized)===1){const key=x.vaccine||'Unspecified';vaccineCounts[key]=(vaccineCounts[key]||0)+1;}
      })); const top=Object.entries(vaccineCounts).sort((a,b)=>b[1]-a[1]).slice(0,6);
        return [model('Immunization record status','doughnut',['Immunized entry','Recorded not immunized','No record'],[
          count(r=>p(r).immunizations.some(x=>Number(x.immunized)===1)),
          count(r=>p(r).immunizations.length&&!p(r).immunizations.some(x=>Number(x.immunized)===1)),count(r=>!p(r).immunizations.length)]),
          model('Recorded administered doses by vaccine','bar',top.map(x=>x[0]),top.map(x=>x[1]))]; }
      if(this.activeTab==='screening') { const types={};rows.forEach(r=>p(r).screenings.forEach(x=>{
        if(Number(x.screened)===1){const key=x.screening_type||'Unspecified';types[key]=(types[key]||0)+1;}
      }));const top=Object.entries(types).sort((a,b)=>b[1]-a[1]).slice(0,6);
        return [model('Screening record status','doughnut',['Screened','Recorded not screened','No record'],[
          count(r=>p(r).screenings.some(x=>Number(x.screened)===1)),
          count(r=>p(r).screenings.length&&!p(r).screenings.some(x=>Number(x.screened)===1)),count(r=>!p(r).screenings.length)]),
          model('Recorded screenings by type','bar',top.map(x=>x[0]),top.map(x=>x[1]))]; }
      return [model('Tobacco control status','doughnut',['Violation recorded','No violation','No record'],[
        count(r=>this.hasViolation(p(r))),count(r=>p(r).tobacco&&!this.hasViolation(p(r))),count(r=>!p(r).tobacco)]),
        model('Referral to care flag','bar',['Referred','Not referred'],[
          count(r=>Number(p(r).tobacco?.referred_to_care||0)===1),
          count(r=>p(r).tobacco&&Number(p(r).tobacco.referred_to_care||0)!==1)])];
    }
  },
  watch: {
    activeTab() { this.search='';this.gradeFilter='';this.statusFilter='';this.page=1;
      history.replaceState(null,'','#'+this.activeTab);this.$nextTick(()=>this.renderCharts()); },
    search() { this.page=1; },gradeFilter() { this.page=1; },statusFilter() { this.page=1; },pageSize() { this.page=1; }
  },
  async mounted() {
    if(localStorage.getItem('active_role')!=='Clinic Nurse'||!localStorage.getItem('local_id_token')) {
      window.location.href='login.php';return;
    }
    const hash=window.location.hash.slice(1);if(this.tabs.some(x=>x.key===hash))this.activeTab=hash;
    await this.loadYears();if(this.schoolYear)await this.loadData();
  },
  methods: {
    authHeaders() { return {Authorization:'Bearer '+localStorage.getItem('local_id_token')}; },
    program(row) { return this.programs[String(row.record_id)]||emptyProgram(); },
    hasDewormingChannel(p,channel) { return Number(channel==='SBFP'?p.deworming_baseline?.dewormed_sbfp:p.deworming_baseline?.dewormed_other)===1 ||
      p.deworming_events.some(x=>x.channel===channel&&x.outcome==='Given'); },
    hasReferral(p) { return p.screenings.some(x=>['referred_school','referred_lgu','referred_private','referred_others'].some(key=>Number(x[key])===1)); },
    hasViolation(p) { return !!p.tobacco&&!!p.tobacco.violation_type&&!['none','no violation'].includes(String(p.tobacco.violation_type).trim().toLowerCase()); },
    rowStatus(row) { const p=this.program(row);
      if(this.activeTab==='nutrition')return row.bmi_category||'For Review';
      if(this.activeTab==='feeding')return last(p.feeding_measurements)?.progress_status||
        (p.feeding_measurements.length?'Measured; not assessed':(row.feeding_required==='Yes'?'Flagged for review':'No follow-up'));
      if(this.activeTab==='wifa')return recordedWifa(p)?'Dose recorded':'No dose recorded';
      if(this.activeTab==='deworming')return recordedDeworming(p)?'Dose recorded':'No dose recorded';
      if(this.activeTab==='arh')return p.arh?.pregnancy_status|| (p.arh?'Recorded; status blank':'No record');
      if(this.activeTab==='immunization')return !p.immunizations.length?'No record':
        (p.immunizations.some(x=>Number(x.immunized)===1)?'Immunized entry':'Recorded not immunized');
      if(this.activeTab==='screening')return !p.screenings.length?'No record':
        (p.screenings.some(x=>Number(x.screened)===1)?'Screened':'Recorded not screened');
      return !p.tobacco?'No record':(this.hasViolation(p)?'Violation recorded':'No violation');
    },
    statusClass(row) { const value=this.rowStatus(row).toLowerCase();return {
      muted:value.startsWith('no ')||value.includes('not assessed')||value.includes('blank'),
      warn:value.includes('review')||value.includes('paused')||value.includes('follow-up')||value.includes('wasted'),
      danger:value.includes('violation')||value==='pregnant'||value==='severely wasted'
    }; },
    programCell(row,key) { const p=this.program(row),
      givenDeworm=last(p.deworming_events.filter(x=>x.outcome==='Given')),
      measurement=last(p.feeding_measurements),blank=value=>value===null||value===undefined||value===''?'—':value,
      yn=value=>Number(value)===1?'Yes':'No',lines=(rows,field)=>rows.length?rows.map(field).join('\n'):'—';
      const values={
        weight:blank(row.weight_kg),height:blank(row.height_m),bmi:blank(row.bmi),
        hfa:row.height_for_age||'For review',risk:row.predicted_risk_level||'For review',
        baselineWeight:blank(row.weight_kg),baselineBmi:blank(row.bmi),
        latestWeight:blank(measurement?.weight_kg),latestBmi:blank(measurement?.bmi),measurementDate:blank(measurement?.measured_on),
        wifaDose:recordedWifa(p)?'Recorded':'No dose recorded',
        wifaStart:Number(p.wifa_baseline?.wifa||0)===1?blank(p.wifa_baseline?.wifa_date):'—',
        wifaLastDose:blank([Number(p.wifa_baseline?.wifa||0)===1?p.wifa_baseline?.wifa_date:null,
          ...p.wifa_events.filter(x=>x.outcome==='Given').map(x=>x.event_date)].filter(Boolean).sort().pop()),
        wifaCount:p.wifa_events.length,
        dewormSbfp:this.hasDewormingChannel(p,'SBFP')?'Recorded':'No dose recorded',
        dewormOther:this.hasDewormingChannel(p,'Other')?'Recorded':'No dose recorded',
        dewormLastDose:blank(givenDeworm?.event_date),dewormSource:blank(givenDeworm?.channel),
        delivery:blank(p.arh?.delivery_mode),peerEducator:p.arh?yn(p.arh.peer_educator):'—',
        vaccine:lines(p.immunizations,x=>blank(x.vaccine)),
        dose:lines(p.immunizations,x=>blank(x.dose)),
        immunized:lines(p.immunizations,x=>yn(x.immunized)),
        screeningType:lines(p.screenings,x=>blank(x.screening_type)),
        screened:lines(p.screenings,x=>yn(x.screened)),
        finding:lines(p.screenings,x=>blank(x.findings)),
        referral:lines(p.screenings,x=>{const referrals=[['referred_school','School'],['referred_lgu','LGU'],
          ['referred_private','Private'],['referred_others','Other']].filter(([field])=>Number(x[field])===1).map(([,label])=>label);
          return referrals.length?referrals.join(', '):'—';}),
        violation:blank(p.tobacco?.violation_type),referredCare:p.tobacco?yn(p.tobacco.referred_to_care):'—'
      };return values[key]??'—'; },
    profileHref(row) { const anchor=this.activeTab==='wifa'?'#wifa-monitoring':this.activeTab==='deworming'?'#deworming-monitoring':'';
      return 'student-profile.php?record_id='+encodeURIComponent(row.record_id)+anchor; },
    goToProfile(row) { window.location.href=this.profileHref(row); },
    selectTab(key) { this.activeTab=key; },
    async loadYears() { try { const res=await fetch('api/get_school_years.php?t='+Date.now());const data=await res.json();
      if(!data.success)throw new Error(data.message||'School years could not be loaded.');
      this.schoolYears=(data.years||[]).map(x=>x.year_label);this.activeSchoolYear=data.active||'';
      this.schoolYear=data.active||this.schoolYears[0]||'';
      if(!this.schoolYear)throw new Error('No school year is configured.');
    }catch(e){this.loading=false;this.error=e.message;} },
    async loadData() { if(!this.schoolYear)return;this.loading=true;this.error='';
      try {const query='school_year='+encodeURIComponent(this.schoolYear)+'&t='+Date.now();
        const headers=this.authHeaders();
        const [nutritionResponse,programResponse]=await Promise.all([
          fetch('api/get_monitoring_data.php?'+query,{headers}),fetch('api/get_program_monitoring.php?'+query,{headers})]);
        const [nutrition,programs]=await Promise.all([nutritionResponse.json(),programResponse.json()]);
        if(!nutrition.success)throw new Error(nutrition.message||'Nutrition data could not be loaded.');
        if(!programs.success)throw new Error(programs.message||'Program data could not be loaded.');
        this.records=nutrition.records||[];this.programs=programs.programs||{};this.page=1;
      }catch(e){this.error=e.message;}finally{this.loading=false;await this.$nextTick();this.renderCharts();}
    },
    renderCharts() { this.charts.forEach(chart=>chart.destroy());this.charts=[];if(this.loading)return;
      this.chartModels.forEach((model,index)=>{const canvas=document.getElementById('monitorChart'+index);if(!canvas)return;
        this.charts.push(new Chart(canvas,{type:model.type,data:{labels:model.labels,datasets:[{
          label:'Learners / records',data:model.values,backgroundColor:model.labels.map((_,i)=>monitoringColors[i%monitoringColors.length]),
          borderColor:'#fff',borderWidth:model.type==='doughnut'?2:0,borderRadius:model.type==='bar'?8:0
        }]},options:{responsive:true,maintainAspectRatio:false,animation:false,plugins:{legend:{display:model.type==='doughnut',position:'bottom'}},
          scales:model.type==='bar'?{y:{beginAtZero:true,ticks:{precision:0}}}:{}}}));
      }); },
    resetFeedingForm() { this.feedingForm={measurement_id:null,measured_on:'',weight_kg:'',bmi:'',progress_status:'',notes:''};this.feedingError=''; },
    async openFeeding(row) { this.feedingStudent=row;this.feedingOpen=true;this.feedingNotice='';this.resetFeedingForm();await this.loadFeeding(); },
    async loadFeeding() { this.feedingLoading=true;this.feedingError='';try {
      const res=await fetch('api/feeding_monitoring.php?record_id='+encodeURIComponent(this.feedingStudent.record_id),{headers:this.authHeaders()});
      const data=await res.json();if(!data.success)throw new Error(data.message||'Feeding history could not be loaded.');
      this.feedingHistory=data.measurements||[];
    }catch(e){this.feedingError=e.message;}finally{this.feedingLoading=false;await this.$nextTick();this.drawFeedingCharts();} },
    drawFeedingCharts() { this.feedingCharts.forEach(chart=>chart.destroy());this.feedingCharts=[];
      if(!this.feedingOpen||this.feedingLoading||!this.feedingStudent)return;
      const labels=['SF8 baseline',...this.feedingHistory.map(x=>x.measured_on)];
      [['weightTrend','Weight (kg)','weight_kg','#0f766e'],['bmiTrend','BMI','bmi','#0ea5e9']].forEach(([id,label,key,color])=>{
        const canvas=document.getElementById(id);if(!canvas)return;
        const values=[this.feedingStudent[key]||null,...this.feedingHistory.map(x=>x[key]??null)];
        this.feedingCharts.push(new Chart(canvas,{type:'line',data:{labels,datasets:[{label,data:values,spanGaps:true,
          borderColor:color,backgroundColor:color,tension:.25,pointRadius:4}]},
          options:{responsive:true,maintainAspectRatio:false,animation:false,plugins:{legend:{display:true,position:'bottom'}},scales:{y:{beginAtZero:false}}}}));
      }); },
    editFeeding(entry) { this.feedingForm={measurement_id:entry.measurement_id,measured_on:entry.measured_on,
      weight_kg:entry.weight_kg??'',bmi:entry.bmi??'',progress_status:entry.progress_status||'',notes:entry.notes||''};
      this.feedingError='';this.feedingNotice=''; },
    async saveFeeding() { this.feedingError='';this.feedingNotice='';
      if(this.feedingForm.weight_kg===''&&this.feedingForm.bmi===''){this.feedingError='Enter weight, BMI, or both.';return;}
      this.feedingSaving=true;try {const res=await fetch('api/feeding_monitoring.php',{method:'POST',
        headers:{...this.authHeaders(),'Content-Type':'application/json'},body:JSON.stringify({
          ...this.feedingForm,action:this.feedingForm.measurement_id?'update':'add',record_id:this.feedingStudent.record_id})});
        const data=await res.json();if(!data.success)throw new Error(data.message||'Could not save follow-up.');
        this.feedingNotice=data.message;this.resetFeedingForm();await this.loadFeeding();await this.loadData();
      }catch(e){this.feedingError=e.message;}finally{this.feedingSaving=false;} },
    openAddStudent() { this.addError='';this.addForm={lrn:'',learner_name:'',sex:'',birthdate:'',age:'',weight_kg:'',height_m:'',
      grade_level:'',section:'',track_strand:'',school_name:'',school_id:'',district:'',division:'',region:'',remarks:''};this.addOpen=true; },
    async saveStudent() { this.addError='';if(!this.activeSchoolYear){this.addError='Set an active school year first.';return;}
      this.addSaving=true;try{const res=await fetch('api/add_manual_student.php',{method:'POST',headers:{...this.authHeaders(),'Content-Type':'application/json'},
        body:JSON.stringify({...this.addForm,school_year:this.activeSchoolYear})});const data=await res.json();
        if(!data.success)throw new Error(data.message||'Could not add student.');
        this.addOpen=false;this.notice='Student added to '+this.activeSchoolYear+'.';this.schoolYear=this.activeSchoolYear;await this.loadData();
      }catch(e){this.addError=e.message;}finally{this.addSaving=false;} },
    printPage() { window.print(); },
    exportCsv() { const rows=[['Student','LRN','Grade / Section',...this.programColumns.map(column=>column.label)],
      ...this.filteredRecords.map(row=>[row.learner_name,row.lrn,
        [row.grade_level,row.section].filter(Boolean).join(' / '),...this.programColumns.map(column=>column.key==='status'?this.rowStatus(row):this.programCell(row,column.key))])];
      const csv='\uFEFF'+rows.map(row=>row.map(value=>{let cell=String(value??'');
        if(/^[\s]*[=+\-@]/.test(cell))cell="'"+cell;
        return '"'+cell.replaceAll('"','""')+'"';}).join(',')).join('\r\n');
      const url=URL.createObjectURL(new Blob([csv],{type:'text/csv;charset=utf-8'}));const link=document.createElement('a');
      link.href=url;link.download='monitoring_'+this.activeTab+'_'+this.schoolYear+'.csv';link.click();setTimeout(()=>URL.revokeObjectURL(url),1000); }
  }
}).mount('#app');
</script>
</body>
</html>
