<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('receptionist');
require_once __DIR__ . '/../models/ReceptionistModel.php';
$model  = new ReceptionistModel();
$userId = $_SESSION['user_id'];
$user   = $model->getUserById($userId);

if (empty($user)) {
    flashMessage('error', 'Could not load profile. Please log in again.');
    header('Location: ' . BASE_URL . '/controllers/auth_controller.php?action=logout');
    exit;
}
$pageTitle='My Profile'; $activeRole='receptionist'; $activePage='profile';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header"><h1 class="page-title">My Profile</h1></div>
  <div class="card" style="max-width:500px">
    <form method="POST" action="<?= BASE_URL ?>/receptionist/controllers/receptionist_controller.php">
      <input type="hidden" name="action" value="update_profile">
      <div class="form-group"><label class="form-label">Full Name</label><input type="text" name="name" class="form-control" value="<?= sanitize($user['name'] ?? '') ?>" required></div>
      <div class="form-group"><label class="form-label">Email</label><input class="form-control" value="<?= sanitize($user['email'] ?? '') ?>" disabled></div>
      <div class="form-group"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control" value="<?= sanitize($user['phone'] ?? '') ?>"></div>
      <div class="form-group"><label class="form-label">Role</label><input class="form-control" value="Receptionist" disabled></div>
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
  </div>
</main>
</div>
<?php include BASE_PATH.'/includes/footer.php'; ?>
