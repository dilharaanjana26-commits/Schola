<?php
require_once __DIR__ . '/../../helpers/student_auth.php';
require_student();

require_once __DIR__ . '/../../helpers/student_guard.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';
require_once __DIR__ . '/../layout/header.php';

$pdo = db();
$student_id = (int)$_SESSION['student_id'];

$batches = $pdo->prepare("
  SELECT b.id, b.name, b.fee_amount
  FROM student_enrollments se
  JOIN batches b ON b.id = se.batch_id
  WHERE se.student_id=? AND se.status='active'
  ORDER BY b.name
");
$batches->execute([$student_id]);
$batches = $batches->fetchAll();

?>

<div class="app-shell">
  <?php require_once __DIR__ . '/../layout/sidebar_student.php'; ?>

  <div class="content">
    <?php require_once __DIR__ . '/../layout/topbar.php'; ?>

    <div class="page">
      <div class="cardx p-4 mb-3">
        <div class="fw-bold fs-4">My Content</div>
        <div class="text-muted">Batches are unlocked only after admin approves your payment.</div>
      </div>

      <div class="cardx p-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div class="fw-semibold">Batches</div>
          <div class="text-muted small"><?= count($batches) ?> total</div>
        </div>

        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr class="text-muted">
                <th>Batch</th>
                <th>Fee</th>
                <th>Status</th>
                <th class="text-end">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!$batches): ?>
                <tr><td colspan="4" class="text-center text-muted py-4">No batches available.</td></tr>
              <?php endif; ?>

              <?php foreach ($batches as $b): ?>
                <?php $ok = student_has_batch_access($student_id, (int)$b['id']); ?>
                <tr>
                  <td class="fw-semibold"><?= e($b['name']) ?></td>
                  <td>LKR <?= money($b['fee_amount']) ?></td>
                  <td>
                    <?php if ($ok): ?>
                      <span class="badge text-bg-success">Active</span>
                    <?php else: ?>
                      <span class="badge text-bg-warning">Locked</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end">
                    <?php if ($ok): ?>
                      <a class="btn btn-sm btn-primary"
                         href="index.php?page=student_batch_content&batch_id=<?= (int)$b['id'] ?>">
                        <i class="bi bi-play-circle me-1"></i> Open
                      </a>
                    <?php else: ?>
                      <a class="btn btn-sm btn-outline-primary"
                         href="index.php?page=student_payment&batch_id=<?= (int)$b['id'] ?>">
                        <i class="bi bi-credit-card me-1"></i> Pay Fee
                      </a>
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

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
