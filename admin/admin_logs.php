<?php
session_start();
// Go back one folder to find the connection
include '../includes/db_connect.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

$query = "SELECT l.*, u.username 
          FROM admin_logs l 
          JOIN users u ON l.admin_id = u.user_id 
          ORDER BY l.timestamp DESC";
$logs = $pdo->query($query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Audit Log | Nuqtah</title>
    <link rel="icon" type="image/png" href="/Nuqtah_IT/assets/img/Nuqtah_logo_small.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; }
        .audit-container { max-width: 1100px; margin: 40px auto; }
        .card { border: none; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .table thead th { 
            background-color: #f8f9fa; 
            color: #666; 
            text-transform: uppercase; 
            font-size: 0.75rem; 
            padding: 1.2rem;
            border-bottom: 2px solid #edf2f7;
        }
        .admin-user { color: #00796B; font-weight: 600; }
        .action-text { color: #2d3748; }
        
        @media print {
            .no-print { display: none !important; }
            body { background-color: white; }
            .audit-container { margin: 0; max-width: 100%; }
            .card { box-shadow: none; border: 1px solid #eee; }
        }
    </style>
</head>
<body>

<div class="container audit-container">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <a href="index.php" class="btn btn-white shadow-sm rounded-pill px-4 bg-white text-dark text-decoration-none border">
            <i class="bi bi-arrow-left me-2"></i>Dashboard
        </a>
        <div class="text-end">
            <h4 class="fw-bold mb-0">System Audit Log</h4>
            <small class="text-muted text-uppercase" style="letter-spacing: 1px;">Internal Use Only</small>
        </div>
        <button onclick="window.print()" class="btn btn-dark rounded-pill px-4 shadow-sm">
            <i class="bi bi-printer me-2"></i>Print Report
        </button>
    </div>

    <div class="card overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Timestamp</th>
                            <th>Administrator</th>
                            <th>Activity Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="ps-4">
                                <span class="text-muted d-block small"><?php echo date('D, d M Y', strtotime($log['timestamp'])); ?></span>
                                <span class="fw-bold"><?php echo date('h:i A', strtotime($log['timestamp'])); ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center admin-user">
                                    <i class="bi bi-person-badge me-2"></i>
                                    <?php echo htmlspecialchars($log['username']); ?>
                                </div>
                            </td>
                            <td class="action-text py-3">
                                <?php echo htmlspecialchars($log['action_taken']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <p class="text-center mt-4 text-muted small no-print">
        End of report. Generated on <?php echo date('d M Y, h:i A'); ?>
    </p>
</div>

</body>
</html>