<?php
session_start();
include 'includes/db_connect.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch requests for the logged-in user
$query = "SELECT r.*, a.asset_name, a.category 
          FROM borrowing_requests r
          JOIN assets a ON r.asset_id = a.asset_id
          WHERE r.user_id = ?
          ORDER BY r.request_date DESC";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$my_requests = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Requests - Nuqtah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; padding: 40px; }
        .status-badge { font-size: 0.8rem; padding: 5px 12px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">My Borrowing History</h2>
        <a href="inventory_list.php" class="btn btn-outline-dark rounded-pill">Back to Inventory</a>
    </div>

    <div class="card overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Asset</th>
                        <th>Qty</th>
                        <th>Date Requested</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Admin Note</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($my_requests as $row): ?>
                        <tr>
                            <td class="ps-4">
                                <strong><?php echo htmlspecialchars($row['asset_name']); ?></strong><br>
                                <small class="text-muted"><?php echo $row['category']; ?></small>
                            </td>
                            <td><?php echo $row['quantity']; ?></td>
                            <td><?php echo date('d M Y', strtotime($row['request_date'])); ?></td>
                            <td>
                                <span class="badge rounded-pill status-badge 
                                    <?php echo ($row['status'] == 'Pending') ? 'bg-warning text-dark' : 
                                               (($row['status'] == 'Approved' || $row['status'] == 'On Loan') ? 'bg-success' : 'bg-danger'); ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td class="text-end pe-4 small">
                                <?php 
                                    if ($row['status'] == 'Rejected' && !empty($row['admin_note'])) {
                                        echo '<span class="text-danger fw-bold">Reason:</span> ' . htmlspecialchars($row['admin_note']);
                                    } 
                                    elseif ($row['status'] == 'On Loan' && !empty($row['condition_note'])) {
                                        echo '<span class="text-dark fw-bold">Handover Condition:</span> ' . htmlspecialchars($row['condition_note']);
                                    }
                                    elseif ($row['status'] == 'Returned' && !empty($row['return_note'])) {
                                        echo '<span class="text-success fw-bold">Return Note:</span> ' . htmlspecialchars($row['return_note']);
                                    }
                                    elseif (!empty($row['admin_note'])) {
                                        // This catches notes for Approved or other statuses
                                        echo htmlspecialchars($row['admin_note']);
                                    }
                                    else {
                                        echo '<span class="text-muted">-</span>';
                                    }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>