<?php
require_once __DIR__ . '/../../helpers/teacher_auth.php';
require_teacher();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';
require_once __DIR__ . '/../layout/header.php';

$pdo = db();
$teacher_id = (int)$_SESSION['teacher_id'];

$msg = '';
$error = '';

$tstmt = $pdo->prepare("SELECT subscription_status, subscription_expiry FROM teachers WHERE id=? LIMIT 1");
$tstmt->execute([$teacher_id]);
$t = $tstmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $amount = (float)($_POST['amount'] ?? 0);

  if ($amount <= 0) {
    $error = "Enter a valid amount.";
  } elseif (empty($_FILES['proof']['name'])) {
    $error = "Please upload payment proof (JPG/PNG/PDF).";
  } else {
    $file = $_FILES['proof'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
      $error = "Upload failed. Try again.";
    } else {
      $allowedExt = ['jpg','jpeg','png','pdf'];
      $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
      if (!in_array($ext, $allowedExt, true)) {
        $error = "Only JPG, PNG, or PDF allowed.";
      } else {
        $uploadDir = __DIR__ . '/../../../uploads/teacher_proofs/';
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0777, true);

        $safeName = "teacher_" . $teacher_id . "_" . time() . "." . $ext;
        $destPath = $uploadDir . $safeName;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
          $webPath = "uploads/teacher_proofs/" . $safeName;

          $stmt = $pdo->prepare("
            INSERT INTO teacher_payments (teacher_id, amount, payment_type, proof, status)
            VALUES (?, ?, 'manual', ?, 'pending')
          ");
          $stmt->execute([$teacher_id, $amount, $webPath]);

          $msg = "Payment submitted successfully. Waiting for admin approval.";
        } else {
          $error = "Could not save file. Check folder permissions.";
        }
      }
    }
  }
}

$list = $pdo->prepare("SELECT * FROM teacher_payments WHERE teacher_id=? ORDER BY id DESC LIMIT 15");
$list->execute([$teacher_id]);
$payments = $list->fetchAll();
?>

<div class="app-shell">
  <?php require_once __DIR__ . '/../layout/sidebar_teacher.php'; ?>
  <div class="content">
    <?php require_once __DIR__ . '/../layout/topbar.php'; ?>

    <div class="page">

      <?php if ($msg): ?>
        <div class="alert alert-success cardx border-0"><?= e($msg) ?></div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="alert alert-danger cardx border-0"><?= e($error) ?></div>
      <?php endif; ?>

      <div class="grid">
        <div style="grid-column: span 5;">
          <div class="cardx p-4">
            <div class="fw-semibold mb-3">Submit Subscription Payment</div>

            <div class="mb-2 text-muted small">
              Status: <b><?= e($t['subscription_status'] ?? 'pending') ?></b>
            </div>
            <div class="mb-3 text-muted small">
              Expiry: <b><?= e($t['subscription_expiry'] ?? '—') ?></b>
            </div>

            <form method="post" enctype="multipart/form-data">
              <div class="mb-3">
                <label class="form-label">Amount (LKR)</label>
                <input class="form-control" type="number" step="0.01" name="amount" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Upload Proof (JPG/PNG/PDF)</label>
                <input class="form-control" type="file" name="proof" required>
              </div>

              <button class="btn btn-primary" type="submit">
                <i class="bi bi-upload me-1"></i> Submit
              </button>
            </form>

            <div class="text-muted small mt-3">
              Admin will review and approve your subscription payment.
            </div>
          </div>
        </div>

        <div style="grid-column: span 7;">
          <div class="cardx p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div class="fw-semibold">Recent Payments</div>
              <div class="text-muted small"><?= count($payments) ?> records</div>
            </div>

            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr class="text-muted">
                    <th>#</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Proof</th>
                    <th>Created</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!$payments): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No payments yet.</td></tr>
                  <?php endif; ?>

                  <?php foreach ($payments as $p): ?>
                    <tr>
                      <td><?= e($p['id']) ?></td>
                      <td>LKR <?= money($p['amount']) ?></td>
                      <td>
                        <?php
                          $badge = 'text-bg-secondary';
                          if ($p['status'] === 'pending') $badge = 'text-bg-warning';
                          if ($p['status'] === 'approved') $badge = 'text-bg-success';
                          if ($p['status'] === 'rejected') $badge = 'text-bg-danger';
                        ?>
                        <span class="badge <?= $badge ?>"><?= e($p['status']) ?></span>
                      </td>
                      <td>
                        <?php if (!empty($p['proof'])): ?>
                          <a class="btn btn-sm btn-outline-primary" target="_blank" href="<?= e($p['proof']) ?>">
                            <i class="bi bi-paperclip me-1"></i> View
                          </a>
                        <?php else: ?>
                          <span class="text-muted">—</span>
                        <?php endif; ?>
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
