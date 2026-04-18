<?php
session_start();
include '../../includes/db_connect.php';
include '../../includes/mail_helper.php'; // Include the helper

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = $_POST['request_id'];
    $tag_id = $_POST['assigned_tag'] ?? null; 
    $handover_note = $_POST['handover_note'] ?? '';

    if ($request_id && $tag_id) {
        try {
            $pdo->beginTransaction();

            // 1. Get Asset, User, and Request details (JOINed for email)
            $stmtDetails = $pdo->prepare("SELECT a.asset_id, a.category, a.asset_name, 
                                                u.full_name, u.email, r.return_date
                                         FROM borrowing_requests r 
                                         JOIN assets a ON r.asset_id = a.asset_id 
                                         JOIN users u ON r.user_id = u.user_id
                                         WHERE r.request_id = ?");
            $stmtDetails->execute([$request_id]);
            $details = $stmtDetails->fetch();
            
            if (!$details) throw new Exception("Request not found");

            $asset_id = $details['asset_id'];
            $category = strtolower($details['category']); 

            // 2. Logic Branching for Stock
            if ($category === 'consumables') {
                $newStatus = 'Issued';
                $updateStock = $pdo->prepare("UPDATE assets SET current_stock = current_stock - 1, total_stock = total_stock - 1 WHERE asset_id = ?");
            } else {
                $newStatus = 'On Loan';
                $updateStock = $pdo->prepare("UPDATE assets SET current_stock = current_stock - 1 WHERE asset_id = ?");
            }
            $updateStock->execute([$asset_id]);

            // 3. Update the Request Status
            $stmtReq = $pdo->prepare("UPDATE borrowing_requests 
                                      SET status = ?, 
                                          tag_id = ?, 
                                          condition_note = ? 
                                      WHERE request_id = ?");
            $stmtReq->execute([$newStatus, $tag_id, $handover_note, $request_id]);

            // 4. Update the Tag Status
            $stmtTag = $pdo->prepare("UPDATE asset_tags SET status = ? WHERE tag_id = ?");
            $stmtTag->execute([$newStatus, $tag_id]);

            $pdo->commit();

            // 5. TRIGGER EMAIL NOTIFICATION
            $subject = ($newStatus === 'Issued') ? "Item Issued - Nuqtah System" : "Equipment Handover Confirmed - Nuqtah";
            
            $instr = ($newStatus === 'Issued') 
                ? "This item has been issued to you. No return is required." 
                : "This item is now on loan to you. Please return it by <strong>" . date('d M Y', strtotime($details['return_date'])) . "</strong>.";

            $message = "
                <p>Hello <strong>{$details['full_name']}</strong>,</p>
                <p>The ICT Department has processed your request for <strong>{$details['asset_name']}</strong>.</p>
                <div style='background: #f4f4f4; padding: 15px; border-radius: 8px; border-left: 5px solid #00796B;'>
                    <strong>Status:</strong> $newStatus<br>
                    <strong>Tag ID:</strong> $tag_id<br>
                    <strong>Condition:</strong> " . ($handover_note ?: 'Good/Normal') . "
                </div>
                <p>$instr</p>
                <p>Please handle the equipment with care.</p>";

            sendNuqtahEmail($details['email'], $details['full_name'], $subject, $message);

            header("Location: ../view_requests.php?msg=issued");
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log($e->getMessage());
            header("Location: ../view_requests.php?msg=error");
        }
    } else {
        header("Location: ../view_requests.php?msg=error");
    }
}
exit();