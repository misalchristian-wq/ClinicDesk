<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | SF8 Preview</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <style>
    /* Your existing styles remain unchanged */
    body {
      background: #f4f8fb;
      font-family: Arial, sans-serif;
    }

    .wrapper {
      max-width: 1400px;
      margin: 35px auto;
      padding: 20px;
    }

    .header-box {
      background: linear-gradient(135deg, #198754, #20c997);
      color: white;
      padding: 28px;
      border-radius: 18px;
      margin-bottom: 24px;
    }

    .card {
      border: none;
      border-radius: 18px;
      box-shadow: 0 8px 22px rgba(0,0,0,0.08);
    }

    .table th {
      background: #eef4f7;
      white-space: nowrap;
    }

    .table td {
      white-space: nowrap;
    }

    .btn-green {
      background: #198754;
      color: white;
      font-weight: 600;
    }

    .btn-green:hover {
      background: #146c43;
      color: white;
    }

    .badge-purpose {
      background: #e7f8f1;
      color: #198754;
      border: 1px solid #b7ead5;
      padding: 8px 12px;
      border-radius: 999px;
      font-weight: 700;
      display: inline-block;
    }
  </style>
</head>

<body>
<div id="app" class="wrapper">

  <div class="header-box d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
      <h1 class="fw-bold mb-2">SF8 Preview</h1>
      <p class="mb-0">Review extracted learner data before approval.</p>
    </div>

    <a href="nurse-sf8-uploads.php" class="btn btn-light">Back to Uploads</a>
  </div>

  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">
    {{ message }}
  </div>

  <div class="card p-4 mb-4" v-if="upload">
    <h4 class="fw-bold">Upload Information</h4>
    <p><strong>File:</strong> {{ upload.file_name }}</p>
    <p><strong>Uploaded By:</strong> {{ upload.uploaded_by_email }}</p>
    <p><strong>Status:</strong> {{ upload.status }}</p>
    <p v-if="reportCode">
      <strong>Report Type:</strong>
      <span class="badge-purpose">{{ reportLabel }}</span>
    </p>
  </div>

  <div class="card p-4 mb-4" v-if="hasHeaderData">
    <h4 class="fw-bold">School Information</h4>
    <div class="row">
      <div class="col-md-4"><strong>School:</strong> {{ header.school_name || "" }}</div>
      <div class="col-md-4"><strong>District:</strong> {{ header.district || "" }}</div>
      <div class="col-md-4"><strong>Division:</strong> {{ header.division || "" }}</div>
      <div class="col-md-4"><strong>Region:</strong> {{ header.region || "" }}</div>
      <div class="col-md-4"><strong>Grade:</strong> {{ header.grade_level || "" }}</div>
      <div class="col-md-4"><strong>Section:</strong> {{ header.section || "" }}</div>
      <div class="col-md-4"><strong>School Year:</strong> {{ header.school_year || "" }}</div>
    </div>
  </div>

  <div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <h4 class="fw-bold mb-0">Extracted Records: {{ records.length }}</h4>

      <div v-if="upload && upload.status === 'Pending'">
        <button class="btn btn-green me-2" @click="approveUpload" :disabled="loading">
          Approve and Save
        </button>

        <button class="btn btn-danger" @click="rejectUpload" :disabled="loading">
          Reject
        </button>
      </div>
    </div>

    <div v-if="loading" class="alert alert-info">Loading, please wait...</div>

    <div class="table-responsive" v-if="records.length > 0">
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

</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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
  },

  methods: {
    formatValue(value) {
      if (value === null || value === undefined) return "—";
      if (typeof value === "string" && value.trim() === "") return "—";
      return value;
    },

    async loadPreview() {
      this.loading = true;
      this.message = "";

      try {
        const response = await fetch("api/parse_sf8_from_upload.php?upload_id=" + this.uploadId);
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

    async approveUpload() {
      try {
        const response = await fetch("api/approve_sf8_upload.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            upload_id: this.uploadId,
            reviewed_by: "Clinic Nurse"
          })
        });

        const data = await response.json();

        if (data.success) {
          alert(data.message);
          window.location.href = "nurse-sf8-uploads.php";
        } else {
          // Show modal instead of alert
          this.errorMessage = data.message || "Approval failed. Please check the file and try again.";
          this.errorDetails = data.details || "";
          if (this.errorModal) {
            this.errorModal.show();
          } else {
            alert(this.errorMessage);
          }
        }
      } catch (error) {
        console.error(error);
        this.errorMessage = "Server error while approving upload: " + error.message;
        if (this.errorModal) {
          this.errorModal.show();
        } else {
          alert(this.errorMessage);
        }
      }
    },

    async rejectUpload() {
      const reason = prompt("Enter rejection reason:", "Invalid or incomplete file.");
      if (reason === null) return;

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
          this.messageType = "success";
          this.message = result.message;
          this.loadPreview();
        } else {
          this.messageType = "error";
          this.message = result.message || "Rejection failed.";
        }
      } catch (error) {
        this.messageType = "error";
        this.message = "Error: " + error.message;
      }
      this.loading = false;
    }
  }
}).mount("#app");
</script>
</body>
</html>