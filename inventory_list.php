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

$stmt = $pdo->query("SELECT * FROM assets");
$assets = $stmt->fetchAll();
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
        .text-warning { color: #F57C00 !important; }
        .bg-warning { background-color: #F57C00 !important; }
        .bg-teal { background-color: #00796B !important; }
        .btn-teal { background-color: #00796B; border: none; transition: 0.3s; color: white; }
        .btn-teal:hover { background-color: #004D40; color: white; }
        .asset-card { transition: transform 0.3s ease, box-shadow 0.3s ease; cursor: pointer; }
        .asset-card:hover { transform: translateY(-10px); box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important; }
        .status-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
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
    <div class="row justify-content-center mb-4">
        <div class="col-md-10">
            <input type="text" id="searchInput" class="form-control rounded-pill border-secondary px-4 py-2" placeholder="Search for equipment....." onkeyup="filterInventory()">
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-center gap-2 mb-5">
        <button class="btn btn-teal rounded-3 px-4 filter-btn" onclick="filterCategory('all', this)">All Equipment</button>
        <button class="btn btn-outline-dark bg-white rounded-3 px-4 filter-btn" onclick="filterCategory('Laptops', this)"><i class="bi bi-laptop me-2"></i> Laptops</button>
        <button class="btn btn-outline-dark bg-white rounded-3 px-4 filter-btn" onclick="filterCategory('Projectors', this)"><i class="bi bi-projector me-2"></i> Projectors</button>
        <button class="btn btn-outline-dark bg-white rounded-3 px-4 filter-btn" onclick="filterCategory('Accessories', this)"><i class="bi bi-plugin me-2"></i> Accessories</button>
        <button class="btn btn-outline-dark bg-white rounded-3 px-4 filter-btn" onclick="filterCategory('Consumables', this)"><i class="bi bi-box-seam me-2"></i> Consumables</button>
        <button class="btn btn-outline-dark bg-white rounded-3 px-4 filter-btn" onclick="filterCategory('Others', this)"><i class="bi bi-three-dots me-2"></i> Others</button>
    </div>

    <div class="row g-4" id="inventoryGrid">
    <?php if (count($assets) > 0): ?>
        <?php foreach ($assets as $row): ?>
            <?php 
                $placeholder = "assets/img/no_image_available.jpg";
                $check_path = __DIR__ . "/assets/upload/" . $row['asset_image'];
                $image_path = (!empty($row['asset_image']) && file_exists($check_path)) ? "assets/upload/" . $row['asset_image'] : $placeholder;

                $current_stock = $row['current_stock'];
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
                <div class="card asset-card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
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

                        <form action="actions/add_to_cart.php" method="POST">
                            <input type="hidden" name="asset_id" value="<?php echo $row['asset_id']; ?>">
                            <button type="submit" class="btn btn-teal w-100 rounded-pill py-2 fw-bold" <?php echo !$can_borrow ? 'disabled' : ''; ?>>
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

<script>
    // Functional Search and Filter Logic
    let currentCategory = 'all';

    function filterCategory(category, btn) {
        currentCategory = category;
        document.querySelectorAll('.filter-btn').forEach(b => {
            b.classList.remove('btn-teal');
            b.classList.add('btn-outline-dark', 'bg-white');
        });
        btn.classList.add('btn-teal');
        btn.classList.remove('btn-outline-dark', 'bg-white');
        filterInventory();
    }

    function filterInventory() {
        const searchTerm = document.getElementById("searchInput").value.toLowerCase();
        const items = document.querySelectorAll(".asset-item");

        items.forEach(item => {
            const name = item.querySelector(".asset-name").innerText.toLowerCase();
            const category = item.getAttribute("data-category");
            const matchesSearch = name.includes(searchTerm);
            const matchesCategory = (currentCategory === 'all' || category === currentCategory);
            item.style.display = (matchesSearch && matchesCategory) ? "block" : "none";
        });
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