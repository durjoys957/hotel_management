<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('guest');
require_once __DIR__ . '/../models/GuestModel.php';

$model      = new GuestModel();
$id         = (int)($_GET['id'] ?? 0);
$checkin    = $_GET['checkin']  ?? '';
$checkout   = $_GET['checkout'] ?? '';
$guests     = (int)($_GET['guests'] ?? 1);
$roomType   = $model->getRoomTypeById($id);

if (!$roomType) {
    flashMessage('error', 'Room type not found');
    header('Location: ' . BASE_URL . '/guest/views/search.php'); exit;
}

$ratings  = $model->getRoomTypeRatings($id);
$reviews  = $model->getReviewsForRoomType($id);
$seasonal = $checkin && $checkout ? $model->getSeasonalPrice($id, $checkin, $checkout) : null;
$nights   = ($checkin && $checkout) ? (int)((strtotime($checkout) - strtotime($checkin)) / 86400) : 0;
$price    = $seasonal ? $seasonal['price_per_night'] : $roomType['price_per_night'];

$pageTitle  = sanitize($roomType['name']);
$activeRole = 'guest';
$activePage = 'search';
include BASE_PATH . '/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__ . '/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div>
      <h1 class="page-title"><?= sanitize($roomType['name']) ?></h1>
      <p class="page-subtitle"><?= sanitize(substr($roomType['description'] ?? '', 0, 80)) ?></p>
    </div>
    <?php if ($checkin && $checkout): ?>
    <a href="<?= BASE_URL ?>/guest/views/booking_form.php?room_type_id=<?= $id ?>&checkin=<?= urlencode($checkin) ?>&checkout=<?= urlencode($checkout) ?>&guests=<?= $guests ?>" class="btn btn-primary">Book This Room</a>
    <?php endif; ?>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start">
    <div>
      <?php if (!empty($roomType['thumbnail_path'])): ?>
      <img src="<?= BASE_URL ?>/<?= sanitize($roomType['thumbnail_path']) ?>" style="width:100%;border-radius:10px;object-fit:cover;max-height:280px">
      <?php else: ?>
      <div style="width:100%;height:220px;background:var(--gray-lt);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:4rem">🏨</div>
      <?php endif; ?>
    </div>

    <div class="card">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
        <div><div class="stat-label">Price per Night</div>
          <?php if ($seasonal): ?>
          <div style="text-decoration:line-through;color:var(--gray-mid);font-size:0.85rem">৳<?= number_format($roomType['price_per_night'], 2) ?></div>
          <div class="stat-value text-gold">৳<?= number_format($price, 2) ?></div>
          <span class="badge badge-confirmed">🎉 <?= sanitize($seasonal['label']) ?></span>
          <?php else: ?>
          <div class="stat-value text-gold">৳<?= number_format($price, 2) ?></div>
          <?php endif; ?>
        </div>
        <div><div class="stat-label">Max Capacity</div><div class="stat-value"><?= $roomType['max_capacity'] ?> guests</div></div>
      </div>

      <?php if ($nights > 0): ?>
      <div class="card" style="background:var(--navy);color:var(--white);margin-bottom:1rem">
        <div style="display:flex;justify-content:space-between">
          <span><?= $nights ?> night<?= $nights > 1 ? 's' : '' ?> &times; ৳<?= number_format($price, 2) ?></span>
          <strong style="color:var(--gold)">৳<?= number_format($price * $nights, 2) ?></strong>
        </div>
      </div>
      <?php endif; ?>

      <div class="card-title" style="margin-top:0.5rem">Amenities</div>
      <?php $amen = $roomType['amenities'] ?? []; ?>
      <?php if ($amen): ?>
      <div style="display:flex;flex-wrap:wrap;gap:0.4rem">
        <?php foreach ($amen as $a): ?>
        <span class="badge badge-confirmed"><?= sanitize($a) ?></span>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <p class="text-muted">No amenities listed</p>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($ratings['cnt'] > 0): ?>
  <div class="card">
    <div class="card-title">Guest Ratings</div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;text-align:center">
      <?php foreach (['ov' => 'Overall', 'cl' => 'Cleanliness', 'sv' => 'Service'] as $k => $label): ?>
      <div>
        <div style="font-size:2rem;font-weight:700;color:var(--gold)"><?= number_format($ratings[$k], 1) ?></div>
        <div style="color:var(--gray-mid)"><?= $label ?></div>
        <div class="stars"><?= str_repeat('★', round($ratings[$k])) . str_repeat('☆', 5 - round($ratings[$k])) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <p class="text-muted" style="text-align:center;margin-top:0.5rem">Based on <?= $ratings['cnt'] ?> review<?= $ratings['cnt'] > 1 ? 's' : '' ?></p>
  </div>
  <?php endif; ?>

  <?php if ($reviews): ?>
  <div class="card">
    <div class="card-title">Recent Reviews</div>
    <?php foreach ($reviews as $rv): ?>
    <div style="border-bottom:1px solid var(--gray-lt);padding-bottom:1rem;margin-bottom:1rem">
      <div style="display:flex;justify-content:space-between">
        <strong><?= sanitize($rv['guest_name']) ?></strong>
        <small class="text-muted"><?= date('M j, Y', strtotime($rv['created_at'])) ?></small>
      </div>
      <div style="display:flex;gap:1rem;font-size:0.85rem;margin:0.3rem 0">
        <span>Overall <span class="stars"><?= str_repeat('★', $rv['overall_rating']) ?></span></span>
        <span>Cleanliness <span class="stars"><?= str_repeat('★', $rv['cleanliness_rating']) ?></span></span>
        <span>Service <span class="stars"><?= str_repeat('★', $rv['service_rating']) ?></span></span>
      </div>
      <?php if ($rv['review_text']): ?>
      <p style="font-size:0.88rem;color:var(--gray-dk)">"<?= sanitize($rv['review_text']) ?>"</p>
      <?php endif; ?>
      <?php if ($rv['admin_reply']): ?>
      <div style="background:var(--gray-lt);border-radius:6px;padding:0.5rem 0.8rem;font-size:0.83rem;margin-top:0.4rem">
        <strong style="color:var(--gold)">🏨 Hotel Reply:</strong> <?= sanitize($rv['admin_reply']) ?>
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div style="text-align:center;margin-top:1rem">
    <a href="<?= BASE_URL ?>/guest/views/search.php<?= $checkin ? "?checkin=$checkin&checkout=$checkout&guests=$guests" : '' ?>" class="btn btn-secondary">&larr; Back to Search</a>
    <?php if ($checkin && $checkout): ?>
    <a href="<?= BASE_URL ?>/guest/views/booking_form.php?room_type_id=<?= $id ?>&checkin=<?= urlencode($checkin) ?>&checkout=<?= urlencode($checkout) ?>&guests=<?= $guests ?>" class="btn btn-primary" style="margin-left:0.8rem">Book This Room</a>
    <?php endif; ?>
  </div>
</main>
</div>
<?php include BASE_PATH . '/includes/footer.php'; ?>
