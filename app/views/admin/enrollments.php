<?php
require_once __DIR__ . '/../../helpers/auth.php';
require_admin();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';

$pdo = db();
$error = '';
$msg = '';

// ---------------- CREATE / ENROLL ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
  $student_id = (int)($_POST['student_id'] ?? 0);
  $batch_id   = (int)($_POST['batch_id'] ?? 0);

  if ($student_id <= 0 || $batch_id <= 0) {
    $error = "Select student and batch.";
  } else {
    $stmt = $pdo->prepare("INSERT IGNORE INTO student_enrollments (student_id, batch_id, status) VALUES (?, ?, 'active')");
    $stmt->execute([$student_id, $batch_id]);
    header("Location: index.php?page=admin_enrollments&ok=created");
    exit;
  }
}

// ---------------- STATUS TOGGLE ----------------
if (isset($_GET['toggle'])) {
  $id = (int)$_GET['toggle'];
  if ($id > 0) {
    $cur = $pdo->prepare("SELECT status FROM student_enrollments WHERE id=?");
    $cur->execute([$id]);
    $row = $cur->fetch();

    if ($row) {
      $new = ($row['status'] === 'active') ? 'inactive' : 'active';
      $upd = $pdo->prepare("UPDATE student_enrollments SET status=? WHERE id=?");
      $upd->execute([$new, $id]);
    }
  }
  header("Location: index.php?page=admin_enrollments&ok=toggled");
  exit;
}

// ---------------- DELETE (Optional) ----------------
// Safer to keep history (toggle inactive), but delete is here if you want.
if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];
  if ($id > 0) {
    $pdo->prepare("DELETE FROM student_enrollments WHERE id=?")->execute([$id]);
  }
  header("Location: index.php?page=admin_enrollments&ok=deleted");
  exit;
}

// ---------------- FILTERS ----------------
$f_student = (int)($_GET['student_id'] ?? 0);
$f_batch   = (int)($_GET['batch_id'] ?? 0);
$f_status  = trim($_GET['status'] ?? ''); // active / inactive / ''

// dropdown lists
$students = $pdo->query("SELECT id, name, email FROM students ORDER BY name")->fetchAll();
$batches  = $pdo->query("SELECT id, name FROM batches ORDER BY name")->fetchAll();

// query enrollments with filters
$sql = "
  SELECT se.*, s.name AS student_name, s.email AS student_email, b.name AS batch_name
  FROM student_enrollments se
  JOIN students s ON s.id = se.student_id
  JOIN batches b ON b.id = se.batch_id
  WHERE 1=1
";
$params = [];

if ($f_student > 0) { $sql .= " AND se.student_id=?"; $params[] = $f_student; }
if ($f_batch > 0)   { $sql .= " AND se.batch_id=?";   $params[] = $f_batch; }
if ($f_status !== '' && in_array($f_status, ['active','inactive'], true)) {
  $sql .= " AND se.status=?"; $params[] = $f_status;
}

$sql .= " ORDER BY se.id DESC LIMIT 300";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$enrollments = $stmt->fetchAll();

require_once __DIR__ . '/../layout/header.php';
?>

<div class="app-shell">
  <?php require_once __DIR__ . '/../layout/sidebar.php'; ?>
  <div class="content">
    <?php require_once __DIR__ . '/../layout/topbar.php'; ?>

    <div class="page">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
          <div class="fw-bold fs-4">Enrollments</div>
          <div class="text-muted">Enroll students into batches and manage access.</div>
        </div>
      </div>

      <?php if (!empty($_GET['ok'])): ?>
        <div class="alert alert-success cardx border-0">
          Action completed: <?= e($_GET['ok']) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger cardx border-0"><?= e($error) ?></div>
      <?php endif; ?>

      <div class="grid">
        <!-- Create Enrollment -->
        <div style="grid-column: span 4;">
          <div class="cardx p-4">
            <div class="fw-semibold mb-3">Enroll Student</div>

            <form method="post">
              <input type="hidden" name="action" value="create">

              <div class="mb-3">
                <label class="form-label">Student</label>
                <select class="form-select" name="student_id" required>
                  <option value="">-- Select student --</option>
                  <?php foreach ($students as $s): ?>
                    <option value="<?= (int)$s['id'] ?>">
                      <?= e($s['name']) ?> (<?= e($s['email']) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
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

              <button class="btn btn-primary" type="submit">
                <i class="bi bi-check2-circle me-1"></i> Enroll
              </button>

              <div class="text-muted small mt-3">
                Tip: If already enrolled, this will not create duplicates.
              </div>
            </form>
          </div>

          <!-- Filters -->
          <div class="cardx p-4 mt-3">
            <div class="fw-semibold mb-3">Filters</div>

            <form method="get">
              <input type="hidden" name="page" value="admin_enrollments">

              <div class="mb-3">
                <label class="form-label">Student</label>
                <select class="form-select" name="student_id">
                  <option value="">All</option>
                  <?php foreach ($students as $s): ?>
                    <option value="<?= (int)$s['id'] ?>" <?= ($f_student === (int)$s['id']) ? 'selected' : '' ?>>
                      <?= e($s['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Batch</label>
                <select class="form-select" name="batch_id">
                  <option value="">All</option>
                  <?php foreach ($batches as $b): ?>
                    <option value="<?= (int)$b['id'] ?>" <?= ($f_batch === (int)$b['id']) ? 'selected' : '' ?>>
                      <?= e($b['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                  <option value="" <?= ($f_status === '') ? 'selected' : '' ?>>All</option>
                  <option value="active" <?= ($f_status === 'active') ? 'selected' : '' ?>>Active</option>
                  <option value="inactive" <?= ($f_status === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                </select>
              </div>

              <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" type="submit">
                  <i class="bi bi-funnel me-1"></i> Apply
                </button>
                <a class="btn btn-outline-secondary" href="index.php?page=admin_enrollments">
                  Reset
                </a>
              </div>
            </form>
          </div>
        </div>

        <!-- Enrollment Table -->
        <div style="grid-column: span 8;">
          <div class="cardx p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div class="fw-semibold">All Enrollments</div>
              <div class="text-muted small"><?= count($enrollments) ?> showing</div>
            </div>

            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr class="text-muted">
                    <th>#</th>
                    <th>Student</th>
                    <th>Batch</th>
                    <th>Status</th>
                    <th>Enrolled On</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!$enrollments): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No enrollments found.</td></tr>
                  <?php endif; ?>

                  <?php foreach ($enrollments as $eRow): ?>
                    <?php
                      $badge = ($eRow['status'] === 'active') ? 'text-bg-success' : 'text-bg-secondary';
                      $toggleText = ($eRow['status'] === 'active') ? 'Deactivate' : 'Activate';
                      $toggleIcon = ($eRow['status'] === 'active') ? 'bi-x-circle' : 'bi-check-circle';
                    ?>
                    <tr>
                      <td><?= e($eRow['id']) ?></td>
                      <td>
                        <div class="fw-semibold"><?= e($eRow['student_name']) ?></div>
                        <div class="text-muted small"><?= e($eRow['student_email']) ?></div>
                      </td>
                      <td class="fw-semibold"><?= e($eRow['batch_name']) ?></td>
                      <td><span class="badge <?= $badge ?>"><?= e($eRow['status']) ?></span></td>
                      <td class="text-muted small"><?= e($eRow['enrolled_on']) ?></td>
                      <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary"
                           href="index.php?page=admin_enrollments&toggle=<?= (int)$eRow['id'] ?>"
                           title="<?= $toggleText ?>">
                          <i class="bi <?= $toggleIcon ?>"></i>
                        </a>

                        <a class="btn btn-sm btn-outline-danger"
                           href="index.php?page=admin_enrollments&delete=<?= (int)$eRow['id'] ?>"
                           onclick="return confirm('Delete this enrollment record permanently? (Recommended: deactivate instead)')"
                           title="Delete">
                          <i class="bi bi-trash"></i>
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <div class="text-muted small">
              Best practice: use Activate/Deactivate instead of deleting, to keep history.
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
