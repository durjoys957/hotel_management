<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('admin');
require_once __DIR__ . '/../models/AdminModel.php';
$model   = new AdminModel();
$pricing = $model->getSeasonalPricing();
$types   = $model->getRoomTypes();
$pageTitle='Seasonal Pricing'; $activeRole='admin'; $activePage='seasonal';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Seasonal Pricing</h1><p class="page-subtitle">Date-range price overrides per room type</p></div>
    <button onclick="openModal('newModal')" class="btn btn-primary">+ Add Pricing Rule</button>
  </div>
  <div class="card">
    <?php if (!$pricing): ?>
    <div class="empty-state"><div class="empty-icon">🎉</div><p>No seasonal pricing rules yet.</p></div>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Label</th><th>Room Type</th><th>Start</th><th>End</th><th>Price/Night</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($pricing as $p): ?>
        <tr>
          <td><strong><?= sanitize($p['label']) ?></strong></td>
          <td><?= sanitize($p['type_name']) ?></td>
          <td><?= sanitize($p['start_date']) ?></td>
          <td><?= sanitize($p['end_date']) ?></td>
          <td>৳<?= number_format($p['price_per_night'],2) ?></td>
          <td>
            <button onclick="openEditModal(<?= htmlspecialchars(json_encode($p)) ?>)" class="btn btn-sm btn-secondary">Edit</button>
            <form method="POST" action="<?= BASE_URL ?>/admin/controllers/admin_controller.php" style="display:inline">
              <input type="hidden" name="action" value="delete_seasonal">
              <input type="hidden" name="id" value="<?= $p['id'] ?>">
              <button type="submit" class="btn btn-sm btn-danger" data-confirm="Delete this pricing rule?">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <!-- New Pricing Rule Modal -->
  <div class="modal-overlay" id="newModal">
    <div class="modal">
      <button class="modal-close" onclick="closeModal('newModal')">×</button>
      <h2 class="modal-title">Add Pricing Rule</h2>
      <form method="POST" action="<?= BASE_URL ?>/admin/controllers/admin_controller.php">
        <input type="hidden" name="action" value="create_seasonal">
        <div class="form-group">
          <label class="form-label">Label *</label>
          <input type="text" name="label" class="form-control" required placeholder="e.g. Eid Holiday">
        </div>
        <div class="form-group">
          <label class="form-label">Room Type *</label>
          <select name="room_type_id" class="form-control" required>
            <option value="">Select...</option>
            <?php foreach ($types as $t): ?>
            <option value="<?= $t['id'] ?>"><?= sanitize($t['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control" required></div>
          <div class="form-group"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control" required></div>
        </div>
        <div class="form-group">
          <label class="form-label">Price per Night (৳)</label>
          <input type="number" name="price_per_night" class="form-control" required min="1" step="0.01">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%">Create Rule</button>
      </form>
    </div>
  </div>

  <!-- Edit Pricing Rule Modal -->
  <div class="modal-overlay" id="editModal">
    <div class="modal">
      <button class="modal-close" onclick="closeModal('editModal')">×</button>
      <h2 class="modal-title">Edit Pricing Rule</h2>
      <form method="POST" action="<?= BASE_URL ?>/admin/controllers/admin_controller.php">
        <input type="hidden" name="action" value="update_seasonal">
        <input type="hidden" name="id" id="edit_id" value="">
        <div class="form-group">
          <label class="form-label">Label *</label>
          <input type="text" name="label" id="edit_label" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Room Type *</label>
          <select name="room_type_id" id="edit_type" class="form-control" required>
            <option value="">Select...</option>
            <?php foreach ($types as $t): ?>
            <option value="<?= $t['id'] ?>"><?= sanitize($t['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Start Date</label><input type="date" name="start_date" id="edit_start" class="form-control" required></div>
          <div class="form-group"><label class="form-label">End Date</label><input type="date" name="end_date" id="edit_end" class="form-control" required></div>
        </div>
        <div class="form-group">
          <label class="form-label">Price per Night (৳)</label>
          <input type="number" name="price_per_night" id="edit_price" class="form-control" required min="1" step="0.01">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%">Save Changes</button>
      </form>
    </div>
  </div>

</main>
</div>
<script>
function openEditModal(p) {
  document.getElementById('edit_id').value    = p.id;
  document.getElementById('edit_label').value = p.label;
  document.getElementById('edit_type').value  = p.room_type_id;
  document.getElementById('edit_start').value = p.start_date;
  document.getElementById('edit_end').value   = p.end_date;
  document.getElementById('edit_price').value = p.price_per_night;
  openModal('editModal');
}
</script>
<?php include BASE_PATH.'/includes/footer.php'; ?>
