<?php
require_once __DIR__ . '/../../helpers/auth.php';
require_admin();
require_once __DIR__ . '/../layout/header.php';
?>
<div class="app-shell">
  <?php require_once __DIR__ . '/../layout/sidebar.php'; ?>
  <div class="content">
    <?php require_once __DIR__ . '/../layout/topbar.php'; ?>

    <div class="page">
      <div class="cardx p-4">
        <div class="fw-bold fs-5">Reports</div>
        <div class="text-muted">Next: payment reports, batch attendance, performance analytics, exports.</div>
      </div>
    </div>

  </div>
</div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
