<?php
// ============================================================
// Database Configuration
// ⚠️  SECURITY WARNING - PRODUCTION DEPLOYMENT
// ============================================================
// This configuration uses root user with no password.
// This is ONLY acceptable for local development (XAMPP).
//
// BEFORE PRODUCTION DEPLOYMENT:
// 1. Create a dedicated database user with limited privileges
// 2. Use a strong password (20+ characters, mixed case, numbers, symbols)
// 3. Use environment variables or a separate secure config file
// 4. Never commit credentials to version control
// 5. Use PHP_ENV variable to switch between dev/prod configs
//
// Example production setup:
// $host = $_ENV['DB_HOST'] ?? 'localhost';
// $db   = $_ENV['DB_NAME'] ?? 'gems_db';
// $user = $_ENV['DB_USER'] ?? 'app_user';
// $pass = $_ENV['DB_PASS'] ?? '';
// ============================================================

$host = "localhost";
$db   = "gem_db";
$user = "root";
$pass = "";
$char = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$char;port=3306";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    exit("Connection failed: " . $e->getMessage());
}