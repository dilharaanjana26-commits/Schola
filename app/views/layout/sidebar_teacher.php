<?php
$page = $_GET['page'] ?? '';
?>

<div class="sidebar d-flex flex-column p-3">
  <div class="d-flex align-items-center gap-2 mb-3">
    <div class="logo-badge">S</div>
    <div>
      <div class="fw-bold text-white">Schola</div>
      <div class="text-white-50 small">Teacher Panel</div>
    </div>
  </div>

  <div class="sidebar-nav">
    <div class="text-white-50 small mb-2">TEACHER</div>

    <a class="navlink <?= ($page === 'teacher_dashboard') ? 'active' : '' ?>" href="index.php?page=teacher_dashboard">
      <i class="bi bi-grid"></i> Dashboard
    </a>

    <a class="navlink <?= ($page === 'teacher_subscription') ? 'active' : '' ?>" href="index.php?page=teacher_subscription">
      <i class="bi bi-cash-stack"></i> Subscription
    </a>

    <a class="navlink <?= ($page === 'teacher_content') ? 'active' : '' ?>" href="index.php?page=teacher_content">
      <i class="bi bi-folder-plus"></i> Content Upload
    </a>

    <a class="navlink <?= ($page === 'teacher_live') ? 'active' : '' ?>" href="index.php?page=teacher_live">
      <i class="bi bi-youtube"></i> Live Classes
    </a>

    <a class="navlink <?= ($page === 'teacher_schedule') ? 'active' : '' ?>" href="index.php?page=teacher_schedule">
      <i class="bi bi-calendar-event"></i> Class Schedule
    </a>

    <a class="navlink <?= ($page === 'post_create') ? 'active' : '' ?>" href="index.php?page=post_create">
      <i class="bi bi-megaphone"></i> Create Post
    </a>
  </div>

  <div class="pt-3">
    <a class="navlink" href="index.php?page=teacher_logout">
      <i class="bi bi-box-arrow-right"></i> Logout
    </a>
  </div>
</div>
