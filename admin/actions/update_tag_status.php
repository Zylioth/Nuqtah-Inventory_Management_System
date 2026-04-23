<?php
session_start();
include '../../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$tag_id = $_POST['tag_id'] ?? null;
$asset_id = $_POST['asset_id'] ?? null;
$unique_tag = $_POST['unique_tag'] ?? 'Unknown';

if ($tag_id && $asset_id) {
    try {
        $pdo->beginTransaction();

        // 1. Update the tag to Available
        $stmt = $pdo->prepare("UPDATE asset_tags SET status = 'Available' WHERE tag_id = ?");
        $stmt->execute([$tag_id]);

        // 2. Increase the current_stock of the main asset
        $stmtStock = $pdo->prepare("UPDATE assets SET current_stock = current_stock + 1 WHERE asset_id = ?");
        $stmtStock->execute([$asset_id]);

        // 3. LOG THE ACTION (4-column schema)
        $admin_name = $_SESSION['full_name'];
        $log_action = "Admin $admin_name COMPLETED maintenance for Tag $unique_tag. Item is now Available.";
        
        $stmtLog = $pdo->prepare("INSERT INTO admin_logs (admin_id, action_taken) VALUES (?, ?)");
        $stmtLog->execute([$_SESSION['user_id'], $log_action]);

        $pdo->commit();
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}