<?php
require_once __DIR__ . '/../../helpers/teacher_auth.php';
require_teacher();
require_once __DIR__ . '/../../helpers/teacher_guard.php';
require_teacher_active();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';
require_once __DIR__ . '/../layout/header.php';

$pdo = db();
$teacher_id = (int)$_SESSION['teacher_id'];
$msg = '';
$error = '';

$bstmt = $pdo->prepare("SELECT id, name FROM batches WHERE teacher_id=? ORDER BY name ASC");
$bstmt->execute([$teacher_id]);
$batches = $bstmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $batch_id = (int)($_POST['batch_id'] ?? 0);
  $class_date = trim($_POST['class_date'] ?? '');
  $start_time = trim($_POST['start_time'] ?? '');
  $end_time = trim($_POST['end_time'] ?? '');
  $topic = trim($_POST['topic'] ?? '');

  if ($batch_id<=0 || $class_date==='' || $start_time==='' || $end_time==='' || $topic==='') {
    $error = "Please fill all fields.";
  } else {
    $chk = $pdo->prepare("SELECT id FROM batches WHERE id=? AND teacher_id=?");
    $chk->execute([$batch_id, $teacher_id]);
    if (!$chk->fetch()) {
      $error = "Invalid batch selection.";
    } else {
      $stmt = $pdo->prepare("INSERT INTO class_schedule (batch_id, class_date, start_time, end_time, topic, status) VALUES (?,?,?,?,?, 'scheduled')");
      $stmt->execute([$batch_id, $class_date, $start_time, $end_time, $topic]);
      $msg = "Class scheduled successfully.";
    }
  }
}

$list = $pdo->prepare("
  SELECT cs.*, b.name AS batch_name
  FROM class_schedule cs
  JOIN batches b ON b.id = cs.batch_id
  WHERE b.teacher_id=?
  ORDER BY cs.class_date DESC, cs.start_time DESC
  LIMIT 25
");
$list->execute([$teacher_id]);
$classes = $list->fetchAll();
?>

<div class="app-shell">
  <?php require_once __DIR__ . '/../layout/sidebar_teacher.php'; ?>
  <div class="content">
    <?php require_once __DIR__ . '/../layout/topbar.php'; ?>

    <div class="page">
      <?php if ($msg): ?><div class="alert alert-success cardx border-0"><?= e($msg) ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert alert-danger cardx border-0"><?= e($error) ?></div><?php endif; ?>

      <div class="grid">
        <div style="grid-column: span 5;">
          <div class="cardx p-4">
            <div class="fw-semibold mb-3">Add Class</div>

            <form method="post">
              <div class="mb-3">
                <label class="form-label">Batch</label>
                <select class="form-select" name="batch_id" required>
                  <option value="">-- Select batch --</option>
                  <?php foreach ($batches as $b): ?>
                    <option value="<?= e($b['id']) ?>"><?= e($b['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Class Date</label>
                <input class="form-control" type="date" name="class_date" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Start Time</label>
                <input class="form-control" type="time" name="start_time" required>
              </div>

              <div class="mb-3">
                <label class="form-label">End Time</label>
                <input class="form-control" type="time" name="end_time" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Topic</label>
                <input class="form-control" name="topic" placeholder="e.g., Algebra - Linear Equations" required>
              </div>

              <button class="btn btn-primary" type="submit">
                <i class="bi bi-calendar-plus me-1"></i> Schedule
              </button>
            </form>
          </div>
        </div>

        <div style="grid-column: span 7;">
          <div class="cardx p-4">
            <div class="fw-semibold mb-3">Recent Classes</div>

            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr class="text-muted">
                    <th>Batch</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Topic</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!$classes): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No classes scheduled yet.</td></tr>
                  <?php endif; ?>

                  <?php foreach ($classes as $c): ?>
                    <tr>
                      <td class="fw-semibold"><?= e($c['batch_name']) ?></td>
                      <td><?= e($c['class_date']) ?></td>
                      <td class="text-muted small"><?= e($c['start_time']) ?> - <?= e($c['end_time']) ?></td>
                      <td><?= e($c['topic']) ?></td>
                      <td><span class="badge text-bg-info"><?= e($c['status']) ?></span></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
