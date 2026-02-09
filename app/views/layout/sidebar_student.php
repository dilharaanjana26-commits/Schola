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

    <!-- NOTE:
      student_payment normally requires batch_id.
      So this menu goes to content hub; student can click Pay Fee from there.
    -->
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
