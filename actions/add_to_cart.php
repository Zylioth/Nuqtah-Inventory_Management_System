<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asset_id'])) {
    $asset_id = $_POST['asset_id'];

    // Initialize cart if not set
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add asset to cart if it's not already there
    if (!in_array($asset_id, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $asset_id;
        $msg = "added";
    } else {
        $msg = "exists";
    }

    header("Location: ../inventory_list.php?msg=" . $msg);
    exit();
}