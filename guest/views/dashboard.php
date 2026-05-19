<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('guest');
require_once __DIR__ . '/../models/GuestModel.php';

$model    = new GuestModel();
$userId   = $_SESSION['user_id'];
$bookings = $model->getGuestBookings($userId);
$balance  = $model->getLoyaltyBalance($userId);
$anns     = $model->getAnnouncements(3);

$upcoming  = array_filter($bookings, fn($b) => in_array($b['status'], ['pending','confirmed']));
$active    = array_filter($bookings, fn($b) => $b['status'] === 'checked_in');
$completed = array_filter($bookings, fn($b) => $b['status'] === 'checked_out');

$pageTitle  = 'My Dashboard';
$activeRole = 'guest';
$activePage = 'dashboard';
include BASE_PATH . '/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__ . '/sidebar_guest.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div>
      <h1 class="page-title">Welcome back, <?= sanitize($_SESSION['name'] ?? 'Guest') ?>!</h1>
      <p class="page-subtitle"><?= date('l, F j, Y') ?></p>
    </div>
    <a href="<?= BASE_URL ?>/guest/views/search.php" class="btn btn-primary">+ Book a Room</a>
  </div>

  <div class="stats-grid">
    <div class="stat-card" style="border-top-color:var(--gold)">
      <div class="stat-label">Upcoming Bookings</div>
      <div class="stat-value text-gold"><?= count($upcoming) ?></div>
      <div class="stat-sub">Pending / Confirmed</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Currently Checked In</div>
      <div class="stat-value"><?= count($active) ?></div>
      <div class="stat-sub">Active stays</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Completed Stays</div>
      <div class="stat-value"><?= count($completed) ?></div>
      <div class="stat-sub">Past bookings</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Loyalty Points</div>
      <div class="stat-value text-gold"><?= number_format($balance) ?></div>
      <div class="stat-sub">≈ ৳<?= number_format($balance * POINTS_VALUE, 2) ?> value</div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">
    <div class="card">
      <div class="card-title">Recent Bookings</div>
      <?php if (!$bookings): ?>
      <div class="empty-state"><div class="empty-icon">📋</div><p>No bookings yet. <a href="<?= BASE_URL ?>/guest/views/search.php">Book your first room!</a></p></div>
      <?php else: ?>
      <table style="font-size:0.88rem;width:100%">
        <thead><tr><th>ID</th><th>Room Type</th><th>Check-in</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach (array_slice($bookings, 0, 5) as $b): ?>
        <tr>
          <td><a href="<?= BASE_URL ?>/guest/views/booking_detail.php?id=<?= $b['id'] ?>">#<?= $b['id'] ?></a></td>
          <td><?= sanitize($b['type_name']) ?></td>
          <td><?= sanitize($b['checkin_date']) ?></td>
          <td><span class="badge badge-<?= $b['status'] ?>"><?= $b['status'] ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <a href="<?= BASE_URL ?>/guest/views/bookings.php" class="btn btn-sm btn-outline" style="margin-top:1rem">View All</a>
      <?php endif; ?>
    </div>

    <div class="card">
      <div class="card-title">📢 Announcements</div>
      <?php if (!$anns): ?>
      <div class="empty-state"><p>No announcements.</p></div>
      <?php else: ?>
      <?php foreach ($anns as $a): ?>
      <div style="border-left:3px solid var(--gold);padding:0.6rem 0.8rem;margin-bottom:0.8rem;background:var(--gray-lt);border-radius:0 6px 6px 0">
        <strong style="font-size:0.9rem"><?= sanitize($a['title']) ?></strong>
        <p style="margin:0.2rem 0 0;font-size:0.82rem;color:var(--gray-dk)"><?= sanitize($a['message']) ?></p>
        <small class="text-muted"><?= date('M j', strtotime($a['created_at'])) ?></small>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <?php if (count($active) > 0): ?>
  <div class="card" style="border-top:3px solid var(--success)">
    <div class="card-title">🏨 You are currently checked in</div>
    <?php foreach ($active as $b): ?>
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem">
      <div>
        <strong><?= sanitize($b['type_name']) ?></strong>
        <?php if ($b['room_number']): ?>
        <span class="badge badge-confirmed" style="margin-left:0.5rem">Room <?= sanitize($b['room_number']) ?></span>
        <?php endif; ?>
        <span style="color:var(--gray-mid);font-size:0.85rem;margin-left:0.5rem">Check-out: <?= sanitize($b['checkout_date']) ?></span>
      </div>
      <a href="<?= BASE_URL ?>/guest/views/services.php?booking_id=<?= $b['id'] ?>" class="btn btn-sm btn-primary">Request Service</a>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</main>
</div>
<?php include BASE_PATH . '/includes/footer.php'; ?>
