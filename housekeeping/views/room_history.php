<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('housekeeping');
require_once __DIR__ . '/../models/HousekeepingModel.php';
$model  = new HousekeepingModel();
$roomId = (int)($_GET['id'] ?? 0);
$room   = $model->getRoomById($roomId);
if (!$room) { flashMessage('error','Room not found'); header('Location:'.BASE_URL.'/housekeeping/views/rooms.php'); exit; }
$history = $model->getTaskHistoryForRoom($roomId);
$pageTitle='Room '.$room['room_number'].' History'; $activeRole='housekeeping'; $activePage='rooms';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div>
      <h1 class="page-title">Room <?= sanitize($room['room_number']) ?> History</h1>
      <p class="page-subtitle"><?= sanitize($room['type_name']) ?> — Floor <?= $room['floor'] ?> — <span class="badge badge-<?= $room['status'] ?>"><?= $room['status'] ?></span></p>
    </div>
    <a href="<?= BASE_URL ?>/housekeeping/views/rooms.php" class="btn btn-secondary">← Back</a>
  </div>
  <div class="card">
    <div class="card-title">Task History</div>
    <?php if (!$history): ?>
    <div class="empty-state"><div class="empty-icon">📋</div><p>No task history for this room.</p></div>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Date</th><th>Type</th><th>Priority</th><th>Assigned To</th><th>Status</th><th>Notes</th><th>Completed</th></tr></thead>
        <tbody>
        <?php foreach ($history as $h): ?>
        <tr>
          <td><?= sanitize($h['scheduled_date']) ?></td>
          <td><?= ucfirst($h['task_type']) ?></td>
          <td><span class="badge badge-<?= $h['priority'] ?>"><?= $h['priority'] ?></span></td>
          <td><?= sanitize($h['assigned_name']) ?></td>
          <td><span class="badge badge-<?= $h['status'] ?>"><?= $h['status'] ?></span></td>
          <td style="max-width:200px;font-size:0.82rem"><?= sanitize($h['notes'] ?: '—') ?></td>
          <td><?= $h['completed_at'] ? date('M j, Y g:i a', strtotime($h['completed_at'])) : '—' ?></td>
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
