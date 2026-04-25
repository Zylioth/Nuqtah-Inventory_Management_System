<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

// Default filter values
$date_from = $_GET['date_from'] ?? date('Y-m-01'); 
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$report_type = $_GET['report_type'] ?? 'borrowing';

// 1. Build the Query based on filters
if ($report_type == 'borrowing') {
    $sql = "SELECT b.*, u.full_name, u.user_id as student_id, a.asset_name, a.category 
            FROM borrowing_requests b
            JOIN users u ON b.user_id = u.user_id
            JOIN assets a ON b.asset_id = a.asset_id
            WHERE b.request_date BETWEEN :from AND :to
            ORDER BY b.request_date DESC";
} elseif ($report_type == 'logs') {
    $sql = "SELECT l.*, u.full_name 
            FROM admin_logs l
            JOIN users u ON l.admin_id = u.user_id
            WHERE l.timestamp BETWEEN :from AND :to
            ORDER BY l.timestamp DESC";
} else {
    $sql = "SELECT asset_id, asset_name, category, current_stock, status FROM assets";
}

$stmt = $pdo->prepare($sql);
if ($report_type == 'borrowing' || $report_type == 'logs') {
    $stmt->execute(['from' => $date_from . ' 00:00:00', 'to' => $date_to . ' 23:59:59']);
} else {
    $stmt->execute();
}
$data = $stmt->fetchAll();

// 2. Summary Logic
$total_count = count($data);
$late_count = 0;

if ($report_type == 'borrowing') {
foreach ($data as $row) {
    $d = date('Y-m-d', strtotime($row['return_date']));
    $a = !empty($row['actual_return_date']) ? date('Y-m-d', strtotime($row['actual_return_date'])) : null;
    $t = date('Y-m-d');

    if (($row['status'] == 'On Loan' && $t > $d) || ($a && $a > $d)) {
        $late_count++;
    }
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nuqtah - Audit Report</title>
    <link rel="icon" type="image/png" href="/Nuqtah_IT/assets/img/Nuqtah_logo_small.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root { --teal-primary: #00796B; }
        body { background-color: #f4f7f6; font-family: 'Inter', sans-serif; font-size: 14px; }
        
        .main-content { margin-left: 260px; padding: 20px; transition: all 0.3s; }
        
        .report-paper {
            background: white; padding: 60px; min-height: 297mm; width: 230mm;
            margin: 20px auto; box-shadow: 0 0 20px rgba(0,0,0,0.1); position: relative;
        }

        .btn-teal { background-color: var(--teal-primary) !important; color: white !important; border: none; }
        .btn-teal:hover { background-color: #004D40 !important; }

        .audit-table th { 
            background-color: #f8f9fa !important; font-size: 0.75rem; 
            text-transform: uppercase; border-top: 2px solid #333; padding: 12px;
        }
        .audit-table td { padding: 12px; vertical-align: middle; }
        
        @media print {
            .d-print-none, .sidebar { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; }
            .report-paper { margin: 0; box-shadow: none; width: 100%; padding: 40px; }
        }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid d-print-none mb-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">REPORT TYPE</label>
                        <select name="report_type" class="form-select border-0 bg-light rounded-3">
                            <option value="borrowing" <?php echo $report_type == 'borrowing' ? 'selected' : ''; ?>>Borrowing Activity</option>
                            <option value="assets" <?php echo $report_type == 'assets' ? 'selected' : ''; ?>>Asset Inventory</option>
                            <option value="logs" <?php echo $report_type == 'logs' ? 'selected' : ''; ?>>System Activity Log</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">FROM DATE</label>
                        <input type="date" name="date_from" value="<?php echo $date_from; ?>" class="form-control border-0 bg-light rounded-3">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">TO DATE</label>
                        <input type="date" name="date_to" value="<?php echo $date_to; ?>" class="form-control border-0 bg-light rounded-3">
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-teal w-100 rounded-pill fw-bold">Generate</button>
                        <button type="button" onclick="window.print()" class="btn btn-outline-dark rounded-pill px-3">
                            <i class="bi bi-printer-fill"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="report-paper">
        <div class="report-header d-flex justify-content-between align-items-start border-bottom border-3 pb-3 mb-4">
            <div>
                <img src="/Nuqtah_IT/assets/img/logoNuqtah.png" height="60" alt="Nuqtah Logo" class="mb-2">
                <p class="text-muted small">ICT Department Inventory System<br>ITQSHHB, Brunei Darussalam</p>
            </div>
            <div class="text-end">
                <h4 class="fw-light text-muted mb-1 text-uppercase">
                    <?php 
                        if($report_type == 'logs') echo "System Activity Log";
                        elseif($report_type == 'assets') echo "Asset Inventory Report";
                        else echo "Borrowing Audit Report";
                    ?>
                </h4>
                <p class="mb-0 small text-muted"><strong>Ref:</strong> #AUD-<?php echo date('Ymd-Hi'); ?></p>
                <p class="mb-0 small text-muted"><strong>Period:</strong> <?php echo date('d M', strtotime($date_from)); ?> - <?php echo date('d M Y', strtotime($date_to)); ?></p>
            </div>
        </div>

        <div class="row g-3 mb-5">
            <div class="col-4">
                <div class="p-4 border rounded-4 bg-light text-center">
                    <p class="text-muted small text-uppercase fw-bold mb-1">Total Entries</p>
                    <h2 class="fw-bold mb-0"><?php echo $total_count; ?></h2>
                </div>
            </div>
            <div class="col-4">
                <div class="p-4 border rounded-4 bg-light text-center">
                    <p class="text-muted small text-uppercase fw-bold mb-1">
                        <?php echo $report_type == 'logs' ? 'Total Actions' : 'Violations'; ?>
                    </p>
                    <h2 class="fw-bold mb-0 <?php echo ($report_type != 'logs' && $late_count > 0) ? 'text-danger' : ''; ?>">
                        <?php echo $report_type == 'logs' ? $total_count : $late_count; ?>
                    </h2>
                </div>
            </div>
            <div class="col-4">
                <div class="p-4 border rounded-4 bg-light text-center">
                    <p class="text-muted small text-uppercase fw-bold mb-1">Status</p>
                    <h2 class="fw-bold mb-0 text-success" style="font-size: 1.5rem; padding-top: 5px;">VERIFIED</h2>
                </div>
            </div>
        </div>

        <table class="table audit-table mt-4">
            <thead>
                <?php if ($report_type == 'borrowing'): ?>
                    <tr>
                        <th width="15%">REF ID</th>
                        <th width="25%">BORROWER</th>
                        <th width="20%">ASSET</th>
                        <th width="15%">DEADLINE</th>
                        <th width="15%">ACTUAL RETURN</th>
                        <th width="10%" class="text-end">STATUS</th>
                    </tr>
                <?php elseif ($report_type == 'logs'): ?>
                    <tr>
                        <th width="15%">LOG ID</th>
                        <th width="20%">TIMESTAMP</th>
                        <th width="20%">ADMIN</th>
                        <th colspan="3">ACTION TAKEN</th>
                    </tr>
                <?php else: ?>
                    <tr>
                        <th width="15%">ASSET ID</th>
                        <th width="35%">NAME</th>
                        <th width="20%">CATEGORY</th>
                        <th width="15%">STOCK</th>
                        <th colspan="2" class="text-end">STATUS</th>
                    </tr>
                <?php endif; ?>
            </thead>
            
            <tbody>
                <?php if (empty($data)): ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted">No records found.</td></tr>
                <?php else: ?>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <?php if ($report_type == 'borrowing'): ?>
                                <td class="text-muted small">#REQ-<?php echo $row['request_id']; ?></td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                    <div class="text-muted small">ID: <?php echo $row['student_id']; ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($row['asset_name']); ?></td>
                                <td class="small"><?php echo date('d M Y', strtotime($row['return_date'])); ?></td>
                                
                                <td class="small">
                                    <?php 
                                    if (!empty($row['actual_return_date'])) {
                                        // Define clean date strings here too
                                        $deadline_date = date('Y-m-d', strtotime($row['return_date']));
                                        $actual_date = date('Y-m-d', strtotime($row['actual_return_date']));
                                        
                                        // Only red if strictly past the deadline
                                        $isLate = ($actual_date > $deadline_date);
                                        $color = $isLate ? 'text-danger fw-bold' : 'text-success';
                                        
                                        echo "<span class='$color'>" . date('d M Y', strtotime($row['actual_return_date'])) . "</span>";
                                    } else { 
                                        echo '<span class="text-muted">—</span>'; 
                                    }
                                    ?>
                                </td>

                                <td class="text-end fw-bold small">
                                    <?php 
                                        $deadline_date = date('Y-m-d', strtotime($row['return_date']));
                                        $actual_date = !empty($row['actual_return_date']) ? date('Y-m-d', strtotime($row['actual_return_date'])) : null;
                                        $today_date = date('Y-m-d');

                                        if ($row['status'] == 'On Loan') {
                                            if ($today_date > $deadline_date) {
                                                echo '<span class="badge bg-danger">OVERDUE</span>';
                                            } else {
                                                echo '<span class="text-primary">ON LOAN</span>';
                                            }
                                        } 
                                        elseif ($row['status'] == 'Returned') {
                                            if ($actual_date && $actual_date > $deadline_date) {
                                                echo '<span class="text-danger">LATE</span>';
                                            } else {
                                                echo '<span class="text-success">RETURNED</span>';
                                            }
                                        } 
                                        else {
                                            echo '<span class="text-muted">' . strtoupper($row['status']) . '</span>';
                                        }
                                    ?>
                                </td>

                            <?php elseif ($report_type == 'logs'): ?>
                                <td class="text-muted small">#LOG-<?php echo $row['log_id']; ?></td>
                                <td class="small"><?php echo date('d/m/y H:i', strtotime($row['timestamp'])); ?></td>
                                <td class="fw-bold small"><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td colspan="3" class="small"><?php echo htmlspecialchars($row['action_taken']); ?></td>
                            
                            <?php else: ?>
                                <td class="small">#AST-<?php echo $row['asset_id']; ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($row['asset_name']); ?></td>
                                <td class="small"><?php echo $row['category']; ?></td>
                                <td><?php echo $row['current_stock']; ?></td>
                                <td colspan="2" class="text-end small fw-bold text-uppercase"><?php echo $row['status']; ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>