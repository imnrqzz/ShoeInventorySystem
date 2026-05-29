<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/register_style.css" />
    <title>Register</title>
</head>
<body>
    <section class="container">
        <header> Registration Form </header>
        <form method ="POST" action="process_register.php" class="form">
            <div class="input-box">
                <label>Username</label>
                <input type="text" placeholder="Enter Full Name" name="username" required />
            </div>

            <div class="input-box">
                <label>Password</label>
                <input type="password" placeholder="Enter Password" name="password" required />
            </div>

            <div class="column">
                <div class="input-box">
                    <label>Year & Section</label>
                    <input type="text" placeholder="Enter Year & Section" name="course" required />
                </div>

                <div class="input-box">
                    <label>Repeat Password </label>
                    <input type="password" placeholder="Repeat Password" name="repeatpassword" required />
                </div>
            </div>
            <div class="gender-box">
                <h3>Gender</h3>
                <div class="gender-option">
                    <div class="gender">
                        <input type="radio" id="check-male" name="gender" checked />
                        <label for="check-male">Male</label>
                    </div>
                    <div class="gender">
                        <input type="radio" id="check-female" name="gender"/>
                        <label for="check-female">Female</label>
                    </div>
                    <div class="gender">
                        <input type="radio" id="check-other" name="gender"/>
                        <label for="check-other">Prefer not to say</label>
                    </div>
                </div>
            </div>
            <div class="input-box address">
                <label>Address</label>
                <input type="text" placeholder="Enter street address" name="address1" required />
                <input type="text" placeholder="Enter street address line 2" name="address2" required />
                <div class="column">
                    <div class="select-box">
                        <select>
                            <option hidden>Country</option>
                            <option>America</option>
                            <option>Japan</option>
                            <option>Philippines</option>
                            <option>Korea</option>
                        </select>
                    </div>
                    <input type="text" placeholder="Enter your City" name="city" required />
                </div>
                <div class="column">
                    <input type="text" placeholder="Enter your Region" name="region" required />
                    <input type="number" placeholder="Enter Postal Code" name="postal" required />
                </div>
            </div>
            <button>Submit</button>
            <p class="sign_up">Already have an account? <a href="login.php"> Log In </a></p>
        </form>
      </section>
    </body>
</html>
