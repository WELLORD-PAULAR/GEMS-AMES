<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'gem_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', 3306);
define('DB_CHARSET', 'utf8mb4');

define('API_ENV', 'development');
define('API_DEBUG', true);

define('JWT_SECRET', 'your-super-secret-key-change-in-production');
define('JWT_EXPIRY', 86400);

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET . ";port=" . DB_PORT;

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    if (API_DEBUG) {
        exit("Connection failed: " . $e->getMessage());
    } else {
        exit("Database connection error. Please contact support.");
    }
}