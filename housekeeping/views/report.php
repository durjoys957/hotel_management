<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('housekeeping');
require_once __DIR__ . '/../models/HousekeepingModel.php';
$model  = new HousekeepingModel();
$date   = $_GET['date'] ?? date('Y-m-d');
$report = $model->getDailyReport($date);
$allTasks = $model->getTodayTasks();
$pageTitle='Housekeeping Report'; $activeRole='housekeeping'; $activePage='report';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Daily Housekeeping Report</h1></div>
    <div style="display:flex;gap:0.8rem;align-items:center">
      <form method="GET" style="display:flex;gap:0.5rem">
        <input type="date" name="date" class="form-control" value="<?= sanitize($date) ?>">
        <button type="submit" class="btn btn-primary">View</button>
      </form>
      <button onclick="window.print()" class="btn btn-secondary">🖨 Print</button>
    </div>
  </div>

  <div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Total Assigned</div><div class="stat-value"><?= $report['total'] ?></div></div>
    <div class="stat-card"><div class="stat-label">Completed</div><div class="stat-value text-gold"><?= $report['done'] ?></div></div>
    <div class="stat-card"><div class="stat-label">In Progress</div><div class="stat-value"><?= $report['in_progress'] ?></div></div>
    <div class="stat-card"><div class="stat-label">Pending</div><div class="stat-value"><?= $report['pending'] ?></div></div>
    <div class="stat-card"><div class="stat-label">Rooms Available</div><div class="stat-value"><?= $report['rooms_cleared'] ?></div></div>
    <div class="stat-card">
      <div class="stat-label">Completion Rate</div>
      <div class="stat-value"><?= $report['total'] > 0 ? round($report['done']/$report['total']*100) : 0 ?>%</div>
    </div>
  </div>

  <div class="card">
    <div class="card-title">Task Summary for <?= date('F j, Y', strtotime($date)) ?></div>
    <?php if (!$allTasks): ?>
    <div class="empty-state"><div class="empty-icon">📊</div><p>No tasks for this date.</p></div>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Room</th><th>Type</th><th>Priority</th><th>Assigned To</th><th>Status</th><th>Completed At</th><th>Notes</th></tr></thead>
        <tbody>
        <?php foreach ($allTasks as $t): ?>
        <tr>
          <td>Room <?= sanitize($t['room_number']) ?></td>
          <td><?= ucfirst($t['task_type']) ?></td>
          <td><span class="badge badge-<?= $t['priority'] ?>"><?= $t['priority'] ?></span></td>
          <td><?= sanitize($t['assigned_name']) ?></td>
          <td><span class="badge badge-<?= $t['status'] ?>"><?= $t['status'] ?></span></td>
          <td><?= $t['completed_at'] ? date('g:i a', strtotime($t['completed_at'])) : '—' ?></td>
          <td style="font-size:0.82rem"><?= sanitize($t['notes'] ?: '—') ?></td>
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
