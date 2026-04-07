<?php
/**
 * view_all.php
 * Browse and filter all available rooms. Guests can add to cart from here.
 */
session_start();
include("../connect.php");

if (!isset($_SESSION['account_ID'])) {
    header("Location: ../login.php");
    exit;
}

// Filter inputs
$search    = trim($_GET['search']    ?? '');
$room_type = trim($_GET['room_type'] ?? '');
$guests    = (int)($_GET['guests']   ?? 0);
$sort      = $_GET['sort']           ?? '';

// Base query
$sql = "SELECT r.RoomID, r.RoomNumber, rt.TypeName, rt.Description, rt.RatePerNight, rt.Capacity
        FROM rooms r
        JOIN roomtypes rt ON r.RoomTypeID = rt.RoomTypeID
        WHERE r.Status = 'available'";

if ($search !== '') {
    $safe = $conn->real_escape_string($search);
    $sql .= " AND (rt.TypeName LIKE '%$safe%' OR rt.Description LIKE '%$safe%')";
}
if ($room_type !== '') {
    $safe = $conn->real_escape_string($room_type);
    $sql .= " AND rt.TypeName = '$safe'";
}
if ($guests > 0) {
    $sql .= " AND rt.Capacity >= $guests";
}

$sql .= match($sort) {
    'low'  => " ORDER BY rt.RatePerNight ASC",
    'high' => " ORDER BY rt.RatePerNight DESC",
    default => " ORDER BY r.RoomNumber ASC",
};

$available_rooms = $conn->query($sql);

$today    = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Rooms &mdash; Grand Hotel</title>
</head>
<body>

<nav>
    <a class="brand" href="guest_dashboard.php">&#9670; Grand Hotel</a>
    <div class="nav-right">
        <a class="btn-nav" href="guest_dashboard.php">&larr; Dashboard</a>
        <a class="btn-nav" href="../logout.php">Logout</a>
    </div>
</nav>
<hr>
<button class="btn-nav solid" onclick="openCart()">Cart</button>
<hr>
<div class="page-header">
    <h1>All Available Rooms</h1>
    <p>Filter and find the perfect room for your stay</p>
</div>

<!-- FILTERS -->
<form method="GET" id="filterForm">
    <div class="filters-wrap">
        <div class="filter-group">
            <label>Search</label>
            <input type="text" name="search" placeholder="e.g. Deluxe, Suite..."
                   value="<?= htmlspecialchars($search) ?>" onchange="this.form.submit()">
        </div>
        <div class="filter-group">
            <label>Room Type</label>
            <select name="room_type" onchange="this.form.submit()">
                <option value="">All Types</option>
                <option value="Single"  <?= $room_type === 'Single'  ? 'selected' : '' ?>>Single</option>
                <option value="Double"  <?= $room_type === 'Double'  ? 'selected' : '' ?>>Double</option>
                <option value="Deluxe"  <?= $room_type === 'Deluxe'  ? 'selected' : '' ?>>Deluxe</option>
                <option value="Suite"   <?= $room_type === 'Suite'   ? 'selected' : '' ?>>Suite</option>
                <option value="Family"  <?= $room_type === 'Family'  ? 'selected' : '' ?>>Family</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Min. Guests</label>
            <input type="number" name="guests" min="1" max="6"
                   value="<?= $guests > 0 ? $guests : '' ?>" placeholder="Any"
                   onchange="this.form.submit()" onkeydown="return false;">
        </div>
        <div class="filter-group">
            <label>Sort by Price</label>
            <select name="sort" onchange="this.form.submit()">
                <option value=""    <?= $sort === ''    ? 'selected' : '' ?>>Default</option>
                <option value="low" <?= $sort === 'low' ? 'selected' : '' ?>>Lowest First</option>
                <option value="high"<?= $sort === 'high'? 'selected' : '' ?>>Highest First</option>
            </select>
        </div>
        <span class="filter-count"><?= $available_rooms->num_rows ?> room(s) found</span>
    </div>
</form>
<hr>
<br>    
<div class="content">
    <?php if ($available_rooms->num_rows > 0): ?>
        <div class="rooms-grid">
            <?php foreach ($available_rooms as $room): ?>
            <div class="room-card">
                <div class="room-card-body">
                    <span class="type-badge"><?= htmlspecialchars($room['TypeName']) ?></span>
                    <div class="room-number">Room <?= htmlspecialchars($room['RoomNumber']) ?></div>
                    <div class="room-desc"><?= htmlspecialchars($room['Description']) ?></div>
                    <div class="room-meta">
                        <div class="room-price">
                            ₱<?= number_format($room['RatePerNight'], 2) ?>
                            <small>/night</small>
                        </div>
                        <div class="room-capacity">Up to <?= (int)$room['Capacity'] ?></div>
                    </div>
                    <button class="btn-add-cart add-cart-btn" data-room="<?= $room['RoomID'] ?>">
                        + Add to Cart
                    </button>
                    <hr>
                    <br>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-results">
            <p>No rooms match your current filters. Try adjusting your search.</p>
        </div>
    <?php endif; ?>
</div>
<br>
<footer>
    <span class="footer-brand">&#9670; Grand Hotel</span>
    Email: hotel@grandhotel.com &nbsp;|&nbsp; Phone: +635 555 555<br>
    123 Luxury Avenue, Angeles City, Philippines<br>
    &copy; <?= date('Y') ?> Grand Hotel. All rights reserved.
</footer>
<hr>
<!-- CART MODAL -->
<div class="modal-overlay" id="cartModal"
 style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
            align-items:center; justify-content:center;">
    <div class="modal-box cart-modal-box">
        <button class="modal-close" onclick="closeCart()">&times;</button>
        <h3>Your Cart</h3>
        <div id="cartContent">
            <p style="text-align:center; color:#888;">Loading...</p>
        </div>
        <div class="cart-modal-footer">
            <button class="btn-modal-submit" onclick="goCheckout()">Proceed to Checkout</button>
            <button class="btn-modal-cancel" onclick="closeCart()">Close</button>
        </div>
    </div>
</div>

<!-- ADD TO CART MODAL -->
<div class="modal-overlay" id="cartForm"
 style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
            align-items:center; justify-content:center;">
    <div class="modal-box">
        <button class="modal-close" onclick="closeForm()">&times;</button>
        <h3>Add Room to Cart</h3>
        <input type="hidden" id="room_id">

        <div class="form-group">
            <label>Check-in:</label><br>
            <input type="date" name="checkin" id="modal_checkin"><br><br>
        </div>
        <div class="form-group">
            <label>Check-out:</label><br>
            <input type="date" name="checkout" id="modal_checkout"><br><br>
        </div>
        <div class="form-group">
            <label>Guests:</label><br>
            <input type="number" name="guests" id="modal_guests" min="1"><br><br>
        </div>
        <div class="form-group">
            <label>Special Notes (optional)</label>
            <textarea id="modal_notes" placeholder="Any special requests..."></textarea>
        </div>
        <div class="modal-actions">
            <button class="btn-modal-submit" onclick="submitCart()">Add to Cart</button>
            <button class="btn-modal-cancel" onclick="closeForm()">Cancel</button>
        </div>
    </div>
</div>

<!-- EDIT CART MODAL -->
<div class="modal-overlay" id="editForm"
 style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
            align-items:center; justify-content:center;">
    <div class="modal-box">
        <button class="modal-close" onclick="closeEdit()">&times;</button>
        <h3>Edit Cart Item</h3>
        <input type="hidden" id="edit_index">

        <div class="form-group">
            <label>Check-in:</label><br>
            <input type="date" id="edit_checkin"><br><br>
        </div>
        <div class="form-group">
            <label>Check-out:</label><br>
            <input type="date" id="edit_checkout"><br><br>
        </div>
        <div class="form-group">
            <label>Number of Guests</label>
            <input type="number" id="edit_guests" min="1">
        </div>
        <div class="modal-actions">
            <button class="btn-modal-submit" onclick="submitEdit()">Update</button>
            <button class="btn-modal-cancel" onclick="closeEdit()">Cancel</button>
        </div>
    </div>
</div>

<script src="guest_script.js"></script>
</body>
</html>