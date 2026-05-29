<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5, user-scalable=yes" />
  <title>ClinicDesk | Unified User Management</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

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
      --clinic-shadow: 0 12px 32px rgba(15, 118, 110, 0.1);
      --clinic-radius: 22px;
      --transition: all 0.25s ease;
    }

    * {
      box-sizing: border-box;
    }

    body {
      min-height: 100vh;
      margin: 0;
      background: radial-gradient(circle at top left, rgba(242, 245, 245, 0.16), transparent 25%),
                  radial-gradient(circle at top right, rgba(14, 165, 233, 0.12), transparent 25%),
                  linear-gradient(135deg, #eef8fb, #f8fcfd);
      font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
      color: var(--clinic-text);
      line-height: 1.5;
    }

    .main-wrapper {
      max-width: 1500px;
      margin: 24px auto;
      padding: 20px;
    }

    .header-box {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white;
      padding: 30px 34px;
      border-radius: 28px;
      margin-bottom: 26px;
      box-shadow: 0 16px 38px rgba(15, 118, 110, 0.22);
      position: relative;
      overflow: hidden;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 18px;
    }
    .header-box::before {
      content: "";
      position: absolute;
      top: -70px;
      right: -60px;
      width: 230px;
      height: 230px;
      background: rgba(255,255,255,0.12);
      border-radius: 50%;
    }
    .header-box::after {
      content: "";
      position: absolute;
      bottom: -90px;
      left: -60px;
      width: 180px;
      height: 180px;
      background: rgba(255,255,255,0.08);
      border-radius: 50%;
    }
    .header-content {
      display: flex;
      align-items: center;
      gap: 16px;
      position: relative;
      z-index: 2;
    }
    .header-icon {
      width: 58px;
      height: 58px;
      background: rgba(255,255,255,0.18);
      border: 1px solid rgba(255,255,255,0.28);
      border-radius: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
    }
    .header-text h1 {
      font-size: 2.2rem;
      font-weight: 800;
      margin-bottom: 4px;
    }
    .header-text p {
      margin: 0;
      opacity: 0.92;
      font-size: 0.95rem;
    }
    .btn-logout {
      background: white;
      color: var(--clinic-primary);
      border: none;
      border-radius: 14px;
      padding: 11px 22px;
      font-weight: 700;
      box-shadow: 0 8px 18px rgba(0,0,0,0.12);
      transition: var(--transition);
      position: relative;
      z-index: 2;
    }
    .btn-logout:hover {
      background: #ecfeff;
      color: #0f5b55;
    }

    .dashboard-stats {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 18px;
      margin-bottom: 28px;
    }
    .stat-card {
      background: var(--clinic-card);
      border: 1px solid rgba(217, 238, 240, 0.9);
      border-radius: 20px;
      padding: 20px 22px;
      box-shadow: var(--clinic-shadow);
    }
    .stat-label {
      font-size: 0.8rem;
      text-transform: uppercase;
      letter-spacing: 0.7px;
      color: var(--clinic-muted);
      font-weight: 700;
      margin-bottom: 6px;
    }
    .stat-value {
      font-size: 2.1rem;
      font-weight: 800;
      color: var(--clinic-primary);
      margin: 0;
    }
    .stat-helper {
      font-size: 0.8rem;
      color: var(--clinic-muted);
      margin-top: 4px;
    }

    .card {
      background: var(--clinic-card);
      border: 1px solid rgba(217, 238, 240, 0.9);
      border-radius: var(--clinic-radius);
      box-shadow: var(--clinic-shadow);
      height: 100%;
    }

    .btn-green {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white;
      font-weight: 700;
      border: none;
      border-radius: 13px;
      padding: 11px 18px;
      box-shadow: 0 8px 18px rgba(15, 118, 110, 0.2);
      transition: var(--transition);
    }
    .btn-green:hover {
      background: linear-gradient(135deg, #115e59, #0f766e);
      color: white;
    }
    .btn-green:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
    .btn-outline-success {
      border-color: var(--clinic-primary);
      color: var(--clinic-primary);
      font-weight: 700;
      border-radius: 12px;
    }
    .btn-outline-success:hover {
      background: var(--clinic-primary);
      color: white;
    }

    .form-label {
      font-weight: 700;
      color: #24404d;
      font-size: 0.9rem;
      margin-bottom: 4px;
    }
    .form-control, .form-select {
      border-radius: 13px;
      border: 1px solid var(--clinic-border);
      padding: 10px 14px;
      font-size: 0.9rem;
    }
    .form-control:focus, .form-select:focus {
      border-color: var(--clinic-secondary);
      box-shadow: 0 0 0 0.2rem rgba(20, 184, 166, 0.15);
    }
    .is-invalid {
      border-color: #dc2626 !important;
    }
    .is-valid {
      border-color: #16a34a !important;
    }

    .table-responsive {
      border-radius: 18px;
      border: 1px solid var(--clinic-border);
      background: white;
    }
    .table {
      margin-bottom: 0;
      font-size: 0.9rem;
    }
    .table th {
      background: #e8f5f6;
      color: #1e3b44;
      font-weight: 700;
      white-space: nowrap;
      border-bottom: 1px solid var(--clinic-border);
    }
    .table td {
      vertical-align: middle;
      background: white;
    }
    .table tbody tr:hover td {
      background: #f6fcfd;
    }
    .badge {
      border-radius: 30px;
      padding: 6px 12px;
      font-weight: 600;
      font-size: 0.75rem;
    }
    .bg-soft-primary { background: #d4f0fc; color: #0c5e7e; }
    .bg-soft-success { background: #ccf0e0; color: #115e42; }
    .bg-soft-danger { background: #ffe0e0; color: #a12020; }
    .bg-soft-warning { background: #fff3cd; color: #856404; }

    .password-strength-meter {
      height: 8px;
      background: #e9ecef;
      border-radius: 20px;
      margin-top: 6px;
      overflow: hidden;
    }
    .strength-fill {
      height: 100%;
      width: 0%;
      transition: width 0.3s ease, background 0.3s;
      border-radius: 20px;
    }
    .requirement-list {
      font-size: 0.8rem;
      margin-top: 8px;
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      color: #4b5c64;
    }
    .requirement-item {
      display: flex;
      align-items: center;
      gap: 4px;
    }
    .requirement-item i {
      font-size: 0.8rem;
    }
    .text-success { color: #16a34a !important; }
    .text-danger { color: #dc2626 !important; }

    .storage-badge {
      background: #eef2ff;
      color: #1e3a8a;
      border-radius: 20px;
      padding: 3px 12px;
      font-size: 0.7rem;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    .security-note {
      background: #f0fdfa;
      border-left: 5px solid var(--clinic-secondary);
      padding: 12px 14px;
      border-radius: 14px;
      color: #155e75;
      font-size: 0.85rem;
      margin-top: 1.2rem;
    }

    .role-info-box {
      background: #f8fafc;
      border: 1px solid var(--clinic-border);
      border-radius: 14px;
      padding: 12px;
      margin-top: 8px;
    }

    @media (max-width: 992px) {
      .dashboard-stats {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
<div id="app" class="main-wrapper">
  <!-- HEADER -->
  <div class="header-box">
    <div class="header-content">
      <div class="header-icon"><i class="fas fa-user-shield"></i></div>
      <div class="header-text">
        <h1>Unified User Management</h1>
        <p>Manage all accounts (Firebase & Local) · Logged in as <strong>{{ adminName }}</strong></p>
      </div>
    </div>
    <button class="btn btn-logout" @click="logoutAdmin"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
  </div>

  <!-- STATS -->
  <div class="dashboard-stats">
    <div class="stat-card"><div class="stat-label">Total Accounts</div><p class="stat-value">{{ unifiedUsers.length }}</p><p class="stat-helper">Combined Firebase + Local</p></div>
    <div class="stat-card"><div class="stat-label">Firebase</div><p class="stat-value">{{ firebaseCount }}</p><p class="stat-helper">Teacher accounts</p></div>
    <div class="stat-card"><div class="stat-label">Local (XAMPP)</div><p class="stat-value">{{ localCount }}</p><p class="stat-helper">Nurse / Admin / IT</p></div>
  </div>

  <!-- ALERT -->
  <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']" style="border-radius:16px;">
    <i :class="messageType==='success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle'"></i> {{ message }}
  </div>

  <div class="row g-4">
    <!-- LEFT FORM CARD -->
    <div class="col-lg-5">
      <div class="card p-4">
        <h4 class="fw-bold mb-3" style="color: var(--clinic-primary);">
          <i class="fas fa-user-edit me-2"></i>{{ editingUser ? 'Update Account' : 'Create New Account' }}
        </h4>

        <form @submit.prevent="editingUser ? updateAccount() : createAccount()">
          <!-- Role (MOVED TO FIRST POSITION) -->
          <div class="mb-3">
            <label class="form-label">Role <span class="text-danger">*</span></label>
            <select v-model="form.role" class="form-select" required @change="onRoleChange" :disabled="editingUser">
              <option value="" disabled>Select role</option>
              <option value="Teacher">Teacher</option>
              <option value="Clinic Nurse">Clinic Nurse</option>
              <option value="School Admin">School Admin</option>
              <option value="IT Admin">IT Admin </option>
            </select>
            
            <!-- Role restriction info -->
            <div class="role-info-box" v-if="!editingUser">
              <div v-if="form.role === 'Teacher'" class="text-success">
                <i class="fas fa-check-circle me-1"></i> Teachers can be created multiple times.
              </div>
              <div v-else-if="form.role === 'Clinic Nurse'" class="text-warning">
                <i class="fas fa-exclamation-triangle me-1"></i> 
                <span v-if="roleCounts.nurse >= 1">❌ Clinic Nurse already exists! Cannot create another.</span>
                <span v-else>✅ Clinic Nurse slot available (max 1).</span>
              </div>
              <div v-else-if="form.role === 'School Admin'" class="text-warning">
                <i class="fas fa-exclamation-triangle me-1"></i> 
                <span v-if="roleCounts.schoolAdmin >= 1">❌ School Admin already exists! Cannot create another.</span>
                <span v-else>✅ School Admin slot available (max 1).</span>
              </div>
              <div v-else-if="form.role === 'IT Admin'" class="text-warning">
                <i class="fas fa-exclamation-triangle me-1"></i> 
                <span v-if="roleCounts.itAdmin >= 1">❌ IT Admin already exists! Cannot create another.</span>
                <span v-else>✅ IT Admin slot available (max 1).</span>
              </div>
              <div v-else class="text-muted">
                <i class="fas fa-info-circle me-1"></i> Please select a role to continue.
              </div>
            </div>
          </div>

          <!-- Full Name -->
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input v-model="form.full_name" type="text" class="form-control" placeholder="e.g., Maria Santos" required />
          </div>

          <!-- Email -->
          <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input v-model="form.email" type="email" class="form-control" placeholder="user@clinicdesk.com" required />
          </div>

          <!-- Password with strength meter -->
          <div class="mb-3">
            <label class="form-label">
              {{ editingUser ? 'New Password (leave blank to keep)' : 'Password' }}
            </label>
            <div class="input-group">
              <input :type="showPassword ? 'text' : 'password'" v-model="form.password" class="form-control" 
                     :class="{ 'is-valid': form.password && passwordStrength.valid, 'is-invalid': form.password && !passwordStrength.valid }"
                     :placeholder="editingUser ? '••••••••' : 'Strong password'" :required="!editingUser" 
                     @input="evaluatePasswordStrength" />
              <button class="btn btn-outline-secondary" type="button" @click="showPassword = !showPassword" 
                      style="border-color:var(--clinic-border);">
                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
              </button>
            </div>
            <div class="password-strength-meter mt-2" v-if="form.password.length > 0">
              <div class="strength-fill" :style="{ width: passwordStrength.percent + '%', background: passwordStrength.color }"></div>
            </div>
            <div class="requirement-list" v-if="form.password.length > 0">
              <span class="requirement-item"><i :class="passwordStrength.lowercase ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-danger'"></i> a-z</span>
              <span class="requirement-item"><i :class="passwordStrength.uppercase ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-danger'"></i> A-Z</span>
              <span class="requirement-item"><i :class="passwordStrength.digit ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-danger'"></i> 0-9</span>
              <span class="requirement-item"><i :class="passwordStrength.symbol ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-danger'"></i> Symbol</span>
              <span class="requirement-item"><i :class="passwordStrength.lengthValid ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-danger'"></i> ≥12 chars</span>
            </div>
            <small class="text-muted">Minimum 12 characters, mix of upper, lower, digit & symbol.</small>
          </div>

          <!-- Confirm Password -->
          <div class="mb-3" v-if="form.password.length > 0">
            <label class="form-label">Confirm Password</label>
            <div class="position-relative">
              <input :type="showConfirmPassword ? 'text' : 'password'" v-model="form.confirmPassword" class="form-control" 
                     :class="{ 'is-valid': passwordsMatch && form.confirmPassword, 'is-invalid': !passwordsMatch && form.confirmPassword }"
                     placeholder="Re-enter password" />
              <button class="btn btn-sm position-absolute end-0 top-50 translate-middle-y me-1" type="button" 
                      @click="showConfirmPassword = !showConfirmPassword" style="border:none; background:transparent; color:var(--clinic-muted);">
                <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
              </button>
              <span v-if="passwordsMatch && form.confirmPassword" class="text-success position-absolute end-0 top-50 translate-middle-y me-4">
                <i class="fas fa-check-circle"></i>
              </span>
              <span v-if="!passwordsMatch && form.confirmPassword" class="text-danger position-absolute end-0 top-50 translate-middle-y me-4">
                <i class="fas fa-times-circle"></i>
              </span>
            </div>
            <small v-if="!passwordsMatch && form.confirmPassword" class="text-danger">Passwords do not match.</small>
            <small v-if="passwordsMatch && form.confirmPassword" class="text-success">Passwords match.</small>
          </div>

          <!-- Status (edit only) -->
          <div class="mb-3" v-if="editingUser">
            <label class="form-label">Status</label>
            <select v-model="form.status" class="form-select">
              <option value="Active">Active</option>
              <option value="Disabled">Disabled</option>
              <option value="Inactive">Inactive</option>
            </select>
          </div>

          <button class="btn btn-green w-100" :disabled="loading || !isFormValid || !canCreateRole">
            <i class="fas fa-spinner fa-spin" v-if="loading"></i>
            {{ loading ? 'Processing...' : editingUser ? 'Update Account' : 'Create Account' }}
          </button>
          <button v-if="editingUser" type="button" class="btn btn-outline-secondary w-100 mt-2" @click="cancelEdit">
            Cancel
          </button>
        </form>

        
      </div>
    </div>

    <!-- RIGHT UNIFIED TABLE -->
    <div class="col-lg-7">
      <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="fw-bold mb-0" style="color: var(--clinic-primary);"><i class="fas fa-users me-2"></i>All Accounts</h4>
          <button class="btn btn-outline-success btn-sm" @click="refreshAllUsers"><i class="fas fa-sync-alt"></i> Refresh</button>
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>Name / Email</th>
                <th>Role</th>
                <th>Storage</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="unifiedUsers.length === 0">
                <td colspan="5" class="text-center text-muted py-4">No accounts found.</td>
              </tr>
              <tr v-for="user in unifiedUsers" :key="user.uniqueKey">
                <td>
                  <div class="fw-bold">{{ user.full_name || 'No name' }}</div>
                  <small class="text-muted">{{ user.email }}</small>
                </td>
                <td><span class="badge bg-soft-primary">{{ user.role }}</span></td>
                <td>
                  <span class="storage-badge">
                    <i :class="user.storage === 'firebase' ? 'fas fa-cloud' : 'fas fa-database'"></i>
                    {{ user.storage === 'firebase' ? 'Firebase' : 'Local' }}
                  </span>
                </td>
                <td>
                  <span :class="user.status === 'Active' ? 'badge bg-soft-success' : 'badge bg-soft-danger'">
                    {{ user.status || 'Active' }}
                  </span>
                </td>
                <td>
                  <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-warning" @click="editUser(user)"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger" @click="deleteUser(user)"><i class="fas fa-trash"></i></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <p class="small-note mt-2 mb-0"><i class="fas fa-sync-alt me-1"></i> Auto-refresh every 5 seconds. Teachers → Firebase, Others → Local.</p>
      </div>
    </div>
  </div>
</div>

<!-- Vue & Bootstrap -->
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
const { createApp } = Vue;

createApp({
  data() {
    return {
      adminName: "IT Admin",
      firebaseUsers: [],
      localAccounts: [],
      message: "",
      messageType: "success",
      loading: false,
      editingUser: false,
      showPassword: false,
      showConfirmPassword: false,
      form: {
        uid: "",
        account_id: "",
        full_name: "",
        email: "",
        password: "",
        confirmPassword: "",
        role: "",
        storage: "firebase",
        status: "Active"
      },
      passwordStrength: {
        percent: 0,
        color: "#dc2626",
        lowercase: false,
        uppercase: false,
        digit: false,
        symbol: false,
        lengthValid: false,
        valid: false
      },
      firebaseTimer: null
    };
  },

  computed: {
    unifiedUsers() {
      const combined = [];
      
      this.firebaseUsers.forEach(u => {
        combined.push({
          uniqueKey: 'fb-' + u.uid,
          uid: u.uid,
          full_name: u.displayName || 'No name',
          email: u.email,
          role: 'Teacher',
          storage: 'firebase',
          status: u.disabled === 'Active' ? 'Active' : 'Disabled',
          rawStatus: u.disabled,
        });
      });

      this.localAccounts.forEach(acc => {
        combined.push({
          uniqueKey: 'local-' + acc.account_id,
          account_id: acc.account_id,
          full_name: acc.full_name,
          email: acc.email,
          role: acc.role,
          storage: 'local',
          status: acc.status || 'Active',
          rawStatus: acc.status
        });
      });

      return combined;
    },
    firebaseCount() {
      return this.firebaseUsers.length;
    },
    localCount() {
      return this.localAccounts.length;
    },
    passwordsMatch() {
      return this.form.password && this.form.confirmPassword && this.form.password === this.form.confirmPassword;
    },
    // Count existing roles in local accounts only
    roleCounts() {
      const counts = {
        nurse: 0,
        schoolAdmin: 0,
        itAdmin: 0
      };
      
      this.localAccounts.forEach(acc => {
        if (acc.role === 'Clinic Nurse') counts.nurse++;
        if (acc.role === 'School Admin') counts.schoolAdmin++;
        if (acc.role === 'IT Admin') counts.itAdmin++;
      });
      
      return counts;
    },
    // Check if selected role can be created (for non-teacher roles, only 1 allowed)
    canCreateRole() {
      if (this.editingUser) return true; // Editing always allowed
      if (!this.form.role) return false;
      
      if (this.form.role === 'Teacher') return true; // Unlimited teachers
      if (this.form.role === 'Clinic Nurse') return this.roleCounts.nurse < 1;
      if (this.form.role === 'School Admin') return this.roleCounts.schoolAdmin < 1;
      if (this.form.role === 'IT Admin') return this.roleCounts.itAdmin < 1;
      
      return false;
    },
    isFormValid() {
      // Basic required fields
      if (!this.form.full_name || !this.form.email || !this.form.role) return false;
      
      // If password is being set, it must be strong and match confirm
      if (this.form.password.length > 0) {
        return this.passwordStrength.valid && this.passwordsMatch;
      }
      
      // For new accounts, password is required
      if (!this.editingUser && !this.form.password) return false;
      
      return true;
    }
  },

  mounted() {
    this.checkSession();
    this.loadFirebaseUsers();
    this.loadLocalAccounts();
    this.firebaseTimer = setInterval(() => this.loadFirebaseUsers(), 5000);
  },

  beforeUnmount() {
    if (this.firebaseTimer) clearInterval(this.firebaseTimer);
  },

  methods: {
    checkSession() {
      const role = localStorage.getItem("active_role");
      const name = localStorage.getItem("local_full_name");
      if (role !== "IT Admin") {
        window.location.href = "login.php";
        return;
      }
      this.adminName = name || "IT Administrator";
    },

    logoutAdmin() {
      localStorage.clear();
      window.location.href = "login.php";
    },

    showMessage(type, text) {
      this.messageType = type;
      this.message = text;
      setTimeout(() => { this.message = ""; }, 6000);
    },

    onRoleChange() {
      if (this.form.role === 'Teacher') {
        this.form.storage = 'firebase';
      } else {
        this.form.storage = 'local';
      }
    },

    evaluatePasswordStrength() {
      const pwd = this.form.password || "";
      const hasLower = /[a-z]/.test(pwd);
      const hasUpper = /[A-Z]/.test(pwd);
      const hasDigit = /[0-9]/.test(pwd);
      const hasSymbol = /[^a-zA-Z0-9]/.test(pwd);
      const lengthOk = pwd.length >= 12;

      this.passwordStrength.lowercase = hasLower;
      this.passwordStrength.uppercase = hasUpper;
      this.passwordStrength.digit = hasDigit;
      this.passwordStrength.symbol = hasSymbol;
      this.passwordStrength.lengthValid = lengthOk;

      const criteria = [hasLower, hasUpper, hasDigit, hasSymbol, lengthOk];
      const met = criteria.filter(Boolean).length;
      this.passwordStrength.percent = (met / 5) * 100;

      if (met <= 2) this.passwordStrength.color = "#dc2626";
      else if (met <= 3) this.passwordStrength.color = "#f59e0b";
      else if (met === 4) this.passwordStrength.color = "#0ea5e9";
      else this.passwordStrength.color = "#16a34a";

      this.passwordStrength.valid = met === 5;
    },

    resetForm() {
      this.form = {
        uid: "", account_id: "", full_name: "", email: "", password: "", confirmPassword: "",
        role: "", storage: "firebase", status: "Active"
      };
      this.passwordStrength = {
        percent: 0, color: "#dc2626", lowercase: false, uppercase: false,
        digit: false, symbol: false, lengthValid: false, valid: false
      };
      this.editingUser = false;
      this.showPassword = false;
      this.showConfirmPassword = false;
    },

    async createAccount() {
      if (!this.isFormValid) {
        this.showMessage("error", "Please fill all required fields correctly.");
        return;
      }
      
      if (!this.canCreateRole) {
        this.showMessage("error", `Cannot create another ${this.form.role}. Only one is allowed.`);
        return;
      }
      
      this.loading = true;
      const payload = { ...this.form };
      delete payload.confirmPassword;
      payload.security_note = "hash_salt_pepper_applied";

      try {
        const endpoint = this.form.storage === 'firebase' 
          ? 'api/firebase_create_user.php' 
          : 'api/create_local_account.php';
        
        const response = await fetch(endpoint, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload)
        });
        const result = await response.json();
        if (result.success) {
          this.showMessage("success", result.message || "Account created successfully.");
          this.resetForm();
          this.refreshAllUsers();
        } else {
          this.showMessage("error", result.message || "Creation failed.");
        }
      } catch (e) {
        this.showMessage("error", "Network error: " + e.message);
      }
      this.loading = false;
    },

    editUser(user) {
      this.editingUser = true;
      this.form = {
        uid: user.uid || "",
        account_id: user.account_id || "",
        full_name: user.full_name || "",
        email: user.email || "",
        password: "",
        confirmPassword: "",
        role: user.role || "Teacher",
        storage: user.storage || "local",
        status: user.status || user.rawStatus || "Active"
      };
      this.passwordStrength = { percent:0, color:"#dc2626", lowercase:false, uppercase:false, digit:false, symbol:false, lengthValid:false, valid:false };
      this.showPassword = false;
      this.showConfirmPassword = false;
    },

    async updateAccount() {
      if (this.form.password && !this.isFormValid) {
        this.showMessage("error", "Password strength insufficient or passwords don't match.");
        return;
      }
      this.loading = true;
      const payload = { ...this.form };
      delete payload.confirmPassword;
      payload.security_note = "hash_salt_pepper";
      
      try {
        const endpoint = this.form.storage === 'firebase' 
          ? 'api/firebase_update_user.php' 
          : 'api/update_local_account.php';
        
        const response = await fetch(endpoint, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload)
        });
        const result = await response.json();
        if (result.success) {
          this.showMessage("success", "Account updated successfully.");
          this.resetForm();
          this.refreshAllUsers();
        } else {
          this.showMessage("error", result.message || "Update failed.");
        }
      } catch (e) {
        this.showMessage("error", e.message);
      }
      this.loading = false;
    },

    async deleteUser(user) {
      if (!confirm(`Delete ${user.full_name || user.email}? This cannot be undone.`)) return;
      
      try {
        const endpoint = user.storage === 'firebase' 
          ? 'api/firebase_delete_user.php' 
          : 'api/delete_local_account.php';
        
        const body = user.storage === 'firebase' 
          ? JSON.stringify({ uid: user.uid }) 
          : JSON.stringify({ account_id: user.account_id });
        
        const response = await fetch(endpoint, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: body
        });
        const result = await response.json();
        if (result.success) {
          this.showMessage("success", "Deleted successfully.");
          this.refreshAllUsers();
        } else {
          this.showMessage("error", result.message || "Deletion failed.");
        }
      } catch (e) {
        this.showMessage("error", e.message);
      }
    },

    cancelEdit() {
      this.resetForm();
    },

    async loadFirebaseUsers() {
      try {
        const res = await fetch("api/firebase_get_users.php?cache=" + Date.now());
        const data = await res.json();
        if (data.success) this.firebaseUsers = data.users || [];
      } catch (e) { console.warn("Firebase fetch error", e); }
    },

    async loadLocalAccounts() {
      try {
        const res = await fetch("api/get_local_accounts.php");
        const data = await res.json();
        if (data.success) this.localAccounts = data.accounts || [];
      } catch (e) { console.warn("Local fetch error", e); }
    },

    refreshAllUsers() {
      this.loadFirebaseUsers();
      this.loadLocalAccounts();
    }
  }
}).mount("#app");
</script>
</body>
</html>