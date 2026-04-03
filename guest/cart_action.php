<?php
session_start();
include("../connect.php");

header('Content-Type: application/json');

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_POST['action'] ?? '';
$index = isset($_POST['index']) ? (int)$_POST['index'] : -1;

if (!isset($_SESSION['cart'][$index])) {
    echo json_encode(["status"=>"error","message"=>"Invalid item"]);
    exit;
}

// REMOVE
if ($action == "remove") {

    array_splice($_SESSION['cart'], $index, 1);
    $_SESSION['cart'] = array_values($_SESSION['cart']);

    echo json_encode(["status"=>"success","message"=>"Item removed"]);
    exit;
}

// EDIT
if ($action == "edit") {

    $guest_name = $_POST['guest_name'];
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $guests = (int)$_POST['guests'];

    $room_id = $_SESSION['cart'][$index]['room_id'];

    $res = $conn->query("SELECT rt.Capacity 
                         FROM rooms r 
                         JOIN roomtypes rt ON r.RoomTypeID = rt.RoomTypeID 
                         WHERE r.RoomID='$room_id'");
    $room = $res->fetch_assoc();

    if ($guests > $room['Capacity']) {
        echo json_encode([
            "status"=>"error",
            "message"=>"Max guests is ".$room['Capacity']
        ]);
        exit;
    }

    $_SESSION['cart'][$index]['guest_name'] = $guest_name;
    $_SESSION['cart'][$index]['checkin'] = $checkin;
    $_SESSION['cart'][$index]['checkout'] = $checkout;
    $_SESSION['cart'][$index]['guests'] = $guests;

    echo json_encode(["status"=>"success","message"=>"Updated"]);
}
?>