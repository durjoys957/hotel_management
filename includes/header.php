<?php
// includes/header.php — call with $pageTitle and $activeRole set
$flash = getFlash();
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= sanitize($pageTitle ?? 'Hotel Management') ?> — LuxStay</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
</head>
<body class="role-<?= sanitize($activeRole ?? 'guest') ?>">
<nav class="topnav">
  <div class="topnav-brand">
    <a href="<?= BASE_URL ?>"><span class="brand-lux">Lux</span><span class="brand-stay">Stay</span></a>
  </div>
  <div class="topnav-links">
    <?php if (isLoggedIn()): ?>
      <span class="nav-user">👤 <?= sanitize($user['name']) ?></span>
      <a href="<?= BASE_URL ?>/<?= $activeRole ?? 'guest' ?>/views/profile.php">Profile</a>
      <a href="<?= BASE_URL ?>/controllers/auth_controller.php?action=logout" class="btn-logout">Logout</a>
    <?php else: ?>
      <a href="<?= BASE_URL ?>/index.php">Login</a>
    <?php endif; ?>
  </div>
</nav>
<?php if ($flash): ?>
<div class="flash flash-<?= sanitize($flash['type']) ?>">
  <?= sanitize($flash['message']) ?>
  <button onclick="this.parentElement.remove()" class="flash-close">×</button>
</div>
<?php endif; ?>
