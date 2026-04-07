<?php
/**
 * process_reservation.php
 * Processes the confirmed reservation: inserts records into DB, clears cart.
 * Only accepts POST from checkout.php.
 */
session_start();
include("../connect.php");

// Guard: must be a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: guest_dashboard.php");
    exit;
}

// Guard: must be logged in
if (!isset($_SESSION['account_ID'])) {
    header("Location: ../login.php");
    exit;
}

// Guard: cart must not be empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: guest_dashboard.php");
    exit;
}

$user_id  = $_SESSION['account_ID'];
$username = $_SESSION['username'] ?? 'guest';

$inserted_ids = [];

foreach ($_SESSION['cart'] as $item) {
    // Only process items belonging to this user
    if ((int)$item['account_ID'] !== (int)$user_id) continue;

    $room_id    = (int)$item['room_id'];
    $guest_name = $item['guest_name'];
    $checkin    = $item['checkin'];
    $checkout   = $item['checkout'];
    $guests     = (int)$item['guests'];
    $notes      = $item['notes'] ?? '';

    // Get room details (number + rate)
    $stmt = $conn->prepare(
        "SELECT r.RoomNumber, rt.RatePerNight
         FROM rooms r
         JOIN roomtypes rt ON r.RoomTypeID = rt.RoomTypeID
         WHERE r.RoomID = ?"
    );
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $room = $stmt->get_result()->fetch_assoc();

    if (!$room) continue; // skip if room was deleted

    $room_number = $room['RoomNumber'];
    $rate        = $room['RatePerNight'];

    // Calculate nights
    $d1     = new DateTime($checkin);
    $d2     = new DateTime($checkout);
    $nights = (int)$d1->diff($d2)->days;
    if ($nights <= 0) $nights = 1;

    $amount = $nights * $rate;

    // Prevent double booking if the room is already reserved for this date range.
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
        echo "<script>alert('One or more rooms are no longer available for the selected dates. Please update your cart.'); window.location.href='checkout.php';</script>";
        exit;
    }

    // Insert reservation (prepared statement — no SQL injection)
    $ins = $conn->prepare(
        "INSERT INTO reservations
            (RoomID, guest_name, room_number, checkin_date, checkout_date, days, amount, num_guests, status, notes, username)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?, ?)"
    );
    $ins->bind_param(
        "issssidiss",
        $room_id, $guest_name, $room_number,
        $checkin, $checkout,
        $nights, $amount, $guests,
        $notes, $username
    );
    $ins->execute();

    $inserted_ids[] = $conn->insert_id;
}

// Clear the session cart
unset($_SESSION['cart']);

// Pass reservation IDs to confirmation page via session
$_SESSION['last_reservation_ids'] = $inserted_ids;

header("Location: confirmation.php");
exit;