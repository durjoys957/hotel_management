<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('admin');
require_once __DIR__ . '/../models/AdminModel.php';
$model = new AdminModel();
$anns  = $model->getAnnouncements();
$pageTitle='Announcements'; $activeRole='admin'; $activePage='announcements';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Announcements</h1><p class="page-subtitle">Visible to all guest accounts</p></div>
    <button onclick="openModal('newAnnModal')" class="btn btn-primary">+ Post Announcement</button>
  </div>

  <?php if (!$anns): ?>
  <div class="empty-state"><div class="empty-icon">📢</div><p>No announcements yet.</p></div>
  <?php else: ?>
  <?php foreach ($anns as $a): ?>
  <div class="card" style="border-left:4px solid var(--gold)">
    <div style="display:flex;justify-content:space-between;align-items:flex-start">
      <div>
        <h3 style="font-family:'Playfair Display',serif"><?= sanitize($a['title']) ?></h3>
        <p style="color:var(--gray-dk);margin-top:0.5rem"><?= sanitize($a['message']) ?></p>
        <small class="text-muted">Posted by <?= sanitize($a['author']) ?> on <?= date('M j, Y g:i a', strtotime($a['created_at'])) ?></small>
      </div>
      <form method="POST" action="<?= BASE_URL ?>/admin/controllers/admin_controller.php">
        <input type="hidden" name="action" value="delete_announcement">
        <input type="hidden" name="id" value="<?= $a['id'] ?>">
        <button type="submit" class="btn btn-sm btn-danger" data-confirm="Delete this announcement?">Delete</button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>

  <div class="modal-overlay" id="newAnnModal">
    <div class="modal">
      <button class="modal-close" onclick="closeModal('newAnnModal')">×</button>
      <h2 class="modal-title">Post Announcement</h2>
      <form method="POST" action="<?= BASE_URL ?>/admin/controllers/admin_controller.php">
        <input type="hidden" name="action" value="create_announcement">
        <div class="form-group"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" required placeholder="Announcement title"></div>
        <div class="form-group"><label class="form-label">Message *</label><textarea name="message" class="form-control" rows="4" required placeholder="Write your announcement..."></textarea></div>
        <button type="submit" class="btn btn-primary" style="width:100%">Post</button>
      </form>
    </div>
  </div>
</main>
</div>
<?php include BASE_PATH.'/includes/footer.php'; ?>
