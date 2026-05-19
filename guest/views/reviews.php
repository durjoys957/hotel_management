<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('guest');
require_once __DIR__ . '/../models/GuestModel.php';

$model      = new GuestModel();
$userId     = $_SESSION['user_id'];
$reviews    = $model->getReviewsByGuest($userId);
$preBooking = (int)($_GET['booking_id'] ?? 0);

// Pre-load booking for review form if given
$preBookingData = null;
if ($preBooking) {
    $preBookingData = $model->getBookingById($preBooking);
    if (!$preBookingData || $preBookingData['guest_id'] != $userId || $preBookingData['status'] !== 'checked_out') {
        $preBookingData = null;
    }
}

$pageTitle  = 'My Reviews';
$activeRole = 'guest';
$activePage = 'reviews';
include BASE_PATH . '/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__ . '/sidebar_guest.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">My Reviews</h1><p class="page-subtitle">Rate and review your completed stays</p></div>
  </div>

  <?php if ($preBookingData): ?>
  <div class="card" style="border-top:3px solid var(--gold)">
    <div class="card-title">Write a Review for <?= sanitize($preBookingData['type_name']) ?></div>
    <form method="POST" action="<?= BASE_URL ?>/guest/controllers/GuestController.php">
      <input type="hidden" name="action" value="create_review">
      <input type="hidden" name="booking_id" value="<?= $preBooking ?>">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Overall Rating *</label>
          <select name="overall_rating" class="form-control" required>
            <option value="">Select...</option>
            <?php for ($i = 5; $i >= 1; $i--): ?>
            <option value="<?= $i ?>"><?= str_repeat('★', $i) ?> (<?= $i ?>)</option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Cleanliness *</label>
          <select name="cleanliness_rating" class="form-control" required>
            <option value="">Select...</option>
            <?php for ($i = 5; $i >= 1; $i--): ?>
            <option value="<?= $i ?>"><?= str_repeat('★', $i) ?> (<?= $i ?>)</option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Service *</label>
          <select name="service_rating" class="form-control" required>
            <option value="">Select...</option>
            <?php for ($i = 5; $i >= 1; $i--): ?>
            <option value="<?= $i ?>"><?= str_repeat('★', $i) ?> (<?= $i ?>)</option>
            <?php endfor; ?>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Your Comments</label>
        <textarea name="review_text" class="form-control" rows="3" placeholder="Share your experience..."></textarea>
      </div>
      <button type="submit" class="btn btn-primary">Submit Review</button>
    </form>
  </div>
  <?php endif; ?>

  <?php if (!$reviews): ?>
  <div class="empty-state"><div class="empty-icon">⭐</div><p>No reviews yet. Complete a stay and share your experience!</p></div>
  <?php else: ?>
  <?php foreach ($reviews as $rv): ?>
  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:0.5rem">
      <div>
        <strong><?= sanitize($rv['type_name']) ?></strong>
        <span style="color:var(--gray-mid);font-size:0.85rem;margin-left:0.5rem"><?= sanitize($rv['checkin_date']) ?> – <?= sanitize($rv['checkout_date']) ?></span>
      </div>
      <small class="text-muted"><?= date('M j, Y', strtotime($rv['created_at'])) ?></small>
    </div>
    <div style="display:flex;gap:1.2rem;margin:0.5rem 0;font-size:0.87rem">
      <span>Overall <span class="stars"><?= str_repeat('★', $rv['overall_rating']) ?></span></span>
      <span>Cleanliness <span class="stars"><?= str_repeat('★', $rv['cleanliness_rating']) ?></span></span>
      <span>Service <span class="stars"><?= str_repeat('★', $rv['service_rating']) ?></span></span>
    </div>
    <?php if ($rv['review_text']): ?>
    <p style="font-size:0.88rem;color:var(--gray-dk);">"<?= sanitize($rv['review_text']) ?>"</p>
    <?php endif; ?>
    <?php if ($rv['admin_reply']): ?>
    <div style="background:var(--gray-lt);border-radius:6px;padding:0.6rem 0.8rem;font-size:0.84rem;margin:0.5rem 0">
      <strong style="color:var(--gold)">🏨 Hotel Reply:</strong> <?= sanitize($rv['admin_reply']) ?>
    </div>
    <?php endif; ?>
    <div style="display:flex;gap:0.5rem;margin-top:0.6rem">
      <button onclick="openEditReview(<?= htmlspecialchars(json_encode($rv)) ?>)" class="btn btn-sm btn-secondary">Edit</button>
      <form method="POST" action="<?= BASE_URL ?>/guest/controllers/GuestController.php" style="display:inline">
        <input type="hidden" name="action" value="delete_review">
        <input type="hidden" name="review_id" value="<?= $rv['id'] ?>">
        <button type="submit" class="btn btn-sm btn-danger" data-confirm="Delete this review?">Delete</button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>

  <!-- Edit Review Modal -->
  <div class="modal-overlay" id="editReviewModal">
    <div class="modal">
      <button class="modal-close" onclick="closeModal('editReviewModal')">×</button>
      <h2 class="modal-title">Edit Review</h2>
      <form method="POST" action="<?= BASE_URL ?>/guest/controllers/GuestController.php">
        <input type="hidden" name="action" value="update_review">
        <input type="hidden" name="review_id" id="editReviewId">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Overall Rating</label>
            <select name="overall_rating" id="editOverall" class="form-control" required>
              <?php for ($i = 5; $i >= 1; $i--): ?>
              <option value="<?= $i ?>"><?= str_repeat('★', $i) ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Cleanliness</label>
            <select name="cleanliness_rating" id="editClean" class="form-control" required>
              <?php for ($i = 5; $i >= 1; $i--): ?>
              <option value="<?= $i ?>"><?= str_repeat('★', $i) ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Service</label>
            <select name="service_rating" id="editService" class="form-control" required>
              <?php for ($i = 5; $i >= 1; $i--): ?>
              <option value="<?= $i ?>"><?= str_repeat('★', $i) ?></option>
              <?php endfor; ?>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Comments</label>
          <textarea name="review_text" id="editText" class="form-control" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%">Save Changes</button>
      </form>
    </div>
  </div>
</main>
</div>
<script>
function openEditReview(rv) {
  document.getElementById('editReviewId').value  = rv.id;
  document.getElementById('editOverall').value   = rv.overall_rating;
  document.getElementById('editClean').value     = rv.cleanliness_rating;
  document.getElementById('editService').value   = rv.service_rating;
  document.getElementById('editText').value      = rv.review_text || '';
  openModal('editReviewModal');
}
</script>
<?php include BASE_PATH . '/includes/footer.php'; ?>
