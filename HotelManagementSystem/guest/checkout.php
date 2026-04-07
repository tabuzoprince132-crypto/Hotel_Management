<?php
session_start();
include("../connect.php");

if (!isset($_SESSION['account_ID'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<script src='guest_script.js'></script>
          <script>
            noCart();
            window.location.href='guest_dashboard.php';
          </script>";
    exit;
}

$user_id     = $_SESSION['account_ID'];
$grand_total = 0;
$cart_items  = []; // enriched for display

foreach ($_SESSION['cart'] as $item) {
    if ((int)$item['account_ID'] !== (int)$user_id) continue;

    $room_id = (int)$item['room_id'];

    $stmt = $conn->prepare(
        "SELECT r.RoomNumber, rt.TypeName, rt.RatePerNight, rt.Capacity
         FROM rooms r
         JOIN roomtypes rt ON r.RoomTypeID = rt.RoomTypeID
         WHERE r.RoomID = ?"
    );
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $room = $stmt->get_result()->fetch_assoc();
    if (!$room) continue;

    $d1     = new DateTime($item['checkin']);
    $d2     = new DateTime($item['checkout']);
    $nights = (int)$d1->diff($d2)->days;
    if ($nights <= 0) $nights = 1;

    $subtotal     = $nights * $room['RatePerNight'];
    $grand_total += $subtotal;

    $cart_items[] = [
        'room_number' => $room['RoomNumber'],
        'type_name'   => $room['TypeName'],
        'rate'        => $room['RatePerNight'],
        'guest_name'  => $item['guest_name'],
        'checkin'     => $item['checkin'],
        'checkout'    => $item['checkout'],
        'nights'      => $nights,
        'guests'      => $item['guests'],
        'notes'       => $item['notes'] ?? '',
        'subtotal'    => $subtotal,
    ];
}

if (empty($cart_items)) {
    header("Location: guest_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout &mdash; Review Your Reservation</title>
</head>
<body>

<nav>
    <a class="brand" href="guest_dashboard.php">&#9670; Grand Hotel</a>
    <a class="back-link" href="guest_dashboard.php">&larr; Back to Dashboard</a>
</nav>
<hr>
<div class="page-wrap">
    <h1>Review Your Reservation</h1>
    <p class="subtitle">Please review the details below before confirming your booking.</p>
<hr>
    <?php foreach ($cart_items as $r): ?>
    <div class="res-card">
        <div class="res-card-header">
            <div>
                <div class="room-title">Room <?= htmlspecialchars($r['room_number']) ?></div>
                <div class="room-type"><?= htmlspecialchars($r['type_name']) ?></div>
            </div>
            <div class="subtotal-badge">₱<?= number_format($r['subtotal'], 2) ?></div>
        </div>
        <div class="res-grid">
            <div class="res-row">
                <span class="lbl">Guest Name</span>
                <span class="val"><?= htmlspecialchars($r['guest_name']) ?></span>
            </div>
            <div class="res-row">
                <span class="lbl">No. of Guests</span>
                <span class="val"><?= (int)$r['guests'] ?></span>
            </div>
            <div class="res-row">
                <span class="lbl">Check-in</span>
                <span class="val"><?= date('F j, Y', strtotime($r['checkin'])) ?></span>
            </div>
            <div class="res-row">
                <span class="lbl">Check-out</span>
                <span class="val"><?= date('F j, Y', strtotime($r['checkout'])) ?></span>
            </div>
            <div class="res-row">
                <span class="lbl">Nights</span>
                <span class="val"><?= $r['nights'] ?> night<?= $r['nights'] > 1 ? 's' : '' ?></span>
            </div>
            <div class="res-row">
                <span class="lbl">Rate per Night</span>
                <span class="val">₱<?= number_format($r['rate'], 2) ?></span>
            </div>
        </div>
        <?php if (!empty($r['notes'])): ?>
            <div class="notes-row">Notes: <?= htmlspecialchars($r['notes']) ?></div>
        <?php endif; ?>
    </div>
    <br>
    <?php endforeach; ?>

    <div class="total-box">
        <div class="total-label">Total Amount Due</div>
        <div class="total-amount">₱<?= number_format($grand_total, 2) ?></div>
    </div>

    <div class="confirm-wrap">
        <a class="btn-back-link" href="guest_dashboard.php"><button>← Edit Cart</button></a>
        <form method="POST" action="process_reservation.php" style="display:inline;">
            <button type="submit" class="btn-confirm">Confirm Reservation</button>
        </form>
    </div>
    <p class="policy-note">By confirming, you agree to our cancellation policy. Reservations are subject to room availability.</p>
</div>
</body>
</html>