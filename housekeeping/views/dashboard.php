<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('housekeeping');
require_once __DIR__ . '/../models/HousekeepingModel.php';
$model    = new HousekeepingModel();
$userId   = $_SESSION['user_id'];
$stats    = $model->getDashboardStats($userId);
$todayTasks = $model->getTodayTasks();
$openMaint  = $model->getMaintenanceReports('open');
$checkouts  = $model->getUpcomingCheckouts(1);
$checkins   = $model->getUpcomingCheckins(1);
$pageTitle='Housekeeping Dashboard'; $activeRole='housekeeping'; $activePage='dashboard';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Housekeeping</h1><p class="page-subtitle"><?= date('l, F j, Y') ?></p></div>
    <a href="<?= BASE_URL ?>/housekeeping/views/tasks.php" class="btn btn-primary">+ New Task</a>
  </div>

  <div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Dirty Rooms</div><div class="stat-value"><?= $stats['dirty'] ?></div><div class="stat-sub">Need cleaning</div></div>
    <div class="stat-card"><div class="stat-label">Maintenance</div><div class="stat-value"><?= $stats['maintenance'] ?></div><div class="stat-sub">Out of service</div></div>
    <div class="stat-card"><div class="stat-label">Open Issues</div><div class="stat-value"><?= $stats['open_issues'] ?></div><div class="stat-sub">Unresolved</div></div>
    <div class="stat-card"><div class="stat-label">Done Today</div><div class="stat-value text-gold"><?= $stats['done_today'] ?></div><div class="stat-sub">Tasks completed</div></div>
    <div class="stat-card"><div class="stat-label">Pending Tasks</div><div class="stat-value"><?= $stats['pending_tasks'] ?></div><div class="stat-sub">Awaiting</div></div>
  </div>

  <!-- AJAX Room Status Board -->
  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
      <span class="card-title" style="margin-bottom:0;padding-bottom:0;border-bottom:none">Live Room Status</span>
      <button onclick="refreshRoomBoard()" class="btn btn-sm btn-outline">🔄 Refresh</button>
    </div>
    <div id="roomBoard">Loading...</div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">
    <div class="card">
      <div class="card-title">Today's Tasks</div>
      <?php if (!$todayTasks): ?>
      <div class="empty-state"><div class="empty-icon">📋</div><p>No tasks today</p></div>
      <?php else: foreach ($todayTasks as $t): ?>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:0.6rem 0;border-bottom:1px solid var(--gray-lt)">
        <div>
          <span class="badge badge-<?= $t['priority'] ?>"><?= $t['priority'] ?></span>
          <strong style="margin-left:0.4rem">Room <?= sanitize($t['room_number']) ?></strong>
          <div style="font-size:0.82rem;color:var(--gray-mid)"><?= ucfirst($t['task_type']) ?></div>
        </div>
        <span class="badge badge-<?= $t['status'] ?>"><?= $t['status'] ?></span>
      </div>
      <?php endforeach; endif; ?>
    </div>

    <div class="card">
      <div class="card-title">Upcoming Check-outs (Today)</div>
      <?php if (!$checkouts): ?>
      <div class="empty-state"><div class="empty-icon">🚪</div><p>No check-outs today</p></div>
      <?php else: foreach ($checkouts as $c): ?>
      <div style="padding:0.6rem 0;border-bottom:1px solid var(--gray-lt)">
        <strong>Room <?= sanitize($c['room_number']) ?></strong> — <?= sanitize($c['guest_name']) ?>
        <div style="font-size:0.82rem;color:var(--warning)">Checkout: <?= sanitize($c['checkout_date']) ?></div>
      </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <?php if ($openMaint): ?>
  <div class="card" style="border-left:4px solid var(--danger)">
    <div class="card-title">🔴 Open Maintenance Issues</div>
    <?php foreach ($openMaint as $m): ?>
    <div style="display:flex;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid var(--gray-lt)">
      <div>
        <strong>Room <?= sanitize($m['room_number']) ?></strong> — <?= sanitize($m['description']) ?>
        <span class="badge badge-<?= $m['severity'] ?>" style="margin-left:0.5rem"><?= $m['severity'] ?></span>
      </div>
      <a href="<?= BASE_URL ?>/housekeeping/views/maintenance.php" class="btn btn-sm btn-outline">View</a>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</main>
</div>
<script>
const BASE_URL = '<?= BASE_URL ?>';

function refreshRoomBoard() {
  ajaxGet(BASE_URL + '/api/room_status.php', function(err, data) {
    if (err || !data.rooms) { document.getElementById('roomBoard').innerHTML = '<p class="text-danger">Error loading.</p>'; return; }
    let html = '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:0.7rem">';
    const icons = {available:'✅',occupied:'🔵',dirty:'🟡',maintenance:'🔴',blocked:'⛔'};
    data.rooms.forEach(r => {
      html += `<div style="text-align:center;padding:0.6rem;background:var(--gray-lt);border-radius:8px">
        <div style="font-size:1.3rem">${icons[r.status]||'⬜'}</div>
        <div style="font-weight:700;font-size:0.88rem">R${r.room_number}</div>
        <span class="badge badge-${r.status}" style="font-size:0.7rem">${r.status}</span>
      </div>`;
    });
    html += '</div>';
    document.getElementById('roomBoard').innerHTML = html;
  });
}
refreshRoomBoard();
setInterval(refreshRoomBoard, 20000);
</script>
<?php include BASE_PATH.'/includes/footer.php'; ?>
