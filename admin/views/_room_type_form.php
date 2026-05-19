<?php
// Shared partial — requires $edit (or null) and $allAmenities to be set
$currentAmenities = ($edit && $edit['amenities']) ? (is_array($edit['amenities']) ? $edit['amenities'] : json_decode($edit['amenities'],true)) : [];
?>
<div class="form-row">
  <div class="form-group"><label class="form-label">Name *</label>
    <input type="text" name="name" class="form-control" value="<?= sanitize($edit['name'] ?? '') ?>" required placeholder="e.g. Standard, Deluxe, Suite">
  </div>
  <div class="form-group"><label class="form-label">Price per Night (৳) *</label>
    <input type="number" name="price_per_night" class="form-control" value="<?= $edit['price_per_night'] ?? '' ?>" required min="1" step="0.01">
  </div>
</div>
<div class="form-group"><label class="form-label">Description</label>
  <textarea name="description" class="form-control" rows="3"><?= sanitize($edit['description'] ?? '') ?></textarea>
</div>
<div class="form-row">
  <div class="form-group"><label class="form-label">Max Capacity</label>
    <input type="number" name="max_capacity" class="form-control" value="<?= $edit['max_capacity'] ?? 2 ?>" min="1" max="10">
  </div>
  <div class="form-group"><label class="form-label">Thumbnail Image</label>
    <input type="file" name="thumbnail" class="form-control" accept="image/*">
    <?php if (!empty($edit['thumbnail_path'])): ?>
    <span class="form-hint">Current image exists. Upload to replace.</span>
    <?php endif; ?>
  </div>
</div>
<div class="form-group">
  <label class="form-label">Amenities</label>
  <div style="display:flex;flex-wrap:wrap;gap:0.5rem">
    <?php foreach ($allAmenities as $a): ?>
    <label style="display:flex;align-items:center;gap:0.3rem;font-size:0.88rem;cursor:pointer">
      <input type="checkbox" name="amenities[]" value="<?= $a ?>" <?= in_array($a,$currentAmenities)?'checked':'' ?>>
      <?= $a ?>
    </label>
    <?php endforeach; ?>
  </div>
</div>
<button type="submit" class="btn btn-primary"><?= isset($edit) ? 'Save Changes' : 'Create Room Type' ?></button>
<?php if (isset($edit)): ?><a href="<?= BASE_URL ?>/admin/views/room_types.php" class="btn btn-secondary" style="margin-left:0.5rem">Cancel</a><?php endif; ?>
