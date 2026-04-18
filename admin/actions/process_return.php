<?php
session_start();
include '../../includes/db_connect.php';
include '../../includes/mail_helper.php'; // 1. Added mail helper

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../login.php");
    exit();
}

$request_id = $_POST['request_id'] ?? null;
$return_status = $_POST['return_status'] ?? 'Available'; 
$note = $_POST['return_note'] ?? 'Returned';

if ($request_id) {
    try {
        $pdo->beginTransaction();

        // 2. Modified Query to get User and Asset info for the email
        $stmt = $pdo->prepare("SELECT r.asset_id, r.tag_id, u.full_name, u.email, a.asset_name 
                               FROM borrowing_requests r
                               JOIN users u ON r.user_id = u.user_id
                               JOIN assets a ON r.asset_id = a.asset_id
                               WHERE r.request_id = ?");
        $stmt->execute([$request_id]);
        $details = $stmt->fetch();

        if ($details) {
            $asset_id = $details['asset_id'];
            $tag_id = $details['tag_id'];

            // 3. Update the Tag Status based on the Admin's choice
            if (!empty($tag_id)) {
                $updateTag = $pdo->prepare("UPDATE asset_tags SET status = ? WHERE tag_id = ?");
                $updateTag->execute([$return_status, $tag_id]);
            }

            // 4. Stock Management Logic
            if ($return_status === 'Available') {
                $updateStock = $pdo->prepare("UPDATE assets SET current_stock = current_stock + 1 WHERE asset_id = ?");
                $updateStock->execute([$asset_id]);
            } else {
                // If Damaged/Maintenance, reduce total pool
                $reduceTotal = $pdo->prepare("UPDATE assets SET total_stock = total_stock - 1 WHERE asset_id = ?");
                $reduceTotal->execute([$asset_id]);
            }

            // 5. Finalize the request status
            $updateStatus = $pdo->prepare("UPDATE borrowing_requests SET status = 'Returned', return_note = ?, actual_return_date = NOW() WHERE request_id = ?");
            $updateStatus->execute([$note, $request_id]);

            $pdo->commit();

            // 6. TRIGGER EMAIL NOTIFICATION
            $subject = "Equipment Return Confirmation - Nuqtah";
            
            // Customize message based on the condition it was returned in
            $statusText = ($return_status === 'Available') ? "Good Condition" : "Flagged as $return_status";
            
            $message = "
                <p>Hello <strong>{$details['full_name']}</strong>,</p>
                <p>This is a confirmation that the equipment you borrowed has been successfully processed as <strong>Returned</strong>.</p>
                <div style='background: #f9f9f9; padding: 15px; border-radius: 8px; border-left: 5px solid #00796B;'>
                    <strong>Item:</strong> {$details['asset_name']}<br>
                    <strong>Returned Condition:</strong> $statusText<br>
                    <strong>Staff Note:</strong> " . htmlspecialchars($note) . "
                </div>
                <p>Your record for this specific request is now cleared. Thank you for your cooperation!</p>";

            sendNuqtahEmail($details['email'], $details['full_name'], $subject, $message);

        } else {
            $pdo->rollBack();
            header("Location: ../view_requests.php?msg=error");
            exit();
        }

        header("Location: ../view_requests.php?msg=returned");
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Return Process Error: " . $e->getMessage());
        header("Location: ../view_requests.php?msg=error");
    }
} else {
    header("Location: ../view_requests.php");
}
exit();