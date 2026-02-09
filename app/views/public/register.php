<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';

$pdo = db();
$ok = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $role = $_POST['role'] ?? '';

  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($role !== 'teacher' && $role !== 'student') {
    $error = "Select account type.";
  } elseif ($name === '' || $email === '' || $password === '') {
    $error = "Name, Email and Password are required.";
  } else {
    $hash = password_hash($password, PASSWORD_DEFAULT);

    try {
      if ($role === 'teacher') {
        $mobile = trim($_POST['mobile'] ?? '');
        $st = $pdo->prepare("INSERT INTO teachers (name,email,mobile,password,subscription_status,status) VALUES (?,?,?,?, 'pending', 'pending')");
        $st->execute([$name, $email, $mobile ?: null, $hash]);
      } else {
        $age = (int)($_POST['age'] ?? 0);
        $nic = trim($_POST['nic'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $whatsapp = trim($_POST['whatsapp'] ?? '');

        $st = $pdo->prepare("INSERT INTO students (name,age,nic,city,whatsapp,email,password,status) VALUES (?,?,?,?,?,?,?, 'pending')");
        $st->execute([$name, $age ?: null, $nic ?: null, $city ?: null, $whatsapp ?: null, $email, $hash]);
      }

      $ok = "Account request submitted. Please wait for admin approval.";
    } catch (Exception $e) {
      $error = "This email is already used or invalid data.";
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Create account • Schola</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{background:#f5f7fb;}
    .cardx{background:#fff;border:1px solid #eef1f7;border-radius:18px;box-shadow:0 18px 40px rgba(16,24,40,.06);}
    .pill{border-radius:999px;}
  </style>
</head>
<body>

<div class="container py-5" style="max-width:720px;">
  <div class="cardx p-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div class="fw-bold fs-4">Create account</div>
      <a class="btn btn-outline-secondary pill" href="index.php?page=home">Back</a>
    </div>
    <div class="text-muted mb-3">Requests must be approved by admin before login.</div>

    <?php if ($ok): ?><div class="alert alert-success"><?= e($ok) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

    <form method="post">
      <div class="mb-3">
        <label class="form-label">Account type</label>
        <select class="form-select" name="role" id="roleSelect" required>
          <option value="">Select...</option>
          <option value="student">Student</option>
          <option value="teacher">Teacher</option>
        </select>
      </div>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Full name</label>
          <input class="form-control" name="name" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input class="form-control" type="email" name="email" required>
        </div>
      </div>

      <div class="mt-3">
        <label class="form-label">Password</label>
        <input class="form-control" type="password" name="password" required>
      </div>

      <!-- Teacher fields -->
      <div id="teacherFields" class="mt-3" style="display:none;">
        <label class="form-label">Mobile</label>
        <input class="form-control" name="mobile" placeholder="077xxxxxxx">
      </div>

      <!-- Student fields -->
      <div id="studentFields" class="mt-3" style="display:none;">
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Age</label>
            <input class="form-control" type="number" name="age">
          </div>
          <div class="col-md-4">
            <label class="form-label">NIC</label>
            <input class="form-control" name="nic">
          </div>
          <div class="col-md-5">
            <label class="form-label">WhatsApp</label>
            <input class="form-control" name="whatsapp">
          </div>
          <div class="col-md-12">
            <label class="form-label">City</label>
            <input class="form-control" name="city">
          </div>
        </div>
      </div>

      <div class="d-flex gap-2 mt-4">
        <button class="btn btn-primary pill" type="submit">Submit request</button>
        <a class="btn btn-outline-primary pill" href="index.php?page=login">Already have account? Login</a>
      </div>
    </form>

  </div>
</div>

<script>
  const roleSelect = document.getElementById('roleSelect');
  const teacherFields = document.getElementById('teacherFields');
  const studentFields = document.getElementById('studentFields');

  roleSelect.addEventListener('change', () => {
    teacherFields.style.display = roleSelect.value === 'teacher' ? 'block' : 'none';
    studentFields.style.display = roleSelect.value === 'student' ? 'block' : 'none';
  });
</script>
</body>
</html>
