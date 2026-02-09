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
      $stmt = $pdo->prepare("INSERT INTO students (name, age, nic, city, whatsapp, email, password) VALUES (?,?,?,?,?,?,?)");
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
  $sid = (int)$r['student_id'];
  if (!isset($enrollMap[$sid])) $enrollMap[$sid] = [];
  $enrollMap[$sid][] = [
    'batch_id' => (int)$r['batch_id'],
    'batch_name' => $r['batch_name'],
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
          <div class="text-muted">Add, edit, remove students and enroll them into batches.</div>
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
          <div id="addForm"></div>
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
                <input class="form-control" name="name" required value="<?= e($editStudent['name'] ?? '') ?>">
              </div>

              <div class="mb-3">
                <label class="form-label">Age</label>
                <input class="form-control" type="number" name="age" value="<?= e($editStudent['age'] ?? '') ?>">
              </div>

              <div class="mb-3">
                <label class="form-label">NIC</label>
                <input class="form-control" name="nic" value="<?= e($editStudent['nic'] ?? '') ?>">
              </div>

              <div class="mb-3">
                <label class="form-label">City</label>
                <input class="form-control" name="city" value="<?= e($editStudent['city'] ?? '') ?>">
              </div>

              <div class="mb-3">
                <label class="form-label">WhatsApp Number</label>
                <input class="form-control" name="whatsapp" value="<?= e($editStudent['whatsapp'] ?? '') ?>">
              </div>

              <div class="mb-3">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" name="email" required value="<?= e($editStudent['email'] ?? '') ?>">
              </div>

              <div class="mb-3">
                <label class="form-label">
                  Password <?= $editStudent ? '<span class="text-muted small">(leave blank to keep old)</span>' : '' ?>
                </label>
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
            <div class="d-flex align-items-center justify-content-between mb-3">
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
                    <th>Enrolled Batches</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!$students): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No students found.</td></tr>
                  <?php endif; ?>

                  <?php foreach ($students as $s): ?>
                    <?php $sid = (int)$s['id']; ?>
                    <tr>
                      <td><?= e($s['id']) ?></td>
                      <td class="fw-semibold"><?= e($s['name']) ?></td>
                      <td><?= e($s['email']) ?></td>
                      <td><?= e($s['whatsapp'] ?? '—') ?></td>

                      <td>
                        <?php
                          $en = $enrollMap[$sid] ?? [];
                          $activeTags = [];
                          foreach ($en as $x) {
                            if ($x['status'] === 'active') {
                              $activeTags[] = $x;
                            }
                          }
                        ?>
                        <?php if (!$activeTags): ?>
                          <span class="text-muted">—</span>
                        <?php else: ?>
                          <div class="d-flex flex-wrap gap-1">
                            <?php foreach ($activeTags as $x): ?>
                              <span class="badge text-bg-light border">
                                <?= e($x['batch_name']) ?>
                                <a class="text-danger ms-1"
                                   style="text-decoration:none"
                                   onclick="return confirm('Unenroll this student from this batch?')"
                                   href="index.php?page=admin_students&unenroll_student=<?= $sid ?>&unenroll_batch=<?= (int)$x['batch_id'] ?>">
                                  ×
                                </a>
                              </span>
                            <?php endforeach; ?>
                          </div>
                        <?php endif; ?>
                      </td>

                      <td class="text-end">
                        <!-- Enroll button -->
                        <button class="btn btn-sm btn-outline-success"
                                data-bs-toggle="modal"
                                data-bs-target="#enrollModal<?= $sid ?>">
                          <i class="bi bi-person-plus"></i>
                        </button>

                        <a class="btn btn-sm btn-outline-primary" href="index.php?page=admin_students&edit=<?= e($s['id']) ?>">
                          <i class="bi bi-pencil"></i>
                        </a>

                        <a class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Delete this student?')"
                           href="index.php?page=admin_students&delete=<?= e($s['id']) ?>">
                          <i class="bi bi-trash"></i>
                        </a>

                        <!-- Enroll Modal -->
                        <div class="modal fade" id="enrollModal<?= $sid ?>" tabindex="-1">
                          <div class="modal-dialog">
                            <form method="post" class="modal-content">
                              <input type="hidden" name="action" value="enroll">
                              <input type="hidden" name="student_id" value="<?= $sid ?>">

                              <div class="modal-header">
                                <h5 class="modal-title">Enroll Student</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                              </div>

                              <div class="modal-body">
                                <div class="mb-3">
                                  <label class="form-label">Student</label>
                                  <input class="form-control" value="<?= e($s['name']) ?>" disabled>
                                </div>

                                <div class="mb-3">
                                  <label class="form-label">Batch</label>
                                  <select class="form-select" name="batch_id" required>
                                    <option value="">-- Select batch --</option>
                                    <?php foreach ($batches as $b): ?>
                                      <option value="<?= (int)$b['id'] ?>"><?= e($b['name']) ?></option>
                                    <?php endforeach; ?>
                                  </select>
                                </div>

                                <div class="text-muted small">
                                  Once enrolled, student will see this batch in “My Content” and can pay to unlock access.
                                </div>
                              </div>

                              <div class="modal-footer">
                                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-primary" type="submit">
                                  <i class="bi bi-check2-circle me-1"></i> Enroll
                                </button>
                              </div>
                            </form>
                          </div>
                        </div>

                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <div class="text-muted small">
              Enrollment controls which batches the student can see and pay for.
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
