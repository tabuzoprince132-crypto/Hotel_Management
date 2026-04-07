<?php
/**
 * guest_dashboard.php
 * Main guest page: search rooms, view previews, access cart.
 */
session_start();
include("../connect.php");

if (!isset($_SESSION['account_ID'])) {
    header("Location: ../index.php");
    exit;
}

// Search inputs
$checkin  = $_GET['checkin']  ?? '';
$checkout = $_GET['checkout'] ?? '';
$guests   = $_GET['guests']   ?? '';

// Fetch user info
$user = null;
if (isset($_SESSION['account_ID'])) {
    $uid  = (int)$_SESSION['account_ID'];
    $stmt = $conn->prepare("SELECT * FROM account WHERE account_ID = ?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $user = $res->fetch_assoc();
    }
}

// Preview rooms (advertisement section — first 4 rooms)
$preview_rooms = $conn->query(
    "SELECT r.RoomID, r.RoomNumber, rt.TypeName, rt.Description, rt.RatePerNight, rt.Capacity
     FROM rooms r
     JOIN roomtypes rt ON r.RoomTypeID = rt.RoomTypeID
     WHERE r.Status = 'available'
     LIMIT 4"
);

// Available rooms (search results)
$available_rooms = null;
$show_rooms      = false;

if (!empty($checkin) && !empty($checkout) && $checkin < $checkout) {
    $show_rooms  = true;
    $guests_int  = (int)$guests;

    $sql = "SELECT r.RoomID, r.RoomNumber, rt.TypeName, rt.Description, rt.RatePerNight, rt.Capacity
            FROM rooms r
            JOIN roomtypes rt ON r.RoomTypeID = rt.RoomTypeID
            WHERE r.Status = 'available'
              AND NOT EXISTS (
                  SELECT 1 FROM reservations res
                  WHERE res.RoomID = r.RoomID
                    AND res.checkin_date < ?
                    AND res.checkout_date > ?
                    AND res.status <> 'Cancelled'
              )";

    if ($guests_int > 0) {
        $sql .= " AND rt.Capacity >= ?";
    }

    $stmt = $conn->prepare($sql);
    if ($guests_int > 0) {
        $stmt->bind_param("ssi", $checkout, $checkin, $guests_int);
    } else {
        $stmt->bind_param("ss", $checkout, $checkin);
    }
    $stmt->execute();
    $available_rooms = $stmt->get_result();
}

$today    = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grand Hotel &mdash; Guest Dashboard</title>
</head>
<body>

<!-- NAV -->
<nav>
    <a class="brand" href="guest_dashboard.php">&#9670; Grand Hotel</a>
    <div class="nav-right">
        <span class="nav-welcome">
</span>
        <a class="btn-nav" href="../logout.php">Logout</a>
    </div>
</nav>
<hr>

<div>
    Welcome, <?= htmlspecialchars($user['firstName'] ?? ($_SESSION['username'] ?? 'Guest')) ?> <br>
    <button class="btn-nav solid" onclick="openCart()">Cart</button>
</div>

<hr>

<!-- HERO / SEARCH -->
<div class="hero">
    <h1>Find Your Perfect Room</h1>
    <p>Luxury and comfort tailored to your stay</p>

    <form action="" method="GET">
        <div class="search-bar">
            <div class="search-field">
                <label>Check-in</label>
                <input type="date" name="checkin" id="checkin"
                       value="<?= htmlspecialchars($checkin) ?>"
                       min="<?= $today ?>">
            </div>
            <div class="search-field">
                <label>Check-out</label>
                <input type="date" name="checkout" id="checkout"
                       value="<?= htmlspecialchars($checkout) ?>"
                       min="<?= $tomorrow ?>">
            </div>
            <div class="search-field">
                <label>Guests</label>
                <input type="number" name="guests" id="guests"
                       value="<?= htmlspecialchars($guests) ?>"
                       min="1" max="6" placeholder="How many?">
            </div>
            <button type="submit" class="btn-search" onclick="return validateSearch()">Search</button>
        </div>
    </form>
</div>

<!-- MAIN CONTENT -->
<div class="content">

    <!-- Search Results -->
    <?php if ($show_rooms): ?>
        <h2 class="section-title">Available Rooms</h2>
        <?php if ($available_rooms && $available_rooms->num_rows > 0): ?>
            <div class="rooms-grid">
                <?php foreach ($available_rooms as $room): ?>
                <div class="room-card">
                    <div class="room-card-body">
                        <b><span class="type-badge"><?= htmlspecialchars($room['TypeName']) ?></span></b>
                        <div class="room-number">Room <?= htmlspecialchars($room['RoomNumber']) ?></div>
                        <div class="room-desc"><?= htmlspecialchars($room['Description']) ?></div>
                        <div class="room-meta">
                            <div class="room-price">
                                ₱<?= number_format($room['RatePerNight'], 2) ?>
                                <small>/night</small>
                            </div>
                            <div class="room-capacity">Up to <?= (int)$room['Capacity'] ?></div>
                        </div>
                        <button class="btn-add-cart add-cart-btn"
                                data-room="<?= $room['RoomID'] ?>">
                            + Add to Cart
                        </button>
                        <br><br>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <p>No available rooms match your search criteria. Try different dates or fewer guests.</p>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="search-prompt">
            <p>Enter check-in, check-out dates, and number of guests to see available rooms.</p>
        </div>
    <?php endif; ?>

    <br>

    <hr>
    <!-- Room Previews -->
    <div>
        <h2 class="section-title">Room Previews</h2>
        <a href="view_all.php"><button>View All</button></a>
    </div>

    <hr>
    <div class="rooms-grid">
        <?php foreach ($preview_rooms as $room): ?>
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
                <a href="view_all.php">
                    View Details
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<hr>
<!-- FOOTER -->
<footer>
    <span class="footer-brand">&#9670; Grand Hotel</span>
    Email: hotel@grandhotel.com &nbsp;|&nbsp; Phone: +635 555 555<br>
    123 Luxury Avenue, Angeles City, Philippines<br>
    &copy; <?= date('Y') ?> Grand Hotel. All rights reserved.
</footer>
<hr>
<!-- ══════════════ MODALS ══════════════ -->

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

<script>
// Date restriction logic
let checkinInput  = document.getElementById('checkin');
let checkoutInput = document.getElementById('checkout');

checkinInput.addEventListener('change', function () {
    if (this.value) {
        let next = new Date(this.value);
        next.setDate(next.getDate() + 1);
        checkoutInput.min = next.toISOString().split('T')[0];
        if (checkoutInput.value && checkoutInput.value <= this.value) {
            checkoutInput.value = '';
        }
    }
});

checkoutInput.addEventListener('change', function () {
    if (this.value) {
        if (checkinInput.value && checkinInput.value >= this.value) {
            checkinInput.value = '';
        }
    }
});

function validateSearch() {
    let ci = document.getElementById('checkin').value;
    let co = document.getElementById('checkout').value;
    if (!ci || !co) { alert('Please select check-in and check-out dates.'); return false; }
    if (ci >= co)   { alert('Check-out must be after check-in.'); return false; }
    return true;
}

// ===== OPEN MODAL + AUTOFILL =====
document.querySelectorAll('.add-cart-btn').forEach(btn => {
    btn.addEventListener('click', function () {

        // Room ID
        document.getElementById('room_id').value = this.dataset.room;

        // Autofill from search
        document.getElementById('form_checkin').value  = document.getElementById('checkin').value;
        document.getElementById('form_checkout').value = document.getElementById('checkout').value;
        document.getElementById('form_guests').value   = document.getElementById('guests').value;

        // Show modal
        document.getElementById('cartForm').style.display = "block";
    });
});

function closeForm() {
    document.getElementById('cartForm').style.display = "none";
}


</script>
<script src="guest_script.js"></script>
</body>
</html>