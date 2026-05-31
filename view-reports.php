<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ClinicDesk | Generated Reports</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    :root {
      --clinic-primary: #0f766e;
      --clinic-secondary: #14b8a6;
      --clinic-soft: #ecfeff;
      --clinic-border: #d9eef0;
      --clinic-text: #16323f;
      --clinic-muted: #6b7d87;
    }
    body {
      background: linear-gradient(135deg, #f0fdfa 0%, #e6f7f5 100%);
      font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
      min-height: 100vh;
    }
    .page-header {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      border-radius: 0 0 30px 30px;
      padding: 2rem 1.5rem;
      margin-bottom: 2rem;
      color: white;
      box-shadow: 0 10px 30px rgba(15, 118, 110, 0.2);
    }
    .page-header h1 {
      font-weight: 800;
      font-size: 2rem;
      margin: 0;
      letter-spacing: -0.5px;
    }
    .page-header p {
      opacity: 0.9;
      margin: 0.5rem 0 0;
    }
    .btn-back {
      background: rgba(255,255,255,0.2);
      border: 1px solid rgba(255,255,255,0.3);
      color: white;
      border-radius: 30px;
      padding: 8px 20px;
      font-weight: 600;
      transition: 0.2s;
    }
    .btn-back:hover {
      background: rgba(255,255,255,0.35);
      color: white;
      transform: translateY(-2px);
    }
    .report-card {
      background: white;
      border-radius: 24px;
      border: none;
      box-shadow: 0 8px 25px rgba(0,0,0,0.05);
      transition: all 0.3s ease;
      overflow: hidden;
      height: 100%;
    }
    .report-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 20px 35px rgba(15, 118, 110, 0.12);
    }
    .card-header-icon {
      background: var(--clinic-soft);
      width: 60px;
      height: 60px;
      border-radius: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 1rem;
    }
    .card-header-icon i {
      font-size: 28px;
      color: var(--clinic-primary);
    }
    .report-card .card-title {
      font-weight: 800;
      color: var(--clinic-text);
      font-size: 1.3rem;
      margin-bottom: 0.5rem;
    }
    .report-meta {
      font-size: 0.85rem;
      color: var(--clinic-muted);
      margin-bottom: 1rem;
      border-bottom: 1px dashed #eef2f0;
      padding-bottom: 0.75rem;
    }
    .btn-view {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      border: none;
      border-radius: 40px;
      padding: 10px;
      font-weight: 700;
      transition: 0.2s;
    }
    .btn-view:hover {
      transform: scale(1.02);
      filter: brightness(1.02);
    }
    .btn-outline-download {
      border: 1px solid var(--clinic-border);
      background: white;
      color: var(--clinic-primary);
      border-radius: 40px;
      padding: 10px;
      font-weight: 600;
      transition: 0.2s;
    }
    .btn-outline-download:hover {
      background: var(--clinic-soft);
      border-color: var(--clinic-secondary);
    }
    .modal-content {
      border: none;
      border-radius: 32px;
      overflow: hidden;
    }
    .modal-header {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      border-bottom: none;
      padding: 1.2rem 1.5rem;
    }
    .modal-header h5 {
      font-weight: 800;
      letter-spacing: -0.3px;
    }
    .report-viewer {
      background: #fefefe;
      padding: 20px;
      max-height: 70vh;
      overflow-y: auto;
    }
    .loading-spinner {
      text-align: center;
      padding: 50px;
      background: #fafafa;
    }
    .footer-note {
      text-align: center;
      margin-top: 2rem;
      color: var(--clinic-muted);
      font-size: 0.8rem;
    }
    @media (max-width: 768px) {
      .page-header { padding: 1.5rem; }
      .page-header h1 { font-size: 1.5rem; }
    }
  </style>
</head>
<body>
<div id="app">
  <div class="page-header">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
          <h1><i class="fas fa-file-alt me-2"></i>Generated Reports</h1>
          <p>Consolidated school health reports from the clinic nurse</p>
        </div>
        <a href="dashboard-redirect.php" class="btn btn-back"><i class="fas fa-arrow-left me-1"></i> Back to Dashboard</a>
      </div>
    </div>
  </div>

  <div class="container">
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
      <p class="mt-2 text-muted">Loading reports...</p>
    </div>
    <div v-else-if="reports.length === 0" class="alert alert-info text-center py-4 rounded-4">
      <i class="fas fa-info-circle me-2"></i> No reports have been generated yet.
    </div>
    <div v-else class="row g-4">
      <div class="col-md-6 col-lg-4" v-for="rep in reports" :key="rep.report_id">
        <div class="report-card p-4">
          <div class="card-header-icon">
            <i class="fas fa-chalkboard-user"></i>
          </div>
          <div class="card-title">{{ rep.school_year }}</div>
          <div class="report-meta">
            <i class="fas fa-user-check me-1"></i> {{ rep.generated_by }}<br>
            <i class="fas fa-calendar-alt me-1"></i> {{ formatDate(rep.generated_at) }}
          </div>
          <div class="d-grid gap-2">
            <button class="btn btn-view text-white" @click="viewReport(rep.cloudinary_url)">
              <i class="fas fa-eye me-2"></i> View Report
            </button>
            <a :href="rep.cloudinary_url" download class="btn btn-outline-download">
              <i class="fas fa-download me-2"></i> Download File
            </a>
          </div>
        </div>
      </div>
    </div>
    <div class="footer-note">
      <i class="fas fa-cloud-upload-alt me-1"></i> Reports are securely stored in the cloud
    </div>
  </div>

  <!-- Modal Preview -->
  <div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title text-white"><i class="fas fa-file-contract me-2"></i>Consolidated School Report</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-0">
          <div v-if="reportLoading" class="loading-spinner">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
            <p class="mt-2">Fetching report content...</p>
          </div>
          <div v-else class="report-viewer" v-html="reportHtml"></div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Close</button>
          <a :href="currentReportUrl" download class="btn btn-primary"><i class="fas fa-download me-1"></i>Download Report</a>
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
      reports: [],
      loading: false,
      currentReportUrl: '',
      reportHtml: '',
      reportLoading: false,
      modal: null
    };
  },
  mounted() {
    this.loadReports();
    this.modal = new bootstrap.Modal(document.getElementById('reportModal'));
  },
  methods: {
    formatDate(dateStr) {
      if (!dateStr) return '';
      const d = new Date(dateStr);
      return d.toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
    },
    async loadReports() {
      this.loading = true;
      try {
        const res = await fetch('api/get_generated_reports.php');
        const data = await res.json();
        if (data.success) this.reports = data.reports;
        else alert(data.message);
      } catch(e) { alert('Error loading reports'); }
      this.loading = false;
    },
    async viewReport(url) {
      this.currentReportUrl = url;
      this.reportHtml = '';
      this.reportLoading = true;
      this.modal.show();
      try {
        const response = await fetch(url);
        const html = await response.text();
        // Optional: inject base tag to fix relative paths if needed
        this.reportHtml = html;
      } catch(e) {
        this.reportHtml = '<div class="alert alert-danger m-3">Failed to load report content. You can download it instead.</div>';
      } finally {
        this.reportLoading = false;
      }
    }
  }
}).mount("#app");
</script>
</body>
</html>