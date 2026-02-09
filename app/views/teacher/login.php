<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';
require_once __DIR__ . '/../../helpers/teacher_auth.php';
require_once __DIR__ . '/../layout/header.php';

if (!empty($_SESSION['teacher_id'])) {
  header("Location: index.php?page=teacher_dashboard");
  exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';

  $stmt = db()->prepare("SELECT * FROM teachers WHERE email=? LIMIT 1");
  $stmt->execute([$email]);
  $teacher = $stmt->fetch();

  if ($teacher && password_verify($pass, $teacher['password'])) {
    $status = $teacher['status'] ?? 'pending';
    if ($status === 'approved') {
      teacher_login_session($teacher);
      header("Location: index.php?page=teacher_dashboard");
      exit;
    }

    if ($status === 'rejected') {
      $error = "Rejected. Contact admin.";
    } else {
      $error = "Waiting for admin approval.";
    }
  } else {
    $error = "Invalid email or password.";
  }
}
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="cardx p-4">
        <div class="d-flex align-items-center gap-2 mb-2">
          <div class="logo-wrap" style="background:#eef2ff;">
            <img src="assets/img/logo.png" alt="Schola Logo"
                 onerror="this.style.display='none'; this.parentElement.innerHTML='S';">
          </div>
          <div>
            <div class="fw-bold"><?= e(APP_NAME) ?></div>
            <div class="text-muted small">Teacher Login</div>
          </div>
        </div>

        <?php if ($error): ?>
          <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input class="form-control" type="email" name="email" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Password</label>
            <input class="form-control" type="password" name="password" required>
          </div>

          <button class="btn btn-primary w-100" type="submit">
            <i class="bi bi-box-arrow-in-right me-1"></i> Login
          </button>
        </form>

        <div class="text-muted small mt-3">
          Admin creates teacher accounts. Teacher pays subscription manually.
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
