<?php
// Securely fetch active page name to toggle active class in sidebar menu
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    /* --- Sidebar Custom Styling --- */
    .btn-menu-toggle {
        color: rgba(255, 255, 255, 0.95);
        border: none;
        transition: all 0.2s ease-in-out;
    }
    .btn-menu-toggle:hover {
        color: #ffffff;
        transform: scale(1.1);
    }
    
    .offcanvas-header { 
        background-color: var(--brand-teal, #00796B); 
    }
    
    .student-nav .list-group-item {
        border: none;
        padding: 1rem 1.5rem;
        margin: 5px 10px;
        border-radius: 8px;
        transition: all 0.2s ease;
        color: #555555;
        display: flex;
        align-items: center;
    }
    
    .student-nav .list-group-item:hover {
        background-color: #f1f1f1;
        color: var(--brand-teal, #00796B);
    }
    
    /* Highlight the active page */
    .student-nav .list-group-item.active {
        background-color: rgba(0, 121, 107, 0.1) !important;
        color: var(--brand-teal, #00796B) !important;
        font-weight: 600;
        border-left: 4px solid var(--brand-teal, #00796B) !important;
    }
</style>

<header class="shadow-sm" style="position: sticky; top: 0; z-index: 1000;">
    <nav class="navbar navbar-dark py-3" style="background-color: var(--brand-teal, #00796B); border-bottom: 1px solid rgba(255, 255, 255, 0.15);">
        <div class="container">
            <div class="d-flex align-items-center">
                <!-- Hamburger Menu Toggle Button integrated directly into the navbar -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <button class="btn btn-menu-toggle p-0 me-3 bg-transparent border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#studentSidebar" aria-label="Open Navigation Menu">
                        <i class="bi bi-list fs-3"></i>
                    </button>
                <?php endif; ?>

                <a class="navbar-brand d-flex align-items-center m-0" href="inventory_list.php">
                    <img src="assets/img/logoNuqtah_White.png" alt="ITQSHHB Logo" height="45" style="transition: transform 0.3s ease;">
                </a>
            </div>
            
            <div class="ms-auto d-flex align-items-center gap-3">
                <?php if (isset($_SESSION['full_name'])): ?>
                    <span class="text-white fw-semibold d-none d-md-inline small">
                        <i class="bi bi-person-circle me-1 opacity-75"></i> Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                    </span>
                <?php endif; ?>
                
                <a href="actions/logout.php" class="btn btn-outline-light btn-sm rounded-pill px-3" style="font-weight: 500; transition: all 0.2s ease;">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>
</header>

<!-- Sidebar Offcanvas Menu Drawer -->
<?php if (isset($_SESSION['user_id'])): ?>
<div class="offcanvas offcanvas-start shadow-lg border-0" tabindex="-1" id="studentSidebar" style="width: 280px;">
    <div class="offcanvas-header text-white py-4">
        <div>
            <h5 class="offcanvas-title fw-bold mb-0">Nuqtah</h5>
            <small class="opacity-75">Users Menu</small>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    
    <div class="offcanvas-body p-0">
        <div class="list-group list-group-flush student-nav mt-3">
            <a href="inventory_list.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'inventory_list.php') ? 'active' : ''; ?>">
                <i class="bi bi-grid-fill me-3"></i>Browse Inventory
            </a>
            
            <a href="my_requests.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'my_requests.php') ? 'active' : ''; ?>">
                <i class="bi bi-clock-history me-3"></i>My Requests
            </a>
            
            <hr class="mx-4 my-3 opacity-10">
            
            <a href="actions/logout.php" class="list-group-item list-group-item-action text-danger">
                <i class="bi bi-box-arrow-right me-3"></i>Logout
            </a>
        </div>
    </div>
    
    <div class="p-3 text-center border-top">
        <p class="text-muted mb-0 small">Nuqtah @ ITQSHHB</p>
    </div>
</div>
<?php endif; ?>