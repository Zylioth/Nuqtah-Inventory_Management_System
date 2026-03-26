<?php
session_start();

// prevent entry mengajut
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect them to login.php
    header("Location: login.php?error=unauthorized");
    exit();
}
?>