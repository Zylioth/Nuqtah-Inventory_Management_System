<?php
session_start();
include '../includes/db_connect.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

$message = "";
$asset_id = $_GET['id'] ?? null;

if (!$asset_id) {
    header("Location: manage_assets.php");
    exit();
}

// 1. Fetch current data
$stmt = $pdo->prepare("SELECT * FROM assets WHERE asset_id = ?");
$stmt->execute([$asset_id]);
$asset = $stmt->fetch();

if (!$asset) {
    header("Location: manage_assets.php");
    exit();
}

// 2. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['asset_name'];
    $category = $_POST['category'];
    $total_stock = $_POST['total_stock'];
    $current_stock = $_POST['current_stock'];
    $status = ($current_stock > 0) ? 'Available' : 'Out of Stock';
    
    $image_name = $asset['asset_image']; // Default to old image

    if (isset($_FILES['asset_image']) && $_FILES['asset_image']['error'] == 0) {
        $target_dir = "../assets/img/";
        $file_ext = pathinfo($_FILES["asset_image"]["name"], PATHINFO_EXTENSION);
        $image_name = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $name) . "." . $file_ext;
        move_uploaded_file($_FILES["asset_image"]["tmp_name"], $target_dir . $image_name);
        
        // Optional: Delete old image file here if it exists
    }

    try {
        $sql = "UPDATE assets SET asset_name = ?, category = ?, total_stock = ?, current_stock = ?, status = ?, asset_image = ? WHERE asset_id = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$name, $category, $total_stock, $current_stock, $status, $image_name, $asset_id])) {
            $message = "success";
            // Refresh local data
            $asset['asset_name'] = $name;
            $asset['category'] = $category;
            $asset['total_stock'] = $total_stock;
            $asset['current_stock'] = $current_stock;
            $asset['asset_image'] = $image_name;
        } else {
            $message = "error";
        }
    } catch (PDOException $e) {
        $message = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Asset - Nuqtah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --teal-primary: #00796B; }
        body { background-color: #f8f9fa; }
        .main-content { padding: 40px; min-height: 100vh; }
        .btn-teal { background-color: var(--teal-primary); color: white; border: none; }
        .btn-teal:hover { background-color: #004D40; color: white; }
        .form-card { max-width: 850px; margin: 0 auto; }
        .current-img-preview { width: 100px; height: 100px; object-fit: cover; border-radius: 10px; }
    </style>
</head>
<body>

<div class="main-content">
    <div class="container">
        <div class="form-card">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="manage_assets.php" class="text-decoration-none">Inventory</a></li>
                    <li class="breadcrumb-item active">Edit Asset</li>
                </ol>
            </nav>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-5">
                    <h3 class="fw-bold mb-4">Edit Equipment Details</h3>
                    
                    <?php if ($message == "success"): ?>
                        <div class="alert alert-success border-0 rounded-3 mb-4">Changes saved successfully!</div>
                    <?php endif; ?>

                    <form action="edit_assets.php?id=<?php echo $asset_id; ?>" method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Asset Name</label>
                            <input type="text" name="asset_name" class="form-control py-2" value="<?php echo htmlspecialchars($asset['asset_name']); ?>" required>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Category</label>
                                <select name="category" class="form-select py-2" required>
                                    <?php 
                                    $cats = ["Laptops", "Projectors", "Accessories", "Consumables"];
                                    foreach($cats as $cat) {
                                        $selected = ($asset['category'] == $cat) ? "selected" : "";
                                        echo "<option value='$cat' $selected>$cat</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Available Stock</label>
                                <input type="number" name="current_stock" class="form-control py-2" value="<?php echo $asset['current_stock']; ?>" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Total Owned</label>
                                <input type="number" name="total_stock" class="form-control py-2" value="<?php echo $asset['total_stock']; ?>" min="0" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Update Asset Image</label>
                            <div class="d-flex align-items-center bg-light p-3 rounded-3">
                                <?php if($asset['asset_image']): ?>
                                    <img src="../assets/img/<?php echo $asset['asset_image']; ?>" class="current-img-preview me-3 border">
                                <?php endif; ?>
                                <input type="file" name="asset_image" class="form-control" accept="image/*">
                            </div>
                        </div>

                        <div class="d-grid mt-5">
                            <button type="submit" class="btn btn-teal py-3 rounded-3 fw-bold">Update Asset Information</button>
                            <a href="manage_assets.php" class="btn btn-link text-muted mt-2">Cancel and Go Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>