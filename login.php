<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>ClinicDesk | Login</title>

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

    * {
      box-sizing: border-box;
    }

    html,
    body {
      width: 100%;
      min-height: 100%;
      margin: 0;
    }

    body {
      min-height: 100vh;
      font-family: Arial, sans-serif;
      color: var(--clinic-text);
      background:
        radial-gradient(circle at top left, rgba(20,184,166,0.16), transparent 25%),
        radial-gradient(circle at top right, rgba(14,165,233,0.12), transparent 25%),
        linear-gradient(135deg, #eef8fb, #f8fcfd);
      overflow-x: hidden;
    }

    .page-wrap {
      min-height: 100vh;
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
    }

    .login-shell {
      width: 100%;
      max-width: 1050px;
      display: grid;
      grid-template-columns: 1.05fr 0.95fr;
      background: var(--clinic-card);
      border: 1px solid var(--clinic-border);
      border-radius: 32px;
      box-shadow: var(--clinic-shadow);
      overflow: hidden;
    }

    .login-hero {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white;
      padding: 42px;
      position: relative;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-height: 610px;
    }

    .login-hero::before {
      content: "";
      position: absolute;
      top: -90px;
      right: -80px;
      width: 260px;
      height: 260px;
      border-radius: 50%;
      background: rgba(255,255,255,0.14);
      filter: blur(3px);
    }

    .login-hero::after {
      content: "";
      position: absolute;
      bottom: -95px;
      left: -70px;
      width: 220px;
      height: 220px;
      border-radius: 50%;
      background: rgba(255,255,255,0.10);
      filter: blur(3px);
    }

    .hero-content,
    .hero-footer {
      position: relative;
      z-index: 2;
    }

    .brand-row {
      display: flex;
      align-items: center;
      gap: 14px;
      margin-bottom: 38px;
    }

    .brand-icon {
      width: 62px;
      height: 62px;
      border-radius: 20px;
      background: rgba(255,255,255,0.18);
      border: 2px solid rgba(255,255,255,0.35);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 30px;
      font-weight: 900;
      box-shadow: 0 0 28px rgba(255,255,255,0.20);
    }

    .brand-name {
      font-size: 29px;
      font-weight: 900;
      line-height: 1;
    }

    .brand-subtitle {
      font-size: 14px;
      opacity: 0.9;
      margin-top: 5px;
    }

    .hero-content h1 {
      font-size: 43px;
      font-weight: 900;
      line-height: 1.15;
      margin-bottom: 18px;
      max-width: 560px;
    }

    .hero-content p {
      font-size: 16px;
      line-height: 1.7;
      opacity: 0.95;
      max-width: 560px;
      margin-bottom: 0;
    }

    .feature-list {
      margin-top: 32px;
      display: grid;
      gap: 14px;
    }

    .feature-item {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 15px;
      font-weight: 600;
    }

    .feature-check {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      background: rgba(255,255,255,0.18);
      border: 1px solid rgba(255,255,255,0.28);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 900;
      flex-shrink: 0;
    }

    .hero-footer {
      font-size: 13px;
      opacity: 0.88;
    }

    .login-panel {
      padding: 42px;
      background: white;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .login-card {
      width: 100%;
      max-width: 430px;
    }

    .mobile-brand {
      display: none;
      align-items: center;
      gap: 12px;
      margin-bottom: 22px;
    }

    .mobile-brand .brand-icon {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white;
      width: 56px;
      height: 56px;
      border-radius: 18px;
    }

    .mobile-brand .brand-name {
      color: var(--clinic-text);
      font-size: 24px;
    }

    .mobile-brand .brand-subtitle {
      color: var(--clinic-muted);
    }

    .login-title {
      font-size: 34px;
      font-weight: 900;
      margin-bottom: 8px;
      color: var(--clinic-primary);
    }

    .login-subtitle {
      color: var(--clinic-muted);
      font-size: 15px;
      line-height: 1.6;
      margin-bottom: 26px;
    }

    .form-label {
      color: #24404d;
      font-size: 13px;
      font-weight: 800;
      letter-spacing: 0.4px;
      text-transform: uppercase;
      margin-bottom: 8px;
    }

    .field-box {
      position: relative;
      margin-bottom: 18px;
    }

    .field-icon {
      position: absolute;
      left: 15px;
      top: 41px;
      color: var(--clinic-primary);
      font-size: 17px;
      line-height: 1;
      z-index: 3;
    }

    .form-control,
    .form-select {
      width: 100%;
      border-radius: 14px;
      border: 1px solid var(--clinic-border);
      background-color: #ffffff;
      color: var(--clinic-text);
      padding: 12px 46px 12px 46px;
      font-size: 14px;
      outline: none;
      min-height: 46px;
    }

    .form-select {
      appearance: none;
      background-image: none;
    }

    .form-select option {
      color: #111827;
      background: white;
    }

    .form-control::placeholder {
      color: #94a3b8;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: var(--clinic-secondary);
      background-color: #ffffff;
      color: var(--clinic-text);
      box-shadow: 0 0 0 0.2rem rgba(20, 184, 166, 0.16);
    }

    .right-icon {
      position: absolute;
      right: 15px;
      top: 41px;
      color: var(--clinic-primary);
      z-index: 3;
      border: none;
      background: transparent;
      font-weight: 800;
      padding: 0;
      cursor: pointer;
      font-size: 12px;
    }

    .eye-button {
      position: absolute;
      right: 13px;
      top: 36px;
      width: 34px;
      height: 34px;
      border: none;
      background: transparent;
      color: var(--clinic-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
      cursor: pointer;
      z-index: 4;
      border-radius: 10px;
    }

    .eye-button:hover {
      background: var(--clinic-light);
      color: var(--clinic-secondary);
    }

    .eye-button svg {
      width: 21px;
      height: 21px;
      stroke: currentColor;
      stroke-width: 2;
      fill: none;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .select-arrow {
      pointer-events: none;
      font-size: 17px;
      top: 41px;
    }

    .role-note {
      background: var(--clinic-light);
      border-left: 5px solid var(--clinic-secondary);
      color: #155e75;
      padding: 11px 13px;
      border-radius: 13px;
      font-size: 13px;
      line-height: 1.45;
      margin-bottom: 18px;
    }

    .forgot-row {
      text-align: right;
      margin-top: -6px;
      margin-bottom: 20px;
    }

    .forgot-row a {
      color: var(--clinic-primary);
      text-decoration: none;
      font-size: 13px;
      font-weight: 800;
    }

    .forgot-row a:hover {
      color: var(--clinic-secondary);
    }

    .btn-login {
      width: 100%;
      border: none;
      border-radius: 15px;
      padding: 13px 16px;
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white;
      font-weight: 900;
      font-size: 16px;
      box-shadow: 0 12px 24px rgba(15, 118, 110, 0.18);
      transition: 0.2s ease;
    }

    .btn-login:hover {
      transform: translateY(-1px);
      box-shadow: 0 14px 30px rgba(15, 118, 110, 0.22);
    }

    .btn-login:disabled {
      opacity: 0.7;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    .divider {
      display: flex;
      align-items: center;
      gap: 12px;
      color: var(--clinic-muted);
      font-size: 11px;
      font-weight: 800;
      text-transform: uppercase;
      margin: 24px 0 14px;
      letter-spacing: 0.5px;
    }

    .divider::before,
    .divider::after {
      content: "";
      height: 1px;
      flex: 1;
      background: var(--clinic-border);
    }

    .security-row {
      display: flex;
      justify-content: center;
      gap: 14px;
      margin-bottom: 12px;
    }

    .security-pill {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: #f8fcfd;
      border: 1px solid var(--clinic-border);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--clinic-primary);
      font-size: 18px;
    }

    .footer-note {
      text-align: center;
      color: var(--clinic-muted);
      font-size: 12px;
      line-height: 1.4;
      margin-bottom: 0;
    }

    .alert {
      border-radius: 14px;
      border: none;
      font-size: 13px;
      margin-bottom: 16px;
      padding: 11px 13px;
      box-shadow: var(--clinic-shadow);
    }

    .alert-danger {
      background: #fee2e2;
      color: #991b1b;
      border: 1px solid #fecaca;
    }

    .alert-success {
      background: #dcfce7;
      color: #166534;
      border: 1px solid #bbf7d0;
    }

    @media (max-width: 930px) {
      .page-wrap {
        padding: 16px;
      }

      .login-shell {
        max-width: 520px;
        grid-template-columns: 1fr;
      }

      .login-hero {
        display: none;
      }

      .login-panel {
        padding: 34px 26px;
      }

      .mobile-brand {
        display: flex;
      }

      .login-title {
        font-size: 30px;
      }
    }

    @media (max-width: 480px) {
      .page-wrap {
        padding: 12px;
      }

      .login-panel {
        padding: 26px 20px;
      }

      .login-title {
        font-size: 28px;
      }

      .form-control,
      .form-select {
        padding-left: 42px;
      }
    }

    @media (max-height: 720px) and (min-width: 931px) {
      .login-hero {
        min-height: 560px;
        padding: 32px;
      }

      .hero-content h1 {
        font-size: 36px;
      }

      .login-panel {
        padding: 32px;
      }

      .field-box {
        margin-bottom: 14px;
      }

      .divider {
        margin: 18px 0 12px;
      }
    }
  </style>
</head>

<body>

<div id="app" class="page-wrap">
  <div class="login-shell">

    <section class="login-hero">
      <div class="hero-content">
        <div class="brand-row">
          <div class="brand-icon">⚕</div>
          <div>
            <div class="brand-name">ClinicDesk</div>
            <div class="brand-subtitle">Student Nutritional Monitoring System</div>
          </div>
        </div>

        <h1>School clinic records and monitoring in one secure system.</h1>

        <p>
          Sign in using your assigned role to access SF8 uploads, learner health records,
          nutritional assessment, prediction results, and recommendation tools.
        </p>

        <div class="feature-list">
          <div class="feature-item">
            <div class="feature-check">✓</div>
            <span>Role-based access for teachers, clinic nurses, school admins, and IT admins</span>
          </div>

          <div class="feature-item">
            <div class="feature-check">✓</div>
            <span>Centralized student health and nutritional monitoring records</span>
          </div>

          <div class="feature-item">
            <div class="feature-check">✓</div>
            <span>Prediction and recommendation support for school clinic decisions</span>
          </div>
        </div>
      </div>

      <div class="hero-footer">
        ClinicDesk · School Clinic Health Monitoring
      </div>
    </section>

    <section class="login-panel">
      <div class="login-card">

        <div class="mobile-brand">
          <div class="brand-icon">⚕</div>
          <div>
            <div class="brand-name">ClinicDesk</div>
            <div class="brand-subtitle">Student Nutritional Monitoring</div>
          </div>
        </div>

        <h1 class="login-title">Welcome Back</h1>
        <p class="login-subtitle">
          Select your role and sign in using your assigned ClinicDesk account.
        </p>

        <div v-if="message" :class="['alert', messageType === 'success' ? 'alert-success' : 'alert-danger']">
          {{ message }}
        </div>

        <form @submit.prevent="loginUser">

          <div class="field-box">
            <label class="form-label">Login Role</label>
            <span class="field-icon">👥</span>

            <select v-model="role" class="form-select" required>
              <option value="">Select your role</option>
              <option value="Teacher">Teacher</option>
              <option value="Clinic Nurse">Clinic Nurse</option>
              <option value="School Admin">School Admin</option>
              <option value="IT Admin">IT Admin</option>
            </select>

            <span class="right-icon select-arrow">⌄</span>
          </div>

         

          <div class="field-box">
            <label class="form-label">Email Address</label>
            <span class="field-icon">✉</span>

            <input
              v-model="email"
              type="email"
              class="form-control"
              placeholder="Enter your email"
              autocomplete="email"
              required
            >

            <span class="right-icon">●</span>
          </div>

          <div class="field-box">
            <label class="form-label">Password</label>
            <span class="field-icon">🔒</span>

            <input
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              class="form-control"
              placeholder="Enter your password"
              autocomplete="current-password"
              required
            >

            <button type="button" class="eye-button" @click="showPassword = !showPassword" :aria-label="showPassword ? 'Hide password' : 'Show password'">
              <svg v-if="!showPassword" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>

              <svg v-else viewBox="0 0 24 24" aria-hidden="true">
                <path d="M3 3l18 18"></path>
                <path d="M10.6 10.6A3 3 0 0 0 13.4 13.4"></path>
                <path d="M9.9 4.3A10.5 10.5 0 0 1 12 4c6.5 0 10 8 10 8a18.5 18.5 0 0 1-4.1 5.1"></path>
                <path d="M6.1 6.1C3.5 7.9 2 12 2 12s3.5 8 10 8a10.6 10.6 0 0 0 4.2-.9"></path>
              </svg>
            </button>
          </div>

          <div class="forgot-row">
            <a href="#" @click.prevent="showMessage('error', 'Please contact the IT Admin to reset your password.')">
              Forgot Password?
            </a>
          </div>

          <button type="submit" class="btn-login" :disabled="loading">
            {{ loading ? "Signing in..." : "Sign In" }}
          </button>

        </form>

        <div class="divider">Secure Login</div>

        <div class="security-row">
          <div class="security-pill">🛡</div>
          <div class="security-pill">♡</div>
        </div>

        <p class="footer-note">
          Secure access for teachers, clinic nurses, school admins, and IT admins.
        </p>

      </div>
    </section>

  </div>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<script>
const { createApp } = Vue;

createApp({
  data() {
    return {
      firebaseApiKey: "AIzaSyBqPdtJJhCkvbCm82QjNbhPjerbv0Mjqjc",
      role: "",
      email: "",
      password: "",
      showPassword: false,
      loading: false,
      message: "",
      messageType: "success"
    };
  },

  mounted() {
    const activeRole = localStorage.getItem("active_role");

    if (activeRole === "Teacher" && localStorage.getItem("teacher_id_token")) {
      window.location.href = "teacher-dashboard.php";
    }

    if (activeRole === "Clinic Nurse" && localStorage.getItem("local_account_id")) {
      window.location.href = "nurse-dashboard.php";
    }

    if (activeRole === "School Admin" && localStorage.getItem("local_account_id")) {
      window.location.href = "school-admin-dashboard.php";
    }

    if (activeRole === "IT Admin" && localStorage.getItem("local_account_id")) {
      window.location.href = "user-management.php";
    }
  },

  methods: {
    showMessage(type, text) {
      this.messageType = type;
      this.message = text;
    },

    async loginUser() {
      this.message = "";

      if (!this.role) {
        this.showMessage("error", "Please select a role.");
        return;
      }

      if (!this.email || !this.password) {
        this.showMessage("error", "Please enter your email and password.");
        return;
      }

      if (this.role === "Teacher") {
        await this.loginTeacherFirebase();
      } else {
        await this.loginLocalAccount();
      }
    },

    async loginTeacherFirebase() {
      this.loading = true;

      try {
        const url = `https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key=${this.firebaseApiKey}`;

        const response = await fetch(url, {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            email: this.email,
            password: this.password,
            returnSecureToken: true
          })
        });

        const result = await response.json();

        if (result.error) {
          let errorMessage = "Login failed. Please check your email and password.";

          if (result.error.message === "EMAIL_NOT_FOUND") {
            errorMessage = "No teacher account found with this email.";
          } else if (result.error.message === "INVALID_PASSWORD") {
            errorMessage = "Incorrect password.";
          } else if (result.error.message === "USER_DISABLED") {
            errorMessage = "This teacher account has been disabled.";
          } else if (result.error.message === "INVALID_LOGIN_CREDENTIALS") {
            errorMessage = "Invalid login credentials.";
          }

          this.showMessage("error", errorMessage);
          this.loading = false;
          return;
        }

        this.clearAllSessions();

        localStorage.setItem("active_role", "Teacher");
        localStorage.setItem("teacher_uid", result.localId);
        localStorage.setItem("teacher_email", result.email);
        localStorage.setItem("teacher_id_token", result.idToken);
        localStorage.setItem("teacher_refresh_token", result.refreshToken);
        localStorage.setItem("teacher_login_time", new Date().toISOString());

        this.showMessage("success", "Teacher login successful. Redirecting...");

        setTimeout(() => {
          window.location.href = "teacher-dashboard.php";
        }, 800);

      } catch (error) {
        this.showMessage("error", "Error: " + error.message);
      }

      this.loading = false;
    },

    async loginLocalAccount() {
      this.loading = true;

      try {
        const response = await fetch("api/local_login.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            email: this.email,
            password: this.password,
            role: this.role
          })
        });

        const text = await response.text();
        let result;

        try {
          result = JSON.parse(text);
        } catch (jsonError) {
          this.showMessage("error", "Login API did not return JSON. Check api/local_login.php.");
          this.loading = false;
          return;
        }

        if (!result.success) {
          this.showMessage("error", result.message);
          this.loading = false;
          return;
        }

        this.clearAllSessions();

        localStorage.setItem("active_role", result.user.role);
        localStorage.setItem("local_account_id", result.user.account_id);
        localStorage.setItem("local_full_name", result.user.full_name);
        localStorage.setItem("local_email", result.user.email);
        localStorage.setItem("local_role", result.user.role);
        localStorage.setItem("local_login_time", new Date().toISOString());

        this.showMessage("success", "Login successful. Redirecting...");

        setTimeout(() => {
          if (result.user.role === "Clinic Nurse") {
            window.location.href = "nurse-dashboard.php";
          } else if (result.user.role === "School Admin") {
            window.location.href = "school-admin-dashboard.php";
          } else if (result.user.role === "IT Admin") {
            window.location.href = "user-management.php";
          }
        }, 800);

      } catch (error) {
        this.showMessage("error", "Error: " + error.message);
      }

      this.loading = false;
    },

    clearAllSessions() {
      localStorage.removeItem("active_role");

      localStorage.removeItem("teacher_uid");
      localStorage.removeItem("teacher_email");
      localStorage.removeItem("teacher_id_token");
      localStorage.removeItem("teacher_refresh_token");
      localStorage.removeItem("teacher_login_time");

      localStorage.removeItem("local_account_id");
      localStorage.removeItem("local_full_name");
      localStorage.removeItem("local_email");
      localStorage.removeItem("local_role");
      localStorage.removeItem("local_login_time");
    }
  }
}).mount("#app");
</script>

</body>
</html>