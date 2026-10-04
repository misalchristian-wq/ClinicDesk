<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | SF8 Preview</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

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
      background: radial-gradient(circle at top left, rgba(20,184,166,.16), transparent 25%),
        radial-gradient(circle at top right, rgba(14,165,233,.12), transparent 25%),
        linear-gradient(135deg, #eef8fb, #f8fcfd);
      font-family: Arial, sans-serif;
      color: var(--clinic-text);
    }
    .wrapper {
      max-width: 1450px;
      margin: 28px auto;
      padding: 20px;
    }
    .header-box {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white;
      padding: 34px;
      border-radius: 28px;
      margin-bottom: 24px;
      box-shadow: 0 16px 38px rgba(15,118,110,.22);
      position: relative;
      overflow: hidden;
    }
    .header-box::before, .header-box::after { content: ""; position: absolute; border-radius: 50%; background: rgba(255,255,255,.13); }
    .header-box::before { width: 240px; height: 240px; top: -100px; right: -75px; }
    .header-box::after { width: 190px; height: 190px; bottom: -100px; left: 35%; }
    .header-content, .header-actions { position: relative; z-index: 1; }
    .header-icon { width: 62px; height: 62px; border-radius: 20px; background: rgba(255,255,255,.18);
      border: 2px solid rgba(255,255,255,.35); display: flex; align-items: center; justify-content: center;
      font-size: 29px; flex-shrink: 0; }
    .header-box h1 { font-size: 38px; font-weight: 900; margin: 0 0 6px; }
    .header-box p { color: rgba(255,255,255,.92); margin: 0; }
    .btn-back { background: #fff; color: var(--clinic-primary); border: 0; border-radius: 15px;
      padding: 11px 18px; font-weight: 800; text-decoration: none; box-shadow: 0 12px 28px rgba(0,0,0,.12); }
    .btn-back:hover { color: var(--clinic-primary); background: #ecfeff; }
    .card {
      border: 1px solid var(--clinic-border);
      border-radius: var(--clinic-radius);
      background: var(--clinic-card);
      box-shadow: var(--clinic-shadow);
    }
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }
    .section-heading { display: flex; align-items: center; gap: 10px; font-size: 20px; font-weight: 900;
      color: var(--clinic-primary); margin-bottom: 20px; }
    .section-heading i { width: 37px; height: 37px; border-radius: 12px; background: var(--clinic-light);
      display: inline-flex; align-items: center; justify-content: center; }
    .info-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
    .info-item { min-width: 0; padding: 12px 14px; background: #f7fcfc; border: 1px solid #e6f1f2; border-radius: 14px; }
    .info-label { display: block; color: var(--clinic-muted); font-size: 11px; font-weight: 800;
      letter-spacing: .6px; text-transform: uppercase; margin-bottom: 5px; }
    .info-value { font-weight: 700; overflow-wrap: anywhere; }
    .records-count { background: var(--clinic-light); border: 1px solid #b8e9e3; color: var(--clinic-primary);
      border-radius: 999px; padding: 7px 12px; font-size: 13px; font-weight: 800; }
    .records-subtitle { color: var(--clinic-muted); margin: 4px 0 0; font-size: 14px; }
    .records-table { border: 1px solid var(--clinic-border); border-radius: 15px; overflow-x: auto; }
    .records-table .table { margin-bottom: 0; }
    .records-table .table th { background: #e8f7f4; color: var(--clinic-primary); font-size: 12px;
      letter-spacing: .3px; text-transform: uppercase; white-space: nowrap; padding: 13px 12px; }
    .records-table .table td { white-space: nowrap; padding: 11px 12px; border-color: #e6f0f1; }
    .records-table .table tbody tr:hover { background: #f4fbfa; }
    .btn-green {
      background: var(--clinic-primary);
      color: white;
      border-radius: 12px;
      padding: 10px 17px;
      font-weight: 800;
    }
    .btn-green:hover { background: #0b625b; color: white; }
    .btn-reject { border-radius: 12px; padding: 10px 17px; font-weight: 800; }
    .badge-purpose {
      background: var(--clinic-light);
      color: var(--clinic-primary);
      border: 1px solid #b8e9e3;
      padding: 6px 10px;
      border-radius: 999px;
      font-weight: 800;
      display: inline-block;
    }
    .modal-content { border: 1px solid var(--clinic-border); border-radius: 20px; overflow: hidden; }
    .modal-header-clinic { background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary)); color: white; }
    .identity-card { background: #f7fcfc; border: 1px solid var(--clinic-border); border-radius: 14px; padding: 15px; }
    .identity-pair { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; font-weight: 800; }
    .identity-pair span { padding: 9px 12px; border-radius: 10px; background: white; border: 1px solid var(--clinic-border); }
    .identity-pair i { color: var(--clinic-primary); }
    @media (max-width: 850px) { .info-grid { grid-template-columns: 1fr; } .header-box h1 { font-size: 30px; } }
    @media (max-width: 520px) { .wrapper { padding: 12px; margin: 12px auto; } .header-box { padding: 24px; }
      .header-icon { display: none; } .info-list { grid-template-columns: 1fr; } }
  </style>
</head>

<body>
<div id="app" class="wrapper">

  <div class="header-box d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div class="header-content d-flex align-items-center gap-3">
      <div class="header-icon"><i class="bi bi-file-earmark-spreadsheet"></i></div>
      <div>
        <h1>SF8 Preview</h1>
        <p>Review extracted learner data before approval.</p>
      </div>
    </div>
    <a href="nurse-sf8-uploads.php" class="btn-back header-actions"><i class="bi bi-arrow-left me-2"></i>Back to Uploads</a>
  </div>

  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">
    {{ message }}
  </div>

  <div class="info-grid" v-if="upload || hasHeaderData">
    <div class="card p-4" v-if="upload">
      <h2 class="section-heading"><i class="bi bi-cloud-check"></i>Upload Information</h2>
      <div class="info-list">
        <div class="info-item"><span class="info-label">File name</span><span class="info-value">{{ upload.file_name }}</span></div>
        <div class="info-item"><span class="info-label">Status</span><span class="badge-purpose">{{ upload.status }}</span></div>
        <div class="info-item"><span class="info-label">Uploaded by</span><span class="info-value">{{ upload.uploaded_by_email }}</span></div>
        <div class="info-item"><span class="info-label">Report type</span><span class="info-value">{{ reportLabel }}</span></div>
      </div>
    </div>
    <div class="card p-4" v-if="hasHeaderData">
      <h2 class="section-heading"><i class="bi bi-building"></i>School Information</h2>
      <div class="info-list">
        <div class="info-item"><span class="info-label">School</span><span class="info-value">{{ header.school_name || "—" }}</span></div>
        <div class="info-item"><span class="info-label">School year</span><span class="info-value">{{ header.school_year || "—" }}</span></div>
        <div class="info-item"><span class="info-label">District</span><span class="info-value">{{ header.district || "—" }}</span></div>
        <div class="info-item"><span class="info-label">Division</span><span class="info-value">{{ header.division || "—" }}</span></div>
        <div class="info-item"><span class="info-label">Region</span><span class="info-value">{{ header.region || "—" }}</span></div>
        <div class="info-item"><span class="info-label">Grade / Section</span><span class="info-value">{{ header.grade_level || "—" }} / {{ header.section || "—" }}</span></div>
      </div>
    </div>
  </div>

  <div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <div>
        <h2 class="section-heading mb-0"><i class="bi bi-list-check"></i>Extracted Records <span class="records-count">{{ records.length }}</span></h2>
        <p class="records-subtitle">Check the file contents before saving them to the student records. For Student Information, height-for-age is recalculated from sex, age, and standing height using WHO tables.</p>
      </div>

      <div v-if="upload && upload.status === 'Pending'" class="d-flex flex-wrap gap-2">
        <button class="btn btn-green" @click="approveUpload()" :disabled="loading">
          <i class="bi bi-check2-circle me-1"></i>{{ loading ? "Working..." : "Approve and Save" }}
        </button>

        <button class="btn btn-outline-danger btn-reject" @click="openRejectModal" :disabled="loading">
          <i class="bi bi-x-circle me-1"></i>Reject
        </button>
      </div>
    </div>
    <div v-if="upload && upload.file_type === 'sf8_enc_v1'" class="alert alert-success py-2 small mb-3">
      <i class="bi bi-shield-lock me-1"></i>Encrypted cloud file · Decrypted preview
    </div>
    <div v-if="reportCode && reportCode !== 'students_information'" class="alert alert-info py-2 small mb-3">
      <i class="bi bi-person-plus me-1"></i>If this learner has no Student Information yet, approval creates a provisional learner profile and saves only this file's health records.
    </div>

    <div v-if="loading" class="alert alert-info">Loading, please wait...</div>

    <div class="table-responsive records-table" v-if="records.length > 0">
      <table class="table table-bordered table-sm align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th v-for="col in currentColumns" :key="col.key">
              {{ col.label }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(record, index) in records" :key="index">
            <td class="text-center">{{ index + 1 }}</td>
            <td v-for="col in currentColumns" :key="col.key">
              {{ formatValue(record[col.key]) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="!loading && records.length === 0" class="alert alert-warning">
      No records extracted from this file.
    </div>
  </div>

  <!-- ERROR MODAL -->
  <div class="modal fade" id="errorModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title fw-bold">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>Approval Failed
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>{{ errorMessage }}</p>
          <div v-if="errorDetails" class="alert alert-secondary mt-2 small">
            <strong>Details:</strong> {{ errorDetails }}
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- EXISTING-DATA REVIEW MODAL -->
  <div class="modal fade" id="conflictModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header modal-header-clinic">
          <h5 class="modal-title fw-bold"><i class="bi bi-arrow-repeat me-2"></i>Review existing learner data</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-3">Review each change for the same LRN and school year. Empty fields will still be filled automatically.</p>
          <div v-if="hasExistingDuplicates" class="alert alert-danger">
            Some learners already have multiple rows. Resolve those records in the Records Manager before this file can be approved.
          </div>
          <div v-for="(conflict, index) in conflicts" :key="index" class="border rounded p-3 mb-3">
            <div class="fw-bold mb-2">{{ conflict.learner_name }} · LRN {{ conflict.lrn }} · {{ conflict.school_year }}</div>
            <div v-if="conflict.existing_count > 1" class="alert alert-warning py-2">
              {{ conflict.existing_count }} existing rows were found for this record.
              <div v-for="row in conflict.existing_rows" :key="row.id" class="small mt-2">
                Record #{{ row.id }}: {{ formatConflictRow(row) }}
              </div>
            </div>
            <div class="table-responsive" v-if="conflict.changes.length">
              <table class="table table-sm table-bordered align-middle mb-0">
                <thead><tr><th>Field</th><th>Existing data</th><th></th><th>New SF8 data</th></tr></thead>
                <tbody>
                  <tr v-for="change in conflict.changes" :key="change.field">
                    <td>{{ change.field.replace(/_/g, ' ') }}</td>
                    <td>{{ formatValue(change.existing) }}</td>
                    <td class="text-center fw-bold">→</td>
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
          <button type="button" class="btn btn-warning fw-bold" @click="confirmOverride" :disabled="loading || hasExistingDuplicates || !conflictFingerprint">Override and Approve</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="identityModal" tabindex="-1" data-bs-backdrop="static" aria-labelledby="identityModalTitle">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header modal-header-clinic">
          <h5 class="modal-title fw-bold" id="identityModalTitle"><i class="bi bi-person-exclamation me-2"></i>Check learner LRN</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>The same learner name and school year appear with different LRNs. No records were saved. Compare the workbook with the existing record and correct the incorrect LRN before approval. ClinicDesk will not merge learners by name.</p>
          <div v-for="(issue, index) in identityConflicts" :key="index" class="identity-card mb-3">
            <div class="fw-bold mb-2">{{ issue.learner_name }} · {{ issue.school_year }}</div>
            <div class="identity-pair"><span>Existing LRN: {{ issue.existing_lrn }}</span><i class="bi bi-arrow-right"></i><span>File LRN: {{ issue.incoming_lrn }}</span></div>
            <div class="small text-muted mt-2">Found in {{ issue.source }}</div>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-green" data-bs-dismiss="modal">Review workbook</button></div>
      </div>
    </div>
  </div>

  <!-- SUCCESS MODAL -->
  <div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title fw-bold">
            <i class="bi bi-check-circle-fill me-2"></i>Success
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="mb-2">{{ successMessage }}</p>
          <div v-if="successDetails" class="alert alert-secondary mt-2 small mb-0">
            {{ successDetails }}
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>

  <!-- REJECT REASON MODAL -->
  <div class="modal fade" id="rejectModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title fw-bold">
            <i class="bi bi-x-circle-fill me-2"></i>Reject Upload
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label class="form-label fw-semibold">Reason for rejection</label>
          <textarea
            v-model="rejectReason"
            class="form-control"
            rows="3"
            placeholder="Enter the reason this file is being rejected..."></textarea>
          <div v-if="!rejectReason.trim()" class="text-danger small mt-1">
            A reason is required.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" @click="confirmReject" :disabled="!rejectReason.trim()">
            Confirm Reject
          </button>
        </div>
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
      uploadId: "",
      upload: null,
      header: null,
      records: [],
      reportCode: "",
      loading: false,
      message: "",
      messageType: "success",
      errorMessage: "",
      errorDetails: "",
      errorModal: null,
      conflictModal: null,
      identityModal: null,
      identityConflicts: [],
      conflicts: [],
      hasExistingDuplicates: false,
      conflictFingerprint: "",

      // Success + reject modals
      successMessage: "",
      successDetails: "",
      successModal: null,
      successRedirect: false,
      rejectReason: "Invalid or incomplete file.",
      rejectModal: null,

      labels: {
        students_information: "Nutritional Status (SF8)",
        okd_lhas: "OKD and LHAS",
        immunization_nutritional_status: "Immunization & Nutritional Status",
        deworming_wifa: "Deworming & WIFA",
        adolescent_reproductive_health_arh: "Adolescent Reproductive Health / ARH",
        comprehensive_tobacco_control: "Comprehensive Tobacco Control"
      },

      columnMap: {
        students_information: [
          { key: "lrn", label: "LRN" },
          { key: "sex", label: "Sex" },
          { key: "learner_name", label: "Learner Name" },
          { key: "birthdate", label: "Birthdate" },
          { key: "age", label: "Age" },
          { key: "weight_kg", label: "Weight (kg)" },
          { key: "height_m", label: "Height (m)" },
          { key: "height_squared", label: "Height²" },
          { key: "bmi", label: "BMI" },
          { key: "bmi_category", label: "BMI Category" },
          { key: "height_for_age", label: "Height-for-Age" },
          { key: "remarks", label: "Remarks" }
        ],
        okd_lhas: [
          { key: "lrn", label: "LRN" },          // <-- ADD THIS LINE
          { key: "learner_name", label: "Learner Name" },
          { key: "grade_level", label: "Grade" },
          { key: "section", label: "Section" },
          { key: "gender", label: "Gender" },
          { key: "age", label: "Age" },
          { key: "screening_type", label: "Screening Type" },
          { key: "masterlisted", label: "Masterlisted" },
          { key: "screened", label: "Screened" },
          { key: "findings", label: "Findings" },
          { key: "referred_school", label: "Referred School" },
          { key: "referred_lgu", label: "Referred LGU" },
          { key: "referred_private", label: "Referred Private" },
          { key: "referred_others", label: "Referred Others" },
          { key: "remarks", label: "Remarks" }
      ],
        immunization_nutritional_status: [
          { key: "lrn", label: "LRN" },          // ADD THIS
          { key: "learner_name", label: "Learner Name" },
          { key: "grade_level", label: "Grade" },
          { key: "section", label: "Section" },
          { key: "gender", label: "Gender" },
          { key: "age", label: "Age" },
          { key: "vaccine", label: "Vaccine" },
          { key: "dose", label: "Dose" },
          { key: "immunized", label: "Immunized" },
          { key: "remarks", label: "Remarks" }
      ],
        deworming_wifa: [
          { key: "lrn", label: "LRN" },          // <-- ADD THIS LINE
          { key: "learner_name", label: "Learner Name" },
          { key: "gender", label: "Gender" },
          { key: "birthdate", label: "Birthdate" },
          { key: "age", label: "Age" },
          { key: "dewormed_sbfp", label: "Dewormed SBFP" },
          { key: "dewormed_other", label: "Dewormed Other" },
          { key: "wifa", label: "WIFA" },
          { key: "wifa_date", label: "WIFA Date" },
          { key: "remarks", label: "Remarks" }
      ],
        adolescent_reproductive_health_arh: [
          { key: "lrn", label: "LRN" },          // <-- ADD THIS LINE
          { key: "learner_name", label: "Learner Name" },
          { key: "grade_level", label: "Grade" },
          { key: "section", label: "Section" },
          { key: "gender", label: "Gender" },
          { key: "age", label: "Age" },
          { key: "pregnancy_status", label: "Pregnancy Status" },
          { key: "delivery_mode", label: "Delivery Mode" },
          { key: "peer_educator", label: "Peer Educator" },
          { key: "remarks", label: "Remarks" }
        ],
        ccomprehensive_tobacco_control: [
          { key: "lrn", label: "LRN" },          // <-- ADD THIS LINE
          { key: "learner_name", label: "Learner Name" },
          { key: "grade_level", label: "Grade" },
          { key: "section", label: "Section" },
          { key: "gender", label: "Gender" },
          { key: "age", label: "Age" },
          { key: "violation_type", label: "Violation Type" },
          { key: "referred_to_care", label: "Referred to Care" },
          { key: "remarks", label: "Remarks" }
      ]

      }
    };
  },

  computed: {
    hasHeaderData() {
      if (!this.header) return false;
      return Object.values(this.header).some(value => 
        value !== null && value !== undefined && String(value).trim() !== ""
      );
    },

    reportLabel() {
      return this.labels[this.reportCode] || this.reportCode || "Nutritional Status (SF8)";
    },

    currentColumns() {
      if (this.columnMap[this.reportCode]) {
        return this.columnMap[this.reportCode];
      }
      if (this.records.length > 0) {
        return Object.keys(this.records[0]).map(key => ({
          key: key,
          label: key.replace(/_/g, ' ').toUpperCase()
        }));
      }
      return [];
    }
  },

  mounted() {
    const params = new URLSearchParams(window.location.search);
    this.uploadId = params.get("upload_id");

    if (!this.uploadId) {
      this.messageType = "error";
      this.message = "No upload ID provided.";
      return;
    }

    this.loadPreview();

    // Initialize Bootstrap modal
    const modalElement = document.getElementById("errorModal");
    if (modalElement) {
      this.errorModal = new bootstrap.Modal(modalElement);
    }
    const conflictEl = document.getElementById("conflictModal");
    if (conflictEl) this.conflictModal = new bootstrap.Modal(conflictEl);
    const identityEl = document.getElementById("identityModal");
    if (identityEl) this.identityModal = new bootstrap.Modal(identityEl);
    const successEl = document.getElementById("successModal");
    if (successEl) {
      this.successModal = new bootstrap.Modal(successEl);
      // If this success should redirect, do it when the modal is dismissed.
      successEl.addEventListener("hidden.bs.modal", () => {
        if (this.successRedirect) {
          window.location.href = "nurse-sf8-uploads.php";
        }
      });
    }
    const rejectEl = document.getElementById("rejectModal");
    if (rejectEl) {
      this.rejectModal = new bootstrap.Modal(rejectEl);
    }
  },

  methods: {
    formatConflictRow(row) {
      return Object.entries(row.values || {})
        .filter(([, value]) => value !== null && value !== "")
        .map(([key, value]) => key.replace(/_/g, " ") + ": " + value)
        .join(" · ");
    },
    formatValue(value) {
      if (value === null || value === undefined) return "—";
      if (typeof value === "string" && value.trim() === "") return "—";
      return value;
    },

    async loadPreview() {
      this.loading = true;
      this.message = "";

      try {
        const response = await clinicSf8Fetch("api/parse_sf8_from_upload.php?upload_id=" + this.uploadId);
        const text = await response.text();
        console.log("Preview raw response:", text);

        let result;
        try {
          result = JSON.parse(text);
        } catch (jsonError) {
          this.messageType = "error";
          this.message = "Preview API did not return valid JSON. Check server logs.";
          this.loading = false;
          return;
        }

        if (result.success) {
          this.upload = result.upload || null;
          this.header = result.header || null;
          this.reportCode = result.report_code || result.upload?.report_code || "students_information";
          this.records = result.records || result.students || [];
        } else {
          this.messageType = "error";
          this.message = result.message || "Failed to load preview.";
        }
      } catch (error) {
        this.messageType = "error";
        this.message = "Error: " + error.message;
      }

      this.loading = false;
    },

    async approveUpload(overrideExisting = false) {
      this.loading = true;
      try {
        const response = await clinicSf8Fetch("api/approve_sf8_upload.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            upload_id: this.uploadId,
            override_existing: overrideExisting,
            conflict_fingerprint: overrideExisting ? this.conflictFingerprint : ""
          })
        });

        const data = await response.json();

        if (data.success) {
          this.successMessage = data.message || "Upload approved and student records saved.";
          // If the server reported how many were new vs skipped, show it.
          if (data.details) {
            const d = data.details;
            this.successDetails =
              `New records saved: ${d.new_records ?? "-"}. ` +
              `Skipped (already existed this school year): ${d.skipped_existing ?? 0}. ` +
              `Skipped (duplicate in file): ${d.skipped_in_file ?? 0}.`;
          } else {
            this.successDetails = "";
          }
          this.successRedirect = true;
          if (this.successModal) {
            this.successModal.show();
          } else {
            window.location.href = "nurse-sf8-uploads.php";
          }
        } else if (Array.isArray(data.identity_conflicts) && data.identity_conflicts.length) {
          this.identityConflicts = data.identity_conflicts;
          if (this.identityModal) this.identityModal.show();
        } else if (data.requires_override && Array.isArray(data.conflicts)) {
          this.conflicts = data.conflicts;
          this.hasExistingDuplicates = !!data.has_existing_duplicates;
          this.conflictFingerprint = data.conflict_fingerprint || "";
          if (this.conflictModal) this.conflictModal.show();
        } else {
          this.errorMessage = data.message || "Approval failed. Please check the file and try again.";
          this.errorDetails = data.details
            ? (typeof data.details === "string" ? data.details : JSON.stringify(data.details))
            : "";
          if (this.errorModal) this.errorModal.show();
        }
      } catch (error) {
        console.error(error);
        this.errorMessage = "Server error while approving upload: " + error.message;
        this.errorDetails = "";
        if (this.errorModal) this.errorModal.show();
      }
      this.loading = false;
    },

    async confirmOverride() {
      if (this.loading || this.hasExistingDuplicates || !this.conflictFingerprint) return;
      const modalElement = document.getElementById("conflictModal");
      const hidden = new Promise(resolve => modalElement.addEventListener("hidden.bs.modal", resolve, { once: true }));
      this.conflictModal.hide();
      await hidden;
      await this.approveUpload(true);
    },

    openRejectModal() {
      this.rejectReason = "Invalid or incomplete file.";
      if (this.rejectModal) this.rejectModal.show();
    },

    async confirmReject() {
      const reason = (this.rejectReason || "").trim();
      if (!reason) {
        // Keep the modal open; show a small inline note handled in template.
        return;
      }
      if (this.rejectModal) this.rejectModal.hide();

      this.loading = true;
      try {
        const response = await fetch("api/reject_sf8_upload.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            upload_id: this.uploadId,
            reviewed_by: "Clinic Nurse",
            remarks: reason
          })
        });

        const result = await response.json();

        if (result.success) {
          this.successMessage = result.message || "Upload rejected.";
          this.successDetails = "";
          this.successRedirect = false;
          if (this.successModal) this.successModal.show();
          this.loadPreview();
        } else {
          this.errorMessage = result.message || "Rejection failed.";
          this.errorDetails = "";
          if (this.errorModal) this.errorModal.show();
        }
      } catch (error) {
        this.errorMessage = "Error: " + error.message;
        this.errorDetails = "";
        if (this.errorModal) this.errorModal.show();
      }
      this.loading = false;
    }
  }
}).mount("#app");
</script>
<script src="assets/table-pagination.js" defer></script>
</body>
</html>
