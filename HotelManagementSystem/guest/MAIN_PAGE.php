<?php
include("../connect.php");

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HOTEL</title>

<style>
    body {
        margin: 0;
    }

    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: black;
        padding: 15px 40px;
    }

    .book-btn {
        position: relative;
        padding: 15px 80px;
        font-size: 18px;
    }

    .rooms {
        display: flex;
        gap: 40px;
    }

    .card {
        width: 300px;
        background: white;
    }

    .card-img {
        height: 150px;
        background: #ddd;
    }

    .card-body {
        padding: 15px;
    }

    .view-all {
        margin-top: 20px;
        font-weight: bold;
    }

</style>
</head>

<body>

<div class="navbar">
    <div class="logo">
        <div class="logo-circle"></div>
        <h2>Brand</h2>
    </div>

    <div class="profile">
        <button><p>PROFILE</p></button>
    </div>
</div>
<hr>
<div class="hero">
    <div>
        <p>Welcome to</p>
        <h1>Hotel PAKUNDO</h1>
    </div>
    <a href="guest_dashboard.php"><button class="book-btn">BOOK</button></a>
</div>
<hr>

<div class="section">
    <h2>Rooms</h2>
    <div class="line"></div>

    <div class="rooms">
        <button>
            <div class="card">
                <div class="card-img"></div>
                <div class="card-body">
                    <div class="card-title">SINGLE</div>
                    <small>1 King size bed. 1-2 pax</small>
                </div>
            </div>
        </button>
        
        <button>
            <div class="card">
                <div class="card-img"></div>
                <div class="card-body">
                    <div class="card-title">Deluxe</div>
                    <small>1 King size bed. 1-2 pax</small>
                </div>
            </div>
        </button>
    </div>

    <div class="view-all">
        <a href="view_all.php"><button>VIEW ALL</button></a>
    </div>

    <!--Footer-->   
        <hr>
        <h3>Contact Information</h3>
        <p>Email: website@mail.com</p>
        <p>Phone: +635 555 555</p>
        <p>Address: Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
        <p>&copy 2026 Brand. All rights reserved.</p>
</div>


</body>
</html>