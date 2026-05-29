<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk | SF8 Uploads</title>
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

    .summary-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 18px;
      margin-bottom: 24px;
    }

    .summary-card {
      background: var(--clinic-card);
      border: 1px solid var(--clinic-border);
      border-radius: var(--clinic-radius);
      padding: 20px;
      box-shadow: var(--clinic-shadow);
    }

    .summary-label {
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      color: var(--clinic-muted);
      font-weight: 800;
      margin-bottom: 8px;
    }

    .summary-value {
      font-size: 30px;
      font-weight: 900;
      color: var(--clinic-primary);
      margin-bottom: 0;
    }

    .summary-helper {
      font-size: 13px;
      color: var(--clinic-muted);
      margin-top: 4px;
      margin-bottom: 0;
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

    .btn-outline-success {
      border-color: var(--clinic-primary);
      color: var(--clinic-primary);
      font-weight: 800;
      border-radius: 12px;
    }

    .btn-outline-success:hover {
      background: var(--clinic-primary);
      border-color: var(--clinic-primary);
      color: white;
    }

    .small-note {
      font-size: 0.9rem;
      color: var(--clinic-muted);
    }

    .status-dot {
      display: inline-block;
      width: 10px;
      height: 10px;
      background: var(--clinic-secondary);
      border-radius: 50%;
      margin-right: 6px;
      box-shadow: 0 0 0 4px rgba(20,184,166,0.14);
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

    .table-responsive {
      width: 100%;
      overflow-x: auto;
      overflow-y: visible;
      border-radius: 16px;
      border: 1px solid var(--clinic-border);
      background: white;
    }

    .table {
      margin-bottom: 0;
      color: var(--clinic-text);
    }

    .table th {
      background: #e8f7f5;
      color: #24404d;
      font-weight: 900;
      white-space: nowrap;
      border-bottom: 1px solid var(--clinic-border);
      font-size: 14px;
      vertical-align: middle;
    }

    .table td {
      vertical-align: middle;
      color: #263f4a;
      background: white;
      border-color: #e5f0f2;
      font-size: 14px;
    }

    .table tbody tr:hover td {
      background: #f7fcfd;
    }

    .badge {
      border-radius: 999px;
      padding: 7px 10px;
      font-size: 12px;
    }

    .empty-row {
      padding: 28px;
      text-align: center;
      color: var(--clinic-muted);
      background: #f8fcfd;
      border-radius: 14px;
    }

    .ellipsis-btn {
      width: 38px;
      height: 34px;
      border-radius: 12px;
      border: 1px solid var(--clinic-border);
      background: #ffffff;
      color: var(--clinic-primary);
      font-size: 20px;
      font-weight: 900;
      line-height: 1;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .ellipsis-btn:hover,
    .ellipsis-btn:focus {
      background: #ecfeff;
      color: var(--clinic-primary);
      border-color: var(--clinic-secondary);
      box-shadow: 0 0 0 0.2rem rgba(20, 184, 166, 0.12);
    }

    .dropdown-menu {
      background: white;
      border: 1px solid var(--clinic-border);
      border-radius: 14px;
      box-shadow: 0 18px 40px rgba(15, 118, 110, 0.16);
      padding: 8px;
    }

    .dropdown-item {
      color: #24404d;
      border-radius: 10px;
      font-weight: 700;
      font-size: 14px;
      padding: 9px 12px;
    }

    .dropdown-item:hover {
      background: #ecfeff;
      color: var(--clinic-primary);
    }

    .dropdown-item.text-danger:hover {
      background: #fee2e2;
      color: #991b1b !important;
    }

    .modal-content {
      border: none;
      border-radius: 22px;
      box-shadow: 0 18px 42px rgba(15, 118, 110, 0.18);
    }

    .modal-header-danger {
      background: linear-gradient(135deg, #dc2626, #ef4444);
      color: white;
      border-top-left-radius: 22px;
      border-top-right-radius: 22px;
    }

    .modal-header-danger .btn-close {
      filter: brightness(0) invert(1);
    }

    @media (max-width: 992px) {
      .summary-grid {
        grid-template-columns: 1fr 1fr;
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

      .summary-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
<div id="app" class="wrapper">

  <div class="header-box d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div class="header-content d-flex align-items-center">
      <div class="header-icon">📄</div>

      <div>
        <h1 class="fw-bold mb-2">SF8 Uploaded Files</h1>
        <p class="mb-0">
          Preview, approve, reject, or delete SF8 Excel files submitted by teachers.
        </p>
      </div>
    </div>

    <div class="header-actions">
      <a href="nurse-dashboard.php" class="btn btn-back">
        Back to Dashboard
      </a>
    </div>
  </div>

  <div class="summary-grid">
    <div class="summary-card">
      <div class="summary-label">Total Uploads</div>
      <p class="summary-value">{{ uploads.length }}</p>
      <p class="summary-helper">Uploaded SF8 files</p>
    </div>

    <div class="summary-card">
      <div class="summary-label">Pending</div>
      <p class="summary-value">{{ pendingCount }}</p>
      <p class="summary-helper">Waiting for review</p>
    </div>

    <div class="summary-card">
      <div class="summary-label">Approved</div>
      <p class="summary-value">{{ approvedCount }}</p>
      <p class="summary-helper">Saved learner records</p>
    </div>

    <div class="summary-card">
      <div class="summary-label">Rejected</div>
      <p class="summary-value">{{ rejectedCount }}</p>
      <p class="summary-helper">Returned or invalid files</p>
    </div>
  </div>

  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">
    {{ message }}
  </div>

  <div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <div>
        <h4 class="fw-bold mb-1">Uploaded SF8 Files</h4>
        <p class="small-note mb-0">
          <span class="status-dot"></span>
          Auto-refreshing every 5 seconds. Files are listed from the local upload database.
        </p>
      </div>

      <button class="btn btn-outline-success btn-sm" @click="loadUploads">
        Refresh Now
      </button>
    </div>

    <div v-if="loading" class="alert alert-info">
      Loading uploads, please wait...
    </div>

    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead>
          <tr>
            <th>ID</th>
            <th>File Name</th>
            <th>Uploaded By</th>
            <th>Upload Date</th>
            <th>Status</th>
            <th>Reviewed By</th>
            <th>Reviewed Date</th>
            <th>Cloudinary</th>
            <th style="width: 90px; text-align: center;">Action</th>
          </tr>
        </thead>

        <tbody>
          <tr v-if="uploads.length === 0 && !loading">
            <td colspan="9">
              <div class="empty-row">
                No SF8 uploads found.
              </div>
            </td>
          </tr>

          <tr v-for="upload in uploads" :key="upload.upload_id">
            <td>{{ upload.upload_id }}</td>
            <td class="fw-semibold">{{ upload.file_name }}</td>
            <td>{{ upload.uploaded_by_email }}</td>
            <td>{{ upload.upload_date }}</td>

            <td>
              <span class="badge"
                :class="{
                  'bg-warning text-dark': upload.status === 'Pending',
                  'bg-success': upload.status === 'Approved',
                  'bg-danger': upload.status === 'Rejected',
                  'bg-secondary': upload.status === 'Missing in Cloudinary'
                }">
                {{ upload.status }}
              </span>
            </td>

            <td>{{ upload.reviewed_by || "-" }}</td>
            <td>{{ upload.reviewed_date || "-" }}</td>

            <td>
              <span class="badge" :class="upload.cloudinary_url ? 'bg-success' : 'bg-secondary'">
                {{ upload.cloudinary_url ? "Exists" : "No URL" }}
              </span>
            </td>

            <td>
              <div class="dropdown text-center">
                <button class="ellipsis-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  ⋯
                </button>

                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <button class="dropdown-item" type="button" @click="previewUpload(upload.upload_id)">
                      Preview File
                    </button>
                  </li>

                  <li>
                    <a :href="upload.cloudinary_url" target="_blank" class="dropdown-item">
                      Open in Cloudinary
                    </a>
                  </li>

                  <li><hr class="dropdown-divider"></li>

                  <li>
                    <button class="dropdown-item text-danger" type="button" @click="openDeleteModal(upload)">
                      Delete File
                    </button>
                  </li>
                </ul>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <p class="small-note mt-2 mb-0">
      Deleting an upload removes its local upload record and attempts to delete the Cloudinary raw file.
    </p>
  </div>

  <div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header modal-header-danger">
          <h5 class="modal-title fw-bold">Confirm File Deletion</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <p class="mb-2">You are about to delete this uploaded file:</p>

          <div class="alert alert-danger mb-3" v-if="selectedDeleteUpload">
            <strong>{{ selectedDeleteUpload.file_name }}</strong><br>
            Upload ID: {{ selectedDeleteUpload.upload_id }}
          </div>

          <label class="form-label fw-bold">Enter your account password</label>
          <input
            type="password"
            class="form-control"
            v-model="deletePassword"
            placeholder="Password"
            @keyup.enter="deleteUpload"
          >

          <p class="small-note mt-2 mb-0">
            This action cannot be undone.
          </p>
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal" :disabled="deleting">
            Cancel
          </button>

          <button class="btn btn-danger" @click="deleteUpload" :disabled="deleting">
            {{ deleting ? "Deleting..." : "Delete File" }}
          </button>
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
      uploads: [],
      loading: false,
      message: "",
      messageType: "success",
      refreshTimer: null,

      selectedDeleteUpload: null,
      deletePassword: "",
      deleteModalInstance: null,
      deleting: false
    };
  },

  computed: {
    pendingCount() {
      return this.uploads.filter(upload => upload.status === "Pending").length;
    },

    approvedCount() {
      return this.uploads.filter(upload => upload.status === "Approved").length;
    },

    rejectedCount() {
      return this.uploads.filter(upload => upload.status === "Rejected").length;
    }
  },

  mounted() {
    this.loadUploads();

    this.refreshTimer = setInterval(() => {
      this.loadUploads(false);
    }, 5000);
  },

  beforeUnmount() {
    if (this.refreshTimer) {
      clearInterval(this.refreshTimer);
    }
  },

  methods: {
    showMessage(type, text) {
      this.messageType = type;
      this.message = text;

      setTimeout(() => {
        this.message = "";
      }, 4000);
    },

    async loadUploads(showLoading = true) {
      if (showLoading) {
        this.loading = true;
      }

      try {
        const response = await fetch("api/get_sf8_uploads.php?cache_buster=" + Date.now());
        const text = await response.text();

        console.log("SF8 uploads raw response:", text);

        let result;

        try {
          result = JSON.parse(text);
        } catch (jsonError) {
          this.showMessage(
            "error",
            "Uploads API did not return JSON. Check api/get_sf8_uploads.php in browser or Console."
          );
          this.loading = false;
          return;
        }

        if (result.success) {
          this.uploads = result.uploads || [];
        } else {
          this.showMessage("error", result.message || "Failed to load uploads.");
        }

      } catch (error) {
        this.showMessage("error", "Error loading uploads: " + error.message);
      }

      this.loading = false;
    },

    previewUpload(uploadId) {
      window.location.href = "nurse-sf8-preview.php?upload_id=" + uploadId;
    },

    openDeleteModal(upload) {
      this.selectedDeleteUpload = upload;
      this.deletePassword = "";

      this.deleteModalInstance = new bootstrap.Modal(document.getElementById("deleteModal"));
      this.deleteModalInstance.show();
    },

    getActiveUserEmail() {
      return (
        localStorage.getItem("nurse_email") ||
        localStorage.getItem("user_email") ||
        localStorage.getItem("teacher_email") ||
        localStorage.getItem("email") ||
        ""
      );
    },

    async deleteUpload() {
  if (!this.selectedDeleteUpload) return;

  if (!this.deletePassword.trim()) {
    this.showMessage("error", "Please enter your password.");
    return;
  }

  this.deleting = true;

  try {
    const response = await fetch("api/delete_sf8_upload.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        upload_id: this.selectedDeleteUpload.upload_id,
        email: this.getActiveUserEmail(),
        password: this.deletePassword
      })
    });

    const text = await response.text();
    console.log("Delete raw response:", text);

    let result;

    try {
      result = JSON.parse(text);
    } catch (jsonError) {
      this.showMessage("error", "Delete API did not return JSON. Check Console.");
      this.deleting = false;
      return;
    }

    if (result.success) {
      this.showMessage("success", result.message);

      if (this.deleteModalInstance) {
        this.deleteModalInstance.hide();
      }

      this.selectedDeleteUpload = null;
      this.deletePassword = "";
      this.loadUploads();
    } else {
      this.showMessage("error", result.message || "Delete failed.");
    }

  } catch (error) {
    this.showMessage("error", "Error deleting upload: " + error.message);
  }

  this.deleting = false;
}
  }
}).mount("#app");
</script>
</body>
</html>