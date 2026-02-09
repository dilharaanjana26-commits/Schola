<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';
require_once __DIR__ . '/../../helpers/student_auth.php';
require_once __DIR__ . '/../layout/header.php';

if (!empty($_SESSION['student_id'])) {
  header("Location: index.php?page=student_dashboard");
  exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';

  $stmt = db()->prepare("SELECT * FROM students WHERE email=? LIMIT 1");
  $stmt->execute([$email]);
  $s = $stmt->fetch();

  if ($s && password_verify($pass, $s['password'])) {
    $status = $s['status'] ?? 'pending';
    if ($status === 'approved') {
      student_login_session($s);
      header("Location: index.php?page=student_dashboard");
      exit;
    }

    if ($status === 'rejected') {
      $error = "Rejected. Contact admin.";
    } else {
      $error = "Waiting for admin approval.";
    }
  } else {
    $error = "Invalid login details.";
  }
}
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="cardx p-4">
        <h5 class="fw-bold">Student Login</h5>

        <?php if ($error): ?>
          <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post">
          <div class="mb-3">
            <label>Email</label>
            <input class="form-control" type="email" name="email" required>
          </div>
          <div class="mb-3">
            <label>Password</label>
            <input class="form-control" type="password" name="password" required>
          </div>
          <button class="btn btn-primary w-100">Login</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
