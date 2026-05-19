<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('admin');
require_once __DIR__ . '/../models/AdminModel.php';
$model  = new AdminModel();
$report = $model->getOccupancyReport();
$pageTitle='Occupancy Report'; $activeRole='admin'; $activePage='occ';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Occupancy Report</h1></div>
    <button onclick="window.print()" class="btn btn-secondary">🖨 Export</button>
  </div>

  <div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Total Rooms</div><div class="stat-value"><?= $report['total'] ?></div></div>
    <div class="stat-card"><div class="stat-label">Currently Occupied</div><div class="stat-value"><?= $report['occ'] ?></div></div>
    <div class="stat-card"><div class="stat-label">Occupancy Rate</div><div class="stat-value text-gold"><?= $report['total']>0?round($report['occ']/$report['total']*100):0 ?>%</div></div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">
    <div class="card">
      <div class="card-title">Most Popular Room Types</div>
      <?php if (!$report['popular']): ?>
      <div class="empty-state"><p>No booking data yet.</p></div>
      <?php else: ?>
      <?php $max = max(array_column($report['popular'],'cnt')) ?: 1; ?>
      <?php foreach ($report['popular'] as $p): ?>
      <div style="margin-bottom:1rem">
        <div style="display:flex;justify-content:space-between;font-size:0.9rem;margin-bottom:0.3rem">
          <strong><?= sanitize($p['name']) ?></strong><span><?= $p['cnt'] ?> bookings</span>
        </div>
        <div style="background:var(--gray-lt);border-radius:4px;height:10px">
          <div style="background:var(--gold);height:10px;border-radius:4px;width:<?= round($p['cnt']/$max*100) ?>%"></div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="card">
      <div class="card-title">Peak Booking Months</div>
      <?php if (!$report['peak']): ?>
      <div class="empty-state"><p>No booking data yet.</p></div>
      <?php else: ?>
      <?php $maxP = max(array_column($report['peak'],'cnt')) ?: 1; ?>
      <?php foreach ($report['peak'] as $p): ?>
      <div style="margin-bottom:1rem">
        <div style="display:flex;justify-content:space-between;font-size:0.9rem;margin-bottom:0.3rem">
          <strong><?= sanitize($p['mon']) ?></strong><span><?= $p['cnt'] ?> bookings</span>
        </div>
        <div style="background:var(--gray-lt);border-radius:4px;height:10px">
          <div style="background:var(--navy);height:10px;border-radius:4px;width:<?= round($p['cnt']/$maxP*100) ?>%"></div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</main>
</div>
<?php include BASE_PATH.'/includes/footer.php'; ?>
