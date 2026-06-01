<?php
// frontend/register.php
require_once __DIR__ . '/../backend/db.php';
$error = $_GET['err'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ShoeInventory</title>
    <link rel="stylesheet" href="../css/register_styles.css">
</head>
<body>
    <div class="register-card">
        <div class="register-brand">
            <div class="brand-icon">
                <img src="../images/shoes.png" alt="ShoeInventory Logo">
            </div>
            <h1>ShoeInventory</h1>
            <p>Create a new account</p>
        </div>

        <?php if ($error === 'exists'): ?>
        <div class="alert-error">Username or email already taken. Please try another.</div>
        <?php elseif ($error === 'invalid_input'): ?>
        <div class="alert-error">All fields are required and email must be valid.</div>
        <?php elseif ($error): ?>
        <div class="alert-error">An error occurred. Please try again.</div>
        <?php endif; ?>

        <form method="POST" action="../backend/process_register.php">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Enter your name" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Choose a username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a password" required>
            </div>
            <div class="form-group">
                <label for="repeatpassword">Confirm Password</label>
                <input type="password" id="repeatpassword" name="repeatpassword" placeholder="Repeat your password" required>
            </div>
            <button type="submit" class="btn-register">Create Account</button>
        </form>

        <p class="register-footer">Already have an account? <a href="login.php">Sign In</a></p>
    </div>
</body>
</html>
