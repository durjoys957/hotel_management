<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('admin');
require_once __DIR__ . '/../models/AdminModel.php';
$model = new AdminModel();
$rooms = $model->getRooms();
$types = $model->getRoomTypes();
$edit  = isset($_GET['edit']) ? $model->getRoomById((int)$_GET['edit']) : null;
$pageTitle='Rooms'; $activeRole='admin'; $activePage='rooms';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Rooms</h1><p class="page-subtitle"><?= count($rooms) ?> total rooms</p></div>
    <button onclick="openModal('newRoomModal')" class="btn btn-primary">+ Add Room</button>
  </div>

  <?php if ($edit): ?>
  <div class="card">
    <div class="card-title">Edit Room <?= sanitize($edit['room_number']) ?></div>
    <form method="POST" action="<?= BASE_URL ?>/admin/controllers/admin_controller.php" style="max-width:600px">
      <input type="hidden" name="action" value="update_room">
      <input type="hidden" name="id" value="<?= $edit['id'] ?>">
      <?php include __DIR__.'/_room_form.php'; ?>
    </form>
  </div>
  <?php endif; ?>

  <div class="card">
    <div class="table-wrap">
      <table>
        <thead><tr><th>Room #</th><th>Type</th><th>Floor</th><th>Status</th><th>Notes</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (!$rooms): ?>
        <tr><td colspan="6" class="text-center text-muted" style="padding:2rem">No rooms found</td></tr>
        <?php else: ?>
        <?php foreach ($rooms as $r): ?>
        <tr>
          <td><strong><?= sanitize($r['room_number']) ?></strong></td>
          <td><?= sanitize($r['type_name']) ?></td>
          <td>Floor <?= $r['floor'] ?></td>
          <td><span class="badge badge-<?= $r['status'] ?>"><?= $r['status'] ?></span></td>
          <td style="font-size:0.82rem"><?= sanitize($r['notes'] ?: '—') ?></td>
          <td>
            <a href="?edit=<?= $r['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
            <form method="POST" action="<?= BASE_URL ?>/admin/controllers/admin_controller.php" style="display:inline">
              <input type="hidden" name="action" value="delete_room">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <button type="submit" class="btn btn-sm btn-danger" data-confirm="Delete Room <?= sanitize($r['room_number']) ?>?">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- New Room Modal -->
  <div class="modal-overlay" id="newRoomModal">
    <div class="modal">
      <button class="modal-close" onclick="closeModal('newRoomModal')">×</button>
      <h2 class="modal-title">Add Room</h2>
      <form method="POST" action="<?= BASE_URL ?>/admin/controllers/admin_controller.php">
        <input type="hidden" name="action" value="create_room">
        <?php include __DIR__.'/_room_form.php'; ?>
      </form>
    </div>
  </div>
</main>
</div>
<?php include BASE_PATH.'/includes/footer.php'; ?>
