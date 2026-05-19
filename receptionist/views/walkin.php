<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('receptionist');
require_once __DIR__ . '/../models/ReceptionistModel.php';
$model     = new ReceptionistModel();
$roomTypes = $model->getRoomTypes();
$pageTitle='Walk-in Check-in'; $activeRole='receptionist'; $activePage='walkin';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Walk-in Check-in</h1><p class="page-subtitle">Register a new walk-in guest</p></div>
  </div>
  <div class="card" style="max-width:700px">
    <form method="POST" action="<?= BASE_URL ?>/receptionist/controllers/receptionist_controller.php">
      <input type="hidden" name="action" value="walkin">
      <div class="card-title">Guest Information</div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Full Name *</label><input type="text" name="guest_name" class="form-control" required placeholder="John Doe"></div>
        <div class="form-group"><label class="form-label">Email *</label><input type="email" name="guest_email" class="form-control" required placeholder="guest@email.com"></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Phone</label><input type="tel" name="guest_phone" class="form-control" placeholder="+88..."></div>
        <div class="form-group"><label class="form-label">Nationality</label><input type="text" name="nationality" class="form-control" placeholder="Bangladeshi"></div>
      </div>
      <div class="form-group"><label class="form-label">ID Number</label><input type="text" name="id_number" class="form-control" placeholder="NID/Passport"></div>

      <hr class="divider">
      <div class="card-title">Booking Details</div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Room Type *</label>
          <select name="room_type_id" id="roomTypeSelect" class="form-control" required onchange="loadRooms(this.value)">
            <option value="">Select type...</option>
            <?php foreach ($roomTypes as $rt): ?>
            <option value="<?= $rt['id'] ?>">
              <?= sanitize($rt['name']) ?> — ৳<?= number_format($rt['price_per_night'],2) ?>/night (max <?= $rt['max_capacity'] ?> guests)
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><label class="form-label">Available Room *</label>
          <select name="room_id" id="roomSelect" class="form-control" required><option value="">Select type first...</option></select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Check-in Date *</label><input type="date" name="checkin_date" class="form-control" value="<?= date('Y-m-d') ?>" required onchange="calcTotal()"></div>
        <div class="form-group"><label class="form-label">Check-out Date *</label><input type="date" name="checkout_date" id="checkout" class="form-control" required onchange="calcTotal()"></div>
      </div>
      <div class="form-group"><label class="form-label">Number of Guests *</label>
        <select name="num_guests" class="form-control"><option>1</option><option>2</option><option>3</option><option>4</option></select>
      </div>
      <div id="totalPreview" style="background:var(--gray-lt);border-radius:8px;padding:1rem;margin-bottom:1rem;display:none">
        Total: <strong id="totalText" style="color:var(--gold)"></strong>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%">✅ Create Walk-in Booking & Check In</button>
    </form>
  </div>
</main>
</div>
<script>
const BASE_URL = '<?= BASE_URL ?>';
const roomTypeData = <?= json_encode(array_column($roomTypes, null, 'id')) ?>;

function loadRooms(typeId) {
  if (!typeId) return;
  ajaxGet(BASE_URL + '/api/available_rooms.php?type_id=' + typeId, function(err, data) {
    const sel = document.getElementById('roomSelect');
    sel.innerHTML = '<option value="">Select room...</option>';
    if (data && data.rooms) {
      data.rooms.forEach(r => sel.innerHTML += `<option value="${r.id}">Room ${r.room_number} (Floor ${r.floor})</option>`);
    }
  });
  calcTotal();
}

function calcTotal() {
  const typeId = document.getElementById('roomTypeSelect').value;
  const checkout = document.getElementById('checkout').value;
  const checkin = document.querySelector('[name=checkin_date]').value;
  if (!typeId || !checkin || !checkout || checkin >= checkout) { document.getElementById('totalPreview').style.display='none'; return; }
  const rt = roomTypeData[typeId];
  const nights = (new Date(checkout) - new Date(checkin)) / 86400000;
  const total = nights * rt.price_per_night;
  document.getElementById('totalText').textContent = nights + ' night(s) × ৳' + parseFloat(rt.price_per_night).toFixed(2) + ' = ৳' + total.toFixed(2);
  document.getElementById('totalPreview').style.display = 'block';
}
</script>
<?php include BASE_PATH.'/includes/footer.php'; ?>
