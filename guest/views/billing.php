<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('guest');
require_once __DIR__ . '/../models/GuestModel.php';

$model   = new GuestModel();
$userId  = $_SESSION['user_id'];
$bills   = $model->getBillingHistory($userId);

$pageTitle  = 'Billing History';
$activeRole = 'guest';
$activePage = 'billing';
include BASE_PATH . '/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__ . '/sidebar_guest.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Billing History</h1><p class="page-subtitle"><?= count($bills) ?> invoice<?= count($bills) !== 1 ? 's' : '' ?></p></div>
    <button onclick="window.print()" class="btn btn-secondary">🖨 Print</button>
  </div>

  <?php if (!$bills): ?>
  <div class="empty-state"><div class="empty-icon">💳</div><p>No billing records yet.</p></div>
  <?php else: ?>
  <div class="card">
    <div class="table-wrap">
      <table>
        <thead><tr><th>Date</th><th>Room Type</th><th>Room</th><th>Stay</th><th>Base</th><th>Extras</th><th>Discount</th><th>Total</th><th>Status</th><th>Receipt</th></tr></thead>
        <tbody>
        <?php $grandTotal = 0; ?>
        <?php foreach ($bills as $b): ?>
        <?php $grandTotal += $b['total_amount']; ?>
        <tr>
          <td><?= $b['paid_at'] ? date('M j, Y', strtotime($b['paid_at'])) : '—' ?></td>
          <td><strong><?= sanitize($b['type_name']) ?></strong></td>
          <td><?= $b['room_number'] ? 'Room ' . sanitize($b['room_number']) : '—' ?></td>
          <td style="font-size:0.82rem"><?= sanitize($b['checkin_date']) ?><br><?= sanitize($b['checkout_date']) ?></td>
          <td>৳<?= number_format($b['base_amount'], 2) ?></td>
          <td>৳<?= number_format($b['extras_amount'], 2) ?></td>
          <td style="color:var(--success)">-৳<?= number_format($b['discount_amount'], 2) ?></td>
          <td><strong style="color:var(--gold)">৳<?= number_format($b['total_amount'], 2) ?></strong></td>
          <td><span class="badge badge-<?= $b['payment_status'] === 'paid' ? 'available' : 'pending' ?>"><?= ucfirst($b['payment_status']) ?></span></td>
          <td>
            <?php if (!empty($b['receipt_path'])): ?>
            <a href="<?= BASE_URL ?>/<?= sanitize($b['receipt_path']) ?>" target="_blank" class="btn btn-sm btn-outline">📄 View</a>
            <?php else: ?>
            <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <tr style="background:var(--navy);color:var(--white)">
          <td colspan="7"><strong>Grand Total</strong></td>
          <td colspan="3"><strong style="color:var(--gold)">৳<?= number_format($grandTotal, 2) ?></strong></td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
</main>
</div>
<?php include BASE_PATH . '/includes/footer.php'; ?>
