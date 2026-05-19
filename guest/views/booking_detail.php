<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('guest');
require_once __DIR__ . '/../models/GuestModel.php';

$model   = new GuestModel();
$userId  = $_SESSION['user_id'];
$id      = (int)($_GET['id'] ?? 0);
$booking = $model->getBookingById($id);

if (!$booking || $booking['guest_id'] != $userId) {
    flashMessage('error', 'Booking not found');
    header('Location: ' . BASE_URL . '/guest/views/bookings.php'); exit;
}

$nights   = (int)((strtotime($booking['checkout_date']) - strtotime($booking['checkin_date'])) / 86400);
$reviewed = $model->hasReviewed($id, $userId);

$pageTitle  = 'Booking #' . $id;
$activeRole = 'guest';
$activePage = 'bookings';
include BASE_PATH . '/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__ . '/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Booking #<?= $id ?></h1></div>
    <a href="<?= BASE_URL ?>/guest/views/bookings.php" class="btn btn-secondary">&larr; Back</a>
  </div>

  <div style="display:grid;grid-template-columns:1fr 380px;gap:1.5rem;align-items:start">
    <div class="card">
      <div class="card-title">Stay Details</div>
      <table style="width:100%;font-size:0.93rem">
        <tr><td style="color:var(--gray-mid);width:150px;padding:0.4rem 0">Room Type</td><td><strong><?= sanitize($booking['type_name']) ?></strong></td></tr>
        <?php if ($booking['room_number']): ?>
        <tr><td style="color:var(--gray-mid);padding:0.4rem 0">Room Number</td><td>Room <?= sanitize($booking['room_number']) ?></td></tr>
        <?php endif; ?>
        <tr><td style="color:var(--gray-mid);padding:0.4rem 0">Check-in</td><td><?= sanitize($booking['checkin_date']) ?></td></tr>
        <tr><td style="color:var(--gray-mid);padding:0.4rem 0">Check-out</td><td><?= sanitize($booking['checkout_date']) ?></td></tr>
        <tr><td style="color:var(--gray-mid);padding:0.4rem 0">Duration</td><td><?= $nights ?> night<?= $nights > 1 ? 's' : '' ?></td></tr>
        <tr><td style="color:var(--gray-mid);padding:0.4rem 0">Status</td><td><span class="badge badge-<?= $booking['status'] ?>"><?= ucfirst($booking['status']) ?></span></td></tr>
        <tr><td style="color:var(--gray-mid);padding:0.4rem 0">Source</td><td><?= ucfirst($booking['source']) ?></td></tr>
      </table>

      <?php if ($booking['status'] === 'checked_out' && !$reviewed): ?>
      <div style="margin-top:1.2rem">
        <a href="<?= BASE_URL ?>/guest/views/reviews.php?booking_id=<?= $id ?>" class="btn btn-primary">⭐ Leave a Review</a>
      </div>
      <?php endif; ?>

      <?php if ($booking['status'] === 'checked_in'): ?>
      <div style="margin-top:1.2rem">
        <a href="<?= BASE_URL ?>/guest/views/services.php?booking_id=<?= $id ?>" class="btn btn-primary">🛎 Request Service</a>
      </div>
      <?php endif; ?>
    </div>

    <div>
      <div class="card">
        <div class="card-title">Billing</div>
        <?php if ($booking['total_amount']): ?>
        <table style="width:100%;font-size:0.9rem">
          <tr><td style="color:var(--gray-mid);padding:0.3rem 0">Room Charges</td><td style="text-align:right">৳<?= number_format($booking['base_amount'], 2) ?></td></tr>
          <?php if ($booking['extras_amount'] > 0): ?>
          <tr><td style="color:var(--gray-mid);padding:0.3rem 0">Extras</td><td style="text-align:right">৳<?= number_format($booking['extras_amount'], 2) ?></td></tr>
          <?php endif; ?>
          <?php if ($booking['discount_amount'] > 0): ?>
          <tr><td style="color:var(--success);padding:0.3rem 0">Discount</td><td style="text-align:right;color:var(--success)">-৳<?= number_format($booking['discount_amount'], 2) ?></td></tr>
          <?php endif; ?>
          <tr style="border-top:1px solid var(--gray-lt)">
            <td style="font-weight:700;padding-top:0.5rem">Total</td>
            <td style="text-align:right;font-weight:700;color:var(--gold)">৳<?= number_format($booking['total_amount'], 2) ?></td>
          </tr>
        </table>
        <div style="margin-top:0.8rem">
          <span class="badge badge-<?= $booking['payment_status'] === 'paid' ? 'available' : 'pending' ?>"><?= ucfirst($booking['payment_status']) ?></span>
          <?php if ($booking['payment_method']): ?>
          <span style="font-size:0.83rem;color:var(--gray-mid);margin-left:0.5rem">via <?= sanitize($booking['payment_method']) ?></span>
          <?php endif; ?>
        </div>
        <?php if (!empty($booking['receipt_path'])): ?>
        <a href="<?= BASE_URL ?>/<?= sanitize($booking['receipt_path']) ?>" target="_blank" class="btn btn-sm btn-secondary" style="margin-top:0.8rem;display:inline-block">📄 Download Receipt</a>
        <?php endif; ?>
        <?php else: ?>
        <p class="text-muted">Billing details will appear after check-in.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>
</div>
<?php include BASE_PATH . '/includes/footer.php'; ?>
