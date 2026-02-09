<?php
require_once __DIR__ . '/../../helpers/student_auth.php';
require_student();

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

$stmt = $pdo->prepare("SELECT * FROM batches WHERE id=? LIMIT 1");
$stmt->execute([$batch_id]);
$batch = $stmt->fetch();
if (!$batch) {
  header("Location: index.php?page=student_content");
  exit;
}

// ✅ Enrollment check (NEW)
$chk = $pdo->prepare("
  SELECT id FROM student_enrollments
  WHERE student_id=? AND batch_id=? AND status='active'
  LIMIT 1
");
$chk->execute([$student_id, $batch_id]);
$isEnrolled = (bool)$chk->fetch();

$fee = (float)$batch['fee_amount'];
$convenience = convenience_fee($fee);
$total = $fee + $convenience;

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!$isEnrolled) {
    $error = "You are not enrolled in this batch. Please contact admin.";
  } elseif (empty($_FILES['proof']['name'])) {
    $error = "Upload payment proof.";
  } else {
    $file = $_FILES['proof'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
      $error = "Upload failed. Try again.";
    } else {
      $allowedExt = ['jpg','jpeg','png','pdf'];
      $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
      if (!in_array($ext, $allowedExt, true)) {
        $error = "Only JPG, PNG, PDF allowed.";
      } else {
        $dir = __DIR__ . '/../../../uploads/student_proofs/';
        if (!is_dir($dir)) @mkdir($dir, 0777, true);

        $safe = "student_{$student_id}_{$batch_id}_" . time() . "." . $ext;
        $webPath = "uploads/student_proofs/" . $safe;

        if (move_uploaded_file($file['tmp_name'], $dir . $safe)) {
          $pdo->prepare("
            INSERT INTO student_payments
            (student_id, batch_id, amount, convenience_fee, total_amount, payment_type, proof, status)
            VALUES (?,?,?,?,?,'manual',?,'pending')
          ")->execute([$student_id, $batch_id, $fee, $convenience, $total, $webPath]);

          $msg = "Payment submitted. Await admin approval.";
        } else {
          $error = "Could not save file. Check folder permissions.";
        }
      }
    }
  }
}
?>

<div class="app-shell">
  <?php require_once __DIR__ . '/../layout/sidebar_student.php'; ?>

  <div class="content">
    <?php require_once __DIR__ . '/../layout/topbar.php'; ?>

    <div class="page">

      <?php if (!empty($_GET['locked'])): ?>
        <div class="alert alert-warning cardx border-0">
          This batch is locked. Please pay and wait for admin approval to access content.
        </div>
      <?php endif; ?>

      <?php if (!$isEnrolled): ?>
        <div class="alert alert-danger cardx border-0">
          You are not enrolled in <b><?= e($batch['name']) ?></b>. Please contact Admin to enroll you.
        </div>
      <?php endif; ?>

      <?php if ($msg): ?><div class="alert alert-success cardx border-0"><?= e($msg) ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert alert-danger cardx border-0"><?= e($error) ?></div><?php endif; ?>

      <div class="grid">
        <div style="grid-column: span 5;">
          <div class="cardx p-4">
            <div class="fw-bold fs-5 mb-1"><?= e($batch['name']) ?></div>
            <div class="text-muted">Submit batch fee payment proof.</div>

            <hr>

            <div class="d-flex justify-content-between mb-2">
              <div class="text-muted">Batch Fee</div>
              <div class="fw-semibold">LKR <?= money($fee) ?></div>
            </div>

            <div class="d-flex justify-content-between mb-2">
              <div class="text-muted">Convenience (5%)</div>
              <div class="fw-semibold">LKR <?= money($convenience) ?></div>
            </div>

            <div class="d-flex justify-content-between">
              <div class="text-muted">Total</div>
              <div class="fw-bold">LKR <?= money($total) ?></div>
            </div>

            <div class="text-muted small mt-2">
              (Convenience fee applies for online payments too. For now we accept manual proof.)
            </div>
          </div>
        </div>

        <div style="grid-column: span 7;">
          <div class="cardx p-4">
            <div class="fw-semibold mb-3">Upload Payment Proof</div>

            <form method="post" enctype="multipart/form-data">
              <div class="mb-3">
                <label class="form-label">Proof (JPG/PNG/PDF)</label>
                <input class="form-control" type="file" name="proof" required <?= !$isEnrolled ? 'disabled' : '' ?>>
              </div>

              <button class="btn btn-primary" type="submit" <?= !$isEnrolled ? 'disabled' : '' ?>>
                <i class="bi bi-upload me-1"></i> Submit Payment
              </button>

              <a class="btn btn-outline-secondary ms-2" href="index.php?page=student_content">
                Back
              </a>
            </form>

            <div class="text-muted small mt-3">
              Admin will review and approve your payment. After approval, your batch becomes Active and content unlocks.
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
