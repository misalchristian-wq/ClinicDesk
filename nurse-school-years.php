<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ClinicDesk | School Year Settings</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    :root {
      --clinic-primary: #0f766e;
      --clinic-secondary: #14b8a6;
      --clinic-accent: #0ea5e9;
      --clinic-card: rgba(255, 255, 255, 0.96);
      --clinic-border: #d9eef0;
      --clinic-text: #16323f;
      --clinic-muted: #6b7d87;
      --clinic-shadow: 0 12px 32px rgba(15, 118, 110, 0.1);
      --transition: all 0.25s ease;
    }
    * { box-sizing: border-box; }
    body {
      min-height: 100vh; margin: 0;
      background: radial-gradient(circle at top left, rgba(242, 245, 245, 0.16), transparent 25%),
                  radial-gradient(circle at top right, rgba(14, 165, 233, 0.12), transparent 25%),
                  linear-gradient(135deg, #eef8fb, #f8fcfd);
      font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
      color: var(--clinic-text); line-height: 1.5;
    }
    .main-wrapper { max-width: 900px; margin: 24px auto; padding: 20px; }

    /* Header (matches nurse dashboard) */
    .header-box {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white; padding: 30px 34px; border-radius: 28px; margin-bottom: 26px;
      box-shadow: 0 16px 38px rgba(15, 118, 110, 0.22);
      position: relative; overflow: hidden;
      display: flex; justify-content: space-between; align-items: center;
      flex-wrap: wrap; gap: 18px;
    }
    .header-box::before {
      content: ""; position: absolute; top: -70px; right: -60px;
      width: 230px; height: 230px; background: rgba(255,255,255,0.12); border-radius: 50%;
    }
    .header-box::after {
      content: ""; position: absolute; bottom: -90px; left: -60px;
      width: 180px; height: 180px; background: rgba(255,255,255,0.08); border-radius: 50%;
    }
    .header-content { display: flex; align-items: center; gap: 16px; position: relative; z-index: 2; }
    .header-icon {
      width: 58px; height: 58px; background: rgba(255,255,255,0.18);
      border: 1px solid rgba(255,255,255,0.28); border-radius: 18px;
      display: flex; align-items: center; justify-content: center; font-size: 28px;
    }
    .header-text h1 { font-size: 2rem; font-weight: 800; margin-bottom: 4px; }
    .header-text p { margin: 0; opacity: 0.92; font-size: 0.95rem; }
    .header-actions { position: relative; z-index: 2; }

    .btn-back {
      background: white; color: var(--clinic-primary); border: none; border-radius: 14px;
      padding: 11px 22px; font-weight: 700; box-shadow: 0 8px 18px rgba(0,0,0,0.12);
      transition: var(--transition); text-decoration: none; display: inline-flex;
      align-items: center; gap: 8px;
    }
    .btn-back:hover { background: #ecfeff; color: #0f5b55; }

    .card {
      background: var(--clinic-card); border: 1px solid rgba(217, 238, 240, 0.9);
      border-radius: 22px; box-shadow: var(--clinic-shadow); padding: 26px; margin-bottom: 22px;
    }
    .card h4 {
      font-weight: 800; color: var(--clinic-primary); font-size: 1.15rem;
      margin-bottom: 6px; display: flex; align-items: center; gap: 8px;
    }
    .card-sub { color: var(--clinic-muted); font-size: 0.88rem; margin-bottom: 18px; }

    .form-control {
      border-radius: 13px; border: 1px solid var(--clinic-border);
      padding: 10px 14px; font-size: 0.95rem;
    }
    .form-control:focus {
      border-color: var(--clinic-secondary); box-shadow: 0 0 0 0.2rem rgba(20, 184, 166, 0.15);
    }

    .btn-green {
      background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
      color: white; font-weight: 700; border: none; border-radius: 13px;
      padding: 11px 20px; box-shadow: 0 8px 18px rgba(15, 118, 110, 0.2); transition: var(--transition);
    }
    .btn-green:hover { background: linear-gradient(135deg, #115e59, #0f766e); color: white; }
    .btn-green:disabled { opacity: 0.6; cursor: not-allowed; }

    .year-row {
      display: flex; align-items: center; justify-content: space-between;
      padding: 14px 18px; border: 1px solid var(--clinic-border);
      border-radius: 16px; margin-bottom: 12px; background: #fbfefe; transition: var(--transition);
    }
    .year-row:hover { background: #f6fcfd; box-shadow: 0 6px 16px rgba(15,118,110,0.06); }
    .year-row.is-active { border-color: var(--clinic-secondary); background: #f0fdfa; }
    .year-label { font-weight: 800; font-size: 1.1rem; }

    .badge-active {
      background: #ccf0e0; color: #115e42; border-radius: 30px;
      padding: 5px 13px; font-size: 0.72rem; font-weight: 800;
      letter-spacing: 0.5px; margin-left: 10px;
    }

    .btn-soft {
      border-radius: 11px; font-weight: 700; font-size: 0.82rem; padding: 7px 14px;
      border: 1px solid var(--clinic-border); background: #fff; cursor: pointer;
      transition: var(--transition);
    }
    .btn-activate { color: var(--clinic-primary); border-color: var(--clinic-primary); }
    .btn-activate:hover { background: var(--clinic-primary); color: #fff; }
    .btn-del { color: #a12020; border-color: #f3c2c2; margin-left: 6px; }
    .btn-del:hover:not(:disabled) { background: #a12020; color: #fff; }
    .btn-del:disabled { opacity: 0.4; cursor: not-allowed; }

    .alert-msg { border-radius: 13px; padding: 12px 18px; margin-bottom: 18px; font-weight: 600; }
    .alert-ok { background: #ccf0e0; color: #115e42; }
    .alert-err { background: #ffe0e0; color: #a12020; }
    .hint { font-size: 0.82rem; color: var(--clinic-muted); margin-top: 8px; margin-bottom: 0; }
    .empty-note {
      text-align: center; color: var(--clinic-muted); padding: 24px;
      border: 1px dashed var(--clinic-border); border-radius: 14px;
    }

    @media (max-width: 576px) {
      .header-box { padding: 24px; }
      .header-text h1 { font-size: 1.6rem; }
    }
  </style>
</head>
<body>
  <div id="app" class="main-wrapper">

    <div class="header-box">
      <div class="header-content">
        <div class="header-icon">⚙️</div>
        <div class="header-text">
          <h1>School Year Settings</h1>
          <p>Set the valid school years. The active year is the default for SF8 uploads and reports.</p>
        </div>
      </div>
      <div class="header-actions">
        <a href="nurse-dashboard.php" class="btn-back">← Back to Dashboard</a>
      </div>
    </div>

    <div v-if="message" class="alert-msg" :class="messageType === 'success' ? 'alert-ok' : 'alert-err'">
      {{ message }}
    </div>

    <div class="card">
      <h4>➕ Add a School Year</h4>
      <p class="card-sub">Enter a school year to make it available across the system.</p>
      <div class="d-flex gap-2 flex-wrap">
        <input
          v-model="newYear"
          class="form-control"
          style="max-width: 240px;"
          placeholder="e.g. 2025-2026"
          @keyup.enter="addYear" />
        <button class="btn-green" @click="addYear" :disabled="adding">
          {{ adding ? "Adding..." : "Add Year" }}
        </button>
      </div>
      <p class="hint">Format: YYYY-YYYY, second year one after the first (e.g. 2025-2026).</p>
    </div>

    <div class="card">
      <h4>📅 Valid School Years</h4>
      <p class="card-sub">Choose which year is active. Only the active year is used as the default.</p>

      <div v-if="years.length === 0" class="empty-note">
        No school years yet. Add one above to get started.
      </div>

      <div v-for="y in years" :key="y.id" class="year-row" :class="{ 'is-active': y.is_active }">
        <div>
          <span class="year-label">{{ y.year_label }}</span>
          <span v-if="y.is_active" class="badge-active">ACTIVE</span>
        </div>
        <div>
          <button v-if="!y.is_active" class="btn-soft btn-activate" @click="setActive(y.id)">
            Set Active
          </button>
          <button
            class="btn-soft btn-del"
            @click="deleteYear(y)"
            :disabled="y.is_active"
            :title="y.is_active ? 'Cannot delete the active year' : 'Delete'">
            Delete
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
  <script>
    const { createApp } = Vue;
    createApp({
      data() {
        return { years: [], newYear: "", adding: false, message: "", messageType: "success" };
      },
      mounted() {
        const role = localStorage.getItem("active_role");
        if (role && role !== "Clinic Nurse") {
          // Comment out the next line if you want admins to access this page too.
          // window.location.href = "login.php";
        }
        this.loadYears();
      },
      methods: {
        showMessage(type, text) {
          this.messageType = type;
          this.message = text;
          setTimeout(() => { this.message = ""; }, 5000);
        },
        async loadYears() {
          try {
            const res = await fetch("api/get_school_years.php?t=" + Date.now());
            const data = await res.json();
            if (data.success) this.years = data.years || [];
          } catch (e) {
            this.showMessage("error", "Could not load school years.");
          }
        },
        async addYear() {
          const label = (this.newYear || "").trim();
          if (!label) { this.showMessage("error", "Enter a school year."); return; }
          this.adding = true;
          try {
            const res = await fetch("api/add_school_year.php", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ year_label: label })
            });
            const data = await res.json();
            if (data.success) {
              this.showMessage("success", data.message);
              this.newYear = "";
              this.loadYears();
            } else {
              this.showMessage("error", data.message);
            }
          } catch (e) {
            this.showMessage("error", "Network error while adding.");
          }
          this.adding = false;
        },
        async setActive(id) {
          try {
            const res = await fetch("api/set_active_school_year.php", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (data.success) { this.showMessage("success", data.message); this.loadYears(); }
            else this.showMessage("error", data.message);
          } catch (e) {
            this.showMessage("error", "Network error.");
          }
        },
        async deleteYear(y) {
          if (y.is_active) return;
          if (!confirm(`Delete school year ${y.year_label}?`)) return;
          try {
            const res = await fetch("api/delete_school_year.php", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ id: y.id })
            });
            const data = await res.json();
            if (data.success) { this.showMessage("success", data.message); this.loadYears(); }
            else this.showMessage("error", data.message);
          } catch (e) {
            this.showMessage("error", "Network error.");
          }
        }
      }
    }).mount("#app");
  </script>
</body>
</html>