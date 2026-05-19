<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('receptionist');
require_once __DIR__ . '/../models/ReceptionistModel.php';
$model    = new ReceptionistModel();
$checkins = $model->getTodayCheckins();
$pageTitle='Today\'s Check-ins'; $activeRole='receptionist'; $activePage='checkins';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Today's Check-ins</h1><p class="page-subtitle"><?= date('F j, Y') ?></p></div>
  </div>
  <div class="card">
    <?php if (!$checkins): ?>
    <div class="empty-state"><div class="empty-icon">✅</div><p>No check-ins scheduled for today.</p></div>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Booking</th><th>Guest</th><th>Phone</th><th>Room Type</th><th>Guests</th><th>Total</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($checkins as $c): ?>
        <tr>
          <td>#<?= $c['id'] ?></td>
          <td><strong><?= sanitize($c['guest_name']) ?></strong><br><small class="text-muted"><?= sanitize($c['email']) ?></small></td>
          <td><?= sanitize($c['phone'] ?? '—') ?></td>
          <td><?= sanitize($c['type_name']) ?></td>
          <td><?= $c['num_guests'] ?></td>
          <td>৳<?= number_format($c['total_price'],2) ?></td>
          <td><a href="<?= BASE_URL ?>/receptionist/views/booking_detail.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-primary">Check In →</a></td>
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
