<?php
session_start();
include '../../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = $_POST['request_id'];
    $handover_note = $_POST['handover_note'] ?? '';

    try {
        // Update status to 'On Loan' and set the condition_note column
        $stmt = $pdo->prepare("UPDATE borrowing_requests SET status = 'On Loan', condition_note = ? WHERE request_id = ?");
        $stmt->execute([$handover_note, $request_id]);

        header("Location: ../view_requests.php?msg=issued");
    } catch (Exception $e) {
        header("Location: ../view_requests.php?msg=error");
    }
}
exit();