<?php
/**
 * cart.php
 * AJAX partial — renders cart items for the current user.
 * Called via GET from guest_script.js → loadCart()
 */
session_start();
include("../connect.php");

$user_id = $_SESSION['account_ID'] ?? 0;

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo '<p>Your cart is empty.</p>';
    exit;
}

$grand_total = 0;
$has_items   = false;

foreach ($_SESSION['cart'] as $index => $item) {
    // Only show items belonging to this user
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
    $res  = $stmt->get_result();
    $room = $res->fetch_assoc();

    if (!$room) continue;

    // Calculate nights and amount
    $d1     = new DateTime($item['checkin']);
    $d2     = new DateTime($item['checkout']);
    $nights = (int)$d1->diff($d2)->days;
    if ($nights <= 0) $nights = 1;

    $subtotal     = $nights * $room['RatePerNight'];
    $grand_total += $subtotal;
    $has_items    = true;

    // Escape values for data attributes
    $esc_name     = htmlspecialchars($item['guest_name'], ENT_QUOTES);
    $esc_checkin  = htmlspecialchars($item['checkin'],     ENT_QUOTES);
    $esc_checkout = htmlspecialchars($item['checkout'],    ENT_QUOTES);
?>
<div class="cart-item">
    <div class="cart-item-header">
        <span class="cart-room-name">
            Room <?= htmlspecialchars($room['RoomNumber']) ?> &mdash; <?= htmlspecialchars($room['TypeName']) ?>
        </span>
        <span class="cart-subtotal">₱<?= number_format($subtotal, 2) ?></span>
    </div>
    <div class="cart-item-details">
        <div><span class="label">Guest:</span> <?= htmlspecialchars($item['guest_name']) ?></div>
        <div><span class="label">Check-in:</span> <?= htmlspecialchars($item['checkin']) ?></div>
        <div><span class="label">Check-out:</span> <?= htmlspecialchars($item['checkout']) ?></div>
        <div><span class="label">Nights:</span> <?= $nights ?></div>
        <div><span class="label">Guests:</span> <?= (int)$item['guests'] ?></div>
        <div><span class="label">Rate:</span> ₱<?= number_format($room['RatePerNight'], 2) ?>/night</div>
    </div>
    <div class="cart-item-actions">
        <button class="btn-edit editBtn"
            data-index="<?= $index ?>"
            data-name="<?= $esc_name ?>"
            data-checkin="<?= $esc_checkin ?>"
            data-checkout="<?= $esc_checkout ?>"
            data-guests="<?= (int)$item['guests'] ?>">
            Edit
        </button>
        <button class="btn-remove removeBtn" data-index="<?= $index ?>">Remove</button>
    </div>
</div>
<br>
<?php } ?>

<?php if ($has_items): ?>
<div class="cart-total">
    <strong>Grand Total: ₱<?= number_format($grand_total, 2) ?></strong>
</div>
<?php else: ?>
<p>Your cart is empty.</p>
<?php endif; ?>