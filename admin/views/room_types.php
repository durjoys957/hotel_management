<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('admin');
require_once __DIR__ . '/../models/AdminModel.php';
$model = new AdminModel();
$types = $model->getRoomTypes();
$edit  = isset($_GET['edit']) ? $model->getRoomTypeById((int)$_GET['edit']) : null;
$pageTitle='Room Types'; $activeRole='admin'; $activePage='room_types';
include BASE_PATH.'/includes/header.php';
$allAmenities = ['WiFi','AC','TV','Mini Bar','Jacuzzi','Lounge Area','King Bed','Room Service','Safe','Balcony','City View','Desk','Wardrobe','Bathroom','Toiletries'];
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Room Types</h1><p class="page-subtitle">Manage room categories</p></div>
    <button onclick="openModal('newTypeModal')" class="btn btn-primary">+ Add Room Type</button>
  </div>

  <div class="card">
    <?php if (!$types): ?>
    <div class="empty-state"><div class="empty-icon">🛏</div><p>No room types yet.</p></div>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Name</th><th>Price/Night</th><th>Capacity</th><th>Rooms</th><th>Amenities</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($types as $t): ?>
        <?php $am = $t['amenities'] ? (is_array($t['amenities']) ? $t['amenities'] : json_decode($t['amenities'],true)) : []; ?>
        <tr>
          <td><strong><?= sanitize($t['name']) ?></strong><br><small class="text-muted"><?= sanitize(substr($t['description']??'',0,50)) ?>…</small></td>
          <td>৳<?= number_format($t['price_per_night'],2) ?></td>
          <td><?= $t['max_capacity'] ?> guests</td>
          <td><?= $t['room_count'] ?></td>
          <td style="max-width:200px;font-size:0.8rem"><?= implode(', ', array_slice($am,0,4)) ?><?= count($am)>4?'…':'' ?></td>
          <td>
            <a href="?edit=<?= $t['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
            <form method="POST" action="<?= BASE_URL ?>/admin/controllers/admin_controller.php" style="display:inline">
              <input type="hidden" name="action" value="delete_room_type">
              <input type="hidden" name="id" value="<?= $t['id'] ?>">
              <button type="submit" class="btn btn-sm btn-danger" data-confirm="Delete '<?= sanitize($t['name']) ?>'? All rooms of this type will also be deleted.">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <?php if ($edit): ?>
  <!-- Edit Form -->
  <div class="card">
    <div class="card-title">Edit Room Type: <?= sanitize($edit['name']) ?></div>
    <form method="POST" action="<?= BASE_URL ?>/admin/controllers/admin_controller.php" enctype="multipart/form-data">
      <input type="hidden" name="action" value="update_room_type">
      <input type="hidden" name="id" value="<?= $edit['id'] ?>">
      <?php include __DIR__.'/_room_type_form.php'; ?>
    </form>
  </div>
  <?php endif; ?>

  <!-- New Modal -->
  <div class="modal-overlay" id="newTypeModal">
    <div class="modal" style="max-width:600px">
      <button class="modal-close" onclick="closeModal('newTypeModal')">×</button>
      <h2 class="modal-title">Add Room Type</h2>
      <form method="POST" action="<?= BASE_URL ?>/admin/controllers/admin_controller.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="create_room_type">
        <?php include __DIR__.'/_room_type_form.php'; ?>
      </form>
    </div>
  </div>
</main>
</div>
<?php include BASE_PATH.'/includes/footer.php'; ?>
