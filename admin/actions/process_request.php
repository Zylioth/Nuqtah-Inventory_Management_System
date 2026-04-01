<?php
session_start();
include '../../includes/db_connect.php';

// Security: Ensure only Admins can process requests
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../login.php");
    exit();
}

$request_id = $_GET['id'] ?? null;
$status = $_GET['status'] ?? null;

if ($request_id && $status) {
    try {
        $pdo->beginTransaction();

        if ($status === 'Approved') {
            // 1. Get the asset_id and quantity for this specific request
            $stmt = $pdo->prepare("SELECT asset_id, quantity FROM borrowing_requests WHERE request_id = ?");
            $stmt->execute([$request_id]);
            $request = $stmt->fetch();

            if ($request) {
                // 2. Subtract the requested quantity from the assets table
                $updateStock = $pdo->prepare("UPDATE assets SET current_stock = current_stock - ? WHERE asset_id = ?");
                $updateStock->execute([$request['quantity'], $request['asset_id']]);
            }
        }

        // 3. Update the request status (Works for both Approved and Rejected)
        $updateStatus = $pdo->prepare("UPDATE borrowing_requests SET status = ? WHERE request_id = ?");
        $updateStatus->execute([$status, $request_id]);

        $pdo->commit();
        header("Location: ../index.php?msg=success");
    } catch (Exception $e) {
        $pdo->rollBack();
        // Redirect with an error message if something fails
        header("Location: ../index.php?msg=error");
    }
} else {
    header("Location: ../index.php");
}
exit();