<?php
session_start();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';

$pdo = db();
$postColumns = table_columns($pdo, 'posts');

$isLoggedIn = isset($_SESSION['role']) && in_array($_SESSION['role'], ['teacher','student','admin'], true);
$canInteract = isset($_SESSION['role']) && in_array($_SESSION['role'], ['teacher','student'], true);

// latest posts (approved only)
$posts = [];
try {
  if (isset($postColumns['status'])) {
    $posts = $pdo->query("SELECT * FROM posts WHERE status='approved' ORDER BY id DESC LIMIT 10")->fetchAll();
  } else {
    $posts = $pdo->query("SELECT * FROM posts ORDER BY id DESC LIMIT 10")->fetchAll();
  }
} catch (Exception $e) {
  // If DB/table issue, do not crash the homepage
  $posts = [];
}

$stats = [
  'approved_teachers' => '—',
  'active_students' => '—',
  'batches' => '—',
  'upcoming_classes' => '—',
];

try {
  $teacherColumns = table_columns($pdo, 'teachers');
  if ($teacherColumns) {
    $teacherFilter = isset($teacherColumns['status']) ? "WHERE status='approved'" : '';
    $stats['approved_teachers'] = (int)$pdo->query("SELECT COUNT(*) FROM teachers {$teacherFilter}")->fetchColumn();
  }
} catch (Exception $e) {
}

try {
  $studentColumns = table_columns($pdo, 'students');
  if ($studentColumns) {
    $studentFilter = isset($studentColumns['status']) ? "WHERE status='approved'" : '';
    $stats['active_students'] = (int)$pdo->query("SELECT COUNT(*) FROM students {$studentFilter}")->fetchColumn();
  }
} catch (Exception $e) {
}

try {
  // Keep this in sync with admin dashboard batch metric.
  $stats['batches'] = (int)$pdo->query("SELECT COUNT(*) FROM batches")->fetchColumn();
} catch (Exception $e) {
}

try {
  $classColumns = table_columns($pdo, 'class_schedule');
  if ($classColumns && isset($classColumns['class_date'])) {
    $classFilters = ["class_date >= CURDATE()"];
    if (isset($classColumns['status'])) {
      $classFilters[] = "status='scheduled'";
    }
    $whereClause = implode(' AND ', $classFilters);
    $stats['upcoming_classes'] = (int)$pdo->query("SELECT COUNT(*) FROM class_schedule WHERE {$whereClause}")->fetchColumn();
  }
} catch (Exception $e) {
}

function post_user_name(PDO $pdo, string $type, int $id): string {
  try {
    if ($type === 'teacher') {
      $st = $pdo->prepare("SELECT name FROM teachers WHERE id=?");
    } else {
      // default student
      $st = $pdo->prepare("SELECT name FROM students WHERE id=?");
    }
    $st->execute([$id]);
    $r = $st->fetch();
    return $r ? (string)$r['name'] : ucfirst($type);
  } catch (Exception $e) {
    return ucfirst($type);
  }
}

// Dashboard link based on role
$dashLink = "index.php?page=login";
if ($isLoggedIn) {
  if ($_SESSION['role'] === 'admin')   $dashLink = "index.php?page=admin_dashboard";
  if ($_SESSION['role'] === 'teacher') $dashLink = "index.php?page=teacher_dashboard";
  if ($_SESSION['role'] === 'student') $dashLink = "index.php?page=student_dashboard";
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Schola OCIMS</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <style>
    body{background:#f5f7fb;}
    .navglass{background:rgba(18,23,38,.92); backdrop-filter: blur(8px);}
    .hero{
      background: radial-gradient(1200px 500px at 20% 10%, #e7ecff 0%, transparent 70%),
                  radial-gradient(900px 400px at 80% 0%, #eaf6ff 0%, transparent 60%),
                  linear-gradient(180deg,#ffffff 0%, #f5f7fb 60%);
      border-bottom:1px solid #eef1f7;
      padding: 34px 0 26px;
    }
    .cardx{
      background:#fff;border:1px solid #eef1f7;border-radius:18px;
      box-shadow:0 18px 40px rgba(16,24,40,.06);
    }
    .pill{border-radius:999px;}
    .muted{color:#667085;}
    .feed-card{border-radius:18px; border:1px solid #eef1f7; background:#fff;}
    .premium-card{
      border:1px solid rgba(245,158,11,.6);
      background:linear-gradient(135deg, rgba(255,247,237,.9), rgba(255,255,255,1));
      box-shadow:0 18px 40px rgba(245,158,11,.15);
    }
    .premium-pill{
      border-radius:999px;
      background:rgba(245,158,11,.12);
      color:#92400e;
      padding:.2rem .6rem;
      font-weight:600;
      font-size:.75rem;
      border:1px solid rgba(245,158,11,.3);
    }
    .avatar{
      width:40px;height:40px;border-radius:999px;
      display:flex;align-items:center;justify-content:center;
      background:#111827;color:#fff;font-weight:700;
    }
    .action-link{color:#344054;text-decoration:none;}
    .action-link:hover{color:#0d6efd;}
  </style>
</head>
<body>

<nav class="navbar navglass navbar-dark">
  <div class="container">
    <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php?page=home">
      <span class="avatar" style="width:34px;height:34px;">S</span> Schola
    </a>

    <div class="d-flex gap-2">
      <?php if (!$isLoggedIn): ?>
        <a class="btn btn-outline-light pill" href="index.php?page=register">
          <i class="bi bi-person-plus me-1"></i> Create account
        </a>
        <a class="btn btn-primary pill" href="index.php?page=login">
          <i class="bi bi-box-arrow-in-right me-1"></i> Login
        </a>
      <?php else: ?>
        <a class="btn btn-outline-light pill" href="<?= e($dashLink) ?>">
          <i class="bi bi-speedometer2 me-1"></i> Dashboard
        </a>
        <a class="btn btn-primary pill" href="index.php?page=logout">
          <i class="bi bi-box-arrow-right me-1"></i> Logout
        </a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<section class="hero">
  <div class="container">
    <div class="row g-3 align-items-center">
      <div class="col-lg-7">
        <div class="fw-bold display-6 lh-sm">A complete learning management system for modern institutes.</div>
        <p class="muted mt-2 mb-3">
          Manage admissions, classes, content, and payments in one connected workspace.
          Schola keeps your institute running smoothly — with dashboards, approvals, and reminders.
        </p>
        <div class="d-flex gap-2 flex-wrap">
          <a class="btn btn-primary pill" href="<?= e($dashLink) ?>">Launch dashboard</a>
          <a class="btn btn-outline-secondary pill" href="index.php?page=register">Create account</a>
          <a class="btn btn-outline-primary pill" href="index.php?page=posts_feed">View posts feed</a>
        </div>

        <div class="row g-2 mt-3">
          <div class="col-md-6">
            <div class="cardx p-3">
              <div class="d-flex gap-2 align-items-start">
                <i class="bi bi-lightning-charge text-primary fs-4"></i>
                <div>
                  <div class="fw-semibold">Instant onboarding</div>
                  <div class="muted small">Admin approves teacher/student accounts before access.</div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="cardx p-3">
              <div class="d-flex gap-2 align-items-start">
                <i class="bi bi-shield-check text-primary fs-4"></i>
                <div>
                  <div class="fw-semibold">Secure community feed</div>
                  <div class="muted small">View posts publicly, interact only with login.</div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <div class="col-lg-5">
        <div class="cardx p-4">
          <div class="fw-semibold mb-2">At a glance</div>
          <div class="row g-2">
            <div class="col-6">
              <div class="cardx p-3" style="box-shadow:none;">
                <div class="muted small">Approved Teachers</div>
                <div class="fw-bold fs-4"><?= e($stats['approved_teachers']) ?></div>
              </div>
            </div>
            <div class="col-6">
              <div class="cardx p-3" style="box-shadow:none;">
                <div class="muted small">Active Students</div>
                <div class="fw-bold fs-4"><?= e($stats['active_students']) ?></div>
              </div>
            </div>
            <div class="col-6">
              <div class="cardx p-3" style="box-shadow:none;">
                <div class="muted small">Batches Running</div>
                <div class="fw-bold fs-4"><?= e($stats['batches']) ?></div>
              </div>
            </div>
            <div class="col-6">
              <div class="cardx p-3" style="box-shadow:none;">
                <div class="muted small">Upcoming Classes</div>
                <div class="fw-bold fs-4"><?= e($stats['upcoming_classes']) ?></div>
              </div>
            </div>
          </div>

          <div class="mt-3 muted small">
            <i class="bi bi-check-circle text-success me-1"></i> Admin-approved signups<br>
            <i class="bi bi-check-circle text-success me-1"></i> Public posts feed + gated interactions<br>
            <i class="bi bi-check-circle text-success me-1"></i> Premium dashboards
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-4">
  <div class="container">
    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
      <div>
        <div class="fw-bold fs-4">Latest Posts</div>
        <div class="muted">Public preview. Login as Teacher/Student to like/comment/share.</div>
      </div>

      <div class="d-flex gap-2">
        <a class="btn btn-outline-primary pill" href="index.php?page=posts_feed">
          Open feed <i class="bi bi-arrow-right ms-1"></i>
        </a>
        <?php if (!$isLoggedIn): ?>
          <a class="btn btn-primary pill" href="index.php?page=login">Login to interact</a>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!$posts): ?>
      <div class="feed-card p-4 text-center">
        <i class="bi bi-megaphone fs-1 text-muted"></i>
        <div class="fw-semibold mt-2">No posts yet</div>
        <div class="muted">Posts will appear here after admin approval.</div>
      </div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($posts as $p): ?>
          <?php
            $name = post_user_name($pdo, (string)$p['user_type'], (int)$p['user_id']);
            $initial = strtoupper(substr($name, 0, 1));
            $pid = (int)$p['id'];
            $postType = $p['post_type'] ?? 'update';
            $isPremium = !empty($p['is_premium']);
            $paymentAmount = $p['payment_amount'] ?? null;
          ?>
          <div class="col-lg-6">
            <div class="feed-card p-4 <?= $isPremium ? 'premium-card' : '' ?>">
              <div class="d-flex align-items-center gap-2 mb-2">
                <div class="avatar"><?= e($initial) ?></div>
                <div>
                  <div class="fw-semibold">
                    <?= e($name) ?>
                    <span class="badge text-bg-light border ms-1"><?= e($p['user_type']) ?></span>
                    <?php if ($isPremium): ?>
                      <span class="premium-pill ms-1">Premium</span>
                    <?php endif; ?>
                  </div>
                  <div class="muted small"><?= e($p['created_at']) ?></div>
                </div>
              </div>

              <?php if ($postType === 'payment_request'): ?>
                <div class="mb-2 d-flex flex-wrap gap-2 align-items-center">
                  <span class="badge text-bg-warning">Payment Request</span>
                  <?php if (!empty($paymentAmount)): ?>
                    <span class="fw-semibold">Amount: <?= e(number_format((float)$paymentAmount, 2)) ?></span>
                  <?php endif; ?>
                </div>
              <?php endif; ?>

              <div class="mb-2"><?= nl2br(e($p['content'])) ?></div>

              <?php if (!empty($p['image_path'])): ?>
                <img class="img-fluid rounded-4 border" src="<?= e($p['image_path']) ?>" alt="Post image">
              <?php endif; ?>

              <div class="d-flex gap-3 mt-3 muted small align-items-center flex-wrap">
                <?php if ($canInteract): ?>
                  <a class="action-link" href="index.php?page=post_like&post_id=<?= $pid ?>&back=home">
                    <i class="bi bi-hand-thumbs-up"></i> Like
                  </a>
                  <a class="action-link" href="index.php?page=posts_feed#commentBox<?= $pid ?>">
                    <i class="bi bi-chat"></i> Comment
                  </a>
                  <a class="action-link" href="index.php?page=posts_feed#post<?= $pid ?>">
                    <i class="bi bi-share"></i> Share
                  </a>
                <?php else: ?>
                  <span class="text-muted"><i class="bi bi-hand-thumbs-up"></i> Like</span>
                  <span class="text-muted"><i class="bi bi-chat"></i> Comment</span>
                  <a class="action-link" href="index.php?page=login">
                    <i class="bi bi-box-arrow-in-right"></i> Login to interact
                  </a>
                <?php endif; ?>
              </div>

            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="cardx p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div>
        <div class="fw-bold fs-4">Ready to run a smarter institute?</div>
        <div class="muted">Bring everyone — admins, teachers, and learners — together in one platform.</div>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-primary pill" href="index.php?page=register">Create account</a>
        <a class="btn btn-outline-secondary pill" href="index.php?page=login">Sign in</a>
      </div>
    </div>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
