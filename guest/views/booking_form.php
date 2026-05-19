<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('guest');
require_once __DIR__ . '/../models/GuestModel.php';

$model      = new GuestModel();
$userId     = $_SESSION['user_id'];
$roomTypeId = (int)($_GET['room_type_id'] ?? 0);
$checkin    = $_GET['checkin']  ?? '';
$checkout   = $_GET['checkout'] ?? '';
$numGuests  = (int)($_GET['guests'] ?? 1);
$roomType   = $model->getRoomTypeById($roomTypeId);

if (!$roomType || !$checkin || !$checkout) {
    flashMessage('error', 'Invalid booking parameters');
    header('Location: ' . BASE_URL . '/guest/views/search.php'); exit;
}

$nights   = (int)((strtotime($checkout) - strtotime($checkin)) / 86400);
$seasonal = $model->getSeasonalPrice($roomTypeId, $checkin, $checkout);
$price    = $seasonal ? $seasonal['price_per_night'] : $roomType['price_per_night'];
$total    = $price * $nights;
$balance  = $model->getLoyaltyBalance($userId);
$maxRedeem = min($balance, (int)($total / POINTS_VALUE));

$pageTitle  = 'Confirm Booking';
$activeRole = 'guest';
$activePage = 'bookings';
include BASE_PATH . '/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__ . '/sidebar_guest.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Confirm Your Booking</h1></div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 380px;gap:1.5rem;align-items:start">
    <div class="card">
      <div class="card-title">Booking Details</div>
      <form method="POST" action="<?= BASE_URL ?>/guest/controllers/GuestController.php">
        <input type="hidden" name="action" value="create_booking">
        <input type="hidden" name="room_type_id" value="<?= $roomTypeId ?>">
        <input type="hidden" name="checkin_date"  value="<?= sanitize($checkin) ?>">
        <input type="hidden" name="checkout_date" value="<?= sanitize($checkout) ?>">

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Check-in</label>
            <input type="text" class="form-control" value="<?= sanitize($checkin) ?>" disabled style="opacity:0.7">
          </div>
          <div class="form-group">
            <label class="form-label">Check-out</label>
            <input type="text" class="form-control" value="<?= sanitize($checkout) ?>" disabled style="opacity:0.7">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Number of Guests</label>
          <select name="num_guests" class="form-control">
            <?php for ($i = 1; $i <= $roomType['max_capacity']; $i++): ?>
            <option value="<?= $i ?>" <?= $numGuests === $i ? 'selected' : '' ?>><?= $i ?> guest<?= $i > 1 ? 's' : '' ?></option>
            <?php endfor; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Special Requests</label>
          <textarea name="special_requests" class="form-control" rows="3" placeholder="Any special requests? (optional)"></textarea>
        </div>

        <?php if ($balance > 0): ?>
        <div class="form-group">
          <label class="form-label">Redeem Loyalty Points</label>
          <div style="display:flex;align-items:center;gap:1rem">
            <input type="number" name="use_points" id="usePoints" class="form-control" min="0" max="<?= $maxRedeem ?>" value="0" style="flex:1" oninput="updateTotal()">
            <span style="color:var(--gray-mid);font-size:0.85rem">Max: <?= number_format($maxRedeem) ?> pts (saves ৳<?= number_format($maxRedeem * POINTS_VALUE, 2) ?>)</span>
          </div>
          <span class="form-hint">You have <?= number_format($balance) ?> points available</span>
        </div>
        <?php else: ?>
        <input type="hidden" name="use_points" value="0">
        <?php endif; ?>

        <button type="submit" class="btn btn-primary" style="width:100%;margin-top:0.5rem">Confirm Booking</button>
      </form>
    </div>

    <div>
      <div class="card" style="background:var(--navy);color:var(--white)">
        <div class="card-title" style="color:var(--gold)">Price Summary</div>
        <?php if ($roomType['thumbnail_path']): ?>
        <img src="<?= BASE_URL ?>/<?= sanitize($roomType['thumbnail_path']) ?>" style="width:100%;border-radius:8px;margin-bottom:1rem;height:130px;object-fit:cover">
        <?php endif; ?>
        <strong style="font-size:1.1rem"><?= sanitize($roomType['name']) ?></strong>
        <?php if ($seasonal): ?>
        <span class="badge badge-confirmed" style="display:block;margin:0.3rem 0">🎉 <?= sanitize($seasonal['label']) ?> pricing applied</span>
        <?php endif; ?>
        <table style="width:100%;font-size:0.9rem;margin-top:1rem">
          <tr><td style="color:var(--gray-mid);padding:0.3rem 0">Rate/Night</td><td style="text-align:right">৳<?= number_format($price, 2) ?></td></tr>
          <tr><td style="color:var(--gray-mid);padding:0.3rem 0">Nights</td><td style="text-align:right"><?= $nights ?></td></tr>
          <tr><td style="color:var(--gray-mid);padding:0.3rem 0">Subtotal</td><td style="text-align:right">৳<?= number_format($total, 2) ?></td></tr>
          <?php if ($balance > 0): ?>
          <tr id="discountRow" style="display:none"><td style="color:var(--success);padding:0.3rem 0">Points Discount</td><td id="discountAmt" style="text-align:right;color:var(--success)"></td></tr>
          <?php endif; ?>
          <tr style="border-top:1px solid rgba(255,255,255,0.2)">
            <td style="padding-top:0.5rem;font-weight:700">Total</td>
            <td id="totalAmt" style="text-align:right;font-weight:700;color:var(--gold);font-size:1.2rem">৳<?= number_format($total, 2) ?></td>
          </tr>
        </table>
        <p style="color:var(--gray-mid);font-size:0.78rem;margin-top:0.8rem">Payment is collected at the hotel during check-in</p>
      </div>
      <a href="<?= BASE_URL ?>/guest/views/room_detail.php?id=<?= $roomTypeId ?>&checkin=<?= urlencode($checkin) ?>&checkout=<?= urlencode($checkout) ?>&guests=<?= $numGuests ?>" class="btn btn-secondary" style="width:100%;margin-top:0.8rem">&larr; Back to Room Details</a>
    </div>
  </div>
</main>
</div>
<script>
const baseTotal   = <?= $total ?>;
const pointsValue = <?= POINTS_VALUE ?>;

function updateTotal() {
  const pts      = parseInt(document.getElementById('usePoints')?.value || 0);
  const discount = pts * pointsValue;
  const final    = Math.max(0, baseTotal - discount);

  document.getElementById('totalAmt').textContent = '৳' + final.toFixed(2);
  const row = document.getElementById('discountRow');
  if (row) {
    row.style.display = discount > 0 ? '' : 'none';
    document.getElementById('discountAmt').textContent = '-৳' + discount.toFixed(2);
  }
}
</script>
<?php include BASE_PATH . '/includes/footer.php'; ?>
