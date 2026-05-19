<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('admin');
require_once __DIR__ . '/../models/AdminModel.php';
$model = new AdminModel();
$staff = $model->getStaff();
$filter = $_GET['role'] ?? '';
if ($filter) $staff = array_filter($staff, fn($s) => $s['role'] === $filter);
$pageTitle='Staff Management'; $activeRole='admin'; $activePage='staff';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Staff Management</h1></div>
    <button onclick="openModal('newStaffModal')" class="btn btn-primary">+ Add Staff</button>
  </div>

  <div style="display:flex;gap:0.5rem;margin-bottom:1.2rem">
    <?php foreach ([''=> 'All','receptionist'=>'Receptionist','housekeeping'=>'Housekeeping','admin'=>'Admin'] as $v=>$l): ?>
    <a href="?role=<?= $v ?>" class="btn btn-sm <?= $filter===$v?'btn-primary':'btn-outline' ?>"><?= $l ?></a>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <div class="table-wrap">
      <table>
        <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (!$staff): ?>
        <tr><td colspan="7" class="text-center text-muted" style="padding:2rem">No staff found</td></tr>
        <?php else: ?>
        <?php foreach ($staff as $s): ?>
        <tr>
          <td><strong><?= sanitize($s['name']) ?></strong></td>
          <td><?= sanitize($s['email']) ?></td>
          <td><?= sanitize($s['phone'] ?? '—') ?></td>
          <td><span class="badge badge-<?= $s['role']==='admin'?'occupied':($s['role']==='receptionist'?'confirmed':'available') ?>"><?= ucfirst($s['role']) ?></span></td>
          <td><span class="badge badge-<?= $s['is_active']?'available':'maintenance' ?>"><?= $s['is_active']?'Active':'Inactive' ?></span></td>
          <td><?= date('M j, Y', strtotime($s['created_at'])) ?></td>
          <td>
            <button onclick="openEditStaff(<?= htmlspecialchars(json_encode($s)) ?>)" class="btn btn-sm btn-secondary">Edit</button>
            <?php if ($s['id'] != $_SESSION['user_id']): ?>
            <form method="POST" action="<?= BASE_URL ?>/admin/controllers/admin_controller.php" style="display:inline">
              <input type="hidden" name="action" value="toggle_user">
              <input type="hidden" name="id" value="<?= $s['id'] ?>">
              <input type="hidden" name="active" value="<?= $s['is_active']?0:1 ?>">
              <button type="submit" class="btn btn-sm <?= $s['is_active']?'btn-danger':'btn-success' ?>"><?= $s['is_active']?'Deactivate':'Activate' ?></button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- New Staff Modal -->
  <div class="modal-overlay" id="newStaffModal">
    <div class="modal">
      <button class="modal-close" onclick="closeModal('newStaffModal')">×</button>
      <h2 class="modal-title">Add Staff Account</h2>
      <form method="POST" action="<?= BASE_URL ?>/admin/controllers/admin_controller.php">
        <input type="hidden" name="action" value="create_staff">
        <div class="form-group"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control"></div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Role *</label>
            <select name="role" class="form-control" required>
              <option value="">Select...</option>
              <option value="receptionist">Receptionist</option>
              <option value="housekeeping">Housekeeping</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="form-group"><label class="form-label">Password *</label><input type="password" name="password" class="form-control" required minlength="6"></div>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%">Create Account</button>
      </form>
    </div>
  </div>

  <!-- Edit Staff Modal -->
  <div class="modal-overlay" id="editStaffModal">
    <div class="modal">
      <button class="modal-close" onclick="closeModal('editStaffModal')">×</button>
      <h2 class="modal-title">Edit Staff</h2>
      <form method="POST" action="<?= BASE_URL ?>/admin/controllers/admin_controller.php">
        <input type="hidden" name="action" value="update_staff">
        <input type="hidden" name="id" id="edit_staff_id">
        <div class="form-group"><label class="form-label">Full Name</label><input type="text" name="name" id="edit_staff_name" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" id="edit_staff_email" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Phone</label><input type="tel" name="phone" id="edit_staff_phone" class="form-control"></div>
        <button type="submit" class="btn btn-primary" style="width:100%">Save Changes</button>
      </form>
    </div>
  </div>
</main>
</div>
<script>
function openEditStaff(s) {
  document.getElementById('edit_staff_id').value    = s.id;
  document.getElementById('edit_staff_name').value  = s.name;
  document.getElementById('edit_staff_email').value = s.email;
  document.getElementById('edit_staff_phone').value = s.phone || '';
  openModal('editStaffModal');
}
</script>
<?php include BASE_PATH.'/includes/footer.php'; ?>
