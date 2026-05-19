<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('receptionist');
require_once __DIR__ . '/../models/ReceptionistModel.php';
$model     = new ReceptionistModel();
$checkouts = $model->getTodayCheckouts();
$pageTitle='Today\'s Check-outs'; $activeRole='receptionist'; $activePage='checkouts';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Today's Check-outs</h1><p class="page-subtitle"><?= date('F j, Y') ?></p></div>
  </div>
  <div class="card">
    <?php if (!$checkouts): ?>
    <div class="empty-state"><div class="empty-icon">🚪</div><p>No check-outs scheduled for today.</p></div>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Booking</th><th>Guest</th><th>Room</th><th>Type</th><th>Total</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($checkouts as $c): ?>
        <tr>
          <td>#<?= $c['id'] ?></td>
          <td><strong><?= sanitize($c['guest_name']) ?></strong></td>
          <td>Room <?= sanitize($c['room_number']) ?></td>
          <td><?= sanitize($c['type_name']) ?></td>
          <td>৳<?= number_format($c['total_price'],2) ?></td>
          <td><a href="<?= BASE_URL ?>/receptionist/views/booking_detail.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-secondary">Process →</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</main>
</div>
<?php include BASE_PATH.'/includes/footer.php'; ?>
