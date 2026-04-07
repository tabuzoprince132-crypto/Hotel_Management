<?php
/**
 * add_to_cart.php
 * AJAX endpoint — adds a room to the session cart.
 * Returns JSON: { status: "success"|"error", message: "..." }
 */
session_start();
include("../connect.php");

header('Content-Type: application/json');

// ── Guard: POST only ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request!']);
    exit;
}

// ── Collect inputs ──────────────────────────────────────────────────────────
$room_id   = (int)($_POST['room_id']   ?? 0);
$checkin   = trim($_POST['checkin']    ?? '');
$checkout  = trim($_POST['checkout']   ?? '');
$guests    = (int)($_POST['guests']    ?? 0);
$notes     = trim($_POST['notes']      ?? '');

// Auto-fill guest name from session
$guest_name = trim(($_SESSION['firstName'] ?? '') . ' ' . ($_SESSION['lastName'] ?? ''));
if ($guest_name === '') $guest_name = $_SESSION['username'] ?? 'Guest';

$user_id  = $_SESSION['account_ID']  ?? 0;
$username = $_SESSION['username'] ?? 'guest';

// ── Validate inputs BEFORE touching the database ───────────────────────────
if ($room_id <= 0 || $checkin === '' || $checkout === '' || $guests <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit;
}

$today = date('Y-m-d');
if ($checkin < $today) {
    echo json_encode(['status' => 'error', 'message' => 'Check-in date cannot be in the past.']);
    exit;
}
if ($checkout <= $checkin) {
    echo json_encode(['status' => 'error', 'message' => 'Check-out must be after check-in.']);
    exit;
}

// ── Fetch room capacity ─────────────────────────────────────────────────────
$stmt = $conn->prepare(
    "SELECT rt.Capacity
     FROM rooms r
     JOIN roomtypes rt ON r.RoomTypeID = rt.RoomTypeID
     WHERE r.RoomID = ?"
);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Room not found.']);
    exit;
}

$room     = $result->fetch_assoc();
$capacity = (int)$room['Capacity'];

if ($guests > $capacity) {
    echo json_encode([
        'status'  => 'error',
        'message' => "Number of guests ($guests) exceeds room capacity ($capacity)."
    ]);
    exit;
}

// ── Prevent double booking for the same room/date range ──────────────────────
$conflict = $conn->prepare(
    "SELECT 1 FROM reservations
     WHERE RoomID = ?
       AND checkin_date < ?
       AND checkout_date > ?
       AND status <> 'Cancelled'
     LIMIT 1"
);
$conflict->bind_param("iss", $room_id, $checkout, $checkin);
$conflict->execute();
$conflictResult = $conflict->get_result();
if ($conflictResult && $conflictResult->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Room is already reserved for the selected dates.']);
    exit;
}

// ── Initialize cart ─────────────────────────────────────────────────────────
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ── Duplicate check (same room already in cart) ─────────────────────────────
foreach ($_SESSION['cart'] as $item) {
    if ((int)$item['room_id'] === $room_id) {
        echo json_encode(['status' => 'error', 'message' => 'This room is already in your cart.']);
        exit;
    }
}

// ── Add to cart ─────────────────────────────────────────────────────────────
$_SESSION['cart'][] = [
    'account_ID'    => $user_id,
    'username'   => $username,
    'room_id'    => $room_id,
    'guest_name' => $guest_name,
    'checkin'    => $checkin,
    'checkout'   => $checkout,
    'guests'     => $guests,
    'notes'      => $notes,
];

echo json_encode(['status' => 'success', 'message' => 'Room added to cart!']);
exit;