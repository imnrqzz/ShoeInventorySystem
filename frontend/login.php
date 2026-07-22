<?php
// frontend/login.php

require_once __DIR__ . '/../backend/bootstrap.php';

$registered = isset($_GET['registered']) && $_GET['registered'] === '1';
$error = $_GET['err'] ?? '';
$usernameValue = trim($_GET['username'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ShoeInventory</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <?php if ($registered): ?>
    <div class="alert-error" style="margin-bottom:16px;">Registration is disabled. Please contact the administrator.</div>
    <?php endif; ?>

    <div class="login-card">
        <div class="login-brand">
            <div class="brand-icon">
                <img src="../images/shoes.png" alt="ShoeInventory Logo">
            </div>
            <h1>ShoeInventory</h1>
            <p>Sign in to your account</p>
        </div>

        <?php if ($error === 'invalid' || $error === '1'): ?>
        <div class="alert-error">Invalid username or password. Please try again.</div>
        <?php elseif ($error === 'disabled'): ?>
        <div class="alert-error">This account has been disabled. Please contact the administrator.</div>
        <?php endif; ?>

        <form method="POST" action="../backend/handlers/process_login.php" id="loginForm" data-validate data-form-name="login" novalidate>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required
                       minlength="3" autocomplete="username" value="<?= safe($usernameValue) ?>">
                <span class="field-error" id="usernameError"></span>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required
                       minlength="6" autocomplete="current-password">
                <span class="field-error" id="passwordError"></span>
            </div>
            <?= csrf_field() ?>
                <button type="submit" class="btn-login">Sign In</button>
        </form>

        <p class="login-footer">Need an account? Please contact the admin.</p>
    </div>

    <?php if ($registered): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('successModal');
            var ok = document.getElementById('modalOk');
            function hide() { if (modal) modal.style.display = 'none'; }
            if (ok) ok.addEventListener('click', hide);
            setTimeout(hide, 4000);
        });
    </script>
    <?php endif; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('loginForm');
        var username = document.getElementById('username');
        var password = document.getElementById('password');

        if (!form || !username || !password) return;

        var hasError = window.location.search.indexOf('err=') !== -1;
        if (hasError) {
            username.focus();
        }

        form.addEventListener('submit', function() {
            form.dataset.submitting = 'true';
        });
    });
    </script>
</body>
</html>
