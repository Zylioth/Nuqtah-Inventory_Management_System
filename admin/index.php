<?php
session_start();
include '../includes/db_connect.php'; 

// Check if the user is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

// 1. Fetch Statistics
$total_assets = $pdo->query("SELECT COUNT(*) FROM assets")->fetchColumn();
$pending_requests = $pdo->query("SELECT COUNT(*) FROM borrowings WHERE status = 'Pending'")->fetchColumn();
$low_stock_count = $pdo->query("SELECT COUNT(*) FROM assets WHERE current_stock > 0 AND current_stock <= 5")->fetchColumn();
$out_of_stock_count = $pdo->query("SELECT COUNT(*) FROM assets WHERE current_stock = 0")->fetchColumn();

// 2. Fetch Recent Pending Requests (joining with users and assets tables)
$query = "SELECT b.*, u.full_name, a.asset_name 
          FROM borrowings b 
          JOIN users u ON b.user_id = u.user_id 
          JOIN assets a ON b.asset_id = a.asset_id 
          WHERE b.status = 'Pending' 
          ORDER BY b.request_date DESC LIMIT 5";
$pending_list = $pdo->query($query)->fetchAll();
?>

