<?php ?>
<nav class="sidebar">
  <div class="sidebar-section">
    <div class="sidebar-label">Housekeeping</div>
    <a href="<?= BASE_URL ?>/housekeeping/views/dashboard.php" class="<?= ($activePage??'')==='dashboard'?'active':'' ?>">🏠 Dashboard</a>
    <a href="<?= BASE_URL ?>/housekeeping/views/rooms.php" class="<?= ($activePage??'')==='rooms'?'active':'' ?>">🏨 Room Status</a>
    <a href="<?= BASE_URL ?>/housekeeping/views/tasks.php" class="<?= ($activePage??'')==='tasks'?'active':'' ?>">📋 Tasks</a>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-label">Maintenance</div>
    <a href="<?= BASE_URL ?>/housekeeping/views/maintenance.php" class="<?= ($activePage??'')==='maintenance'?'active':'' ?>">🔧 Maintenance</a>
    <a href="<?= BASE_URL ?>/housekeeping/views/schedule.php" class="<?= ($activePage??'')==='schedule'?'active':'' ?>">📅 Schedule</a>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-label">Reports</div>
    <a href="<?= BASE_URL ?>/housekeeping/views/report.php" class="<?= ($activePage??'')==='report'?'active':'' ?>">📊 Daily Report</a>
    <a href="<?= BASE_URL ?>/housekeeping/views/profile.php" class="<?= ($activePage??'')==='profile'?'active':'' ?>">👤 Profile</a>
  </div>
</nav>
