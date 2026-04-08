<?php
session_start();
include '../../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') exit('Unauthorized');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $full_name = $_POST['full_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $new_role = $_POST['role'];
    $new_status = $_POST['account_status']; // Catching the new status from your modal

    // Safety checks for the current Admin
    if ($user_id == $_SESSION['user_id']) {
        // 1. Prevent Admin from demoting themselves
        if ($new_role !== 'Admin') {
            header("Location: ../manage_users.php?msg=self_demote_error");
            exit();
        }
        // 2. Prevent Admin from suspending or pending their own account
        if ($new_status !== 'Active') {
            header("Location: ../manage_users.php?msg=error"); // Or create a specific msg for self-suspension
            exit();
        }
    }

    try {
        // Updated SQL to include account_status
        $sql = "UPDATE users SET 
                full_name = ?, 
                username = ?, 
                email = ?, 
                role = ?, 
                account_status = ? 
                WHERE user_id = ?";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$full_name, $username, $email, $new_role, $new_status, $user_id]);
        
        header("Location: ../manage_users.php?msg=success");
    } catch (Exception $e) {
        // You can log $e->getMessage() here if you need to debug
        header("Location: ../manage_users.php?msg=error");
    }
}
exit();