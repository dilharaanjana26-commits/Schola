<?php
$page = $_GET['page'] ?? '';
?>

<div class="sidebar d-flex flex-column p-3">
  <div class="d-flex align-items-center gap-2 mb-3">
    <div class="logo-badge">S</div>
    <div>
      <div class="fw-bold text-white">Schola</div>
      <div class="text-white-50 small">Student Panel</div>
    </div>
  </div>

  <div class="sidebar-nav">
    <div class="text-white-50 small mb-2">STUDENT</div>

    <a class="navlink <?= ($page === 'student_dashboard') ? 'active' : '' ?>" href="index.php?page=student_dashboard">
      <i class="bi bi-grid"></i> Dashboard
    </a>

    <a class="navlink <?= ($page === 'student_content') ? 'active' : '' ?>" href="index.php?page=student_content">
      <i class="bi bi-collection-play"></i> My Content
    </a>

    <a class="navlink <?= ($page === 'student_payment') ? 'active' : '' ?>" href="index.php?page=student_content">
      <i class="bi bi-credit-card"></i> Pay Fees
    </a>
  </div>

  <div class="pt-3">
    <a class="navlink" href="index.php?page=student_logout">
      <i class="bi bi-box-arrow-right"></i> Logout
    </a>
  </div>
</div>

<div class="offcanvas offcanvas-start sidebar-offcanvas d-lg-none" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
  <div class="offcanvas-header border-bottom border-light border-opacity-10">
    <h5 class="offcanvas-title text-white" id="mobileSidebarLabel">Schola Student</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <div class="offcanvas-body p-3">
    <div class="sidebar-nav">
      <div class="text-white-50 small mb-2">STUDENT</div>

      <a class="navlink <?= ($page === 'student_dashboard') ? 'active' : '' ?>" href="index.php?page=student_dashboard" data-bs-dismiss="offcanvas">
        <i class="bi bi-grid"></i> Dashboard
      </a>
      <a class="navlink <?= ($page === 'student_content') ? 'active' : '' ?>" href="index.php?page=student_content" data-bs-dismiss="offcanvas">
        <i class="bi bi-collection-play"></i> My Content
      </a>
      <a class="navlink <?= ($page === 'student_payment') ? 'active' : '' ?>" href="index.php?page=student_content" data-bs-dismiss="offcanvas">
        <i class="bi bi-credit-card"></i> Pay Fees
      </a>

      <div class="pt-3">
        <a class="navlink" href="index.php?page=student_logout" data-bs-dismiss="offcanvas">
          <i class="bi bi-box-arrow-right"></i> Logout
        </a>
      </div>
    </div>
  </div>
</div>
