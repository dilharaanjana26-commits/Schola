<?php
require_once __DIR__ . '/../../helpers/teacher_auth.php';
require_teacher();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';

$pdo = db();
$postColumns = table_columns($pdo, 'posts');
$hasStatus = isset($postColumns['status']);
$teacherId = (int)($_SESSION['teacher_id'] ?? 0);

$posts = [];
try {
  if ($hasStatus) {
    $st = $pdo->prepare("SELECT * FROM posts WHERE user_type='teacher' AND user_id=? AND status IN ('approved', 'pending') ORDER BY id DESC");
  } else {
    $st = $pdo->prepare("SELECT * FROM posts WHERE user_type='teacher' AND user_id=? ORDER BY id DESC");
  }
  $st->execute([$teacherId]);
  $posts = $st->fetchAll();
} catch (Exception $e) {
  $posts = [];
}

require_once __DIR__ . '/../layout/header.php';
?>
<div class="app-shell">
  <?php require_once __DIR__ . '/../layout/sidebar_teacher.php'; ?>
  <div class="content">
    <?php require_once __DIR__ . '/../layout/topbar.php'; ?>

    <div class="page">
      <div class="fw-bold fs-4 mb-1">My Posts</div>
      <div class="text-muted mb-3">View your published posts and posts pending admin approval.</div>

      <div class="cardx p-4">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr class="text-muted">
                <th>#</th>
                <th>Post Type</th>
                <?php if ($hasStatus): ?><th>Status</th><?php endif; ?>
                <th>Content</th>
                <th>Created</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!$posts): ?>
                <tr><td colspan="<?= $hasStatus ? 5 : 4 ?>" class="text-center text-muted py-4">No posts found.</td></tr>
              <?php endif; ?>

              <?php foreach ($posts as $p): ?>
                <tr>
                  <td><?= (int)$p['id'] ?></td>
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
                  <td style="max-width:500px;"><?= e(text_excerpt((string)$p['content'], 120)) ?></td>
                  <td class="text-muted small"><?= e((string)$p['created_at']) ?></td>
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
