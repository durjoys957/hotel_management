<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('guest');

$pageTitle  = 'Search Rooms';
$activeRole = 'guest';
$activePage = 'search';
include BASE_PATH . '/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__ . '/sidebar_guest.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div>
      <h1 class="page-title">Search Available Rooms</h1>
      <p class="page-subtitle">Find the perfect room for your stay</p>
    </div>
  </div>

  <div class="card">
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:1rem;align-items:end">
      <div>
        <label class="form-label">Check-in Date *</label>
        <input type="date" id="checkin" class="form-control" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($_GET['checkin'] ?? '') ?>">
      </div>
      <div>
        <label class="form-label">Check-out Date *</label>
        <input type="date" id="checkout" class="form-control" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" value="<?= htmlspecialchars($_GET['checkout'] ?? '') ?>">
      </div>
      <div>
        <label class="form-label">Guests</label>
        <select id="guests" class="form-control">
          <?php for ($i = 1; $i <= 6; $i++): ?>
          <option value="<?= $i ?>" <?= (($_GET['guests'] ?? 1) == $i) ? 'selected' : '' ?>><?= $i ?> Guest<?= $i > 1 ? 's' : '' ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div>
        <button id="searchBtn" class="btn btn-primary">Search</button>
      </div>
    </div>
  </div>

  <div id="searchResults"></div>
</main>
</div>

<script>
const BASE_URL = '<?= BASE_URL ?>';

document.getElementById('searchBtn').addEventListener('click', searchRooms);

// Auto-search if params provided via GET
window.addEventListener('DOMContentLoaded', () => {
  if (document.getElementById('checkin').value && document.getElementById('checkout').value) {
    searchRooms();
  }
});

function searchRooms() {
  const checkin  = document.getElementById('checkin').value;
  const checkout = document.getElementById('checkout').value;
  const guests   = document.getElementById('guests').value;

  if (!checkin || !checkout) {
    document.getElementById('searchResults').innerHTML = '<div class="card"><p style="color:var(--danger)">Please select both check-in and check-out dates.</p></div>';
    return;
  }
  if (checkin >= checkout) {
    document.getElementById('searchResults').innerHTML = '<div class="card"><p style="color:var(--danger)">Check-out must be after check-in.</p></div>';
    return;
  }

  document.getElementById('searchResults').innerHTML = '<div class="card"><p style="color:var(--gray-mid)">Searching...</p></div>';

  const xhr = new XMLHttpRequest();
  xhr.open('GET', BASE_URL + '/guest/controllers/GuestController.php?action=search_rooms_ajax&checkin=' + checkin + '&checkout=' + checkout + '&guests=' + guests, true);
  xhr.onload = function() {
    if (xhr.status === 200) {
      const rooms = JSON.parse(xhr.responseText);
      renderResults(rooms, checkin, checkout, guests);
    }
  };
  xhr.send();
}

function renderResults(rooms, checkin, checkout, guests) {
  const container = document.getElementById('searchResults');
  if (!rooms.length) {
    container.innerHTML = '<div class="card"><div class="empty-state"><div class="empty-icon">🏨</div><p>No rooms available for the selected dates and guest count.</p></div></div>';
    return;
  }

  let html = '<div style="margin-bottom:0.8rem"><strong>' + rooms.length + ' room type(s) available</strong></div>';
  rooms.forEach(r => {
    const amenities = Array.isArray(r.amenities) ? r.amenities : (r.amenities ? JSON.parse(r.amenities) : []);
    const seasonal  = r.seasonal ? '<span class="badge badge-confirmed" style="margin-left:0.5rem">🎉 ' + r.seasonal.label + ' pricing</span>' : '';
    const origPrice = r.seasonal ? '<span style="text-decoration:line-through;color:var(--gray-mid);font-size:0.85rem;margin-right:0.4rem">৳' + parseFloat(r.price_per_night).toFixed(2) + '</span>' : '';
    const avgRating = r.avg_rating ? '<span style="color:var(--gold)">★ ' + parseFloat(r.avg_rating).toFixed(1) + '</span>' : '';

    html += `
    <div class="card" style="display:grid;grid-template-columns:200px 1fr auto;gap:1.5rem;align-items:start">
      <div>
        ${r.thumbnail_path ? `<img src="${BASE_URL}/${r.thumbnail_path}" style="width:100%;height:130px;object-fit:cover;border-radius:8px">` : '<div style="width:100%;height:130px;background:var(--gray-lt);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:2rem">🏨</div>'}
      </div>
      <div>
        <h3 style="font-family:\'Playfair Display\',serif;margin-bottom:0.2rem">${r.name}${seasonal}</h3>
        <p style="font-size:0.88rem;color:var(--gray-dk);margin-bottom:0.6rem">${r.description || ''}</p>
        <div style="display:flex;gap:1rem;font-size:0.82rem;color:var(--gray-mid);margin-bottom:0.6rem">
          <span>👥 Up to ${r.max_capacity} guests</span>
          <span>🌙 ${r.nights} night${r.nights > 1 ? 's' : ''}</span>
          ${avgRating ? '<span>' + avgRating + '</span>' : ''}
        </div>
        ${amenities.length ? '<div style="display:flex;flex-wrap:wrap;gap:0.3rem">' + amenities.slice(0, 6).map(a => `<span class="badge badge-confirmed" style="font-size:0.75rem">${a}</span>`).join('') + (amenities.length > 6 ? '<span style="font-size:0.78rem;color:var(--gray-mid)">+' + (amenities.length - 6) + ' more</span>' : '') + '</div>' : ''}
      </div>
      <div style="text-align:right;min-width:160px">
        <div style="margin-bottom:0.3rem">${origPrice}<strong style="font-size:1.4rem;color:var(--gold)">৳${parseFloat(r.effective_price).toFixed(2)}</strong></div>
        <div style="font-size:0.82rem;color:var(--gray-mid);margin-bottom:0.8rem">per night</div>
        <div style="font-size:1rem;font-weight:600;margin-bottom:1rem">Total: ৳${parseFloat(r.total_price).toFixed(2)}</div>
        <a href="${BASE_URL}/guest/views/room_detail.php?id=${r.id}&checkin=${checkin}&checkout=${checkout}&guests=${guests}" class="btn btn-outline btn-sm" style="display:block;margin-bottom:0.4rem">View Details</a>
        <a href="${BASE_URL}/guest/views/booking_form.php?room_type_id=${r.id}&checkin=${checkin}&checkout=${checkout}&guests=${guests}" class="btn btn-primary btn-sm" style="display:block">Book Now</a>
      </div>
    </div>`;
  });

  container.innerHTML = html;
}
</script>
<?php include BASE_PATH . '/includes/footer.php'; ?>
