<?php
session_start();
include("..\connect.php");

$user_id = $_SESSION['user_id'] ?? 0;

foreach ($_SESSION['cart'] as $item) {

    if ($item['user_id'] != $user_id) {
        continue;
    }

    // insert reservation
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "Cart is empty!";
    exit;
}

foreach ($_SESSION['cart'] as $item) {

    $room_id = $item['room_id'];
    $guest_name = $item['guest_name'];
    $checkin = $item['checkin'];
    $checkout = $item['checkout'];
    $guests = $item['guests'];

    // get room details
    $sql = "SELECT RoomNumber, RatePerNight FROM rooms WHERE RoomID='$room_id'";
    $res = $conn->query($sql);
    $room = $res->fetch_assoc();

    $room_number = $room['RoomNumber'];
    $price = $room['RatePerNight'];

    // calculate days
    $checkin_date = new DateTime($checkin);
    $checkout_date = new DateTime($checkout);
    $interval = $checkin_date->diff($checkout_date);
    $days = $interval->days;

    if ($days <= 0) {
        $days = 1; // minimum 1 day
    }

    // calculate amount
    $amount = $price * $days;

    // insert to database
    $sql_insert = "INSERT INTO reservations 
        (RoomID, guest_name, room_number, checkin_date, checkout_date, days, amount, num_guests)
        VALUES 
        ('$room_id', '$guest_name', '$room_number', '$checkin', '$checkout', '$days', '$amount', '$guests')";

    $conn->query($sql_insert);
}

// clear cart
unset($_SESSION['cart']);

echo "Reservation confirmed successfully!";
?>
<script src="guest_script.js"></script>