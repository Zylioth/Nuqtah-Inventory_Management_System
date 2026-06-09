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
/* --- Design Tokens and Variable Declarations --- */
:root { 
    --brand-teal: #00796B; 
    --teal-primary: #00796B; 
    --teal-dark: #004D40; 
}

body {
    background-color: #f8fafc;
    font-family: 'Inter', sans-serif;
}

header img {
    transition: transform 0.3s ease;
}

/* Force the container to fit the screen width */
.hero-section .container {
    max-width: 100%;
    padding-left: 15px;
    padding-right: 15px;
}

/* --- Strictly Scoped Hover Animations (Index Page Only) --- */
.landing-card {
    transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1), box-shadow 0.4s ease, border-color 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.03) !important;
}

.landing-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 1rem 3rem rgba(0, 121, 107, 0.12) !important;
    border-color: rgba(0, 121, 107, 0.15) !important;
}

/* Rounded Circle Icon Accents */
.feature-icon-wrapper {
    width: 64px;
    height: 64px;
    background-color: rgba(0, 121, 107, 0.08);
    color: var(--teal-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin: 0 auto 1.25rem auto;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.landing-card:hover .feature-icon-wrapper {
    background-color: var(--teal-primary);
    color: white;
}

/* Buttons and Hover Physics */
.btn-hero-action {
    border-width: 2px !important;
    font-weight: 600;
    transition: all 0.3s ease;
    background-color: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(4px);
}

.btn-hero-action:hover {
    background-color: #ffffff !important;
    color: var(--teal-primary) !important;
    transform: scale(1.03);
}

/* --- Mobile Specific Fix (iPhone 11 Pro) --- */
@media (max-width: 768px) {
    /* Make the logo wrapper wrap items if they don't fit */
    .d-flex.align-items-center.justify-content-center.mb-4 {
        flex-wrap: wrap; 
        flex-direction: row; 
    }

    /* Scale down the Nuqtah Logo */
    .hero-section img[alt="Nuqtah Logo"] {
        height: 50px !important; 
        width: auto;
    }

    /* Scale down the ITQSHHB Logo */
    .hero-section img[alt="ITQSHHB Logo"] {
        height: 60px !important;
        width: auto;
    }

    /* Shrink the divider and margins so they don't push logos off-screen */
    .hero-section div[style*="width: 2px"] {
        height: 40px !important;
        margin: 0 15px !important; 
    }

    /* Adjust the lead text so it doesn't look too big compared to logos */
    .hero-section .lead {
        font-size: 1.1rem !important;
        line-height: 1.4;
    }
}
</style>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: var(--brand-teal);">
    <div class="container">

        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="assets/img/logoNuqtah_White.png" alt="ITQSHHB Logo" height="50" class="d-inline-block align-top">
        </a>

        <div class="ms-auto d-flex align-items-center">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="text-white me-3 small d-none d-sm-inline">Welcome, <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong></span>
                <a href="actions/logout.php" class="btn btn-outline-light btn-sm rounded-pill px-3">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline-light me-2 rounded-pill px-3 btn-sm">Login</a>
                <a href="signup.php" class="btn btn-light text-teal rounded-pill px-3 btn-sm" style="color: var(--teal-primary); font-weight: 600;">Register</a>
            <?php endif; ?>
        </div>

    </div>
</nav>

<!-- Hero section with upgraded overlays and fallbacks -->
<header class="hero-section text-center text-white d-flex align-items-center justify-content-center" style="position: relative; overflow: hidden; height: 85vh;">
    
    <video autoplay muted loop playsinline class="hero-video" style="position: absolute; top: 50%; left: 50%; min-width: 100%; min-height: 100%; width: auto; height: auto; z-index: -2; transform: translate(-50%, -50%); object-fit: cover;">
        <source src="assets/video/Front_slowfront_boomerang.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <!-- Upgraded gradient overlay for premium look and high readability -->
    <div class="hero-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, rgba(0, 77, 64, 0.6) 0%, rgba(0, 0, 0, 0.5) 100%); z-index: -1;"></div>

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
                <a href="login.php" class="btn btn-outline-light btn-lg px-5 rounded-pill shadow-sm btn-hero-action">
                    Login to Access Inventory
                </a>
            <?php else: ?>
                <a href="inventory_list.php" class="btn btn-outline-light btn-lg px-5 rounded-pill shadow-sm btn-hero-action">
                    View Inventory
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Restructured feature list with modern, responsive grid metrics -->
<section class="container my-5">
    <div class="row g-4 text-center">
        <div class="col-md-4">
            <div class="card landing-card h-100 border-0 shadow-sm p-4 rounded-4">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <h4 class="fw-bold mb-2">Borrow & Return</h4>
                <p class="text-muted small mb-0">Move away from manual paper forms. Submit digital requests in seconds and track your return deadlines automatically.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card landing-card h-100 border-0 shadow-sm p-4 rounded-4">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-shopping-basket"></i>
                </div>
                <h4 class="fw-bold mb-2">Shop-like Interface</h4>
                <p class="text-muted small mb-0">Browse with a modern cart system. Add multiple items and submit in one single action.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card landing-card h-100 border-0 shadow-sm p-4 rounded-4">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h4 class="fw-bold mb-2">Real-time Tracking</h4>
                <p class="text-muted small mb-0">Monitor real-time stock levels and the current deployment location of all borrowed IT assets.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card landing-card h-100 border-0 shadow-sm p-4 rounded-4">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-tasks"></i>
                </div>
                <h4 class="fw-bold mb-2">Manage</h4>
                <p class="text-muted small mb-0">Easily add, edit, or remove item parameters from the central operational database in seconds.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card landing-card h-100 border-0 shadow-sm p-4 rounded-4">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h4 class="fw-bold mb-2">Admin Dashboard</h4>
                <p class="text-muted small mb-0">Track equipment with real-time dynamic statistics, charts, and transaction logs from one centralized screen.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card landing-card h-100 border-0 shadow-sm p-4 rounded-4">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <h4 class="fw-bold mb-2">Report</h4>
                <p class="text-muted small mb-0">Generate detailed inventory listings and audit reports for institutional administrative reviews.</p>
            </div>
        </div>
    </div>
</section>

</body>

<?php include 'includes/footer.php'; ?>

</html>