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
$healthy_stock_count = $total_assets - ($low_stock_count + $out_of_stock_count);

// 2. Data for Category Bar Chart
$cat_query = $pdo->query("SELECT category, COUNT(*) as count FROM assets GROUP BY category");
$categories = [];
$counts = [];
while($row = $cat_query->fetch()) {
    $categories[] = $row['category'];
    $counts[] = $row['count'];
}

// 3. Fetch Recent Pending Requests
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
    <link rel="icon" type="image/png" href="/Nuqtah_IT/assets/img/Nuqtah_logo_small.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
<style>
    :root { 
        --sidebar-width: 260px; 
        --teal-primary: #00796B; 
    }
    
    body { background-color: #f8f9fa; overflow-x: hidden; }

    /* Keep: Controls the space for the main content */
    .main-content { transition: all 0.3s ease; padding: 20px; }

    /* Keep: Desktop view pushes content to the right */
    @media (min-width: 992px) {
        .main-content { margin-left: var(--sidebar-width); padding: 40px; }
    }

    /* Keep: Mobile view content takes full width */
    @media (max-width: 991.98px) {
        .main-content { margin-left: 0; }
    }

    /* Keep: Dashboard specific styles */
    .card-stats { border: none; border-radius: 15px; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); transition: transform 0.2s; }
    .card-stats:hover { transform: translateY(-5px); }
    .chart-container { position: relative; height: 300px; width: 100%; }
    .text-teal { color: var(--teal-primary) !important; }
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
            <h2 class="fw-bold">Dashboard Overview</h2>
            <p class="text-muted small">Real-time status of Nuqtah Inventory at ITQSHHB.</p>
        </div>
        <button onclick="window.location.reload()" class="btn btn-white shadow-sm rounded-pill px-3 mt-2 mt-md-0 border">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
        </button>
    </header>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
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
        <div class="col-sm-6 col-xl-3">
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
        <div class="col-sm-6 col-xl-3">
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
        <div class="col-sm-6 col-xl-3">
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

    <div class="row mb-4 g-4">
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4">
                <h5 class="fw-bold mb-4">Assets by Category</h5>
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4">
                <h5 class="fw-bold mb-4">Stock Health</h5>
                <div class="chart-container">
                    <canvas id="stockPieChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Recent Requests</h5>
                    <a href="view_requests.php" class="btn btn-sm btn-light rounded-pill px-3">View All</a>
                </div>
                <div class="table-responsive px-3 pb-3">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Asset</th>
                                <th class="d-none d-md-table-cell">Date</th>
                                <th>Qty</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_list as $request): ?>
                            <tr>
                                <td class="fw-bold text-dark"><?php echo htmlspecialchars($request['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($request['asset_name']); ?></td>
                                <td class="text-muted small d-none d-md-table-cell"><?php echo date('d M', strtotime($request['request_date'])); ?></td>
                                <td><span class="badge bg-light text-dark border"><?php echo $request['quantity']; ?></span></td>
                                <td class="text-end">
                                    <a href="view_requests.php" class="btn btn-sm btn-teal-light rounded-pill">Manage</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>

    // Charts
    const catCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(catCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($categories); ?>,
            datasets: [{
                label: 'Items',
                data: <?php echo json_encode($counts); ?>,
                backgroundColor: '#00796B',
                borderRadius: 8
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });

    const stockCtx = document.getElementById('stockPieChart').getContext('2d');
    new Chart(stockCtx, {
        type: 'doughnut',
        data: {
            labels: ['Healthy', 'Low', 'Out'],
            datasets: [{
                data: [<?php echo $healthy_stock_count; ?>, <?php echo $low_stock_count; ?>, <?php echo $out_of_stock_count; ?>],
                backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                borderWidth: 0
            }]
        },
        options: {
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: { legend: { position: 'bottom' } }
        }
    });
</script>

</body>
</html>