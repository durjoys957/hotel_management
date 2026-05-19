<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('receptionist');
require_once __DIR__ . '/../models/ReceptionistModel.php';
$model    = new ReceptionistModel();
$stats    = $model->getDashboardStats();
$checkins = $model->getTodayCheckins();
$checkouts= $model->getTodayCheckouts();
$pageTitle='Front Desk Dashboard'; $activeRole='receptionist'; $activePage='dashboard';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Front Desk</h1><p class="page-subtitle"><?= date('l, F j, Y') ?></p></div>
    <a href="<?= BASE_URL ?>/receptionist/views/walkin.php" class="btn btn-primary">+ Walk-in Check-in</a>
  </div>

  <div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Expected Check-ins</div><div class="stat-value"><?= $stats['expected_checkins'] ?></div><div class="stat-sub">Today</div></div>
    <div class="stat-card"><div class="stat-label">Expected Check-outs</div><div class="stat-value"><?= $stats['expected_checkouts'] ?></div><div class="stat-sub">Today</div></div>
    <div class="stat-card"><div class="stat-label">Current Guests</div><div class="stat-value"><?= $stats['current_guests'] ?></div><div class="stat-sub">Checked in</div></div>
    <div class="stat-card"><div class="stat-label">Available Rooms</div><div class="stat-value text-gold"><?= $stats['available_rooms'] ?></div><div class="stat-sub">Ready</div></div>
    <div class="stat-card"><div class="stat-label">Today's Revenue</div><div class="stat-value">৳<?= number_format($stats['today_revenue']) ?></div><div class="stat-sub">Collected</div></div>
    <div class="stat-card"><div class="stat-label">Pending Services</div><div class="stat-value"><?= $stats['pending_services'] ?></div><div class="stat-sub">Awaiting</div></div>
  </div>

  <!-- Search -->
  <div class="card">
    <div class="card-title">Quick Booking Search</div>
    <div style="display:flex;gap:1rem;align-items:center">
      <input type="text" id="searchInput" class="form-control" placeholder="Search by booking ID, guest name, or phone..." style="flex:1">
      <button onclick="doSearch()" class="btn btn-primary">Search</button>
    </div>
    <div id="searchResults" style="margin-top:1rem"></div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">
    <div class="card">
      <div class="card-title">Today's Check-ins (<?= count($checkins) ?>)</div>
      <?php if (!$checkins): ?>
      <div class="empty-state"><div class="empty-icon">✅</div><p>No check-ins today</p></div>
      <?php else: ?>
      <?php foreach ($checkins as $c): ?>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:0.6rem 0;border-bottom:1px solid var(--gray-lt)">
        <div>
          <strong><?= sanitize($c['guest_name']) ?></strong>
          <div style="font-size:0.82rem;color:var(--gray-mid)"><?= sanitize($c['type_name']) ?> | #<?= $c['id'] ?></div>
        </div>
        <a href="<?= BASE_URL ?>/receptionist/views/booking_detail.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-primary">Check In</a>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="card">
      <div class="card-title">Today's Check-outs (<?= count($checkouts) ?>)</div>
      <?php if (!$checkouts): ?>
      <div class="empty-state"><div class="empty-icon">🚪</div><p>No check-outs today</p></div>
      <?php else: ?>
      <?php foreach ($checkouts as $c): ?>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:0.6rem 0;border-bottom:1px solid var(--gray-lt)">
        <div>
          <strong><?= sanitize($c['guest_name']) ?></strong>
          <div style="font-size:0.82rem;color:var(--gray-mid)">Room <?= sanitize($c['room_number']) ?> | #<?= $c['id'] ?></div>
        </div>
        <a href="<?= BASE_URL ?>/receptionist/views/booking_detail.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-secondary">Check Out</a>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</main>
</div>
<script>
const BASE_URL = '<?= BASE_URL ?>';
function doSearch() {
  const q = document.getElementById('searchInput').value.trim();
  if (!q) return;
  document.getElementById('searchResults').innerHTML = '<p class="loading">Searching...</p>';
  ajaxGet(BASE_URL + '/api/booking_search.php?q=' + encodeURIComponent(q), function(err, data) {
    if (err || !data.bookings) { document.getElementById('searchResults').innerHTML = '<p class="text-danger">Error</p>'; return; }
    if (!data.bookings.length) { document.getElementById('searchResults').innerHTML = '<p class="text-muted">No results found.</p>'; return; }
    let html = '<div class="table-wrap"><table><thead><tr><th>ID</th><th>Guest</th><th>Type</th><th>Check-in</th><th>Status</th><th></th></tr></thead><tbody>';
    data.bookings.forEach(b => {
      html += `<tr><td>#${b.id}</td><td>${b.guest_name}</td><td>${b.type_name}</td><td>${b.checkin_date}</td><td><span class="badge badge-${b.status}">${b.status}</span></td><td><a href="${BASE_URL}/receptionist/views/booking_detail.php?id=${b.id}" class="btn btn-sm btn-outline">View</a></td></tr>`;
    });
    html += '</tbody></table></div>';
    document.getElementById('searchResults').innerHTML = html;
  });
}
document.getElementById('searchInput').addEventListener('keyup', e => { if (e.key === 'Enter') doSearch(); });
</script>
<?php include BASE_PATH.'/includes/footer.php'; ?>
