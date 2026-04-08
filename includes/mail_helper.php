<?php
// Absolute path to ensure it finds the files from anywhere
require_once __DIR__ . '/../vendor/phpmailer/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendNuqtahEmail($toEmail, $toName, $subject, $message) {
    $mail = new PHPMailer(true);

    try {
        // SMTP Settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'nuqtah.system@gmail.com'; 
        $mail->Password   = 'xubsvdrtdssknfwl'; // Remove spaces from the code
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Branding the Sender
        $mail->setFrom('nuqtah.system@gmail.com', 'Nuqtah IT System');
        $mail->addAddress($toEmail, $toName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; border: 1px solid #eee; padding: 20px; border-radius: 10px;'>
                <h2 style='color: #00796B;'>Nuqtah Inventory System</h2>
                <hr style='border: 0; border-top: 1px solid #eee;'>
                <div style='padding: 10px 0;'>
                    $message
                </div>
                <hr style='border: 0; border-top: 1px solid #eee;'>
                <p style='font-size: 12px; color: #777;'>
                    This is an automated notification from Nuqtah Inventory System.
                </p>
            </div>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Error logging (useful for your debugging)
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}