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
  $embed = trim($_POST['youtube_embed_url'] ?? '');
  $schedule_date = trim($_POST['schedule_date'] ?? '');
  $zoom_link = trim($_POST['zoom_link'] ?? '');

  if ($batch_id <= 0 || $embed === '') {
    $error = "Please select a batch and enter embed URL.";
  } else {
    $chk = $pdo->prepare("SELECT id FROM batches WHERE id=? AND teacher_id=?");
    $chk->execute([$batch_id, $teacher_id]);
    if (!$chk->fetch()) {
      $error = "Invalid batch selection.";
    } else {
      $stmt = $pdo->prepare("INSERT INTO live_classes (batch_id, youtube_embed_url, zoom_link, schedule_date) VALUES (?, ?, ?, ?)");
      $stmt->execute([$batch_id, $embed, ($zoom_link ?: null), ($schedule_date ?: null)]);
      $msg = "Live class embed added successfully.";
    }
  }
}

$list = $pdo->prepare("
  SELECT lc.*, b.name AS batch_name
  FROM live_classes lc
  JOIN batches b ON b.id = lc.batch_id
  WHERE b.teacher_id=?
  ORDER BY lc.id DESC
  LIMIT 20
");
$list->execute([$teacher_id]);
$items = $list->fetchAll();
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
            <div class="fw-semibold mb-3">Add Live Embed</div>

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
                <label class="form-label">YouTube Embed URL</label>
                <input class="form-control" name="youtube_embed_url"
                       placeholder="https://www.youtube.com/embed/VIDEO_ID" required>
                <div class="text-muted small mt-1">Use embed URL format.</div>
              </div>

              <div class="mb-3">
                <label class="form-label">Zoom Meeting Link (optional)</label>
                <input class="form-control" name="zoom_link"
                       placeholder="https://zoom.us/j/MEETING_ID">
              </div>

              <div class="mb-3">
                <label class="form-label">Schedule Date/Time (optional)</label>
                <input class="form-control" type="datetime-local" name="schedule_date">
              </div>

              <button class="btn btn-primary" type="submit">
                <i class="bi bi-youtube me-1"></i> Save
              </button>
            </form>
          </div>
        </div>

        <div style="grid-column: span 7;">
          <div class="cardx p-4">
            <div class="fw-semibold mb-3">Recent Live Embeds</div>

            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr class="text-muted">
                    <th>Batch</th>
                    <th>Schedule</th>
                    <th>YouTube</th>
                    <th>Zoom</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!$items): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">No live embeds added yet.</td></tr>
                  <?php endif; ?>
                  <?php foreach ($items as $i): ?>
                    <tr>
                      <td class="fw-semibold"><?= e($i['batch_name']) ?></td>
                      <td class="text-muted small"><?= e($i['schedule_date'] ?? '—') ?></td>
                      <td>
                        <a class="btn btn-sm btn-outline-primary" target="_blank" href="<?= e($i['youtube_embed_url']) ?>">
                          <i class="bi bi-box-arrow-up-right me-1"></i> Open
                        </a>
                      </td>
                      <td>
                        <?php if (!empty($i['zoom_link'])): ?>
                          <a class="btn btn-sm btn-outline-success" target="_blank" href="<?= e($i['zoom_link']) ?>">
                            <i class="bi bi-camera-video me-1"></i> Join
                          </a>
                        <?php else: ?>
                          <span class="text-muted small">—</span>
                        <?php endif; ?>
                      </td>
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
