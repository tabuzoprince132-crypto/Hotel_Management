<?php
/**
 * cart_action.php
 * AJAX endpoint — handles remove and edit actions on cart items.
 * Returns JSON: { status: "success"|"error", message: "..." }
 */
session_start();
include("../connect.php");

header('Content-Type: application/json');

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_POST['action'] ?? '';
$index  = isset($_POST['index']) ? (int)$_POST['index'] : -1;

// Validate index
if ($index < 0 || !array_key_exists($index, $_SESSION['cart'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid cart item.']);
    exit;
}

// ── REMOVE ──────────────────────────────────────────────────────────────────
if ($action === 'remove') {
    array_splice($_SESSION['cart'], $index, 1);
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    echo json_encode(['status' => 'success', 'message' => 'Item removed from cart.']);
    exit;
}

// ── EDIT ─────────────────────────────────────────────────────────────────────
if ($action === 'edit') {
    $checkin  = trim($_POST['checkin']  ?? '');
    $checkout = trim($_POST['checkout'] ?? '');
    $guests   = (int)($_POST['guests']  ?? 0);

    // Validate
    $today = date('Y-m-d');
    if ($checkin < $today) {
        echo json_encode(['status' => 'error', 'message' => 'Check-in date cannot be in the past.']);
        exit;
    }
    if ($checkout <= $checkin) {
        echo json_encode(['status' => 'error', 'message' => 'Check-out must be after check-in.']);
        exit;
    }
    if ($guests <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Number of guests must be at least 1.']);
        exit;
    }

    // Check capacity
    $room_id = (int)$_SESSION['cart'][$index]['room_id'];
    $stmt = $conn->prepare(
        "SELECT rt.Capacity
         FROM rooms r
         JOIN roomtypes rt ON r.RoomTypeID = rt.RoomTypeID
         WHERE r.RoomID = ?"
    );
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $room = $stmt->get_result()->fetch_assoc();

    if ($guests > (int)$room['Capacity']) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Maximum guests for this room is ' . $room['Capacity'] . '.'
        ]);
        exit;
    }

    // Apply changes
    $_SESSION['cart'][$index]['checkin']  = $checkin;
    $_SESSION['cart'][$index]['checkout'] = $checkout;
    $_SESSION['cart'][$index]['guests']   = $guests;

    echo json_encode(['status' => 'success', 'message' => 'Cart item updated.']);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Unknown action.']);
exit;