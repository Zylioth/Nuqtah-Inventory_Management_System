<?php
session_start();
include '../../includes/db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$tag_id = $_POST['tag_id'] ?? null;

if ($tag_id) {
    // We use tag_id (the primary key) to ensure we delete the exact record
    $stmt = $pdo->prepare("DELETE FROM asset_tags WHERE tag_id = ?");
    if ($stmt->execute([$tag_id])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing Tag ID']);
}