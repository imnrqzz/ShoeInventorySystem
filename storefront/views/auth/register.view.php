<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register — SOLEHAUS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="public/css/style.css">
<link rel="stylesheet" href="public/css/auth.css">
<script src="public/js/main.js"></script>
</head>
<body>

<nav>
  <a href="index.php" class="nav-logo">SOLE<span>HAUS</span></a>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="index.php#products">Collection</a></li>
    <li><a href="index.php?page=customizer">Customizer</a></li>
  </ul>
  <div class="nav-actions">
    <!-- Theme Toggle Button -->
    <button id="themeToggleBtn" type="button" class="theme-toggle-btn" title="Toggle Light/Dark Theme" style="background:transparent; border:1px solid var(--border); border-radius:50%; width:36px; height:36px; cursor:pointer; color:var(--white); display:flex; align-items:center; justify-content:center; transition:background 0.2s; margin-right: 0.5rem;">
      <i class="fa-solid fa-moon"></i>
    </button>
    <a href="index.php?page=login" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.08em; color:var(--muted);">Log In</a>
  </div>
</nav>

<section class="auth-section">
  <div class="auth-card">
    <div class="section-eyebrow">Join SoleHaus</div>
    <div class="section-title auth-title">CREATE ACCOUNT</div>

    <?php if (!empty($error)): ?>
      <div class="auth-alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="auth-social">
      <a href="index.php?page=auth-google" class="btn-social btn-google">
        <i class="fa-brands fa-google"></i> Sign up with Google
      </a>
      <a href="index.php?page=auth-facebook" class="btn-social btn-facebook">
        <i class="fa-brands fa-facebook-f"></i> Sign up with Facebook
      </a>
    </div>

    <div class="auth-divider"><span>or</span></div>

    <form method="POST" action="index.php?page=register" class="auth-form">
      <?= csrf_field() ?>
      <label>Full Name
        <input type="text" name="name" required autocomplete="name">
      </label>
      <label>Address
        <input type="text" name="address" required placeholder="Enter your shipping address">
      </label>
      <label>Email
        <input type="email" name="email" required autocomplete="email">
      </label>
      <label>Password
        <input type="password" name="password" required minlength="8" autocomplete="new-password">
      </label>
      <label>Confirm Password
        <input type="password" name="confirm_password" required minlength="8" autocomplete="new-password">
      </label>
      <button type="submit" class="btn-primary auth-submit">Create Account <i class="fa-solid fa-arrow-right"></i></button>
    </form>

    <p class="auth-footer-text">
      Already have an account? <a href="index.php?page=login">Log in</a>
    </p>
  </div>
</section>

<footer>
  <div class="logo">SOLE<span>HAUS</span></div>
  <div>© <?= date('Y') ?> SoleHaus. All rights reserved.</div>
</footer>

</body>
</html>