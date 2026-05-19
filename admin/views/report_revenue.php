<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('admin');
require_once __DIR__ . '/../models/AdminModel.php';
$model   = new AdminModel();
$period  = $_GET['period'] ?? 'month';
$revenue = $model->getRevenueReport($period);
$byType  = $model->getRevenueByRoomType();
$pageTitle='Revenue Report'; $activeRole='admin'; $activePage='rev';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Revenue Report</h1></div>
    <div style="display:flex;gap:0.5rem">
      <?php foreach (['day'=>'Daily','week'=>'Weekly','month'=>'Monthly'] as $v=>$l): ?>
      <a href="?period=<?= $v ?>" class="btn btn-sm <?= $period===$v?'btn-primary':'btn-outline' ?>"><?= $l ?></a>
      <?php endforeach; ?>
      <button onclick="window.print()" class="btn btn-secondary btn-sm">🖨 Export</button>
    </div>
  </div>

  <div class="card">
    <div class="card-title">Revenue by Period</div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Period</th><th>Room Revenue</th><th>Extras</th><th>Discounts</th><th>Total</th><th>Transactions</th></tr></thead>
        <tbody>
        <?php if (!$revenue): ?>
        <tr><td colspan="6" class="text-center text-muted" style="padding:2rem">No revenue data</td></tr>
        <?php else: ?>
        <?php $grandTotal = 0; ?>
        <?php foreach ($revenue as $r): ?>
        <?php $grandTotal += $r['total']; ?>
        <tr>
          <td><strong><?= sanitize($r['period']) ?></strong></td>
          <td>৳<?= number_format($r['base'],2) ?></td>
          <td>৳<?= number_format($r['extras'],2) ?></td>
          <td style="color:var(--success)">-৳<?= number_format($r['discounts'],2) ?></td>
          <td><strong style="color:var(--gold)">৳<?= number_format($r['total'],2) ?></strong></td>
          <td><?= $r['txn_count'] ?></td>
        </tr>
        <?php endforeach; ?>
        <tr style="background:var(--navy);color:var(--white)">
          <td colspan="4"><strong>Grand Total</strong></td>
          <td colspan="2"><strong style="color:var(--gold)">৳<?= number_format($grandTotal,2) ?></strong></td>
        </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-title">Revenue by Room Type</div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Room Type</th><th>Bookings</th><th>Revenue</th></tr></thead>
        <tbody>
        <?php foreach ($byType as $t): ?>
        <tr>
          <td><strong><?= sanitize($t['name']) ?></strong></td>
          <td><?= $t['bookings'] ?></td>
          <td><strong style="color:var(--gold)">৳<?= number_format($t['revenue'],2) ?></strong></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
</div>
<?php include BASE_PATH.'/includes/footer.php'; ?>
