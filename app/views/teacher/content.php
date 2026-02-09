<?php
require_once __DIR__ . '/../../helpers/teacher_auth.php';
require_teacher();
require_once __DIR__ . '/../../helpers/teacher_guard.php';
require_teacher_active();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';
require_once __DIR__ . '/../layout/header.php';

$pdo = db();
$teacher_id = (int)$_SESSION['teacher_id'];
$msg = '';
$error = '';

$bstmt = $pdo->prepare("SELECT id, name FROM batches WHERE teacher_id=? ORDER BY name ASC");
$bstmt->execute([$teacher_id]);
$batches = $bstmt->fetchAll();

if (!$batches) {
  $error = "No batches assigned to you yet. Please ask Admin to assign a batch.";
}

// Upload tute
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload_tute') {
  $batch_id = (int)($_POST['batch_id'] ?? 0);
  $title = trim($_POST['title'] ?? '');

  if ($batch_id <= 0 || $title === '') {
    $error = "Please select a batch and enter a title.";
  } elseif (empty($_FILES['file']['name'])) {
    $error = "Please choose a file to upload.";
  } else {
    $chk = $pdo->prepare("SELECT id FROM batches WHERE id=? AND teacher_id=?");
    $chk->execute([$batch_id, $teacher_id]);
    if (!$chk->fetch()) {
      $error = "Invalid batch selection.";
    } else {
      $file = $_FILES['file'];
      if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Upload failed. Try again.";
      } else {
        $allowedExt = ['pdf','doc','docx','ppt','pptx','zip','rar','jpg','jpeg','png'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt, true)) {
          $error = "Invalid file type. Allowed: PDF, DOC, DOCX, PPT, PPTX, ZIP, RAR, JPG, PNG.";
        } else {
          $uploadDir = __DIR__ . '/../../../uploads/tutes/';
          if (!is_dir($uploadDir)) @mkdir($uploadDir, 0777, true);

          $safeName = "tute_" . $teacher_id . "_" . $batch_id . "_" . time() . "." . $ext;
          $destPath = $uploadDir . $safeName;

          if (move_uploaded_file($file['tmp_name'], $destPath)) {
            $webPath = "uploads/tutes/" . $safeName;
            $stmt = $pdo->prepare("INSERT INTO tutes (batch_id, title, file_path) VALUES (?, ?, ?)");
            $stmt->execute([$batch_id, $title, $webPath]);
            $msg = "Tute uploaded successfully.";
          } else {
            $error = "Could not save file. Check folder permissions.";
          }
        }
      }
    }
  }
}

// Add recorded link
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_video') {
  $batch_id = (int)($_POST['batch_id'] ?? 0);
  $title = trim($_POST['title'] ?? '');
  $url = trim($_POST['video_url'] ?? '');

  if ($batch_id <= 0 || $title === '' || $url === '') {
    $error = "Please select batch, title and video link.";
  } else {
    $chk = $pdo->prepare("SELECT id FROM batches WHERE id=? AND teacher_id=?");
    $chk->execute([$batch_id, $teacher_id]);
    if (!$chk->fetch()) {
      $error = "Invalid batch selection.";
    } else {
      $stmt = $pdo->prepare("INSERT INTO recorded_videos (teacher_id, batch_id, title, video_url) VALUES (?, ?, ?, ?)");
      $stmt->execute([$teacher_id, $batch_id, $title, $url]);
      $msg = "Recorded link added successfully.";
    }
  }
}

$tutes = $pdo->prepare("
  SELECT t.id, t.title, t.file_path, t.created_at, b.name AS batch_name
  FROM tutes t
  JOIN batches b ON b.id = t.batch_id
  WHERE b.teacher_id=?
  ORDER BY t.id DESC
  LIMIT 15
");
$tutes->execute([$teacher_id]);
$tuteList = $tutes->fetchAll();

$vids = $pdo->prepare("
  SELECT rv.id, rv.title, rv.video_url, rv.created_at, b.name AS batch_name
  FROM recorded_videos rv
  JOIN batches b ON b.id = rv.batch_id
  WHERE rv.teacher_id=?
  ORDER BY rv.id DESC
  LIMIT 15
");
$vids->execute([$teacher_id]);
$videoList = $vids->fetchAll();
?>

<div class="app-shell">
  <?php require_once __DIR__ . '/../layout/sidebar_teacher.php'; ?>
  <div class="content">
    <?php require_once __DIR__ . '/../layout/topbar.php'; ?>

    <div class="page">
      <?php if ($msg): ?>
        <div class="alert alert-success cardx border-0"><?= e($msg) ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-danger cardx border-0"><?= e($error) ?></div>
      <?php endif; ?>

      <div class="grid">
        <div style="grid-column: span 6;">
          <div class="cardx p-4">
            <div class="fw-semibold mb-3">Upload Tutes / Study Materials</div>

            <form method="post" enctype="multipart/form-data">
              <input type="hidden" name="action" value="upload_tute">

              <div class="mb-3">
                <label class="form-label">Batch</label>
                <select class="form-select" name="batch_id" required>
                  <option value="">-- Select batch --</option>
                  <?php foreach ($batches as $b): ?>
                    <option value="<?= e($b['id']) ?>"><?= e($b['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Title</label>
                <input class="form-control" name="title" placeholder="e.g., Algebra - Lesson 01" required>
              </div>

              <div class="mb-3">
                <label class="form-label">File</label>
                <input class="form-control" type="file" name="file" required>
                <div class="text-muted small mt-1">Allowed: PDF/DOC/DOCX/PPT/PPTX/ZIP/RAR/JPG/PNG</div>
              </div>

              <button class="btn btn-primary" type="submit">
                <i class="bi bi-upload me-1"></i> Upload
              </button>
            </form>
          </div>
        </div>

        <div style="grid-column: span 6;">
          <div class="cardx p-4">
            <div class="fw-semibold mb-3">Add Recorded Video Link</div>

            <form method="post">
              <input type="hidden" name="action" value="add_video">

              <div class="mb-3">
                <label class="form-label">Batch</label>
                <select class="form-select" name="batch_id" required>
                  <option value="">-- Select batch --</option>
                  <?php foreach ($batches as $b): ?>
                    <option value="<?= e($b['id']) ?>"><?= e($b['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Title</label>
                <input class="form-control" name="title" placeholder="e.g., Revision Part 1" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Video URL</label>
                <input class="form-control" name="video_url" placeholder="https://youtube.com/..." required>
              </div>

              <button class="btn btn-primary" type="submit">
                <i class="bi bi-link-45deg me-1"></i> Add Link
              </button>
            </form>
          </div>
        </div>

        <div style="grid-column: span 6;">
          <div class="cardx p-4">
            <div class="fw-semibold mb-3">Recent Tutes</div>
            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr class="text-muted">
                    <th>Batch</th>
                    <th>Title</th>
                    <th>File</th>
                    <th>Uploaded</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!$tuteList): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">No tutes uploaded yet.</td></tr>
                  <?php endif; ?>
                  <?php foreach ($tuteList as $t): ?>
                    <tr>
                      <td><?= e($t['batch_name']) ?></td>
                      <td class="fw-semibold"><?= e($t['title']) ?></td>
                      <td>
                        <a class="btn btn-sm btn-outline-primary" target="_blank" href="<?= e($t['file_path']) ?>">
                          <i class="bi bi-file-earmark me-1"></i> Open
                        </a>
                      </td>
                      <td class="text-muted small"><?= e($t['created_at']) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div style="grid-column: span 6;">
          <div class="cardx p-4">
            <div class="fw-semibold mb-3">Recent Recorded Links</div>
            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr class="text-muted">
                    <th>Batch</th>
                    <th>Title</th>
                    <th>Link</th>
                    <th>Added</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!$videoList): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">No recorded links yet.</td></tr>
                  <?php endif; ?>
                  <?php foreach ($videoList as $v): ?>
                    <tr>
                      <td><?= e($v['batch_name']) ?></td>
                      <td class="fw-semibold"><?= e($v['title']) ?></td>
                      <td>
                        <a class="btn btn-sm btn-outline-primary" target="_blank" href="<?= e($v['video_url']) ?>">
                          <i class="bi bi-play-circle me-1"></i> Open
                        </a>
                      </td>
                      <td class="text-muted small"><?= e($v['created_at']) ?></td>
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
