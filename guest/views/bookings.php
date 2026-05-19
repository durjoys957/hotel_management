<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('guest');
require_once __DIR__ . '/../models/GuestModel.php';

$model    = new GuestModel();
$userId   = $_SESSION['user_id'];
$bookings = $model->getGuestBookings($userId);

$cancelDays = defined('CANCEL_DAYS_BEFORE') ? CANCEL_DAYS_BEFORE : 2;

$pageTitle  = 'My Bookings';
$activeRole = 'guest';
$activePage = 'bookings';
include BASE_PATH . '/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__ . '/sidebar_guest.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">My Bookings</h1><p class="page-subtitle"><?= count($bookings) ?> total bookings</p></div>
    <a href="<?= BASE_URL ?>/guest/views/search.php" class="btn btn-primary">+ New Booking</a>
  </div>

  <?php if (!$bookings): ?>
  <div class="empty-state"><div class="empty-icon">📋</div><p>No bookings yet. <a href="<?= BASE_URL ?>/guest/views/search.php">Search for available rooms</a></p></div>
  <?php else: ?>
  <?php foreach ($bookings as $b):
    $nights         = (int)((strtotime($b['checkout_date']) - strtotime($b['checkin_date'])) / 86400);
    $daysUntilIn    = (strtotime($b['checkin_date']) - time()) / 86400;
    $canCancel      = in_array($b['status'], ['pending','confirmed']) && $daysUntilIn >= $cancelDays;
    $canModify      = in_array($b['status'], ['pending','confirmed']) && $daysUntilIn > 0;
    $canReview      = $b['status'] === 'checked_out';
    $canService     = $b['status'] === 'checked_in';
  ?>
  <div class="card">
    <div style="display:grid;grid-template-columns:auto 1fr auto;gap:1rem;align-items:start">
      <?php if ($b['thumbnail_path']): ?>
      <img src="<?= BASE_URL ?>/<?= sanitize($b['thumbnail_path']) ?>" style="width:100px;height:80px;object-fit:cover;border-radius:8px">
      <?php else: ?>
      <div style="width:100px;height:80px;background:var(--gray-lt);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:2rem">🏨</div>
      <?php endif; ?>
      <div>
        <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;margin-bottom:0.3rem">
          <strong style="font-size:1rem"><?= sanitize($b['type_name']) ?></strong>
          <?php if ($b['room_number']): ?><span class="badge badge-confirmed">Room <?= sanitize($b['room_number']) ?></span><?php endif; ?>
          <span class="badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span>
        </div>
        <div style="color:var(--gray-mid);font-size:0.85rem">
          <?= sanitize($b['checkin_date']) ?> &mdash; <?= sanitize($b['checkout_date']) ?> &bull; <?= $nights ?> night<?= $nights > 1 ? 's' : '' ?>
        </div>
        <div style="margin-top:0.3rem">
          <strong style="color:var(--gold)">৳<?= number_format($b['total_price'], 2) ?></strong>
          <?php if ($b['payment_status']): ?>
          <span class="badge badge-<?= $b['payment_status'] === 'paid' ? 'available' : 'pending' ?>" style="margin-left:0.4rem"><?= ucfirst($b['payment_status']) ?></span>
          <?php endif; ?>
        </div>
      </div>
      <div style="text-align:right;display:flex;flex-direction:column;gap:0.4rem">
        <a href="<?= BASE_URL ?>/guest/views/booking_detail.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-secondary">View</a>

        <?php if ($canService): ?>
        <a href="<?= BASE_URL ?>/guest/views/services.php?booking_id=<?= $b['id'] ?>" class="btn btn-sm btn-primary">Request Service</a>
        <?php endif; ?>

        <?php if ($canReview): ?>
        <a href="<?= BASE_URL ?>/guest/views/reviews.php?booking_id=<?= $b['id'] ?>" class="btn btn-sm btn-outline">Leave Review</a>
        <?php endif; ?>

        <?php if ($canModify): ?>
        <button onclick="openModifyModal(<?= $b['id'] ?>, '<?= $b['checkin_date'] ?>', '<?= $b['checkout_date'] ?>')" class="btn btn-sm btn-secondary">Modify Dates</button>
        <?php endif; ?>

        <?php if ($canCancel): ?>
        <form method="POST" action="<?= BASE_URL ?>/guest/controllers/GuestController.php" style="display:inline">
          <input type="hidden" name="action" value="cancel_booking">
          <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
          <button type="submit" class="btn btn-sm btn-danger" data-confirm="Cancel booking #<?= $b['id'] ?>? This cannot be undone.">Cancel</button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>

  <!-- Modify Dates Modal -->
  <div class="modal-overlay" id="modifyModal">
    <div class="modal">
      <button class="modal-close" onclick="closeModal('modifyModal')">×</button>
      <h2 class="modal-title">Request Date Modification</h2>
      <p style="color:var(--gray-mid);font-size:0.88rem;margin-bottom:1rem">A receptionist will review and confirm the change.</p>
      <form method="POST" action="<?= BASE_URL ?>/guest/controllers/GuestController.php">
        <input type="hidden" name="action" value="request_modification">
        <input type="hidden" name="booking_id" id="modifyBookingId">
        <div class="form-row">
          <div class="form-group"><label class="form-label">New Check-in</label><input type="date" name="new_checkin_date" id="modifyCheckin" class="form-control" required></div>
          <div class="form-group"><label class="form-label">New Check-out</label><input type="date" name="new_checkout_date" id="modifyCheckout" class="form-control" required></div>
        </div>
        <div style="background:var(--gray-lt);border-radius:6px;padding:0.7rem;font-size:0.85rem;margin-bottom:1rem">
          ⚠ Cancellation policy: bookings can only be cancelled at least <?= $cancelDays ?> days before check-in.
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%">Submit Request</button>
      </form>
    </div>
  </div>
</main>
</div>
<script>
function openModifyModal(id, checkin, checkout) {
  document.getElementById('modifyBookingId').value = id;
  document.getElementById('modifyCheckin').value   = checkin;
  document.getElementById('modifyCheckout').value  = checkout;
  openModal('modifyModal');
}
</script>
<?php include BASE_PATH . '/includes/footer.php'; ?>
