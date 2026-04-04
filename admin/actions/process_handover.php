<?php
session_start();
include '../../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = $_POST['request_id'];
    $assigned_tag = $_POST['assigned_tag'] ?? null; // Get the tag from the modal
    $handover_note = $_POST['handover_note'] ?? '';

    if ($request_id && $assigned_tag) {
        try {
            $pdo->beginTransaction();

            // 1. Update the request to 'On Loan' and save the specific tag
            $stmt = $pdo->prepare("UPDATE borrowing_requests 
                                   SET status = 'On Loan', 
                                       assigned_tag = ?, 
                                       condition_note = ? 
                                   WHERE request_id = ?");
            $stmt->execute([$assigned_tag, $handover_note, $request_id]);

            // 2. Update the physical tag in asset_tags so it's no longer 'Available'
            $stmt2 = $pdo->prepare("UPDATE asset_tags SET status = 'On Loan' WHERE unique_tag = ?");
            $stmt2->execute([$assigned_tag]);

            $pdo->commit();
            header("Location: ../view_requests.php?msg=issued");
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            header("Location: ../view_requests.php?msg=error");
        }
    } else {
        // Fallback if tag is missing
        header("Location: ../view_requests.php?msg=error");
    }
}
exit();