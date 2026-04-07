<?php
/**
 * confirmation.php
 * Shows the reservation(s) that were just confirmed.
 * Uses $_SESSION['last_reservation_ids'] set by process_reservation.php.
 */
session_start();
include("../connect.php");

if (!isset($_SESSION['account_ID'])) {
    header("Location: ../login.php");
    exit;
}

// Redirect if accessed directly without completing checkout
if (empty($_SESSION['last_reservation_ids'])) {
    header("Location: guest_dashboard.php");
    exit;
}

$ids = $_SESSION['last_reservation_ids'];
unset($_SESSION['last_reservation_ids']); // consume so it can't be re-shown on refresh

// Fetch only the just-created reservations
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types        = str_repeat('i', count($ids));
$stmt         = $conn->prepare("SELECT * FROM reservations WHERE id IN ($placeholders) ORDER BY id ASC");
$stmt->bind_param($types, ...$ids);
$stmt->execute();
$result = $stmt->get_result();

$grand_total = 0;
$rows        = [];
while ($r = $result->fetch_assoc()) {
    $grand_total += $r['amount'];
    $rows[]       = $r;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed!</title>
</head>
<body>

<nav>
    <a class="brand" href="guest_dashboard.php">&#9670; Grand Hotel</a>
    <a class="nav-link" href="guest_dashboard.php">Back to Dashboard</a>
</nav>
<hr>
<div class="page-wrap">

    <div class="success-banner">
        <div>
            <h1>Booking Confirmed!</h1>
            <p>Your reservation has been submitted. Please wait for staff approval.</p>
        </div>
    </div>
<hr>
    <?php foreach ($rows as $r): ?>
    <div class="res-card">
        <div class="res-card-header">
            <div>
                <div class="room-title">Room <?= htmlspecialchars($r['room_number']) ?></div>
                <div style="margin-top:6px;">
                    <span class="status-badge"><?= htmlspecialchars($r['status']) ?></span>
                </div>
            </div>
            <span class="ref-number">Ref #<?= str_pad($r['id'], 6, '0', STR_PAD_LEFT) ?></span>
        </div>
        <div class="res-grid">
            <div class="res-row">
                <span class="lbl">Guest Name</span>
                <span class="val"><?= htmlspecialchars($r['guest_name']) ?></span>
            </div>
            <div class="res-row">
                <span class="lbl">No. of Guests</span>
                <span class="val"><?= (int)$r['num_guests'] ?></span>
            </div>
            <div class="res-row">
                <span class="lbl">Check-in</span>
                <span class="val"><?= date('F j, Y', strtotime($r['checkin_date'])) ?></span>
            </div>
            <div class="res-row">
                <span class="lbl">Check-out</span>
                <span class="val"><?= date('F j, Y', strtotime($r['checkout_date'])) ?></span>
            </div>
            <div class="res-row">
                <span class="lbl">Nights</span>
                <span class="val"><?= (int)$r['days'] ?></span>
            </div>
            <div class="res-row">
                <span class="lbl">Amount</span>
                <span class="val">₱<?= number_format($r['amount'], 2) ?></span>
            </div>
        </div>
        <?php if (!empty($r['notes'])): ?>
            <div class="notes-row"><?= htmlspecialchars($r['notes']) ?></div>
        <?php endif; ?>
        <div class="subtotal-row">
            Subtotal: <strong>₱<?= number_format($r['amount'], 2) ?></strong>
            &nbsp;|&nbsp;
            <a href="receipt.php?id=<?= $r['id'] ?>" target="_blank" style="color: var(--gold); font-weight:700; text-decoration:none;">🖨 Print Receipt</a>
        </div>
    </div>
    <br>
    <?php endforeach; ?>

    <?php if (count($rows) > 1): ?>
    <div class="total-box">
        <div class="total-label">Total Amount Due</div>
        <div class="total-amount">₱<?= number_format($grand_total, 2) ?></div>
    </div>
    <?php endif; ?>

    <div class="actions">
        <a class="btn btn-gold" href="guest_dashboard.php">Book Another Room</a>
        <a class="btn btn-outline" href="receipt.php?id=<?= $rows[0]['id'] ?>" target="_blank">Print Receipt</a>
    </div>

</div>
</body>
</html>