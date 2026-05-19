<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('housekeeping');
require_once __DIR__ . '/../models/HousekeepingModel.php';
$model     = new HousekeepingModel();
$checkouts = $model->getUpcomingCheckouts(2);
$checkins  = $model->getUpcomingCheckins(2);
$pageTitle='Schedule'; $activeRole='housekeeping'; $activePage='schedule';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Schedule</h1><p class="page-subtitle">Upcoming check-ins and check-outs for the next 2 days</p></div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">
    <div class="card">
      <div class="card-title">🚪 Upcoming Check-outs</div>
      <p style="font-size:0.85rem;color:var(--gray-mid);margin-bottom:1rem">These rooms will need cleaning soon. Plan ahead.</p>
      <?php if (!$checkouts): ?>
      <div class="empty-state"><div class="empty-icon">🚪</div><p>No check-outs in the next 2 days.</p></div>
      <?php else: ?>
      <?php foreach ($checkouts as $c): ?>
      <div style="padding:0.8rem;background:var(--gray-lt);border-radius:8px;margin-bottom:0.7rem;border-left:4px solid var(--warning)">
        <div style="display:flex;justify-content:space-between">
          <strong>Room <?= sanitize($c['room_number']) ?></strong>
          <span style="font-size:0.85rem;color:var(--warning);font-weight:600"><?= $c['checkout_date'] === date('Y-m-d') ? 'TODAY' : 'Tomorrow' ?></span>
        </div>
        <div style="font-size:0.85rem;color:var(--gray-dk);margin-top:0.2rem">
          <?= sanitize($c['type_name']) ?> | Guest: <?= sanitize($c['guest_name']) ?>
        </div>
        <div style="font-size:0.82rem;color:var(--gray-mid)">Check-out: <?= sanitize($c['checkout_date']) ?></div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="card">
      <div class="card-title">✅ Upcoming Check-ins</div>
      <p style="font-size:0.85rem;color:var(--gray-mid);margin-bottom:1rem">Ensure these rooms are clean and ready before guest arrival.</p>
      <?php if (!$checkins): ?>
      <div class="empty-state"><div class="empty-icon">✅</div><p>No check-ins in the next 2 days.</p></div>
      <?php else: ?>
      <?php foreach ($checkins as $c): ?>
      <div style="padding:0.8rem;background:var(--gray-lt);border-radius:8px;margin-bottom:0.7rem;border-left:4px solid var(--success)">
        <div style="display:flex;justify-content:space-between">
          <strong><?= sanitize($c['type_name']) ?></strong>
          <span style="font-size:0.85rem;color:var(--success);font-weight:600"><?= $c['checkin_date'] === date('Y-m-d') ? 'TODAY' : 'Tomorrow' ?></span>
        </div>
        <div style="font-size:0.85rem;color:var(--gray-dk);margin-top:0.2rem">
          Guest: <?= sanitize($c['guest_name']) ?> | <?= $c['num_guests'] ?> guest(s)
        </div>
        <div style="font-size:0.82rem;color:var(--gray-mid)">Check-in: <?= sanitize($c['checkin_date']) ?></div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</main>
</div>
<?php include BASE_PATH.'/includes/footer.php'; ?>
