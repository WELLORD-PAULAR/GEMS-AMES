<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'gem_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', 3306);
define('DB_CHARSET', 'utf8mb4');

//API CONFIG
define('API_ENV', 'development');
define('API_DEBUG', true);
define('API_TIMEOUT', 30);
define('MAX_ITEMS_PER_PAGE', 100);
define('DEFAULT_ITEMS_PER_PAGE', 10);

//SECURITY CONFIG
define('JWT_SECRET', 'your-super-secret-key-change-in-production');
define('JWT_EXPIRY', 86400);
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_OPTIONS', ['cost' => 10]);

// CORS CONFIG
define('ALLOWED_ORIGINS', '*');
define('ALLOWED_METHODS', 'GET, POST, PUT, DELETE, OPTIONS');
define('ALLOWED_HEADERS', 'Content-Type, Authorization');

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