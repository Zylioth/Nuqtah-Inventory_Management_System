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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

<!-- message kalau success delete -->
        <script>
        // Wait for the DOM to load
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            
            // Check if the 'msg' parameter exists in the URL
            if (urlParams.has('msg')) {
                const msg = urlParams.get('msg');
                
                if (msg === 'deleted') {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'The asset has been removed successfully.',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 4000,
                        showClass: {
                            popup: 'animate__animated animate__fadeInDown'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__fadeOutUp'
                        }
                    });
                }
                
                if (msg === 'success') {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Asset details have been updated.',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }

                // Optional: Clean the URL after showing the alert
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
        </script>


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
                        <th>Stock (Current / Total)</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assets as $row): ?>
                        <?php 
                            $current = $row['current_stock'];
                            $total = $row['total_stock'];
                            $is_consumable = ($row['category'] === 'Consumables' || $row['category'] === 'Stationery');
                            
                            // Determine Badge Status based on current availability
                            if ($current <= 0) {
                                $status_label = $is_consumable ? "Out of Stock" : "Not Available";
                                $badge_class = "bg-danger text-white";
                            } elseif ($current <= $low_stock_threshold) {
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
                            
                            <td>
                                <span class="fw-bold text-dark"><?php echo $current; ?></span> 
                                <span class="text-muted">/ <?php echo $total; ?></span>
                                <div class="progress mt-1" style="height: 4px; width: 60px;">
                                    <?php 
                                        $percent = ($total > 0) ? ($current / $total) * 100 : 0;
                                        $bar_color = ($percent <= 20) ? 'bg-danger' : 'bg-teal';
                                    ?>
                                    <div class="progress-bar <?php echo $bar_color; ?>" role="progressbar" style="width: <?php echo $percent; ?>%"></div>
                                </div>
                            </td>

                            <td><span class="badge <?php echo $badge_class; ?> rounded-pill"><?php echo $status_label; ?></span></td>
                            <td class="text-end pe-4">
                                <a href="edit_asset.php?id=<?php echo $row['asset_id']; ?>" class="btn btn-light btn-sm border me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-light btn-sm border text-danger" onclick="showDeleteModal(<?php echo $row['asset_id']; ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <p class="text-muted">Are you sure you want to delete this asset? This action will permanently remove the record from the Nuqtah database and cannot be undone.</p>
                    <div class="d-flex align-items-center bg-light p-3 rounded-3">
                        <i class="bi bi-exclamation-triangle-fill text-warning fs-4 me-3"></i>
                        <span class="small fw-bold text-dark">Warning: This may affect existing borrowing history records.</span>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger rounded-pill px-4">Delete Permanently</a>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function showDeleteModal(id) {
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    deleteBtn.href = 'delete_asset.php?id=' + id;
    const myModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    myModal.show();
}
</script>

<script>
function showDeleteModal(id) {
    // 1. Get the delete button inside the modal
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    
    // 2. Set the href to your delete_asset.php with the correct ID
    deleteBtn.href = 'delete_asset.php?id=' + id;
    
    // 3. Show the modal using Bootstrap's JS API
    const myModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    myModal.show();
}
</script>

</body>
</html>