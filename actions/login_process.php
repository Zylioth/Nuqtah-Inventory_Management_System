<?php
session_start();
require '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_btn'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Verify password and check if account is active
    if ($user && password_verify($password, $user['password'])) {
        
        if ($user['account_status'] === 'Pending') {
            header("Location: ../login.php?error=account_pending");
            exit();
        }

        // Set Session Variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];

        // Redirect based on role
        if ($user['role'] === 'Admin') {
            header("Location: ../admin/index.php");
        } else {
            header("Location: ../index.php");
        }
        exit();
    } else {
        header("Location: ../login.php?error=invalid_credentials");
        exit();
    }
}
?>