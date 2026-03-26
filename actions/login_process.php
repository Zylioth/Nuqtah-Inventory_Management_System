<?php
session_start();
include '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        // Use a prepared statement to prevent SQL injection
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        // Check if user exists and verify hashed password
        if ($user && password_verify($password, $user['password'])) {
            
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['status'] = $user['account_status'];

            // Redirect to the inventory list
            header("Location: ../inventory_list.php");
            exit();
            
        } else {
            // Login failed
            header("Location: ../login.php?error=invalid_credentials");
            exit();
        }
    } catch (PDOException $e) {
        // Handle database errors
        header("Location: ../login.php?error=database_error");
        exit();
    }
} else {
    header("Location: ../login.php");
    exit();
}
?>