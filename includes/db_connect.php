<?php
// Function to load .env variables into $_ENV
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Skip comments
        
        // Only split if an equal sign exists
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
            putenv(trim($name) . "=" . trim($value)); // Also sets environment variable
        }
    }
}

// Trigger the load - assuming .env is in your project root
loadEnv(__DIR__ . '/../.env');

// Database config using $_ENV
$host = $_ENV['DB_HOST'] ?? 'localhost';
$db   = $_ENV['DB_NAME'] ?? 'nuqtah_inv';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? ''; // XAMPP default is empty
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

// Logs Activity (Keep this exactly as it was)
function logActivity($pdo, $admin_id, $action) {
    try {
        $stmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action_taken, timestamp) VALUES (?, ?, NOW())");
        $stmt->execute([$admin_id, $action]);
    } catch (PDOException $e) {
        error_log("Logging failed: " . $e->getMessage());
    }
}
?>