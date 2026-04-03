<?php
session_start();
include("../connect.php");

if(!isset($_SESSION['cart']) || !is_array($_SESSION['cart']) || empty($_SESSION['cart'])){
    echo "Cart is empty";
    exit;
}

$user_id = $_SESSION['user_id'] ?? 0;

foreach($_SESSION['cart'] as $index => $item){
    if($item['user_id'] != $user_id) continue;

    $room_id = $item['room_id'];
    $sql = "SELECT r.RoomID, r.RoomNumber, rt.TypeName, rt.Description, rt.RatePerNight, rt.Capacity
            FROM rooms r
            JOIN roomtypes rt ON r.RoomTypeID = rt.RoomTypeID
            WHERE r.RoomID='$room_id'";
    $res = $conn->query($sql);
    $room = $res->fetch_assoc();

    echo "<div style='border:1px solid #ccc; padding:10px; margin:5px 0'>";
    echo "<strong>Room:</strong> ".$room['RoomNumber']." - ".$room['TypeName']."<br>";
    echo "<strong>Guest Name:</strong> ".$item['guest_name']."<br>";
    echo "<strong>Check-In:</strong> ".$item['checkin']."<br>";
    echo "<strong>Check-Out:</strong> ".$item['checkout']."<br>";
    echo "<strong>Guests:</strong> ".$item['guests']."<br>";
    echo "<strong>Price:</strong> ₱".number_format($room['RatePerNight'],2)."<br>";
    echo "<button onclick=\"openEditForm($index, '{$item['guest_name']}', '{$item['checkin']}', '{$item['checkout']}', {$item['guests']})\">Edit</button>";
    echo "<button class='removeBtn' data-index='$index'>Remove</button>";
    echo "</div>";
}
?>