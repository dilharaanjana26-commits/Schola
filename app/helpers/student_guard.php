<?php
require_once __DIR__ . '/../config/db.php';

function student_has_batch_access(int $student_id, int $batch_id): bool {
  $pdo = db();

  // must be enrolled
  $en = $pdo->prepare("
    SELECT id FROM student_enrollments
    WHERE student_id=? AND batch_id=? AND status='active'
  ");
  $en->execute([$student_id, $batch_id]);
  if (!$en->fetch()) return false;

  // must have approved payment
  $pay = $pdo->prepare("
    SELECT id FROM student_payments
    WHERE student_id=? AND batch_id=? AND status='approved'
    ORDER BY id DESC LIMIT 1
  ");
  $pay->execute([$student_id, $batch_id]);

  return (bool)$pay->fetch();
}

function require_student_batch_access(int $student_id, int $batch_id) {
  if (!student_has_batch_access($student_id, $batch_id)) {
    header("Location: index.php?page=student_content&locked=1");
    exit;
  }
}
