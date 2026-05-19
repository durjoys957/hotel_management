<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('guest');
require_once __DIR__ . '/../models/GuestModel.php';

$model      = new GuestModel();
$userId     = $_SESSION['user_id'];
$requests   = $model->getServiceRequestsByGuest($userId);
$activeStay = $model->getActiveBookingForGuest($userId);
$preBooking = (int)($_GET['booking_id'] ?? 0);

$pageTitle  = 'Service Requests';
$activeRole = 'guest';
$activePage = 'services';
include BASE_PATH . '/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__ . '/sidebar_guest.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Service Requests</h1><p class="page-subtitle">Request in-stay services for your active booking</p></div>
    <?php if ($activeStay): ?>
    <button onclick="openModal('newServiceModal')" class="btn btn-primary">+ New Request</button>
    <?php endif; ?>
  </div>

  <?php if (!$activeStay): ?>
  <div class="card">
    <div class="empty-state"><div class="empty-icon">🛎</div><p>Service requests are only available during an active checked-in stay. <a href="<?= BASE_URL ?>/guest/views/search.php">Book a room</a></p></div>
  </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-title">My Requests</div>
    <?php if (!$requests): ?>
    <div class="empty-state"><p>No service requests yet.</p></div>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Type</th><th>Room</th><th>Description</th><th>Status</th><th>Requested</th></tr></thead>
        <tbody>
        <?php foreach ($requests as $sr): ?>
        <tr>
          <td><strong><?= ucfirst(str_replace('_', ' ', $sr['service_type'])) ?></strong></td>
          <td><?= $sr['room_number'] ? 'Room ' . sanitize($sr['room_number']) : '—' ?></td>
          <td style="font-size:0.85rem"><?= sanitize($sr['description'] ?: '—') ?></td>
          <td><span class="badge badge-<?= $sr['status'] === 'completed' ? 'available' : ($sr['status'] === 'in_progress' ? 'confirmed' : 'pending') ?>"><?= ucfirst($sr['status']) ?></span></td>
          <td style="font-size:0.82rem"><?= date('M j, g:i a', strtotime($sr['requested_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <?php if ($activeStay): ?>
  <div class="modal-overlay" id="newServiceModal">
    <div class="modal">
      <button class="modal-close" onclick="closeModal('newServiceModal')">×</button>
      <h2 class="modal-title">New Service Request</h2>
      <form method="POST" action="<?= BASE_URL ?>/guest/controllers/GuestController.php">
        <input type="hidden" name="action" value="create_service">
        <input type="hidden" name="booking_id" value="<?= $activeStay['id'] ?>">
        <input type="hidden" name="room_id" value="<?= $activeStay['room_id'] ?? '' ?>">
        <div class="form-group">
          <label class="form-label">Service Type *</label>
          <select name="service_type" class="form-control" required>
            <option value="">Select...</option>
            <option value="extra_bed">Extra Bed</option>
            <option value="toiletries">Toiletries</option>
            <option value="laundry">Laundry</option>
            <option value="room_service">Room Service</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="3" placeholder="Provide any extra details (optional)"></textarea>
        </div>
        <p style="font-size:0.85rem;color:var(--gray-mid)">Stay: <?= sanitize($activeStay['type_name']) ?><?= $activeStay['room_number'] ? ' — Room ' . sanitize($activeStay['room_number']) : '' ?></p>
        <button type="submit" class="btn btn-primary" style="width:100%">Submit Request</button>
      </form>
    </div>
  </div>
  <?php if ($preBooking): ?>
  <script>document.addEventListener('DOMContentLoaded', () => openModal('newServiceModal'));</script>
  <?php endif; ?>
  <?php endif; ?>
</main>
</div>
<?php include BASE_PATH . '/includes/footer.php'; ?>
