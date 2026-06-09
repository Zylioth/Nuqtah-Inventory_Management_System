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
    overflow-x: hidden; /* Prevent horizontal layout breaking */
}

/* --- Hero Section & Video Layer Scaling --- */
.hero-section {
    position: relative;
    overflow: hidden;
    min-height: 85vh; /* Fluid viewport adaptation */
    display: flex;
    align-items: center;
    justify-content: center;
}

.hero-video {
    position: absolute;
    top: 50%;
    left: 50%;
    min-width: 100%;
    min-height: 100%;
    width: auto;
    height: auto;
    z-index: -2;
    transform: translate(-50%, -50%);
    object-fit: cover;
}

/* Fallback gradient to ensure absolute text contrast if video takes time to buffer */
.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(0, 77, 64, 0.7) 0%, rgba(0, 0, 0, 0.65) 100%);
    z-index: -1;
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
    background-color: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(4px);
}

.btn-hero-action:hover {
    background-color: #ffffff !important;
    color: var(--teal-primary) !important;
    transform: scale(1.03);
}

/* --- Fluid Workflow Circles --- */
.step-circle {
    width: 60px;
    height: 60px;
    background-color: rgba(0, 121, 107, 0.1);
    color: var(--teal-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem auto;
    font-size: 1.25rem;
    font-weight: 700;
    transition: transform 0.3s ease;
}
.step-item:hover .step-circle {
    transform: scale(1.1);
    background-color: var(--teal-primary);
    color: #ffffff;
}

/* --- Perfect Adaptive Responsiveness Rules --- */
@media (max-width: 991px) {
    .hero-section {
        min-height: 75vh;
    }
}

@media (max-width: 768px) {
    /* Fluid adjustment of the logo split structure */
    .brand-logos-container {
        flex-direction: column !important;
        gap: 1.25rem;
    }
    
    .logo-divider {
        width: 60px !important;
        height: 2px !important;
        margin: 0.5rem 0 !important;
    }

    /* Scale down the Logos to prevent side clipping on mobile devices */
    .hero-section img[alt="Nuqtah Logo"] {
        height: 50px !important;
        width: auto;
    }

    .hero-section img[alt="ITQSHHB Logo"] {
        height: 70px !important;
        width: auto;
    }

    .hero-section .lead {
        font-size: 1.1rem !important;
        line-height: 1.4;
    }
}

@media (max-width: 480px) {
    .hero-section {
        padding-top: 3rem;
        padding-bottom: 3rem;
    }
}
</style>

<body>

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: var(--brand-teal);">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="assets/img/logoNuqtah_White.png" alt="Nuqtah Logo" height="42" class="d-inline-block align-top">
        </a>

        <div class="ms-auto d-flex align-items-center">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="text-white me-3 small d-none d-md-inline">Welcome, <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong></span>
                <a href="actions/logout.php" class="btn btn-outline-light btn-sm rounded-pill px-3">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline-light me-2 rounded-pill px-3 btn-sm">Login</a>
                <a href="signup.php" class="btn btn-light text-teal rounded-pill px-3 btn-sm" style="color: var(--teal-primary); font-weight: 600;">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Hero section with upgraded overlays and responsive scaling safeguards -->
<header class="hero-section text-center text-white">
    <video autoplay muted loop playsinline class="hero-video">
        <source src="assets/video/Front_slowfront_boomerang.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <div class="hero-overlay"></div>

    <div class="container px-4 px-md-5">
        
        <!-- Both logo ITQSHHB and nuqtah with fluid centering flex layouts -->
        <div class="d-flex align-items-center justify-content-center brand-logos-container mb-4">
            <img src="assets/img/logoNuqtah_White.png" alt="Nuqtah Logo" style="height: 80px; filter: drop-shadow(0 4px 10px rgba(0,0,0,0.5));">   
            <div class="logo-divider" style="width: 2px; height: 80px; background: white; margin: 0 30px; opacity: 0.8; box-shadow: 0 0 10px rgba(0,0,0,0.3);"></div>
            <img src="assets/img/ITQSHHBLogo.png" alt="ITQSHHB Logo" style="height: 100px; filter: drop-shadow(0 4px 10px rgba(0,0,0,0.5));"> 
        </div>

        <p class="lead mb-4 fw-light text-white" style="text-shadow: 0 2px 8px rgba(0,0,0,0.8); font-size: 1.35rem; letter-spacing: 0.5px;">
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

<!-- Workflow section using perfectly responsive grid cards -->
<section class="bg-white py-5 border-top border-bottom">
    <div class="container my-3 px-4">
        <div class="text-center mb-5">
            <span class="badge rounded-pill px-3 py-2 mb-2 text-white" style="font-size: 0.75rem; background-color: var(--teal-primary) !important; letter-spacing: 1px;">WORKFLOW</span>
            <h2 class="fw-bold text-dark">How Digital Borrowing Works</h2>
            <p class="text-muted small">A seamless paperless experience from request to return</p>
        </div>
        <div class="row g-4 justify-content-center text-center">
            <div class="col-sm-6 col-lg-3 step-item">
                <div class="p-3">
                    <div class="step-circle">1</div>
                    <h5 class="fw-bold text-dark">Sign In</h5>
                    <p class="text-muted small mb-0">Register with your institutional credentials to access the central catalog.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 step-item">
                <div class="p-3">
                    <div class="step-circle">2</div>
                    <h5 class="fw-bold text-dark">Reserve Items</h5>
                    <p class="text-muted small mb-0">Browse live stock counts, add items to your basket, and specify a pickup time.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 step-item">
                <div class="p-3">
                    <div class="step-circle">3</div>
                    <h5 class="fw-bold text-dark">Inspection</h5>
                    <p class="text-muted small mb-0">Present your request ID at the desk, verify the item condition, and collect.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 step-item">
                <div class="p-3">
                    <div class="step-circle">4</div>
                    <h5 class="fw-bold text-dark">Safe Return</h5>
                    <p class="text-muted small mb-0">Return the equipment on or before your return date to keep the inventory healthy.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Restructured feature list with modern responsive grid metrics (col-12 col-md-6 col-lg-4) -->
<section class="container my-5 px-4">
    <div class="text-center mb-5">
        <span class="badge rounded-pill px-3 py-2 mb-2 text-white" style="font-size: 0.75rem; background-color: var(--teal-primary) !important; letter-spacing: 1px;">FEATURES</span>
        <h2 class="fw-bold text-dark">System Capabilities</h2>
        <p class="text-muted small">Fully loaded with all the modules needed to run campus assets smoothly</p>
    </div>

    <div class="row g-4 text-center">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card landing-card h-100 border-0 shadow-sm p-4 rounded-4">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <h4 class="fw-bold mb-2">Borrow & Return</h4>
                <p class="text-muted small mb-0">Move away from manual paper forms. Submit digital requests in seconds and track your return deadlines automatically.</p>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card landing-card h-100 border-0 shadow-sm p-4 rounded-4">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-shopping-basket"></i>
                </div>
                <h4 class="fw-bold mb-2">Shop-like Interface</h4>
                <p class="text-muted small mb-0">Browse with a modern cart system. Add multiple items and submit in one single action.</p>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card landing-card h-100 border-0 shadow-sm p-4 rounded-4">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h4 class="fw-bold mb-2">Real-time Tracking</h4>
                <p class="text-muted small mb-0">Monitor real-time stock levels and the current deployment location of all borrowed IT assets.</p>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card landing-card h-100 border-0 shadow-sm p-4 rounded-4">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-tasks"></i>
                </div>
                <h4 class="fw-bold mb-2">Manage</h4>
                <p class="text-muted small mb-0">Easily add, edit, or remove item parameters from the central operational database in seconds.</p>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card landing-card h-100 border-0 shadow-sm p-4 rounded-4">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h4 class="fw-bold mb-2">Admin Dashboard</h4>
                <p class="text-muted small mb-0">Track equipment with real-time dynamic statistics, charts, and transaction logs from one centralized screen.</p>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
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

<!-- Footer file is included correctly inside body to maintain perfect layout structure -->
<?php include 'includes/footer.php'; ?>

</body>
</html>