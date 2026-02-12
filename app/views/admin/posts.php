<?php
require_once __DIR__ . '/../../helpers/auth.php';
require_admin();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';

$pdo = db();
$postColumns = table_columns($pdo, 'posts');
$hasStatus = isset($postColumns['status']);

if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];

  if ($id > 0) {
    try {
      $pdo->beginTransaction();
      $pdo->prepare("DELETE FROM post_comments WHERE post_id=?")->execute([$id]);
      $pdo->prepare("DELETE FROM post_likes WHERE post_id=?")->execute([$id]);
      $pdo->prepare("DELETE FROM posts WHERE id=?")->execute([$id]);
      $pdo->commit();
      header("Location: index.php?page=admin_posts&ok=deleted");
      exit;
    } catch (Exception $e) {
      if ($pdo->inTransaction()) $pdo->rollBack();
      header("Location: index.php?page=admin_posts&ok=delete_failed");
      exit;
    }
  }
}

$posts = [];
try {
  $posts = $pdo->query("SELECT * FROM posts ORDER BY id DESC")->fetchAll();
} catch (Exception $e) {
  $posts = [];
}

require_once __DIR__ . '/../layout/header.php';
?>
<div class="app-shell">
  <?php require_once __DIR__ . '/../layout/sidebar.php'; ?>
  <div class="content">
    <?php require_once __DIR__ . '/../layout/topbar.php'; ?>

    <div class="page">
      <div class="fw-bold fs-4 mb-1">Posts</div>
      <div class="text-muted mb-3">View and manage all posts from teachers and students.</div>

      <?php if (!empty($_GET['ok'])): ?>
        <div class="alert alert-success cardx border-0">Action completed: <?= e($_GET['ok']) ?></div>
      <?php endif; ?>

      <div class="cardx p-4">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr class="text-muted">
                <th>#</th>
                <th>Author Type</th>
                <th>Post Type</th>
                <?php if ($hasStatus): ?><th>Status</th><?php endif; ?>
                <th>Content</th>
                <th>Created</th>
                <th class="text-end">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!$posts): ?>
                <tr><td colspan="<?= $hasStatus ? 7 : 6 ?>" class="text-center text-muted py-4">No posts found.</td></tr>
              <?php endif; ?>

              <?php foreach ($posts as $p): ?>
                <tr>
                  <td><?= (int)$p['id'] ?></td>
                  <td class="fw-semibold"><?= e((string)$p['user_type']) ?></td>
                  <td>
                    <?php $postType = $p['post_type'] ?? 'update'; ?>
                    <span class="badge text-bg-light border"><?= e(str_replace('_', ' ', $postType)) ?></span>
                  </td>
                  <?php if ($hasStatus): ?>
                    <td>
                      <?php $status = (string)($p['status'] ?? 'unknown'); ?>
                      <span class="badge text-bg-<?= $status === 'approved' ? 'success' : ($status === 'pending' ? 'warning' : 'secondary') ?>">
                        <?= e($status) ?>
                      </span>
                    </td>
                  <?php endif; ?>
                  <td style="max-width:460px;"><?= e(text_excerpt((string)$p['content'], 120)) ?></td>
                  <td class="text-muted small"><?= e((string)$p['created_at']) ?></td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-outline-danger" href="index.php?page=admin_posts&delete=<?= (int)$p['id'] ?>" onclick="return confirm('Delete this post? This removes it from home and feed.');">
                      <i class="bi bi-trash"></i> Delete
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
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
