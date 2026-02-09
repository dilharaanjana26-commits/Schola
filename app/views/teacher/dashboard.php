<?php
require_once __DIR__ . '/../../helpers/teacher_auth.php';
require_teacher();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';
require_once __DIR__ . '/../layout/header.php';

$pdo = db();
$teacher_id = (int)$_SESSION['teacher_id'];

$stmt = $pdo->prepare("SELECT subscription_status, subscription_expiry FROM teachers WHERE id=? LIMIT 1");
$stmt->execute([$teacher_id]);
$t = $stmt->fetch();

$status = $t['subscription_status'] ?? 'pending';
$expiry = $t['subscription_expiry'] ?? null;

$badge = 'text-bg-secondary';
if ($status === 'active') $badge = 'text-bg-success';
if ($status === 'pending') $badge = 'text-bg-warning';
if ($status === 'expired') $badge = 'text-bg-danger';

$batches = $pdo->prepare("SELECT COUNT(*) c FROM batches WHERE teacher_id=?");
$batches->execute([$teacher_id]);
$batchCount = (int)($batches->fetch()['c'] ?? 0);

$tutes = $pdo->prepare("SELECT COUNT(*) c FROM tutes t JOIN batches b ON b.id=t.batch_id WHERE b.teacher_id=?");
$tutes->execute([$teacher_id]);
$tuteCount = (int)($tutes->fetch()['c'] ?? 0);

$classes = $pdo->prepare("SELECT COUNT(*) c FROM class_schedule cs JOIN batches b ON b.id=cs.batch_id WHERE b.teacher_id=?");
$classes->execute([$teacher_id]);
$classCount = (int)($classes->fetch()['c'] ?? 0);
?>

<div class="app-shell">
  <?php require_once __DIR__ . '/../layout/sidebar_teacher.php'; ?>

  <div class="content">
    <?php require_once __DIR__ . '/../layout/topbar.php'; ?>

    <div class="page">
      <div class="cardx p-4 mb-4">
        <div class="fw-bold fs-4">Teacher Dashboard</div>
        <div class="text-muted">Quick snapshot of your teaching activity.</div>
      </div>

      <div class="cardx p-4 mb-4">
        <div class="fw-semibold">Quick Overview</div>
        <div class="text-muted small">Subscription, batches, materials, and scheduled classes.</div>
      </div>

      <div class="grid">
        <div style="grid-column: span 4;">
          <div class="cardx p-4">
            <div class="text-muted small">Subscription Status</div>
            <div class="d-flex align-items-center justify-content-between mt-2">
              <span class="badge <?= $badge ?>"><?= e($status) ?></span>
              <i class="bi bi-shield-check"></i>
            </div>
            <div class="text-muted small mt-2">Expiry: <b><?= e($expiry ?: '—') ?></b></div>
            <div class="mt-3">
              <a class="btn btn-sm btn-outline-primary" href="index.php?page=teacher_subscription">
                <i class="bi bi-cash-stack me-1"></i> Manage Subscription
              </a>
            </div>
          </div>
        </div>

        <div style="grid-column: span 4;">
          <div class="cardx p-4">
            <div class="text-muted small">Assigned Batches</div>
            <div class="fw-bold fs-3"><?= $batchCount ?></div>
            <div class="text-muted small">Groups assigned to you</div>
          </div>
        </div>

        <div style="grid-column: span 4;">
          <div class="cardx p-4">
            <div class="text-muted small">Uploaded Tutes</div>
            <div class="fw-bold fs-3"><?= $tuteCount ?></div>
            <div class="text-muted small">Materials uploaded</div>
            <div class="mt-3">
              <a class="btn btn-sm btn-outline-primary" href="index.php?page=teacher_content">
                <i class="bi bi-folder-plus me-1"></i> Upload Content
              </a>
            </div>
          </div>
        </div>

        <div style="grid-column: span 6;">
          <div class="cardx p-4">
            <div class="text-muted small">Scheduled Classes</div>
            <div class="fw-bold fs-3"><?= $classCount ?></div>
            <div class="text-muted small">Total timetable entries</div>
            <div class="mt-3">
              <a class="btn btn-sm btn-outline-primary" href="index.php?page=teacher_schedule">
                <i class="bi bi-calendar-event me-1"></i> Add Timetable
              </a>
            </div>
          </div>
        </div>

        <div style="grid-column: span 6;">
          <div class="cardx p-4 d-flex align-items-center justify-content-between">
            <div>
              <div class="fw-semibold">Next Steps</div>
              <div class="text-muted small">Upload tutes, add live links and schedule classes.</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
              <a class="btn btn-outline-primary" href="index.php?page=teacher_live">
                <i class="bi bi-youtube me-1"></i> Live Classes
              </a>
              <a class="btn btn-outline-primary" href="index.php?page=teacher_schedule">
                <i class="bi bi-calendar2-week me-1"></i> Schedule
              </a>
            </div>
          </div>
        </div>

      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
