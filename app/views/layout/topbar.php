<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$page = $_GET['page'] ?? 'admin_dashboard';

$pages = [
  'admin_dashboard'        => ['title' => 'Admin Dashboard',   'crumb' => ['Dashboard']],
  'admin_teachers'         => ['title' => 'Teachers',          'crumb' => ['Dashboard','Teachers']],
  'admin_students'         => ['title' => 'Students',          'crumb' => ['Dashboard','Students']],
  'admin_batches'          => ['title' => 'Batches',           'crumb' => ['Dashboard','Batches']],
  'admin_teacher_payments' => ['title' => 'Teacher Payments',  'crumb' => ['Dashboard','Teacher Payments']],
  'admin_student_payments' => ['title' => 'Student Payments',  'crumb' => ['Dashboard','Student Payments']],
  'admin_class_schedule'   => ['title' => 'Class Schedule',    'crumb' => ['Dashboard','Class Schedule']],
  'admin_reports'          => ['title' => 'Reports',           'crumb' => ['Dashboard','Reports']],
    
      // Teacher
  'teacher_dashboard'    => ['title' => 'Teacher Dashboard', 'crumb' => ['Dashboard']],
  'teacher_subscription' => ['title' => 'Subscription',      'crumb' => ['Dashboard','Subscription']],
  'teacher_content'      => ['title' => 'Content Upload',    'crumb' => ['Dashboard','Content']],
  'teacher_live'         => ['title' => 'Live Classes',      'crumb' => ['Dashboard','Live Classes']],
  'teacher_schedule'     => ['title' => 'Class Schedule',    'crumb' => ['Dashboard','Class Schedule']],

  // Student
  'student_dashboard'    => ['title' => 'Student Dashboard', 'crumb' => ['Dashboard']],
  'student_content'      => ['title' => 'My Content',        'crumb' => ['Dashboard','Content']],
  'student_payment'      => ['title' => 'Pay Fees',          'crumb' => ['Dashboard','Payments']],
  'student_batch_content'=> ['title' => 'Batch Content',     'crumb' => ['Dashboard','Content']],

];

$title = $pages[$page]['title'] ?? 'Admin Panel';
$crumb = $pages[$page]['crumb'] ?? ['Dashboard'];

// Page-specific action buttons (optional)
$actions = [
  'admin_teachers' => ['label' => 'Add Teacher', 'url' => 'index.php?page=admin_teachers#addForm', 'icon' => 'bi-person-plus'],
  'admin_students' => ['label' => 'Add Student', 'url' => 'index.php?page=admin_students#addForm', 'icon' => 'bi-person-plus'],
  'admin_batches'  => ['label' => 'Add Batch',   'url' => 'index.php?page=admin_batches#addForm',  'icon' => 'bi-plus-square'],
];
$action = $actions[$page] ?? null;

// Notifications placeholder
$notifCount = 0;

// Theme cookie
$theme = $_COOKIE['schola_theme'] ?? 'light';
?>

<div class="topbar d-flex align-items-center justify-content-between px-3">

  <!-- LEFT: Mobile menu + Title + Breadcrumb -->
  <div class="d-flex align-items-center gap-2">
    <!-- Mobile hamburger -->
    <button class="iconbtn d-inline-flex d-lg-none" type="button"
            data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
      <i class="bi bi-list"></i>
    </button>

    <div>
      <div class="topbar-title">
      <div class="title-main"><?= htmlspecialchars($title) ?></div>
      <div class="title-crumb">
        <?php foreach ($crumb as $i => $c): ?>
          <?= htmlspecialchars($c) ?><?= ($i < count($crumb)-1) ? ' / ' : '' ?>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- RIGHT: Actions + Icons + Admin -->
  <div class="d-flex align-items-center gap-2 topbar-right">

    <?php if ($action): ?>
      <a class="btn btn-sm btn-primary" href="<?= htmlspecialchars($action['url']) ?>">
        <i class="bi <?= htmlspecialchars($action['icon']) ?> me-1"></i>
        <?= htmlspecialchars($action['label']) ?>
      </a>
    <?php endif; ?>

    <a class="iconbtn position-relative" href="index.php?page=admin_reports" title="Notifications (soon)">
      <i class="bi bi-bell"></i>
      <?php if ($notifCount > 0): ?>
        <span class="notif-dot"><?= (int)$notifCount ?></span>
      <?php endif; ?>
    </a>

    <button class="iconbtn" type="button" id="themeToggle" title="Toggle theme">
      <i class="bi <?= ($theme === 'dark') ? 'bi-sun' : 'bi-moon' ?>"></i>
    </button>

    <?php
    $userLabel =
      $_SESSION['admin_name'] ?? $_SESSION['teacher_name'] ?? $_SESSION['student_name'] ?? 'User';

    $userRole =
      isset($_SESSION['admin_id']) ? 'Admin' :
      (isset($_SESSION['teacher_id']) ? 'Teacher' :
      (isset($_SESSION['student_id']) ? 'Student' : '') );
    ?>

    <span class="badge rounded-pill text-bg-light border topbar-user">
      <i class="bi bi-person-circle me-1"></i>
      <?= htmlspecialchars($userRole) ?> - <?= htmlspecialchars($userLabel) ?>
    </span>


  </div>

</div>
