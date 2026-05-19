<?php
require_once __DIR__ . '/includes/init.php';
if (isLoggedIn()) redirectToDashboard();

$error = $_GET['error'] ?? '';
$mode  = $_GET['mode'] ?? 'login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LuxStay — Welcome</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
</head>
<body>
<div class="login-page">
  <div class="login-card">
    <div class="login-brand"><span>Lux</span><span style="color:var(--navy)">Stay</span></div>
    <p class="login-subtitle">Hotel Management System</p>

    <?php if ($error): ?>
    <div class="flash flash-error" style="margin-bottom:1rem;border-radius:8px;">
      <?= sanitize($error) ?>
    </div>
    <?php endif; ?>

    <div class="login-tabs">
      <button class="login-tab <?= $mode==='login'?'active':'' ?>" onclick="showTab('login')">Sign In</button>
      <button class="login-tab <?= $mode==='register'?'active':'' ?>" onclick="showTab('register')">Register</button>
    </div>

    <!-- LOGIN FORM -->
    <div id="loginForm" class="<?= $mode==='register'?'register-form':'' ?>">
      <form action="<?= BASE_URL ?>/controllers/auth_controller.php" method="POST">
        <input type="hidden" name="action" value="login">
        <div class="form-group" style="text-align:left">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
        </div>
        <div class="form-group" style="text-align:left">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;margin-top:0.5rem">Sign In</button>
      </form>
      <p style="margin-top:1rem;font-size:0.82rem;color:var(--gray-mid)">
        Demo accounts: admin@hotel.com / receptionist@hotel.com / housekeeping@hotel.com / guest@hotel.com — password: <b>password</b>
      </p>
    </div>

    <!-- REGISTER FORM -->
    <div id="registerForm" class="register-form <?= $mode==='register'?'show':'' ?>">
      <form action="<?= BASE_URL ?>/controllers/auth_controller.php" method="POST">
        <input type="hidden" name="action" value="register">
        <div class="form-row">
          <div class="form-group" style="text-align:left">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" placeholder="John Doe" required>
          </div>
          <div class="form-group" style="text-align:left">
            <label class="form-label">Phone</label>
            <input type="tel" name="phone" class="form-control" placeholder="+8801XXXXXXXXX">
          </div>
        </div>
        <div class="form-group" style="text-align:left">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
        </div>
        <div class="form-row">
          <div class="form-group" style="text-align:left">
            <label class="form-label">Nationality</label>
            <input type="text" name="nationality" class="form-control" placeholder="Bangladeshi">
          </div>
          <div class="form-group" style="text-align:left">
            <label class="form-label">ID Number</label>
            <input type="text" name="id_number" class="form-control" placeholder="NID / Passport">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group" style="text-align:left">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required minlength="6">
          </div>
          <div class="form-group" style="text-align:left">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
          </div>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;margin-top:0.5rem">Create Account</button>
      </form>
    </div>
  </div>
</div>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script>
function showTab(tab) {
  document.getElementById('loginForm').classList.toggle('register-form', tab === 'register');
  document.getElementById('loginForm').classList.toggle('show', tab !== 'register');
  document.getElementById('registerForm').classList.toggle('show', tab === 'register');
  document.querySelectorAll('.login-tab').forEach((b, i) => b.classList.toggle('active', (i===0 && tab==='login') || (i===1 && tab==='register')));
}
// Fix initial state for login tab
if ('<?= $mode ?>' === 'login') {
  document.getElementById('loginForm').classList.add('show');
  document.getElementById('loginForm').classList.remove('register-form');
}
</script>
</body>
</html>
