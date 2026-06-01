<?php require_once __DIR__ . '/../backend/db.php'; ?>
<?php $registered = isset($_GET['registered']) && $_GET['registered'] === '1'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/register_styles.css" />
    <title>Register</title>
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
    <section class="container">
        <header> Registration Form </header>
        <?php
        // Check if the error parameter exists in the URL
        if (isset($_GET['err'])) {
            $error = $_GET['err'];
            if ($error === 'exists') {
                echo '<div style="color: red; padding: 10px; border: 1px solid red; margin-bottom: 10px;">
                        Error: The username or email is already registered. Please try another.
                    </div>';
            } elseif ($error === 'invalid_input') {
                echo '<div style="color: red; padding: 10px; border: 1px solid red; margin-bottom: 10px;">
                        Error: All fields are required and email must be valid.
                    </div>';
            }
        }
        ?>
        <form method ="POST" action="../backend/process_register.php" class="form">
            <div class="input-box">
                <label>Name</label>
                <input type="text" placeholder="Enter Name" name="name" autocomplete="name" required />
            </div>

            <div class="input-box">
                <label>Username</label>
                <input type="text" placeholder="Enter Username" name="username" autocomplete="username" required />
            </div>

            <div class="input-box">
                <label>Email</label>
                <input type="email" placeholder="Enter Email" name="email" autocomplete="email" required />
            </div>

            <div class="input-box">
                <label>Password</label>
                <input type="password" placeholder="Enter Password" name="password" autocomplete="new-password" required />
            </div>
            
            <div class="input-box">
                <label>Repeat Password </label>
                <input type="password" placeholder="Repeat Password" name="repeatpassword" autocomplete="new-password" required />
            </div>


            

                
            </div>
            <button>Submit</button>
            <p class="sign_up">Already have an account? <a href="login.php"> Log In </a></p>
        </form>
      </section>

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