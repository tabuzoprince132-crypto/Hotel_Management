<?php
include("..\connect.php");

// Get filter inputs
$search = $_GET['search'] ?? '';
$room_type = $_GET['room_type'] ?? '';
$guests = $_GET['guests'] ?? '';
$sort = $_GET['sort'] ?? '';

// Base query
$sql = "SELECT r.RoomID, r.RoomNumber, rt.TypeName, rt.Description, rt.RatePerNight, rt.Capacity
        FROM rooms r 
        JOIN roomtypes rt ON r.RoomTypeID = rt.RoomTypeID
        WHERE r.Status='available'";

// Search
if (!empty($search)) {
    $sql .= " AND rt.TypeName LIKE '%" . $conn->real_escape_string($search) . "%'";
}

// Room type filter
if (!empty($room_type)) {
    $sql .= " AND rt.TypeName = '" . $conn->real_escape_string($room_type) . "'";
}

// Guests filter
if (!empty($guests)) {
    $sql .= " AND rt.Capacity >= " . (int)$guests;
}

// Sorting
if ($sort == 'low') {
    $sql .= " ORDER BY rt.RatePerNight ASC";
} elseif ($sort == 'high') {
    $sql .= " ORDER BY rt.RatePerNight DESC";
}

$available_rooms = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Search Rooms</title>
</head>
<body>

<!-- Header -->
<nav>
    <a href="guest_dashboard.php">
        <div>LOGO</div>
        <span>Brand</span>
    </a>
    <button><a href="..\logout.php">Logout</a></button>
</nav>

<hr>
<!-- FILTER FORM -->
<form method="GET">

    <label>Search Room Type:</label>
    <input type="text" name="search" placeholder="e.g. Deluxe"
        value="<?= $search ?>" onchange="this.form.submit()">

    <br><br>

    <label>Room Type:</label>
    <select name="room_type" onchange="this.form.submit()">
        <option value="">All</option>
        <option value="Single" <?= ($room_type=='Single')?'selected':'' ?>>Single</option>
        <option value="Double" <?= ($room_type=='Double')?'selected':'' ?>>Double</option>
        <option value="Deluxe" <?= ($room_type=='Deluxe')?'selected':'' ?>>Deluxe</option>
        <option value="Family" <?= ($room_type=='Family')?'selected':'' ?>>Family</option>
    </select>

    <br><br>

    <label>Guests:</label>
    <input type="number" min="1" max="6" name="guests"
        value="<?= $guests ?>" onchange="this.form.submit()" onkeydown="return false;">

    <br><br>

    <label>Sort by Price:</label>
    <select name="sort" onchange="this.form.submit()">
        <option value="">Default</option>
        <option value="low" <?= ($sort=='low')?'selected':'' ?>>Lowest Price</option>
        <option value="high" <?= ($sort=='high')?'selected':'' ?>>Highest Price</option>
    </select>

</form>

<hr>
<button onclick="openCart()">View Cart</button>
<!-- RESULTS -->
<h3>Select a Room</h3>

<?php if ($available_rooms->num_rows > 0): ?>
    <?php foreach($available_rooms as $room): ?>
        <div>
            <h4><?= $room['RoomNumber'] ?> - <?= $room['TypeName'] ?></h4>
            <p>Capacity: up to <?= $room['Capacity'] ?> guests</p>
            <p>Description: <?= $room['Description']?></p>
            <p>Price: ₱<?= number_format($room['RatePerNight'],2) ?> per night</p>
            <button class="add-cart-btn" data-room="<?= $room['RoomID'] ?>">Add to Cart</button>
        </div>
        <hr>
    <?php endforeach; ?>
<?php else: ?>
    <p>No rooms found.</p>
<?php endif; ?>

<!-- Footer -->
<hr>
<h3>Contact Information</h3>
<p>Email: website@mail.com</p>
<p>Phone: +635 555 555</p>
<p>Address: Lorem ipsum dolor sit amet.</p>
<p>&copy; 2026 Brand. All rights reserved.</p>

<!-- CART MODAL -->
<div id="cartModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5)">
    <div style="background:#fff; width:500px; margin:100px auto; padding:20px; position:relative;">
        <h3>Your Cart</h3>
        <div id="cartContent">
            <!-- AJAX injects cart items here -->
        </div>
        <br>
        <button onclick="checkout()">Reserve / Checkout</button>
        <button onclick="closeCart()">Close</button>
    </div>
</div>

<!-- ADD TO CART FORM MODAL -->
<div id="cartForm" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5)">
    
    <div style="background:#fff; width:300px; margin:100px auto; padding:20px; position:relative;">
        <h3>Enter Booking Details</h3>

        <input type="hidden" id="room_id">

        <label>Guest Name:</label><br>
        <input type="text" id="guest_name"><br><br>

        <label>Check-in:</label><br>
        <input type="date" id="checkin"><br><br>

        <label>Check-out:</label><br>
        <input type="date" id="checkout"><br><br>

        <label>Guests:</label><br>
        <input type="number" id="guests" min="1"><br><br>

        <button onclick="submitCart()">Add</button>
        <button onclick="closeForm()">Cancel</button>
    </div>

</div>

<!-- EDIT CART FORM MODAL -->
<div id="editForm" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5)">
    <div style="background:#fff; width:300px; margin:100px auto; padding:20px; position:relative;">
        <h3>Edit Cart Item</h3>

        <input type="hidden" id="edit_index">

        <label>Guest Name:</label><br>
        <input type="text" id="edit_guest_name"><br><br>

        <label>Check-in:</label><br>
        <input type="date" id="edit_checkin"><br><br>

        <label>Check-out:</label><br>
        <input type="date" id="edit_checkout"><br><br>

        <label>Guests:</label><br>
        <input type="number" id="edit_guests" min="1"><br><br>

        <button onclick="submitEdit()">Update</button>
        <button onclick="closeEditForm()">Cancel</button>
    </div>
</div>

<script src="guest_script.js"></script>
</body>
</html>