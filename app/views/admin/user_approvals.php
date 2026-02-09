<?php
require_once __DIR__ . '/../../helpers/auth.php';
require_admin();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';

$pdo = db();
$pageError = '';

/* ---------------- ACTION HANDLERS ---------------- */
if (isset($_GET['approve'], $_GET['type'], $_GET['id'])) {
  $id = (int)$_GET['id'];
  $type = $_GET['type'];

  if ($id > 0 && in_array($type, ['student','teacher'], true)) {
    $table = $type === 'student' ? 'students' : 'teachers';
    try {
      $pdo->prepare("UPDATE {$table} SET status='approved' WHERE id=?")->execute([$id]);
    } catch (Exception $e) {
      header("Location: index.php?page=admin_user_approvals&err=update_failed");
      exit;
    }
  }

  header("Location: index.php?page=admin_user_approvals&ok=approved");
  exit;
}

if (isset($_GET['reject'], $_GET['type'], $_GET['id'])) {
  $id = (int)$_GET['id'];
  $type = $_GET['type'];

  if ($id > 0 && in_array($type, ['student','teacher'], true)) {
    $table = $type === 'student' ? 'students' : 'teachers';
    try {
      $pdo->prepare("UPDATE {$table} SET status='rejected' WHERE id=?")->execute([$id]);
    } catch (Exception $e) {
      header("Location: index.php?page=admin_user_approvals&err=update_failed");
      exit;
    }
  }

  header("Location: index.php?page=admin_user_approvals&ok=rejected");
  exit;
}

/* ---------------- FETCH PENDING USERS ---------------- */
$pendingStudents = [];
$pendingTeachers = [];
try {
  $pendingStudents = $pdo->query("
    SELECT id, name, email, whatsapp, created_at
    FROM students
    WHERE status='pending'
    ORDER BY id DESC
  ")->fetchAll();

  $pendingTeachers = $pdo->query("
    SELECT id, name, email, mobile, created_at
    FROM teachers
    WHERE status='pending'
    ORDER BY id DESC
  ")->fetchAll();
} catch (Exception $e) {
  $pageError = 'Unable to load pending requests. Please ensure the students/teachers tables include status and created_at columns.';
}

require_once __DIR__ . '/../layout/header.php';
?>

<div class="app-shell">
  <?php require_once __DIR__ . '/../layout/sidebar.php'; ?>

  <div class="content">
    <?php require_once __DIR__ . '/../layout/topbar.php'; ?>

    <div class="page">

      <div class="mb-3">
        <div class="fw-bold fs-4">User Approvals</div>
        <div class="text-muted">
          Review and approve new student and teacher account requests.
        </div>
      </div>

      <?php if (!empty($_GET['ok'])): ?>
        <div class="alert alert-success cardx border-0">
          Action completed: <?= e($_GET['ok']) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($_GET['err']) || $pageError): ?>
        <div class="alert alert-danger cardx border-0">
          <?= e($pageError ?: 'Action failed. Please try again.') ?>
        </div>
      <?php endif; ?>

      <div class="grid">

        <!-- STUDENT REQUESTS -->
        <div style="grid-column: span 6;">
          <div class="cardx p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div class="fw-semibold">Pending Students</div>
              <div class="text-muted small"><?= count($pendingStudents) ?> requests</div>
            </div>

            <?php if (!$pendingStudents): ?>
              <div class="text-muted text-center py-4">No pending student requests.</div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table align-middle">
                  <thead class="text-muted">
                    <tr>
                      <th>Name</th>
                      <th>Email</th>
                      <th>WhatsApp</th>
                      <th class="text-end">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($pendingStudents as $s): ?>
                      <tr>
                        <td class="fw-semibold"><?= e($s['name']) ?></td>
                        <td><?= e($s['email']) ?></td>
                        <td><?= e($s['whatsapp'] ?? '—') ?></td>
                        <td class="text-end">
                          <a class="btn btn-sm btn-success"
                             href="index.php?page=admin_user_approvals&approve=1&type=student&id=<?= e($s['id']) ?>">
                            <i class="bi bi-check-circle"></i>
                          </a>
                          <a class="btn btn-sm btn-outline-danger"
                             onclick="return confirm('Reject this student?')"
                             href="index.php?page=admin_user_approvals&reject=1&type=student&id=<?= e($s['id']) ?>">
                            <i class="bi bi-x-circle"></i>
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- TEACHER REQUESTS -->
        <div style="grid-column: span 6;">
          <div class="cardx p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div class="fw-semibold">Pending Teachers</div>
              <div class="text-muted small"><?= count($pendingTeachers) ?> requests</div>
            </div>

            <?php if (!$pendingTeachers): ?>
              <div class="text-muted text-center py-4">No pending teacher requests.</div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table align-middle">
                  <thead class="text-muted">
                    <tr>
                      <th>Name</th>
                      <th>Email</th>
                      <th>Mobile</th>
                      <th class="text-end">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($pendingTeachers as $t): ?>
                      <tr>
                        <td class="fw-semibold"><?= e($t['name']) ?></td>
                        <td><?= e($t['email']) ?></td>
                        <td><?= e($t['mobile'] ?? '—') ?></td>
                        <td class="text-end">
                          <a class="btn btn-sm btn-success"
                             href="index.php?page=admin_user_approvals&approve=1&type=teacher&id=<?= e($t['id']) ?>">
                            <i class="bi bi-check-circle"></i>
                          </a>
                          <a class="btn btn-sm btn-outline-danger"
                             onclick="return confirm('Reject this teacher?')"
                             href="index.php?page=admin_user_approvals&reject=1&type=teacher&id=<?= e($t['id']) ?>">
                            <i class="bi bi-x-circle"></i>
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>

      </div>

      <div class="text-muted small mt-3">
        Approved users can log in immediately. Rejected users will be blocked.
      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
