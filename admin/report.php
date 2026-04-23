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
    // NEW: System Activity Log Query
    $sql = "SELECT l.*, u.full_name 
            FROM admin_logs l
            JOIN users u ON l.admin_id = u.user_id
            WHERE l.timestamp BETWEEN :from AND :to
            ORDER BY l.timestamp DESC";
} else {
    // Basic Asset Inventory Report
    $sql = "SELECT asset_id, asset_name, category, current_stock, status FROM assets";
}

$stmt = $pdo->prepare($sql);
if ($report_type == 'borrowing' || $report_type == 'logs') {
    // Add time to date_to to include the whole last day
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
        if ($row['status'] == 'On Loan' && date('Y-m-d') > $row['return_date']) {
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
        body { background-color: #f4f7f6; font-family: 'Inter', sans-serif; }
        .report-paper {
            background: white; padding: 50px; min-height: 297mm; width: 210mm;
            margin: 20px auto; box-shadow: 0 0 20px rgba(0,0,0,0.1); position: relative;
        }
        .main-content { margin-left: 260px; padding: 20px; transition: all 0.3s; }
        .report-header { border-bottom: 3px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
        .btn-teal { background-color: var(--teal-primary); color: white; border: none; }
        .btn-teal:hover { background-color: #004D40; color: white; }
        .audit-table th { background-color: #f8f9fa !important; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; border-top: 1px solid #dee2e6; }
        
        @media print {
            .d-print-none, .sidebar { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; }
            .report-paper { margin: 0; box-shadow: none; width: 100%; padding: 20px; }
            body { background: white; }
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
        <div class="report-header d-flex justify-content-between align-items-start">
            <div>
                <img src="/Nuqtah_IT/assets/img/logoNuqtah.png" height="50" alt="Nuqtah Logo" class="mb-2">
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
                <p class="mb-0 small text-muted"><strong>Generated By:</strong> <?php echo $_SESSION['full_name'] ?? 'Admin'; ?></p>
            </div>
        </div>

        <div class="row g-3 mb-5">
            <div class="col-4">
                <div class="p-3 border rounded-3 bg-light text-center">
                    <p class="text-muted small text-uppercase fw-bold mb-1">Total Entries</p>
                    <h3 class="fw-bold mb-0"><?php echo $total_count; ?></h3>
                </div>
            </div>
            <div class="col-4">
                <div class="p-3 border rounded-3 bg-light text-center">
                    <p class="text-muted small text-uppercase fw-bold mb-1">
                        <?php echo $report_type == 'logs' ? 'Action Events' : 'Overdue Issues'; ?>
                    </p>
                    <h3 class="fw-bold mb-0 <?php echo $late_count > 0 ? 'text-danger' : ''; ?>">
                        <?php echo $report_type == 'logs' ? $total_count : $late_count; ?>
                    </h3>
                </div>
            </div>
            <div class="col-4">
                <div class="p-3 border rounded-3 bg-light text-center">
                    <p class="text-muted small text-uppercase fw-bold mb-1">Report Status</p>
                    <h3 class="fw-bold mb-0 text-success">Verified</h3>
                </div>
            </div>
        </div>

        <table class="table audit-table mt-4">
            <thead>
                <?php if ($report_type == 'borrowing'): ?>
                    <tr>
                        <th>Ref ID</th>
                        <th>Borrower Details</th>
                        <th>Asset Name</th>
                        <th>Request Date</th>
                        <th>Return Date</th>
                        <th class="text-end">Status</th>
                    </tr>
                <?php elseif ($report_type == 'logs'): ?>
                    <tr>
                        <th>Log ID</th>
                        <th>Timestamp</th>
                        <th>Administrator</th>
                        <th>Action Description</th>
                    </tr>
                <?php else: ?>
                    <tr>
                        <th>Asset ID</th>
                        <th>Asset Name</th>
                        <th>Category</th>
                        <th>Stock Level</th>
                        <th class="text-end">Status</th>
                    </tr>
                <?php endif; ?>
            </thead>
            <tbody>
                <?php if (empty($data)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No records found for this period.</td></tr>
                <?php else: ?>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <?php if ($report_type == 'borrowing'): ?>
                                <td class="text-muted small">#REQ-<?php echo $row['request_id']; ?></td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                    <div class="text-muted" style="font-size: 0.7rem;">ID: <?php echo $row['student_id']; ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($row['asset_name']); ?></td>
                                <td class="small"><?php echo date('d M Y', strtotime($row['request_date'])); ?></td>
                                <td class="small"><?php echo date('d M Y', strtotime($row['return_date'])); ?></td>
                                <td class="text-end fw-bold small">
                                    <?php 
                                        $isLate = ($row['status'] == 'On Loan' && date('Y-m-d') > $row['return_date']);
                                        echo $isLate ? '<span class="text-danger">OVERDUE</span>' : $row['status'];
                                    ?>
                                </td>
                            <?php elseif ($report_type == 'logs'): ?>
                                <td class="text-muted small">#LOG-<?php echo $row['log_id']; ?></td>
                                <td class="small"><?php echo date('d/m/Y H:i', strtotime($row['timestamp'])); ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td class="small"><?php echo htmlspecialchars($row['action_taken']); ?></td>
                            <?php else: ?>
                                <td>#AST-<?php echo $row['asset_id']; ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($row['asset_name']); ?></td>
                                <td><?php echo $row['category']; ?></td>
                                <td><?php echo $row['current_stock']; ?> Units</td>
                                <td class="text-end small fw-bold"><?php echo $row['status']; ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="mt-5 pt-5">
            <div class="row mt-5">
                <div class="col-6">
                    <div style="border-top: 1px solid #333; width: 200px; padding-top: 5px;" class="text-center small">
                        Officer Signature
                    </div>
                </div>
                <div class="col-6 text-end">
                    <p class="small text-muted italic">This is a system-generated document. No signature required for digital audit.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>