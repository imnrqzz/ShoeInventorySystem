<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verify Code — SOLEHAUS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="public/css/style.css">
<link rel="stylesheet" href="public/css/auth.css">
<script src="public/js/main.js"></script>
</head>
<body>

<?php require __DIR__ . '/../partials/nav.php'; ?>

<section class="auth-section">
  <div class="auth-card">
    <div class="section-eyebrow">Confirm Email</div>
    <div class="section-title auth-title" style="font-size: 2.2rem;">ENTER CODE</div>

    <?php if (!empty($info)): ?>
      <div class="auth-alert" style="background: rgba(124, 255, 107, 0.08); border-color: #7CFF6B; color: #7CFF6B;">
        <?= $info ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="auth-alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <p style="color: var(--muted); font-size: 0.85rem; line-height: 1.6; margin-bottom: 1.5rem;">
      A 6-digit confirmation code has been sent to <strong><?= htmlspecialchars($email) ?></strong>. Please check your inbox (and spam folder) to verify your account.
    </p>

    <form method="POST" action="index.php?page=verify-code" class="auth-form">
      <?= csrf_field() ?>
      <label>Verification Code
        <input type="text" name="code" required placeholder="123456" maxlength="6" style="text-align: center; font-size: 1.5rem; letter-spacing: 4px; font-weight: 600;">
      </label>
      <button type="submit" class="btn-primary auth-submit">Verify Account <i class="fa-solid fa-check"></i></button>
    </form>

    <form method="POST" action="index.php?page=resend-code" style="margin-top: 1.5rem; text-align: center;">
      <?= csrf_field() ?>
      <button type="submit" style="background: none; border: none; color: #7CFF6B; text-decoration: underline; cursor: pointer; font-family: inherit; font-size: 0.85rem; transition: opacity 0.2s;">
        Didn't receive the email? Resend Code
      </button>
    </form>
  </div>
</section>

<footer>
  <div class="logo">SOLE<span>HAUS</span></div>
  <div>© <?= date('Y') ?> SoleHaus. All rights reserved.</div>
</footer>

</body>
</html>
