<?php
session_start();
include '../../includes/db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$asset_id = $_POST['asset_id'] ?? null;
$unique_tag = $_POST['unique_tag'] ?? '';

if ($asset_id && !empty($unique_tag)) {
    $stmt = $pdo->prepare("INSERT INTO asset_tags (asset_id, unique_tag, status) VALUES (?, ?, 'Available')");
    if ($stmt->execute([$asset_id, $unique_tag])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
}