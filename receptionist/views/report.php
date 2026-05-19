<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('receptionist');
require_once __DIR__ . '/../models/ReceptionistModel.php';
$model  = new ReceptionistModel();
$date   = $_GET['date'] ?? date('Y-m-d');
$report = $model->getDailyReport($date);
$pageTitle='Daily Report'; $activeRole='receptionist'; $activePage='report';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Daily Operations Report</h1></div>
    <div style="display:flex;gap:0.8rem;align-items:center">
      <form method="GET" style="display:flex;gap:0.5rem;align-items:center">
        <input type="date" name="date" class="form-control" value="<?= sanitize($date) ?>">
        <button type="submit" class="btn btn-primary">View</button>
      </form>
      <button onclick="window.print()" class="btn btn-secondary">🖨 Print</button>
    </div>
  </div>
  <div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Arrivals</div><div class="stat-value"><?= $report['arrivals'] ?></div><div class="stat-sub">Check-ins</div></div>
    <div class="stat-card"><div class="stat-label">Departures</div><div class="stat-value"><?= $report['departures'] ?></div><div class="stat-sub">Check-outs</div></div>
    <div class="stat-card"><div class="stat-label">Walk-ins</div><div class="stat-value"><?= $report['walkins'] ?></div><div class="stat-sub">Direct arrivals</div></div>
    <div class="stat-card"><div class="stat-label">Revenue</div><div class="stat-value">৳<?= number_format($report['revenue']) ?></div><div class="stat-sub">Collected today</div></div>
    <div class="stat-card"><div class="stat-label">Occupied</div><div class="stat-value"><?= $report['occupied'] ?></div><div class="stat-sub">Rooms in use</div></div>
    <div class="stat-card"><div class="stat-label">Available</div><div class="stat-value text-gold"><?= $report['available'] ?></div><div class="stat-sub">Rooms ready</div></div>
  </div>
  <div class="card">
    <div class="card-title">Report for <?= date('F j, Y', strtotime($date)) ?></div>
    <p style="color:var(--gray-dk)">Occupancy Rate: <strong style="color:var(--gold)"><?= ($report['occupied']+$report['available']) > 0 ? round($report['occupied']/($report['occupied']+$report['available'])*100) : 0 ?>%</strong></p>
  </div>
</main>
</div>
<?php include BASE_PATH.'/includes/footer.php'; ?>
