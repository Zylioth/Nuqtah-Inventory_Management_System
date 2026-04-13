<?php
session_start();
include '../../includes/db_connect.php';
// Include the mail helper
include_once '../../includes/mail_helper.php';

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

        // 1. UPDATED QUERY: Fetch user and asset details along with the request
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

                if ($qty > 1) {
                    for ($i = 1; $i < $qty; $i++) {
                        $copyStmt = $pdo->prepare("INSERT INTO borrowing_requests 
                            (user_id, asset_id, quantity, status, request_date) 
                            SELECT user_id, asset_id, 1, 'Approved', request_date 
                            FROM borrowing_requests WHERE request_id = ?");
                        $copyStmt->execute([$request_id]);
                    }
                    $updateOrig = $pdo->prepare("UPDATE borrowing_requests SET status = 'Approved', quantity = 1 WHERE request_id = ?");
                    $updateOrig->execute([$request_id]);
                } else {
                    $updateStatus = $pdo->prepare("UPDATE borrowing_requests SET status = 'Approved' WHERE request_id = ?");
                    $updateStatus->execute([$request_id]);
                }

                // --- EMAIL FOR APPROVAL ---
                $subject = "Request Approved - Nuqtah Inventory";
                $message = "
                    <h3>Hello {$request['full_name']}!</h3>
                    <p>Your request for <b>{$request['asset_name']}</b> has been <b>Approved</b>.</p>
                    <p>Please proceed to the ICT Department to collect your item.</p>
                    <p><i>Note: Please bring your ID for verification.</i></p>";
                
                sendNuqtahEmail($request['email'], $request['full_name'], $subject, $message);

            } else {
                $updateStatus = $pdo->prepare("UPDATE borrowing_requests SET status = 'Rejected', admin_note = ? WHERE request_id = ?");
                $updateStatus->execute([$note, $request_id]);

                // --- EMAIL FOR REJECTION ---
                $subject = "Update on Your Borrowing Request";
                $message = "
                    <h3>Hello {$request['full_name']},</h3>
                    <p>We regret to inform you that your request for <b>{$request['asset_name']}</b> could not be approved at this time.</p>
                    <p><b>Reason:</b> " . (!empty($note) ? htmlspecialchars($note) : "No specific reason provided.") . "</p>
                    <p>Please contact the ICT Department if you have further questions.</p>";
                
                sendNuqtahEmail($request['email'], $request['full_name'], $subject, $message);
            }

            $pdo->commit();
            header("Location: ../view_requests.php?msg=success");
        } else {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            header("Location: ../view_requests.php?msg=already_processed");
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        header("Location: ../view_requests.php?msg=error");
    }
} else {
    header("Location: ../view_requests.php");
}
exit();