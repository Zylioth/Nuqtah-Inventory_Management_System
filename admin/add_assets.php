<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Asset - Nuqtah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --sidebar-width: 260px; --teal-primary: #00796B; }
        body { background-color: #f8f9fa; }
        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; top: 0; left: 0; background-color: white; border-right: 1px solid #eee; }
        .main-content { margin-left: var(--sidebar-width); padding: 40px; }
        .btn-teal { background-color: var(--teal-primary); color: white; border: none; }
        .btn-teal:hover { background-color: #004D40; color: white; }
    </style>
</head>
<body>

<div class="main-content">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="manage_assets.php" class="text-decoration-none">Inventory</a></li>
                    <li class="breadcrumb-item active">Add New Asset</li>
                </ol>
            </nav>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-5">
                    <h3 class="fw-bold mb-4">Add Equipment</h3>
                    
                    <?php if ($message == "success"): ?>
                        <div class="alert alert-success border-0 rounded-3">Asset added successfully! <a href="manage_assets.php">View Inventory</a></div>
                    <?php elseif ($message == "error"): ?>
                        <div class="alert alert-danger border-0 rounded-3">Failed to add asset. Please check the database.</div>
                    <?php endif; ?>

                    <form action="add_assets.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Asset Name</label>
                            <input type="text" name="asset_name" class="form-control py-2" placeholder="e.g. HP 682 Black Ink" required>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Category</label>
                                <select name="category" class="form-select py-2" required>
                                    <option value="Laptops">Laptops</option>
                                    <option value="Projectors">Projectors</option>
                                    <option value="Accessories">Accessories</option>
                                    <option value="Consumables">Consumables</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Initial Stock</label>
                                <input type="number" name="current_stock" class="form-control py-2" value="1" min="0" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Asset Image</label>
                            <input type="file" name="asset_image" class="form-control py-2" accept="image/*">
                            <small class="text-muted">Recommended size: 800x600px (JPG or PNG).</small>
                        </div>

                        <div class="d-grid mt-5">
                            <button type="submit" class="btn btn-teal py-3 rounded-3 fw-bold">Save Asset to Database</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>