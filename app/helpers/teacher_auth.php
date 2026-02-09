<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function require_teacher() {
  if (empty($_SESSION['teacher_id'])) {
    header("Location: index.php?page=teacher_login");
    exit;
  }
}

function teacher_login_session(array $teacher) {
  $_SESSION['teacher_id'] = $teacher['id'];
  $_SESSION['teacher_name'] = $teacher['name'];
  $_SESSION['teacher_email'] = $teacher['email'];
}

function teacher_logout() {
  unset($_SESSION['teacher_id'], $_SESSION['teacher_name'], $_SESSION['teacher_email']);
}
