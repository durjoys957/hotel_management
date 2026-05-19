<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('admin');
require_once __DIR__ . '/../models/AdminModel.php';
$model  = new AdminModel();
$stats  = $model->getDashboardStats();
$ratings= $model->getAvgRatings();
$pageTitle='Admin Dashboard'; $activeRole='admin'; $activePage='dashboard';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Admin Dashboard</h1><p class="page-subtitle"><?= date('l, F j, Y') ?></p></div>
  </div>

  <div class="stats-grid">
    <div class="stat-card" style="border-top-color:var(--gold)">
      <div class="stat-label">Occupancy Rate</div>
      <div class="stat-value text-gold"><?= $stats['occupancy_rate'] ?>%</div>
      <div class="stat-sub"><?= $stats['occupied'] ?> occupied / <?= $stats['available'] ?> available</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Today's Revenue</div>
      <div class="stat-value">৳<?= number_format($stats['today_revenue']) ?></div>
      <div class="stat-sub">Collected payments</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Maintenance Issues</div>
      <div class="stat-value"><?= $stats['maintenance_issues'] ?></div>
      <div class="stat-sub">Open / In Progress</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Pending Reviews</div>
      <div class="stat-value"><?= $stats['pending_reviews'] ?></div>
      <div class="stat-sub">Awaiting reply</div>
    </div>
  </div>

  <?php if ($ratings['cnt'] > 0): ?>
  <div class="card">
    <div class="card-title">Average Guest Ratings (<?= $ratings['cnt'] ?> total reviews)</div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem;text-align:center">
      <?php foreach (['ov'=>'Overall','cl'=>'Cleanliness','sv'=>'Service'] as $k=>$label): ?>
      <div>
        <div style="font-size:2.5rem;font-weight:700;color:var(--gold)"><?= number_format($ratings[$k],1) ?></div>
        <div style="color:var(--gray-mid)"><?= $label ?></div>
        <div class="stars" style="font-size:1.3rem"><?= str_repeat('★',round($ratings[$k])).str_repeat('☆',5-round($ratings[$k])) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">
    <div class="card">
      <div class="card-title">Quick Links</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.8rem">
        <a href="<?= BASE_URL ?>/admin/views/room_types.php" class="btn btn-outline">🛏 Room Types</a>
        <a href="<?= BASE_URL ?>/admin/views/rooms.php" class="btn btn-outline">🏨 Rooms</a>
        <a href="<?= BASE_URL ?>/admin/views/staff.php" class="btn btn-outline">👩‍💼 Staff</a>
        <a href="<?= BASE_URL ?>/admin/views/seasonal.php" class="btn btn-outline">🎉 Pricing</a>
        <a href="<?= BASE_URL ?>/admin/views/reviews.php" class="btn btn-outline">⭐ Reviews</a>
        <a href="<?= BASE_URL ?>/admin/views/report_revenue.php" class="btn btn-outline">💰 Reports</a>
      </div>
    </div>
    <div class="card">
      <div class="card-title">System Summary</div>
      <?php
      $db = getDB();
      $rows = [
        'Total Rooms'    => $db->query("SELECT COUNT(*) c FROM rooms")->fetch_assoc()['c'],
        'Total Bookings' => $db->query("SELECT COUNT(*) c FROM bookings")->fetch_assoc()['c'],
        'Active Guests'  => $db->query("SELECT COUNT(*) c FROM bookings WHERE status='checked_in'")->fetch_assoc()['c'],
        'Total Staff'    => $db->query("SELECT COUNT(*) c FROM users WHERE role!='guest'")->fetch_assoc()['c'],
        'Total Guests'   => $db->query("SELECT COUNT(*) c FROM users WHERE role='guest'")->fetch_assoc()['c'],
        'Total Revenue'  => '৳'.number_format($db->query("SELECT COALESCE(SUM(total_amount),0) v FROM billing WHERE payment_status='paid'")->fetch_assoc()['v']),
      ];
      ?>
      <table style="font-size:0.9rem;width:100%">
        <?php foreach ($rows as $k=>$v): ?>
        <tr><td style="color:var(--gray-mid);padding:0.3rem 0"><?= $k ?></td><td style="font-weight:600;text-align:right"><?= $v ?></td></tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>
</main>
</div>
<?php include BASE_PATH.'/includes/footer.php'; ?>
