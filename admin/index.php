<?php
session_start();
include '../includes/db_connect.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

// 1. Basic Stats
$total_assets = $pdo->query("SELECT COUNT(*) FROM assets")->fetchColumn();
$pending_requests = $pdo->query("SELECT COUNT(*) FROM borrowing_requests WHERE status = 'Pending'")->fetchColumn();
$low_stock_count = $pdo->query("SELECT COUNT(*) FROM assets WHERE current_stock > 0 AND current_stock <= 5")->fetchColumn();
$out_of_stock_count = $pdo->query("SELECT COUNT(*) FROM assets WHERE current_stock = 0")->fetchColumn();

$overdue_count = $pdo->query("SELECT COUNT(*) FROM borrowing_requests WHERE status = 'On Loan' AND return_date < CURDATE()")->fetchColumn();
$healthy_stock_count = $total_assets - ($low_stock_count + $out_of_stock_count);

// 2. Data for Category Bar Chart
$cat_query = $pdo->query("SELECT category, COUNT(*) as count FROM assets GROUP BY category");
$categories = [];
$counts = [];
while($row = $cat_query->fetch()) {
    $categories[] = $row['category'];
    $counts[] = $row['count'];
}

// Fetch Pending OR Overdue requests (Limited to 5)
$query = "SELECT b.request_id, b.request_date, b.return_date, b.quantity, b.status, u.full_name, a.asset_name 
          FROM borrowing_requests b 
          JOIN users u ON b.user_id = u.user_id 
          JOIN assets a ON b.asset_id = a.asset_id 
          WHERE b.status = 'Pending' 
          OR (b.status = 'On Loan' AND b.return_date < CURDATE())
          ORDER BY (CASE WHEN b.status = 'On Loan' THEN 1 ELSE 2 END), b.request_date DESC 
          LIMIT 5";
$pending_list = $pdo->query($query)->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuqtah Admin - Dashboard</title>
    <link rel="icon" type="image/png" href="/Nuqtah_IT/assets/img/Nuqtah_logo_small.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
<style>
    :root { 
        --sidebar-width: 260px; 
        --teal-primary: #00796B; 
        --teal-dark: #004D40;
        --teal-light: rgba(0, 121, 107, 0.08);
    }
    
    body { background-color: #f8f9fa; overflow-x: hidden; font-family: 'Inter', sans-serif; }

    /* Space layout metrics */
    .main-content { transition: all 0.3s ease; padding: 20px; }

    /* Desktop View adjustments */
    @media (min-width: 992px) {
        .main-content { margin-left: var(--sidebar-width); padding: 40px; }
    }

    /* Mobile view adjustments */
    @media (max-width: 991.98px) {
        .main-content { margin-left: 0; }
    }

    /* Modern stat cards */
    .card-stats { 
        border: none; 
        border-radius: 16px; 
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.05); 
        transition: transform 0.22s ease, box-shadow 0.22s ease; 
        background: #ffffff;
    }
    .card-stats:hover { 
        transform: translateY(-4px); 
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.08); 
    }
    .chart-container { position: relative; height: 300px; width: 100%; }
    .text-teal { color: var(--teal-primary) !important; }

    /* Translucent custom button styles */
    .btn-teal-light {
        background-color: var(--teal-light);
        color: var(--teal-primary);
        font-weight: 600;
        border: none;
        transition: all 0.2s;
    }
    .btn-teal-light:hover {
        background-color: var(--teal-primary);
        color: #ffffff;
    }

    /* Conditional alerting pulses */
    .card-pulse {
        animation: card-alert-pulse 2s infinite;
    }

    @keyframes card-alert-pulse {
        0% { box-shadow: 0 0.125rem 0.25rem rgba(220, 53, 69, 0.15); }
        50% { box-shadow: 0 0 16px rgba(220, 53, 69, 0.35); }
        100% { box-shadow: 0 0.125rem 0.25rem rgba(220, 53, 69, 0.15); }
    }

    /* Table styling */
    .table-hover tbody tr:hover {
        background-color: #f4faf9;
        transition: background-color 0.2s ease;
    }
</style>

</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="mobile-toggle d-lg-none mb-3">
        <button class="btn btn-white shadow-sm border text-teal" id="menuToggle">
            <i class="bi bi-list fs-3"></i>
        </button>
    </div>

    <header class="mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <h2 class="fw-bold text-dark mb-1">Dashboard Overview</h2>
            <p class="text-muted small mb-0">Real-time status of Nuqtah Inventory at ITQSHHB.</p>
        </div>
        <button onclick="window.location.reload()" class="btn btn-white shadow-sm rounded-pill px-4 py-2 mt-2 mt-md-0 border text-dark fw-semibold" style="font-size: 0.9rem;">
            <i class="bi bi-arrow-clockwise me-1 text-teal"></i> Refresh Data
        </button>
    </header>

    <!-- Cards Grid -->
    <div class="row g-3 mb-4">
        <!-- Card: Total Assets -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-stats p-3 border-start border-4 border-success">
                <div class="d-flex align-items-center">
                    <div class="p-3 bg-success bg-opacity-10 text-success rounded-3 me-3">
                        <i class="bi bi-box-seam fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small fw-semibold">Total Assets</p>
                        <h4 class="fw-bold text-dark mb-0"><?php echo $total_assets; ?></h4>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Card: Pending Approvals -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-stats p-3 border-start border-4 border-primary">
                <div class="d-flex align-items-center">
                    <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-3 me-3">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small fw-semibold">Pending Requests</p>
                        <h4 class="fw-bold text-dark mb-0"><?php echo $pending_requests; ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card: Overdue Returns -->
        <div class="col-sm-6 col-xl-3">
            <!-- Pulses only if overdue items exist -->
            <div class="card card-stats p-3 border-start border-4 border-danger <?php echo ($overdue_count > 0) ? 'card-pulse' : ''; ?>">
                <div class="d-flex align-items-center">
                    <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-3 me-3">
                        <i class="bi bi-calendar-x fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small fw-semibold">Overdue Loans</p>
                        <h4 class="fw-bold mb-0 <?php echo ($overdue_count > 0) ? 'text-danger' : 'text-dark'; ?>">
                            <?php echo $overdue_count; ?>
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card: Out of Stock -->
        <div class="col-sm-6 col-xl-3">
            <!-- Pulses only if critical outages exist -->
            <div class="card card-stats p-3 border-start border-4 border-danger <?php echo ($out_of_stock_count > 0) ? 'card-pulse' : ''; ?>">
                <div class="d-flex align-items-center">
                    <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-3 me-3">
                        <i class="bi bi-x-circle fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small fw-semibold">Out of Stock</p>
                        <h4 class="fw-bold mb-0 <?php echo ($out_of_stock_count > 0) ? 'text-danger' : 'text-dark'; ?>"><?php echo $out_of_stock_count; ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="row mb-4 g-4">
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white">
                <h5 class="fw-bold text-dark mb-4">Assets by Category</h5>
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white">
                <h5 class="fw-bold text-dark mb-4">Stock Health Distribution</h5>
                <div class="chart-container">
                    <canvas id="stockPieChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Required Desk -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">Action Required</h5>
                        <p class="text-muted mb-0 small">Outstanding administrative approvals and late returns requiring attention.</p>
                    </div>
                    <a href="view_requests.php" class="btn btn-sm btn-light rounded-pill px-4 py-2 border fw-semibold">View All Tasks</a>
                </div>
                
                <div class="table-responsive px-4 pb-4">
                    <?php if (count($pending_list) > 0): ?>
                        <table class="table align-middle table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Recipient Name</th>
                                    <th>Asset Description</th>
                                    <th class="d-none d-md-table-cell">Request Date</th>
                                    <th>Quantity</th>
                                    <th>Current Status</th>
                                    <th class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_list as $request): 
                                    $isOverdue = ($request['status'] == 'On Loan' && date('Y-m-d') > $request['return_date']);
                                    
                                    if ($isOverdue) {
                                        $status_badge = '<span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25 rounded-pill px-3 py-1.5">Overdue</span>';
                                    } else {
                                        $status_badge = '<span class="badge bg-warning-subtle text-warning border border-warning border-opacity-25 rounded-pill px-3 py-1.5">Pending Approval</span>';
                                    }
                                ?>
                                <tr>
                                    <td class="fw-bold text-dark ps-3">
                                        <?php echo htmlspecialchars($request['full_name']); ?>
                                    </td>
                                    <td class="text-secondary fw-semibold"><?php echo htmlspecialchars($request['asset_name']); ?></td>
                                    <td class="text-muted small d-none d-md-table-cell">
                                        <?php echo date('d M Y', strtotime($request['request_date'])); ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $isOverdue ? 'bg-danger-subtle text-danger border-danger' : 'bg-light text-dark'; ?> border px-2.5 py-1.5">
                                            <?php echo $request['quantity']; ?> Unit(s)
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo $status_badge; ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="view_requests.php" class="btn btn-sm btn-teal-light rounded-pill px-3 py-1.5">Manage</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <!-- Friendly Empty State design -->
                        <div class="text-center py-5">
                            <div class="bg-teal-light p-4 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                                <i class="bi bi-check-lg text-teal fs-2"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">All Caught Up!</h6>
                            <p class="text-muted small mb-0">No pending approvals or overdue loans require your attention.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Handles side menu toggles cleanly on mobile layouts
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.getElementById('menuToggle');
        if (menuToggle) {
            menuToggle.addEventListener('click', function() {
                const sidebar = document.querySelector('.sidebar');
                if (sidebar) {
                    sidebar.classList.toggle('show');
                }
            });
        }
    });

    // Charts Configuration
    const catCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(catCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($categories); ?>,
            datasets: [{
                label: 'Items Count',
                data: <?php echo json_encode($counts); ?>,
                backgroundColor: 'rgba(0, 121, 107, 0.85)',
                hoverBackgroundColor: '#004D40',
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false } 
            },
            scales: {
                x: {
                    grid: { display: false }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f1f1' },
                    ticks: {
                        stepSize: 1,
                        precision: 0
                    }
                }
            }
        }
    });

    const stockCtx = document.getElementById('stockPieChart').getContext('2d');
    new Chart(stockCtx, {
        type: 'doughnut',
        data: {
            labels: ['Healthy', 'Low Stock', 'Out of Stock'],
            datasets: [{
                data: [<?php echo $healthy_stock_count; ?>, <?php echo $low_stock_count; ?>, <?php echo $out_of_stock_count; ?>],
                backgroundColor: ['#00796B', '#F57C00', '#D32F2F'],
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '74%',
            plugins: { 
                legend: { 
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 18,
                        font: { size: 12, weight: '500' }
                    }
                } 
            }
        }
    });
</script>

</body>
</html>