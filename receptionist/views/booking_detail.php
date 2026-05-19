<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('receptionist');
require_once __DIR__ . '/../models/ReceptionistModel.php';
$model   = new ReceptionistModel();
$id      = (int)($_GET['id'] ?? 0);
$booking = $model->getBookingById($id);
if (!$booking) { flashMessage('error','Booking not found'); header('Location:'.BASE_URL.'/receptionist/views/dashboard.php'); exit; }
$bill    = $model->getBillingForBooking($id);
$availableRooms = $model->getAvailableRoomsOfType($booking['room_type_id']);
$pageTitle='Booking #'.$id; $activeRole='receptionist'; $activePage='bookings';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Booking #<?= $id ?></h1><span class="badge badge-<?= $booking['status'] ?>"><?= $booking['status'] ?></span></div>
    <a href="javascript:history.back()" class="btn btn-secondary">← Back</a>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">
    <!-- Booking Info -->
    <div class="card">
      <div class="card-title">Booking Details</div>
      <table style="font-size:0.9rem">
        <tr><td style="color:var(--gray-mid);padding:0.4rem 1rem 0.4rem 0;width:140px">Guest</td><td><strong><?= sanitize($booking['guest_name']) ?></strong></td></tr>
        <tr><td style="color:var(--gray-mid);padding:0.4rem 1rem 0.4rem 0">Email</td><td><?= sanitize($booking['email']) ?></td></tr>
        <tr><td style="color:var(--gray-mid);padding:0.4rem 1rem 0.4rem 0">Phone</td><td><?= sanitize($booking['phone'] ?? '—') ?></td></tr>
        <tr><td style="color:var(--gray-mid);padding:0.4rem 1rem 0.4rem 0">ID Number</td><td><?= sanitize($booking['id_number'] ?? '—') ?></td></tr>
        <tr><td style="color:var(--gray-mid);padding:0.4rem 1rem 0.4rem 0">Nationality</td><td><?= sanitize($booking['nationality'] ?? '—') ?></td></tr>
        <tr><td style="color:var(--gray-mid);padding:0.4rem 1rem 0.4rem 0">Room Type</td><td><?= sanitize($booking['type_name']) ?></td></tr>
        <tr><td style="color:var(--gray-mid);padding:0.4rem 1rem 0.4rem 0">Room</td><td><?= $booking['room_number'] ? 'Room '.sanitize($booking['room_number']).' (Floor '.$booking['floor'].')' : '<em>Not assigned</em>' ?></td></tr>
        <tr><td style="color:var(--gray-mid);padding:0.4rem 1rem 0.4rem 0">Check-in</td><td><?= sanitize($booking['checkin_date']) ?></td></tr>
        <tr><td style="color:var(--gray-mid);padding:0.4rem 1rem 0.4rem 0">Check-out</td><td><?= sanitize($booking['checkout_date']) ?></td></tr>
        <tr><td style="color:var(--gray-mid);padding:0.4rem 1rem 0.4rem 0">Guests</td><td><?= $booking['num_guests'] ?></td></tr>
        <tr><td style="color:var(--gray-mid);padding:0.4rem 1rem 0.4rem 0">Source</td><td><span class="badge badge-<?= $booking['source'] ?>"><?= $booking['source'] ?></span></td></tr>
        <?php if ($booking['special_requests']): ?>
        <tr><td style="color:var(--gray-mid);padding:0.4rem 1rem 0.4rem 0;vertical-align:top">Notes</td><td style="font-size:0.85rem"><?= nl2br(sanitize($booking['special_requests'])) ?></td></tr>
        <?php endif; ?>
      </table>
    </div>

    <!-- Billing -->
    <div>
      <div class="card">
        <div class="card-title">Billing</div>
        <?php if ($bill): ?>
        <table style="font-size:0.9rem;width:100%">
          <tr><td style="color:var(--gray-mid);padding:0.4rem 0">Room Charges</td><td style="text-align:right">৳<?= number_format($bill['base_amount'],2) ?></td></tr>
          <tr><td style="color:var(--gray-mid);padding:0.4rem 0">Extras</td><td style="text-align:right">৳<?= number_format($bill['extras_amount'],2) ?></td></tr>
          <tr><td style="color:var(--success);padding:0.4rem 0">Discount</td><td style="text-align:right;color:var(--success)">-৳<?= number_format($bill['discount_amount'],2) ?></td></tr>
          <tr style="border-top:2px solid var(--gold)"><td style="padding:0.6rem 0"><strong>Total</strong></td><td style="text-align:right"><strong style="color:var(--gold);font-size:1.1rem">৳<?= number_format($bill['total_amount'],2) ?></strong></td></tr>
        </table>
        <div style="margin-top:0.5rem">Status: <span class="badge badge-<?= $bill['payment_status'] ?>"><?= $bill['payment_status'] ?></span>
        <?php if ($bill['payment_method']): ?>&nbsp;via <?= sanitize($bill['payment_method']) ?><?php endif; ?>
        <?php if ($bill['paid_at']): ?><br><small class="text-muted">Paid <?= date('M j, g:i a', strtotime($bill['paid_at'])) ?></small><?php endif; ?>
        </div>
        <?php else: ?><p class="text-muted">No billing record.</p><?php endif; ?>

        <!-- Actions based on status -->
        <?php if ($booking['status'] === 'confirmed'): ?>
        <hr class="divider">
        <div class="card-title">Check In Guest</div>
        <form method="POST" action="<?= BASE_URL ?>/receptionist/controllers/receptionist_controller.php">
          <input type="hidden" name="action" value="checkin">
          <input type="hidden" name="booking_id" value="<?= $id ?>">
          <div class="form-group">
            <label class="form-label">Assign Room *</label>
            <select name="room_id" class="form-control" required>
              <option value="">Select available room...</option>
              <?php foreach ($availableRooms as $r): ?>
              <option value="<?= $r['id'] ?>">Room <?= sanitize($r['room_number']) ?> (Floor <?= $r['floor'] ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-success" style="width:100%" data-confirm="Confirm check-in?">✅ Confirm Check-in</button>
        </form>
        <?php endif; ?>

        <?php if ($booking['status'] === 'checked_in' && $bill && $bill['payment_status'] !== 'paid'): ?>
        <hr class="divider">
        <!-- Add extra -->
        <div class="card-title">Add Extra Charge</div>
        <form method="POST" action="<?= BASE_URL ?>/receptionist/controllers/receptionist_controller.php" style="display:flex;gap:0.5rem;margin-bottom:1rem">
          <input type="hidden" name="action" value="add_extra">
          <input type="hidden" name="booking_id" value="<?= $id ?>">
          <input type="number" name="amount" class="form-control" placeholder="Amount (৳)" step="0.01" min="0.01" required>
          <input type="text" name="description" class="form-control" placeholder="Description">
          <button type="submit" class="btn btn-secondary">Add</button>
        </form>
        <!-- Loyalty points -->
        <div class="card-title">Apply Loyalty Points</div>
        <form method="POST" action="<?= BASE_URL ?>/receptionist/controllers/receptionist_controller.php" style="display:flex;gap:0.5rem;margin-bottom:1rem">
          <input type="hidden" name="action" value="apply_points">
          <input type="hidden" name="booking_id" value="<?= $id ?>">
          <input type="hidden" name="guest_id" value="<?= $booking['guest_id'] ?>">
          <input type="number" name="points" class="form-control" placeholder="Points to redeem" min="1" required>
          <button type="submit" class="btn btn-outline">Apply</button>
        </form>
        <!-- Process payment -->
        <div class="card-title">Process Payment</div>
        <form method="POST" action="<?= BASE_URL ?>/receptionist/controllers/receptionist_controller.php" style="display:flex;gap:0.5rem">
          <input type="hidden" name="action" value="process_payment">
          <input type="hidden" name="booking_id" value="<?= $id ?>">
          <select name="payment_method" class="form-control" required>
            <option value="">Method...</option>
            <option value="cash">Cash</option>
            <option value="card">Card</option>
            <option value="online">Online</option>
          </select>
          <button type="submit" class="btn btn-success" data-confirm="Mark bill as paid?">💳 Pay</button>
        </form>
        <?php endif; ?>

        <?php if ($booking['status'] === 'checked_in' && $bill && $bill['payment_status'] === 'paid'): ?>
        <hr class="divider">
        <form method="POST" action="<?= BASE_URL ?>/receptionist/controllers/receptionist_controller.php">
          <input type="hidden" name="action" value="checkout">
          <input type="hidden" name="booking_id" value="<?= $id ?>">
          <button type="submit" class="btn btn-danger" style="width:100%" data-confirm="Confirm check-out? Room will be marked dirty.">🚪 Check Out</button>
        </form>
        <?php endif; ?>
      </div>

      <!-- Modify dates -->
      <?php if (in_array($booking['status'],['confirmed','checked_in'])): ?>
      <div class="card">
        <div class="card-title">Modify Dates</div>
        <form method="POST" action="<?= BASE_URL ?>/receptionist/controllers/receptionist_controller.php">
          <input type="hidden" name="action" value="modify_dates">
          <input type="hidden" name="booking_id" value="<?= $id ?>">
          <div class="form-row">
            <div class="form-group"><label class="form-label">New Check-in</label><input type="date" name="new_checkin" class="form-control" value="<?= sanitize($booking['checkin_date']) ?>" required></div>
            <div class="form-group"><label class="form-label">New Check-out</label><input type="date" name="new_checkout" class="form-control" value="<?= sanitize($booking['checkout_date']) ?>" required></div>
          </div>
          <button type="submit" class="btn btn-secondary">Update Dates</button>
        </form>
      </div>
      <?php endif; ?>
    </div>
  </div>
</main>
</div>
<?php include BASE_PATH.'/includes/footer.php'; ?>
