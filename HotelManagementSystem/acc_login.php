<?php

session_start();
//connect to database
include('connect.php');

//check if the register button is clicked
if(isset($_POST['register']))
    {

        $fname=$_POST['firstName'];
        $lname=$_POST['lastName'];
        $email=$_POST['email'];
        $password=$_POST['password'];
        $address=$_POST['address'];
        $contact=$_POST['contact'];
        $role=$_POST['role'];

        $checkEmail = $conn->query("SELECT email FROM account WHERE email='$email'");
        if($checkEmail->num_rows > 0){
            echo "<script>
            alert('Email has already registered');
            window.location.href='index.php';
            </script>";

        }
        else {
            $conn ->query("INSERT INTO account (firstName, lastName, email, password, address, contact, role) VALUES
            ('$fname', '$lname', '$email', '$password', '$address', '$contact', '$role')");

            echo "<script>
            alert('Account successfully registered');
            window.location.href='index.php';
            </script>";
                
        }
    }
    
    if (isset($_POST['login'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM account WHERE email = '$email'");

    if ($result->num_rows > 0) {

        $user = $result->fetch_assoc();

        if ($password === $user['password']) {

            $_SESSION['firstName'] = $user['firstName'];

            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
                exit();
            } else {
                header("Location: guest_dashboard.php");
                exit();
            }
        }
    }

    // only runs if login failed
    $_SESSION['login_error'] = 'Incorrect email or password';
    $_SESSION['active_form'] = 'login';
    header("Location: index.php");
    exit();
}


?>