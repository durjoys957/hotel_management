<nav class="sidebar">
  <div class="sidebar-section">
    <div class="sidebar-label">Overview</div>
    <a href="<?= BASE_URL ?>/guest/views/dashboard.php" class="<?= ($activePage??'')==='dashboard'?'active':'' ?>">🏠 Dashboard</a>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-label">Rooms</div>
    <a href="<?= BASE_URL ?>/guest/views/search.php"  class="<?= ($activePage??'')==='search'?'active':'' ?>">🔍 Search Rooms</a>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-label">My Stays</div>
    <a href="<?= BASE_URL ?>/guest/views/bookings.php"  class="<?= ($activePage??'')==='bookings'?'active':'' ?>">📋 My Bookings</a>
    <a href="<?= BASE_URL ?>/guest/views/services.php"  class="<?= ($activePage??'')==='services'?'active':'' ?>">🛎 Service Requests</a>
    <a href="<?= BASE_URL ?>/guest/views/reviews.php"   class="<?= ($activePage??'')==='reviews'?'active':'' ?>">⭐ My Reviews</a>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-label">Account</div>
    <a href="<?= BASE_URL ?>/guest/views/billing.php"   class="<?= ($activePage??'')==='billing'?'active':'' ?>">💳 Billing History</a>
    <a href="<?= BASE_URL ?>/guest/views/loyalty.php"   class="<?= ($activePage??'')==='loyalty'?'active':'' ?>">🏅 Loyalty Points</a>
    <a href="<?= BASE_URL ?>/guest/views/profile.php"   class="<?= ($activePage??'')==='profile'?'active':'' ?>">👤 My Profile</a>
  </div>
</nav>
