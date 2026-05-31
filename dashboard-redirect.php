<?php
// dashboard-redirect.php – reads localStorage via JavaScript and redirects
// We'll handle redirect in JavaScript inside the page, but easier: just provide links in the view-reports page.
// Actually we can just let the user use the "Back" button.
// For simplicity, create a page that uses localStorage to redirect.
?>
<script>
const role = localStorage.getItem("active_role");
if (role === "Teacher") window.location.href = "teacher-dashboard.php";
else if (role === "Clinic Nurse") window.location.href = "nurse-dashboard.php";
else if (role === "School Admin") window.location.href = "school-admin-dashboard.php";
else window.location.href = "login.php";
</script>