<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('admin');
require_once __DIR__ . '/../models/AdminModel.php';
$model   = new AdminModel();
$types   = $model->getRoomTypes();
$filters = [
    'status'    => $_GET['status'] ?? '',
    'room_type' => $_GET['room_type'] ?? '',
    'source'    => $_GET['source'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to'   => $_GET['date_to'] ?? '',
];
$bookings = $model->getAllBookings(array_filter($filters));
$pageTitle='All Bookings'; $activeRole='admin'; $activePage='bookings';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">All Bookings</h1><p class="page-subtitle"><?= count($bookings) ?> results</p></div>
    <button onclick="window.print()" class="btn btn-secondary">🖨 Export</button>
  </div>
  <div class="card">
    <form method="GET" style="display:grid;grid-template-columns:repeat(5,1fr) auto;gap:0.7rem;align-items:end;margin-bottom:1rem">
      <div><label class="form-label">Status</label>
        <select name="status" class="form-control">
          <option value="">All</option>
          <?php foreach (['pending','confirmed','checked_in','checked_out','cancelled'] as $s): ?>
          <option value="<?= $s ?>" <?= $filters['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div><label class="form-label">Room Type</label>
        <select name="room_type" class="form-control">
          <option value="">All</option>
          <?php foreach ($types as $t): ?>
          <option value="<?= $t['id'] ?>" <?= $filters['room_type']==$t['id']?'selected':'' ?>><?= sanitize($t['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div><label class="form-label">Source</label>
        <select name="source" class="form-control">
          <option value="">All</option>
          <option value="online" <?= $filters['source']==='online'?'selected':'' ?>>Online</option>
          <option value="walk_in" <?= $filters['source']==='walk_in'?'selected':'' ?>>Walk-in</option>
        </select>
      </div>
      <div><label class="form-label">From</label><input type="date" name="date_from" class="form-control" value="<?= sanitize($filters['date_from']) ?>"></div>
      <div><label class="form-label">To</label><input type="date" name="date_to" class="form-control" value="<?= sanitize($filters['date_to']) ?>"></div>
      <div><button type="submit" class="btn btn-primary">Filter</button></div>
    </form>
    <div class="table-wrap">
      <table>
        <thead><tr><th>ID</th><th>Guest</th><th>Type</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>Total</th><th>Source</th><th>Status</th></tr></thead>
        <tbody>
        <?php if (!$bookings): ?>
        <tr><td colspan="9" class="text-center text-muted" style="padding:2rem">No bookings found</td></tr>
        <?php else: ?>
        <?php foreach ($bookings as $b): ?>
        <tr>
          <td>#<?= $b['id'] ?></td>
          <td><?= sanitize($b['guest_name']) ?></td>
          <td><?= sanitize($b['type_name']) ?></td>
          <td><?= $b['room_number'] ? 'R'.sanitize($b['room_number']) : '—' ?></td>
          <td><?= sanitize($b['checkin_date']) ?></td>
          <td><?= sanitize($b['checkout_date']) ?></td>
          <td>৳<?= number_format($b['total_price'],2) ?></td>
          <td><span class="badge badge-<?= $b['source'] ?>"><?= $b['source'] ?></span></td>
          <td><span class="badge badge-<?= $b['status'] ?>"><?= $b['status'] ?></span></td>
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
