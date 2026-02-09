<?php
require_once __DIR__ . '/../../helpers/auth.php';
require_admin();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';

$pdo = db();
$error = '';

// ------------------ Actions approve/reject ------------------
if (isset($_GET['action'], $_GET['id']) && in_array($_GET['action'], ['approve','reject'], true)) {
  $id = (int)$_GET['id'];
  $action = $_GET['action'];

  $stmt = $pdo->prepare("SELECT * FROM student_payments WHERE id=? LIMIT 1");
  $stmt->execute([$id]);
  $pay = $stmt->fetch();

  if (!$pay) {
    $error = "Payment record not found.";
  } else {
    if ($action === 'approve') {
      $stmt = $pdo->prepare("UPDATE student_payments SET status='approved', paid_on=NOW() WHERE id=?");
      $stmt->execute([$id]);
      header("Location: index.php?page=admin_student_payments&ok=approved");
      exit;
    }

    if ($action === 'reject') {
      $stmt = $pdo->prepare("UPDATE student_payments SET status='rejected' WHERE id=?");
      $stmt->execute([$id]);
      header("Location: index.php?page=admin_student_payments&ok=rejected");
      exit;
    }
  }
}

// ------------------ Filter ------------------
$status = $_GET['status'] ?? 'pending';
$allowed = ['pending','approved','rejected','all'];
if (!in_array($status, $allowed, true)) $status = 'pending';

$where = "";
$params = [];
if ($status !== 'all') {
  $where = "WHERE sp.status = ?";
  $params[] = $status;
}

$stmt = $pdo->prepare("
  SELECT sp.*,
         s.name AS student_name, s.email AS student_email, s.whatsapp AS student_whatsapp,
         b.name AS batch_name
  FROM student_payments sp
  JOIN students s ON s.id = sp.student_id
  JOIN batches b ON b.id = sp.batch_id
  $where
  ORDER BY sp.id DESC
");
$stmt->execute($params);
$payments = $stmt->fetchAll();

require_once __DIR__ . '/../layout/header.php';
?>
<div class="app-shell">
  <?php require_once __DIR__ . '/../layout/sidebar.php'; ?>
  <div class="content">
    <?php require_once __DIR__ . '/../layout/topbar.php'; ?>

    <div class="page">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
          <div class="fw-bold fs-4">Student Payments</div>
          <div class="text-muted">Approve or reject student batch fee payments.</div>
        </div>
      </div>

      <?php if (!empty($_GET['ok'])): ?>
        <div class="alert alert-success cardx border-0">
          Payment <?= e($_GET['ok']) ?> successfully.
        </div>
      <?php endif; ?>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger cardx border-0">
          <?= e($error) ?>
        </div>
      <?php endif; ?>

      <!-- Filters -->
      <div class="cardx p-3 mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
          <div class="fw-semibold">Filter</div>
          <div class="d-flex flex-wrap gap-2">
            <?php
              function filterBtn2($label, $value, $current) {
                $active = ($value === $current) ? 'btn-primary' : 'btn-outline-secondary';
                $url = "index.php?page=admin_student_payments&status=" . urlencode($value);
                echo '<a class="btn btn-sm '.$active.'" href="'.$url.'">'.$label.'</a>';
              }
              filterBtn2('Pending', 'pending', $status);
              filterBtn2('Approved', 'approved', $status);
              filterBtn2('Rejected', 'rejected', $status);
              filterBtn2('All', 'all', $status);
            ?>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="cardx p-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div class="fw-semibold">Payment Requests</div>
          <div class="text-muted small"><?= count($payments) ?> records</div>
        </div>

        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr class="text-muted">
                <th>#</th>
                <th>Student</th>
                <th>Batch</th>
                <th>Fee</th>
                <th>Conv. (5%)</th>
                <th>Total</th>
                <th>Type</th>
                <th>Proof</th>
                <th>Status</th>
                <th>Created</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!$payments): ?>
                <tr>
                  <td colspan="11" class="text-center text-muted py-4">No payment records found.</td>
                </tr>
              <?php endif; ?>

              <?php foreach ($payments as $p): ?>
                <tr>
                  <td><?= e($p['id']) ?></td>
                  <td>
                    <div class="fw-semibold"><?= e($p['student_name']) ?></div>
                    <div class="text-muted small">
                      <?= e($p['student_email']) ?>
                      <?= !empty($p['student_whatsapp']) ? ' • '.e($p['student_whatsapp']) : '' ?>
                    </div>
                  </td>
                  <td class="fw-semibold"><?= e($p['batch_name']) ?></td>
                  <td>LKR <?= money($p['amount']) ?></td>
                  <td>LKR <?= money($p['convenience_fee']) ?></td>
                  <td><b>LKR <?= money($p['total_amount']) ?></b></td>
                  <td><?= e($p['payment_type']) ?></td>
                  <td>
                    <?php if (!empty($p['proof'])): ?>
                      <a href="<?= e($p['proof']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-paperclip me-1"></i> View
                      </a>
                    <?php else: ?>
                      <span class="text-muted">—</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php
                      $badge = 'text-bg-secondary';
                      if ($p['status'] === 'pending') $badge = 'text-bg-warning';
                      if ($p['status'] === 'approved') $badge = 'text-bg-success';
                      if ($p['status'] === 'rejected') $badge = 'text-bg-danger';
                    ?>
                    <span class="badge <?= $badge ?>"><?= e($p['status']) ?></span>
                  </td>
                  <td class="text-muted small"><?= e($p['created_at']) ?></td>
                  <td class="text-end">
                    <?php if ($p['status'] === 'pending'): ?>
                      <a class="btn btn-sm btn-success"
                         onclick="return confirm('Approve this student payment?')"
                         href="index.php?page=admin_student_payments&action=approve&id=<?= e($p['id']) ?>">
                        <i class="bi bi-check2-circle"></i>
                      </a>
                      <a class="btn btn-sm btn-danger"
                         onclick="return confirm('Reject this student payment?')"
                         href="index.php?page=admin_student_payments&action=reject&id=<?= e($p['id']) ?>">
                        <i class="bi bi-x-circle"></i>
                      </a>
                    <?php else: ?>
                      <span class="text-muted">No actions</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="text-muted small mt-2">
          Approving a student payment allows the student to access batch content (we’ll enforce access next).
        </div>
      </div>

    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
