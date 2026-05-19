<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('guest');
require_once __DIR__ . '/../models/GuestModel.php';

$model   = new GuestModel();
$userId  = $_SESSION['user_id'];
$balance = $model->getLoyaltyBalance($userId);
$history = $model->getLoyaltyHistory($userId);

$pageTitle  = 'Loyalty Points';
$activeRole = 'guest';
$activePage = 'loyalty';
include BASE_PATH . '/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__ . '/sidebar_guest.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Loyalty Points</h1><p class="page-subtitle">Earn points with every stay and redeem for discounts</p></div>
  </div>

  <div class="stats-grid">
    <div class="stat-card" style="border-top-color:var(--gold)">
      <div class="stat-label">Current Balance</div>
      <div class="stat-value text-gold"><?= number_format($balance) ?></div>
      <div class="stat-sub">Points available</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Cash Value</div>
      <div class="stat-value">৳<?= number_format($balance * POINTS_VALUE, 2) ?></div>
      <div class="stat-sub">Redeemable as discount</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Total Earned</div>
      <div class="stat-value"><?= number_format(array_sum(array_column($history, 'points_earned'))) ?></div>
      <div class="stat-sub">All time</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Total Redeemed</div>
      <div class="stat-value"><?= number_format(array_sum(array_column($history, 'points_used'))) ?></div>
      <div class="stat-sub">All time</div>
    </div>
  </div>

  <div class="card" style="border-left:4px solid var(--gold);margin-bottom:1.5rem">
    <div style="display:flex;align-items:center;gap:1rem">
      <div style="font-size:2.5rem">🏅</div>
      <div>
        <strong>How to earn points</strong>
        <p style="color:var(--gray-mid);margin:0.2rem 0 0;font-size:0.88rem">You earn points automatically after each completed stay based on your total bill. Leaving a review also earns bonus points. Use your points as a discount on your next booking.</p>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-title">Points History</div>
    <?php if (!$history): ?>
    <div class="empty-state"><p>No points activity yet. Complete a stay to start earning!</p></div>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Date</th><th>Stay</th><th>Earned</th><th>Redeemed</th><th>Balance</th></tr></thead>
        <tbody>
        <?php foreach ($history as $h): ?>
        <tr>
          <td><?= date('M j, Y', strtotime($h['created_at'])) ?></td>
          <td><?= sanitize($h['type_name']) ?> (<?= sanitize($h['checkin_date']) ?>)</td>
          <td style="color:var(--success)">+<?= number_format($h['points_earned']) ?></td>
          <td style="color:var(--danger)"><?= $h['points_used'] > 0 ? '-'.number_format($h['points_used']) : '—' ?></td>
          <td><strong><?= number_format($h['balance']) ?></strong></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</main>
</div>
<?php include BASE_PATH . '/includes/footer.php'; ?>
