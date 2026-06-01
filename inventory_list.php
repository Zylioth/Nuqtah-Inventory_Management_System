<?php
session_start();
// This file creates the $pdo object
include 'includes/db_connect.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_role = $_SESSION['role'] ?? 'Staff';
$user_id = $_SESSION['user_id']; 

// --- NEW NOTIFICATION LOGIC START ---
$notifQuery = "SELECT r.*, a.asset_name 
                FROM borrowing_requests r 
                JOIN assets a ON r.asset_id = a.asset_id 
                WHERE r.user_id = ? 
                AND r.is_read = 0 
                AND r.status != 'Pending'
                ORDER BY r.request_date DESC LIMIT 1";
$notifStmt = $pdo->prepare($notifQuery);
$notifStmt->execute([$user_id]);
$latest_request = $notifStmt->fetch();

$show_status_modal = isset($_SESSION['first_login_session']) && $_SESSION['first_login_session'] && $latest_request;
// --- NEW NOTIFICATION LOGIC END ---

$searchTerm = trim($_GET['search'] ?? '');
$categoryFilter = trim($_GET['category'] ?? 'all');
$allowedCategories = ['Laptops', 'Projectors', 'Accessories', 'Consumables', 'Others'];
if ($categoryFilter !== 'all' && !in_array($categoryFilter, $allowedCategories, true)) {
    $categoryFilter = 'all';
}

$itemsPerPage = 10;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$whereClauses = [];
$queryParams = [];

if ($searchTerm !== '') {
    $whereClauses[] = '(asset_name LIKE :search_name OR category LIKE :search_category)';
    $queryParams[':search_name'] = "%{$searchTerm}%";
    $queryParams[':search_category'] = "%{$searchTerm}%";
}

if ($categoryFilter !== 'all') {
    $whereClauses[] = 'category = :category';
    $queryParams[':category'] = $categoryFilter;
}

$whereSql = $whereClauses ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM assets {$whereSql}");
foreach ($queryParams as $param => $value) {
    $countStmt->bindValue($param, $value, PDO::PARAM_STR);
}
$countStmt->execute();
$totalItems = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalItems / $itemsPerPage));

if ($page > $totalPages) {
    $page = $totalPages;
}

$offset = ($page - 1) * $itemsPerPage;

$stmt = $pdo->prepare("SELECT * FROM assets {$whereSql} ORDER BY asset_name ASC LIMIT :limit OFFSET :offset");
foreach ($queryParams as $param => $value) {
    $stmt->bindValue($param, $value, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$assets = $stmt->fetchAll();

$firstItem = $totalItems > 0 ? $offset + 1 : 0;
$lastItem = min($offset + count($assets), $totalItems);
$queryBase = ['search' => $searchTerm, 'category' => $categoryFilter];
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuqtah - Inventory List</title>
    <link rel="icon" type="image/png" href="/Nuqtah_IT/assets/img/Nuqtah_logo_small.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        :root {
            --brand-teal: #00796B;
            --brand-teal-dark: #005a4d;
            --brand-soft: #edf7f4;
        }

        .text-warning { color: #F57C00 !important; }
        .bg-warning { background-color: #F57C00 !important; }
        .bg-teal { background-color: var(--brand-teal) !important; }

        .btn-teal {
            background-color: var(--brand-teal);
            border: none;
            color: #fff;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08);
            transition: transform 0.25s ease, background-color 0.25s ease;
        }
        .btn-teal:hover,
        .btn-teal:focus {
            background-color: var(--brand-teal-dark);
            color: #fff;
            transform: translateY(-1px);
        }

        .btn-outline-teal {
            color: var(--brand-teal);
            border: 1px solid var(--brand-teal);
            background-color: #fff;
            transition: background-color 0.25s ease, color 0.25s ease;
        }
        .btn-outline-teal:hover,
        .btn-outline-teal.active {
            background-color: var(--brand-teal);
            color: #fff;
            border-color: var(--brand-teal);
        }

        .search-card {
            background: #fff;
            border-radius: 50px;
            padding: 18px 22px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.06);
        }
        .search-card .form-control {
            border: none;
            box-shadow: none;
            min-height: 50px;
            font-size: 1rem;
        }
        .search-card .form-control:focus {
            box-shadow: none;
            border-color: rgba(0, 121, 107, 0.35);
        }

        .asset-card {
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            cursor: pointer;
            border-radius: 24px;
            overflow: hidden;
        }
        .asset-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 35px rgba(0, 0, 0, 0.12) !important;
        }
        .asset-card img {
            transition: transform 0.35s ease;
        }
        .asset-card:hover img {
            transform: scale(1.03);
        }

        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }
        .asset-card .card-body {
            padding: 1.75rem 1.5rem;
        }
        .asset-name {
            min-height: 3rem;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--brand-teal);
            border-color: var(--brand-teal);
        }
        .pagination .page-link {
            color: var(--brand-teal);
        }
        .pagination .page-link:hover {
            background-color: rgba(0, 121, 107, 0.1);
        }

        .badge-status {
            border-radius: 999px;
            padding: 0.35rem 0.85rem;
            font-size: 0.82rem;
        }

        .x-small { font-size: 0.75rem; }
    </style>
</head>
<body class="bg-light">

<?php include 'includes/header.php'; ?> 
<?php include 'includes/users_sidebar.php'; ?>

<div class="container mt-3">
    <?php if ($latest_request && $latest_request['is_read'] == 0 && $latest_request['status'] !== 'Pending'): ?>
        <?php 
            $alertClass = 'alert-info';
            $instruction = "";
            
            if ($latest_request['status'] == 'Approved') {
                $alertClass = 'alert-success';
                $instruction = "Please head over to the ICT Department to collect your items.";
            } elseif ($latest_request['status'] == 'Rejected') {
                $alertClass = 'alert-danger';
                $instruction = "Please check the rejection reason in your history.";
            } elseif ($latest_request['status'] == 'On Loan') {
                $alertClass = 'alert-primary';
                $instruction = "Ensure you handle the equipment with care.";
            }
        ?>
        <div class="alert <?php echo $alertClass; ?> alert-dismissible fade show shadow-sm border-0 rounded-4 p-3 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-bell-fill fs-4 me-3"></i>
                <div>
                    <strong class="d-block">Request Status: <?php echo $latest_request['status']; ?>!</strong>
                    Your request for <strong><?php echo htmlspecialchars($latest_request['asset_name']); ?></strong> is ready. 
                    <span class="d-block small mt-1"><?php echo $instruction; ?></span>
                    <a href="my_requests.php" class="alert-link text-decoration-underline">View details</a>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="container mt-4">
    <form method="GET" action="" class="row justify-content-center mb-4">
        <div class="col-md-10">
            <div class="search-card d-flex align-items-center gap-3">
                <i class="bi bi-search fs-4 text-secondary"></i>
                <input type="text" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" class="form-control rounded-pill" placeholder="Search for equipment...">
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($categoryFilter); ?>">
                <button type="submit" class="btn btn-teal rounded-pill px-4">Search</button>
            </div>
        </div>
    </form>

    <div class="d-flex flex-wrap justify-content-center gap-2 mb-5">
        <a href="?<?php echo http_build_query(array_merge($queryBase, ['category' => 'all', 'page' => 1])); ?>" class="btn rounded-3 px-4 <?php echo ($categoryFilter === 'all') ? 'btn-teal' : 'btn-outline-teal'; ?>">All Equipment</a>
        <a href="?<?php echo http_build_query(array_merge($queryBase, ['category' => 'Laptops', 'page' => 1])); ?>" class="btn rounded-3 px-4 <?php echo ($categoryFilter === 'Laptops') ? 'btn-teal' : 'btn-outline-teal'; ?>"><i class="bi bi-laptop me-2"></i> Laptops</a>
        <a href="?<?php echo http_build_query(array_merge($queryBase, ['category' => 'Projectors', 'page' => 1])); ?>" class="btn rounded-3 px-4 <?php echo ($categoryFilter === 'Projectors') ? 'btn-teal' : 'btn-outline-teal'; ?>"><i class="bi bi-projector me-2"></i> Projectors</a>
        <a href="?<?php echo http_build_query(array_merge($queryBase, ['category' => 'Accessories', 'page' => 1])); ?>" class="btn rounded-3 px-4 <?php echo ($categoryFilter === 'Accessories') ? 'btn-teal' : 'btn-outline-teal'; ?>"><i class="bi bi-plugin me-2"></i> Accessories</a>
        <a href="?<?php echo http_build_query(array_merge($queryBase, ['category' => 'Consumables', 'page' => 1])); ?>" class="btn rounded-3 px-4 <?php echo ($categoryFilter === 'Consumables') ? 'btn-teal' : 'btn-outline-teal'; ?>"><i class="bi bi-box-seam me-2"></i> Consumables</a>
        <a href="?<?php echo http_build_query(array_merge($queryBase, ['category' => 'Others', 'page' => 1])); ?>" class="btn rounded-3 px-4 <?php echo ($categoryFilter === 'Others') ? 'btn-teal' : 'btn-outline-teal'; ?>"><i class="bi bi-three-dots me-2"></i> Others</a>
    </div>

    <div class="row g-4" id="inventoryGrid">
    <?php if (count($assets) > 0): ?>
        <?php foreach ($assets as $row): ?>
            <?php 
                $placeholder = "assets/img/no_image_available.jpg";
                $check_path = __DIR__ . "/assets/upload/" . $row['asset_image'];
                $image_path = (!empty($row['asset_image']) && file_exists($check_path)) ? "assets/upload/" . $row['asset_image'] : $placeholder;

                $current_stock = $row['current_stock'];
                $total_stock = $row['total_stock'] ?? null;
                $category = $row['category'];
                $low_stock_threshold = 5;

                if ($current_stock <= 0) {
                    $status_class = "bg-danger"; $text_class = "text-danger"; $can_borrow = false;
                    $display_status = ($category === 'Consumables' || $category === 'Stationery') ? "Out of Stock" : "Not Available";
                } elseif ($current_stock <= $low_stock_threshold) {
                    $display_status = "Low Stock (" . $current_stock . " left)";
                    $status_class = "bg-warning"; $text_class = "text-warning"; $can_borrow = true;
                } else {
                    $display_status = "Available";
                    $status_class = "bg-success"; $text_class = "text-success"; $can_borrow = true;
                }
            ?>
            <div class="col-md-4 asset-item" data-category="<?php echo htmlspecialchars($category); ?>">
                <div class="card asset-card border-0 shadow-sm rounded-4 h-100 overflow-hidden" role="button" tabindex="0"
                     onclick='openAssetModal(<?php echo $row['asset_id']; ?>, <?php echo json_encode($row['asset_name']); ?>, <?php echo json_encode($category); ?>, <?php echo json_encode($display_status); ?>, <?php echo $current_stock; ?>, <?php echo $total_stock !== null ? $total_stock : 'null'; ?>, <?php echo json_encode($image_path); ?>)'>
                    <img src="<?php echo htmlspecialchars($image_path); ?>" 
                         class="card-img-top" 
                         alt="<?php echo htmlspecialchars($row['asset_name']); ?>" 
                         style="height: 200px; object-fit: cover;">
                    
                    <div class="card-body px-4 pb-4">
                        <p class="text-muted x-small mb-1"><?php echo htmlspecialchars($row['category']); ?></p>
                        <h5 class="card-title fw-bold mb-3 asset-name"><?php echo htmlspecialchars($row['asset_name']); ?></h5>
                        
                        <div class="d-flex align-items-center small mb-4">
                            <span class="status-dot me-2 <?php echo $status_class; ?>"></span>
                            <span class="<?php echo $text_class; ?> fw-bold"><?php echo $display_status; ?></span>
                        </div>

                        <div class="d-flex align-items-center justify-content-between asset-meta mb-3">
                            <span class="badge badge-status bg-light text-secondary">Current <?php echo $current_stock; ?></span>
                            <?php if ($total_stock !== null): ?>
                                <span class="badge badge-status bg-light text-secondary">Total <?php echo $total_stock; ?></span>
                            <?php endif; ?>
                        </div>

                        <form action="actions/add_to_cart.php" method="POST" onsubmit="event.stopPropagation();">
                            <input type="hidden" name="asset_id" value="<?php echo $row['asset_id']; ?>">
                            <button type="submit" class="btn btn-teal w-100 rounded-pill py-2 fw-bold" <?php echo !$can_borrow ? 'disabled' : ''; ?> onclick="event.stopPropagation();">
                                <i class="bi bi-cart-plus me-2"></i>
                                <?php echo $can_borrow ? 'Add to Borrowing Cart' : $display_status; ?>
                            </button>
                        </form>
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

    <?php if ($totalPages > 1): ?>
        <div class="row">
            <div class="col-12 d-flex flex-column flex-sm-row align-items-center justify-content-between mt-4">
                <div class="text-muted small mb-3 mb-sm-0">
                    Showing <?php echo $firstItem; ?> to <?php echo $lastItem; ?> of <?php echo $totalItems; ?> items
                </div>
                <nav aria-label="Inventory pagination">
                    <ul class="pagination mb-0">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo max(1, $page - 1); ?>" tabindex="-1">Previous</a>
                        </li>
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <li class="page-item <?php echo ($p === $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $p; ?>"><?php echo $p; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo min($totalPages, $page + 1); ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($user_role === 'Admin'): ?>
<div class="position-fixed bottom-0 end-0 p-4" style="z-index: 1050;">
    <a href="admin/index.php" class="btn btn-teal rounded-pill shadow-lg px-4 py-3 d-flex align-items-center">
        <i class="bi bi-speedometer2 fs-5 me-2"></i>
        <span class="fw-bold">ADMIN DASHBOARD</span>
    </a>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'includes/footer.php'; ?>

<?php if ($show_status_modal): ?>
<div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-body p-4 text-center">
                <?php 
                    $status = $latest_request['status'];
                    $icon = 'bi-info-circle'; $color = 'text-primary'; $msg = "Your request is being processed.";
                    if ($status == 'Approved') { $icon = 'bi-check-circle-fill'; $color = 'text-success'; $msg = "Your request for <strong>" . htmlspecialchars($latest_request['asset_name']) . "</strong> has been <strong>Approved</strong>! Please head to the ICT Department for collection."; }
                    elseif ($status == 'On Loan') { $icon = 'bi-box-seam-fill'; $color = 'text-teal'; $msg = "You are currently borrowing <strong>" . htmlspecialchars($latest_request['asset_name']) . "</strong>. Don't forget to check the handover notes!"; }
                    elseif ($status == 'Rejected') { $icon = 'bi-exclamation-octagon-fill'; $color = 'text-danger'; $msg = "Your request for <strong>" . htmlspecialchars($latest_request['asset_name']) . "</strong> was <strong>Rejected</strong>."; }
                    elseif ($status == 'Returned') { $icon = 'bi-arrow-left-right'; $color = 'text-info'; $msg = "The item <strong>" . htmlspecialchars($latest_request['asset_name']) . "</strong> has been successfully <strong>Returned</strong>. Thank you!"; }
                ?>
                <div class="display-4 <?php echo $color; ?> mb-3"><i class="bi <?php echo $icon; ?>"></i></div>
                <h4 class="fw-bold">Request Update</h4>
                <p class="text-muted px-3"><?php echo $msg; ?></p>
                <div class="d-grid gap-2">
                    <a href="my_requests.php" class="btn btn-teal rounded-pill py-2 fw-bold">View My History</a>
                    <button type="button" class="btn btn-link text-muted btn-sm text-decoration-none" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
        statusModal.show();
    });
</script>
<?php unset($_SESSION['first_login_session']); endif; ?>

<?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
<div class="position-fixed bottom-0 start-0 p-4" style="z-index: 1050;">
    <a href="cart.php" class="btn btn-dark rounded-pill shadow-lg px-4 py-3 d-flex align-items-center position-relative">
        <i class="bi bi-shopping-basket fs-5 me-2"></i>
        <span class="fw-bold text-uppercase">View Cart</span>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light">
            <?php echo count($_SESSION['cart']); ?>
        </span>
    </a>
</div>
<?php endif; ?>

<!-- Asset detail modal -->
<div class="modal fade" id="assetDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="assetDetailModalLabel">Asset Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-md-5">
                        <img id="assetDetailImage" src="" alt="Asset Image" class="img-fluid rounded-4 w-100" style="object-fit: cover; height: 300px;">
                    </div>
                    <div class="col-md-7">
                        <p class="text-muted mb-1" id="assetDetailCategory"></p>
                        <h4 class="fw-bold mb-3" id="assetDetailName"></h4>
                        <div class="mb-3">
                            <span class="badge bg-secondary me-2" id="assetDetailStatus"></span>
                            <span class="text-muted" id="assetDetailStock"></span>
                        </div>
                        <div class="small text-muted mb-4">
                            <strong>Stock Details:</strong> Current stock and total stock are shown here.
                        </div>
                        <div id="assetDetailTags" class="border-top pt-3">
                            <div class="text-center text-muted py-3">Loading tag information...</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-between">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <a href="cart.php" class="btn btn-teal">Go to Borrowing Cart</a>
            </div>
        </div>
    </div>
</div>

<script>
    function openAssetModal(assetId, assetName, category, displayStatus, currentStock, totalStock, imagePath) {
        document.getElementById('assetDetailModalLabel').innerText = assetName;
        document.getElementById('assetDetailName').innerText = assetName;
        document.getElementById('assetDetailCategory').innerText = category;
        document.getElementById('assetDetailStatus').innerText = displayStatus;
        document.getElementById('assetDetailImage').src = imagePath;
        document.getElementById('assetDetailStock').innerText = totalStock !== null ? `Stock: ${currentStock} / ${totalStock}` : `Stock: ${currentStock}`;

        const tagsContainer = document.getElementById('assetDetailTags');
        tagsContainer.innerHTML = '<div class="text-center text-muted py-3">Loading tag information...</div>';

        fetch(`admin/actions/get_asset_tags.php?asset_id=${assetId}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.text();
            })
            .then(html => {
                tagsContainer.innerHTML = html;
            })
            .catch(() => {
                tagsContainer.innerHTML = '<div class="text-danger py-3">Tag information is not available.</div>';
            });

        const modal = new bootstrap.Modal(document.getElementById('assetDetailModal'));
        modal.show();
    }

    // Original SweetAlert scripts kept exactly the same
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    if (msg === 'added') {
        Swal.fire({ icon: 'success', title: 'Added to Cart', toast: true, position: 'top-end', timer: 2000, showConfirmButton: false, timerProgressBar: true });
    } else if (msg === 'submitted') {
        Swal.fire({ icon: 'success', title: 'Request Submitted!', text: 'Your borrowing request has been sent to the ICT Department for approval.', confirmButtonColor: '#00796B', confirmButtonText: 'Great!' });
    }
    if (msg) window.history.replaceState({}, document.title, window.location.pathname);
</script>
</body>
</html>