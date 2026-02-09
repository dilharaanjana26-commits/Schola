<?php
require_once __DIR__ . '/../config/db.php';

function teacher_subscription_ok(int $teacher_id): bool {
  $stmt = db()->prepare("SELECT subscription_status, subscription_expiry FROM teachers WHERE id=? LIMIT 1");
  $stmt->execute([$teacher_id]);
  $t = $stmt->fetch();

  if (!$t) return false;
  if (($t['subscription_status'] ?? '') !== 'active') return false;

  // expiry check
  if (!empty($t['subscription_expiry'])) {
    $today = date('Y-m-d');
    if ($t['subscription_expiry'] < $today) return false;
  }
  return true;
}

function require_teacher_active() {
  if (session_status() === PHP_SESSION_NONE) session_start();
  $teacher_id = (int)($_SESSION['teacher_id'] ?? 0);
  if ($teacher_id <= 0) {
    header("Location: index.php?page=teacher_login");
    exit;
  }
  if (!teacher_subscription_ok($teacher_id)) {
    header("Location: index.php?page=teacher_subscription&msg=subscription_required");
    exit;
  }
}
