<?php
require_once __DIR__ . '/../../helpers/auth.php';
require_admin();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';

$pdo = db();
$error = '';

// ---------------- CREATE ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
  $name = trim($_POST['name'] ?? '');
  $age = (int)($_POST['age'] ?? 0);
  $nic = trim($_POST['nic'] ?? '');
  $city = trim($_POST['city'] ?? '');
  $whatsapp = trim($_POST['whatsapp'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($name === '' || $email === '' || $password === '') {
    $error = "Name, Email and Password are required.";
  } else {
    $hash = password_hash($password, PASSWORD_DEFAULT);

    try {
      $stmt = $pdo->prepare("INSERT INTO students (name, age, nic, city, whatsapp, email, password, status) VALUES (?,?,?,?,?,?,?, 'approved')");
      $stmt->execute([$name, $age ?: null, $nic ?: null, $city ?: null, $whatsapp ?: null, $email, $hash]);
      header("Location: index.php?page=admin_students&ok=created");
      exit;
    } catch (Exception $e) {
      $error = "Email already exists or invalid data.";
    }
  }
}

// ---------------- UPDATE ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
  $id = (int)($_POST['id'] ?? 0);
  $name = trim($_POST['name'] ?? '');
  $age = (int)($_POST['age'] ?? 0);
  $nic = trim($_POST['nic'] ?? '');
  $city = trim($_POST['city'] ?? '');
  $whatsapp = trim($_POST['whatsapp'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = trim($_POST['password'] ?? '');

  if ($id <= 0 || $name === '' || $email === '') {
    $error = "Name and Email are required.";
  } else {
    if ($password !== '') {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare("UPDATE students SET name=?, age=?, nic=?, city=?, whatsapp=?, email=?, password=? WHERE id=?");
      $stmt->execute([$name, $age ?: null, $nic ?: null, $city ?: null, $whatsapp ?: null, $email, $hash, $id]);
    } else {
      $stmt = $pdo->prepare("UPDATE students SET name=?, age=?, nic=?, city=?, whatsapp=?, email=? WHERE id=?");
      $stmt->execute([$name, $age ?: null, $nic ?: null, $city ?: null, $whatsapp ?: null, $email, $id]);
    }

    header("Location: index.php?page=admin_students&ok=updated");
    exit;
  }
}

// ---------------- ENROLL ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'enroll') {
  $student_id = (int)($_POST['student_id'] ?? 0);
  $batch_id = (int)($_POST['batch_id'] ?? 0);

  if ($student_id > 0 && $batch_id > 0) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO student_enrollments (student_id, batch_id, status) VALUES (?, ?, 'active')");
    $stmt->execute([$student_id, $batch_id]);
  }

  header("Location: index.php?page=admin_students&ok=enrolled");
  exit;
}

// ---------------- UNENROLL ----------------
if (isset($_GET['unenroll_student'], $_GET['unenroll_batch'])) {
  $student_id = (int)$_GET['unenroll_student'];
  $batch_id = (int)$_GET['unenroll_batch'];

  if ($student_id > 0 && $batch_id > 0) {
    $stmt = $pdo->prepare("UPDATE student_enrollments SET status='inactive' WHERE student_id=? AND batch_id=?");
    $stmt->execute([$student_id, $batch_id]);
  }
  header("Location: index.php?page=admin_students&ok=unenrolled");
  exit;
}

// ---------------- DELETE ----------------
if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];
  if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM students WHERE id=?");
    $stmt->execute([$id]);
  }
  header("Location: index.php?page=admin_students&ok=deleted");
  exit;
}

// ---------------- EDIT MODE ----------------
$editStudent = null;
if (isset($_GET['edit'])) {
  $id = (int)$_GET['edit'];
  $stmt = $pdo->prepare("SELECT * FROM students WHERE id=?");
  $stmt->execute([$id]);
  $editStudent = $stmt->fetch();
}

// ---------------- LIST ----------------
$students = $pdo->query("SELECT * FROM students ORDER BY id DESC")->fetchAll();
$batches  = $pdo->query("SELECT id, name FROM batches ORDER BY name")->fetchAll();

// Enrollments map: student_id => [batchName...]
$enrollRows = $pdo->query("
  SELECT se.student_id, se.batch_id, se.status, b.name AS batch_name
  FROM student_enrollments se
  JOIN batches b ON b.id = se.batch_id
")->fetchAll();

$enrollMap = [];
foreach ($enrollRows as $r) {
  $enrollMap[$r['student_id']][] = [
    'batch_name' => $r['batch_name'],
    'batch_id' => $r['batch_id'],
    'status' => $r['status'],
  ];
}

require_once __DIR__ . '/../layout/header.php';
?>
<div class="app-shell">
  <?php require_once __DIR__ . '/../layout/sidebar.php'; ?>
  <div class="content">
    <?php require_once __DIR__ . '/../layout/topbar.php'; ?>

    <div class="page">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
          <div class="fw-bold fs-4">Students</div>
          <div class="text-muted">Create, edit, remove students and manage enrollments.</div>
        </div>
      </div>

      <?php if (!empty($_GET['ok'])): ?>
        <div class="alert alert-success cardx border-0">
          Action completed: <?= e($_GET['ok']) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger cardx border-0">
          <?= e($error) ?>
        </div>
      <?php endif; ?>

      <div class="grid">
        <!-- Form -->
        <div style="grid-column: span 4;">
          <div class="cardx p-4">
            <div class="fw-semibold mb-3">
              <?= $editStudent ? "Edit Student" : "Add New Student" ?>
            </div>

            <form method="post">
              <?php if ($editStudent): ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= e($editStudent['id']) ?>">
              <?php else: ?>
                <input type="hidden" name="action" value="create">
              <?php endif; ?>

              <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input class="form-control" name="name" required
                       value="<?= e($editStudent['name'] ?? '') ?>">
              </div>

              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">Age</label>
                  <input class="form-control" type="number" name="age"
                         value="<?= e($editStudent['age'] ?? '') ?>">
                </div>
                <div class="col-md-8">
                  <label class="form-label">NIC</label>
                  <input class="form-control" name="nic"
                         value="<?= e($editStudent['nic'] ?? '') ?>">
                </div>
              </div>

              <div class="row g-3 mt-1">
                <div class="col-md-6">
                  <label class="form-label">WhatsApp</label>
                  <input class="form-control" name="whatsapp"
                         value="<?= e($editStudent['whatsapp'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label">City</label>
                  <input class="form-control" name="city"
                         value="<?= e($editStudent['city'] ?? '') ?>">
                </div>
              </div>

              <div class="mb-3 mt-3">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" name="email" required
                       value="<?= e($editStudent['email'] ?? '') ?>">
              </div>

              <div class="mb-3">
                <label class="form-label">Password <?= $editStudent ? '(leave blank to keep)' : '' ?></label>
                <input class="form-control" type="password" name="password" <?= $editStudent ? '' : 'required' ?>>
              </div>

              <div class="d-flex gap-2">
                <button class="btn btn-primary" type="submit">
                  <i class="bi bi-check2-circle me-1"></i>
                  <?= $editStudent ? "Update" : "Create" ?>
                </button>

                <?php if ($editStudent): ?>
                  <a class="btn btn-outline-secondary" href="index.php?page=admin_students">Cancel</a>
                <?php endif; ?>
              </div>
            </form>
          </div>
        </div>

        <!-- Table -->
        <div style="grid-column: span 8;">
          <div class="cardx p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div class="fw-semibold">All Students</div>
              <div class="text-muted small"><?= count($students) ?> total</div>
            </div>

            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr class="text-muted">
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>WhatsApp</th>
                    <th>City</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!$students): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No students found.</td></tr>
                  <?php endif; ?>

                  <?php foreach ($students as $s): ?>
                    <tr>
                      <td><?= e($s['id']) ?></td>
                      <td class="fw-semibold"><?= e($s['name']) ?></td>
                      <td><?= e($s['email']) ?></td>
                      <td><?= e($s['whatsapp']) ?></td>
                      <td><?= e($s['city']) ?></td>
                      <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="index.php?page=admin_students&edit=<?= e($s['id']) ?>">
                          <i class="bi bi-pencil"></i>
                        </a>
                        <a class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Delete this student?')"
                           href="index.php?page=admin_students&delete=<?= e($s['id']) ?>">
                          <i class="bi bi-trash"></i>
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <div class="mt-4">
              <div class="fw-semibold mb-2">Enrollments</div>
              <div class="table-responsive">
                <table class="table align-middle">
                  <thead>
                    <tr class="text-muted">
                      <th>Student</th>
                      <th>Batch</th>
                      <th>Status</th>
                      <th class="text-end">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($enrollMap as $studentId => $batchesList): ?>
                      <?php foreach ($batchesList as $x): ?>
                        <tr>
                          <td><?= e($students[array_search($studentId, array_column($students, 'id'))]['name'] ?? '') ?></td>
                          <td><?= e($x['batch_name']) ?></td>
                          <td>
                            <?php
                              $badge = ($x['status'] === 'active') ? 'text-bg-success' : 'text-bg-secondary';
                            ?>
                            <span class="badge <?= $badge ?>"><?= e($x['status']) ?></span>
                          </td>
                          <td class="text-end">
                            <?php if ($x['status'] === 'active'): ?>
                              <a class="btn btn-sm btn-outline-danger"
                                 href="index.php?page=admin_students&unenroll_student=<?= e($studentId) ?>&unenroll_batch=<?= e($x['batch_id']) ?>">
                                Unenroll
                              </a>
                            <?php else: ?>
                              <span class="text-muted small">Inactive</span>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="cardx p-4 mt-4">
        <div class="fw-semibold mb-2">Enroll Student to Batch</div>
        <form method="post" class="row g-3">
          <input type="hidden" name="action" value="enroll">
          <div class="col-md-5">
            <label class="form-label">Student</label>
            <select class="form-select" name="student_id" required>
              <option value="">Select student</option>
              <?php foreach ($students as $s): ?>
                <option value="<?= e($s['id']) ?>"><?= e($s['name']) ?> (<?= e($s['email']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-5">
            <label class="form-label">Batch</label>
            <select class="form-select" name="batch_id" required>
              <option value="">Select batch</option>
              <?php foreach ($batches as $b): ?>
                <option value="<?= e($b['id']) ?>"><?= e($b['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100" type="submit">Enroll</button>
          </div>
        </form>
      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
