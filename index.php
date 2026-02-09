<?php
require_once __DIR__ . '/app/config/db.php';
require_once __DIR__ . '/app/helpers/auth.php';
require_once __DIR__ . '/app/helpers/functions.php';

$page = $_GET['page'] ?? 'home';


$routes = [
  // Public
  'home'     => __DIR__ . '/app/views/public/home.php',
  'login'    => __DIR__ . '/app/views/public/login.php',
  'register' => __DIR__ . '/app/views/public/register.php',
  'logout'   => __DIR__ . '/app/views/public/logout.php',

  // Admin
  'admin_login'           => __DIR__ . '/app/views/admin/login.php',
  'admin_logout'          => __DIR__ . '/app/views/admin/logout.php',
  'admin_dashboard'       => __DIR__ . '/app/views/admin/dashboard.php',
  'admin_teachers'        => __DIR__ . '/app/views/admin/teachers.php',
  'admin_students'        => __DIR__ . '/app/views/admin/students.php',
  'admin_batches'         => __DIR__ . '/app/views/admin/batches.php',
  'admin_teacher_payments'=> __DIR__ . '/app/views/admin/teacher_payments.php',
  'admin_student_payments'=> __DIR__ . '/app/views/admin/student_payments.php',
  'admin_class_schedule'  => __DIR__ . '/app/views/admin/class_schedule.php',
  'admin_reports'         => __DIR__ . '/app/views/admin/reports.php',
  'admin_enrollments'     => __DIR__ . '/app/views/admin/enrollments.php',

  // ✅ Admin approvals (ONLY ONCE!)
  'admin_user_approvals'  => __DIR__ . '/app/views/admin/user_approvals.php',
  'admin_post_approvals'  => __DIR__ . '/app/views/admin/post_approvals.php',

  // Teacher
  'teacher_login'        => __DIR__ . '/app/views/teacher/login.php',
  'teacher_logout'       => __DIR__ . '/app/views/teacher/logout.php',
  'teacher_dashboard'    => __DIR__ . '/app/views/teacher/dashboard.php',
  'teacher_subscription' => __DIR__ . '/app/views/teacher/subscription.php',
  'teacher_content'      => __DIR__ . '/app/views/teacher/content.php',
  'teacher_live'         => __DIR__ . '/app/views/teacher/live.php',
  'teacher_schedule'     => __DIR__ . '/app/views/teacher/schedule.php',

  // Student
  'student_login'        => __DIR__ . '/app/views/student/login.php',
  'student_logout'       => __DIR__ . '/app/views/student/logout.php',
  'student_dashboard'    => __DIR__ . '/app/views/student/dashboard.php',
  'student_payment'      => __DIR__ . '/app/views/student/payment.php',
  'student_content'      => __DIR__ . '/app/views/student/content.php',
  'student_batch_content'=> __DIR__ . '/app/views/student/batch_content.php',

  // Posts
  'posts_feed'   => __DIR__ . '/app/views/posts/feed.php',
  'post_create'  => __DIR__ . '/app/views/posts/create.php',
  'post_like'    => __DIR__ . '/app/views/posts/like.php',
  'post_comment' => __DIR__ . '/app/views/posts/comment.php',
];


if (!isset($routes[$page])) {
  http_response_code(404);
  echo "404 - Page not found";
  exit;
}

require $routes[$page];
