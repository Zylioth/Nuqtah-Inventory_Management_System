<style>
    :root { --nuqtah-teal: #00796B; --nuqtah-dark: #004D40; }
    
    .btn-menu-toggle {
        background-color: var(--nuqtah-teal);
        color: white;
        border-radius: 12px;
        transition: all 0.3s;
    }
    .btn-menu-toggle:hover {
        background-color: var(--nuqtah-dark);
        color: white;
        transform: scale(1.05);
    }
    
    .offcanvas-header { background-color: var(--nuqtah-teal); }
    
    .student-nav .list-group-item {
        border: none;
        padding: 1rem 1.5rem;
        margin: 5px 10px;
        border-radius: 8px;
        transition: 0.2s;
        color: #555;
    }
    
    .student-nav .list-group-item:hover {
        background-color: #f1f1f1;
        color: var(--nuqtah-teal);
    }
    
    /* Highlight the active page */
    .student-nav .list-group-item.active {
        background-color: rgba(0, 121, 107, 0.1) !important;
        color: var(--nuqtah-teal) !important;
        font-weight: 600;
        border-left: 4px solid var(--nuqtah-teal) !important;
    }
</style>

<button class="btn btn-menu-toggle shadow-sm position-fixed top-0 start-0 m-3 z-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#studentSidebar">
    <i class="bi bi-list fs-4"></i>
</button>

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
            <?php 
                // Simple logic to detect active page
                $current_page = basename($_SERVER['PHP_SELF']); 
            ?>
            
            <a href="inventory_list.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'inventory_list.php') ? 'active' : ''; ?>">
                <i class="bi bi-grid-fill me-3"></i>Browse Inventory
            </a>
            
            <a href="my_requests.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'my_requests.php') ? 'active' : ''; ?>">
                <i class="bi bi-clock-history me-3"></i>My Requests
            </a>
            
            <hr class="mx-4">
            
            <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                <i class="bi bi-box-arrow-right me-3"></i>Logout
            </a>
        </div>
    </div>
    
    <div class="p-3 text-center border-top">
        <p class="text-muted mb-0 small">Nuqtah @ ITQSHHB</p>
    </div>
</div>