<?php
session_start();
include '../../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../login.php");
    exit();
}

$request_id = $_POST['request_id'] ?? null;
// Capture the status from our new dropdown!
$return_status = $_POST['return_status'] ?? 'Available'; 
$note = $_POST['return_note'] ?? 'Returned';

if ($request_id) {
    try {
        $pdo->beginTransaction();

        // 1. Get the asset_id and the tag_id (the numeric ID we fixed earlier)
        $stmt = $pdo->prepare("SELECT asset_id, tag_id FROM borrowing_requests WHERE request_id = ?");
        $stmt->execute([$request_id]);
        $request = $stmt->fetch();

        if ($request) {
            $asset_id = $request['asset_id'];
            $tag_id = $request['tag_id'];

            // 2. Update the Tag Status based on the Admin's choice
            if (!empty($tag_id)) {
                $updateTag = $pdo->prepare("UPDATE asset_tags SET status = ? WHERE tag_id = ?");
                $updateTag->execute([$return_status, $tag_id]);
            }

            // 3. Stock Management Logic (The Analytics part)
            if ($return_status === 'Available') {
                // If it's good, put it back in the pool
                $updateStock = $pdo->prepare("UPDATE assets SET current_stock = current_stock + 1 WHERE asset_id = ?");
                $updateStock->execute([$asset_id]);
            } else {
                // If Damaged or Maintenance, it's no longer part of the total usable units
                $reduceTotal = $pdo->prepare("UPDATE assets SET total_stock = total_stock - 1 WHERE asset_id = ?");
                $reduceTotal->execute([$asset_id]);
            }

            // 4. Finalize the request status
            $updateStatus = $pdo->prepare("UPDATE borrowing_requests SET status = 'Returned', return_note = ?, actual_return_date = NOW() WHERE request_id = ?");
            $updateStatus->execute([$note, $request_id]);
        }

        $pdo->commit();
        header("Location: ../view_requests.php?msg=returned");
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header("Location: ../view_requests.php?msg=error");
    }
} else {
    header("Location: ../view_requests.php");
}
exit();