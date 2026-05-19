<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('housekeeping');
require_once __DIR__ . '/../models/HousekeepingModel.php';
$model    = new HousekeepingModel();
$userId   = $_SESSION['user_id'];
$rooms    = $model->getAllRooms();
$filter   = $_GET['status'] ?? '';
$reports  = $model->getMaintenanceReports($filter ?: null);
$pageTitle='Maintenance Reports'; $activeRole='housekeeping'; $activePage='maintenance';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Maintenance Reports</h1><p class="page-subtitle"><?= count($reports) ?> reports</p></div>
    <button onclick="openModal('newReportModal')" class="btn btn-primary">+ Log Issue</button>
  </div>

  <div style="display:flex;gap:0.5rem;margin-bottom:1.2rem">
    <?php foreach ([''=> 'All','open'=>'Open','in_progress'=>'In Progress','resolved'=>'Resolved'] as $v=>$l): ?>
    <a href="?status=<?= $v ?>" class="btn btn-sm <?= $filter===$v?'btn-primary':'btn-outline' ?>"><?= $l ?></a>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <?php if (!$reports): ?>
    <div class="empty-state"><div class="empty-icon">🔧</div><p>No maintenance reports found.</p></div>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>ID</th><th>Room</th><th>Description</th><th>Severity</th><th>Reporter</th><th>Reported</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($reports as $r): ?>
        <tr>
          <td>#<?= $r['id'] ?></td>
          <td>Room <?= sanitize($r['room_number']) ?> <small class="text-muted">(F<?= $r['floor'] ?>)</small></td>
          <td style="max-width:200px"><?= sanitize($r['description']) ?></td>
          <td><span class="badge badge-<?= $r['severity'] ?>"><?= $r['severity'] ?></span></td>
          <td><?= sanitize($r['reporter']) ?></td>
          <td><?= date('M j, Y g:i a', strtotime($r['reported_at'])) ?></td>
          <td><span class="badge badge-<?= $r['status'] ?>"><?= $r['status'] ?></span></td>
          <td style="white-space:nowrap">
            <?php if ($r['status'] === 'open'): ?>
            <form method="POST" action="<?= BASE_URL ?>/housekeeping/controllers/housekeeping_controller.php" style="display:inline">
              <input type="hidden" name="action" value="update_maintenance">
              <input type="hidden" name="report_id" value="<?= $r['id'] ?>">
              <input type="hidden" name="status" value="in_progress">
              <button type="submit" class="btn btn-sm btn-secondary">Start</button>
            </form>
            <?php endif; ?>
            <?php if ($r['status'] !== 'resolved'): ?>
            <form method="POST" action="<?= BASE_URL ?>/housekeeping/controllers/housekeeping_controller.php" style="display:inline">
              <input type="hidden" name="action" value="update_maintenance">
              <input type="hidden" name="report_id" value="<?= $r['id'] ?>">
              <input type="hidden" name="status" value="resolved">
              <button type="submit" class="btn btn-sm btn-success" data-confirm="Mark as resolved? Room will be restored to available.">Resolve</button>
            </form>
            <?php else: ?>
            <?php if ($r['resolved_at']): ?><small class="text-muted">Resolved <?= date('M j', strtotime($r['resolved_at'])) ?></small><?php endif; ?>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <!-- New Report Modal -->
  <div class="modal-overlay" id="newReportModal">
    <div class="modal">
      <button class="modal-close" onclick="closeModal('newReportModal')">×</button>
      <h2 class="modal-title">Log Maintenance Issue</h2>
      <form method="POST" action="<?= BASE_URL ?>/housekeeping/controllers/housekeeping_controller.php">
        <input type="hidden" name="action" value="create_maintenance">
        <div class="form-group">
          <label class="form-label">Room *</label>
          <select name="room_id" class="form-control" required>
            <option value="">Select room...</option>
            <?php foreach ($rooms as $r): ?>
            <option value="<?= $r['id'] ?>">Room <?= sanitize($r['room_number']) ?> — <?= sanitize($r['type_name']) ?> (<?= $r['status'] ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Description *</label>
          <textarea name="description" class="form-control" rows="3" placeholder="Describe the issue in detail..." required></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Severity</label>
          <select name="severity" class="form-control">
            <option value="low">Low — Minor issue, not urgent</option>
            <option value="medium" selected>Medium — Needs attention soon</option>
            <option value="high">High — Critical, immediate action needed</option>
          </select>
        </div>
        <p style="font-size:0.85rem;color:var(--warning);margin-bottom:1rem">⚠️ Room will be automatically set to <strong>Maintenance</strong> status.</p>
        <button type="submit" class="btn btn-primary" style="width:100%">Submit Report</button>
      </form>
    </div>
  </div>
</main>
</div>
<?php include BASE_PATH.'/includes/footer.php'; ?>
