<?php
session_start();
include 'includes/db_connect.php';
// Include the mail helper
include_once 'includes/mail_helper.php';

// Redirect if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: inventory_list.php");
    exit();
}

// Fetch items in the cart
$cart_ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($cart_ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM assets WHERE asset_id IN ($placeholders)");
$stmt->execute($cart_ids);
$cart_items = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['full_name']; // Make sure this is set in login
    $user_email = $_SESSION['email'];     // Make sure this is set in login
    $return_date = $_POST['return_date'];

    try {
        $pdo->beginTransaction();
        
        $insert = $pdo->prepare("INSERT INTO borrowing_requests (user_id, asset_id, quantity, return_date, status) VALUES (?, ?, ?, ?, 'Pending')");
        
        $emailItemList = "<ul>"; // Start building the HTML list for the email

        foreach ($_SESSION['cart'] as $asset_id => $qty) {
            $check_stock = $pdo->prepare("SELECT asset_name, current_stock FROM assets WHERE asset_id = ?");
            $check_stock->execute([$asset_id]);
            $asset = $check_stock->fetch();

            if ($qty > $asset['current_stock']) {
                throw new Exception("Sorry, only " . $asset['current_stock'] . " units of " . $asset['asset_name'] . " are available.");
            }

            $insert->execute([$user_id, $asset_id, $qty, $return_date]);
            
            // Add item to the email list string
            $emailItemList .= "<li>" . htmlspecialchars($asset['asset_name']) . " (Qty: $qty)</li>";
        }

        $emailItemList .= "</ul>";
        $pdo->commit();

        // --- EMAIL NOTIFICATION LOGIC ---
        
        // 1. Send Receipt to User
        $userSubject = "Request Received - Nuqtah Inventory";
        $userBody = "
            <p>Hello <b>$user_name</b>,</p>
            <p>Your request for the following items has been submitted:</p>
            $emailItemList
            <p><b>Expected Return:</b> $return_date</p>
            <p><b>Status:</b> Pending Approval</p>
            <p>Please wait for an official approval email before collecting from the ICT Department.</p>";
        
        sendNuqtahEmail($user_email, $user_name, $userSubject, $userBody);


        
        // 2. Fetch all Active Admins and Notify them
        $adminStmt = $pdo->prepare("SELECT email, full_name FROM users WHERE role = 'Admin' AND account_status = 'Active'");
        $adminStmt->execute();
        $admins = $adminStmt->fetchAll();

        foreach ($admins as $admin) {
            $adminSubject = "ACTION REQUIRED: New Request from $user_name";
            $adminBody = "
                <h3 style='color: #00796B;'>New Borrow Request</h3>
                <p><b>From:</b> $user_name ($user_email)</p>
                <p><b>Items:</b></p>
                $emailItemList
                <p><b>Required Until:</b> $return_date</p>
                <br>
                <a href='http://localhost/Nuqtah_IT/admin/view_requests.php' 
                   style='background: #00796B; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>
                   Review Request Now
                </a>";
                
            sendNuqtahEmail($admin['email'], $admin['full_name'], $adminSubject, $adminBody);
        }

        unset($_SESSION['cart']); 
        header("Location: inventory_list.php?msg=submitted");
        exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Review Request - Nuqtah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger rounded-4 mb-4"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm rounded-4 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold m-0">Review Your Request</h2>
                    <a href="inventory_list.php" class="btn btn-outline-secondary btn-sm rounded-pill">Add More Items</a>
                </div>

                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Item Details</th>
                            <th>Quantity</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): 
                                $id = $item['asset_id'];
                                $qty = $_SESSION['cart'][$id];
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($item['asset_name']); ?></div>
                                    <small class="text-muted"><?php echo $item['category']; ?></small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="actions/update_cart.php?id=<?php echo $id; ?>&action=minus" class="btn btn-sm btn-outline-secondary">-</a>
                                        <span class="mx-3 fw-bold"><?php echo $qty; ?></span>
                                        <a href="actions/update_cart.php?id=<?php echo $id; ?>&action=plus" class="btn btn-sm btn-outline-secondary">+</a>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <a href="actions/remove_from_cart.php?id=<?php echo $id; ?>" class="text-danger">
                                        <i class="bi bi-trash"></i>
                                    </a>                                
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <form method="POST" class="mt-4 pt-3 border-top">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Expected Return Date</label>
                        <input type="date" name="return_date" class="form-control rounded-3" required min="<?php echo date('Y-m-d'); ?>">
                        <small class="text-muted">Please select when you plan to return all these items to the ICT Department.</small>
                    </div>
                    
                    <button type="submit" class="btn btn-teal w-100 py-3 rounded-pill fw-bold text-white" style="background-color: #00796B;">
                        Confirm & Submit Request
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>