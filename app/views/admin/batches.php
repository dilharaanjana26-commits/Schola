<?php
require_once __DIR__ . '/../../helpers/auth.php';
require_admin();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';

$pdo = db();
$error = '';

// -------- Fetch teachers for dropdown --------
$teachers = $pdo->query("SELECT id, name, email FROM teachers ORDER BY name ASC")->fetchAll();

// -------- Create Batch --------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
  $name = trim($_POST['name'] ?? '');
  $teacher_id = (int)($_POST['teacher_id'] ?? 0);
  $fee_amount = trim($_POST['fee_amount'] ?? '0');

  if ($name === '') {
    $error = "Batch name is required.";
  } else {
    // allow teacher_id to be NULL
    $teacherValue = ($teacher_id > 0) ? $teacher_id : null;

    $stmt = $pdo->prepare("INSERT INTO batches (name, teacher_id, fee_amount) VALUES (?,?,?)");
    $stmt->execute([$name, $teacherValue, (float)$fee_amount]);

    header("Location: index.php?page=admin_batches&ok=created");
    exit;
  }
}

// -------- Update Batch --------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
  $id = (int)($_POST['id'] ?? 0);
  $name = trim($_POST['name'] ?? '');
  $teacher_id = (int)($_POST['teacher_id'] ?? 0);
  $fee_amount = trim($_POST['fee_amount'] ?? '0');

  if ($id <= 0 || $name === '') {
    $error = "Batch name is required.";
  } else {
    $teacherValue = ($teacher_id > 0) ? $teacher_id : null;

    $stmt = $pdo->prepare("UPDATE batches SET name=?, teacher_id=?, fee_amount=? WHERE id=?");
    $stmt->execute([$name, $teacherValue, (float)$fee_amount, $id]);

    header("Location: index.php?page=admin_batches&ok=updated");
    exit;
  }
}

// -------- Delete Batch --------
if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];
  if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM batches WHERE id=?");
    $stmt->execute([$id]);
  }
  header("Location: index.php?page=admin_batches&ok=deleted");
  exit;
}

// -------- Edit Mode --------
$editBatch = null;
if (isset($_GET['edit'])) {
  $id = (int)$_GET['edit'];
  $stmt = $pdo->prepare("SELECT * FROM batches WHERE id=?");
  $stmt->execute([$id]);
  $editBatch = $stmt->fetch();
}

// -------- Fetch Batch List (with teacher name) --------
$batches = $pdo->query("
  SELECT b.*, t.name AS teacher_name, t.email AS teacher_email
  FROM batches b
  LEFT JOIN teachers t ON t.id = b.teacher_id
  ORDER BY b.id DESC
")->fetchAll();

require_once __DIR__ . '/../layout/header.php';
?>
<div class="app-shell">
  <?php require_once __DIR__ . '/../layout/sidebar.php'; ?>
  <div class="content">
    <?php require_once __DIR__ . '/../layout/topbar.php'; ?>

    <div class="page">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
          <div class="fw-bold fs-4">Batches</div>
          <div class="text-muted">Create batches, assign teachers, and manage batch fees.</div>
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
              <?= $editBatch ? "Edit Batch" : "Add New Batch" ?>
            </div>

            <form method="post">
              <?php if ($editBatch): ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= e($editBatch['id']) ?>">
              <?php else: ?>
                <input type="hidden" name="action" value="create">
              <?php endif; ?>

              <div class="mb-3">
                <label class="form-label">Batch Name</label>
                <input class="form-control" name="name" required
                       value="<?= e($editBatch['name'] ?? '') ?>"
                       placeholder="e.g., Grade 10 - Maths (Morning)">
              </div>

              <div class="mb-3">
                <label class="form-label">Assign Teacher</label>
                <select class="form-select" name="teacher_id">
                  <option value="0">-- Not assigned yet --</option>
                  <?php foreach ($teachers as $t): ?>
                    <?php
                      $selected = '';
                      if ($editBatch && (int)$editBatch['teacher_id'] === (int)$t['id']) $selected = 'selected';
                    ?>
                    <option value="<?= e($t['id']) ?>" <?= $selected ?>>
                      <?= e($t['name']) ?> (<?= e($t['email']) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
                <div class="text-muted small mt-1">You can assign later.</div>
              </div>

              <div class="mb-3">
                <label class="form-label">Batch Fee (LKR)</label>
                <input class="form-control" type="number" step="0.01" name="fee_amount"
                       value="<?= e($editBatch['fee_amount'] ?? '0') ?>">
              </div>

              <div class="d-flex gap-2">
                <button class="btn btn-primary" type="submit">
                  <i class="bi bi-check2-circle me-1"></i>
                  <?= $editBatch ? "Update" : "Create" ?>
                </button>

                <?php if ($editBatch): ?>
                  <a class="btn btn-outline-secondary" href="index.php?page=admin_batches">Cancel</a>
                <?php endif; ?>
              </div>
            </form>
          </div>
        </div>

        <!-- Table -->
        <div style="grid-column: span 8;">
          <div class="cardx p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div class="fw-semibold">All Batches</div>
              <div class="text-muted small"><?= count($batches) ?> total</div>
            </div>

            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr class="text-muted">
                    <th>#</th>
                    <th>Batch</th>
                    <th>Teacher</th>
                    <th>Fee (LKR)</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!$batches): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No batches found.</td></tr>
                  <?php endif; ?>

                  <?php foreach ($batches as $b): ?>
                    <tr>
                      <td><?= e($b['id']) ?></td>
                      <td class="fw-semibold"><?= e($b['name']) ?></td>
                      <td>
                        <?php if (!empty($b['teacher_name'])): ?>
                          <div class="fw-semibold"><?= e($b['teacher_name']) ?></div>
                          <div class="text-muted small"><?= e($b['teacher_email']) ?></div>
                        <?php else: ?>
                          <span class="text-muted">Not assigned</span>
                        <?php endif; ?>
                      </td>
                      <td><?= money($b['fee_amount']) ?></td>
                      <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="index.php?page=admin_batches&edit=<?= e($b['id']) ?>">
                          <i class="bi bi-pencil"></i>
                        </a>
                        <a class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Delete this batch?')"
                           href="index.php?page=admin_batches&delete=<?= e($b['id']) ?>">
                          <i class="bi bi-trash"></i>
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <div class="text-muted small">
              Tip: You can assign a teacher anytime by editing a batch.
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
