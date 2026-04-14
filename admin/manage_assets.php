<?php
session_start();
include '../includes/db_connect.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

$query = "SELECT * FROM assets ORDER BY category ASC, asset_name ASC";
$stmt = $pdo->query($query);
$assets = $stmt->fetchAll();

$low_stock_threshold = 5;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Inventory - Nuqtah</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root { --sidebar-width: 260px; --teal-primary: #00796B; }
        body { background-color: #f8f9fa; overflow-x: hidden; }

        /* Main Content Responsiveness */
        .main-content { transition: all 0.3s ease; padding: 20px; }

        @media (min-width: 992px) {
            .main-content { margin-left: var(--sidebar-width); padding: 40px; }
        }

        @media (max-width: 991.98px) {
            .main-content { margin-left: 0; }
        }

        .asset-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
        .badge-low { background-color: #FFF3E0; color: #E65100; border: 1px solid #FFE0B2; }
        .text-teal { color: var(--teal-primary) !important; }
        
        /* Table adjustments for mobile */
        .table { font-size: 0.9rem; }
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

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div class="mb-3 mb-md-0">
            <h2 class="fw-bold mb-0">Inventory Management</h2>
            <p class="text-muted mb-0">View and update the ITQSHHB equipment list.</p>
        </div>
        <a href="add_assets.php" class="btn text-white rounded-pill px-4 py-2" style="background-color: #00796B;">
            <i class="bi bi-plus-lg me-2"></i> Add New Asset
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" id="adminSearch" class="form-control border-start-0 ps-0" 
                               placeholder="Search by name, category or ID..." onkeyup="filterAssets()">     
                    </div>
                </div>
                <div class="col-md-4">
                    <select class="form-select" id="categoryFilter">
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
                        <th>Asset Details</th>
                        <th class="d-none d-md-table-cell">Category</th>
                        <th>Stock</th>
                        <th class="d-none d-sm-table-cell">Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assets as $row): 
                        $current = $row['current_stock'];
                        $total = $row['total_stock'];
                        $is_consumable = ($row['category'] === 'Consumables' || $row['category'] === 'Stationery');
                        
                        if ($current <= 0) {
                            $status_label = $is_consumable ? "Out" : "N/A";
                            $badge_class = "bg-danger text-white";
                        } elseif ($current <= $low_stock_threshold) {
                            $status_label = "Low";
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
                                <small class="text-muted d-md-none"><?php echo $row['category']; ?><br></small>
                                <button class="btn btn-link btn-sm p-0 text-decoration-none text-teal fw-bold" 
                                        onclick="viewAssetTags(<?php echo $row['asset_id']; ?>, '<?php echo addslashes($row['asset_name']); ?>')"
                                        style="font-size: 0.75rem;">
                                    <i class="bi bi-tag-fill me-1"></i>Serials
                                </button>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <span class="badge bg-light text-dark border"><?php echo $row['category']; ?></span>
                            </td>
                            <td>
                                <span class="fw-bold"><?php echo $current; ?></span><span class="text-muted small">/<?php echo $total; ?></span>
                            </td>
                            <td class="d-none d-sm-table-cell">
                                <span class="badge <?php echo $badge_class; ?> rounded-pill" style="font-size: 0.7rem;">
                                    <?php echo $status_label; ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a href="edit_asset.php?id=<?php echo $row['asset_id']; ?>" class="btn btn-light btn-sm border">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button class="btn btn-light btn-sm border text-danger" onclick="showDeleteModal(<?php echo $row['asset_id']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// 1. Delete Modal Logic
function showDeleteModal(id) {
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    if (deleteBtn) {
        deleteBtn.href = 'delete_asset.php?id=' + id;
        const myModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        myModal.show();
    }
}

// 2. Asset Tag / Serial Management
function viewAssetTags(assetId, assetName) {
    document.getElementById('tagModalTitle').innerText = "Serials: " + assetName;
    document.getElementById('add_tag_asset_id').value = assetId;
    
    loadTags(assetId);
    
    const tagModal = new bootstrap.Modal(document.getElementById('assetTagModal'));
    tagModal.show();
}

function loadTags(assetId) {
    const tagListBody = document.getElementById('tagListBody');
    // Show spinner while loading
    tagListBody.innerHTML = '<div class="p-5 text-center"><div class="spinner-border text-teal"></div></div>';

    fetch(`actions/get_asset_tags.php?asset_id=${assetId}`)
        .then(res => res.text())
        .then(data => {
            tagListBody.innerHTML = data;
        })
        .catch(err => {
            tagListBody.innerHTML = '<div class="p-3 text-danger text-center">Error loading tags.</div>';
        });
}

// 3. Handle AJAX Tag Addition
const addTagForm = document.getElementById('addTagForm');
if (addTagForm) {
    addTagForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const assetId = formData.get('asset_id');

        fetch('actions/add_asset_tag.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                this.reset();
                loadTags(assetId);
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        });
    });
}

// 4. Combined Filter Logic (Search + Category)
// This ensures that if you select "Laptops" AND search for "Dell", it works correctly.
function filterAssets() {
    const searchInput = document.getElementById("adminSearch").value.toLowerCase();
    const categorySelect = document.querySelector('.form-select').value.toLowerCase();
    const rows = document.querySelectorAll("table tbody tr");

    rows.forEach(row => {
        const rowText = row.textContent.toLowerCase();
        // Adjust the selector to match the cell containing your category badge
        const rowCategory = row.querySelector('td:nth-child(3)').textContent.toLowerCase();

        const matchesSearch = rowText.includes(searchInput);
        const matchesCategory = (categorySelect === "all categories" || rowCategory.includes(categorySelect));

        if (matchesSearch && matchesCategory) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}

// Attach listeners for filtering
document.getElementById("adminSearch").addEventListener("keyup", filterAssets);
document.querySelector('.form-select').addEventListener('change', filterAssets);
</script>



</body>
</html>