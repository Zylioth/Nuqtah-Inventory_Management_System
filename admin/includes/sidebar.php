<div class="sidebar d-flex flex-column p-3 shadow-sm bg-white">
    <div class="text-center mb-4 mt-2">
        <a href="index.php" class="text-decoration-none">
            <?php 
                // This is the URL path the browser uses
                $logo_path = "../assets/img/logoNuqtah.png";
                
                // This checks if the file exists relative to the admin folder 
                // where index.php or manage_assets.php is running
                if (file_exists("../assets/img/logoNuqtah.png")): 
            ?>
                <img src="<?php echo $logo_path; ?>" alt="Nuqtah Logo" style="max-width: 170px; height: auto;">
            <?php else: ?>
                <div class="py-2">
                    <span class="fs-4 fw-bold" style="color: #00796B;">NUQTAH</span>
                    <p class="x-small text-muted mb-0">Inventory System</p>
                </div>
            <?php endif; ?>
        </a>
    </div>

    <hr class="mx-3 mb-4 opacity-25">
    
    <div class="px-3 mb-2">
        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 1px;">Management</small>
    </div>
    
    <ul class="nav nav-pills flex-column mb-auto">
        <?php 
            // Get current filename to determine active class
            $current_page = basename($_SERVER['PHP_SELF']); 
        ?>
        <li class="nav-item">
            <a href="index.php" class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                <i class="bi bi-grid-1x2-fill me-2"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="manage_assets.php" class="nav-link <?php echo ($current_page == 'manage_assets.php') ? 'active' : ''; ?>">
                <i class="bi bi-archive me-2"></i> Inventory
            </a>
        </li>
        <li>
            <a href="borrowing_requests.php" class="nav-link <?php echo ($current_page == 'borrowing_requests.php') ? 'active' : ''; ?>">
                <i class="bi bi-clipboard-check me-2"></i> Requests
            </a>
        </li>
        <li>
            <a href="users.php" class="nav-link <?php echo ($current_page == 'users.php') ? 'active' : ''; ?>">
                <i class="bi bi-people me-2"></i> Users
            </a>
        </li>
    </ul>
    
    <div class="mt-auto">
        <hr class="mx-3 opacity-25">
        <ul class="nav nav-pills flex-column pb-3">
            <li>
                <a href="../inventory_list.php" class="nav-link">
                    <i class="bi bi-shop me-2"></i> User View
                </a>
            </li>
            <li>
                <a href="../actions/logout.php" class="nav-link text-danger fw-bold">
                    <i class="bi bi-box-arrow-right me-2"></i> Sign Out
                </a>
            </li>
        </ul>
    </div>
</div>