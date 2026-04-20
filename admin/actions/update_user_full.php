<?php
session_start();
include '../../includes/db_connect.php';
include_once '../../includes/mail_helper.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') exit('Unauthorized');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $full_name = $_POST['full_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $new_role = $_POST['role'];
    $new_status = $_POST['account_status']; 

    // Safety checks for the current Admin (prevent self-lockout)
    if ($user_id == $_SESSION['user_id']) {
        if ($new_role !== 'Admin' || $new_status !== 'Active') {
            header("Location: ../manage_users.php?msg=error");
            exit();
        }
    }

    try {
        // 1. Get current status BEFORE updating
        $checkStmt = $pdo->prepare("SELECT account_status FROM users WHERE user_id = ?");
        $checkStmt->execute([$user_id]);
        $old_status = $checkStmt->fetchColumn();

        // 2. Perform the Update
        $sql = "UPDATE users SET 
                full_name = ?, 
                username = ?, 
                email = ?, 
                role = ?, 
                account_status = ?,
                activation_token = CASE WHEN ? = 'Active' THEN NULL ELSE activation_token END 
                WHERE user_id = ?";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$full_name, $username, $email, $new_role, $new_status, $new_status, $user_id]);

        // 3. Trigger Email ONLY if status changed from Pending to Active
        if ($old_status === 'Pending' && $new_status === 'Active') {
            $subject = "Account Activated - Nuqtah Inventory System";
            $message = "
                <p>Hello <b>" . htmlspecialchars($full_name) . "</b>,</p>
                <p>Your account for <b>Nuqtah</b> has been approved by the ICT Department.</p>
                <p>You can now log in to the system to view IT assets and manage your borrowings.</p>
                <br>
                <a href='http://localhost/Nuqtah_IT/login.php' style='display:inline-block; background: #00796B; color: white; padding: 12px 25px; text-decoration: none; border-radius: 30px; font-weight: bold;'>Login to Nuqtah</a>
                <br><p style='color: #888; font-size: 11px; margin-top: 20px;'>If the button doesn't work, copy this link: http://localhost/Nuqtah_IT/login.php</p>";
            
            // Send email and log if it fails
            if (!sendNuqtahEmail($email, $full_name, $subject, $message)) {
                error_log("Failed to send activation email to: $email");
            }

            // Redirect with 'activated' message
            header("Location: ../manage_users.php?msg=activated");
        } else {
            // General update redirect
            header("Location: ../manage_users.php?msg=updated");
        }
        
    } catch (Exception $e) {
        error_log("Update User Error: " . $e->getMessage());
        header("Location: ../manage_users.php?msg=error");
    }
} else {
    header("Location: ../manage_users.php");
}
exit();