<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('admin');
require_once __DIR__ . '/../models/AdminModel.php';
$model   = new AdminModel();
$filter  = $_GET['filter'] ?? '';
$reviews = $model->getReviews($filter === 'unanswered');
$ratings = $model->getAvgRatings();
$pageTitle='Guest Reviews'; $activeRole='admin'; $activePage='reviews';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Guest Reviews</h1><p class="page-subtitle"><?= $ratings['cnt'] ?> total reviews</p></div>
  </div>

  <?php if ($ratings['cnt'] > 0): ?>
  <div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Overall Avg</div><div class="stat-value text-gold"><?= number_format($ratings['ov'],1) ?>/5</div></div>
    <div class="stat-card"><div class="stat-label">Cleanliness</div><div class="stat-value"><?= number_format($ratings['cl'],1) ?>/5</div></div>
    <div class="stat-card"><div class="stat-label">Service</div><div class="stat-value"><?= number_format($ratings['sv'],1) ?>/5</div></div>
    <div class="stat-card"><div class="stat-label">Total Reviews</div><div class="stat-value"><?= $ratings['cnt'] ?></div></div>
  </div>
  <?php endif; ?>

  <div style="display:flex;gap:0.5rem;margin-bottom:1.2rem">
    <a href="?" class="btn btn-sm <?= !$filter?'btn-primary':'btn-outline' ?>">All</a>
    <a href="?filter=unanswered" class="btn btn-sm <?= $filter==='unanswered'?'btn-primary':'btn-outline' ?>">Unanswered</a>
  </div>

  <?php if (!$reviews): ?>
  <div class="empty-state"><div class="empty-icon">⭐</div><p>No reviews found.</p></div>
  <?php else: ?>
  <?php foreach ($reviews as $rv): ?>
  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:0.5rem">
      <div>
        <strong><?= sanitize($rv['guest_name']) ?></strong>
        <span class="badge badge-confirmed" style="margin-left:0.5rem"><?= sanitize($rv['type_name']) ?></span>
        <span style="color:var(--gray-mid);font-size:0.85rem;margin-left:0.5rem"><?= sanitize($rv['checkin_date']) ?> – <?= sanitize($rv['checkout_date']) ?></span>
      </div>
      <small class="text-muted"><?= date('M j, Y', strtotime($rv['created_at'])) ?></small>
    </div>
    <div style="display:flex;gap:1.5rem;margin:0.6rem 0;font-size:0.88rem">
      <span>Overall <span class="stars"><?= str_repeat('★',$rv['overall_rating']) ?></span></span>
      <span>Cleanliness <span class="stars"><?= str_repeat('★',$rv['cleanliness_rating']) ?></span></span>
      <span>Service <span class="stars"><?= str_repeat('★',$rv['service_rating']) ?></span></span>
    </div>
    <?php if ($rv['review_text']): ?>
    <p style="font-size:0.9rem;color:var(--gray-dk);margin-bottom:0.8rem">"<?= sanitize($rv['review_text']) ?>"</p>
    <?php endif; ?>

    <?php if ($rv['admin_reply']): ?>
    <div style="background:var(--gray-lt);border-radius:6px;padding:0.7rem;font-size:0.87rem;margin-bottom:0.7rem">
      <strong style="color:var(--gold)">🏨 Hotel Reply:</strong> <?= sanitize($rv['admin_reply']) ?>
    </div>
    <?php else: ?>
    <form method="POST" action="<?= BASE_URL ?>/admin/controllers/admin_controller.php" style="display:flex;gap:0.5rem;align-items:flex-end">
      <input type="hidden" name="action" value="reply_review">
      <input type="hidden" name="review_id" value="<?= $rv['id'] ?>">
      <div style="flex:1"><label class="form-label" style="font-size:0.82rem">Reply as Hotel</label>
        <input type="text" name="reply" class="form-control" placeholder="Write an official response..." required>
      </div>
      <button type="submit" class="btn btn-primary btn-sm" style="margin-bottom:2px">Post Reply</button>
    </form>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</main>
</div>
<?php include BASE_PATH.'/includes/footer.php'; ?>
