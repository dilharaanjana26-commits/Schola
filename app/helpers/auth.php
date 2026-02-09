<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function require_admin() {
  if (empty($_SESSION['admin_id'])) {
    header("Location: index.php?page=admin_login");
    exit;
  }
}

function admin_login(array $admin) {
  $_SESSION['admin_id'] = $admin['id'];
  $_SESSION['admin_name'] = $admin['name'];
  $_SESSION['admin_email'] = $admin['email'];
}

function admin_logout() {
  session_unset();
  session_destroy();
}
