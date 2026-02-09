<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['teacher','student'], true)) {
  header("Location: index.php?page=login");
  exit;
}

$pdo = db();
$postColumns = table_columns($pdo, 'posts');
$role = $_SESSION['role'];
$user_id = ($role === 'teacher') ? (int)$_SESSION['teacher_id'] : (int)$_SESSION['student_id'];

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $content = trim($_POST['content'] ?? '');
  $postType = $_POST['post_type'] ?? 'update';
  $isPremium = ($role === 'teacher' && !empty($_POST['is_premium'])) ? 1 : 0;
  $paymentAmount = null;

  if ($role !== 'teacher') {
    $postType = 'update';
    $isPremium = 0;
  }

  if ($role === 'teacher' && !in_array($postType, ['update', 'payment_request'], true)) {
    $postType = 'update';
  }

  if ($content === '') {
    $error = "Post content is required.";
  }

  if (!$error && $postType === 'payment_request') {
    $paymentAmount = (float)($_POST['payment_amount'] ?? 0);
    if ($paymentAmount <= 0) {
      $error = "Enter a valid payment amount.";
    }
  }

  $imagePath = null;

  if (!$error && !empty($_FILES['image']['name'])) {
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
    $fields = [
      'user_type' => $role,
      'user_id' => $user_id,
      'content' => $content,
    ];

    if (isset($postColumns['image_path'])) $fields['image_path'] = $imagePath;
    if (isset($postColumns['status'])) $fields['status'] = 'pending';
    if (isset($postColumns['post_type'])) $fields['post_type'] = $postType;
    if (isset($postColumns['payment_amount'])) $fields['payment_amount'] = $paymentAmount;
    if (isset($postColumns['is_premium'])) $fields['is_premium'] = $isPremium;

    try {
      $columns = array_keys($fields);
      $placeholders = implode(',', array_fill(0, count($columns), '?'));
      $st = $pdo->prepare("INSERT INTO posts (" . implode(',', $columns) . ") VALUES ($placeholders)");
      $st->execute(array_values($fields));
      $msg = isset($postColumns['status'])
        ? "Post submitted! Waiting for admin approval."
        : "Post submitted!";
    } catch (Exception $e) {
      try {
        $fallback = [
          'user_type' => $role,
          'user_id' => $user_id,
          'content' => $content,
        ];
        $columns = array_keys($fallback);
        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $st = $pdo->prepare("INSERT INTO posts (" . implode(',', $columns) . ") VALUES ($placeholders)");
        $st->execute(array_values($fallback));
        $msg = "Post submitted!";
      } catch (Exception $e) {
        $error = "Unable to save post. Please contact support.";
      }
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
          <?php if ($role === 'teacher'): ?>
            <div class="mb-3">
              <label class="form-label">Post Type</label>
              <select class="form-select" name="post_type" id="postType">
                <option value="update">Announcement / Update</option>
                <option value="payment_request">Payment Request</option>
              </select>
            </div>

            <div class="mb-3 d-none" id="paymentAmountWrap">
              <label class="form-label">Payment Amount</label>
              <input class="form-control" type="number" step="0.01" min="0" name="payment_amount" placeholder="Enter requested amount">
              <div class="form-text">Add the amount you want the admin to approve.</div>
            </div>
          <?php endif; ?>

          <div class="mb-3">
            <label class="form-label">Post Text</label>
            <textarea class="form-control" name="content" rows="5" placeholder="Share an update..." required></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Image (optional)</label>
            <input class="form-control" type="file" name="image">
          </div>

          <?php if ($role === 'teacher'): ?>
            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" id="isPremium" name="is_premium">
              <label class="form-check-label" for="isPremium">Mark as premium (highlighted in feed)</label>
            </div>
          <?php endif; ?>

          <button class="btn btn-primary" type="submit">
            <i class="bi bi-send me-1"></i> Submit Post
          </button>

          <a class="btn btn-outline-secondary ms-2" href="index.php?page=posts_feed">View Feed</a>
        </form>
      </div>

    </div>
  </div>
</div>

<script>
  const postType = document.getElementById('postType');
  const paymentWrap = document.getElementById('paymentAmountWrap');

  if (postType && paymentWrap) {
    const togglePayment = () => {
      if (postType.value === 'payment_request') paymentWrap.classList.remove('d-none');
      else paymentWrap.classList.add('d-none');
    };
    postType.addEventListener('change', togglePayment);
    togglePayment();
  }
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
