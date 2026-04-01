<?php
session_start();
include '../../includes/db_connect.php';

// Security: Ensure only Admins can process requests
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../login.php");
    exit();
}

// Support both 'status' (from Dashboard) and 'action' (from View Requests)
$request_id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? $_GET['status'] ?? null;
$note = $_GET['note'] ?? ''; // Rejection reason from the JS prompt

if ($request_id && $action) {
    try {
        $pdo->beginTransaction();

        // 1. Convert action names to match Database Status
        $new_status = (strtolower($action) === 'approve' || $action === 'Approved') ? 'Approved' : 'Rejected';

        // 2. Fetch the request details
        $stmt = $pdo->prepare("SELECT asset_id, quantity, status FROM borrowing_requests WHERE request_id = ?");
        $stmt->execute([$request_id]);
        $request = $stmt->fetch();

        // Only process if the request is still 'Pending' to prevent double-deducting stock
        if ($request && $request['status'] === 'Pending') {
            
            if ($new_status === 'Approved') {
                // 3. Subtract the requested quantity from the assets table
                $updateStock = $pdo->prepare("UPDATE assets SET current_stock = current_stock - ? WHERE asset_id = ?");
                $updateStock->execute([$request['quantity'], $request['asset_id']]);
                
                // 4. Update status to Approved
                $updateStatus = $pdo->prepare("UPDATE borrowing_requests SET status = 'Approved' WHERE request_id = ?");
                $updateStatus->execute([$request_id]);
            } 
            else {
                // 5. Update status to Rejected and save the admin_note
                $updateStatus = $pdo->prepare("UPDATE borrowing_requests SET status = 'Rejected', admin_note = ? WHERE request_id = ?");
                $updateStatus->execute([$note, $request_id]);
            }

            $pdo->commit();
            header("Location: ../view_requests.php?msg=success");
        } else {
            // Already processed
            $pdo->rollBack();
            header("Location: ../view_requests.php?msg=already_processed");
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: ../view_requests.php?msg=error");
    }
} else {
    header("Location: ../view_requests.php");
}
exit();