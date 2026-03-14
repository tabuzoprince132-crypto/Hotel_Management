<?php
    session_start();
    
    include('connect.php');

    $errors =[
        'login' => $_SESSION['login_error'] ?? '',
        'register' => $_SESSION['register_error'] ?? ''
    ];

    $activeform = $_SESSION['active_form'] ?? 'login';

    unset($_SESSION['login_error']);
    unset($_SESSION['register_error']);

    function showError($errors){
        return !empty($errors) ? "<p class = 'error-message'> $errors</p>" : '';
    }

    function isActive($formname, $activeform){
        return $formname === $activeform ? 'active' : '';
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">

        <h1>Welcome to Hotel ni RaymondFacundo</h1>

        <div class="form-box <?= isActive('login', $activeform); ?>" id="loginform">
            <form action="acc_login.php" method="post">

                <h2>Login</h2>
                <?php echo showError($errors['login']); ?>
                <label for="email">Email :</label>
                
                <input type="email" name="email" id="email" placeholder="Email" required>
                <br>
                <label for="password">Password: </label>
                <br>
                <input type="password" name="password" id="password" placeholder="Password" required>

                <br>
                <button type="submit" class="login" name="login">Login</button>
                <br>

                <p>Don't have an Account? <a href="#" onclick="showForm('registerform')">Register</a></p>
            </form>
        </div>

        <div class="form-box <?php isActive('register', $activeform); ?>" id="registerform">
            <form action="acc_login.php" method="post">
                <h2>Register</h2>
                <?php echo showError($errors['register']); ?>
                <label for="firstName">First Name :</label>
                <br>
                <input type="text" name="firstName" id="firstName" placeholder="First Name" required>
                <br>

                <label for="lastName">Last Name :</label>
                <br>
                <input type="text" name="lastName" id="lastName" placeholder="Last Name">
                <br>

                <label for="email">Email :</label>
                <br>
                <input type="email" name="email" id="email" placeholder="Email" required>
                <br>

                <label for="password">Password: </label>
                <br>
                <input type="password" name="password" id="password" placeholder="Password" required>
                <br>

                <label for="address">Address :</label>
                <br>
                <input type="text" name="address" id="address" placeholder="Address" required>
                <br>

                <label for="contact">Contact :</label>
                <br>
                <input type="number" name="contact" id="contact" placeholder="Contact" required>
                <br>

                <label for="role">Role :</label>
                <select name="role" id="role" required>
                    <option value="">--Select Role--</option>
                    <option value="guest">GUEST</option>
                </select>

                <br>
                <button type="submit" class="register" name="register">Register</button>
                <br>

                <p>Already have an Account? <a href="#" onclick="showForm('loginform')">Login</a></p>
            </form>
        </div>
    </div>
    
    <script src="script.js"></script>

</body>
</html>