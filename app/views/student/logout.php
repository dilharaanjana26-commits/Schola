<?php
require_once __DIR__ . '/../../helpers/student_auth.php';
student_logout();
header("Location: index.php?page=student_login");
exit;
