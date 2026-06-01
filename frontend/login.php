<?php require_once __DIR__ . '/../backend/db.php'; ?>
<?php $registered = isset($_GET['registered']) && $_GET['registered'] === '1'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/login_style.css">
    <title>Login</title>
</head>
<body>
    <?php if ($registered): ?>
        <div class="modal-backdrop" id="successModal">
            <div class="modal success-modal">
                <div class="modal-header">
                    <h4>Registration Successful</h4>
                    <button type="button" class="modal-close" id="closeModal">×</button>
                </div>
                <div class="modal-body">
                    <p>Your account has been created successfully. Please log in to continue.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="button-secondary" id="modalOk">OK</button>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="login_form">
        <form method="POST" action="../backend/process_login.php">
            <h3> Log In </h3>
            <div class="input_box">
                <label for="username">Username : </label> 
                <input type="text" name="username" required>
            </div>     

            <br>

            <div class="input_box">
                <div class="password_title">
                    <label for="password">  Password : </label>
                    <input type="password" name="password" required>
                </div>
            </div>

            <button type="submit" value="Submit"> Login </button>
            <p class="sign_up">Don't have an account? <a href="register.php"> Sign up </a></p>
        </form>
    </div>

    <?php if ($registered): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('successModal');
            var close = document.getElementById('closeModal');
            var ok = document.getElementById('modalOk');
            function hideModal() {
                if (modal) {
                    modal.style.display = 'none';
                }
            }
            if (close) close.addEventListener('click', hideModal);
            if (ok) ok.addEventListener('click', hideModal);
            setTimeout(hideModal, 4000);
        });
    </script>
    <?php endif; ?>
</body>
</html>