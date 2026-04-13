<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuqtah Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: var(--brand-teal);">
    <div class="container">

        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="assets/img/logoNuqtah_White.png" alt="ITQSHHB Logo" height="50" class="d-inline-block align-top">
        </a>

        <div class="ms-auto">
    <?php if (isset($_SESSION['user_id'])): ?>
        <span class="text-white me-3 small">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
        <a href="actions/logout.php" class="btn btn-outline-danger">Logout</a>
    <?php else: ?>
        <a href="login.php" class="btn btn-outline-light me-2">Login</a>
        <a href="signup.php" class="btn btn-outline-light me-2">Register</a>
    <?php endif; ?>
        </div>

    </div>
</nav>

<header class="hero-section text-center text-white d-flex align-items-center">
    
    <video autoplay muted loop playsinline class="hero-video">
        <source src="assets/video/Front_slowfront_boomerang.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <div class="hero-overlay"></div>

    <div class="container px-5">
        <h1 class="display-3 fw-bold">Welcome to Nuqtah</h1>
        <p class="lead mb-4">An IT Inventory Management System built for ITQSHHB</p>
        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php" class="btn btn-outline-light btn-lg px-4 rounded-pill">Login to Access Inventory</a>
            <?php else: ?>
                <a href="inventory_list.php" class="btn btn-outline-light btn-lg px-4 rounded-pill">View Inventory</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<section class="container my-5">
    <div class="row g-4 text-center">
        <div class="col-md-4">
            <div class="card landing-card h-100 border-0 shadow-sm p-4">
                <i class="fas fa-exchange-alt feature-icon"></i>
                <h4 class="fw-bold">Borrow & Return</h4>
                <p class="text-muted small">Easily request to borrow and return IT assets.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card landing-card h-100 border-0 shadow-sm p-4">
                <i class="fas fa-shopping-basket feature-icon"></i>
                <h4 class="fw-bold">Shop-like Interface</h4>
                <p class="text-muted small">Browse with a modern cart system. Add multiple items and submit in one click.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card landing-card h-100 border-0 shadow-sm p-4">
                <i class="fas fa-map-marker-alt feature-icon"></i>
                <h4 class="fw-bold">Track</h4>
                <p class="text-muted small">Monitor real-time stock levels and the location of all borrowed IT assets.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card landing-card h-100 border-0 shadow-sm p-4">
                <i class="fas fa-tasks feature-icon"></i>
                <h4 class="fw-bold">Manage</h4>
                <p class="text-muted small">Easily add, edit, or remove items from the central inventory database.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card landing-card h-100 border-0 shadow-sm p-4">
                <i class="fas fa-chart-line feature-icon"></i>
                <h4 class="fw-bold">Admin Dashboard</h4>
                <p class="text-muted small">Track equipment with real-time graphs and transaction trends from one panel.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card landing-card h-100 border-0 shadow-sm p-4">
                <i class="fas fa-file-invoice feature-icon"></i>
                <h4 class="fw-bold">Report</h4>
                <p class="text-muted small">Generate detailed inventory reports for administrative and audit reviews.</p>
            </div>
        </div>
    </div>
</section>
</body>

<?php include 'includes/footer.php'; ?>

</html>