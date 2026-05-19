<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('housekeeping');
require_once __DIR__ . '/../models/HousekeepingModel.php';
$model    = new HousekeepingModel();
$userId   = $_SESSION['user_id'];
$rooms    = $model->getAllRooms();
$filterStatus   = $_GET['status'] ?? '';
$filterPriority = $_GET['priority'] ?? '';
$tasks    = $model->getTodayTasks(null, $filterStatus ?: null, $filterPriority ?: null);
$pageTitle='Housekeeping Tasks'; $activeRole='housekeeping'; $activePage='tasks';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Today's Tasks</h1><p class="page-subtitle"><?= date('F j, Y') ?></p></div>
    <button onclick="openModal('newTaskModal')" class="btn btn-primary">+ New Task</button>
  </div>

  <!-- Filters -->
  <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:1.2rem">
    <?php
    $statuses   = [''=> 'All Status','pending'=>'Pending','in_progress'=>'In Progress','done'=>'Done'];
    $priorities = [''=> 'All Priority','urgent'=>'Urgent','normal'=>'Normal'];
    foreach ($statuses as $v=>$l): ?>
    <a href="?status=<?= $v ?>&priority=<?= urlencode($filterPriority) ?>" class="btn btn-sm <?= $filterStatus===$v?'btn-primary':'btn-outline' ?>"><?= $l ?></a>
    <?php endforeach; ?>
    <span style="border-left:1px solid var(--gray-lt);margin:0 0.3rem"></span>
    <?php foreach ($priorities as $v=>$l): ?>
    <a href="?status=<?= urlencode($filterStatus) ?>&priority=<?= $v ?>" class="btn btn-sm <?= $filterPriority===$v?'btn-primary':'btn-outline' ?>"><?= $l ?></a>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <?php if (!$tasks): ?>
    <div class="empty-state"><div class="empty-icon">📋</div><p>No tasks found for today.</p></div>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>ID</th><th>Room</th><th>Type</th><th>Priority</th><th>Status</th><th>Notes</th><th>Scheduled</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($tasks as $t): ?>
        <tr>
          <td>#<?= $t['id'] ?></td>
          <td>Room <?= sanitize($t['room_number']) ?> <small class="text-muted">(F<?= $t['floor'] ?>)</small></td>
          <td><?= ucfirst($t['task_type']) ?></td>
          <td><span class="badge badge-<?= $t['priority'] ?>"><?= $t['priority'] ?></span></td>
          <td><span class="badge badge-<?= $t['status'] ?>"><?= $t['status'] ?></span></td>
          <td style="max-width:180px;font-size:0.82rem"><?= sanitize($t['notes'] ?: '—') ?></td>
          <td><?= sanitize($t['scheduled_date']) ?></td>
          <td>
            <?php if ($t['status'] === 'pending'): ?>
            <form method="POST" action="<?= BASE_URL ?>/housekeeping/controllers/housekeeping_controller.php" style="display:inline">
              <input type="hidden" name="action" value="update_task">
              <input type="hidden" name="task_id" value="<?= $t['id'] ?>">
              <input type="hidden" name="status" value="in_progress">
              <button type="submit" class="btn btn-sm btn-secondary">Start</button>
            </form>
            <?php elseif ($t['status'] === 'in_progress'): ?>
            <button onclick="openCompleteModal(<?= $t['id'] ?>, <?= $t['room_id'] ?>)" class="btn btn-sm btn-success">Complete</button>
            <?php else: ?>
            <span class="text-muted">✓ Done</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <!-- New Task Modal -->
  <div class="modal-overlay" id="newTaskModal">
    <div class="modal">
      <button class="modal-close" onclick="closeModal('newTaskModal')">×</button>
      <h2 class="modal-title">Create Housekeeping Task</h2>
      <form method="POST" action="<?= BASE_URL ?>/housekeeping/controllers/housekeeping_controller.php">
        <input type="hidden" name="action" value="create_task">
        <div class="form-group"><label class="form-label">Room *</label>
          <select name="room_id" class="form-control" required>
            <option value="">Select room...</option>
            <?php foreach ($rooms as $r): ?>
            <option value="<?= $r['id'] ?>">Room <?= sanitize($r['room_number']) ?> (<?= sanitize($r['type_name']) ?>) — <?= $r['status'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Task Type *</label>
            <select name="task_type" class="form-control" required>
              <option value="cleaning">Cleaning</option>
              <option value="inspection">Inspection</option>
              <option value="maintenance">Maintenance</option>
            </select>
          </div>
          <div class="form-group"><label class="form-label">Priority</label>
            <select name="priority" class="form-control">
              <option value="normal">Normal</option>
              <option value="urgent">Urgent</option>
            </select>
          </div>
        </div>
        <div class="form-group"><label class="form-label">Scheduled Date</label><input type="date" name="scheduled_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
        <div class="form-group"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
        <button type="submit" class="btn btn-primary" style="width:100%">Create Task</button>
      </form>
    </div>
  </div>

  <!-- Complete Task Modal -->
  <div class="modal-overlay" id="completeModal">
    <div class="modal">
      <button class="modal-close" onclick="closeModal('completeModal')">×</button>
      <h2 class="modal-title">Complete Task</h2>
      <form method="POST" action="<?= BASE_URL ?>/housekeeping/controllers/housekeeping_controller.php" id="completeForm">
        <input type="hidden" name="action" value="update_task">
        <input type="hidden" name="task_id" id="completeTaskId">
        <input type="hidden" name="status" value="done">
        <div class="form-group"><label class="form-label">Completion Notes</label><textarea name="notes" class="form-control" rows="3" placeholder="Any issues found or notes..."></textarea></div>
        <button type="submit" class="btn btn-success" style="width:100%">Mark as Done</button>
      </form>
      <hr class="divider">
      <form method="POST" action="<?= BASE_URL ?>/housekeeping/controllers/housekeeping_controller.php" id="markCleanForm">
        <input type="hidden" name="action" value="mark_clean">
        <input type="hidden" name="room_id" id="completeRoomId">
        <button type="submit" class="btn btn-primary" style="width:100%" data-confirm="Mark this room as clean/available?">✅ Also Mark Room as Available</button>
      </form>
    </div>
  </div>
</main>
</div>
<script>
function openCompleteModal(taskId, roomId) {
  document.getElementById('completeTaskId').value = taskId;
  document.getElementById('completeRoomId').value = roomId;
  openModal('completeModal');
}
</script>
<?php include BASE_PATH.'/includes/footer.php'; ?>
