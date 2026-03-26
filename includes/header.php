<nav class="navbar navbar-dark bg-teal py-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="inventory_list.php">
            <img src="assets/img/logoNuqtah_White.png" alt="ITQSHHB Logo" height="45" class="me-2">
        </a>
        <div class="ms-auto d-flex align-items-center gap-3">
                <span class="text-white fw-bold d-none d-md-inline small">
                    Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                </span>
                
                <a href="actions/logout.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </a>
            </div>
        <div class="ms-auto text-white fw-bold">
            <i class="bi bi-cart3 me-1"></i> My Cart
        </div>
    </div>
</nav>