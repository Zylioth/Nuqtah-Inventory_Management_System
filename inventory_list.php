<?php
session_start();
// This file creates the $pdo object
include 'includes/db_connect.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_role = $_SESSION['role'] ?? 'Staff';

// PDO syntax to fetch all assets
$stmt = $pdo->query("SELECT * FROM assets");
$assets = $stmt->fetchAll();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuqtah - Inventory List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        /* COLOR IKUT HIFI */
        .bg-teal { background-color: #00796B !important; }
        .btn-teal { background-color: #00796B; border: none; transition: 0.3s; color: white; }
        .btn-teal:hover { background-color: #004D40; color: white; }
        
        /* Card Hover Effect restricted to index/inventory page */
        .asset-card { transition: transform 0.3s ease, box-shadow 0.3s ease; cursor: pointer; }
        .asset-card:hover { transform: translateY(-10px); box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important; }
        
        .status-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
        .x-small { font-size: 0.75rem; }
    </style>
</head>
<body class="bg-light">

<?php include 'includes/header.php'; ?> 

<div class="container mt-4">
    <div class="row justify-content-center mb-4">
        <div class="col-md-10">
            <input type="text" id="searchInput" class="form-control rounded-pill border-secondary px-4 py-2" placeholder="Search for equipment.....">
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-center gap-2 mb-5">
        <button class="btn btn-teal rounded-3 px-4">All Equipment</button>
        <button class="btn btn-outline-dark bg-white rounded-3 px-4"><i class="bi bi-laptop me-2"></i> Laptops</button>
        <button class="btn btn-outline-dark bg-white rounded-3 px-4"><i class="bi bi-projector me-2"></i> Projectors</button>
        <button class="btn btn-outline-dark bg-white rounded-3 px-4"><i class="bi bi-plugin me-2"></i> Accessories</button>
    </div>

   <div class="row g-4" id="inventoryGrid">
    <?php if (count($assets) > 0): ?>
        <?php foreach ($assets as $row): ?>
            <?php 
                $is_available = ($row['current_stock'] > 0 && $row['status'] == 'Available');
                $status_class = $is_available ? 'bg-success' : 'bg-danger';
                $text_class = $is_available ? 'text-success' : 'text-danger';
            ?>
            <div class="col-md-4 asset-item">
                <div class="card asset-card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <img src="assets/img/<?php echo htmlspecialchars($row['asset_image']); ?>" class="card-img-top" alt="Equipment" style="height: 200px; object-fit: cover;">
                    <div class="card-body px-4 pb-4">
                        <p class="text-muted x-small mb-1"><?php echo htmlspecialchars($row['category']); ?></p>
                        <h5 class="card-title fw-bold mb-3"><?php echo htmlspecialchars($row['asset_name']); ?></h5>
                        
                        <div class="d-flex align-items-center small mb-4">
                            <span class="status-dot me-2 <?php echo $status_class; ?>"></span>
                            <span class="<?php echo $text_class; ?>">
                                <?php echo $is_available ? 'Available' : 'Currently in use'; ?>
                            </span>
                        </div>

                        <button class="btn btn-teal w-100 rounded-pill py-2 fw-bold" <?php echo !$is_available ? 'disabled' : ''; ?>>
                            + Add to Borrowing Cart
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12 text-center py-5">
            <p class="text-muted">No equipment found in the database.</p>
        </div>
    <?php endif; ?>
</div>

<?php if ($user_role === 'Admin'): ?>
<div class="position-fixed bottom-0 end-0 p-4" style="z-index: 1050;">
    <a href="admin/dashboard.php" class="btn btn-teal rounded-pill shadow-lg px-4 py-3 d-flex align-items-center">
        <i class="bi bi-speedometer2 fs-5 me-2"></i>
        <span class="fw-bold">ADMIN DASHBOARD</span>
    </a>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'includes/footer.php'; ?>

</body>



</html>