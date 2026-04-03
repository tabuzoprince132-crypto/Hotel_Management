<?php
session_start();
include("../connect.php"); // Make sure we can query the room capacity

header('Content-Type: application/json'); // Return JSON for AJAX

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $room_id = $_POST['room_id'];
    $user_id = $_SESSION['user_id'] ?? 0;

    // Get user inputs safely
    $guest_name = $_POST['guest_name'] ?? 'Guest';
    $checkin = $_POST['checkin'] ?? '';
    $checkout = $_POST['checkout'] ?? '';
    $guests = (int) ($_POST['guests'] ?? 1);

    // Get room capacity from database
    $stmt = $conn->prepare("SELECT rt.Capacity FROM rooms r JOIN roomtypes rt ON r.RoomTypeID = rt.RoomTypeID WHERE r.RoomID = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Room not found!'
        ]);
        exit;
    }

    $room = $result->fetch_assoc();
    $capacity = (int) $room['Capacity'];

    // Check if guests exceed capacity
    if ($guests > $capacity) {
        echo json_encode([
            'status' => 'error',
            'message' => "Number of guests cannot exceed room capacity ($capacity)!"
        ]);
        exit;
    }

    // Check if room is already in cart
    $exists = false;
    foreach ($_SESSION['cart'] as $item) {
        if ($item['room_id'] == $room_id) {
            $exists = true;
            break;
        }
    }

    if (!$exists) {
        $_SESSION['cart'][] = [
            'user_id' => $user_id,
            'room_id' => $room_id,
            'guest_name' => $guest_name,
            'checkin' => $checkin,
            'checkout' => $checkout,
            'guests' => $guests
        ];

        echo json_encode([
            'status' => 'success',
            'message' => 'Room added to cart!'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Room already in cart!'
        ]);
    }

} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request!'
    ]);
}
?>