<?php
session_start();

// remove ONLY cart (recommended)
unset($_SESSION['cart']);

// OR destroy everything (strong reset)
// session_destroy();

header("Location: index.php");
exit;
?>