<?php
// Receptionist sidebar — include $activePage before including this
?>
<nav class="sidebar">
  <div class="sidebar-section">
    <div class="sidebar-label">Front Desk</div>
    <a href="<?= BASE_URL ?>/receptionist/views/dashboard.php" class="<?= ($activePage??'')==='dashboard'?'active':'' ?>">🏠 Dashboard</a>
    <a href="<?= BASE_URL ?>/receptionist/views/checkins.php" class="<?= ($activePage??'')==='checkins'?'active':'' ?>">✅ Check-ins</a>
    <a href="<?= BASE_URL ?>/receptionist/views/checkouts.php" class="<?= ($activePage??'')==='checkouts'?'active':'' ?>">🚪 Check-outs</a>
    <a href="<?= BASE_URL ?>/receptionist/views/walkin.php" class="<?= ($activePage??'')==='walkin'?'active':'' ?>">🚶 Walk-in</a>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-label">Bookings</div>
    <a href="<?= BASE_URL ?>/receptionist/views/bookings.php" class="<?= ($activePage??'')==='bookings'?'active':'' ?>">📋 All Bookings</a>
    <a href="<?= BASE_URL ?>/receptionist/views/room_status.php" class="<?= ($activePage??'')==='rooms'?'active':'' ?>">🏨 Room Status</a>
    <a href="<?= BASE_URL ?>/receptionist/views/services.php" class="<?= ($activePage??'')==='services'?'active':'' ?>">🛎 Service Requests</a>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-label">Reports</div>
    <a href="<?= BASE_URL ?>/receptionist/views/report.php" class="<?= ($activePage??'')==='report'?'active':'' ?>">📊 Daily Report</a>
    <a href="<?= BASE_URL ?>/receptionist/views/profile.php" class="<?= ($activePage??'')==='profile'?'active':'' ?>">👤 Profile</a>
  </div>
</nav>
