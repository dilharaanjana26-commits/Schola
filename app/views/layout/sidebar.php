<div class="sidebar d-flex flex-column p-3">
  <div class="d-flex align-items-center gap-2 mb-3">
    <div class="logo-badge">S</div>
    <div>
      <div class="fw-bold text-white">Schola</div>
      <div class="text-white-50 small">Admin Panel</div>
    </div>
  </div>

  <div class="text-white-50 small mb-2">ADMINISTRATION</div>

  <a class="navlink <?= (($_GET['page'] ?? '') === 'admin_dashboard') ? 'active' : '' ?>" href="index.php?page=admin_dashboard">
    <i class="bi bi-grid"></i> Dashboard
  </a>

  <a class="navlink <?= (($_GET['page'] ?? '') === 'admin_teachers') ? 'active' : '' ?>" href="index.php?page=admin_teachers">
    <i class="bi bi-person-badge"></i> Teachers
  </a>

  <a class="navlink <?= (($_GET['page'] ?? '') === 'admin_students') ? 'active' : '' ?>" href="index.php?page=admin_students">
    <i class="bi bi-people"></i> Students
  </a>
    
  <a class="navlink <?= (($_GET['page'] ?? '') === 'admin_enrollments') ? 'active' : '' ?>" href="index.php?page=admin_enrollments">
  	<i class="bi bi-person-check"></i> Enrollments
  </a>

  <a class="navlink <?= (($_GET['page'] ?? '') === 'admin_batches') ? 'active' : '' ?>" href="index.php?page=admin_batches">
    <i class="bi bi-collection"></i> Batches
  </a>

  <a class="navlink <?= (($_GET['page'] ?? '') === 'admin_teacher_payments') ? 'active' : '' ?>" href="index.php?page=admin_teacher_payments">
    <i class="bi bi-cash-stack"></i> Teacher Payments
  </a>

  <a class="navlink <?= (($_GET['page'] ?? '') === 'admin_student_payments') ? 'active' : '' ?>" href="index.php?page=admin_student_payments">
    <i class="bi bi-credit-card"></i> Student Payments
  </a>
    
  <a class="navlink <?= (($_GET['page'] ?? '') === 'admin_user_approvals') ? 'active' : '' ?>" href="index.php?page=admin_user_approvals">
    <i class="bi bi-person-check"></i> User Approvals
  </a>


  <a class="navlink <?= (($_GET['page'] ?? '') === 'admin_class_schedule') ? 'active' : '' ?>" href="index.php?page=admin_class_schedule">
    <i class="bi bi-calendar-event"></i> Class Schedule
  </a>
    
  <a class="navlink <?= (($_GET['page'] ?? '') === 'admin_post_approvals') ? 'active' : '' ?>" href="index.php?page=admin_post_approvals">
     <i class="bi bi-megaphone"></i> Post Approvals
  </a>

  <a class="navlink <?= (($_GET['page'] ?? '') === 'admin_reports') ? 'active' : '' ?>" href="index.php?page=admin_reports">
    <i class="bi bi-bar-chart"></i> Reports
  </a>

  <div class="mt-auto pt-3">
    <a class="navlink" href="index.php?page=admin_logout">
      <i class="bi bi-box-arrow-right"></i> Logout
    </a>
  </div>
</div>
