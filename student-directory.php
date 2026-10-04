<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ClinicDesk | Student Directory</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/student-directory.css" rel="stylesheet">
</head>
<body>
<div id="app" class="directory-shell" v-cloak>
  <header class="directory-hero">
    <div>
      <span class="hero-eyebrow">Clinic Nurse · Student Records</span>
      <h1>Student Directory</h1>
      <p>Find learners, update their profiles, and manage student group information.</p>
    </div>
    <a href="nurse-dashboard.php" class="hero-back">Back to Nurse Dashboard</a>
  </header>

  <div v-if="message" class="alert" :class="messageType === 'success' ? 'alert-success' : 'alert-danger'" role="status">{{ message }}</div>

  <section class="directory-panel" aria-label="Students on record">
    <div class="section-heading">
      <div><h2>Students on record</h2><p>Counts reflect the selected school year. Grades 7–10 are Junior High; Grades 11–12 are Senior High.</p></div>
      <div class="section-actions">
        <button type="button" class="btn clinic-outline" @click="loadRecords" :disabled="loading">{{ loading ? 'Refreshing…' : 'Refresh' }}</button>
        <button type="button" class="btn clinic-primary" @click="openAddModal">Add Student</button>
      </div>
    </div>

    <div class="year-bar">
      <div><label for="directoryYear" class="form-label">School year</label><select id="directoryYear" class="form-select" v-model="schoolYear"><option v-for="year in schoolYearOptions" :key="year" :value="year">{{ year }}</option></select></div>
      <span class="year-note">Roster counts use approved and manually added student records.</span>
    </div>

    <div class="summary-grid" aria-label="Student counts by level">
      <div class="summary-card"><span>Total students</span><strong>{{ yearRecords.length }}</strong><small>Selected school year</small></div>
      <div class="summary-card"><span>Junior High</span><strong>{{ levelCounts.jhs }}</strong><small>Grades 7–10</small></div>
      <div class="summary-card"><span>Senior High</span><strong>{{ levelCounts.shs }}</strong><small>Grades 11–12</small></div>
      <div class="summary-card"><span>Other / unassigned</span><strong>{{ levelCounts.other }}</strong><small>Grade needs review or is outside 7–12</small></div>
    </div>
    <div class="grade-breakdown" aria-label="Student counts by grade">
      <button v-for="grade in [7,8,9,10,11,12]" :key="grade" type="button" class="grade-chip" :class="{'grade-chip-active':gradeFilter===String(grade)}" @click="gradeFilter=gradeFilter===String(grade)?'':String(grade)">
        <span>Grade {{ grade }}</span><strong>{{ gradeCounts[grade] || 0 }}</strong>
      </button>
    </div>
  </section>

  <section class="directory-panel" aria-label="Learner list">
    <div class="section-heading">
      <div><h2>Learners</h2><p>Open a profile to review the complete record or edit student information.</p></div>
      <button type="button" class="btn clinic-outline" @click="openBulkGroups" :disabled="!selectedIds.length">Edit groups ({{ selectedIds.length }})</button>
    </div>
    <div class="filter-grid">
      <div><label for="directorySearch" class="form-label">Search name or LRN</label><input id="directorySearch" v-model.trim="search" type="search" class="form-control" placeholder="Student name or LRN"></div>
      <div><label for="directoryGrade" class="form-label">Grade</label><select id="directoryGrade" v-model="gradeFilter" class="form-select"><option value="">All grades</option><option v-for="grade in [7,8,9,10,11,12]" :key="grade" :value="String(grade)">Grade {{ grade }}</option><option value="other">Other / unassigned</option></select></div>
      <div><label for="directorySection" class="form-label">Section</label><select id="directorySection" v-model="sectionFilter" class="form-select"><option value="">All sections</option><option v-for="section in sectionOptions" :key="section" :value="section">{{ section }}</option></select></div>
    </div>
    <div v-if="selectedIds.length" class="selection-note"><span>{{ selectedIds.length }} learner(s) selected across pages.</span><button type="button" @click="selectedIds=[]">Clear selection</button></div>
    <div v-if="loading" class="directory-empty">Loading student records…</div>
    <div v-else class="directory-table-wrap">
      <table class="table align-middle directory-table" data-pagination="off">
        <thead><tr>
          <th class="select-col"><input type="checkbox" class="form-check-input" aria-label="Select students on this page" :checked="visibleRecords.length > 0 && visibleRecords.every(row=>selectedIds.includes(Number(row.record_id)))" :disabled="!visibleRecords.length" @change="selectPage($event.target.checked)"></th>
          <th class="student-col">Student</th><th>Grade &amp; section</th><th>Sex / age</th><th>Groups</th><th>Profile</th><th class="actions-col">Actions</th>
        </tr></thead>
        <tbody>
          <tr v-if="!visibleRecords.length"><td colspan="7" class="directory-empty">No students match these filters.</td></tr>
          <tr v-for="record in visibleRecords" :key="record.record_id">
            <td class="select-col"><input type="checkbox" class="form-check-input" v-model="selectedIds" :value="Number(record.record_id)" :aria-label="'Select ' + record.learner_name"></td>
            <td class="student-col"><strong>{{ record.learner_name || 'Unnamed learner' }}</strong><small>LRN {{ record.lrn || 'Not set' }}</small></td>
            <td>{{ gradeDisplay(record.grade_level) }}<span v-if="record.section"> · {{ record.section }}</span></td>
            <td>{{ record.sex || '—' }}<span v-if="record.age"> · {{ record.age }}</span></td>
            <td><div class="group-tags"><span v-if="Number(record.is_muslim)" class="group-tag">Muslim</span><span v-if="Number(record.is_pwd)" class="group-tag">PWD</span><span v-if="Number(record.is_ip)" class="group-tag">IP</span><span v-if="!Number(record.is_muslim)&&!Number(record.is_pwd)&&!Number(record.is_ip)" class="muted">—</span></div></td>
            <td><span class="status-pill" :class="record.profile_status==='Provisional'?'status-provisional':''">{{ record.profile_status || 'Complete' }}</span></td>
            <td class="actions-col"><div class="row-actions"><a class="btn clinic-outline btn-sm" :href="profileUrl(record)">View</a><a class="btn clinic-primary btn-sm" :href="profileUrl(record, true)">Edit</a></div></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="directory-pagination">
      <label>Items per page <select v-model.number="pageSize" class="form-select form-select-sm"><option :value="10">10</option><option :value="15">15</option><option :value="20">20</option></select></label>
      <span>{{ rangeText }}</span>
      <div><button type="button" class="btn clinic-outline btn-sm" @click="page--" :disabled="page<=1">Previous</button><button type="button" class="btn clinic-outline btn-sm" @click="page++" :disabled="page>=totalPages">Next</button></div>
    </div>
  </section>

  <div v-if="showBulkGroups" class="directory-overlay" @click.self="closeBulkGroups">
    <div class="directory-modal" role="dialog" aria-modal="true" aria-labelledby="bulkGroupsTitle">
      <div class="modal-head"><h2 id="bulkGroupsTitle">Edit learner groups</h2><button type="button" @click="closeBulkGroups" aria-label="Close">&times;</button></div>
      <div class="modal-body"><div v-if="bulkError" class="alert alert-danger">{{ bulkError }}</div>
        <p>Update {{ selectedIds.length }} selected learner record(s). Choose Yes or No for a group; Leave unchanged preserves each learner’s current value.</p>
        <div class="row g-3">
          <div class="col-md-4"><label for="bulkMuslim" class="form-label">Muslim</label><select id="bulkMuslim" v-model="bulkChanges.is_muslim" class="form-select"><option value="">Leave unchanged</option><option value="yes">Yes</option><option value="no">No</option></select></div>
          <div class="col-md-4"><label for="bulkPwd" class="form-label">Person with disability (PWD)</label><select id="bulkPwd" v-model="bulkChanges.is_pwd" class="form-select"><option value="">Leave unchanged</option><option value="yes">Yes</option><option value="no">No</option></select></div>
          <div class="col-md-4"><label for="bulkIp" class="form-label">Indigenous People (IP)</label><select id="bulkIp" v-model="bulkChanges.is_ip" class="form-select"><option value="">Leave unchanged</option><option value="yes">Yes</option><option value="no">No</option></select></div>
        </div>
      </div>
      <div class="modal-actions"><button type="button" class="btn clinic-outline" @click="closeBulkGroups" :disabled="bulkSaving">Cancel</button><button type="button" class="btn clinic-primary" @click="saveBulkGroups" :disabled="bulkSaving || !Object.values(bulkChanges).some(Boolean)">{{ bulkSaving ? 'Saving…' : 'Save selected learners' }}</button></div>
    </div>
  </div>

  <div v-if="showAddModal" class="directory-overlay" @click.self="closeAddModal">
    <div class="directory-modal directory-modal-wide" role="dialog" aria-modal="true" aria-labelledby="addStudentTitle">
      <div class="modal-head"><h2 id="addStudentTitle">Add Student Manually</h2><button type="button" @click="closeAddModal" aria-label="Close">&times;</button></div>
      <div class="modal-body"><div v-if="addError" class="alert alert-danger">{{ addError }}</div>
        <p>New students are added to the active school year <strong>{{ activeSchoolYear || 'not set' }}</strong>. BMI and height-for-age are calculated when saved.</p>
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">LRN *</label><input v-model.trim="addForm.lrn" class="form-control" placeholder="Learner Reference Number"></div>
          <div class="col-md-6"><label class="form-label">Learner name *</label><input v-model.trim="addForm.learner_name" class="form-control" placeholder="Last, First, M.I."></div>
          <div class="col-md-4"><label class="form-label">Sex *</label><select v-model="addForm.sex" class="form-select"><option value="">Select</option><option>Male</option><option>Female</option></select></div>
          <div class="col-md-4"><label class="form-label">Birthdate</label><input v-model="addForm.birthdate" type="date" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Age (years) *</label><input v-model="addForm.age" type="number" min="2" max="19" class="form-control"></div>
          <div class="col-md-6"><label class="form-label">Weight (kg) *</label><input v-model="addForm.weight_kg" type="number" min="0" step="0.01" class="form-control"></div>
          <div class="col-md-6"><label class="form-label">Height (m) *</label><input v-model="addForm.height_m" type="number" min="0" step="0.001" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Grade level</label><input v-model.trim="addForm.grade_level" class="form-control" placeholder="e.g. 7"></div>
          <div class="col-md-4"><label class="form-label">Section</label><input v-model.trim="addForm.section" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Track / strand</label><input v-model.trim="addForm.track_strand" class="form-control"></div>
          <div class="col-md-6"><label class="form-label">School name</label><input v-model.trim="addForm.school_name" class="form-control"></div>
          <div class="col-md-6"><label class="form-label">School ID</label><input v-model.trim="addForm.school_id" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">District</label><input v-model.trim="addForm.district" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Division</label><input v-model.trim="addForm.division" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Region</label><input v-model.trim="addForm.region" class="form-control"></div>
          <div class="col-12"><label class="form-label">Remarks</label><textarea v-model.trim="addForm.remarks" class="form-control" rows="2"></textarea></div>
        </div>
      </div>
      <div class="modal-actions"><button type="button" class="btn clinic-outline" @click="closeAddModal" :disabled="addSaving">Cancel</button><button type="button" class="btn clinic-primary" @click="submitAddStudent" :disabled="addSaving">{{ addSaving ? 'Saving…' : 'Save Student' }}</button></div>
    </div>
  </div>
</div>
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script src="assets/student-directory.js"></script>
</body>
</html>
