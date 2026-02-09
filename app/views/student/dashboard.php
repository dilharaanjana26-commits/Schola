<?php
require_once __DIR__ . '/../../helpers/student_auth.php';
require_student();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';
require_once __DIR__ . '/../layout/header.php';

$pdo = db();
$student_id = (int)$_SESSION['student_id'];

// Metrics
$totalBatches = (int)($pdo->query("SELECT COUNT(*) c FROM batches")->fetch()['c'] ?? 0);

$paid = $pdo->prepare("SELECT COUNT(DISTINCT batch_id) c FROM student_payments WHERE student_id=? AND status='approved'");
$paid->execute([$student_id]);
$activeBatches = (int)($paid->fetch()['c'] ?? 0);

$pending = $pdo->prepare("SELECT COUNT(*) c FROM student_payments WHERE student_id=? AND status='pending'");
$pending->execute([$student_id]);
$pendingCount = (int)($pending->fetch()['c'] ?? 0);

$upcoming = $pdo->prepare("
  SELECT COUNT(*) c
  FROM class_schedule cs
  JOIN student_payments sp ON sp.batch_id = cs.batch_id
  WHERE sp.student_id=? AND sp.status='approved'
    AND cs.class_date >= CURDATE()
");
$upcoming->execute([$student_id]);
$upcomingCount = (int)($upcoming->fetch()['c'] ?? 0);

// Recent payments
$payments = $pdo->prepare("
  SELECT sp.*, b.name AS batch_name
  FROM student_payments sp
  JOIN batches b ON b.id = sp.batch_id
  WHERE sp.student_id=?
  ORDER BY sp.id DESC
  LIMIT 8
");
$payments->execute([$student_id]);
$recentPays = $payments->fetchAll();
?>

<div class="app-shell">
  <?php require_once __DIR__ . '/../layout/sidebar_student.php'; ?>

  <div class="content">
    <?php require_once __DIR__ . '/../layout/topbar.php'; ?>

    <div class="page">
      <div class="cardx p-4 mb-4">
        <div class="fw-bold fs-4">Student Dashboard</div>
        <div class="text-muted">Welcome back, <?= e($_SESSION['student_name']) ?>.</div>
      </div>

      <div class="grid">
        <div style="grid-column: span 3;">
          <div class="cardx p-4">
            <div class="text-muted small">Available Batches</div>
            <div class="fw-bold fs-3"><?= $totalBatches ?></div>
            <div class="text-muted small">Institute total</div>
          </div>
        </div>

        <div style="grid-column: span 3;">
          <div class="cardx p-4">
            <div class="text-muted small">Active Access</div>
            <div class="fw-bold fs-3"><?= $activeBatches ?></div>
            <div class="text-muted small">Paid & approved</div>
          </div>
        </div>

        <div style="grid-column: span 3;">
          <div class="cardx p-4">
            <div class="text-muted small">Pending Payments</div>
            <div class="fw-bold fs-3"><?= $pendingCount ?></div>
            <div class="text-muted small">Waiting approval</div>
          </div>
        </div>

        <div style="grid-column: span 3;">
          <div class="cardx p-4">
            <div class="text-muted small">Upcoming Classes</div>
            <div class="fw-bold fs-3"><?= $upcomingCount ?></div>
            <div class="text-muted small">From active batches</div>
          </div>
        </div>

        <div style="grid-column: span 12;">
          <div class="cardx p-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
              <div class="fw-semibold">Quick Actions</div>
              <div class="text-muted small">Open content or pay fees to unlock classes.</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
              <a class="btn btn-outline-primary" href="index.php?page=student_content">
                <i class="bi bi-collection-play me-1"></i> My Content
              </a>
              <a class="btn btn-outline-primary" href="index.php?page=student_content">
                <i class="bi bi-credit-card me-1"></i> Pay Fees
              </a>
              <a class="btn btn-outline-secondary" href="index.php?page=student_logout">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
              </a>
            </div>
          </div>
        </div>

        <div style="grid-column: span 12;">
          <div class="cardx p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div class="fw-semibold">Recent Payments</div>
              <a class="btn btn-sm btn-outline-primary" href="index.php?page=student_content">
                View Batches
              </a>
            </div>

            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr class="text-muted">
                    <th>Batch</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Created</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!$recentPays): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">No payments yet.</td></tr>
                  <?php endif; ?>

                  <?php foreach ($recentPays as $p): ?>
                    <tr>
                      <td class="fw-semibold"><?= e($p['batch_name']) ?></td>
                      <td>LKR <?= money($p['total_amount']) ?></td>
                      <td>
                        <?php
                          $badge = 'text-bg-secondary';
                          if ($p['status'] === 'pending') $badge = 'text-bg-warning';
                          if ($p['status'] === 'approved') $badge = 'text-bg-success';
                          if ($p['status'] === 'rejected') $badge = 'text-bg-danger';
                        ?>
                        <span class="badge <?= $badge ?>"><?= e($p['status']) ?></span>
                      </td>
                      <td class="text-muted small"><?= e($p['created_at'] ?? '') ?></td>
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
