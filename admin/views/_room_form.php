<?php // requires $types and optionally $edit ?>
<div class="form-row">
  <div class="form-group"><label class="form-label">Room Number *</label>
    <input type="text" name="room_number" class="form-control" value="<?= sanitize($edit['room_number'] ?? '') ?>" required placeholder="e.g. 101">
  </div>
  <div class="form-group"><label class="form-label">Floor</label>
    <input type="number" name="floor" class="form-control" value="<?= $edit['floor'] ?? 1 ?>" min="1">
  </div>
</div>
<div class="form-row">
  <div class="form-group"><label class="form-label">Room Type *</label>
    <select name="room_type_id" class="form-control" required>
      <option value="">Select...</option>
      <?php foreach ($types as $t): ?>
      <option value="<?= $t['id'] ?>" <?= (isset($edit) && $edit['room_type_id']==$t['id'])?'selected':'' ?>><?= sanitize($t['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-group"><label class="form-label">Status</label>
    <select name="status" class="form-control">
      <?php foreach (['available','blocked','maintenance'] as $s): ?>
      <option value="<?= $s ?>" <?= (isset($edit) && $edit['status']===$s)?'selected':'' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
</div>
<div class="form-group"><label class="form-label">Notes</label>
  <textarea name="notes" class="form-control" rows="2"><?= sanitize($edit['notes'] ?? '') ?></textarea>
</div>
<button type="submit" class="btn btn-primary"><?= isset($edit) ? 'Save Changes' : 'Add Room' ?></button>
<?php if (isset($edit)): ?><a href="<?= BASE_URL ?>/admin/views/rooms.php" class="btn btn-secondary" style="margin-left:0.5rem">Cancel</a><?php endif; ?>
