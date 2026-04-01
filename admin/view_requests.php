<?php
session_start();
include '../includes/db_connect.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch requests with User and Asset details
$query = "SELECT r.*, u.full_name, a.asset_name, a.category 
          FROM borrowing_requests r
          JOIN users u ON r.user_id = u.user_id
          JOIN assets a ON r.asset_id = a.asset_id
          ORDER BY r.request_date DESC";
$stmt = $pdo->query($query);
$requests = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Borrowing Requests - Nuqtah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --sidebar-width: 260px; --teal-primary: #00796B; }
        body { background-color: #f8f9fa; }
        
        /* Add this section to fix the sidebar positioning */
        .sidebar { 
            width: var(--sidebar-width); 
            height: 100vh; 
            position: fixed; 
            top: 0; 
            left: 0; 
            background-color: white; 
            border-right: 1px solid #eee; 
            z-index: 1000; 
        }

        .main-content { margin-left: var(--sidebar-width); padding: 30px; }
        .status-badge { font-size: 0.85rem; padding: 6px 12px; }
        
        /* Ensure nav links look consistent */
        .nav-link { color: #555; padding: 10px 20px; margin: 2px 10px; border-radius: 8px; text-decoration: none; display: block; }
        .nav-link.active { background-color: var(--teal-primary) !important; color: white !important; }
        .nav-link:hover:not(.active) { background-color: #f1f1f1; }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="mb-4">
        <h2 class="fw-bold">Borrowing Requests</h2>
        <p class="text-muted">Review and manage equipment loan applications.</p>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Requester</th>
                        <th>Asset Item</th>
                        <th>Request Date</th>
                        <th>Expected Return</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $row): ?>
                        <?php 
                            $status = $row['status'];
                            $badge_class = "bg-secondary";
                            if ($status == 'Pending') $badge_class = "bg-warning text-dark";
                            elseif ($status == 'Approved' || $status == 'On Loan') $badge_class = "bg-success";
                            elseif ($status == 'Rejected') $badge_class = "bg-danger";
                            elseif ($status == 'Returned') $badge_class = "bg-info text-dark";
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                <small class="text-muted">User ID: #<?php echo $row['user_id']; ?></small>
                            </td>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($row['asset_name']); ?></div>
                                <span class="badge bg-light text-dark border"><?php echo $row['category']; ?></span>
                            </td>
                            <td><?php echo date('d M Y', strtotime($row['request_date'])); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['return_date'])); ?></td>
                            <td>
                                <span class="badge <?php echo $badge_class; ?> rounded-pill status-badge">
                                    <?php echo $status; ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <?php if ($status == 'Pending'): ?>
                                    <a href="process_request.php?id=<?php echo $row['request_id']; ?>&action=approve" class="btn btn-sm btn-success rounded-pill px-3">Approve</a>
                                    <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="rejectRequest(<?php echo $row['request_id']; ?>)">Reject</button>
                                <?php else: ?>
                                    <span class="text-muted small italic">Processed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function rejectRequest(id) {
    const reason = prompt("Please enter the reason for rejection:");
    if (reason != null) {
        window.location.href = `process_request.php?id=${id}&action=reject&note=${encodeURIComponent(reason)}`;
    }
}
</script>

</body>
</html>