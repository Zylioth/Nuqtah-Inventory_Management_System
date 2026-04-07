<?php
session_start();
include '../../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../login.php");
    exit();
}

$request_id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? $_GET['status'] ?? null;
$note = $_GET['note'] ?? '';

if ($request_id && $action) {
    try {
        $pdo->beginTransaction();

        $new_status = (strtolower($action) === 'approve' || $action === 'Approved') ? 'Approved' : 'Rejected';

        $stmt = $pdo->prepare("SELECT status, quantity FROM borrowing_requests WHERE request_id = ?");
        $stmt->execute([$request_id]);
        $request = $stmt->fetch();

        if ($request && $request['status'] === 'Pending') {
            
            if ($new_status === 'Approved') {
                $qty = (int)$request['quantity'];

                if ($qty > 1) {
                    // Split logic: Create (qty - 1) new rows
                    // Using ONLY the columns we've confirmed: user_id, asset_id, quantity, status, request_date
                    for ($i = 1; $i < $qty; $i++) {
                        $copyStmt = $pdo->prepare("INSERT INTO borrowing_requests 
                            (user_id, asset_id, quantity, status, request_date) 
                            SELECT user_id, asset_id, 1, 'Approved', request_date 
                            FROM borrowing_requests WHERE request_id = ?");
                        $copyStmt->execute([$request_id]);
                    }
                    
                    // Update the original row to qty 1 and status Approved
                    $updateOrig = $pdo->prepare("UPDATE borrowing_requests SET status = 'Approved', quantity = 1 WHERE request_id = ?");
                    $updateOrig->execute([$request_id]);
                } else {
                    $updateStatus = $pdo->prepare("UPDATE borrowing_requests SET status = 'Approved' WHERE request_id = ?");
                    $updateStatus->execute([$request_id]);
                }
            } 
            else {
                $updateStatus = $pdo->prepare("UPDATE borrowing_requests SET status = 'Rejected', admin_note = ? WHERE request_id = ?");
                $updateStatus->execute([$note, $request_id]);
            }

            $pdo->commit();
            header("Location: ../view_requests.php?msg=success");
        } else {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            header("Location: ../view_requests.php?msg=already_processed");
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        // Back to redirecting instead of die() once it's fixed!
        header("Location: ../view_requests.php?msg=error");
    }
} else {
    header("Location: ../view_requests.php");
}
exit();