<?php
session_start();
include ("..\connect.php");

// Get inputs
$checkin = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';
$guests = $_GET['guests'] ?? '';

// Initialize
$adds_rooms = $conn->query
        ("SELECT r.RoomID, r.RoomNumber, rt.TypeName, rt.Description, rt.RatePerNight, rt.Capacity
        FROM rooms r JOIN roomtypes rt 
        ON r.RoomTypeID = rt.RoomTypeID 
        WHERE r.RoomID < 5");

// Initialize
$available_rooms = null;
$show_rooms = false;

// Only show rooms if checkin and checkout are provided and valid
if (!empty($checkin) && !empty($checkout) && $checkin < $checkout) {

    $show_rooms = true;

    $guests_int = (int)$guests;

    $sql = "
    SELECT r.RoomID, r.RoomNumber, rt.TypeName, rt.Description, rt.RatePerNight, rt.Capacity
    FROM rooms r
    JOIN roomtypes rt ON r.RoomTypeID = rt.RoomTypeID
    WHERE r.Status = 'available'
    ";

    // Apply guest filter if provided
    if (!empty($guests_int)) {
        $sql .= " AND rt.Capacity >= $guests_int";
    }

    $available_rooms = $conn->query($sql);
}

      // Initialize user
    $user = null;

    if (isset($_SESSION['account_ID'])) {

        $acc_ID = $_SESSION['account_ID'];

        $result = $conn->query("SELECT * FROM account WHERE account_ID = '$acc_ID'");

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
        }
    }
        ?>

        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Document</title>
            <script src="guest_script.js"></script>
        </head>
        <body>

<div>
    <!-- Header logo -->
    <nav class="navbar">
        <a href="guest_dashboard.php">
            <div>LOGO</div>
            <span>Brand</span>
            <a href="..\logout.php"><button>Logout</button></a>
        </a>
    </nav>

    <hr>

    <!-- Search form -->
    <span><b>Search room Now</b></span><br>

    <form action="" method="GET">

        <label>Checkin Date: </label>
        <input type="date" name="checkin" id="checkin"
            value="<?= $checkin ?>"
            min="<?php echo date('Y-m-d'); ?>">

        <label>Checkout Date: </label>
        <input type="date" name="checkout" id="checkout"
            value="<?= $checkout ?>"
            min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">

        <input type="number" min="1" name="guests" id="guests"
            value="<?= $guests ?>" placeholder="Guests">

        <button type="submit" onclick="return searchRooms()">Search Rooms</button>

</form>

    <hr>
    <?php 
        if ($user && isset($user['firstName'])) {
            echo "Welcome " . $user['firstName']. " to our hotel";
        } else {
            echo "Guest";
        }
    ?>
    <button onclick="openCart()">View Cart</button>
    <hr>

    <!-- Rooms Section -->
    <?php if ($show_rooms): ?>

        <h3>Available Rooms</h3>

        <?php if ($available_rooms && $available_rooms->num_rows > 0): ?>

            <?php foreach($available_rooms as $room): ?>
                <div>
                    <h4><?= $room['RoomNumber'] ?> - <?= $room['TypeName'] ?></h4>
                    <p>Capacity: <?= $room['Capacity'] ?> guests</p>
                    <p>Description: <?= $room['Description']?></p>
                    <p>Price: ₱<?= number_format($room['RatePerNight'],2) ?> per night</p>

                    <button class="add-cart-btn" data-room="<?= $room['RoomID'] ?>">Add to Cart</button>
                </div>
                <hr>
            <?php endforeach; ?>

        <?php else: ?>
            <p>No rooms available for the selected guests.</p>
        <?php endif; ?>

    <?php else: ?>
        <p><b>Please select check-in and check-out dates to view available rooms.</b></p>
    <?php endif; ?>

    <hr>

     <!-- Available rooms for advertistment-->
        <h3>Rooms Previews</h3>
        <a href="view_all.php"><button>View all rooms</button></a>
        <?php foreach($adds_rooms as $rooms): ?>
            <div class="">
                <h4><?= $rooms['TypeName'] ?></h4>
                <p>Capacity: <?= $rooms['Capacity'] ?> guests</p>
                <p>Description: <?= $rooms['Description']?></p>
                <p>Price: ₱<?= number_format($rooms['RatePerNight'],2) ?> per night</p>
                <a href="view_all.php"><button>Reserve Now</button></a>
            </div>
        <?php endforeach; ?>

    <hr>

    <a href="guest_dashboard.php">Back</a>

    <!-- Footer -->
    <hr>
    <h3>Contact Information</h3>
    <p>Email: website@mail.com</p>
    <p>Phone: +635 555 555</p>
    <p>Address: Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
    <p>© 2026 Brand. All rights reserved.</p>

    <script>
    const checkinInput = document.getElementById('checkin');
    const checkoutInput = document.getElementById('checkout');

    // Dynamic date restrictions
    checkinInput.addEventListener('change', function() {
        let checkinDate = this.value;
        if (checkinDate) {
            let nextDay = new Date(checkinDate);
            nextDay.setDate(nextDay.getDate() + 1);
            checkoutInput.min = nextDay.toISOString().split('T')[0];

            // Prevent checkout being before check-in
            if (checkoutInput.value && checkoutInput.value <= checkinDate) {
                checkoutInput.value = '';
            }
        }
        // Remove max restriction if check-in cleared
        if (!checkinDate) checkoutInput.min = new Date().toISOString().split('T')[0];
    });

    checkoutInput.addEventListener('change', function() {
        let checkoutDate = this.value;
        if (checkoutDate) {
            let prevDay = new Date(checkoutDate);
            prevDay.setDate(prevDay.getDate() - 1);
            checkinInput.max = prevDay.toISOString().split('T')[0];

            // Prevent checkin being after checkout
            if (checkinInput.value && checkinInput.value >= checkoutDate) {
                checkinInput.value = '';
            }
        }
        // Remove max restriction if checkout cleared
        if (!checkoutDate) checkinInput.max = '';
    });

    function searchRooms() {
        let checkin  = checkinInput.value;
        let checkout = checkoutInput.value;
        let guests   = document.getElementById('guests').value;

        if (checkin == "" || checkout == "") {
            alert("Please select check-in and check-out dates!");
            return false;
        }
        if (checkin >= checkout) {
            alert("Check-out must be after check-in!");
            return false;
        }
    }
    </script>

</div>
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