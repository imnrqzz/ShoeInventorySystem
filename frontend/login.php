<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Login</title>
</head>
<body>
    <div class="login_form">
        <form method="POST" action="process_login.php">
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

    
</body>
</html>