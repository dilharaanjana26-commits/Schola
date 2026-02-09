<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';

$pdo = db();
$error = '';

if (isset($_SESSION['role'])) {
  // already logged in
  if ($_SESSION['role'] === 'admin') header("Location: index.php?page=admin_dashboard");
  if ($_SESSION['role'] === 'teacher') header("Location: index.php?page=teacher_dashboard");
  if ($_SESSION['role'] === 'student') header("Location: index.php?page=student_dashboard");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($email === '' || $password === '') {
    $error = "Email and password required.";
  } else {
    // 1) Admin
    $st = $pdo->prepare("SELECT * FROM admins WHERE email=? LIMIT 1");
    $st->execute([$email]);
    $u = $st->fetch();
    if ($u && password_verify($password, $u['password'])) {
      $_SESSION['role'] = 'admin';
      $_SESSION['admin_id'] = (int)$u['id'];
      $_SESSION['admin_name'] = $u['name'];
      header("Location: index.php?page=admin_dashboard");
      exit;
    }

    // 2) Teacher
    $st = $pdo->prepare("SELECT * FROM teachers WHERE email=? LIMIT 1");
    $st->execute([$email]);
    $u = $st->fetch();
    if ($u && password_verify($password, $u['password'])) {
      $status = $u['status'] ?? 'pending';
      if ($status === 'approved') {
        $_SESSION['role'] = 'teacher';
        $_SESSION['teacher_id'] = (int)$u['id'];
        $_SESSION['teacher_name'] = $u['name'];
        header("Location: index.php?page=teacher_dashboard");
        exit;
      }

      if ($status === 'rejected') {
        $error = "Rejected. Contact admin.";
      } else {
        $error = "Waiting for admin approval.";
      }
    }

    // 3) Student
    $st = $pdo->prepare("SELECT * FROM students WHERE email=? LIMIT 1");
    $st->execute([$email]);
    $u = $st->fetch();
    if ($u && password_verify($password, $u['password'])) {
      $status = $u['status'] ?? 'pending';
      if ($status === 'approved') {
        $_SESSION['role'] = 'student';
        $_SESSION['student_id'] = (int)$u['id'];
        $_SESSION['student_name'] = $u['name'];
        header("Location: index.php?page=student_dashboard");
        exit;
      }

      if ($status === 'rejected') {
        $error = "Rejected. Contact admin.";
      } else {
        $error = "Waiting for admin approval.";
      }
    }

    if ($error === '') {
      $error = "Invalid login details.";
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login • Schola</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <style>
    body{background:#f5f7fb;}
    .cardx{background:#fff;border:1px solid #eef1f7;border-radius:18px;box-shadow:0 18px 40px rgba(16,24,40,.06);}
    .pill{border-radius:999px;}
    .avatar{width:44px;height:44px;border-radius:999px;display:flex;align-items:center;justify-content:center;background:#111827;color:#fff;font-weight:800;}
  </style>
</head>
<body>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-5">
        <div class="text-center mb-3">
          <a class="text-decoration-none" href="index.php?page=home">
            <span class="avatar mx-auto mb-2">S</span>
            <div class="fw-bold fs-4">Schola</div>
          </a>
          <div class="text-muted">Login to your dashboard</div>
        </div>

        <?php if ($error): ?>
          <div class="alert alert-danger cardx border-0"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="cardx p-4">
          <form method="post">
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input class="form-control" type="email" name="email" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Password</label>
              <input class="form-control" type="password" name="password" required>
            </div>

            <button class="btn btn-primary w-100 pill" type="submit">
              <i class="bi bi-box-arrow-in-right me-1"></i> Sign in
            </button>

            <div class="text-muted small mt-3 text-center">
              This login works for Admin, Teacher, and Student.
            </div>
          </form>
        </div>

        <div class="text-center mt-3">
          <a class="btn btn-outline-secondary pill" href="index.php?page=home">← Back to Home</a>
        </div>

      </div>
    </div>
  </div>
</body>
</html>
