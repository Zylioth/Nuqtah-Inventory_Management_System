<?php
session_start();
include '../../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') exit('Unauthorized');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $username  = $_POST['username'];
    $email     = $_POST['email'];
    $role      = $_POST['role'];
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Default status for new users created by Admin
    $account_status = 'Active'; 

    try {
        // Check if username or email already exists
        $check = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $check->execute([$username, $email]);
        
        if ($check->rowCount() > 0) {
            header("Location: ../manage_users.php?msg=exists");
            exit();
        }

        // Insert new user with account_status
        $sql = "INSERT INTO users (full_name, username, email, role, password, account_status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$full_name, $username, $email, $role, $password, $account_status]);

        header("Location: ../manage_users.php?msg=success");
    } catch (Exception $e) {
        header("Location: ../manage_users.php?msg=error");
    }
}
exit();