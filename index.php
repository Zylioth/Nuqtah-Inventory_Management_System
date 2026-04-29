<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuqtah Inventory System</title>
    <link rel="icon" type="image/png" href="/Nuqtah_IT/assets/img/Nuqtah_logo_small.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<style>
/* --- Desktop & General Styles --- */
header img {
    transition: transform 0.3s ease;
}

/* Force the container to fit the screen width */
.hero-section .container {
    max-width: 100%;
    padding-left: 15px;
    padding-right: 15px;
}

/* --- Mobile Specific Fix (iPhone 11 Pro) --- */
@media (max-width: 768px) {
    /* 1. Make the logo wrapper wrap items if they don't fit */
    .d-flex.align-items-center.justify-content-center.mb-4 {
        flex-wrap: wrap; 
        flex-direction: row; /* Keep them side by side but allowed to shrink */
    }

    /* 2. Scale down the Nuqtah Logo */
    .hero-section img[alt="Nuqtah Logo"] {
        height: 50px !important; 
        width: auto;
    }

    /* 3. Scale down the ITQSHHB Logo */
    .hero-section img[alt="ITQSHHB Logo"] {
        height: 60px !important;
        width: auto;
    }

    /* 4. Shrink the divider and margins so they don't push logos off-screen */
    .hero-section div[style*="width: 2px"] {
        height: 40px !important;
        margin: 0 15px !important; /* Cut margin in half (30px -> 15px) */
    }

    /* 5. Adjust the lead text so it doesn't look too big compared to logos */
    .hero-section .lead {
        font-size: 1.1rem !important;
        line-height: 1.4;
    }
}
</style>

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

<!-- Hero section the first orng liat -->
<header class="hero-section text-center text-white d-flex align-items-center justify-content-center" style="position: relative; overflow: hidden; height: 85vh;">
    
    <video autoplay muted loop playsinline class="hero-video" style="position: absolute; top: 50%; left: 50%; min-width: 100%; min-height: 100%; width: auto; height: auto; z-index: -2; transform: translate(-50%, -50%); object-fit: cover;">
        <source src="assets/video/Front_slowfront_boomerang.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <div class="hero-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.4); z-index: -1;"></div>

    <div class="container px-5" style="animation: fadeUp 1s ease-out forwards;">
        
            <!-- Both logo ITQSHHB and nuqtah -->
        <div class="d-flex align-items-center justify-content-center mb-4">

            <img src="assets/img/logoNuqtah_White.png" alt="Nuqtah Logo" style="height: 80px; filter: drop-shadow(0 4px 10px rgba(0,0,0,0.5));">   
            
            <div style="width: 2px; height: 80px; background: white; margin: 0 30px; opacity: 0.8; box-shadow: 0 0 10px rgba(0,0,0,0.3);"></div>
            
            <img src="assets/img/ITQSHHBLogo.png" alt="ITQSHHB Logo" style="height: 100px; filter: drop-shadow(0 4px 10px rgba(0,0,0,0.5));"> 
        
        </div>

        <p class="lead mb-4 fw-light" style="text-shadow: 0 2px 8px rgba(0,0,0,0.8); font-size: 1.4rem; letter-spacing: 1px;">
            An IT Inventory Management System built for ITQSHHB
        </p>

        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php" class="btn btn-outline-light btn-lg px-5 rounded-pill shadow-sm" style="border-width: 2px; font-weight: 600; transition: all 0.3s ease;">
                    Login to Access Inventory
                </a>
            <?php else: ?>
                <a href="inventory_list.php" class="btn btn-outline-light btn-lg px-5 rounded-pill shadow-sm" style="border-width: 2px; font-weight: 600; transition: all 0.3s ease;">
                    View Inventory
                </a>
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
                <p class="text-muted small">Move away from manual paper forms. Submit digital requests in seconds and track your return deadlines automatically.</p>
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
                <h4 class="fw-bold">Real-time Tracking</h4>
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