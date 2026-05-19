<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('receptionist');
require_once __DIR__ . '/../models/ReceptionistModel.php';
$model   = new ReceptionistModel();
$filter  = $_GET['status'] ?? '';
$requests = $model->getServiceRequests($filter ?: null);
$pageTitle='Service Requests'; $activeRole='receptionist'; $activePage='services';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Service Requests</h1><p class="page-subtitle"><?= count($requests) ?> requests</p></div>
    <div style="display:flex;gap:0.5rem">
      <?php foreach ([''=> 'All','pending'=>'Pending','in_progress'=>'In Progress','completed'=>'Completed'] as $v=>$l): ?>
      <a href="?status=<?= $v ?>" class="btn btn-sm <?= $filter===$v?'btn-primary':'btn-outline' ?>"><?= $l ?></a>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="card">
    <?php if (!$requests): ?>
    <div class="empty-state"><div class="empty-icon">🛎</div><p>No service requests found.</p></div>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>ID</th><th>Guest</th><th>Room</th><th>Type</th><th>Description</th><th>Requested</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($requests as $r): ?>
        <tr>
          <td>#<?= $r['id'] ?></td>
          <td><?= sanitize($r['guest_name']) ?></td>
          <td>Room <?= sanitize($r['room_number']) ?></td>
          <td><span class="badge badge-pending"><?= str_replace('_',' ',sanitize($r['service_type'])) ?></span></td>
          <td><?= sanitize($r['description'] ?: '—') ?></td>
          <td><?= date('M j, g:i a', strtotime($r['requested_at'])) ?></td>
          <td><span class="badge badge-<?= $r['status'] ?>"><?= $r['status'] ?></span></td>
          <td>
            <?php if ($r['status'] === 'pending'): ?>
            <form method="POST" action="<?= BASE_URL ?>/receptionist/controllers/receptionist_controller.php" style="display:inline">
              <input type="hidden" name="action" value="update_service">
              <input type="hidden" name="service_id" value="<?= $r['id'] ?>">
              <input type="hidden" name="status" value="in_progress">
              <button type="submit" class="btn btn-sm btn-secondary">Start</button>
            </form>
            <?php elseif ($r['status'] === 'in_progress'): ?>
            <form method="POST" action="<?= BASE_URL ?>/receptionist/controllers/receptionist_controller.php" style="display:inline">
              <input type="hidden" name="action" value="update_service">
              <input type="hidden" name="service_id" value="<?= $r['id'] ?>">
              <input type="hidden" name="status" value="completed">
              <button type="submit" class="btn btn-sm btn-success">Complete</button>
            </form>
            <?php else: ?>
            <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
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
