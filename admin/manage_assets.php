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
                       <!-- Search features -->
                        <input type="text" id="adminSearch" class="form-control border-start-0 ps-0" 
                            placeholder="Search by name, category or ID..." 
                            onkeyup="filterAssets()">     

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

                            $img = !empty($row['asset_image']) ? "../assets/upload/" . $row['asset_image'] : "../assets/img/no_image_available.jpg";
                        ?>
                        <tr>
                            <td class="ps-4">
                                <img src="<?php echo $img; ?>" class="asset-thumb border" alt="Asset">
                            </td>

                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($row['asset_name']); ?></div>
                                <small class="text-muted">ID: #<?php echo str_pad($row['asset_id'], 4, '0', STR_PAD_LEFT); ?></small>
                                <br>
                                <button class="btn btn-link btn-sm p-0 text-decoration-none text-teal fw-bold" 
                                        onclick="viewAssetTags(<?php echo $row['asset_id']; ?>, '<?php echo addslashes($row['asset_name']); ?>')"
                                        style="color: var(--teal-primary); font-size: 0.75rem;">
                                    <i class="bi bi-tag-fill me-1"></i>View Asset Tags
                                </button>
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

<div class="modal fade" id="assetTagModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white rounded-top-4">
                <h5 class="modal-title fw-bold" id="tagModalTitle">Asset Tags</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 border-bottom bg-light">
                    <form id="addTagForm" class="row g-2">
                        <input type="hidden" name="asset_id" id="add_tag_asset_id">
                        <div class="col-8">
                            <input type="text" name="unique_tag" class="form-control form-control-sm" placeholder="Enter Tag (e.g. ITQSHHB-001L)" required>
                        </div>
                        <div class="col-4">
                            <button type="submit" class="btn btn-teal btn-sm w-100 text-white" style="background-color: #00796B;">
                                <i class="bi bi-plus-circle me-1"></i>Add Tag
                            </button>
                        </div>
                    </form>
                </div>
                
                <div id="tagListBody">
                    <div class="p-5 text-center"><div class="spinner-border text-teal"></div></div>
                </div>
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

function viewAssetTags(assetId, assetName) {
    document.getElementById('tagModalTitle').innerText = "Serials: " + assetName;
    document.getElementById('add_tag_asset_id').value = assetId; // Set the ID for the form
    
    loadTags(assetId);
    
    var tagModal = new bootstrap.Modal(document.getElementById('assetTagModal'));
    tagModal.show();
}

function loadTags(assetId) {
    fetch(`actions/get_asset_tags.php?asset_id=${assetId}`)
        .then(res => res.text())
        .then(data => {
            document.getElementById('tagListBody').innerHTML = data;
            
            // After loading the table, let's update a "Slots Remaining" counter
            // We can pull the stock numbers from the table row hidden in the background
            // or just let the PHP handle the count display.
        });
}

// Handle the "Add Tag" form submission via AJAX
document.getElementById('addTagForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const assetId = formData.get('asset_id');

    fetch('actions/add_asset_tag.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            // Success Toast (Same as the delete one)
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
            Toast.fire({
                icon: 'success',
                title: 'Tag added successfully'
            });

            this.reset();
            loadTags(assetId);
        } else {
            // Professional Error Alert
            Swal.fire({
                title: 'Cannot Add Tag',
                text: data.message, // This pulls the "Limit reached" message from PHP
                icon: 'error',
                confirmButtonColor: '#00796B'
            });
        }
    })
    .catch(err => {
        Swal.fire('Error!', 'Something went wrong with the server.', 'error');
    });
});

function deleteTag(tagId, assetId) {
    Swal.fire({
        title: 'Remove Tag?',
        text: "This serial number will be permanently deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#00796B', // Matches your teal theme
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('tag_id', tagId);

            fetch('actions/delete_asset_tag.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Success Toast
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: 'Tag removed successfully'
                    });
                    
                    // Refresh the list in the modal
                    loadTags(assetId);
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            })
            .catch(err => {
                Swal.fire('Error!', 'Could not connect to the server.', 'error');
            });
        }
    });
}

function filterAssets() {
    const input = document.getElementById("adminSearch");
    const filter = input.value.toLowerCase();
    const table = document.querySelector("table tbody");
    const rows = table.getElementsByTagName("tr");

    for (let i = 0; i < rows.length; i++) {
        // This scans Name, ID, and Category columns specifically
        const rowText = rows[i].textContent.toLowerCase();
        
        if (rowText.includes(filter)) {
            rows[i].style.display = "";
        } else {
            rows[i].style.display = "none";
        }
    }
}

// Optional: Link the Category Dropdown to the same filter
document.querySelector('.form-select').addEventListener('change', function() {
    const category = this.value.toLowerCase();
    const table = document.querySelector("table tbody");
    const rows = table.getElementsByTagName("tr");

    for (let i = 0; i < rows.length; i++) {
        const rowCategory = rows[i].querySelector('td:nth-child(3)').textContent.toLowerCase();
        
        if (category === "all categories" || rowCategory.includes(category)) {
            rows[i].style.display = "";
        } else {
            rows[i].style.display = "none";
        }
    }
});

</script>



</body>
</html>