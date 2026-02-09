<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['teacher','student'], true)) {
  header("Location: index.php?page=login");
  exit;
}

$pdo = db();
$post_id = (int)($_GET['post_id'] ?? 0);
$back = $_GET['back'] ?? 'posts_feed';

$user_type = $_SESSION['role'];
$user_id = ($user_type === 'teacher') ? (int)$_SESSION['teacher_id'] : (int)$_SESSION['student_id'];

if ($post_id > 0) {
  // check existing
  $st = $pdo->prepare("SELECT id FROM post_likes WHERE post_id=? AND user_type=? AND user_id=? LIMIT 1");
  $st->execute([$post_id, $user_type, $user_id]);
  $row = $st->fetch();

  if ($row) {
    $pdo->prepare("DELETE FROM post_likes WHERE id=?")->execute([(int)$row['id']]);
  } else {
    $pdo->prepare("INSERT INTO post_likes (post_id,user_type,user_id) VALUES (?,?,?)")->execute([$post_id, $user_type, $user_id]);
  }
}

header("Location: index.php?page={$back}");
exit;
