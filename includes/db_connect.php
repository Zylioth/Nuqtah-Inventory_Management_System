<?php
// Database config
$host = 'localhost';
$db   = 'nuqtah_inv';
$user = 'root';
$pass = ''; // pkai XAMPP biar kosong
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}


// Logs Aktivit
function logActivity($pdo, $admin_id, $action) {
    try {
        $stmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action_taken, timestamp) VALUES (?, ?, NOW())");
        $stmt->execute([$admin_id, $action]);
    } catch (PDOException $e) {
        // Silently fail or log to a file so a logging error doesn't crash the whole app
        error_log("Logging failed: " . $e->getMessage());
    }
}
?>