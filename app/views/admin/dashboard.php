<?php
require_once __DIR__ . '/../../helpers/auth.php';
require_admin();

$counts = [
  'teachers' => (int)db()->query("SELECT COUNT(*) c FROM teachers")->fetch()['c'],
  'students' => (int)db()->query("SELECT COUNT(*) c FROM students")->fetch()['c'],
  'batches'  => (int)db()->query("SELECT COUNT(*) c FROM batches")->fetch()['c'],
  'pending_teacher_payments' => (int)db()->query("SELECT COUNT(*) c FROM teacher_payments WHERE status='pending'")->fetch()['c'],
  'pending_student_payments' => (int)db()->query("SELECT COUNT(*) c FROM student_payments WHERE status IN ('pending','approved')")->fetch()['c'],
  'upcoming_classes' => (int)db()->query("SELECT COUNT(*) c FROM class_schedule WHERE class_date >= CURDATE() AND status='scheduled'")->fetch()['c'],
];

require_once __DIR__ . '/../layout/header.php';
?>
<div class="app-shell">
  <?php require_once __DIR__ . '/../layout/sidebar.php'; ?>

  <div class="content">
    <?php require_once __DIR__ . '/../layout/topbar.php'; ?>

    <div class="page">
      <div class="cardx p-4 mb-4">
        <div class="fw-bold fs-4">Admin Dashboard</div>
        <div class="text-muted">Welcome back! Here is a quick snapshot of institute activity today.</div>
        <div class="mt-3 p-3 rounded-4" style="background:#f6f8ff;border:1px solid #eef2ff;">
          <div class="fw-semibold">Quick Overview</div>
          <div class="text-muted small">Monitor teachers, students, approvals, and upcoming classes from one organized view.</div>
        </div>
      </div>

      <div class="grid">
        <div class="col-12" style="grid-column: span 4;">
          <div class="metric">
            <div>
              <div class="label">Teachers</div>
              <p class="value"><?= e($counts['teachers']) ?></p>
              <div class="text-muted small">Active faculty network</div>
            </div>
            <div class="icon"><i class="bi bi-person-badge fs-4"></i></div>
          </div>
        </div>

        <div class="col-12" style="grid-column: span 4;">
          <div class="metric">
            <div>
              <div class="label">Students</div>
              <p class="value"><?= e($counts['students']) ?></p>
              <div class="text-muted small">Learners enrolled</div>
            </div>
            <div class="icon"><i class="bi bi-people fs-4"></i></div>
          </div>
        </div>

        <div class="col-12" style="grid-column: span 4;">
          <div class="metric">
            <div>
              <div class="label">Pending Teacher Approvals</div>
              <p class="value"><?= e($counts['pending_teacher_payments']) ?></p>
              <div class="text-muted small">Teacher signups/payments to review</div>
            </div>
            <div class="icon"><i class="bi bi-shield-check fs-4"></i></div>
          </div>
        </div>

        <div class="col-12" style="grid-column: span 4;">
          <div class="metric">
            <div>
              <div class="label">Pending Student Approvals</div>
              <p class="value"><?= e($counts['pending_student_payments']) ?></p>
              <div class="text-muted small">Student payments awaiting approval</div>
            </div>
            <div class="icon"><i class="bi bi-person-check fs-4"></i></div>
          </div>
        </div>

        <div class="col-12" style="grid-column: span 4;">
          <div class="metric">
            <div>
              <div class="label">Batches</div>
              <p class="value"><?= e($counts['batches']) ?></p>
              <div class="text-muted small">Classes/groups created</div>
            </div>
            <div class="icon"><i class="bi bi-collection fs-4"></i></div>
          </div>
        </div>

        <div class="col-12" style="grid-column: span 4;">
          <div class="metric">
            <div>
              <div class="label">Upcoming Classes</div>
              <p class="value"><?= e($counts['upcoming_classes']) ?></p>
              <div class="text-muted small">Scheduled sessions ahead</div>
            </div>
            <div class="icon"><i class="bi bi-calendar-event fs-4"></i></div>
          </div>
        </div>
      </div>

      <div class="cardx p-4 mt-4">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="fw-semibold">Next Steps</div>
            <div class="text-muted small">We will implement CRUD + approvals next.</div>
          </div>
          <div class="d-flex gap-2">
            <a class="btn btn-outline-primary" href="index.php?page=admin_teachers"><i class="bi bi-person-plus me-1"></i> Manage Teachers</a>
            <a class="btn btn-outline-primary" href="index.php?page=admin_batches"><i class="bi bi-plus-square me-1"></i> Manage Batches</a>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
