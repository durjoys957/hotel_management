<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('receptionist');
require_once __DIR__ . '/../models/ReceptionistModel.php';
$model = new ReceptionistModel();
$rooms = $model->getAllRooms();
$pageTitle='Room Status Board'; $activeRole='receptionist'; $activePage='rooms';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Room Status Board</h1></div>
    <button onclick="refreshStatus()" class="btn btn-outline">🔄 Refresh</button>
  </div>

  <!-- Legend -->
  <div style="display:flex;gap:0.8rem;flex-wrap:wrap;margin-bottom:1.5rem">
    <?php foreach (['available'=>'✅','occupied'=>'🔵','dirty'=>'🟡','maintenance'=>'🔴','blocked'=>'⛔'] as $s=>$icon): ?>
    <span class="badge badge-<?= $s ?>"><?= $icon ?> <?= ucfirst($s) ?></span>
    <?php endforeach; ?>
  </div>

  <div id="roomBoard" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:1rem">
    <?php foreach ($rooms as $r): ?>
    <div class="room-tile" data-room-id="<?= $r['id'] ?>" style="background:var(--white);border-radius:10px;box-shadow:var(--shadow);padding:1rem;text-align:center;border-top:4px solid <?= match($r['status']){'available'=>'var(--success)','occupied'=>'var(--info)','dirty'=>'var(--warning)','maintenance'=>'var(--danger)',default=>'var(--gray-mid)'} ?>">
      <div style="font-size:1.6rem"><?= match($r['status']){'available'=>'✅','occupied'=>'🔵','dirty'=>'🟡','maintenance'=>'🔴',default=>'⛔'} ?></div>
      <div style="font-weight:700;font-size:1.1rem;margin:0.3rem 0">Room <?= sanitize($r['room_number']) ?></div>
      <div style="font-size:0.78rem;color:var(--gray-mid)"><?= sanitize($r['type_name']) ?><br>Floor <?= $r['floor'] ?></div>
      <span class="badge badge-<?= $r['status'] ?>" style="margin-top:0.4rem;display:inline-block"><?= $r['status'] ?></span>
    </div>
    <?php endforeach; ?>
  </div>
</main>
</div>
<script>
const BASE_URL = '<?= BASE_URL ?>';
function refreshStatus() {
  ajaxGet(BASE_URL + '/api/room_status.php', function(err, data) {
    if (err || !data.rooms) return;
    data.rooms.forEach(r => {
      const el = document.querySelector('[data-room-id="'+r.id+'"]');
      if (el) el.querySelector('.badge').textContent = r.status;
    });
  });
}
setInterval(refreshStatus, 30000);
</script>
<?php include BASE_PATH.'/includes/footer.php'; ?>
