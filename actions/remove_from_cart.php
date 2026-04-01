<?php
session_start();
$id = $_GET['id'] ?? null;

if ($id && isset($_SESSION['cart'])) {
    $key = array_search($id, $_SESSION['cart']);
    if ($key !== false) {
        unset($_SESSION['cart'][$key]);
        // Re-index array to avoid gaps
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
}

header("Location: ../cart.php");
exit();