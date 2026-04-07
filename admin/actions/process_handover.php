<?php
session_start();
include '../../includes/db_connect.php';

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

            // 1. Get the Asset ID and Category
            $stmtAsset = $pdo->prepare("SELECT a.asset_id, a.category 
                                        FROM borrowing_requests r 
                                        JOIN assets a ON r.asset_id = a.asset_id 
                                        WHERE r.request_id = ?");
            $stmtAsset->execute([$request_id]);
            $assetData = $stmtAsset->fetch();
            
            $asset_id = $assetData['asset_id'];
            // Convert to lowercase to match your database 'consumables'
            $category = strtolower($assetData['category']); 

            // 2. Logic Branching
            if ($category === 'consumables') {
                $newStatus = 'Issued';
                // Consumables: Decrease both Current and Total
                $updateStock = $pdo->prepare("UPDATE assets SET current_stock = current_stock - 1, total_stock = total_stock - 1 WHERE asset_id = ?");
            } else {
                $newStatus = 'On Loan';
                // Durable: Only decrease Current
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
            header("Location: ../view_requests.php?msg=issued");
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            header("Location: ../view_requests.php?msg=error");
        }
    } else {
        header("Location: ../view_requests.php?msg=error");
    }
}
exit();