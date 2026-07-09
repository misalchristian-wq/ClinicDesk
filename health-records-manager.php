<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ClinicDesk | Health Records Manager</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    :root {
      --clinic-primary:#0f766e; --clinic-secondary:#14b8a6; --clinic-accent:#0ea5e9;
      --clinic-border:#d9eef0; --clinic-text:#16323f; --clinic-muted:#6b7d87;
      --clinic-shadow:0 12px 32px rgba(15,118,110,0.10);
    }
    *{box-sizing:border-box;}
    body{margin:0;min-height:100vh;color:var(--clinic-text);
      background:linear-gradient(135deg,#eef8fb,#f8fcfd);
      font-family:'Inter',system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;}
    .wrap{max-width:1400px;margin:24px auto;padding:20px;}
    .page-header{background:linear-gradient(135deg,var(--clinic-primary),var(--clinic-secondary));
      color:#fff;padding:26px 32px;border-radius:26px;margin-bottom:24px;
      box-shadow:0 16px 38px rgba(15,118,110,0.22);
      display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;position:relative;}
    .page-header::before{content:"";position:absolute;top:0;right:0;width:150px;height:150px;
      background:rgba(255,255,255,0.08);border-radius:0 26px 0 60px;pointer-events:none;}
    .ph-left{display:flex;align-items:center;gap:16px;position:relative;z-index:2;}
    .ph-icon{width:56px;height:56px;border-radius:18px;background:rgba(255,255,255,0.18);
      border:1px solid rgba(255,255,255,0.28);display:flex;align-items:center;justify-content:center;font-size:26px;}
    .ph-title{font-size:26px;font-weight:800;margin:0 0 3px;}
    .ph-sub{font-size:13px;opacity:0.92;margin:0;}
    .btn-back{background:#fff;color:var(--clinic-primary);border:none;border-radius:13px;
      padding:10px 18px;font-weight:800;text-decoration:none;box-shadow:0 8px 18px rgba(0,0,0,0.12);position:relative;z-index:2;}
    .btn-back:hover{background:#ecfeff;color:#0f5b55;}
    .card{background:#fff;border:1px solid var(--clinic-border);border-radius:20px;
      box-shadow:var(--clinic-shadow);}
    .btn-green{background:linear-gradient(135deg,var(--clinic-primary),var(--clinic-secondary));
      color:#fff;font-weight:700;border:none;}
    .btn-green:hover{background:linear-gradient(135deg,#115e59,#0f766e);color:#fff;}
    .btn-outline-clinic{border:1px solid var(--clinic-primary);color:var(--clinic-primary);font-weight:700;background:#fff;}
    .btn-outline-clinic:hover{background:var(--clinic-primary);color:#fff;}
    .btn-outline-danger{border:1px solid #dc2626;color:#dc2626;font-weight:700;background:#fff;}
    .btn-outline-danger:hover{background:#dc2626;color:#fff;}
    .form-control, .form-select {
     border-radius: 10px;
     border: 1px solid #000000;
    }
    .form-control:focus,.form-select:focus{border-color:var(--clinic-secondary);box-shadow:0 0 0 .2rem rgba(20,184,166,.15);}
    .chart-box{height:260px;}
    .small-note{font-size:.85rem;color:var(--clinic-muted);}
    table th{background:#e8f5f6;color:#1e3b44;font-weight:700;white-space:nowrap;}
    .mode-toggle .btn{min-width:120px;}
    .mode-toggle .btn.active{background:var(--clinic-primary);color:#fff;border-color:var(--clinic-primary);}
    .search-row{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:16px;}
    .search-row .form-control{flex:1;min-width:200px;}

    /* Modal styles */
    .cd-modal-overlay{position:fixed;inset:0;background:rgba(15,50,63,0.45);display:flex;align-items:flex-start;justify-content:center;z-index:1080;padding:30px 16px;overflow-y:auto;}
    .cd-modal{background:#fff;border-radius:20px;width:100%;max-width:760px;box-shadow:0 24px 60px rgba(0,0,0,0.25);overflow:hidden;margin:auto;}
    .cd-modal-head{display:flex;align-items:center;justify-content:space-between;padding:18px 24px;background:linear-gradient(135deg,var(--clinic-primary),var(--clinic-secondary));color:#fff;}
    .cd-modal-close{background:transparent;border:none;color:#fff;font-size:1.6rem;line-height:1;cursor:pointer;}
    .cd-modal-body{padding:22px 24px;max-height:65vh;overflow-y:auto;}
    .cd-modal-foot{display:flex;justify-content:flex-end;gap:10px;padding:16px 24px;border-top:1px solid #e6f1f2;}
    .delete-modal .cd-modal-head{background:linear-gradient(135deg,#dc2626,#ef4444);}
    @media (max-width:768px){.wrap{padding:12px;}}
  </style>
</head>
<body>
<div id="app" class="wrap">

  <div class="page-header">
    <div class="ph-left">
      <div class="ph-icon">🗂️</div>
      <div>
        <h1 class="ph-title">Health Records Manager</h1>
        <p class="ph-sub">Manage category records – search, edit, add, delete.</p>
      </div>
    </div>
    <a href="student-dashboard.php" class="btn-back">← Back to Student Dashboard</a>
  </div>

  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">{{ message }}</div>

  <!-- Mode Toggle – only category mode -->
  <div class="card p-3 mb-3">
    <div class="mode-toggle d-flex gap-2">
      <button class="btn btn-outline-clinic active" disabled>📂 Category Records</button>
    </div>
  </div>

  <!-- ============================================================ -->
  <!-- CATEGORY MODE -->
  <!-- ============================================================ -->
  <div class="card p-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
      <div>
        <h4 class="mb-1 fw-bold" style="color:var(--clinic-primary);">Records</h4>
        <p class="small-note mb-0">Columns and the chart follow the selected category. Use Actions to edit or delete.</p>
      </div>
      <div class="d-flex gap-2 align-items-end flex-wrap">
        <div>
          <label class="form-label mb-1">Category</label>
          <select v-model="rmCategory" class="form-select" @change="loadCategoryRecords">
            <option value="nutrition">Nutrition (SF8)</option>
            <option value="lhas">OKD &amp; LHAS</option>
            <option value="deworming">Deworming &amp; WIFA</option>
            <option value="immunization">Immunization</option>
            <option value="arh">Adolescent Reproductive Health</option>
            <option value="tobacco">Tobacco Control</option>
          </select>
        </div>
        <button class="btn btn-outline-clinic" @click="loadCategoryRecords" :disabled="rmLoading">
          {{ rmLoading ? "Loading..." : "Refresh" }}
        </button>
        <!-- Add Record button -->
        <button class="btn btn-green" @click="openAddCategoryModal">
          + Add Record
        </button>
      </div>
    </div>

    <div v-if="rmMessage" class="alert" :class="rmMessageType === 'success' ? 'alert-success' : 'alert-danger'">
      {{ rmMessage }}
    </div>

    <!-- Chart -->
    <div class="chart-box" style="margin-bottom:18px;">
      <canvas id="categoryChart"></canvas>
    </div>

    <!-- Search bar -->
    <div class="search-row">
      <input type="text" class="form-control" v-model="rmSearch" placeholder="Search records (name, LRN, grade, section...)" @input="updateFilter">
      <button class="btn btn-outline-clinic" @click="rmSearch = ''; updateFilter()">Clear</button>
      <span class="text-muted small align-self-center" v-if="rmRecords.length > 0">{{ filteredRecords.length }} / {{ rmRecords.length }} shown</span>
    </div>

    <!-- Bulk edit bar -->
    <div v-if="rmSelected.length > 0" class="d-flex gap-2 align-items-end flex-wrap p-3 mb-2"
         style="background:#f0fdfa;border:1px solid #ccf0e0;border-radius:12px;">
      <div>
        <label class="form-label mb-1 fw-bold">{{ rmSelected.length }} selected — set field</label>
        <select v-model="rmBulkField" class="form-select form-select-sm" style="min-width:160px;">
          <option value="">Choose field…</option>
          <option v-for="(meta,key) in rmEditableFields" :key="'bf-'+key" :value="key">{{ meta.label }}</option>
        </select>
      </div>
      <div v-if="rmBulkField">
        <label class="form-label mb-1">Value</label>
        <select v-if="rmFieldType(rmBulkField)==='bool'" v-model="rmBulkValue" class="form-select form-select-sm">
          <option value="1">Yes</option><option value="0">No</option>
        </select>
        <select v-else-if="rmFieldType(rmBulkField)==='enum'" v-model="rmBulkValue" class="form-select form-select-sm">
          <option v-for="opt in rmFieldOptions(rmBulkField)" :key="opt" :value="opt">{{ opt || "(blank)" }}</option>
        </select>
        <input v-else v-model="rmBulkValue" :type="rmInputType(rmBulkField)" class="form-control form-control-sm">
      </div>
      <button class="btn btn-green btn-sm" @click="applyBulkEdit" :disabled="!rmBulkField || rmSaving">
        {{ rmSaving ? "Applying..." : "Apply to selected" }}
      </button>
      <button class="btn btn-outline-clinic btn-sm" @click="rmSelected=[]">Clear selection</button>
    </div>

    <!-- Table -->
    <div class="table-responsive">
      <table class="table table-sm align-middle">
        <thead>
          <tr>
            <th style="width:34px;"><input type="checkbox" :checked="rmAllSelected" @change="toggleSelectAll"></th>
            <th v-for="(meta,key) in rmFields" :key="'h-'+key">{{ meta.label }}</th>
            <th style="text-align:center;width:100px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="filteredRecords.length === 0 && !rmLoading">
            <td :colspan="Object.keys(rmFields).length + 2" class="text-center text-muted py-3">
              No records match your search.
            </td>
          </tr>
          <tr v-for="rec in filteredRecords" :key="rec[rmPk]">
            <td><input type="checkbox" :value="rec[rmPk]" v-model="rmSelected"></td>
            <td v-for="(meta,key) in rmFields" :key="'c-'+rec[rmPk]+'-'+key">
              <!-- For nutrition category, compute BMI category if missing -->
              <span v-if="rmCategory === 'nutrition' && key === 'bmi_category'">
                {{ getComputedBmiCategory(rec) }}
              </span>
              <span v-else>{{ rec[key] !== undefined && rec[key] !== null ? rec[key] : '—' }}</span>
            </td>
            <td style="text-align:center;white-space:nowrap;">
              <button class="btn btn-outline-clinic btn-sm" @click="openEditCategoryModal(rec)" title="Edit">✏️</button>
              <button class="btn btn-outline-danger btn-sm" @click="openDeleteCategoryModal(rec)" title="Delete">🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="filteredRecords.length > 0" class="d-flex justify-content-end mt-2">
      <button class="btn btn-green" @click="saveAllRows" :disabled="rmSaving">
        {{ rmSaving ? "Saving..." : "Save All Changes" }}
      </button>
    </div>
  </div>

  <!-- ============================================================ -->
  <!-- MODALS -->
  <!-- ============================================================ -->

  <!-- Edit Category Record Modal -->
  <div v-if="editCategoryModal.show" class="cd-modal-overlay" @click.self="editCategoryModal.show = false">
    <div class="cd-modal">
      <div class="cd-modal-head">
        <h5 class="fw-bold mb-0">✏️ Edit Record</h5>
        <button class="cd-modal-close" @click="editCategoryModal.show = false">&times;</button>
      </div>
      <div class="cd-modal-body">
        <div v-if="editCategoryModal.error" class="alert alert-danger py-2">{{ editCategoryModal.error }}</div>
        <div class="row g-3">
          <div v-for="(meta,key) in rmEditableFields" :key="key" class="col-md-6">
            <label class="form-label">{{ meta.label }}</label>
            <select v-if="meta.type==='bool'" v-model.number="editCategoryModal.form[key]" class="form-select">
              <option :value="1">Yes</option><option :value="0">No</option>
            </select>
            <select v-else-if="meta.type==='enum'" v-model="editCategoryModal.form[key]" class="form-select">
              <option v-for="opt in meta.options" :key="opt" :value="opt">{{ opt || "(blank)" }}</option>
            </select>
            <input v-else v-model="editCategoryModal.form[key]" :type="rmInputTypeFor(meta.type)" class="form-control">
          </div>
        </div>
      </div>
      <div class="cd-modal-foot">
        <button class="btn btn-secondary" @click="editCategoryModal.show = false" :disabled="editCategoryModal.saving">Cancel</button>
        <button class="btn btn-green" @click="saveCategoryEdit" :disabled="editCategoryModal.saving">
          {{ editCategoryModal.saving ? "Saving..." : "Save Changes" }}
        </button>
      </div>
    </div>
  </div>

  <!-- Delete Category Record Modal -->
  <div v-if="deleteCategoryModal.show" class="cd-modal-overlay delete-modal" @click.self="deleteCategoryModal.show = false">
    <div class="cd-modal">
      <div class="cd-modal-head" style="background:linear-gradient(135deg,#dc2626,#ef4444);">
        <h5 class="fw-bold mb-0">⚠️ Delete Record</h5>
        <button class="cd-modal-close" @click="deleteCategoryModal.show = false">&times;</button>
      </div>
      <div class="cd-modal-body">
        <p>Are you sure you want to delete this record?</p>
        <p class="text-danger">This action cannot be undone.</p>
        <label class="form-label">Enter your password to confirm:</label>
        <input type="password" class="form-control" v-model="deleteCategoryModal.password" placeholder="Password" @keyup.enter="confirmDeleteCategory">
        <div v-if="deleteCategoryModal.error" class="text-danger mt-2">{{ deleteCategoryModal.error }}</div>
      </div>
      <div class="cd-modal-foot">
        <button class="btn btn-secondary" @click="deleteCategoryModal.show = false" :disabled="deleteCategoryModal.deleting">Cancel</button>
        <button class="btn btn-danger" @click="confirmDeleteCategory" :disabled="deleteCategoryModal.deleting">
          {{ deleteCategoryModal.deleting ? "Deleting..." : "Delete Permanently" }}
        </button>
      </div>
    </div>
  </div>

  <!-- Add Category Record Modal -->
  <div v-if="addCategoryModal.show" class="cd-modal-overlay" @click.self="addCategoryModal.show = false">
    <div class="cd-modal">
      <div class="cd-modal-head">
        <h5 class="fw-bold mb-0">➕ Add New Record</h5>
        <button class="cd-modal-close" @click="addCategoryModal.show = false">&times;</button>
      </div>
      <div class="cd-modal-body">
        <div v-if="addCategoryModal.error" class="alert alert-danger py-2">{{ addCategoryModal.error }}</div>
        <div class="row g-3">
          <div v-for="(meta,key) in rmEditableFields" :key="key" class="col-md-6">
            <label class="form-label">{{ meta.label }}</label>
            <select v-if="meta.type==='bool'" v-model.number="addCategoryModal.form[key]" class="form-select">
              <option :value="1">Yes</option><option :value="0">No</option>
            </select>
            <select v-else-if="meta.type==='enum'" v-model="addCategoryModal.form[key]" class="form-select">
              <option v-for="opt in meta.options" :key="opt" :value="opt">{{ opt || "(blank)" }}</option>
            </select>
            <input v-else v-model="addCategoryModal.form[key]" :type="rmInputTypeFor(meta.type)" class="form-control">
          </div>
        </div>
      </div>
      <div class="cd-modal-foot">
        <button class="btn btn-secondary" @click="addCategoryModal.show = false" :disabled="addCategoryModal.saving">Cancel</button>
        <button class="btn btn-green" @click="saveCategoryAdd" :disabled="addCategoryModal.saving">
          {{ addCategoryModal.saving ? "Saving..." : "Add Record" }}
        </button>
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
      activeSchoolYear: '',
      message: '',
      messageType: 'success',

      // ---- Category mode ----
      rmCategory: 'nutrition',
      rmFields: {},
      rmPk: 'record_id',
      rmRecords: [],
      rmSelected: [],
      rmLoading: false,
      rmSaving: false,
      rmBulkField: '',
      rmBulkValue: '',
      rmSearch: '',
      categoryChart: null,

      // Category modals
      editCategoryModal: {
        show: false,
        record: null,
        form: {},
        saving: false,
        error: ''
      },
      deleteCategoryModal: {
        show: false,
        record: null,
        password: '',
        deleting: false,
        error: ''
      },
      addCategoryModal: {
        show: false,
        form: {},
        saving: false,
        error: ''
      }
    };
  },
  computed: {
    rmEditableFields() {
      const out = {};
      for (const [k, meta] of Object.entries(this.rmFields)) if (meta.edit) out[k] = meta;
      return out;
    },
    filteredRecords() {
      if (!this.rmSearch) return this.rmRecords;
      const q = this.rmSearch.toLowerCase();
      return this.rmRecords.filter(rec => {
        for (const val of Object.values(rec)) {
          if (val !== null && val !== undefined && String(val).toLowerCase().includes(q)) {
            return true;
          }
        }
        return false;
      });
    },
    rmAllSelected() {
      return this.filteredRecords.length > 0 && this.rmSelected.length === this.filteredRecords.length;
    }
  },
  mounted() {
    this.loadActiveSchoolYear();
    this.loadCategoryRecords();
  },
  methods: {
    showMessage(type, text) {
      this.messageType = type;
      this.message = text;
      setTimeout(() => { this.message = ''; }, 5000);
    },
    updateFilter() {
      // computed handles it
    },

    // ---- Active School Year ----
    async loadActiveSchoolYear() {
      try {
        const res = await fetch('api/get_school_years.php?t=' + Date.now());
        const data = await res.json();
        if (data.success && data.active) this.activeSchoolYear = data.active;
      } catch (e) { console.warn('Could not load active school year', e); }
    },

    // ============================================================
    // CATEGORY METHODS
    // ============================================================
    rmFieldType(key) { return (this.rmFields[key] || {}).type || 'text'; },
    rmFieldOptions(key) { return (this.rmFields[key] || {}).options || []; },
    rmInputType(key) { const t = this.rmFieldType(key); if (t === 'int' || t === 'float') return 'number'; if (t === 'date') return 'date'; return 'text'; },
    rmInputTypeFor(t) { if (t === 'int' || t === 'float') return 'number'; if (t === 'date') return 'date'; return 'text'; },
    toggleSelectAll(e) {
      if (e.target.checked) {
        this.rmSelected = this.filteredRecords.map(r => r[this.rmPk]);
      } else {
        this.rmSelected = [];
      }
    },

    async loadCategoryRecords() {
      this.rmLoading = true;
      this.rmSelected = [];
      this.rmBulkField = '';
      this.rmBulkValue = '';
      this.rmSearch = '';
      try {
        const url = 'api/get_category_records.php?category=' + encodeURIComponent(this.rmCategory) +
          '&school_year=' + encodeURIComponent(this.activeSchoolYear || '') + '&t=' + Date.now();
        const res = await fetch(url);
        const text = await res.text();
        let data;
        try {
          data = JSON.parse(text);
        } catch (parseError) {
          console.error('Raw response:', text);
          this.showMessage('error', 'Server returned an error: ' + text.substring(0, 200));
          this.rmLoading = false;
          return;
        }
        if (data.success) {
          this.rmFields = data.fields || {};
          this.rmPk = data.pk;
          this.rmRecords = data.records || [];
          this.$nextTick(() => this.renderCategoryChart());
        } else {
          this.showMessage('error', data.message || 'Could not load records.');
        }
      } catch (e) {
        this.showMessage('error', 'Network error: ' + e.message);
      }
      this.rmLoading = false;
    },

    // ===== ENHANCED: compute BMI from weight/height if needed =====
    getComputedBmiCategory(rec) {
      // First try stored bmi_category
      if (rec.bmi_category) return rec.bmi_category;
      
      // Try to compute BMI from weight and height
      let bmi = parseFloat(rec.bmi);
      if (isNaN(bmi) && rec.weight_kg && rec.height_m) {
        const weight = parseFloat(rec.weight_kg);
        const height = parseFloat(rec.height_m);
        if (weight > 0 && height > 0) {
          bmi = weight / (height * height);
        }
      }
      if (isNaN(bmi) || bmi <= 0) return '—';
      if (bmi < 16) return 'Severely Wasted';
      if (bmi < 18.5) return 'Wasted';
      if (bmi < 25) return 'Normal';
      if (bmi < 30) return 'Overweight';
      return 'Obese';
    },

    renderCategoryChart() {
      const ctx = document.getElementById('categoryChart');
      if (!ctx) return;
      if (this.categoryChart) this.categoryChart.destroy();
      const cat = this.rmCategory;

      // ===== ENHANCED: compute BMI from weight/height if needed =====
      const getBmiCat = (r) => {
        if (r.bmi_category) return r.bmi_category;
        let bmi = parseFloat(r.bmi);
        if (isNaN(bmi) && r.weight_kg && r.height_m) {
          const weight = parseFloat(r.weight_kg);
          const height = parseFloat(r.height_m);
          if (weight > 0 && height > 0) {
            bmi = weight / (height * height);
          }
        }
        if (isNaN(bmi) || bmi <= 0) return 'Unknown';
        if (bmi < 16) return 'Severely Wasted';
        if (bmi < 18.5) return 'Wasted';
        if (bmi < 25) return 'Normal';
        if (bmi < 30) return 'Overweight';
        return 'Obese';
      };

      const tally = (fn) => {
        const m = {};
        this.rmRecords.forEach(r => {
          let k = fn(r);
          if (k == null || k === '') k = 'Unknown';
          m[k] = (m[k] || 0) + 1;
        });
        return m;
      };

      let dist = {}, title = '';
      if (cat === 'nutrition') {
        dist = tally(getBmiCat);
        title = 'BMI Category';
      } else if (cat === 'lhas') {
        dist = tally(r => r.screening_type || 'Unknown');
        title = 'Screening Type';
      } else if (cat === 'deworming') {
        dist = { 'Dewormed SBFP': 0, 'Dewormed Other': 0, 'WIFA': 0 };
        this.rmRecords.forEach(r => { if (Number(r.dewormed_sbfp)) dist['Dewormed SBFP']++; if (Number(r.dewormed_other)) dist['Dewormed Other']++; if (Number(r.wifa)) dist['WIFA']++; });
        title = 'Deworming / WIFA';
      } else if (cat === 'immunization') {
        dist = tally(r => r.vaccine || 'Unknown');
        title = 'Vaccine';
      } else if (cat === 'arh') {
        dist = tally(r => r.pregnancy_status || 'Unknown');
        title = 'Pregnancy Status';
      } else if (cat === 'tobacco') {
        dist = tally(r => r.violation_type || 'Unknown');
        title = 'Violation Type';
      }

      const labels = Object.keys(dist), counts = labels.map(l => dist[l]);
      const palette = ['#0f766e','#14b8a6','#0ea5e9','#f59e0b','#dc2626','#7c3aed','#16a34a','#f97316'];
      this.categoryChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels,
          datasets: [{
            label: title,
            data: counts,
            backgroundColor: labels.map((_, i) => palette[i % palette.length]),
            borderRadius: 8
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            title: { display: true, text: title + ' distribution' }
          },
          scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
      });
    },

    // ---- Edit modal ----
    openEditCategoryModal(rec) {
      this.editCategoryModal.record = rec;
      const form = {};
      for (const key of Object.keys(this.rmEditableFields)) {
        form[key] = rec[key] !== undefined ? rec[key] : '';
      }
      this.editCategoryModal.form = form;
      this.editCategoryModal.error = '';
      this.editCategoryModal.saving = false;
      this.editCategoryModal.show = true;
    },

    // ===== UPDATED: Auto-compute BMI when editing =====
    // Inside methods: replace the saveCategoryEdit function

async saveCategoryEdit() {
  this.editCategoryModal.saving = true;
  this.editCategoryModal.error = '';

  // Prepare the row data
  const row = { [this.rmPk]: this.editCategoryModal.record[this.rmPk] };
  
  // Check if this is the nutrition category
  if (this.rmCategory === 'nutrition') {
    const weight = parseFloat(this.editCategoryModal.form.weight_kg);
    const height = parseFloat(this.editCategoryModal.form.height_m);
    
    // If both weight and height are valid, compute everything
    if (weight > 0 && height > 0) {
      const bmi = weight / (height * height);
      const roundedBmi = Math.round(bmi * 100) / 100;
      const heightSquared = Math.round(height * height * 10000) / 10000;
      
      // Determine BMI category
      let category = '';
      if (bmi < 16) category = 'Severely Wasted';
      else if (bmi < 18.5) category = 'Wasted';
      else if (bmi < 25) category = 'Normal';
      else if (bmi < 30) category = 'Overweight';
      else category = 'Obese';
      
      // Add computed values to the row (these will be sent to the server)
      row.bmi = roundedBmi;
      row.bmi_category = category;
      row.height_squared = heightSquared;
      
      // Also update the form data so the user sees the updated values
      this.editCategoryModal.form.bmi = roundedBmi;
      this.editCategoryModal.form.bmi_category = category;
      this.editCategoryModal.form.height_squared = heightSquared;
    }
  }

  // Copy all editable fields from form to row (including weight, height, etc.)
  for (const key of Object.keys(this.rmEditableFields)) {
    // Don't overwrite bmi/bmi_category/height_squared if we already set them
    if (!(this.rmCategory === 'nutrition' && ['bmi', 'bmi_category', 'height_squared'].includes(key) && row[key] !== undefined)) {
      row[key] = this.editCategoryModal.form[key];
    }
  }

  const payload = { category: this.rmCategory, rows: [row] };

  try {
    const res = await fetch('api/update_category_records.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) {
      // Update the local record
      const rec = this.rmRecords.find(r => r[this.rmPk] === row[this.rmPk]);
      if (rec) {
        for (const key of Object.keys(this.rmEditableFields)) {
          if (row[key] !== undefined) rec[key] = row[key];
        }
        // Also update computed fields if they were included
        if (row.bmi !== undefined) rec.bmi = row.bmi;
        if (row.bmi_category !== undefined) rec.bmi_category = row.bmi_category;
        if (row.height_squared !== undefined) rec.height_squared = row.height_squared;
      }
      this.showMessage('success', 'Record updated.');
      this.editCategoryModal.show = false;
      this.$nextTick(() => this.renderCategoryChart());
    } else {
      this.editCategoryModal.error = data.message || 'Update failed.';
    }
  } catch (e) {
    this.editCategoryModal.error = 'Network error: ' + e.message;
  }
  this.editCategoryModal.saving = false;
},

    // ---- Delete modal ----
    openDeleteCategoryModal(rec) {
      this.deleteCategoryModal.record = rec;
      this.deleteCategoryModal.password = '';
      this.deleteCategoryModal.error = '';
      this.deleteCategoryModal.deleting = false;
      this.deleteCategoryModal.show = true;
    },
    async confirmDeleteCategory() {
      if (!this.deleteCategoryModal.record) return;
      if (!this.deleteCategoryModal.password.trim()) {
        this.deleteCategoryModal.error = 'Please enter your password.';
        return;
      }
      this.deleteCategoryModal.deleting = true;
      this.deleteCategoryModal.error = '';
      try {
        const res = await fetch('api/delete_category_record.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            table: this.rmCategory,
            pk: this.rmPk,
            id: this.deleteCategoryModal.record[this.rmPk],
            password: this.deleteCategoryModal.password
          })
        });
        const data = await res.json();
        if (data.success) {
          this.rmRecords = this.rmRecords.filter(r => r[this.rmPk] !== this.deleteCategoryModal.record[this.rmPk]);
          this.showMessage('success', 'Record deleted.');
          this.deleteCategoryModal.show = false;
          this.$nextTick(() => this.renderCategoryChart());
        } else {
          this.deleteCategoryModal.error = data.message || 'Delete failed.';
        }
      } catch (e) {
        this.deleteCategoryModal.error = 'Network error: ' + e.message;
      }
      this.deleteCategoryModal.deleting = false;
    },

    // ---- Add Record ----
    openAddCategoryModal() {
      const form = {};
      for (const key of Object.keys(this.rmEditableFields)) {
        const meta = this.rmEditableFields[key];
        if (meta.type === 'bool') form[key] = 0;
        else if (meta.type === 'int' || meta.type === 'float') form[key] = 0;
        else form[key] = '';
      }
      this.addCategoryModal.form = form;
      this.addCategoryModal.error = '';
      this.addCategoryModal.saving = false;
      this.addCategoryModal.show = true;
    },
    async saveCategoryAdd() {
      this.addCategoryModal.saving = true;
      this.addCategoryModal.error = '';
      const payload = {
        category: this.rmCategory,
        record: this.addCategoryModal.form
      };
      try {
        const res = await fetch('api/add_category_record.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const text = await res.text();
        let data;
        try {
          data = JSON.parse(text);
        } catch (parseError) {
          console.error('Raw response:', text);
          this.addCategoryModal.error = 'Server returned non‑JSON: ' + text.substring(0, 200);
          this.addCategoryModal.saving = false;
          return;
        }
        if (data.success) {
          this.showMessage('success', 'Record added.');
          this.addCategoryModal.show = false;
          this.loadCategoryRecords();
        } else {
          this.addCategoryModal.error = data.message || 'Add failed.';
        }
      } catch (e) {
        this.addCategoryModal.error = 'Network error: ' + e.message;
      }
      this.addCategoryModal.saving = false;
    },

    // ---- Category Bulk & Save All ----
    rowPayload(rec) {
      const p = {};
      p[this.rmPk] = rec[this.rmPk];
      for (const [k, meta] of Object.entries(this.rmFields)) {
        if (meta.edit) p[k] = rec[k];
      }
      return p;
    },
    async saveAllRows() {
      await this.saveRowsPayload(this.rmRecords.map(r => this.rowPayload(r)));
    },
    async saveRowsPayload(rows) {
      this.rmSaving = true;
      try {
        const res = await fetch('api/update_category_records.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ category: this.rmCategory, rows })
        });
        const data = await res.json();
        this.showMessage(data.success ? 'success' : 'error', data.message);
        if (data.success) this.$nextTick(() => this.renderCategoryChart());
      } catch (e) { this.showMessage('error', 'Network error: ' + e.message); }
      this.rmSaving = false;
    },
    async applyBulkEdit() {
      if (!this.rmBulkField || this.rmSelected.length === 0) return;
      this.rmSaving = true;
      try {
        const res = await fetch('api/update_category_records.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            category: this.rmCategory,
            bulk: {
              field: this.rmBulkField,
              value: this.rmBulkValue,
              ids: this.rmSelected
            }
          })
        });
        const data = await res.json();
        if (data.success) {
          const val = this.rmBulkValue, type = this.rmFieldType(this.rmBulkField);
          this.rmRecords.forEach(r => {
            if (this.rmSelected.includes(r[this.rmPk])) {
              r[this.rmBulkField] = (type === 'int' || type === 'bool') ? Number(val) : (type === 'float' ? parseFloat(val) : val);
            }
          });
          this.showMessage('success', data.message);
          this.rmSelected = [];
          this.rmBulkField = '';
          this.rmBulkValue = '';
          this.$nextTick(() => this.renderCategoryChart());
        } else {
          this.showMessage('error', data.message);
        }
      } catch (e) { this.showMessage('error', 'Network error: ' + e.message); }
      this.rmSaving = false;
    }
  }
}).mount('#app');
</script>
</body>
</html>