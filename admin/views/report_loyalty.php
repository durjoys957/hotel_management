<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('admin');
require_once __DIR__ . '/../models/AdminModel.php';
$model  = new AdminModel();
$report = $model->getLoyaltyReport();
$db     = getDB();
$totals = $db->query("SELECT SUM(points_earned) te, SUM(points_used) tu FROM loyalty_points")->fetch_assoc();
$pageTitle='Loyalty Points Report'; $activeRole='admin'; $activePage='loyalty';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Loyalty Points Report</h1></div>
    <button onclick="window.print()" class="btn btn-secondary">🖨 Export</button>
  </div>

  <div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Total Points Issued</div><div class="stat-value text-gold"><?= number_format($totals['te']) ?></div></div>
    <div class="stat-card"><div class="stat-label">Total Points Redeemed</div><div class="stat-value"><?= number_format($totals['tu']) ?></div></div>
    <div class="stat-card"><div class="stat-label">Points Outstanding</div><div class="stat-value"><?= number_format($totals['te']-$totals['tu']) ?></div><div class="stat-sub">≈ ৳<?= number_format(($totals['te']-$totals['tu'])*POINTS_VALUE,2) ?> liability</div></div>
  </div>

  <div class="card">
    <div class="card-title">Monthly Breakdown</div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Month</th><th>Points Issued</th><th>Points Redeemed</th><th>Net</th></tr></thead>
        <tbody>
        <?php if (!$report): ?>
        <tr><td colspan="4" class="text-center text-muted" style="padding:2rem">No data</td></tr>
        <?php else: ?>
        <?php foreach ($report as $r): ?>
        <tr>
          <td><?= sanitize($r['mon']) ?></td>
          <td style="color:var(--success)">+<?= number_format($r['earned']) ?></td>
          <td style="color:var(--danger)">-<?= number_format($r['used']) ?></td>
          <td><strong><?= number_format($r['earned']-$r['used']) ?></strong></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
</div>
<?php include BASE_PATH.'/includes/footer.php'; ?>
