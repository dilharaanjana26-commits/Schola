<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';

$pdo = db();

// login state
$isLoggedIn = isset($_SESSION['role']) && in_array($_SESSION['role'], ['teacher','student','admin'], true);
$role = $_SESSION['role'] ?? '';
$userType = ($role === 'teacher' || $role === 'student') ? $role : null;
$userId = 0;
if ($role === 'teacher') $userId = (int)($_SESSION['teacher_id'] ?? 0);
if ($role === 'student') $userId = (int)($_SESSION['student_id'] ?? 0);

// helper: get author name (teacher/student only)
function post_user_name(PDO $pdo, string $type, int $id): string {
  try {
    if ($type === 'teacher') $st = $pdo->prepare("SELECT name FROM teachers WHERE id=?");
    else $st = $pdo->prepare("SELECT name FROM students WHERE id=?");
    $st->execute([$id]);
    $r = $st->fetch();
    return $r ? (string)$r['name'] : ucfirst($type);
  } catch (Exception $e) {
    return ucfirst($type);
  }
}

// Load approved posts
$posts = [];
try {
  $posts = $pdo->query("SELECT * FROM posts WHERE status='approved' ORDER BY id DESC LIMIT 50")->fetchAll();
} catch (Exception $e) {
  $posts = [];
}

$postIds = array_map(fn($p) => (int)$p['id'], $posts);
$likesCount = [];
$commentsCount = [];
$userLiked = [];

// counts and liked flags (safe)
if ($postIds) {
  $in = implode(',', array_fill(0, count($postIds), '?'));

  try {
    $st = $pdo->prepare("SELECT post_id, COUNT(*) c FROM post_likes WHERE post_id IN ($in) GROUP BY post_id");
    $st->execute($postIds);
    foreach ($st->fetchAll() as $r) $likesCount[(int)$r['post_id']] = (int)$r['c'];
  } catch (Exception $e) {}

  try {
    $st = $pdo->prepare("SELECT post_id, COUNT(*) c FROM post_comments WHERE post_id IN ($in) GROUP BY post_id");
    $st->execute($postIds);
    foreach ($st->fetchAll() as $r) $commentsCount[(int)$r['post_id']] = (int)$r['c'];
  } catch (Exception $e) {}

  if ($userType && $userId > 0) {
    try {
      $st = $pdo->prepare("SELECT post_id FROM post_likes WHERE user_type=? AND user_id=? AND post_id IN ($in)");
      $st->execute(array_merge([$userType, $userId], $postIds));
      foreach ($st->fetchAll() as $r) $userLiked[(int)$r['post_id']] = true;
    } catch (Exception $e) {}
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Posts Feed • Schola</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <style>
    body{background:#f5f7fb;}
    .navglass{background:rgba(18,23,38,.92); backdrop-filter: blur(8px);}
    .cardx{background:#fff;border:1px solid #eef1f7;border-radius:18px;box-shadow:0 18px 40px rgba(16,24,40,.06);}
    .pill{border-radius:999px;}
    .avatar{
      width:42px;height:42px;border-radius:999px;
      display:flex;align-items:center;justify-content:center;
      background:#111827;color:#fff;font-weight:800;
    }
    .feed-card{border-radius:18px; border:1px solid #eef1f7; background:#fff;}
    .premium-card{
      border:1px solid rgba(245,158,11,.6);
      background:linear-gradient(135deg, rgba(255,247,237,.95), rgba(255,255,255,1));
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
    .muted{color:#667085;}
    .actionbtn{
      display:inline-flex;align-items:center;gap:.4rem;
      border:1px solid #eef1f7;background:#fff;border-radius:999px;
      padding:.45rem .8rem;
      text-decoration:none;color:#111827;
    }
    .actionbtn:hover{background:#f8fafc;}
    .liked{border-color:#c7d2fe;background:#eef2ff;}
  </style>
</head>
<body>

<nav class="navbar navglass navbar-dark">
  <div class="container">
    <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php?page=home">
      <span class="avatar" style="width:34px;height:34px;">S</span> Schola
    </a>
    <div class="d-flex gap-2">
      <?php if ($isLoggedIn): ?>
        <?php if ($role === 'teacher' || $role === 'student'): ?>
          <a class="btn btn-primary pill" href="index.php?page=post_create"><i class="bi bi-plus-circle me-1"></i> Create Post</a>
        <?php endif; ?>
        <a class="btn btn-outline-light pill" href="index.php?page=logout">Logout</a>
      <?php else: ?>
        <a class="btn btn-primary pill" href="index.php?page=login"><i class="bi bi-box-arrow-in-right me-1"></i> Login</a>
        <a class="btn btn-outline-light pill" href="index.php?page=register"><i class="bi bi-person-plus me-1"></i> Create account</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
      <div class="fw-bold fs-3">Community Posts</div>
      <div class="muted">Announcements and updates — public view. Like/comment/share requires login.</div>
    </div>

    <div class="d-flex gap-2 flex-wrap">
      <a class="btn btn-outline-secondary pill" href="index.php?page=home">← Home</a>
      <?php if (!$isLoggedIn): ?>
        <a class="btn btn-outline-primary pill" href="index.php?page=login">Login to interact</a>
      <?php endif; ?>
    </div>
  </div>

  <?php if (!$posts): ?>
    <div class="cardx p-5 text-center">
      <i class="bi bi-megaphone fs-1 text-muted"></i>
      <div class="fw-semibold mt-2">No posts yet</div>
      <div class="muted">Posts will appear here after admin approval.</div>
    </div>
  <?php endif; ?>

  <div class="row g-3">
    <?php foreach ($posts as $p): ?>
      <?php
        $pid = (int)$p['id'];
        $name = post_user_name($pdo, (string)$p['user_type'], (int)$p['user_id']);
        $initial = strtoupper(substr($name, 0, 1));
        $lc = $likesCount[$pid] ?? 0;
        $cc = $commentsCount[$pid] ?? 0;
        $isLiked = isset($userLiked[$pid]);
        $postType = $p['post_type'] ?? 'update';
        $isPremium = !empty($p['is_premium']);
        $paymentAmount = $p['payment_amount'] ?? null;
      ?>
      <div class="col-lg-8">
        <div class="feed-card p-4 <?= $isPremium ? 'premium-card' : '' ?>" id="post<?= $pid ?>">
          <div class="d-flex align-items-center gap-2 mb-2">
            <div class="avatar"><?= e($initial) ?></div>
            <div class="flex-grow-1">
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
            <img class="img-fluid rounded-4 border mt-2" src="<?= e($p['image_path']) ?>" alt="Post image">
          <?php endif; ?>

          <div class="d-flex gap-2 mt-3 flex-wrap">
            <?php if ($userType && $userId): ?>
              <a class="actionbtn <?= $isLiked ? 'liked' : '' ?>" href="index.php?page=post_like&post_id=<?= $pid ?>&back=posts_feed">
                <i class="bi <?= $isLiked ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up' ?>"></i>
                Like <span class="muted">(<?= $lc ?>)</span>
              </a>

              <a class="actionbtn" href="#commentBox<?= $pid ?>">
                <i class="bi bi-chat"></i>
                Comment <span class="muted">(<?= $cc ?>)</span>
              </a>

              <a class="actionbtn" href="#" onclick="prompt('Copy link:', 'index.php?page=posts_feed#post<?= $pid ?>'); return false;">
                <i class="bi bi-share"></i> Share
              </a>
            <?php else: ?>
              <span class="actionbtn text-muted"><i class="bi bi-hand-thumbs-up"></i> Like (<?= $lc ?>)</span>
              <span class="actionbtn text-muted"><i class="bi bi-chat"></i> Comment (<?= $cc ?>)</span>
              <a class="actionbtn" href="index.php?page=login"><i class="bi bi-box-arrow-in-right"></i> Login to interact</a>
            <?php endif; ?>
          </div>

          <!-- Comment area -->
          <div class="mt-3" id="commentBox<?= $pid ?>">
            <?php
              $comments = [];
              try {
                $cm = $pdo->prepare("SELECT * FROM post_comments WHERE post_id=? ORDER BY id DESC LIMIT 5");
                $cm->execute([$pid]);
                $comments = $cm->fetchAll();
              } catch (Exception $e) { $comments = []; }
            ?>

            <?php if ($comments): ?>
              <div class="mt-2">
                <?php foreach ($comments as $c): ?>
                  <?php
                    $cn = post_user_name($pdo, (string)$c['user_type'], (int)$c['user_id']);
                    $ci = strtoupper(substr($cn, 0, 1));
                  ?>
                  <div class="d-flex gap-2 py-2 border-top">
                    <div class="avatar" style="width:34px;height:34px; font-size:.9rem;"><?= e($ci) ?></div>
                    <div>
                      <div class="fw-semibold"><?= e($cn) ?> <span class="muted small ms-1"><?= e($c['created_at']) ?></span></div>
                      <div><?= nl2br(e($c['comment'])) ?></div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <?php if ($userType && $userId): ?>
              <form class="mt-3" method="post" action="index.php?page=post_comment">
                <input type="hidden" name="post_id" value="<?= $pid ?>">
                <input type="hidden" name="back" value="posts_feed">
                <div class="input-group">
                  <input class="form-control" name="comment" placeholder="Write a comment..." required>
                  <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i></button>
                </div>
              </form>
            <?php endif; ?>
          </div>

        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
