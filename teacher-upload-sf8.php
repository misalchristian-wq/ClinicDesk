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
      grid-template-columns: 0.9fr 1.1fr;
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
    <div class="card p-4 upload-card">
      <h4 class="fw-bold mb-3">Select SF8 Excel File</h4>

      <div class="step-list">
        <div class="step-item">
          <div class="step-number">1</div>
          <div>
            <strong>Select file</strong>
            <p>Choose the official SF8 `.xlsx` file from your device.</p>
          </div>
        </div>

        <div class="step-item">
          <div class="step-number">2</div>
          <div>
            <strong>Read purpose</strong>
            <p>ClinicDesk will read cell A1 to detect the report type.</p>
          </div>
        </div>

        <div class="step-item">
          <div class="step-number">3</div>
          <div>
            <strong>Submit to clinic</strong>
            <p>The clinic nurse will review, approve, or reject your uploaded file.</p>
          </div>
        </div>
      </div>

      <div class="upload-zone">
        <div class="upload-icon">📄</div>

        <label class="form-label fw-semibold">Upload `.xlsx` file</label>
        <input type="file" class="form-control" accept=".xlsx" @change="handleFile">

        <div v-if="selectedFile" class="file-pill">
          <span>✓</span>
          <div>
            <strong>{{ selectedFile.name }}</strong>
            <div class="small-note mb-0">Ready for preview and submission</div>
          </div>
        </div>
      </div>

      <div class="alert alert-info">
        <strong>Reminder:</strong> Put the report purpose dropdown/code in <strong>cell A1</strong>.
      </div>

      <button
        class="btn btn-green w-100"
        :disabled="!selectedFile || previewRows.length === 0 || !reportCode || uploading"
        data-bs-toggle="modal"
        data-bs-target="#confirmModal">
        {{ uploading ? "Uploading..." : "Submit to Clinic Nurse" }}
      </button>
    </div>

    <div class="card p-4 preview-card">
      <div class="preview-header">
        <div>
          <h4 class="fw-bold mb-1">Excel Preview</h4>
          <p class="small-note mb-0">
            Preview shows the first 40 rows from the selected sheet.
          </p>
        </div>

        <span class="purpose-badge" v-if="reportCode">
          {{ reportCode }}
        </span>

        <span class="preview-badge" v-if="previewRows.length > 0">
          {{ previewRows.length }} preview rows
        </span>
      </div>

      <div v-if="previewRows.length === 0" class="empty-preview">
        <div class="empty-icon">📋</div>
        <h5 class="fw-bold">No preview available yet</h5>
        <p class="mb-0">
          Select an SF8 Excel file to generate a preview before submission.
        </p>
      </div>

      <div class="table-responsive" v-if="previewRows.length > 0">
        <table class="table table-bordered table-sm">
          <tbody>
            <tr v-for="(row, rowIndex) in previewRows" :key="rowIndex">
              <td v-for="(cell, cellIndex) in row" :key="cellIndex">
                {{ cell }}
              </td>
            </tr>
          </tbody>
        </table>
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
          Are you sure you want to submit this file as <strong>{{ reportCode }}</strong> to the clinic nurse for review?
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-green" data-bs-dismiss="modal" @click="uploadToCloudinary">
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

      selectedFile: null,
      previewRows: [],
      extractedRows: [],
      reportCode: "",
      reportPurpose: "",

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

  mounted() {
    const activeRole = localStorage.getItem("active_role");
    const token = localStorage.getItem("teacher_id_token");

    if (activeRole !== "Teacher" || !token) {
      window.location.href = "login.php";
    }
  },

  methods: {
    showMessage(type, text) {
      this.messageType = type;
      this.message = text;
    },

    excelDateToYMD(value) {
      if (!value) return "";

      if (typeof value === "string") {
        return value;
      }

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

    handleFile(event) {
      const file = event.target.files[0];

      this.message = "";
      this.previewRows = [];
      this.extractedRows = [];
      this.reportCode = "";
      this.reportPurpose = "";

      if (!file) return;

      if (!file.name.toLowerCase().endsWith(".xlsx")) {
        this.showMessage("error", "Only .xlsx files are allowed.");
        event.target.value = "";
        return;
      }

      this.selectedFile = file;
      this.readExcelPreview(file);
    },

    readExcelPreview(file) {
      const reader = new FileReader();

      reader.onload = (e) => {
        try {
          const data = new Uint8Array(e.target.result);
          const workbook = XLSX.read(data, { type: "array", cellDates: false });

          const sheetName = workbook.SheetNames.includes("Nutritional Status")
            ? "Nutritional Status"
            : workbook.SheetNames[0];

          const worksheet = workbook.Sheets[sheetName];

          const rows = XLSX.utils.sheet_to_json(worksheet, {
            header: 1,
            defval: "",
            raw: true
          });

          const detectedPurpose = rows[0]?.[0] ? String(rows[0][0]).trim() : "";
          console.log("A1 detected purpose:", detectedPurpose);

          if (!detectedPurpose) {
            this.showMessage("error", "Cell A1 is empty. Please put report purpose/code in A1.");
            return;
          }

          if (!this.allowedPurposes[detectedPurpose]) {
            this.showMessage("error", "Invalid A1 report purpose/code: " + detectedPurpose);
            return;
          }

          this.reportPurpose = detectedPurpose;
          this.reportCode = this.allowedPurposes[detectedPurpose];

          this.previewRows = rows.slice(0, 40);

          if (this.reportCode === "deworming_wifa") {
            this.fetchDewormingWifaData(rows);
          } else {
            this.showMessage("success", "Excel detected as: " + this.reportCode);
          }

        } catch (error) {
          this.showMessage("error", "Unable to read Excel file: " + error.message);
        }
      };

      reader.readAsArrayBuffer(file);
    },

    fetchDewormingWifaData(rows) {
      const dataRows = rows.slice(10);

      this.extractedRows = dataRows
        .filter(row => row[0] && row[2])
        .map(row => {
          return {
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
          };
        });

      console.log("Fetched Deworming & WIFA data:", this.extractedRows);

      this.showMessage(
        "success",
        "Deworming & WIFA file detected. " + this.extractedRows.length + " student rows fetched."
      );
    },

    async uploadToCloudinary() {
      if (!this.selectedFile) {
        this.showMessage("error", "Please select an Excel file first.");
        return;
      }

      if (!this.reportCode) {
        this.showMessage("error", "Missing report code. Please check cell A1.");
        return;
      }

      this.uploading = true;
      this.message = "";

      try {
        const formData = new FormData();
        formData.append("file", this.selectedFile);
        formData.append("upload_preset", this.uploadPreset);

        const cloudinaryUrl = `https://api.cloudinary.com/v1_1/${this.cloudName}/raw/upload`;

        const cloudResponse = await fetch(cloudinaryUrl, {
          method: "POST",
          body: formData
        });

        const cloudText = await cloudResponse.text();
        console.log("Cloudinary raw response:", cloudText);

        let cloudResult;

        try {
          cloudResult = JSON.parse(cloudText);
        } catch (jsonError) {
          this.showMessage("error", "Cloudinary did not return JSON. Check console.");
          this.uploading = false;
          return;
        }

        if (!cloudResponse.ok || cloudResult.error) {
          this.showMessage(
            "error",
            "Cloudinary upload failed: " + (cloudResult.error?.message || "Unknown error")
          );
          this.uploading = false;
          return;
        }

        const teacherEmail = localStorage.getItem("teacher_email") || "Unknown Teacher";

        const saveResponse = await fetch("api/save_sf8_upload.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            file_name: this.selectedFile.name,
            file_type: "xlsx",
            report_purpose: this.reportPurpose,
            report_code: this.reportCode,
            cloudinary_public_id: cloudResult.public_id,
            cloudinary_url: cloudResult.secure_url,
            uploaded_by_email: teacherEmail,
            extracted_rows: this.extractedRows
          })
        });

        const saveText = await saveResponse.text();
        console.log("Save upload raw response:", saveText);

        let saveResult;

        try {
          saveResult = JSON.parse(saveText);
        } catch (jsonError) {
          this.showMessage("error", "Save upload PHP did not return JSON. Check console.");
          this.uploading = false;
          return;
        }

        if (saveResult.success) {
          this.showMessage("success", "File submitted successfully. Waiting for clinic nurse approval.");
          this.selectedFile = null;
          this.previewRows = [];
          this.extractedRows = [];
          this.reportCode = "";
          this.reportPurpose = "";
        } else {
          this.showMessage("error", saveResult.message || "Failed to save upload.");
        }

      } catch (error) {
        this.showMessage("error", "Error: " + error.message);
      }

      this.uploading = false;
    }
  }
}).mount("#app");
</script>
</body>
</html>