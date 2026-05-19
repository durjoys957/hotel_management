<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('admin');
require_once __DIR__ . '/../models/AdminModel.php';

$model  = new AdminModel();
$userId = $_SESSION['user_id'];
$user   = $model->getUserById($userId);

if (empty($user)) {
    flashMessage('error', 'Could not load profile. Please log in again.');
    header('Location: ' . BASE_URL . '/controllers/auth_controller.php?action=logout');
    exit;
}

$pageTitle = 'My Profile';
$activeRole = 'admin';
$activePage = 'profile';
include BASE_PATH . '/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__ . '/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div>
      <h1 class="page-title">My Profile</h1>
      <p class="page-subtitle">Manage your admin account</p>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start">

    <!-- Personal Info -->
    <div class="card">
      <div class="card-title">Personal Information</div>
      <form method="POST" action="<?= BASE_URL ?>/admin/controllers/admin_controller.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update_admin_profile">

        <div style="text-align:center;margin-bottom:1.5rem">
          <?php if (!empty($user['profile_pic'])): ?>
          <img src="<?= BASE_URL ?>/<?= sanitize($user['profile_pic']) ?>"
               style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid var(--gold)">
          <?php else: ?>
          <div style="width:90px;height:90px;border-radius:50%;background:var(--navy);color:var(--gold);display:flex;align-items:center;justify-content:center;font-size:2.5rem;margin:0 auto;font-family:'Playfair Display',serif">
            <?= strtoupper(substr($user['name'] ?? 'A', 0, 1)) ?>
          </div>
          <?php endif; ?>
          <div style="margin-top:0.6rem">
            <label style="cursor:pointer;color:var(--gold);font-size:0.85rem">
              📷 Change Photo
              <input type="file" name="profile_pic" accept="image/*" style="display:none"
                     onchange="this.nextElementSibling.textContent = this.files[0]?.name || ''">
            </label>
            <span style="display:block;font-size:0.78rem;color:var(--gray-mid)"></span>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Full Name *</label>
          <input type="text" name="name" class="form-control"
                 value="<?= sanitize($user['name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" class="form-control"
                 value="<?= sanitize($user['email'] ?? '') ?>" disabled
                 style="opacity:0.6;cursor:not-allowed">
          <span class="form-hint">Email cannot be changed here.</span>
        </div>
        <div class="form-group">
          <label class="form-label">Phone</label>
          <input type="tel" name="phone" class="form-control"
                 value="<?= sanitize($user['phone'] ?? '') ?>"
                 placeholder="+88017XXXXXXXX">
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%">
          Save Changes
        </button>
      </form>
    </div>

    <!-- Right column -->
    <div>
      <!-- Change Password -->
      <div class="card">
        <div class="card-title">Change Password</div>
        <form method="POST" action="<?= BASE_URL ?>/admin/controllers/admin_controller.php">
          <input type="hidden" name="action" value="change_admin_password">
          <div class="form-group">
            <label class="form-label">Current Password</label>
            <input type="password" name="old_password" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control"
                   required minlength="6" placeholder="At least 6 characters">
          </div>
          <div class="form-group">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-secondary" style="width:100%">
            Update Password
          </button>
        </form>
      </div>

      <!-- Account Info -->
      <div class="card">
        <div class="card-title">Account Details</div>
        <table style="width:100%;font-size:0.9rem">
          <tr>
            <td style="color:var(--gray-mid);padding:0.4rem 0;width:140px">Role</td>
            <td><span class="badge badge-occupied">Administrator</span></td>
          </tr>
          <tr>
            <td style="color:var(--gray-mid);padding:0.4rem 0">Status</td>
            <td><span class="badge badge-available">Active</span></td>
          </tr>
          <tr>
            <td style="color:var(--gray-mid);padding:0.4rem 0">Member Since</td>
            <td><?= !empty($user['created_at']) ? date('F j, Y', strtotime($user['created_at'])) : 'N/A' ?></td>
          </tr>
        </table>

        <hr class="divider">

        <div style="font-size:0.88rem;color:var(--gray-mid);line-height:1.7">
          <p>As an administrator, you have full access to all hotel operations, staff management, reports, and system configuration.</p>
        </div>
      </div>
    </div>

  </div>
</main>
</div>
<?php include BASE_PATH . '/includes/footer.php'; ?>
