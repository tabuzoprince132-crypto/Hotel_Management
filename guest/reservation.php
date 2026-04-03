<?php
session_start();
include("..\connect.php");

$total = 0;

foreach ($_SESSION['cart'] as $item) {

    $room_id = $item['room_id'];

    $sql = "SELECT RoomNumber, TypeName, RatePerNight FROM rooms WHERE RoomID='$room_id'";
    $res = $conn->query($sql);
    $room = $res->fetch_assoc();

    $checkin = new DateTime($item['checkin']);
    $checkout = new DateTime($item['checkout']);
    $days = $checkin->diff($checkout)->days;

    if ($days <= 0) $days = 1;

    $amount = $room['RatePerNight'] * $days;
    $total += $amount;

    echo "<div style='border:1px solid #ccc; padding:10px; margin:5px'>";
    echo "Room: " . $room['RoomNumber'] . " - " . $room['TypeName'] . "<br>";
    echo "Guest: " . $item['guest_name'] . "<br>";
    echo "Check-in: " . $item['checkin'] . "<br>";
    echo "Check-out: " . $item['checkout'] . "<br>";
    echo "Days: " . $days . "<br>";
    echo "Guests: " . $item['guests'] . "<br>";
    echo "Price per night: ₱" . number_format($room['RatePerNight'],2) . "<br>";
    echo "Subtotal: ₱" . number_format($amount,2);
    echo "</div>";
}

echo "<h3>Total Amount: ₱" . number_format($total,2) . "</h3>";
?>

<form method="POST" action="confirm_reservation.php">
    <button type="submit">Confirm Reservation</button>
</form>

<script src="guest_script.js"></script>