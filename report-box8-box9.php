<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | Food Handling & Feeding Program</title>
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
    .container-custom { max-width: 900px; margin: 0 auto; padding: 24px 20px; }
    .header-box { background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary)); color: white; padding: 24px 28px; border-radius: 24px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; box-shadow: 0 16px 38px rgba(15, 118, 110, 0.18); }
    .header-box h1 { font-size: 1.6rem; font-weight: 800; margin: 0; }
    .btn-back { background: white; color: var(--clinic-primary); border: none; border-radius: 14px; padding: 10px 20px; font-weight: 700; text-decoration: none; }
    .btn-save { background: white; color: var(--clinic-primary); border: none; border-radius: 14px; padding: 12px 24px; font-weight: 700; cursor: pointer; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
    .btn-save:disabled { opacity: 0.6; cursor: not-allowed; }
    .section-card { background: white; border: 1px solid var(--clinic-border); border-radius: var(--clinic-radius); padding: 24px; margin-bottom: 20px; box-shadow: var(--clinic-shadow); }
    .section-title { font-size: 1.3rem; font-weight: 800; color: var(--clinic-primary); margin-bottom: 16px; }
    .radio-group { display: flex; flex-wrap: wrap; gap: 16px; margin: 8px 0 16px; }
    .form-check { display: flex; align-items: center; gap: 6px; }
    .form-check-input:checked { background-color: var(--clinic-primary); border-color: var(--clinic-primary); }
    .checkbox-group { display: flex; flex-wrap: wrap; gap: 16px; margin: 8px 0 16px; }
    .conditional-section { background: #fbfefe; border: 1px solid var(--clinic-border); border-radius: 14px; padding: 16px; margin-top: 12px; }
    .form-control { border-radius: 12px; border: 1px solid var(--clinic-border); padding: 10px 14px; }
    .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .alert { border-radius: 14px; border: none; margin-bottom: 16px; }
    @media (max-width: 768px) { .container-custom { padding: 12px; } .form-grid-2 { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
<div id="app" class="container-custom">
  <div class="header-box no-print">
    <div>
      <h1>🍽️ BOX 8 & 9 – Food Handling & Feeding Program</h1>
      <p style="margin:4px 0 0; opacity:0.9;">Canteen, kitchen, feeding fund sources, and agriculture resources</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn-save" @click="saveData" :disabled="saving">{{ saving ? 'Saving...' : '💾 Save' }}</button>
      <button class="btn-save" @click="printForm" style="background:#f0fdfa; color:#0f766e;">🖨️ Print</button>
      <a href="reports.php" class="btn-back">← Back</a>
    </div>
  </div>
  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">{{ message }}</div>

  <!-- BOX 8 -->
  <div class="section-card">
    <h2 class="section-title">BOX 8 – Food Handling</h2>
    <div class="mb-3">
      <label class="fw-bold">1. Does the school have a canteen?</label>
      <div class="radio-group">
        <label class="form-check"><input type="radio" class="form-check-input" value="Yes" v-model="formData.hasCanteen"> Yes</label>
        <label class="form-check"><input type="radio" class="form-check-input" value="No" v-model="formData.hasCanteen"> No</label>
      </div>
    </div>
    <div v-if="formData.hasCanteen === 'Yes'" class="conditional-section">
      <label class="fw-bold">Managed by:</label>
      <div class="radio-group">
        <label class="form-check"><input type="radio" class="form-check-input" value="School" v-model="formData.canteenManager"> School</label>
        <label class="form-check"><input type="radio" class="form-check-input" value="Teacher-Coop" v-model="formData.canteenManager"> Teacher-Coop</label>
        <label class="form-check"><input type="radio" class="form-check-input" value="Others" v-model="formData.canteenManager"> Others</label>
      </div>
      <div v-if="formData.canteenManager === 'Others'" class="mt-2">
        <input type="text" class="form-control" placeholder="Specify other manager" v-model="formData.canteenManagerOther">
      </div>
      <div class="form-grid-2 mt-3">
        <div>
          <label class="fw-bold">Sanitary Permit</label>
          <div class="radio-group">
            <label class="form-check"><input type="radio" class="form-check-input" value="Yes" v-model="formData.sanitaryPermit"> Yes</label>
            <label class="form-check"><input type="radio" class="form-check-input" value="No" v-model="formData.sanitaryPermit"> No</label>
          </div>
        </div>
        <div>
          <label class="fw-bold">Food handlers have health certificates</label>
          <div class="radio-group">
            <label class="form-check"><input type="radio" class="form-check-input" value="Yes" v-model="formData.healthCertificates"> Yes</label>
            <label class="form-check"><input type="radio" class="form-check-input" value="No" v-model="formData.healthCertificates"> No</label>
          </div>
        </div>
      </div>
    </div>
    <div class="mb-3 mt-3">
      <label class="fw-bold">2. Does the school have a kitchen?</label>
      <div class="radio-group">
        <label class="form-check"><input type="radio" class="form-check-input" value="Yes" v-model="formData.hasKitchen"> Yes</label>
        <label class="form-check"><input type="radio" class="form-check-input" value="No" v-model="formData.hasKitchen"> No</label>
      </div>
    </div>
  </div>

  <!-- BOX 9 -->
  <div class="section-card">
    <h2 class="section-title">BOX 9 – Feeding Program</h2>
    <div class="mb-3">
      <label class="fw-bold">1. Sources of funding for feeding program</label>
      <div class="checkbox-group">
        <label class="form-check" v-for="fund in feedingFundSources" :key="fund">
          <input type="checkbox" class="form-check-input" :value="fund" v-model="formData.feedingFundSources"> {{ fund }}
        </label>
      </div>
    </div>
    <div class="mb-3">
      <label class="fw-bold">2. Available agriculture/fishery resources</label>
      <div class="checkbox-group">
        <label class="form-check" v-for="res in agriResources" :key="res">
          <input type="checkbox" class="form-check-input" :value="res" v-model="formData.agriResources"> {{ res }}
        </label>
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
      feedingFundSources: ['School MOOE','School Canteen Fund','LGU Fund','PTA Fund','Barangay Fund','Private Individual/Sector Fund','SBFP'],
      agriResources: ['Gulayan sa Paaralan','Fish Pond','Agricultural Crops','Livestock'],
      formData: {
        hasCanteen: '', canteenManager: '', canteenManagerOther: '',
        sanitaryPermit: '', healthCertificates: '', hasKitchen: '',
        feedingFundSources: [], agriResources: []
      },
      saving: false, message: '', messageType: 'success'
    };
  },
  methods: {
    async saveData() {
      this.saving = true;
      try {
        const res = await fetch('api/save_box8_box9.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(this.formData) });
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