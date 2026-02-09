<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function require_student() {
  if (empty($_SESSION['student_id'])) {
    header("Location: index.php?page=student_login");
    exit;
  }
}

function student_login_session(array $s) {
  $_SESSION['student_id'] = $s['id'];
  $_SESSION['student_name'] = $s['name'];
  $_SESSION['student_email'] = $s['email'];
}

function student_logout() {
  unset($_SESSION['student_id'], $_SESSION['student_name'], $_SESSION['student_email']);
}
