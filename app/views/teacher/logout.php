<?php
require_once __DIR__ . '/../../helpers/teacher_auth.php';
teacher_logout();
header("Location: index.php?page=login");
exit;
