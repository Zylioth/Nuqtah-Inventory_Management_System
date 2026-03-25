<?php
// Go up one level to find the includes folder
require '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup_btn'])) {
    
    // 1. Verify reCAPTCHA
    $recaptcha_secret = "6LdkWG8sAAAAALhGukLq7d3il7siuoZTtnAFEPdN";
    $recaptcha_response = $_POST['g-recaptcha-response'];

    $verify_url = "https://www.google.com/recaptcha/api/siteverify?secret=$recaptcha_secret&response=$recaptcha_response";
    $response = file_get_contents($verify_url);
    $response_keys = json_decode($response, true);

    if (!$response_keys["success"]) {
        header("Location: ../signup.php?error=captcha_failed");
        exit();
    }

    //Capture Input
    $full_name = $_POST['full_name'];
    $username  = $_POST['username'];
    $email     = $_POST['email'];
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role      = 'Staff'; 
    $status    = 'Pending';

    //msauk database
    $sql = "INSERT INTO users (full_name, username, email, password, role, account_status) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([$full_name, $username, $email, $password, $role, $status]);
        header("Location: ../login.php?signup=success");
        exit();
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            header("Location: ../signup.php?error=user_exists");
        } else {
            die("Database Error: " . $e->getMessage());
        }
        exit();
    }
}
?>