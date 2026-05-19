<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('admin');
require_once __DIR__ . '/../models/AdminModel.php';
$model  = new AdminModel();
$search = trim($_GET['search'] ?? '');
$guests = $model->getGuests($search);
$pageTitle='Guest Accounts'; $activeRole='admin'; $activePage='guests';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Guest Accounts</h1><p class="page-subtitle"><?= count($guests) ?> guests</p></div>
  </div>
  <div class="card">
    <form method="GET" style="display:flex;gap:0.8rem;margin-bottom:1rem">
      <input type="text" name="search" class="form-control" placeholder="Search by name, email, or phone..." value="<?= sanitize($search) ?>" style="flex:1">
      <button type="submit" class="btn btn-primary">Search</button>
      <?php if ($search): ?><a href="?" class="btn btn-secondary">Clear</a><?php endif; ?>
    </form>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Nationality</th><th>Bookings</th><th>Joined</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (!$guests): ?>
        <tr><td colspan="8" class="text-center text-muted" style="padding:2rem">No guests found</td></tr>
        <?php else: ?>
        <?php foreach ($guests as $g): ?>
        <tr>
          <td><strong><?= sanitize($g['name']) ?></strong></td>
          <td><?= sanitize($g['email']) ?></td>
          <td><?= sanitize($g['phone'] ?? '—') ?></td>
          <td><?= sanitize($g['nationality'] ?? '—') ?></td>
          <td><?= $g['booking_count'] ?></td>
          <td><?= date('M j, Y', strtotime($g['created_at'])) ?></td>
          <td><span class="badge badge-<?= $g['is_active']?'available':'maintenance' ?>"><?= $g['is_active']?'Active':'Inactive' ?></span></td>
          <td>
            <form method="POST" action="<?= BASE_URL ?>/admin/controllers/admin_controller.php" style="display:inline">
              <input type="hidden" name="action" value="toggle_user">
              <input type="hidden" name="id" value="<?= $g['id'] ?>">
              <input type="hidden" name="active" value="<?= $g['is_active']?0:1 ?>">
              <button type="submit" class="btn btn-sm <?= $g['is_active']?'btn-danger':'btn-success' ?>"><?= $g['is_active']?'Deactivate':'Activate' ?></button>
            </form>
          </td>
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
