<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('housekeeping');
require_once __DIR__ . '/../models/HousekeepingModel.php';
$model  = new HousekeepingModel();
$rooms  = $model->getAllRooms();
$pageTitle='Room Status'; $activeRole='housekeeping'; $activePage='rooms';
include BASE_PATH.'/includes/header.php';
?>
<div class="page-wrapper">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">Room Status Board</h1></div>
    <button onclick="refreshRooms()" class="btn btn-outline">🔄 Refresh</button>
  </div>

  <div style="display:flex;gap:0.8rem;flex-wrap:wrap;margin-bottom:1.5rem">
    <?php foreach (['available'=>['✅','var(--success)'],'dirty'=>['🟡','var(--warning)'],'occupied'=>['🔵','var(--info)'],'maintenance'=>['🔴','var(--danger)'],'blocked'=>['⛔','var(--gray-mid)']] as $s=>[$icon,$clr]): ?>
    <span style="display:flex;align-items:center;gap:0.4rem;font-size:0.88rem">
      <span style="width:12px;height:12px;background:<?= $clr ?>;border-radius:50%;display:inline-block"></span>
      <?= $icon ?> <?= ucfirst($s) ?>:
      <strong><?= count(array_filter($rooms,fn($r)=>$r['status']===$s)) ?></strong>
    </span>
    <?php endforeach; ?>
  </div>

  <div id="roomGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:1rem">
    <?php foreach ($rooms as $r): ?>
    <div class="room-tile" data-id="<?= $r['id'] ?>" style="background:var(--white);border-radius:10px;box-shadow:var(--shadow);padding:1rem;text-align:center;border-top:4px solid <?= match($r['status']){'available'=>'var(--success)','occupied'=>'#1565C0','dirty'=>'var(--warning)','maintenance'=>'var(--danger)',default=>'var(--gray-mid)'} ?>">
      <div style="font-size:1.8rem"><?= match($r['status']){'available'=>'✅','occupied'=>'🔵','dirty'=>'🟡','maintenance'=>'🔴',default=>'⛔'} ?></div>
      <div style="font-weight:700;margin:0.3rem 0">Room <?= sanitize($r['room_number']) ?></div>
      <div style="font-size:0.78rem;color:var(--gray-mid)"><?= sanitize($r['type_name']) ?><br>Floor <?= $r['floor'] ?></div>
      <span class="badge badge-<?= $r['status'] ?>" style="margin:0.4rem 0;display:inline-block"><?= $r['status'] ?></span>
      <?php if (in_array($r['status'],['dirty','maintenance'])): ?>
      <form method="POST" action="<?= BASE_URL ?>/housekeeping/controllers/housekeeping_controller.php" style="margin-top:0.5rem">
        <input type="hidden" name="action" value="mark_clean">
        <input type="hidden" name="room_id" value="<?= $r['id'] ?>">
        <button type="submit" class="btn btn-sm btn-success" style="width:100%" data-confirm="Mark Room <?= sanitize($r['room_number']) ?> as clean/available?">Mark Clean</button>
      </form>
      <?php endif; ?>
      <a href="<?= BASE_URL ?>/housekeeping/views/room_history.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline" style="width:100%;margin-top:0.3rem">History</a>
    </div>
    <?php endforeach; ?>
  </div>
</main>
</div>
<script>
const BASE_URL = '<?= BASE_URL ?>';
function refreshRooms() {
  ajaxGet(BASE_URL + '/api/room_status.php', function(err, data) {
    if (err || !data.rooms) return;
    data.rooms.forEach(r => {
      const el = document.querySelector('[data-id="'+r.id+'"]');
      if (el) {
        const badge = el.querySelector('.badge');
        if (badge) badge.textContent = r.status;
      }
    });
  });
}
setInterval(refreshRooms, 15000);
</script>
<?php include BASE_PATH.'/includes/footer.php'; ?>
