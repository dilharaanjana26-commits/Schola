<?php
require_once __DIR__ . '/../../helpers/student_auth.php';
require_student();

require_once __DIR__ . '/../../helpers/student_guard.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';
require_once __DIR__ . '/../layout/header.php';

$pdo = db();
$student_id = (int)$_SESSION['student_id'];
$batch_id = (int)($_GET['batch_id'] ?? 0);

if ($batch_id <= 0) {
  header("Location: index.php?page=student_content");
  exit;
}

require_student_batch_access($student_id, $batch_id);

$bstmt = $pdo->prepare("SELECT * FROM batches WHERE id=? LIMIT 1");
$bstmt->execute([$batch_id]);
$batch = $bstmt->fetch();
if (!$batch) {
  header("Location: index.php?page=student_content");
  exit;
}

$tutes = $pdo->prepare("SELECT * FROM tutes WHERE batch_id=? ORDER BY id DESC LIMIT 30");
$tutes->execute([$batch_id]);
$tuteList = $tutes->fetchAll();

$videos = $pdo->prepare("SELECT * FROM recorded_videos WHERE batch_id=? ORDER BY id DESC LIMIT 30");
$videos->execute([$batch_id]);
$videoList = $videos->fetchAll();

$live = $pdo->prepare("SELECT * FROM live_classes WHERE batch_id=? ORDER BY id DESC LIMIT 10");
$live->execute([$batch_id]);
$liveList = $live->fetchAll();

$sch = $pdo->prepare("SELECT * FROM class_schedule WHERE batch_id=? ORDER BY class_date DESC, start_time DESC LIMIT 30");
$sch->execute([$batch_id]);
$schedule = $sch->fetchAll();
?>

<div class="app-shell">
  <?php require_once __DIR__ . '/../layout/sidebar_student.php'; ?>

  <div class="content">
    <?php require_once __DIR__ . '/../layout/topbar.php'; ?>

    <div class="page">
      <div class="cardx p-4 mb-3">
        <div class="fw-bold fs-4"><?= e($batch['name']) ?></div>
        <div class="text-muted">Materials, recordings, live classes & timetable.</div>
      </div>

      <div class="grid">
        <div style="grid-column: span 6;">
          <div class="cardx p-4">
            <div class="fw-semibold mb-3">Tutes / Study Materials</div>

            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr class="text-muted">
                    <th>Title</th>
                    <th>File</th>
                    <th>Uploaded</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!$tuteList): ?>
                    <tr><td colspan="3" class="text-center text-muted py-4">No tutes yet.</td></tr>
                  <?php endif; ?>
                  <?php foreach ($tuteList as $t): ?>
                    <tr>
                      <td class="fw-semibold"><?= e($t['title']) ?></td>
                      <td>
                        <a class="btn btn-sm btn-outline-primary" target="_blank" href="<?= e($t['file_path']) ?>">
                          <i class="bi bi-file-earmark me-1"></i> Open
                        </a>
                      </td>
                      <td class="text-muted small"><?= e($t['created_at'] ?? '') ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

          </div>
        </div>

        <div style="grid-column: span 6;">
          <div class="cardx p-4">
            <div class="fw-semibold mb-3">Recorded Videos</div>

            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr class="text-muted">
                    <th>Title</th>
                    <th>Link</th>
                    <th>Added</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!$videoList): ?>
                    <tr><td colspan="3" class="text-center text-muted py-4">No recorded videos yet.</td></tr>
                  <?php endif; ?>
                  <?php foreach ($videoList as $v): ?>
                    <tr>
                      <td class="fw-semibold"><?= e($v['title']) ?></td>
                      <td>
                        <a class="btn btn-sm btn-outline-primary" target="_blank" href="<?= e($v['video_url']) ?>">
                          <i class="bi bi-play-circle me-1"></i> Watch
                        </a>
                      </td>
                      <td class="text-muted small"><?= e($v['created_at'] ?? '') ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

          </div>
        </div>

        <div style="grid-column: span 6;">
          <div class="cardx p-4">
            <div class="fw-semibold mb-3">Live Classes</div>

            <?php if (!$liveList): ?>
              <div class="text-muted">No live class links yet.</div>
            <?php endif; ?>

            <?php foreach ($liveList as $l): ?>
              <div class="border rounded-4 p-3 mb-3">
                <div class="text-muted small mb-2">Schedule: <?= e($l['schedule_date'] ?? '—') ?></div>
                <a class="btn btn-sm btn-outline-primary mb-2" target="_blank" href="<?= e($l['youtube_embed_url']) ?>">
                  <i class="bi bi-youtube me-1"></i> Open Live
                </a>

                <div class="ratio ratio-16x9 mt-2">
                  <iframe src="<?= e($l['youtube_embed_url']) ?>" allowfullscreen></iframe>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div style="grid-column: span 6;">
          <div class="cardx p-4">
            <div class="fw-semibold mb-3">Class Timetable</div>

            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr class="text-muted">
                    <th>Date</th>
                    <th>Time</th>
                    <th>Topic</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!$schedule): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">No timetable entries yet.</td></tr>
                  <?php endif; ?>
                  <?php foreach ($schedule as $c): ?>
                    <tr>
                      <td><?= e($c['class_date']) ?></td>
                      <td class="text-muted small"><?= e($c['start_time']) ?> - <?= e($c['end_time']) ?></td>
                      <td class="fw-semibold"><?= e($c['topic']) ?></td>
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
