const { createApp } = Vue;

const emptyStudent = () => ({
  lrn:'', learner_name:'', sex:'', birthdate:'', age:'', weight_kg:'', height_m:'',
  grade_level:'', section:'', track_strand:'', school_name:'', school_id:'',
  district:'', division:'', region:'', remarks:''
});

createApp({
  data() { return {
    records:[], loading:true, message:'', messageType:'success',
    activeSchoolYear:'', schoolYear:'', search:'', gradeFilter:'', sectionFilter:'',
    page:1, pageSize:10, selectedIds:[],
    showBulkGroups:false, bulkChanges:{is_muslim:'',is_pwd:'',is_ip:''}, bulkError:'', bulkSaving:false,
    showAddModal:false, addForm:emptyStudent(), addError:'', addSaving:false
  }; },
  computed: {
    schoolYearOptions() {
      return [...new Set([...this.records.map(row => String(row.school_year || '').trim()), this.activeSchoolYear].filter(Boolean))]
        .sort((a,b) => b.localeCompare(a));
    },
    yearRecords() { return this.records.filter(row => !this.schoolYear || String(row.school_year || '').trim() === this.schoolYear); },
    levelCounts() {
      const counts = {jhs:0, shs:0, other:0};
      this.yearRecords.forEach(row => {
        const grade = this.gradeNumber(row.grade_level);
        if (grade >= 7 && grade <= 10) counts.jhs++;
        else if (grade >= 11 && grade <= 12) counts.shs++;
        else counts.other++;
      });
      return counts;
    },
    gradeCounts() {
      const counts = {7:0,8:0,9:0,10:0,11:0,12:0};
      this.yearRecords.forEach(row => {
        const grade = this.gradeNumber(row.grade_level);
        if (grade >= 7 && grade <= 12) counts[grade]++;
      });
      return counts;
    },
    sectionOptions() {
      return [...new Set(this.yearRecords
        .filter(row => !this.gradeFilter || (this.gradeFilter === 'other'
          ? !this.gradeNumber(row.grade_level) || this.gradeNumber(row.grade_level) < 7 || this.gradeNumber(row.grade_level) > 12
          : this.gradeNumber(row.grade_level) === Number(this.gradeFilter)))
        .map(row => String(row.section || '').trim()).filter(Boolean))].sort((a,b) => a.localeCompare(b));
    },
    filteredRecords() {
      const query = this.search.toLocaleLowerCase();
      return this.yearRecords.filter(row => {
        const grade = this.gradeNumber(row.grade_level);
        const gradeMatches = !this.gradeFilter || (this.gradeFilter === 'other'
          ? !(grade >= 7 && grade <= 12) : grade === Number(this.gradeFilter));
        const sectionMatches = !this.sectionFilter || String(row.section || '').trim() === this.sectionFilter;
        const textMatches = !query || `${row.learner_name || ''} ${row.lrn || ''}`.toLocaleLowerCase().includes(query);
        return gradeMatches && sectionMatches && textMatches;
      });
    },
    totalPages() { return Math.max(1, Math.ceil(this.filteredRecords.length / this.pageSize)); },
    visibleRecords() {
      const start = (this.page - 1) * this.pageSize;
      return this.filteredRecords.slice(start, start + this.pageSize);
    },
    rangeText() {
      const count = this.filteredRecords.length;
      if (!count) return '0 of 0 students';
      return `${(this.page - 1) * this.pageSize + 1}–${Math.min(this.page * this.pageSize, count)} of ${count} students`;
    }
  },
  watch: {
    schoolYear() { this.page=1; this.gradeFilter=''; this.sectionFilter=''; this.selectedIds=[]; },
    gradeFilter() { this.page=1; this.sectionFilter=''; },
    sectionFilter() { this.page=1; },
    search() { this.page=1; },
    pageSize() { this.page=1; }
  },
  mounted() {
    if (localStorage.getItem('active_role') !== 'Clinic Nurse' || !localStorage.getItem('local_account_id')) {
      window.location.href = 'login.php';
      return;
    }
    this.initialize();
  },
  methods: {
    gradeNumber(raw) {
      const match = String(raw ?? '').trim().match(/^(?:grade\s*)?(\d{1,2})$/i);
      return match ? Number(match[1]) : null;
    },
    gradeDisplay(raw) {
      const grade = this.gradeNumber(raw);
      return grade ? `Grade ${grade}` : String(raw || 'Grade not set');
    },
    profileUrl(record, edit=false) {
      return `student-profile.php?record_id=${encodeURIComponent(record.record_id)}&from=directory${edit?'&edit=1':''}`;
    },
    notify(type, text) {
      this.messageType = type;
      this.message = text;
      setTimeout(() => { if (this.message === text) this.message = ''; }, 6000);
    },
    async initialize() {
      await Promise.all([this.loadRecords(), this.loadActiveSchoolYear()]);
      this.schoolYear = this.activeSchoolYear || this.schoolYearOptions[0] || '';
    },
    async loadActiveSchoolYear() {
      try {
        const response = await fetch('api/get_school_years.php?t=' + Date.now());
        const data = await response.json();
        if (data.success) this.activeSchoolYear = String(data.active || '').trim();
      } catch (_) { /* The directory can still use years present in student records. */ }
    },
    async loadRecords() {
      this.loading = true;
      try {
        const response = await fetch('api/get_student_records.php?t=' + Date.now(), {
          headers: {Authorization:'Bearer ' + localStorage.getItem('local_id_token')}
        });
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message || 'Student records could not be loaded.');
        this.records = data.records || [];
        const present = new Set(this.records.map(row => Number(row.record_id)));
        this.selectedIds = this.selectedIds.filter(id => present.has(id));
        if (this.schoolYear && !this.schoolYearOptions.includes(this.schoolYear)) this.schoolYear = this.schoolYearOptions[0] || '';
      } catch (error) { this.notify('error', error.message); }
      finally { this.loading = false; }
    },
    selectPage(checked) {
      const ids = this.visibleRecords.map(row => Number(row.record_id));
      this.selectedIds = checked ? [...new Set([...this.selectedIds, ...ids])]
        : this.selectedIds.filter(id => !ids.includes(id));
    },
    openBulkGroups() {
      if (!this.selectedIds.length) return;
      this.bulkChanges = {is_muslim:'',is_pwd:'',is_ip:''};
      this.bulkError = '';
      this.showBulkGroups = true;
    },
    closeBulkGroups() { if (!this.bulkSaving) this.showBulkGroups = false; },
    async saveBulkGroups() {
      const changes = Object.fromEntries(Object.entries(this.bulkChanges)
        .filter(([,value]) => value !== '').map(([field,value]) => [field,value === 'yes']));
      if (!Object.keys(changes).length) { this.bulkError = 'Choose Yes or No for at least one group.'; return; }
      if (this.selectedIds.length > 100) { this.bulkError = 'Select no more than 100 learners at a time.'; return; }
      this.bulkSaving = true; this.bulkError = '';
      try {
        const response = await fetch('api/bulk_update_student_groups.php', {
          method:'POST', headers:{'Content-Type':'application/json',Authorization:'Bearer ' + localStorage.getItem('local_id_token')},
          body:JSON.stringify({record_ids:this.selectedIds,changes})
        });
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message || 'Could not update learner groups.');
        this.showBulkGroups = false;
        this.selectedIds = [];
        await this.loadRecords();
        this.notify('success', data.message || 'Learner groups updated.');
      } catch (error) { this.bulkError = error.message; }
      finally { this.bulkSaving = false; }
    },
    openAddModal() { this.addForm = emptyStudent(); this.addError = ''; this.showAddModal = true; },
    closeAddModal() { if (!this.addSaving) this.showAddModal = false; },
    async submitAddStudent() {
      const required = {LRN:this.addForm.lrn, 'Learner name':this.addForm.learner_name,
        Sex:this.addForm.sex, Age:this.addForm.age, Weight:this.addForm.weight_kg, Height:this.addForm.height_m};
      const missing = Object.entries(required).filter(([,value]) => value === '' || value === null).map(([name]) => name);
      if (missing.length) { this.addError = 'Please fill: ' + missing.join(', ') + '.'; return; }
      if (!this.activeSchoolYear) { this.addError = 'No active school year is set. Set one in School Year Settings first.'; return; }
      this.addSaving = true; this.addError = '';
      try {
        const response = await fetch('api/add_manual_student.php', {
          method:'POST', headers:{'Content-Type':'application/json',Authorization:'Bearer ' + localStorage.getItem('local_id_token')},
          body:JSON.stringify({...this.addForm,school_year:this.activeSchoolYear})
        });
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message || 'Could not add the student.');
        this.showAddModal = false;
        this.schoolYear = this.activeSchoolYear;
        await this.loadRecords();
        this.notify('success', `Student added: ${data.record?.learner_name || this.addForm.learner_name}.`);
      } catch (error) { this.addError = error.message; }
      finally { this.addSaving = false; }
    }
  }
}).mount('#app');
