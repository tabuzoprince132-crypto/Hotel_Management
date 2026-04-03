<?php
include ("../connect.php");

// get data from URL
$checkin  = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';
$guests   = $_GET['guests'] ?? '';

// simple validation
if ($checkin == "" || $checkout == "" || $guests == "") {
    header("Location: guest_dashboard.php");
    exit();
}

if ($checkin >= $checkout) {
    header("Location: guest_dashboard.php");
    exit();
}

// query available rooms based on capacity
$sql = "SELECT r.RoomID, r.RoomNumber, rt.TypeName, rt.Description, rt.RatePerNight, rt.Capacity
        FROM rooms r
        JOIN roomtypes rt ON r.RoomTypeID = rt.RoomTypeID
        WHERE r.Status='available' AND rt.Capacity >= '$guests'";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Results</title>
</head>
<body>

<!-- Header logo -->
<nav class="navbar">
  <a class="" href="guest_dashboard.php">
    <div class="">LOGO</div>
    <span class="">Brand</span>
  </a>
</nav>

<hr>

<h2>Search Results</h2>

<p>Check-in: <?= $checkin ?></p>
<p>Check-out: <?= $checkout ?></p>
<p>Guests: <?= $guests ?></p>

<hr>

<?php if ($result->num_rows > 0): ?>
    
    <?php while($room = $result->fetch_assoc()): ?>
        <div>
            <h3><?= $room['RoomNumber'] ?> - <?= $room['TypeName'] ?></h3>
            <p>Capacity: <?= $room['Capacity'] ?></p>
            <p><?= $room['Description'] ?></p>
            <p>₱<?= number_format($room['RatePerNight'],2) ?></p>

            <a href="reserve.php?room_id=<?= $room['RoomID'] ?>">
                <button>Book Now</button>
            </a>
        </div>
        <hr>
    <?php endwhile; ?>

<?php else: ?>
    <p>No rooms found.</p>
<?php endif; ?>

<a href="guest_dashboard.php">Back</a>

<!--Footer-->   
    <hr>
    <h3>Contact Information</h3>
    <p>Email: website@mail.com</p>
    <p>Phone: +635 555 555</p>
    <p>Address: Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
    <p>© 2026 Brand. All rights reserved.</p>


</body>
</html>