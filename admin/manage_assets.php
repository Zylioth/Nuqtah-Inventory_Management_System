<?php
session_start();
include '../includes/db_connect.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch all assets ordered by category
$query = "SELECT * FROM assets ORDER BY category ASC, asset_name ASC";
$stmt = $pdo->query($query);
$assets = $stmt->fetchAll();

$low_stock_threshold = 5;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Inventory - Nuqtah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --sidebar-width: 260px; --teal-primary: #00796B; }
        body { background-color: #f8f9fa; }
        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; top: 0; left: 0; background-color: white; border-right: 1px solid #eee; z-index: 1000; }
        .main-content { margin-left: var(--sidebar-width); padding: 30px; }
        .nav-link { color: #555; padding: 10px 20px; margin: 2px 10px; border-radius: 8px; }
        .nav-link.active { background-color: var(--teal-primary) !important; color: white !important; }
        .asset-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
        .badge-low { background-color: #FFF3E0; color: #E65100; border: 1px solid #FFE0B2; }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Inventory Management</h2>
            <p class="text-muted">View and update the ITQSHHB equipment list.</p>
        </div>
        <a href="add_assets.php" class="btn btn-teal rounded-pill px-4 py-2 text-white" style="background-color: #00796B;">
            <i class="bi bi-plus-lg me-2"></i> Add New Asset
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" id="adminSearch" class="form-control border-start-0 ps-0" placeholder="Search by name, category or ID...">
                    </div>
                </div>
                <div class="col-md-4">
                    <select class="form-select">
                        <option selected>All Categories</option>
                        <option>Laptops</option>
                        <option>Projectors</option>
                        <option>Accessories</option>
                        <option>Consumables</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Image</th>
                        <th>Asset Name</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assets as $row): ?>
                        <?php 
                            $stock = $row['current_stock'];
                            $is_consumable = ($row['category'] === 'Consumables' || $row['category'] === 'Stationery');
                            
                            // Determine Badge Status
                            if ($stock <= 0) {
                                $status_label = $is_consumable ? "Out of Stock" : "Not Available";
                                $badge_class = "bg-danger text-white";
                            } elseif ($stock <= $low_stock_threshold) {
                                $status_label = "Low Stock";
                                $badge_class = "badge-low";
                            } else {
                                $status_label = "Available";
                                $badge_class = "bg-success text-white";
                            }

                            $img = !empty($row['asset_image']) ? "../assets/img/" . $row['asset_image'] : "../assets/img/no_image_available.jpg";
                        ?>
                        <tr>
                            <td class="ps-4">
                                <img src="<?php echo $img; ?>" class="asset-thumb border" alt="Asset">
                            </td>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($row['asset_name']); ?></div>
                                <small class="text-muted">ID: #<?php echo str_pad($row['asset_id'], 4, '0', STR_PAD_LEFT); ?></small>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?php echo $row['category']; ?></span></td>
                            <td><span class="fw-bold"><?php echo $stock; ?></span> units</td>
                            <td><span class="badge <?php echo $badge_class; ?> rounded-pill"><?php echo $status_label; ?></span></td>
                            <td class="text-end pe-4">
                                <a href="edit_asset.php?id=<?php echo $row['asset_id']; ?>" class="btn btn-light btn-sm border me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-light btn-sm border text-danger" onclick="confirmDelete(<?php echo $row['asset_id']; ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this asset? This action cannot be undone.')) {
            window.location.href = 'delete_asset.php?id=' + id;
        }
    }
</script>

</body>
</html>