<nav class="sidebar">
  <div class="sidebar-section">
    <div class="sidebar-label">Overview</div>
    <a href="<?= BASE_URL ?>/admin/views/dashboard.php"  class="<?= ($activePage??'')==='dashboard'?'active':'' ?>">🏠 Dashboard</a>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-label">Inventory</div>
    <a href="<?= BASE_URL ?>/admin/views/room_types.php" class="<?= ($activePage??'')==='room_types'?'active':'' ?>">🛏 Room Types</a>
    <a href="<?= BASE_URL ?>/admin/views/rooms.php"      class="<?= ($activePage??'')==='rooms'?'active':'' ?>">🏨 Rooms</a>
    <a href="<?= BASE_URL ?>/admin/views/seasonal.php"   class="<?= ($activePage??'')==='seasonal'?'active':'' ?>">🎉 Seasonal Pricing</a>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-label">People</div>
    <a href="<?= BASE_URL ?>/admin/views/staff.php"      class="<?= ($activePage??'')==='staff'?'active':'' ?>">👩‍💼 Staff</a>
    <a href="<?= BASE_URL ?>/admin/views/guests.php"     class="<?= ($activePage??'')==='guests'?'active':'' ?>">👥 Guests</a>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-label">Operations</div>
    <a href="<?= BASE_URL ?>/admin/views/bookings.php"   class="<?= ($activePage??'')==='bookings'?'active':'' ?>">📋 Bookings</a>
    <a href="<?= BASE_URL ?>/admin/views/reviews.php"    class="<?= ($activePage??'')==='reviews'?'active':'' ?>">⭐ Reviews</a>
    <a href="<?= BASE_URL ?>/admin/views/announcements.php" class="<?= ($activePage??'')==='announcements'?'active':'' ?>">📢 Announcements</a>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-label">Reports</div>
    <a href="<?= BASE_URL ?>/admin/views/report_revenue.php"   class="<?= ($activePage??'')==='rev'?'active':'' ?>">💰 Revenue</a>
    <a href="<?= BASE_URL ?>/admin/views/report_occupancy.php" class="<?= ($activePage??'')==='occ'?'active':'' ?>">📊 Occupancy</a>
    <a href="<?= BASE_URL ?>/admin/views/report_loyalty.php"   class="<?= ($activePage??'')==='loyalty'?'active':'' ?>">🏅 Loyalty</a>
    <a href="<?= BASE_URL ?>/admin/views/report_services.php"  class="<?= ($activePage??'')==='svc'?'active':'' ?>">🛎 Services</a>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-label">Account</div>
    <a href="<?= BASE_URL ?>/admin/views/profile.php" class="<?= ($activePage??'')==='profile'?'active':'' ?>">👤 My Profile</a>
  </div>
</nav>
