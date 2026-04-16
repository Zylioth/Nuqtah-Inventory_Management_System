<?php
session_start();
include '../../includes/db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$asset_id = $_POST['asset_id'] ?? null;
$unique_tag = $_POST['unique_tag'] ?? '';

if ($asset_id && !empty($unique_tag)) {
    
    // 1. Fetch asset details (including category and total_stock)
    $stmt = $pdo->prepare("SELECT total_stock, category FROM assets WHERE asset_id = ?");
    $stmt->execute([$asset_id]);
    $asset = $stmt->fetch();
    
    if (!$asset) {
        exit(json_encode(['success' => false, 'message' => 'Asset not found']));
    }

    $is_consumable = ($asset['category'] === 'Consumables' || $asset['category'] === 'Stationery');

    // 2. Count existing tags
    if ($is_consumable) {
        // For Consumables, we only care about how many are currently 'Available'
        // This allows you to add more tags as old ones get 'Issued'
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM asset_tags WHERE asset_id = ? AND status = 'Available'");
    } else {
        // For hardware (Laptops, etc.), we count every tag because the serial exists forever
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM asset_tags WHERE asset_id = ?");
    }
    
    $countStmt->execute([$asset_id]);
    $current_tag_count = $countStmt->fetchColumn();

    // 3. Validation: Compare against total_stock
    if ($current_tag_count >= $asset['total_stock']) {
        $msg = $is_consumable 
            ? "Limit reached! You already have {$asset['total_stock']} 'Available' consumables tags."
            : "Limit reached! This asset only has a total stock of {$asset['total_stock']} units.";
        
        exit(json_encode(['success' => false, 'message' => $msg]));
    }

    // 4. Proceed with insertion
    $insertStmt = $pdo->prepare("INSERT INTO asset_tags (asset_id, unique_tag, status) VALUES (?, ?, 'Available')");
    if ($insertStmt->execute([$asset_id, $unique_tag])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
}