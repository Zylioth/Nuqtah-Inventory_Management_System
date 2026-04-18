<?php
session_start();
include '../../includes/db_connect.php';
include_once '../../includes/mail_helper.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../login.php");
    exit();
}

// Using GET as per your current file logic
$request_id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? $_GET['status'] ?? null;
$note = $_GET['note'] ?? '';

if ($request_id && $action) {
    try {
        $pdo->beginTransaction();

        $new_status = (strtolower($action) === 'approve' || $action === 'Approved') ? 'Approved' : 'Rejected';

        // 1. Fetch user and asset details
        $stmt = $pdo->prepare("
            SELECT br.status, br.quantity, u.email, u.full_name, a.asset_name 
            FROM borrowing_requests br
            JOIN users u ON br.user_id = u.user_id
            JOIN assets a ON br.asset_id = a.asset_id
            WHERE br.request_id = ?
        ");
        $stmt->execute([$request_id]);
        $request = $stmt->fetch();

        if ($request && $request['status'] === 'Pending') {
            
            if ($new_status === 'Approved') {
                $qty = (int)$request['quantity'];

                // Split quantities into individual rows for unique tag assignment
                if ($qty > 1) {
                    for ($i = 1; $i < $qty; $i++) {
                        $copyStmt = $pdo->prepare("INSERT INTO borrowing_requests 
                            (user_id, asset_id, quantity, status, request_date, is_read) 
                            SELECT user_id, asset_id, 1, 'Approved', request_date, 0 
                            FROM borrowing_requests WHERE request_id = ?");
                        $copyStmt->execute([$request_id]);
                    }
                    $updateOrig = $pdo->prepare("UPDATE borrowing_requests SET status = 'Approved', quantity = 1, is_read = 0 WHERE request_id = ?");
                    $updateOrig->execute([$request_id]);
                } else {
                    $updateStatus = $pdo->prepare("UPDATE borrowing_requests SET status = 'Approved', is_read = 0 WHERE request_id = ?");
                    $updateStatus->execute([$request_id]);
                }

                // Prepare Approval Email
                $subject = "Request Approved - Nuqtah Inventory";
                $message = "
                    <h3>Hello {$request['full_name']}!</h3>
                    <p>Your request for <b>{$request['asset_name']}</b> has been <b>Approved</b>.</p>
                    <p>Please proceed to the <b>ICT Department</b> to collect your item.</p>
                    <p style='color: #555;'><i>Note: Please bring your student/staff ID for verification.</i></p>";
                
            } else {
                // Handle Rejection
                $updateStatus = $pdo->prepare("UPDATE borrowing_requests SET status = 'Rejected', admin_note = ?, is_read = 0 WHERE request_id = ?");
                $updateStatus->execute([$note, $request_id]);

                // Prepare Rejection Email
                $subject = "Update on Your Borrowing Request";
                $message = "
                    <h3>Hello {$request['full_name']},</h3>
                    <p>We regret to inform you that your request for <b>{$request['asset_name']}</b> could not be approved at this time.</p>
                    <p><b>Reason:</b> " . (!empty($note) ? htmlspecialchars($note) : "No specific reason provided.") . "</p>
                    <p>Feel free to submit a new request or visit the ICT Department for inquiries.</p>";
            }

            $pdo->commit();

            // 2. SEND EMAIL AFTER COMMIT (Prevents DB rollback if mail server is slow)
            sendNuqtahEmail($request['email'], $request['full_name'], $subject, $message);

            header("Location: ../view_requests.php?msg=success");
        } else {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            header("Location: ../view_requests.php?msg=already_processed");
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        error_log("Approval Error: " . $e->getMessage());
        header("Location: ../view_requests.php?msg=error");
    }
} else {
    header("Location: ../view_requests.php");
}
exit();