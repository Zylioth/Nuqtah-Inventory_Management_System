<style>
    :root { 
        --sidebar-width: 260px; 
        --nuqtah-teal: #00796B; 
        --nuqtah-teal-hover: #004D40;
    }

    .sidebar { 
        width: var(--sidebar-width); 
        height: 100vh; 
        position: fixed; 
        top: 0; 
        left: 0; 
        background-color: #ffffff !important; 
        border-right: 1px solid #eee; 
        z-index: 1050; 
        transition: transform 0.3s ease-in-out;
    }

    .sidebar .nav-link { 
        color: #555 !important; 
        padding: 12px 20px; 
        margin: 4px 12px; 
        border-radius: 10px; 
        transition: all 0.2s;
        text-decoration: none !important;
        display: flex;
        align-items: center;
    }

    .sidebar .nav-link:hover { 
        background-color: #f0f7f6 !important; 
        color: var(--nuqtah-teal) !important; 
    }

    .sidebar .nav-link.active { 
        background-color: var(--nuqtah-teal) !important; 
        color: #ffffff !important; 
        box-shadow: 0 4px 12px rgba(0, 121, 107, 0.2);
    }

    .sidebar .nav-link i {
        margin-right: 12px;
        font-size: 1.1rem;
    }

    @media (max-width: 991.98px) {
        .sidebar { transform: translateX(-100%); }
        .sidebar.active { transform: translateX(0); box-shadow: 10px 0 25px rgba(0,0,0,0.1); }
    }

    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.4);
        z-index: 1040; 
    }
    .sidebar-overlay.active { display: block; }
</style>

<div class="sidebar d-flex flex-column p-3 shadow-sm bg-white">
    <div class="text-center mb-4 mt-2">
        <a href="index.php" class="text-decoration-none">
            <?php 
                $logo_path = "../assets/img/logoNuqtah.png";
                if (file_exists($logo_path)): 
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
        <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
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
            <a href="view_requests.php" class="nav-link <?php echo ($current_page == 'view_requests.php') ? 'active' : ''; ?>">
                <i class="bi bi-clipboard-check me-2"></i> Requests
            </a>
        </li>
        <li>
            <a href="manage_users.php" class="nav-link <?php echo ($current_page == 'manage_users.php') ? 'active' : ''; ?>">
                <i class="bi bi-people me-2"></i> Users
            </a>
        </li>
        
        <li>
            <a href="admin_logs.php" class="nav-link <?php echo ($current_page == 'admin_logs.php') ? 'active' : ''; ?>">
                <i class="bi bi-clock-history me-2"></i> Activity Logs
            </a>
        </li>
        <li>

        <li>
            <a href="report.php" class="nav-link <?php echo ($current_page == 'report.php') ? 'active' : ''; ?>">
                <i class="bi bi-bar-chart-line-fill me-2"></i> Reports
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

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        if (menuToggle && sidebar && overlay) {
            function toggleSidebar() {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            }

            menuToggle.addEventListener('click', function(e) {
                e.preventDefault();
                toggleSidebar();
            });

            overlay.addEventListener('click', toggleSidebar);
        }
    });
</script>