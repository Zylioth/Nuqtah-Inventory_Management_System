<?php
session_start();
include '../includes/db_connect.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch all users - Added account_status to the SELECT query
$query = "SELECT user_id, full_name, username, email, role, account_status, created_at FROM users ORDER BY created_at DESC";
$stmt = $pdo->query($query);
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management - Nuqtah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --sidebar-width: 260px; --teal-primary: #00796B; }
        body { background-color: #f8f9fa; }
        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; top: 0; left: 0; background-color: white; border-right: 1px solid #eee; z-index: 1000; }
        .main-content { margin-left: var(--sidebar-width); padding: 30px; }
        .nav-link { color: #555; padding: 10px 20px; margin: 2px 10px; border-radius: 8px; text-decoration: none; display: block; }
        .nav-link.active { background-color: var(--teal-primary) !important; color: white !important; }
        
        /* UI Polish */
        .bg-teal { background-color: var(--teal-primary) !important; }
        .btn-teal { background-color: var(--teal-primary); color: white; border: none; }
        .btn-teal:hover { background-color: #00695C; color: white; }
        .role-badge { font-size: 0.8rem; padding: 5px 12px; }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">User Management</h2>
            <p class="text-muted mb-0">Control access levels and manage accounts for Nuqtah.</p>
        </div>
        <button class="btn btn-teal text-white rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus-fill me-2"></i> Add New User
        </button>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4 <?php echo ($_GET['msg'] == 'success' || $_GET['msg'] == 'deleted') ? 'alert-success' : 'alert-danger'; ?>" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi <?php echo ($_GET['msg'] == 'success' || $_GET['msg'] == 'deleted') ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?> fs-5 me-3"></i>
                <div>
                    <?php 
                        if ($_GET['msg'] == 'success') echo "<strong>Success!</strong> Action completed successfully.";
                        elseif ($_GET['msg'] == 'exists') echo "<strong>Registration Error!</strong> That Username or ID already exists.";
                        elseif ($_GET['msg'] == 'self_demote_error') echo "<strong>Security Alert!</strong> You cannot change your own Admin status.";
                        elseif ($_GET['msg'] == 'self_delete_error') echo "<strong>Action Denied!</strong> You cannot delete your own account.";
                        elseif ($_GET['msg'] == 'deleted') echo "<strong>Success!</strong> User has been removed from the system.";
                        else echo "<strong>Error!</strong> Something went wrong. Please check your data.";
                    ?>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Full Name</th>
                        <th>Username / ID</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined Date</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold"><?php echo htmlspecialchars($user['full_name']); ?></div>
                            </td>
                            <td><code><?php echo htmlspecialchars($user['username']); ?></code></td>
                            <td>
                                <?php if ($user['role'] == 'Admin'): ?>
                                    <span class="badge bg-teal rounded-pill role-badge">Admin</span>
                                <?php elseif ($user['role'] == 'Staff'): ?>
                                    <span class="badge bg-primary rounded-pill role-badge">Staff</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark border rounded-pill role-badge">Student</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    $status = $user['account_status'];
                                    $badge_class = 'bg-success';
                                    if ($status == 'Suspended') $badge_class = 'bg-danger';
                                    if ($status == 'Pending') $badge_class = 'bg-warning text-dark';
                                ?>
                                <span class="badge rounded-pill <?php echo $badge_class; ?> role-badge">
                                    <?php echo htmlspecialchars($status); ?>
                                </span>
                            </td>
                            <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3" 
                                    onclick='editUser(<?php echo json_encode([
                                        "id" => $user["user_id"],
                                        "name" => $user["full_name"],
                                        "username" => $user["username"],
                                        "email" => $user["email"],
                                        "role" => $user["role"],
                                        "account_status" => $user["account_status"]
                                    ]); ?>)'>
                                    Edit Profile
                                </button>
                                <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                    <button class="btn btn-sm btn-link text-danger p-0 ms-2" title="Delete User" 
                                            onclick='confirmDelete(<?php echo json_encode(["id" => $user["user_id"], "name" => $user["full_name"]]); ?>)'>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow border-0">
            <div class="modal-header bg-teal text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus me-2"></i> Create New User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="actions/add_users.php" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Full Name</label>
                        <input type="text" name="full_name" class="form-control rounded-3" placeholder="Enter full name" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Username / ID</label>
                            <input type="text" name="username" class="form-control rounded-3" placeholder="B2023..." required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Role</label>
                            <select class="form-select rounded-3" name="role">
                                <option value="Student">Student</option>
                                <option value="Staff">Staff</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Email Address</label>
                        <input type="email" name="email" class="form-control rounded-3" placeholder="email@example.com" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-muted text-uppercase">Temporary Password</label>
                        <input type="password" name="password" class="form-control rounded-3" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-teal text-white rounded-pill px-4">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow border-0">
            <div class="modal-header bg-teal text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-gear me-2"></i> Edit User Profile</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="actions/update_user_full.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Full Name</label>
                        <input type="text" name="full_name" id="edit_full_name" class="form-control rounded-3" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Username / ID</label>
                            <input type="text" name="username" id="edit_username" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Role</label>
                            <select class="form-select rounded-3" name="role" id="edit_role">
                                <option value="Student">Student</option>
                                <option value="Staff">Staff</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Email Address</label>
                            <input type="email" name="email" id="edit_email" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Account Status</label>
                            <select class="form-select rounded-3 fw-bold" name="account_status" id="edit_account_status">
                                <option value="Active">Active</option>
                                <option value="Pending">Pending</option>
                                <option value="Suspended">Suspended</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-teal text-white rounded-pill px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content rounded-4 shadow border-0">
            <div class="modal-header border-0 pt-4 pb-0 justify-content-center">
                <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="bi bi-trash3-fill fs-3"></i>
                </div>
            </div>
            <div class="modal-body text-center p-4">
                <h5 class="fw-bold mb-2">Remove User?</h5>
                <p class="text-muted small mb-0">Are you sure you want to remove <span id="delete_user_name" class="fw-bold text-dark"></span>?</p>
                <p class="text-muted mb-0" style="font-size: 0.75rem;">This action is permanent.</p>
            </div>
            <div class="modal-footer border-0 p-4 pt-0 d-flex flex-column">
                <a href="#" id="delete_confirm_btn" class="btn btn-danger w-100 rounded-pill mb-2">Delete Account</a>
                <button type="button" class="btn btn-light w-100 rounded-pill" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editUser(user) {
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_full_name').value = user.name;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_role').value = user.role;
    // Set the account status dropdown value
    document.getElementById('edit_account_status').value = user.account_status;
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}

function confirmDelete(user) {
    document.getElementById('delete_user_name').innerText = user.name;
    document.getElementById('delete_confirm_btn').href = `actions/delete_user.php?id=${user.id}`;
    new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
}
</script>
</body>
</html>