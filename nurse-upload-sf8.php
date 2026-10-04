<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | Nurse SF8 Upload</title>
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
    .modal-content { border: 1px solid var(--clinic-border); border-radius: 20px; overflow: hidden; }
    .conflict-arrow { color: var(--clinic-primary); font-size: 1.2rem; }
    .conflict-table { max-height: none; }
    .modal-header-clinic { background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary)); color: white; }
    .identity-card { background: #f7fcfc; border: 1px solid var(--clinic-border); border-radius: 14px; padding: 15px; }
    .identity-pair { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; font-weight: 800; }
    .identity-pair span { background: white; border: 1px solid var(--clinic-border); border-radius: 10px; padding: 9px 12px; }
    .identity-pair .arrow { color: var(--clinic-primary); }
  </style>
</head>
<body>
<div id="app" class="wrapper">

  <div class="header-box d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
      <h1 class="fw-bold mb-2">📤 SF8 Upload</h1>
      <p class="mb-0">Upload your SF8 file securely, review the decrypted preview, then approve the records</p>
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
        <span class="ms-2">Encrypting, uploading, and preparing preview...</span>
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
    <div v-if="parsedData.report_code && parsedData.report_code !== 'students_information'" class="alert alert-info small">
      If this learner has no Student Information yet, approval creates a provisional learner profile and saves only this file's health records.
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
          <tr v-for="(rec, idx) in parsedData.records" :key="idx">
            <td>{{ idx + 1 }}</td>
            <td v-for="col in previewColumns" :key="col.key">{{ rec[col.key] ?? '—' }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="d-flex gap-2 mt-3 justify-content-end">
      <button class="btn btn-outline-danger" @click="discardUpload">Close Preview</button>
      <button class="btn btn-green" @click="approveUpload()" :disabled="approving">
        {{ approving ? 'Approving...' : '✅ Approve & Save' }}
      </button>
    </div>
  </div>

  <!-- No data -->
  <div v-else-if="!uploading && !parsedData" class="card text-center text-muted py-5">
    <p>Select an SF8 Excel file to begin.</p>
  </div>

  <div class="modal fade" id="conflictModal" tabindex="-1" data-bs-backdrop="static" aria-labelledby="conflictModalTitle">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header modal-header-clinic">
          <h5 class="modal-title fw-bold" id="conflictModalTitle">Existing learner data found</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>There is existing data for the same LRN and school year. Review the changes before approving this SF8 file.</p>
          <div v-if="hasExistingDuplicates" class="alert alert-danger">
            Multiple existing rows were found for a learner. Resolve those records in Records Manager before approving this file.
          </div>
          <div v-for="(conflict, index) in conflicts" :key="index" class="border rounded-3 p-3 mb-3">
            <div class="fw-bold mb-2">{{ conflict.learner_name || 'Learner' }} · LRN {{ conflict.lrn }} · {{ conflict.school_year }}</div>
            <div v-if="conflict.existing_count > 1" class="alert alert-warning py-2">
              {{ conflict.existing_count }} existing rows were found for this record.
              <div v-for="row in conflict.existing_rows" :key="row.id" class="small mt-2">
                Record #{{ row.id }}: {{ formatConflictRow(row) }}
              </div>
            </div>
            <div v-if="conflict.changes && conflict.changes.length" class="table-responsive conflict-table">
              <table class="table table-sm table-bordered align-middle mb-0">
                <thead><tr><th>Field</th><th>Existing data</th><th></th><th>New SF8 data</th></tr></thead>
                <tbody>
                  <tr v-for="change in conflict.changes" :key="change.field">
                    <td>{{ change.field.replace(/_/g, ' ') }}</td>
                    <td>{{ formatValue(change.existing) }}</td>
                    <td class="text-center fw-bold conflict-arrow">→</td>
                    <td>{{ formatValue(change.incoming) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <p v-else class="small text-muted mb-0">This learner already has a record with the same populated values. Approving this file will not change those values.</p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep existing data</button>
          <button type="button" class="btn btn-warning fw-bold" @click="confirmOverride" :disabled="approving || hasExistingDuplicates || !conflictFingerprint">Override and Approve</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="identityModal" tabindex="-1" data-bs-backdrop="static" aria-labelledby="identityModalTitle">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header modal-header-clinic">
          <h5 class="modal-title fw-bold" id="identityModalTitle">Check learner LRN</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>The same learner name and school year appear with different LRNs. No records were saved. Compare the workbook with the existing record and correct the incorrect LRN before approval. ClinicDesk will not merge learners by name.</p>
          <div v-for="(issue, index) in identityConflicts" :key="index" class="identity-card mb-3">
            <div class="fw-bold mb-2">{{ issue.learner_name }} · {{ issue.school_year }}</div>
            <div class="identity-pair"><span>Existing LRN: {{ issue.existing_lrn }}</span><span class="arrow">→</span><span>File LRN: {{ issue.incoming_lrn }}</span></div>
            <div class="small text-muted mt-2">Found in {{ issue.source }}</div>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-green" data-bs-dismiss="modal">Review workbook</button></div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalTitle">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title fw-bold" id="errorModalTitle">SF8 upload error</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-0">{{ errorMessage }}</p>
          <pre v-if="errorDetails" class="small bg-light rounded p-2 mt-3 mb-0 text-wrap">{{ errorDetails }}</pre>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
      </div>
    </div>
  </div>

</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/sf8-security.js"></script>
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
      previewColumns: [],
      conflicts: [],
      conflictFingerprint: '',
      hasExistingDuplicates: false,
      conflictModal: null,
      identityModal: null,
      identityConflicts: [],
      errorMessage: '',
      errorDetails: '',
      errorModal: null
    };
  },
  mounted() {
    this.conflictModal = new bootstrap.Modal(document.getElementById('conflictModal'));
    this.identityModal = new bootstrap.Modal(document.getElementById('identityModal'));
    this.errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
    const role = localStorage.getItem('active_role');
    if (role !== 'Clinic Nurse') {
      window.location.href = 'login.php';
    }
  },
  methods: {
    formatValue(value) {
      return value === null || value === undefined || (typeof value === 'string' && !value.trim()) ? '—' : value;
    },
    formatConflictRow(row) {
      return Object.entries(row.values || {})
        .filter(([, value]) => value !== null && value !== '')
        .map(([key, value]) => key.replace(/_/g, ' ') + ': ' + value)
        .join(' · ');
    },
    showErrorModal(message, details = '') {
      this.errorMessage = message;
      this.errorDetails = details ? (typeof details === 'string' ? details : JSON.stringify(details, null, 2)) : '';
      this.errorModal.show();
    },
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
      this.previewColumns = [];
      this.conflicts = [];
      this.identityConflicts = [];
      this.conflictFingerprint = '';
      this.hasExistingDuplicates = false;
      if (!this.file.name.toLowerCase().endsWith('.xlsx') || this.file.size > 10 * 1024 * 1024) {
        this.uploading = false;
        this.showErrorModal('Only .xlsx files up to 10 MB are allowed.');
        return;
      }

      const formData = new FormData();
      formData.append('file', this.file);

      try {
        const res = await clinicSf8Fetch('api/upload_sf8_local.php', {
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
          this.showMessage('success', 'File encrypted and uploaded. Review the decrypted records below.');
        } else {
          this.showErrorModal(data.message || 'Parsing failed.', data.details);
        }
      } catch (e) {
        this.showErrorModal('Upload failed: ' + e.message);
      }
      this.uploading = false;
      // Clear file input
      if (this.$refs.fileInput) this.$refs.fileInput.value = '';
    },
    async approveUpload(overrideExisting = false) {
      if (!this.parsedData) return;
      this.approving = true;
      this.message = '';
      try {
        const res = await clinicSf8Fetch('api/approve_local_upload.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            upload_id: this.parsedData.upload_id,
            override_existing: overrideExisting,
            conflict_fingerprint: overrideExisting ? this.conflictFingerprint : ''
          })
        });
        const data = await res.json();
        if (data.success) {
          this.showMessage('success', data.message || 'Records saved successfully.');
          const approvedUploadId = this.parsedData.upload_id;
          setTimeout(() => {
            if (this.parsedData?.upload_id === approvedUploadId) {
              this.parsedData = null;
              this.previewColumns = [];
            }
          }, 3000);
        } else if (Array.isArray(data.identity_conflicts) && data.identity_conflicts.length) {
          this.identityConflicts = data.identity_conflicts;
          this.identityModal.show();
        } else if (data.requires_override && Array.isArray(data.conflicts)) {
          this.conflicts = data.conflicts;
          this.hasExistingDuplicates = !!data.has_existing_duplicates;
          this.conflictFingerprint = data.conflict_fingerprint || '';
          this.conflictModal.show();
        } else {
          this.showErrorModal(data.message || 'Approval failed.', data.details);
        }
      } catch (e) {
        this.showErrorModal('Approval failed: ' + e.message);
      }
      this.approving = false;
    },
    async confirmOverride() {
      if (this.approving || this.hasExistingDuplicates || !this.conflictFingerprint) return;
      const modalElement = document.getElementById('conflictModal');
      const hidden = new Promise(resolve => modalElement.addEventListener('hidden.bs.modal', resolve, { once: true }));
      this.conflictModal.hide();
      await hidden;
      await this.approveUpload(true);
    },
    discardUpload() {
      // The encrypted upload remains Pending and can be reviewed from SF8 Uploads.
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
<script src="assets/table-pagination.js" defer></script>
</body>
</html>
