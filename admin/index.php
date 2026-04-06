<?php
session_start();
include '../includes/db_connect.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

// 1. Fetch Statistics
$total_assets = $pdo->query("SELECT COUNT(*) FROM assets")->fetchColumn();

// Corrected table name to borrowing_requests
$pending_requests = $pdo->query("SELECT COUNT(*) FROM borrowing_requests WHERE status = 'Pending'")->fetchColumn();

$low_stock_count = $pdo->query("SELECT COUNT(*) FROM assets WHERE current_stock > 0 AND current_stock <= 5")->fetchColumn();
$out_of_stock_count = $pdo->query("SELECT COUNT(*) FROM assets WHERE current_stock = 0")->fetchColumn();

// 2. Fetch Recent Pending Requests
$query = "SELECT b.request_id, b.request_date, b.quantity, u.full_name, a.asset_name 
          FROM borrowing_requests b 
          JOIN users u ON b.user_id = u.user_id 
          JOIN assets a ON b.asset_id = a.asset_id 
          WHERE b.status = 'Pending' 
          ORDER BY b.request_date DESC LIMIT 5";
$pending_list = $pdo->query($query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuqtah Admin - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --sidebar-width: 260px; --teal-primary: #00796B; --teal-dark: #004D40; }
        body { background-color: #f8f9fa; }

                /* Sidebar Fixed Positioning */
        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            border-right: 1px solid #eee;
        }

        /* Push the main content to the right */
        .main-content {
            margin-left: 260px;
            padding: 30px;
        }

        /* Nav Link Styling */
        .nav-link {
            color: #555;
            padding: 10px 20px;
            margin: 2px 10px;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .nav-link:hover {
            background-color: #f8f9fa;
            color: #00796B;
        }

        .nav-link.active {
            background-color: #00796B !important;
            color: white !important;
        }

        .x-small {
            font-size: 0.7rem;
        }

        /* Stats Cards */
        .card-stats { border: none; border-radius: 15px; height: 100%; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
        .bg-teal { background-color: var(--teal-primary) !important; }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] == 'success'): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong>Success!</strong> The request has been processed and stock updated.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif ($_GET['msg'] == 'error'): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
                <i class="bi bi-exclamation-octagon-fill me-2"></i>
                <strong>Error!</strong> Something went wrong while updating the inventory.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <header class="mb-4">
        <h2 class="fw-bold">Dashboard</h2>
        <p class="text-muted small">Welcome back to the ITQSHHB Inventory Management System.</p>
    </header>

<!-- Quick Sumary of Assets kira tracking -->

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card card-stats p-3 border-start border-4 border-success">
                <div class="d-flex align-items-center">
                    <div class="p-3 bg-success bg-opacity-10 text-success rounded-3 me-3">
                        <i class="bi bi-box-seam fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small">Total Assets</p>
                        <h4 class="fw-bold mb-0"><?php echo $total_assets; ?></h4>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card card-stats p-3 border-start border-4 border-primary">
                <div class="d-flex align-items-center">
                    <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-3 me-3">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small">Pending</p>
                        <h4 class="fw-bold mb-0"><?php echo $pending_requests; ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stats p-3 border-start border-4 border-warning">
                <div class="d-flex align-items-center">
                    <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-3 me-3">
                        <i class="bi bi-exclamation-triangle fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small">Low Stock</p>
                        <h4 class="fw-bold mb-0"><?php echo $low_stock_count; ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stats p-3 border-start border-4 border-danger">
                <div class="d-flex align-items-center">
                    <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-3 me-3">
                        <i class="bi bi-x-circle fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small">Out of Stock</p>
                        <h4 class="fw-bold mb-0"><?php echo $out_of_stock_count; ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Latest Borrowing Requests -->

    <div class="row">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Recent Borrowing Requests</h5>
                    <a href="view_requests.php" class="text-teal small text-decoration-none">View All</a>
                </div>
                <div class="table-responsive px-3 pb-3">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Asset</th>
                                <th>Request Date</th>
                                <th>Quantity</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($pending_list) > 0): ?>
                                <?php foreach ($pending_list as $request): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo htmlspecialchars($request['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($request['asset_name']); ?></td>
                                    <td class="text-muted"><?php echo date('d M Y', strtotime($request['request_date'])); ?></td>
                                    
                                    <td>
                                        <span class="badge px-3 py-2 rounded-pill" 
                                            style="background-color: rgba(0, 121, 107, 0.1); color: #004D40; border: 1px solid rgba(0, 121, 107, 0.2);">
                                            <i class="bi bi-hash x-small"></i> <?php echo htmlspecialchars($request['quantity']); ?>
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        <a href="actions/process_request.php?id=<?php echo $request['request_id']; ?>&status=Approved" class="btn btn-success btn-sm rounded-pill px-3">Approve</a>
                                        <a href="actions/process_request.php?id=<?php echo $request['request_id']; ?>&status=Rejected" class="btn btn-outline-danger btn-sm rounded-pill px-3">Reject</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">No pending requests found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>