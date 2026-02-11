<?php
require_once __DIR__ . '/../../helpers/auth.php';
admin_logout();
header("Location: index.php?page=login");
exit;
