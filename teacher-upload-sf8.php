<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | Upload SF8 Excel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    :root {
      --clinic-primary: #0f766e;
      --clinic-secondary: #14b8a6;
      --clinic-accent: #0ea5e9;
      --clinic-bg: #eef8fb;
      --clinic-light: #f0fdfa;
      --clinic-card: rgba(255, 255, 255, 0.96);
      --clinic-border: #d9eef0;
      --clinic-text: #16323f;
      --clinic-muted: #6b7d87;
      --clinic-shadow: 0 12px 32px rgba(15, 118, 110, 0.10);
      --clinic-radius: 22px;
    }

    * { box-sizing: border-box; }

    body {
      min-height: 100vh;
      margin: 0;
      background:
        radial-gradient(circle at top left, rgba(20,184,166,0.16), transparent 25%),
        radial-gradient(circle at top right, rgba(14,165,233,0.12), transparent 25%),
        linear-gradient(135deg, #eef8fb, #f8fcfd);
      font-family: Arial, sans-serif;
      color: var(--clinic-text);
      overflow-x: hidden;
    }

    .wrapper {
      max-width: 1300px;
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
      background: rgba(255, 255, 255, 0.16);
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
      background: rgba(255, 255, 255, 0.10);
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
      background: rgba(255, 255, 255, 0.18);
      border: 2px solid rgba(255, 255, 255, 0.35);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 30px;
      margin-right: 16px;
      box-shadow: 0 0 28px rgba(255, 255, 255, 0.20);
      flex-shrink: 0;
    }

    .header-box h1 {
      font-size: 38px;
      font-weight: 900;
      margin-bottom: 8px;
      text-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    }

    .header-box p {
      font-size: 15px;
      color: rgba(255, 255, 255, 0.92);
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

    .layout-grid {
      display: grid;
      grid-template-columns: 0.8fr 1.4fr;
      gap: 24px;
      align-items: start;
    }

    .card {
      background: var(--clinic-card);
      border: 1px solid var(--clinic-border);
      border-radius: var(--clinic-radius);
      box-shadow: var(--clinic-shadow);
      color: var(--clinic-text);
    }

    .card h4 {
      color: var(--clinic-primary);
      font-weight: 900;
    }

    .upload-card {
      position: sticky;
      top: 20px;
    }

    .step-list {
      display: grid;
      gap: 12px;
      margin-bottom: 20px;
    }

    .step-item {
      display: flex;
      gap: 12px;
      align-items: flex-start;
      background: #f8fcfd;
      border: 1px solid var(--clinic-border);
      border-radius: 16px;
      padding: 14px;
    }

    .step-number {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 900;
      flex-shrink: 0;
      font-size: 13px;
    }

    .step-item strong {
      color: var(--clinic-primary);
    }

    .step-item p {
      margin: 2px 0 0;
      color: var(--clinic-muted);
      font-size: 13px;
      line-height: 1.5;
    }

    .upload-zone {
      border: 2px dashed rgba(20, 184, 166, 0.45);
      background: var(--clinic-light);
      border-radius: 18px;
      padding: 22px;
      text-align: center;
      margin-bottom: 16px;
    }

    .upload-icon {
      width: 58px;
      height: 58px;
      border-radius: 18px;
      background: white;
      border: 1px solid var(--clinic-border);
      color: var(--clinic-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 30px;
      margin: 0 auto 12px;
      box-shadow: 0 10px 20px rgba(15, 118, 110, 0.08);
    }

    .form-label {
      color: #24404d;
      font-weight: 800;
      font-size: 14px;
    }

    .form-control {
      border-radius: 14px;
      border: 1px solid var(--clinic-border);
      padding: 11px 13px;
      font-size: 14px;
      background: white;
      color: var(--clinic-text);
    }

    .form-control:focus {
      border-color: var(--clinic-secondary);
      box-shadow: 0 0 0 0.2rem rgba(20, 184, 166, 0.16);
    }

    .btn-green {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white;
      font-weight: 900;
      border: none;
      border-radius: 14px;
      padding: 12px 16px;
      box-shadow: 0 12px 24px rgba(15, 118, 110, 0.18);
    }

    .btn-green:hover {
      color: white;
      transform: translateY(-1px);
      box-shadow: 0 14px 30px rgba(15, 118, 110, 0.22);
    }

    .btn-green:disabled {
      opacity: 0.65;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
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

    .small-note {
      font-size: 0.9rem;
      color: var(--clinic-muted);
      line-height: 1.5;
    }

    .file-pill {
      background: #f8fcfd;
      border: 1px solid var(--clinic-border);
      border-radius: 14px;
      padding: 12px 14px;
      margin-top: 14px;
      display: flex;
      align-items: center;
      gap: 10px;
      color: var(--clinic-text);
      font-size: 14px;
    }

    .file-pill span:first-child {
      color: var(--clinic-primary);
      font-size: 20px;
    }

    .preview-card {
      min-height: 420px;
    }

    .preview-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
      margin-bottom: 14px;
    }

    .preview-badge {
      background: var(--clinic-light);
      color: var(--clinic-primary);
      border: 1px solid var(--clinic-border);
      border-radius: 999px;
      padding: 7px 12px;
      font-size: 13px;
      font-weight: 800;
    }

    .purpose-badge {
      background: #f0fdfa;
      color: #0f766e;
      border: 1px solid #99f6e4;
      border-radius: 999px;
      padding: 7px 12px;
      font-size: 13px;
      font-weight: 900;
    }

    .empty-preview {
      border: 1px dashed var(--clinic-border);
      background: #f8fcfd;
      border-radius: 18px;
      padding: 48px 24px;
      text-align: center;
      color: var(--clinic-muted);
    }

    .empty-preview .empty-icon {
      font-size: 42px;
      margin-bottom: 12px;
      color: var(--clinic-primary);
    }

    .table-responsive {
      width: 100%;
      overflow-x: auto;
      border-radius: 16px;
      border: 1px solid var(--clinic-border);
      background: white;
      max-height: 620px;
    }

    .table {
      margin-bottom: 0;
      color: var(--clinic-text);
    }

    .table td {
      vertical-align: middle;
      color: #263f4a;
      background: white;
      border-color: #e5f0f2;
      font-size: 13px;
      white-space: nowrap;
    }

    .table tr:first-child td {
      background: #e8f7f5;
      color: #24404d;
      font-weight: 900;
    }

    .table tbody tr:hover td {
      background: #f7fcfd;
    }

    .modal-content {
      border: none;
      border-radius: 22px;
      box-shadow: 0 18px 42px rgba(15, 118, 110, 0.18);
    }

    .modal-header {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white;
      border-top-left-radius: 22px;
      border-top-right-radius: 22px;
    }

    .modal-header .btn-close {
      filter: brightness(0) invert(1);
    }

    @media (max-width: 992px) {
      .layout-grid {
        grid-template-columns: 1fr;
      }

      .upload-card {
        position: static;
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
    }
    .file-status-list { display: flex; flex-direction: column; gap: 12px; margin-top: 16px; }
    .file-status-item {
      border: 1px solid var(--clinic-border, #d9eef0); border-radius: 14px;
      padding: 14px 16px; background: #fbfefe;
    }
    .file-status-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
    .file-status-name { font-weight: 700; word-break: break-all; }
    .fs-badge { border-radius: 30px; padding: 4px 12px; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.4px; }
    .fs-valid { background: #ccf0e0; color: #115e42; }
    .fs-invalid { background: #ffe0e0; color: #a12020; }
    .fs-uploading { background: #fff3cd; color: #856404; }
    .fs-uploaded { background: #d4f0fc; color: #0c5e7e; }
    .fs-failed { background: #ffe0e0; color: #a12020; }
    .fs-error-box { color: #7f1d1d; font-size: 0.85rem; margin-top: 8px; background: #fff1f2; border: 1px solid #fecaca; border-radius: 10px; padding: 8px 12px; line-height: 1.5; }
    .fs-meta { font-size: 0.8rem; color: #6b7d87; margin-top: 4px; }
    .fs-remove { border: none; background: transparent; color: #a12020; font-weight: 700; cursor: pointer; font-size: 0.85rem; }
    .mini-preview { margin-top: 10px; max-height: 220px; overflow: auto; border: 1px solid var(--clinic-border, #d9eef0); border-radius: 10px; }
    .mini-preview table { font-size: 0.72rem; margin: 0; }
  </style>
</head>

<body>
<div id="app" class="wrapper">

  <div class="header-box d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div class="header-content d-flex align-items-center">
      <div class="header-icon">📤</div>

      <div>
        <h1 class="fw-bold mb-2">Upload SF8 Excel File</h1>
        <p class="mb-0">
          Preview the SF8 file first before submitting it to the clinic nurse for review.
        </p>
      </div>
    </div>

    <div class="header-actions">
      <a href="teacher-dashboard.php" class="btn btn-back">
        Back to Dashboard
      </a>
    </div>
  </div>

  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">
    {{ message }}
  </div>

  <div class="layout-grid">

    <!-- LEFT: controls -->
    <div class="card p-4 upload-card">
      <h4 class="fw-bold mb-3">Upload SF8 Files</h4>

      <div class="upload-zone">
        <div class="upload-icon">📄</div>
        <label class="form-label fw-semibold">Choose `.xlsx` files</label>
        <input type="file" class="form-control" accept=".xlsx" multiple @change="handleFiles">
      </div>

      <div class="step-list mt-3">
        <div class="step-item">
          <div class="step-number">1</div>
          <div>
            <strong>Select files</strong>
            <p>Choose one or more SF8 `.xlsx` files. You can add more anytime.</p>
          </div>
        </div>
        <div class="step-item">
          <div class="step-number">2</div>
          <div>
            <strong>Auto-check</strong>
            <p>ClinicDesk reads cell A1 and the school year, flagging problems per file.</p>
          </div>
        </div>
        <div class="step-item">
          <div class="step-number">3</div>
          <div>
            <strong>Submit valid files</strong>
            <p>Only valid files are submitted. Errored files are skipped.</p>
          </div>
        </div>
      </div>

      <div class="alert alert-info">
        <strong>Reminder:</strong> Put the report purpose/code in <strong>cell A1</strong>.
        The school year is read from each file and must match the active year set by the clinic nurse.
      </div>

      <!-- Summary tally -->
      <div v-if="files.length > 0" class="d-flex gap-2 flex-wrap mb-3">
        <span class="fs-badge fs-valid">Valid: {{ validCount }}</span>
        <span class="fs-badge fs-invalid">Errors: {{ invalidCount }}</span>
        <span class="fs-badge fs-uploaded" v-if="uploadedCount > 0">Submitted: {{ uploadedCount }}</span>
        <span class="fs-badge fs-failed" v-if="failedCount > 0">Failed: {{ failedCount }}</span>
      </div>

      <button
        class="btn btn-green w-100"
        :disabled="validCount === 0 || uploading"
        data-bs-toggle="modal"
        data-bs-target="#confirmModal">
        {{ uploading
            ? "Uploading..."
            : (validCount > 0
                ? "Submit " + validCount + " valid file" + (validCount > 1 ? "s" : "") + " to Clinic Nurse"
                : "No valid files to submit") }}
      </button>
    </div>

    <!-- RIGHT: file list with previews together -->
    <div class="card p-4 preview-card">
      <div class="preview-header">
        <div>
          <h4 class="fw-bold mb-1">Selected Files &amp; Preview</h4>
          <p class="small-note mb-0">
            Each file shows its status and a preview of its contents.
          </p>
        </div>
        <span class="preview-badge" v-if="files.length > 0">
          {{ files.length }} file{{ files.length > 1 ? "s" : "" }}
        </span>
      </div>

      <div v-if="files.length === 0" class="empty-preview">
        <div class="empty-icon">📋</div>
        <h5 class="fw-bold">No files selected yet</h5>
        <p class="mb-0">Select one or more SF8 Excel files from the panel on the left.</p>
      </div>

      <div v-else class="file-status-list">
        <div v-for="f in files" :key="f.id" class="file-status-item">
          <div class="file-status-head">
            <span class="file-status-name">{{ f.name }}</span>
            <span class="fs-badge" :class="{
              'fs-valid': f.status === 'valid',
              'fs-invalid': f.status === 'invalid',
              'fs-uploading': f.status === 'uploading',
              'fs-uploaded': f.status === 'uploaded',
              'fs-failed': f.status === 'failed'
            }">{{ statusLabel(f.status) }}</span>
          </div>

          <div v-if="f.reportCode" class="fs-meta">
            Type: <strong>{{ f.reportCode }}</strong>
            <span v-if="f.schoolYear"> · School Year: <strong>{{ f.schoolYear }}</strong></span>
          </div>

          <div v-if="f.error" class="fs-error-box">
            <strong>Why invalid:</strong> {{ f.error }}
          </div>

          <!-- Inline preview per file -->
          <div v-if="f.previewRows.length > 0" class="mini-preview">
            <table class="table table-bordered table-sm">
              <tbody>
                <tr v-for="(row, ri) in f.previewRows.slice(0, 10)" :key="ri">
                  <td v-for="(cell, ci) in row" :key="ci">{{ cell }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-2">
            <button class="fs-remove" @click="removeFile(f.id)" :disabled="uploading">
              Remove
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>

  <div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-bold">Confirm Submission</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <p>You are about to submit <strong>{{ validCount }}</strong> valid file{{ validCount > 1 ? "s" : "" }} to the clinic nurse.</p>
          <p v-if="invalidCount > 0" class="text-danger mb-0">
            {{ invalidCount }} file{{ invalidCount > 1 ? "s" : "" }} with errors will be skipped.
          </p>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-green" data-bs-dismiss="modal" @click="submitValidFiles">
            Yes, Submit
          </button>
        </div>
      </div>
    </div>
  </div>

</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>

<script>
const { createApp } = Vue;

createApp({
  data() {
    return {
      cloudName: "du3qpurjj",
      uploadPreset: "atansproject-prod-unsigned",

      files: [],              // per-file objects (see makeFileEntry)
      nextFileId: 1,

      activeSchoolYear: "",   // set by the clinic nurse
      uploading: false,
      message: "",
      messageType: "success",

      allowedPurposes: {
        "Students Information": "students_information",
        "OKD and LHAS": "okd_lhas",
        "Immunization & Nutritional Status": "immunization_nutritional_status",
        "Deworming & WIFA": "deworming_wifa",
        "Adolescent Reproductive Health / ARH": "adolescent_reproductive_health_arh",
        "Comprehensive Tobacco Control": "comprehensive_tobacco_control",

        "students_information": "students_information",
        "okd_lhas": "okd_lhas",
        "immunization_nutritional_status": "immunization_nutritional_status",
        "deworming_wifa": "deworming_wifa",
        "adolescent_reproductive_health_arh": "adolescent_reproductive_health_arh",
        "comprehensive_tobacco_control": "comprehensive_tobacco_control"
      }
    };
  },

  computed: {
    validCount()    { return this.files.filter(f => f.status === "valid").length; },
    invalidCount()  { return this.files.filter(f => f.status === "invalid").length; },
    uploadedCount() { return this.files.filter(f => f.status === "uploaded").length; },
    failedCount()   { return this.files.filter(f => f.status === "failed").length; }
  },

  mounted() {
    const activeRole = localStorage.getItem("active_role");
    const token = localStorage.getItem("teacher_id_token");

    if (activeRole !== "Teacher" || !token) {
      window.location.href = "login.php";
    }

    this.loadActiveSchoolYear();
  },

  methods: {
    showMessage(type, text) {
      this.messageType = type;
      this.message = text;
    },

    statusLabel(status) {
      return {
        valid: "✓ Valid",
        invalid: "✗ Invalid",
        uploading: "Uploading...",
        uploaded: "Submitted",
        failed: "Failed"
      }[status] || status;
    },

    async loadActiveSchoolYear() {
      try {
        const res = await fetch("api/get_school_years.php?t=" + Date.now());
        const data = await res.json();
        if (data.success && data.active) {
          this.activeSchoolYear = data.active;
        }
      } catch (e) {
        console.warn("Could not load active school year", e);
      }
    },

    excelDateToYMD(value) {
      if (!value) return "";
      if (typeof value === "string") return value;
      const excelEpoch = new Date(Date.UTC(1899, 11, 30));
      const convertedDate = new Date(excelEpoch.getTime() + value * 86400000);
      const year = convertedDate.getUTCFullYear();
      const month = String(convertedDate.getUTCMonth() + 1).padStart(2, "0");
      const day = String(convertedDate.getUTCDate()).padStart(2, "0");
      return `${year}-${month}-${day}`;
    },

    normalizeYesNo(value) {
      if (value === "" || value === null || value === undefined) return 0;
      const text = String(value).trim().toLowerCase();
      if (text === "1" || text === "yes" || text === "y") return 1;
      if (text === "0" || text === "no" || text === "n") return 0;
      return 0;
    },

    makeFileEntry(file) {
      return {
        id: this.nextFileId++,
        file: file,
        name: file.name,
        status: "pending",   // pending | valid | invalid | uploading | uploaded | failed
        error: "",
        reportCode: "",
        reportPurpose: "",
        schoolYear: "",
        previewRows: [],
        extractedRows: []
      };
    },

    async handleFiles(event) {
      this.message = "";
      const picked = Array.from(event.target.files || []);
      event.target.value = ""; // allow re-selecting the same file later

      for (const file of picked) {
        if (!file.name.toLowerCase().endsWith(".xlsx")) {
          const bad = this.makeFileEntry(file);
          bad.status = "invalid";
          bad.error = "Only .xlsx files are allowed.";
          this.files.push(bad);
          continue;
        }
        const entry = this.makeFileEntry(file);
        // Parse first, then add to the list so it shows valid/invalid immediately (no "parsing" flash)
        await this.parseFile(entry);
        this.files.push(entry);
      }
    },

    removeFile(id) {
      this.files = this.files.filter(f => f.id !== id);
    },

    // Parses one file, sets status valid/invalid. Returns a Promise.
    parseFile(entry) {
      return new Promise((resolve) => {
        const reader = new FileReader();

        reader.onload = async (e) => {
          // Wait for the active school year to finish loading before validating.
          if (this._schoolYearReady) await this._schoolYearReady;
          try {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: "array", cellDates: false });

            const sheetName = workbook.SheetNames.includes("Nutritional Status")
              ? "Nutritional Status"
              : workbook.SheetNames[0];

            const worksheet = workbook.Sheets[sheetName];
            const rows = XLSX.utils.sheet_to_json(worksheet, { header: 1, defval: "", raw: true });

            // A1 = report purpose/code
            const detectedPurpose = rows[0]?.[0] ? String(rows[0][0]).trim() : "";
            if (!detectedPurpose) {
              this.failFile(entry, "Cell A1 is empty — put the report purpose/code in A1.");
              return resolve();
            }
            if (!this.allowedPurposes[detectedPurpose]) {
              this.failFile(entry, `Unrecognised report type in A1: "${detectedPurpose}". Check that A1 matches one of the allowed report codes.`);
              return resolve();
            }

            entry.reportPurpose = detectedPurpose;
            entry.reportCode = this.allowedPurposes[detectedPurpose];
            entry.previewRows = rows.slice(0, 40);

            // School year (row 7, next to the "School Year" label)
            const syResult = this.readSchoolYear(rows);
            if (syResult.error) {
              this.failFile(entry, syResult.error, entry.reportCode);
              return resolve();
            }
            entry.schoolYear = syResult.year;

            // Deworming files carry structured rows.
            if (entry.reportCode === "deworming_wifa") {
              entry.extractedRows = this.parseDewormingWifa(rows);
            }

            entry.status = "valid";
            entry.error = "";
          } catch (error) {
            this.failFile(entry, "Unable to read Excel file: " + error.message);
          }
          resolve();
        };

        reader.onerror = () => { this.failFile(entry, "Could not read the file from disk."); resolve(); };
        reader.readAsArrayBuffer(entry.file);
      });
    },

    failFile(entry, message, keepCode) {
      entry.status = "invalid";
      entry.error = message;
      if (!keepCode) entry.reportCode = "";
      entry.schoolYear = "";
      entry.extractedRows = [];
    },

    // Returns { year } or { error }.
    readSchoolYear(rows) {
      const row7 = rows[6] || [];
      let rawValue = "";
      for (let i = 0; i < row7.length; i++) {
        const cell = row7[i];
        if (typeof cell === "string" && cell.toLowerCase().includes("school year")) {
          for (let j = i + 1; j < row7.length; j++) {
            if (row7[j] !== "" && row7[j] !== null && row7[j] !== undefined) {
              rawValue = String(row7[j]).trim();
              break;
            }
          }
          break;
        }
      }

      if (!rawValue) {
        return { error: 'No school year found in the file (row 7, next to "School Year").' };
      }
      if (!/^\d{4}-\d{4}$/.test(rawValue)) {
        return { error: `Invalid school year format: "${rawValue}". It must be YYYY-YYYY (e.g. 2024-2025).` };
      }
      if (!this.activeSchoolYear) {
        return { error: "No active school year has been set by the clinic nurse." };
      }
      if (rawValue !== this.activeSchoolYear) {
        return { error: `School year (${rawValue}) does not match the active year (${this.activeSchoolYear}) set by the clinic nurse.` };
      }
      return { year: rawValue };
    },

    parseDewormingWifa(rows) {
      const dataRows = rows.slice(10);
      return dataRows
        .filter(row => row[0] && row[2])
        .map(row => ({
          row_no: row[0] || "",
          learner_name: row[2] || "",
          gender: row[6] || "",
          birthdate: this.excelDateToYMD(row[7]),
          age: row[8] || "",
          dewormed_sbfp: this.normalizeYesNo(row[9]),
          dewormed_other: this.normalizeYesNo(row[10]),
          wifa: this.normalizeYesNo(row[11]),
          wifa_date: this.excelDateToYMD(row[12]),
          remarks: row[13] || ""
        }));
    },

    // Uploads every valid file in sequence; errored files are skipped.
    async submitValidFiles() {
      const toUpload = this.files.filter(f => f.status === "valid");
      if (toUpload.length === 0) {
        this.showMessage("error", "No valid files to submit.");
        return;
      }

      this.uploading = true;
      this.message = "";

      let ok = 0, fail = 0;
      for (const entry of toUpload) {
        const result = await this.uploadOne(entry);
        if (result) ok++; else fail++;
      }

      this.uploading = false;

      if (fail === 0) {
        this.showMessage("success", `${ok} file${ok > 1 ? "s" : ""} submitted successfully. Waiting for clinic nurse approval.`);
      } else {
        this.showMessage("error", `${ok} submitted, ${fail} failed. See each file's status below for details.`);
      }
    },

    // Uploads a single file. Returns true on success, false on failure.
    async uploadOne(entry) {
      entry.status = "uploading";
      entry.error = "";

      try {
        const formData = new FormData();
        formData.append("file", entry.file);
        formData.append("upload_preset", this.uploadPreset);

        const cloudinaryUrl = `https://api.cloudinary.com/v1_1/${this.cloudName}/raw/upload`;
        const cloudResponse = await fetch(cloudinaryUrl, { method: "POST", body: formData });
        const cloudText = await cloudResponse.text();

        let cloudResult;
        try {
          cloudResult = JSON.parse(cloudText);
        } catch (jsonError) {
          entry.status = "failed";
          entry.error = "Cloudinary did not return JSON.";
          return false;
        }

        if (!cloudResponse.ok || cloudResult.error) {
          entry.status = "failed";
          entry.error = "Cloudinary upload failed: " + (cloudResult.error?.message || "Unknown error");
          return false;
        }

        const teacherEmail = localStorage.getItem("teacher_email") || "Unknown Teacher";

        const saveResponse = await fetch("api/save_sf8_upload.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            file_name: entry.name,
            file_type: "xlsx",
            report_purpose: entry.reportPurpose,
            report_code: entry.reportCode,
            cloudinary_public_id: cloudResult.public_id,
            cloudinary_url: cloudResult.secure_url,
            uploaded_by_email: teacherEmail,
            school_year: entry.schoolYear,
            extracted_rows: entry.extractedRows
          })
        });

        const saveText = await saveResponse.text();
        let saveResult;
        try {
          saveResult = JSON.parse(saveText);
        } catch (jsonError) {
          entry.status = "failed";
          entry.error = "Save endpoint did not return JSON.";
          return false;
        }

        if (saveResult.success) {
          entry.status = "uploaded";
          entry.error = "";
          return true;
        } else {
          entry.status = "failed";
          entry.error = saveResult.message || "Failed to save upload.";
          return false;
        }
      } catch (error) {
        entry.status = "failed";
        entry.error = "Error: " + error.message;
        return false;
      }
    }
  }
}).mount("#app");
</script>
</body>
</html>