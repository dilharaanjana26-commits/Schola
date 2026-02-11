<?php
$page = $_GET['page'] ?? '';
?>

<div class="sidebar d-flex flex-column p-3">
  <div class="d-flex align-items-center gap-2 mb-3">
    <div class="logo-badge">S</div>
    <div>
      <div class="fw-bold text-white">Schola</div>
      <div class="text-white-50 small">Admin Panel</div>
    </div>
  </div>

  <div class="sidebar-nav">
    <div class="text-white-50 small mb-2">ADMINISTRATION</div>

    <a class="navlink <?= ($page === 'admin_dashboard') ? 'active' : '' ?>" href="index.php?page=admin_dashboard">
      <i class="bi bi-grid"></i> Dashboard
    </a>

    <a class="navlink <?= ($page === 'admin_teachers') ? 'active' : '' ?>" href="index.php?page=admin_teachers">
      <i class="bi bi-person-badge"></i> Teachers
    </a>

    <a class="navlink <?= ($page === 'admin_students') ? 'active' : '' ?>" href="index.php?page=admin_students">
      <i class="bi bi-people"></i> Students
    </a>

    <a class="navlink <?= ($page === 'admin_enrollments') ? 'active' : '' ?>" href="index.php?page=admin_enrollments">
      <i class="bi bi-person-check"></i> Enrollments
    </a>

    <a class="navlink <?= ($page === 'admin_batches') ? 'active' : '' ?>" href="index.php?page=admin_batches">
      <i class="bi bi-collection"></i> Batches
    </a>

    <a class="navlink <?= ($page === 'admin_teacher_payments') ? 'active' : '' ?>" href="index.php?page=admin_teacher_payments">
      <i class="bi bi-cash-stack"></i> Teacher Payments
    </a>

    <a class="navlink <?= ($page === 'admin_student_payments') ? 'active' : '' ?>" href="index.php?page=admin_student_payments">
      <i class="bi bi-credit-card"></i> Student Payments
    </a>

    <a class="navlink <?= ($page === 'admin_user_approvals') ? 'active' : '' ?>" href="index.php?page=admin_user_approvals">
      <i class="bi bi-person-check"></i> User Approvals
    </a>

    <a class="navlink <?= ($page === 'admin_class_schedule') ? 'active' : '' ?>" href="index.php?page=admin_class_schedule">
      <i class="bi bi-calendar-event"></i> Class Schedule
    </a>

    <a class="navlink <?= ($page === 'admin_post_approvals') ? 'active' : '' ?>" href="index.php?page=admin_post_approvals">
      <i class="bi bi-megaphone"></i> Post Approvals
    </a>

    <a class="navlink <?= ($page === 'admin_reports') ? 'active' : '' ?>" href="index.php?page=admin_reports">
      <i class="bi bi-bar-chart"></i> Reports
    </a>
  </div>

  <div class="pt-3">
    <a class="navlink" href="index.php?page=admin_logout">
      <i class="bi bi-box-arrow-right"></i> Logout
    </a>
  </div>
</div>

<div class="offcanvas offcanvas-start sidebar-offcanvas d-md-none" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
  <div class="offcanvas-header border-bottom border-light border-opacity-10">
    <h5 class="offcanvas-title text-white" id="mobileSidebarLabel">Schola Admin</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <div class="offcanvas-body p-3">
    <div class="sidebar-nav">
      <div class="text-white-50 small mb-2">ADMINISTRATION</div>

      <a class="navlink <?= ($page === 'admin_dashboard') ? 'active' : '' ?>" href="index.php?page=admin_dashboard" data-bs-dismiss="offcanvas">
        <i class="bi bi-grid"></i> Dashboard
      </a>
      <a class="navlink <?= ($page === 'admin_teachers') ? 'active' : '' ?>" href="index.php?page=admin_teachers" data-bs-dismiss="offcanvas">
        <i class="bi bi-person-badge"></i> Teachers
      </a>
      <a class="navlink <?= ($page === 'admin_students') ? 'active' : '' ?>" href="index.php?page=admin_students" data-bs-dismiss="offcanvas">
        <i class="bi bi-people"></i> Students
      </a>
      <a class="navlink <?= ($page === 'admin_enrollments') ? 'active' : '' ?>" href="index.php?page=admin_enrollments" data-bs-dismiss="offcanvas">
        <i class="bi bi-person-check"></i> Enrollments
      </a>
      <a class="navlink <?= ($page === 'admin_batches') ? 'active' : '' ?>" href="index.php?page=admin_batches" data-bs-dismiss="offcanvas">
        <i class="bi bi-collection"></i> Batches
      </a>
      <a class="navlink <?= ($page === 'admin_teacher_payments') ? 'active' : '' ?>" href="index.php?page=admin_teacher_payments" data-bs-dismiss="offcanvas">
        <i class="bi bi-cash-stack"></i> Teacher Payments
      </a>
      <a class="navlink <?= ($page === 'admin_student_payments') ? 'active' : '' ?>" href="index.php?page=admin_student_payments" data-bs-dismiss="offcanvas">
        <i class="bi bi-credit-card"></i> Student Payments
      </a>
      <a class="navlink <?= ($page === 'admin_user_approvals') ? 'active' : '' ?>" href="index.php?page=admin_user_approvals" data-bs-dismiss="offcanvas">
        <i class="bi bi-person-check"></i> User Approvals
      </a>
      <a class="navlink <?= ($page === 'admin_class_schedule') ? 'active' : '' ?>" href="index.php?page=admin_class_schedule" data-bs-dismiss="offcanvas">
        <i class="bi bi-calendar-event"></i> Class Schedule
      </a>
      <a class="navlink <?= ($page === 'admin_post_approvals') ? 'active' : '' ?>" href="index.php?page=admin_post_approvals" data-bs-dismiss="offcanvas">
        <i class="bi bi-megaphone"></i> Post Approvals
      </a>
      <a class="navlink <?= ($page === 'admin_reports') ? 'active' : '' ?>" href="index.php?page=admin_reports" data-bs-dismiss="offcanvas">
        <i class="bi bi-bar-chart"></i> Reports
      </a>

      <div class="pt-3">
        <a class="navlink" href="index.php?page=admin_logout" data-bs-dismiss="offcanvas">
          <i class="bi bi-box-arrow-right"></i> Logout
        </a>
      </div>
    </div>
  </div>
</div>
