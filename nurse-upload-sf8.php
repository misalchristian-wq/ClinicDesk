<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | Nurse Offline SF8 Upload</title>
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
    .wrapper { max-width: 1300px; margin: 28px auto; padding: 20px; }
    .header-box {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white; padding: 34px; border-radius: 28px; margin-bottom: 24px;
      box-shadow: 0 16px 38px rgba(15,118,110,0.22);
    }
    .btn-back { background: white; color: var(--clinic-primary); border: none; border-radius: 15px; padding: 11px 18px; font-weight: 800; text-decoration: none; }
    .btn-back:hover { background: #ecfeff; color: var(--clinic-primary); }
    .card { background: white; border: 1px solid var(--clinic-border); border-radius: var(--clinic-radius); box-shadow: var(--clinic-shadow); padding: 24px; }
    .card h4 { color: var(--clinic-primary); font-weight: 900; }
    .upload-zone { border: 2px dashed rgba(20,184,166,0.45); background: #f0fdfa; border-radius: 18px; padding: 30px; text-align: center; margin-bottom: 20px; }
    .upload-icon { font-size: 48px; }
    .btn-green { background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary)); color: white; font-weight: 900; border: none; border-radius: 14px; padding: 12px 16px; }
    .btn-green:hover { color: white; transform: translateY(-1px); }
    .btn-green:disabled { opacity: 0.65; cursor: not-allowed; }
    .btn-outline-danger { border: 1px solid #dc2626; color: #dc2626; background: white; font-weight: 900; border-radius: 14px; padding: 12px 16px; }
    .btn-outline-danger:hover { background: #dc2626; color: white; }
    .table-responsive { max-height: 500px; overflow-y: auto; border: 1px solid var(--clinic-border); border-radius: 14px; }
    .table th { background: #e8f5f6; color: #1e3b44; font-weight: 800; white-space: nowrap; }
    .alert { border-radius: 14px; }
    .spinner-border-sm { width: 1.2rem; height: 1.2rem; border-width: 0.15em; }
    .badge { border-radius: 30px; padding: 6px 14px; font-weight: 800; }
    .summary-strip { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 20px; }
    .summary-box { background: #f8fcfd; border: 1px solid var(--clinic-border); border-radius: 16px; padding: 16px; text-align: center; }
    .summary-box .number { font-size: 28px; font-weight: 900; color: var(--clinic-primary); }
    .summary-box .label { font-size: 13px; color: var(--clinic-muted); font-weight: 700; }
  </style>
</head>
<body>
<div id="app" class="wrapper">

  <div class="header-box d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
      <h1 class="fw-bold mb-2">📤 Offline SF8 Upload</h1>
      <p class="mb-0">Upload an SF8 Excel file locally, preview data, and approve directly</p>
    </div>
    <a href="nurse-dashboard.php" class="btn btn-back">← Back to Dashboard</a>
  </div>

  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">{{ message }}</div>

  <!-- Upload Zone -->
  <div class="card mb-4">
    <div class="upload-zone" @dragover.prevent @drop.prevent="handleDrop">
      <div class="upload-icon">📄</div>
      <h5 class="fw-bold">Drop your SF8 Excel file here, or click to browse</h5>
      <p class="text-muted">Supports `.xlsx` files only (max 10MB)</p>
      <input type="file" class="form-control" accept=".xlsx" @change="handleFile" ref="fileInput" style="max-width:300px;margin:0 auto;">
      <div v-if="uploading" class="mt-3">
        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
        <span class="ms-2">Uploading and parsing...</span>
      </div>
    </div>
  </div>

  <!-- Preview Area (shown after upload) -->
  <div v-if="parsedData" class="card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
      <h4 class="fw-bold mb-0">Preview – {{ parsedData.records.length }} records</h4>
      <div>
        <span class="badge bg-info me-2">{{ parsedData.report_code || 'SF8' }}</span>
        <span class="badge bg-secondary">{{ parsedData.header?.school_year || 'No SY' }}</span>
      </div>
    </div>

    <div class="summary-strip">
      <div class="summary-box"><div class="number">{{ parsedData.records.length }}</div><div class="label">Total Records</div></div>
      <div class="summary-box"><div class="number">{{ parsedData.header?.grade_level || '—' }}</div><div class="label">Grade Level</div></div>
      <div class="summary-box"><div class="number">{{ parsedData.header?.section || '—' }}</div><div class="label">Section</div></div>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-sm align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th v-for="col in previewColumns" :key="col.key">{{ col.label }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(rec, idx) in parsedData.records.slice(0, 50)" :key="idx">
            <td>{{ idx + 1 }}</td>
            <td v-for="col in previewColumns" :key="col.key">{{ rec[col.key] ?? '—' }}</td>
          </tr>
          <tr v-if="parsedData.records.length > 50">
            <td :colspan="previewColumns.length + 1" class="text-muted text-center">... and {{ parsedData.records.length - 50 }} more</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="d-flex gap-2 mt-3 justify-content-end">
      <button class="btn btn-outline-danger" @click="discardUpload">Discard</button>
      <button class="btn btn-green" @click="approveUpload" :disabled="approving">
        {{ approving ? 'Approving...' : '✅ Approve & Save' }}
      </button>
    </div>
  </div>

  <!-- No data -->
  <div v-else-if="!uploading && !parsedData" class="card text-center text-muted py-5">
    <p>Select an SF8 Excel file to begin.</p>
  </div>

</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script>
const { createApp } = Vue;

createApp({
  data() {
    return {
      file: null,
      uploading: false,
      parsedData: null,
      approving: false,
      message: '',
      messageType: 'success',
      previewColumns: []
    };
  },
  mounted() {
    const role = localStorage.getItem('active_role');
    if (role !== 'Clinic Nurse') {
      window.location.href = 'login.php';
    }
  },
  methods: {
    handleFile(event) {
      this.file = event.target.files[0];
      if (this.file) this.uploadFile();
    },
    handleDrop(event) {
      const files = event.dataTransfer.files;
      if (files.length) {
        this.file = files[0];
        this.uploadFile();
      }
    },
    async uploadFile() {
      if (!this.file) return;
      this.uploading = true;
      this.message = '';
      this.parsedData = null;

      const formData = new FormData();
      formData.append('file', this.file);

      try {
        const res = await fetch('api/upload_sf8_local.php', {
          method: 'POST',
          body: formData
        });
        const data = await res.json();
        if (data.success) {
          this.parsedData = data;
          // Build column list from first record
          if (data.records.length) {
            this.previewColumns = Object.keys(data.records[0]).map(key => ({
              key,
              label: key.replace(/_/g, ' ').toUpperCase()
            }));
          }
          this.showMessage('success', 'File parsed successfully. Review the data below.');
        } else {
          this.showMessage('error', data.message || 'Parsing failed.');
        }
      } catch (e) {
        this.showMessage('error', 'Error: ' + e.message);
      }
      this.uploading = false;
      // Clear file input
      this.$refs.fileInput.value = '';
    },
    async approveUpload() {
      if (!this.parsedData) return;
      this.approving = true;
      this.message = '';
      try {
        const res = await fetch('api/approve_local_upload.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            header: this.parsedData.header,
            records: this.parsedData.records,
            report_code: this.parsedData.report_code,
            uploaded_by: localStorage.getItem('local_full_name') || 'Clinic Nurse'
          })
        });
        const data = await res.json();
        if (data.success) {
          this.showMessage('success', data.message || 'Records saved successfully.');
          // Optionally clear the preview after a delay
          setTimeout(() => {
            this.parsedData = null;
            this.previewColumns = [];
          }, 3000);
        } else {
          this.showMessage('error', data.message || 'Approval failed.');
        }
      } catch (e) {
        this.showMessage('error', 'Error: ' + e.message);
      }
      this.approving = false;
    },
    discardUpload() {
      this.parsedData = null;
      this.previewColumns = [];
      this.message = '';
      this.file = null;
      this.$refs.fileInput.value = '';
    },
    showMessage(type, text) {
      this.messageType = type;
      this.message = text;
      setTimeout(() => { this.message = ''; }, 6000);
    }
  }
}).mount('#app');
</script>
</body>
</html>