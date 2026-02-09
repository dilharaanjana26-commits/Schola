<?php
require_once __DIR__ . '/../../helpers/auth.php';
require_admin();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';

$pdo = db();
$postColumns = table_columns($pdo, 'posts');
$canModerate = isset($postColumns['status']);

if ($canModerate && isset($_GET['approve'])) {
  $id = (int)$_GET['approve'];
  $pdo->prepare("UPDATE posts SET status='approved' WHERE id=?")->execute([$id]);
  header("Location: index.php?page=admin_post_approvals&ok=approved");
  exit;
}
if ($canModerate && isset($_GET['reject'])) {
  $id = (int)$_GET['reject'];
  $pdo->prepare("UPDATE posts SET status='rejected' WHERE id=?")->execute([$id]);
  header("Location: index.php?page=admin_post_approvals&ok=rejected");
  exit;
}

$pending = [];
if ($canModerate) {
  try {
    $pending = $pdo->query("SELECT * FROM posts WHERE status='pending' ORDER BY id DESC")->fetchAll();
  } catch (Exception $e) {
    $canModerate = false;
  }
}
if (!$canModerate) {
  try {
    $pending = $pdo->query("SELECT * FROM posts ORDER BY id DESC")->fetchAll();
  } catch (Exception $e) {
    $pending = [];
  }
}

require_once __DIR__ . '/../layout/header.php';
?>
<div class="app-shell">
  <?php require_once __DIR__ . '/../layout/sidebar.php'; ?>
  <div class="content">
    <?php require_once __DIR__ . '/../layout/topbar.php'; ?>

    <div class="page">
      <div class="fw-bold fs-4 mb-1">Post Approvals</div>
      <div class="text-muted mb-3">Approve teacher/student posts before they appear on the public feed.</div>

      <?php if (!$canModerate): ?>
        <div class="alert alert-warning cardx border-0">
          Approval status is unavailable because the posts table does not include a status column.
        </div>
      <?php endif; ?>

      <?php if (!empty($_GET['ok'])): ?>
        <div class="alert alert-success cardx border-0">Action completed: <?= e($_GET['ok']) ?></div>
      <?php endif; ?>

      <div class="cardx p-4">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr class="text-muted">
                <th>#</th>
                <th>Type</th>
                <th>Post</th>
                <th>Amount</th>
                <th>Content</th>
                <th>Image</th>
                <th>Created</th>
                <th class="text-end">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!$pending): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">No pending posts.</td></tr>
              <?php endif; ?>
              <?php foreach ($pending as $p): ?>
                <tr>
                  <td><?= (int)$p['id'] ?></td>
                  <td class="fw-semibold"><?= e($p['user_type']) ?></td>
                  <td>
                    <?php $postType = $p['post_type'] ?? 'update'; ?>
                    <span class="badge text-bg-light border"><?= e(str_replace('_', ' ', $postType)) ?></span>
                    <?php if (!empty($p['is_premium'])): ?>
                      <span class="badge text-bg-warning ms-1">Premium</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if (($p['post_type'] ?? '') === 'payment_request' && !empty($p['payment_amount'])): ?>
                      <?= e(number_format((float)$p['payment_amount'], 2)) ?>
                    <?php else: ?>
                      <span class="text-muted">—</span>
                    <?php endif; ?>
                  </td>
                  <td style="max-width:420px;"><?= e(text_excerpt((string)$p['content'], 120)) ?></td>
                  <td>
                    <?php if (!empty($p['image_path'])): ?>
                      <a target="_blank" href="<?= e($p['image_path']) ?>">View</a>
                    <?php else: ?>
                      <span class="text-muted">—</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-muted small"><?= e($p['created_at']) ?></td>
                  <td class="text-end">
                    <?php if ($canModerate): ?>
                      <a class="btn btn-sm btn-success" href="index.php?page=admin_post_approvals&approve=<?= (int)$p['id'] ?>">
                        <i class="bi bi-check2"></i>
                      </a>
                      <a class="btn btn-sm btn-outline-danger" href="index.php?page=admin_post_approvals&reject=<?= (int)$p['id'] ?>">
                        <i class="bi bi-x"></i>
                      </a>
                    <?php else: ?>
                      <span class="text-muted">—</span>
                    <?php endif; ?>
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
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
