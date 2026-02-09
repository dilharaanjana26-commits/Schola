<?php
require_once __DIR__ . '/../../helpers/auth.php';
require_admin();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';

$pdo = db();

// ---------- Handle Create ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
  $name   = trim($_POST['name'] ?? '');
  $email  = trim($_POST['email'] ?? '');
  $mobile = trim($_POST['mobile'] ?? '');
  $pass   = $_POST['password'] ?? '';

  if ($name && $email && $pass) {
    $hash = password_hash($pass, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO teachers (name,email,password,mobile,subscription_status) VALUES (?,?,?,?, 'pending')");
    $stmt->execute([$name, $email, $hash, $mobile]);

    header("Location: index.php?page=admin_teachers&ok=created");
    exit;
  } else {
    $error = "Name, email, and password are required.";
  }
}

// ---------- Handle Update ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
  $id     = (int)($_POST['id'] ?? 0);
  $name   = trim($_POST['name'] ?? '');
  $email  = trim($_POST['email'] ?? '');
  $mobile = trim($_POST['mobile'] ?? '');
  $status = trim($_POST['subscription_status'] ?? 'pending');
  $expiry = trim($_POST['subscription_expiry'] ?? '');

  if ($id && $name && $email) {
    $stmt = $pdo->prepare("UPDATE teachers SET name=?, email=?, mobile=?, subscription_status=?, subscription_expiry=? WHERE id=?");
    $stmt->execute([$name, $email, $mobile, $status, ($expiry ?: null), $id]);

    header("Location: index.php?page=admin_teachers&ok=updated");
    exit;
  } else {
    $error = "Name and email are required.";
  }
}

// ---------- Handle Delete ----------
if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];
  if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM teachers WHERE id=?");
    $stmt->execute([$id]);
  }
  header("Location: index.php?page=admin_teachers&ok=deleted");
  exit;
}

// ---------- Fetch Teachers ----------
$teachers = $pdo->query("SELECT * FROM teachers ORDER BY id DESC")->fetchAll();

// ---------- Edit Mode ----------
$editTeacher = null;
if (isset($_GET['edit'])) {
  $id = (int)$_GET['edit'];
  $stmt = $pdo->prepare("SELECT * FROM teachers WHERE id=?");
  $stmt->execute([$id]);
  $editTeacher = $stmt->fetch();
}

require_once __DIR__ . '/../layout/header.php';
?>
<div class="app-shell">
  <?php require_once __DIR__ . '/../layout/sidebar.php'; ?>
  <div class="content">
    <?php require_once __DIR__ . '/../layout/topbar.php'; ?>

    <div class="page">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
          <div class="fw-bold fs-4">Teachers</div>
          <div class="text-muted">Add, edit, remove teachers and manage subscription status.</div>
        </div>
      </div>

      <?php if (!empty($_GET['ok'])): ?>
        <div class="alert alert-success cardx border-0">
          Action completed: <?= e($_GET['ok']) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger cardx border-0">
          <?= e($error) ?>
        </div>
      <?php endif; ?>

      <div class="grid">
        <!-- Form -->
        <div style="grid-column: span 4;">
            <div id="addForm"></div>
          <div class="cardx p-4">
            <div class="fw-semibold mb-3">
              <?= $editTeacher ? "Edit Teacher" : "Add New Teacher" ?>
            </div>

            <form method="post">
              <?php if ($editTeacher): ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= e($editTeacher['id']) ?>">
              <?php else: ?>
                <input type="hidden" name="action" value="create">
              <?php endif; ?>

              <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input class="form-control" name="name" required
                       value="<?= e($editTeacher['name'] ?? '') ?>">
              </div>

              <div class="mb-3">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" name="email" required
                       value="<?= e($editTeacher['email'] ?? '') ?>">
              </div>

              <div class="mb-3">
                <label class="form-label">Mobile</label>
                <input class="form-control" name="mobile"
                       value="<?= e($editTeacher['mobile'] ?? '') ?>">
              </div>

              <?php if (!$editTeacher): ?>
                <div class="mb-3">
                  <label class="form-label">Password</label>
                  <input class="form-control" type="password" name="password" required>
                  <div class="text-muted small mt-1">Teacher can change later (future feature).</div>
                </div>
              <?php else: ?>
                <div class="mb-3">
                  <label class="form-label">Subscription Status</label>
                  <select class="form-select" name="subscription_status">
                    <?php
                      $statuses = ['pending','active','expired','blocked'];
                      foreach ($statuses as $s):
                        $sel = ($editTeacher['subscription_status'] === $s) ? 'selected' : '';
                    ?>
                      <option value="<?= e($s) ?>" <?= $sel ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label">Subscription Expiry</label>
                  <input class="form-control" type="date" name="subscription_expiry"
                         value="<?= e($editTeacher['subscription_expiry'] ?? '') ?>">
                </div>
              <?php endif; ?>

              <div class="d-flex gap-2">
                <button class="btn btn-primary" type="submit">
                  <i class="bi bi-check2-circle me-1"></i>
                  <?= $editTeacher ? "Update" : "Create" ?>
                </button>

                <?php if ($editTeacher): ?>
                  <a class="btn btn-outline-secondary" href="index.php?page=admin_teachers">
                    Cancel
                  </a>
                <?php endif; ?>
              </div>
            </form>
          </div>
        </div>

        <!-- Table -->
        <div style="grid-column: span 8;">
          <div class="cardx p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div class="fw-semibold">All Teachers</div>
              <div class="text-muted small"><?= count($teachers) ?> total</div>
            </div>

            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr class="text-muted">
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th>Status</th>
                    <th>Expiry</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!$teachers): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No teachers found.</td></tr>
                  <?php endif; ?>

                  <?php foreach ($teachers as $t): ?>
                    <tr>
                      <td><?= e($t['id']) ?></td>
                      <td class="fw-semibold"><?= e($t['name']) ?></td>
                      <td><?= e($t['email']) ?></td>
                      <td><?= e($t['mobile']) ?></td>
                      <td>
                        <?php
                          $badge = 'text-bg-secondary';
                          if ($t['subscription_status'] === 'active') $badge = 'text-bg-success';
                          if ($t['subscription_status'] === 'pending') $badge = 'text-bg-warning';
                          if ($t['subscription_status'] === 'expired') $badge = 'text-bg-danger';
                          if ($t['subscription_status'] === 'blocked') $badge = 'text-bg-dark';
                        ?>
                        <span class="badge <?= $badge ?>"><?= e($t['subscription_status']) ?></span>
                      </td>
                      <td><?= e($t['subscription_expiry'] ?? '-') ?></td>
                      <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="index.php?page=admin_teachers&edit=<?= e($t['id']) ?>">
                          <i class="bi bi-pencil"></i>
                        </a>
                        <a class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Delete this teacher?')"
                           href="index.php?page=admin_teachers&delete=<?= e($t['id']) ?>">
                          <i class="bi bi-trash"></i>
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
