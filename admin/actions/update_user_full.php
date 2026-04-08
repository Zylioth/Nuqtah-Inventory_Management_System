<?php
session_start();
include '../../includes/db_connect.php';
// Include the mail helper so we can send notifications
include_once '../../includes/mail_helper.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') exit('Unauthorized');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $full_name = $_POST['full_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $new_role = $_POST['role'];
    $new_status = $_POST['account_status']; 

    // Safety checks for the current Admin
    if ($user_id == $_SESSION['user_id']) {
        if ($new_role !== 'Admin') {
            header("Location: ../manage_users.php?msg=self_demote_error");
            exit();
        }
        if ($new_status !== 'Active') {
            header("Location: ../manage_users.php?msg=error");
            exit();
        }
    }

    try {
        // 1. Get current status before updating to see if it's changing from Pending to Active
        $checkStmt = $pdo->prepare("SELECT account_status FROM users WHERE user_id = ?");
        $checkStmt->execute([$user_id]);
        $old_status = $checkStmt->fetchColumn();

        // 2. Perform the Update
        // Clear token when Admin manually activates
        $sql = "UPDATE users SET 
                full_name = ?, 
                username = ?, 
                email = ?, 
                role = ?, 
                account_status = ?,
                activation_token = NULL 
                WHERE user_id = ?";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$full_name, $username, $email, $new_role, $new_status, $user_id]);

        // 3. Trigger Email if status changed from Pending to Active
        if ($old_status === 'Pending' && $new_status === 'Active') {
            $subject = "Account Activated - Nuqtah Inventory System";
            $message = "
                <p>Hello <b>$full_name</b>,</p>
                <p>Your account for <b>Nuqtah</b> has been approved by the ICT Department.</p>
                <p>You can now log in to the system to view IT assets and manage your borrowings.</p>
                <br>
                <a href='http://localhost/Nuqtah_IT/login.php' style='display:inline-block; background: #00796B; color: white; padding: 12px 25px; text-decoration: none; border-radius: 30px; font-weight: bold;'>Login to Nuqtah</a>
                <br><p style='color: #888; font-size: 11px; margin-top: 20px;'>If the button doesn't work, copy this link: http://localhost/nuqtah/login.php</p>";
            
            sendNuqtahEmail($email, $full_name, $subject, $message);
        }
        
        header("Location: ../manage_users.php?msg=success");
    } catch (Exception $e) {
        header("Location: ../manage_users.php?msg=error");
    }
}
exit();