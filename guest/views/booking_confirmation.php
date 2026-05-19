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

$nights = (int)((strtotime($booking['checkout_date']) - strtotime($booking['checkin_date'])) / 86400);

$pageTitle  = 'Booking Confirmed';
$activeRole = 'guest';
$activePage = 'bookings';
include BASE_PATH . '/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__ . '/sidebar_guest.php'; ?>
<main class="main-content">

  <div class="card" style="max-width:640px;margin:0 auto;text-align:center">
    <div style="font-size:4rem;margin-bottom:0.5rem">🎉</div>
    <h1 style="font-family:'Playfair Display',serif;color:var(--gold);margin-bottom:0.3rem">Booking Confirmed!</h1>
    <p style="color:var(--gray-mid)">Your reservation has been submitted successfully.</p>
  </div>

  <div class="card" style="max-width:640px;margin:1.5rem auto 0">
    <div class="card-title">Booking Summary</div>
    <table style="width:100%;font-size:0.93rem">
      <tr>
        <td style="color:var(--gray-mid);padding:0.4rem 0;width:160px">Booking ID</td>
        <td><strong style="color:var(--gold)">#<?= $booking['id'] ?></strong></td>
      </tr>
      <tr>
        <td style="color:var(--gray-mid);padding:0.4rem 0">Room Type</td>
        <td><strong><?= sanitize($booking['type_name']) ?></strong></td>
      </tr>
      <?php if ($booking['room_number']): ?>
      <tr>
        <td style="color:var(--gray-mid);padding:0.4rem 0">Room Number</td>
        <td>Room <?= sanitize($booking['room_number']) ?></td>
      </tr>
      <?php endif; ?>
      <tr>
        <td style="color:var(--gray-mid);padding:0.4rem 0">Check-in</td>
        <td><?= sanitize($booking['checkin_date']) ?></td>
      </tr>
      <tr>
        <td style="color:var(--gray-mid);padding:0.4rem 0">Check-out</td>
        <td><?= sanitize($booking['checkout_date']) ?></td>
      </tr>
      <tr>
        <td style="color:var(--gray-mid);padding:0.4rem 0">Duration</td>
        <td><?= $nights ?> night<?= $nights > 1 ? 's' : '' ?></td>
      </tr>
      <tr>
        <td style="color:var(--gray-mid);padding:0.4rem 0">Total Price</td>
        <td><strong style="color:var(--gold)">৳<?= number_format($booking['total_price'], 2) ?></strong></td>
      </tr>
      <tr>
        <td style="color:var(--gray-mid);padding:0.4rem 0">Status</td>
        <td><span class="badge badge-<?= $booking['status'] ?>"><?= ucfirst($booking['status']) ?></span></td>
      </tr>
    </table>

    <div style="border-top:1px solid var(--gray-lt);margin-top:1rem;padding-top:1rem;display:flex;gap:0.8rem;flex-wrap:wrap">
      <a href="<?= BASE_URL ?>/guest/views/bookings.php" class="btn btn-primary">View All Bookings</a>
      <a href="<?= BASE_URL ?>/guest/views/search.php" class="btn btn-outline">Book Another Room</a>
    </div>
  </div>
</main>
</div>
<?php include BASE_PATH . '/includes/footer.php'; ?>
