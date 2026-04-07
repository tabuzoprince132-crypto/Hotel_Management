<?php
/**
 * receipt.php
 * Printable receipt for a single reservation.
 */
session_start();
include("../connect.php");

if (!isset($_SESSION['account_ID'])) {
    header("Location: ../login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("Invalid receipt.");
}

$username = $_SESSION['username'] ?? $_SESSION['email'] ?? 'guest';

// Only allow the owner to view their receipt
$stmt = $conn->prepare("SELECT * FROM reservations WHERE id = ? AND username = ?");
$stmt->bind_param("is", $id, $username);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("Receipt not found or access denied.");
}
$r = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt #<?= str_pad($r['id'], 6, '0', STR_PAD_LEFT) ?></title>
</head>
<body>
<div class="receipt">
    <div class="receipt-header">
        <div class="hotel-name">&#9670; Grand Hotel</div>
        <div class="hotel-sub">Official Receipt</div>
        <div class="ref">Ref No. #<?= str_pad($r['id'], 6, '0', STR_PAD_LEFT) ?></div>
    </div>

    <div class="receipt-body">
        <div class="receipt-row">
            <span class="lbl">Guest Name</span>
            <span class="val"><?= htmlspecialchars($r['guest_name']) ?></span>
        </div>
        <div class="receipt-row">
            <span class="lbl">Room Number</span>
            <span class="val">Room <?= htmlspecialchars($r['room_number']) ?></span>
        </div>
        <div class="receipt-row">
            <span class="lbl">Check-in</span>
            <span class="val"><?= date('F j, Y', strtotime($r['checkin_date'])) ?></span>
        </div>
        <div class="receipt-row">
            <span class="lbl">Check-out</span>
            <span class="val"><?= date('F j, Y', strtotime($r['checkout_date'])) ?></span>
        </div>
        <div class="receipt-row">
            <span class="lbl">Duration</span>
            <span class="val"><?= (int)$r['days'] ?> night<?= $r['days'] > 1 ? 's' : '' ?></span>
        </div>
        <div class="receipt-row">
            <span class="lbl">No. of Guests</span>
            <span class="val"><?= (int)$r['num_guests'] ?></span>
        </div>
        <div class="receipt-row">
            <span class="lbl">Rate per Night</span>
            <span class="val">₱<?= number_format($r['amount'] / max($r['days'], 1), 2) ?></span>
        </div>
        <?php if (!empty($r['notes'])): ?>
        <div class="receipt-row">
            <span class="lbl">Notes</span>
            <span class="val" style="max-width:60%; text-align:right;"><?= htmlspecialchars($r['notes']) ?></span>
        </div>
        <?php endif; ?>
        <div class="receipt-row">
            <span class="lbl">Status</span>
            <span class="val"><span class="status-badge"><?= htmlspecialchars($r['status']) ?></span></span>
        </div>

        <hr class="receipt-divider">

        <div class="total-row">
            <span class="total-lbl">Total Due</span>
            <span class="total-val">₱<?= number_format($r['amount'], 2) ?></span>
        </div>
    </div>

    <div class="receipt-footer">
        Thank you for choosing Grand Hotel.<br>
        This is an official receipt. Please present upon check-in.<br>
        Email: hotel@grandhotel.com &nbsp;|&nbsp; Phone: +635 555 555
    </div>

    <button class="btn-print" onclick="window.print()">Print Receipt</button>
</div>
</body>
</html>