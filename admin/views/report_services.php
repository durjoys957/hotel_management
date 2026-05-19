<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('admin');
require_once __DIR__ . '/../models/AdminModel.php';
$model   = new AdminModel();
$summary = $model->getServiceSummary();
$db      = getDB();
$totals  = $db->query("SELECT status, COUNT(*) cnt FROM service_requests GROUP BY status")->fetch_all(MYSQLI_ASSOC);
$pageTitle='Service Requests Report'; $activeRole='admin'; $activePage='svc';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Service Request Summary</h1></div>
    <button onclick="window.print()" class="btn btn-secondary">🖨 Export</button>
  </div>

  <div class="stats-grid">
    <?php foreach ($totals as $t): ?>
    <div class="stat-card">
      <div class="stat-label"><?= ucfirst($t['status']) ?></div>
      <div class="stat-value"><?= $t['cnt'] ?></div>
    </div>
    <?php endforeach; ?>
    <div class="stat-card">
      <div class="stat-label">Total Requests</div>
      <div class="stat-value text-gold"><?= array_sum(array_column($totals,'cnt')) ?></div>
    </div>
  </div>

  <div class="card">
    <div class="card-title">Breakdown by Type & Status</div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Service Type</th><th>Status</th><th>Count</th></tr></thead>
        <tbody>
        <?php if (!$summary): ?>
        <tr><td colspan="3" class="text-center text-muted" style="padding:2rem">No service requests yet</td></tr>
        <?php else: ?>
        <?php foreach ($summary as $s): ?>
        <tr>
          <td><?= ucfirst(str_replace('_',' ',$s['service_type'])) ?></td>
          <td><span class="badge badge-<?= $s['status'] ?>"><?= $s['status'] ?></span></td>
          <td><strong><?= $s['cnt'] ?></strong></td>
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
