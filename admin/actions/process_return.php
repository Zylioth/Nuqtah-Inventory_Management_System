<?php
session_start();
include '../../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../login.php");
    exit();
}

$request_id = $_POST['request_id'] ?? null;
$note = $_POST['return_note'] ?? 'Returned in good condition';

if ($request_id) {
    try {
        $pdo->beginTransaction();

        // 1. Get the asset_id, quantity, AND the assigned_tag
        $stmt = $pdo->prepare("SELECT asset_id, quantity, assigned_tag FROM borrowing_requests WHERE request_id = ?");
        $stmt->execute([$request_id]);
        $request = $stmt->fetch();

        if ($request) {
            // 2. RESTORE general stock count in assets table
            $updateStock = $pdo->prepare("UPDATE assets SET current_stock = current_stock + ? WHERE asset_id = ?");
            $updateStock->execute([$request['quantity'], $request['asset_id']]);
            
            // 3. NEW: RESET the specific serial tag to 'Available'
            if (!empty($request['assigned_tag'])) {
                $updateTag = $pdo->prepare("UPDATE asset_tags SET status = 'Available' WHERE unique_tag = ?");
                $updateTag->execute([$request['assigned_tag']]);
            }

            // 4. Finalize the request status
            $updateStatus = $pdo->prepare("UPDATE borrowing_requests SET status = 'Returned', return_note = ?, is_read = 0 WHERE request_id = ?");
            $updateStatus->execute([$note, $request_id]);
        }

        $pdo->commit();
        header("Location: ../view_requests.php?msg=returned");
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // You can use error_log($e->getMessage()); here for debugging if needed
        header("Location: ../view_requests.php?msg=error");
    }
} else {
    header("Location: ../view_requests.php");
}
exit();