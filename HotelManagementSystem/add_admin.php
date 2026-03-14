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
            window.location.href='register_admin.php';
            </script>";

        }
        else {
            $conn ->query("INSERT INTO account (firstName, lastName, email, password, address, contact, role) VALUES
            ('$fname', '$lname', '$email', '$password', '$address', '$contact', '$role')");

            echo "<script>
            alert('Account successfully registered');
            window.location.href='admin_dashboard.php';
            </script>";
                
        }
    }

    if(isset($_POST['cancel']))
    {
        header("Location: admin_dashboard.php");
    }


?>