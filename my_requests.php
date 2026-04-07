<?php
session_start();
include 'includes/db_connect.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Mark notifications as read
$clearNotif = $pdo->prepare("UPDATE borrowing_requests SET is_read = 1 WHERE user_id = ? AND is_read = 0");
$clearNotif->execute([$user_id]);

// --- UPDATED QUERY ---
// We join asset_tags to get the unique_tag column
$query = "SELECT r.*, a.asset_name, a.category, t.unique_tag 
          FROM borrowing_requests r
          JOIN assets a ON r.asset_id = a.asset_id
          LEFT JOIN asset_tags t ON r.tag_id = t.tag_id
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
        .tag-badge { background-color: #e9ecef; color: #495057; font-family: monospace; font-size: 0.85rem; border: 1px solid #dee2e6; }
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
                        <th class="ps-4">Asset & Tag</th>
                        <th>Qty</th>
                        <th>Date Requested</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Admin Note / Details</th>
                    </tr>
                </thead>
                <tbody>
                        <?php foreach ($my_requests as $row): ?>
                            <tr>
                                <td class="ps-4">
                                    <strong><?php echo htmlspecialchars($row['asset_name']); ?></strong><br>
                                    <div class="mt-1">
                                        <small class="text-muted"><?php echo htmlspecialchars($row['category']); ?></small>
                                        <?php if (!empty($row['unique_tag'])): ?>
                                            <span class="badge tag-badge ms-2">
                                                <i class="bi bi-tag-fill me-1"></i><?php echo htmlspecialchars($row['unique_tag']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo (int)$row['quantity']; ?></td>
                                <td><?php echo date('d M Y', strtotime($row['request_date'])); ?></td>
                                <td>
                                    <?php 
                                        $status = $row['status'];
                                        $badge_class = 'bg-secondary'; // Default

                                        if ($status == 'Pending') {
                                            $badge_class = 'bg-warning text-dark';
                                        } elseif ($status == 'Approved') {
                                            $badge_class = 'bg-primary';
                                        } elseif ($status == 'On Loan') {
                                            $badge_class = 'bg-success';
                                        } elseif ($status == 'Issued') {
                                            $badge_class = 'bg-purple text-white'; // Custom style
                                        } elseif ($status == 'Returned') {
                                            $badge_class = 'bg-info text-dark';
                                        } elseif ($status == 'Rejected') {
                                            $badge_class = 'bg-danger';
                                        }
                                    ?>
                                    <style> .bg-purple { background-color: #6f42c1; } </style>
                                    <span class="badge rounded-pill status-badge <?php echo $badge_class; ?>">
                                        <?php echo htmlspecialchars($status); ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4 small">
                                    <?php 
                                        if ($status == 'Rejected' && !empty($row['admin_note'])) {
                                            echo '<span class="text-danger fw-bold">Reason:</span> ' . htmlspecialchars($row['admin_note']);
                                        } 
                                        elseif ($status == 'On Loan' && !empty($row['condition_note'])) {
                                            echo '<span class="text-dark fw-bold">Condition:</span> ' . htmlspecialchars($row['condition_note']);
                                        }
                                        elseif ($status == 'Issued') {
                                            echo '<span class="text-dark fw-bold">Note:</span> Item handed over for permanent use.';
                                            if(!empty($row['condition_note'])) {
                                                echo '<br><small>' . htmlspecialchars($row['condition_note']) . '</small>';
                                            }
                                        }
                                        elseif ($status == 'Returned' && !empty($row['return_note'])) {
                                            echo '<span class="text-success fw-bold">Return Note:</span> ' . htmlspecialchars($row['return_note']);
                                        }
                                        elseif ($status == 'Approved') {
                                            echo '<span class="text-primary fw-bold">Ready:</span> Please head to the ICT Department for collection.';
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