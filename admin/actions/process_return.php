<?php
session_start();
include '../../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../login.php");
    exit();
}

// It is safer to use POST for data changes, but keeping GET as per your current structure
$request_id = $_GET['id'] ?? null;
$note = $_GET['note'] ?? 'Returned in good condition';

if ($request_id) {
    try {
        $pdo->beginTransaction();

        // 1. Get the asset_id and quantity so we know what to put back
        $stmt = $pdo->prepare("SELECT asset_id, quantity FROM borrowing_requests WHERE request_id = ?");
        $stmt->execute([$request_id]);
        $request = $stmt->fetch();

        if ($request) {
            // 2. ADD the quantity back to the assets table
            $updateStock = $pdo->prepare("UPDATE assets SET current_stock = current_stock + ? WHERE asset_id = ?");
            $updateStock->execute([$request['quantity'], $request['asset_id']]);
            
            // 3. Update status to 'Returned' and save to the new return_note column
            // We no longer use CONCAT on admin_note
            $updateStatus = $pdo->prepare("UPDATE borrowing_requests SET status = 'Returned', return_note = ? WHERE request_id = ?");
            $updateStatus->execute([$note, $request_id]);
        }

        $pdo->commit();
        header("Location: ../view_requests.php?msg=returned");
    } catch (Exception $e) {
        $pdo->rollBack();
        // Log the error for debugging: error_log($e->getMessage());
        header("Location: ../view_requests.php?msg=error");
    }
} else {
    header("Location: ../view_requests.php");
}
exit();