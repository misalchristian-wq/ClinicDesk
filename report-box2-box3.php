<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | School Clinic & Water Supply</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    :root {
      --clinic-primary: #0f766e; --clinic-secondary: #14b8a6; --clinic-border: #d9eef0;
      --clinic-text: #16323f; --clinic-muted: #6b7d87; --clinic-shadow: 0 16px 38px rgba(15, 118, 110, 0.08); --clinic-radius: 22px;
    }
    * { box-sizing: border-box; }
    body { min-height: 100vh; margin: 0; background: #f5fafb; font-family: 'Plus Jakarta Sans', Arial, sans-serif; color: var(--clinic-text); }
    .container-custom { max-width: 1000px; margin: 0 auto; padding: 24px 20px; }
    .header-box { background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary)); color: white; padding: 24px 28px; border-radius: 24px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; box-shadow: 0 16px 38px rgba(15, 118, 110, 0.18); }
    .header-box h1 { font-size: 1.6rem; font-weight: 800; margin: 0; }
    .btn-back { background: white; color: var(--clinic-primary); border: none; border-radius: 14px; padding: 10px 20px; font-weight: 700; text-decoration: none; }
    .btn-save { background: white; color: var(--clinic-primary); border: none; border-radius: 14px; padding: 12px 24px; font-weight: 700; cursor: pointer; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
    .btn-save:disabled { opacity: 0.6; cursor: not-allowed; }
    .section-card { background: white; border: 1px solid var(--clinic-border); border-radius: var(--clinic-radius); padding: 24px; margin-bottom: 20px; box-shadow: var(--clinic-shadow); }
    .section-title { font-size: 1.3rem; font-weight: 800; color: var(--clinic-primary); margin-bottom: 16px; }
    .subsection-title { font-size: 1.05rem; font-weight: 700; color: var(--clinic-text); margin: 16px 0 10px; padding-bottom: 8px; border-bottom: 2px solid #ecfeff; }
    .radio-group { display: flex; flex-wrap: wrap; gap: 16px; margin: 8px 0 16px; }
    .form-check { display: flex; align-items: center; gap: 6px; }
    .form-check-input:checked { background-color: var(--clinic-primary); border-color: var(--clinic-primary); }
    .conditional-section { background: #fbfefe; border: 1px solid var(--clinic-border); border-radius: 14px; padding: 16px; margin-top: 12px; }
    .form-control { border-radius: 12px; border: 1px solid var(--clinic-border); padding: 10px 14px; max-width: 250px; }
    .form-control:focus { border-color: var(--clinic-secondary); box-shadow: 0 0 0 0.2rem rgba(20,184,166,0.12); }
    .checkbox-group { display: flex; flex-wrap: wrap; gap: 16px; margin: 8px 0 16px; }
    .table-responsive { border-radius: 16px; border: 1px solid var(--clinic-border); background: white; margin-bottom: 12px; }
    .table { margin-bottom: 0; font-size: 0.85rem; }
    .table th { background: #f1fbfb; color: #24404d; font-weight: 800; font-size: 0.8rem; text-align: center; }
    .table td { vertical-align: middle; text-align: center; padding: 8px; }
    .alert { border-radius: 14px; border: none; margin-bottom: 16px; }
    @media (max-width: 768px) { .container-custom { padding: 12px; } }
  </style>
</head>
<body>
<div id="app" class="container-custom">
  <div class="header-box no-print">
    <div>
      <h1>🏥 BOX 2 & 3 – School Clinic & Water Supply</h1>
      <p style="margin:4px 0 0; opacity:0.9;">Clinic infrastructure, equipment, and water availability</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn-save" @click="saveData" :disabled="saving">{{ saving ? 'Saving...' : '💾 Save' }}</button>
      <button class="btn-save" @click="printForm" style="background:#f0fdfa; color:#0f766e;">🖨️ Print</button>
      <a href="reports.php" class="btn-back">← Back</a>
    </div>
  </div>
  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">{{ message }}</div>

  <!-- BOX 2 -->
  <div class="section-card">
    <h2 class="section-title">BOX 2 – School Clinic</h2>
    <div class="mb-3">
      <label class="fw-bold">1. Does the school have a designated school clinic?</label>
      <div class="radio-group">
        <label class="form-check"><input type="radio" class="form-check-input" value="Yes" v-model="formData.hasSchoolClinic"> Yes</label>
        <label class="form-check"><input type="radio" class="form-check-input" value="No" v-model="formData.hasSchoolClinic"> No</label>
      </div>
    </div>
    <div class="mb-3">
      <label class="fw-bold">2. Was the school visited by SDO Health Personnel?</label>
      <div class="radio-group">
        <label class="form-check"><input type="radio" class="form-check-input" value="Yes" v-model="formData.visitedBySDO"> Yes</label>
        <label class="form-check"><input type="radio" class="form-check-input" value="No" v-model="formData.visitedBySDO"> No</label>
      </div>
      <div v-if="formData.visitedBySDO === 'Yes'" class="conditional-section">
        <label class="fw-bold">Number of visits:</label>
        <input type="number" min="0" class="form-control" v-model.number="formData.sdoVisits">
      </div>
    </div>
    <h4 class="subsection-title">3. Clinic Infrastructure / Equipment / Materials</h4>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead><tr><th>Item</th><th>Status</th></tr></thead>
        <tbody>
          <tr v-for="item in clinicItems" :key="item">
            <td class="text-start fw-bold">{{ item }}</td>
            <td>
              <div class="radio-group justify-content-center">
                <label class="form-check"><input type="radio" :name="'clinic-'+item" value="Functional" v-model="formData.clinicEquipment[item]"> Functional</label>
                <label class="form-check"><input type="radio" :name="'clinic-'+item" value="Non-functional" v-model="formData.clinicEquipment[item]"> Non-functional</label>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- BOX 3 -->
  <div class="section-card">
    <h2 class="section-title">BOX 3 – Availability of Water Supply</h2>
    <div class="mb-3">
      <label class="fw-bold">1. Water supply sources</label>
      <div class="checkbox-group">
        <label class="form-check" v-for="src in waterSources" :key="src">
          <input type="checkbox" class="form-check-input" :value="src" v-model="formData.waterSources"> {{ src }}
        </label>
      </div>
    </div>
    <div class="mb-3">
      <label class="fw-bold">2. Is the water source used for drinking?</label>
      <div class="radio-group">
        <label class="form-check"><input type="radio" class="form-check-input" value="Yes" v-model="formData.waterForDrinking"> Yes</label>
        <label class="form-check"><input type="radio" class="form-check-input" value="No" v-model="formData.waterForDrinking"> No</label>
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
      clinicItems: ['Bathroom','Hospital/Clinic Bed','Dental Chair','First Aid Kit','Height Tool','Weighing Scale','Autoclave/Sterilizer','BP Apparatus','Nebulizer'],
      waterSources: ['Piped water','Water Well','Rainwater Catchment','Natural Source'],
      formData: {
        hasSchoolClinic: '', visitedBySDO: '', sdoVisits: 0,
        clinicEquipment: {},
        waterSources: [], waterForDrinking: ''
      },
      saving: false, message: '', messageType: 'success'
    };
  },
  mounted() {
    this.clinicItems.forEach(item => {
      if (!this.formData.clinicEquipment[item]) this.formData.clinicEquipment[item] = '';
    });
  },
  methods: {
    async saveData() {
      this.saving = true;
      try {
        const res = await fetch('api/save_box2_box3.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(this.formData) });
        const result = await res.json();
        this.messageType = result.success ? 'success' : 'danger';
        this.message = result.message || 'Saved.';
      } catch(e) { this.messageType='danger'; this.message='Error: '+e.message; }
      this.saving = false;
      setTimeout(()=>{ this.message=''; }, 5000);
    },
    printForm() { window.print(); }
  }
}).mount("#app");
</script>
</body>
</html>