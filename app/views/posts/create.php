<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['teacher','student'], true)) {
  header("Location: index.php?page=login");
  exit;
}

$pdo = db();
$role = $_SESSION['role'];
$user_id = ($role === 'teacher') ? (int)$_SESSION['teacher_id'] : (int)$_SESSION['student_id'];

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $content = trim($_POST['content'] ?? '');

  if ($content === '') {
    $error = "Post content is required.";
  } else {
    $imagePath = null;

    if (!empty($_FILES['image']['name'])) {
      $file = $_FILES['image'];
      $allowedExt = ['jpg','jpeg','png','webp'];
      $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

      if (!in_array($ext, $allowedExt, true)) {
        $error = "Only JPG, PNG, WEBP allowed.";
      } else {
        $dir = __DIR__ . '/../../../uploads/posts/';
        if (!is_dir($dir)) @mkdir($dir, 0777, true);

        $safe = "{$role}_{$user_id}_" . time() . "." . $ext;
        $imagePath = "uploads/posts/" . $safe;

        if (!move_uploaded_file($file['tmp_name'], $dir . $safe)) {
          $error = "Image upload failed.";
        }
      }
    }

    if (!$error) {
      $st = $pdo->prepare("INSERT INTO posts (user_type, user_id, content, image_path, status) VALUES (?,?,?,?, 'pending')");
      $st->execute([$role, $user_id, $content, $imagePath]);
      $msg = "Post submitted! Waiting for admin approval.";
    }
  }
}
?>
<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="app-shell">
  <?php
    // Show their own sidebar
    if ($role === 'teacher') require_once __DIR__ . '/../layout/sidebar_teacher.php';
    else require_once __DIR__ . '/../layout/sidebar_student.php';
  ?>
  <div class="content">
    <?php require_once __DIR__ . '/../layout/topbar.php'; ?>

    <div class="page">
      <div class="cardx p-4 mb-3">
        <div class="fw-bold fs-4">Create Post</div>
        <div class="text-muted">This post will appear publicly after admin approval.</div>
      </div>

      <?php if ($msg): ?><div class="alert alert-success cardx border-0"><?= e($msg) ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert alert-danger cardx border-0"><?= e($error) ?></div><?php endif; ?>

      <div class="cardx p-4">
        <form method="post" enctype="multipart/form-data">
          <div class="mb-3">
            <label class="form-label">Post Text</label>
            <textarea class="form-control" name="content" rows="5" placeholder="Share an update..." required></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Image (optional)</label>
            <input class="form-control" type="file" name="image">
          </div>

          <button class="btn btn-primary" type="submit">
            <i class="bi bi-send me-1"></i> Submit Post
          </button>

          <a class="btn btn-outline-secondary ms-2" href="index.php?page=posts_feed">View Feed</a>
        </form>
      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
