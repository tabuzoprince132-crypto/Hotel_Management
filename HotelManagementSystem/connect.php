<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_db";

//Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

//Check connection
if($conn->connect_error)
    {
        die("Error: ".$conn->connect_error);
    }
    else{
        #echo "Successfully Connected";
    }

?>