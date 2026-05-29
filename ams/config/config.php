<?php
/**
 * Unified Configuration File
 * ============================================================
 * Central configuration for the AMS application
 * All environment-specific settings are managed here
 * 
 * For production deployment:
 * 1. Update database credentials
 * 2. Change JWT_SECRET to a strong value
 * 3. Set API_ENV to 'production'
 * 4. Restrict ALLOWED_ORIGINS to specific domains
 * 5. Set API_DEBUG to false
 * ============================================================
 */

// ============================================================
// DATABASE CONFIGURATION
// ============================================================

// Local Development (XAMPP)
define('DB_HOST', 'localhost');
define('DB_NAME', 'gem_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', 3306);
define('DB_CHARSET', 'utf8mb4');

// Production Environment (example - uncomment and customize)
// define('DB_HOST', $_ENV['DB_HOST'] ?? 'prod-db.example.com');
// define('DB_NAME', $_ENV['DB_NAME'] ?? 'gem_db_prod');
// define('DB_USER', $_ENV['DB_USER'] ?? 'db_user');
// define('DB_PASS', $_ENV['DB_PASS'] ?? '');
// define('DB_PORT', $_ENV['DB_PORT'] ?? 3306);

// ============================================================
// API CONFIGURATION
// ============================================================

define('API_ENV', 'development'); // 'development' or 'production'
define('API_DEBUG', true); // Enable debugging in development
define('API_TIMEOUT', 30); // Request timeout in seconds
define('MAX_ITEMS_PER_PAGE', 100); // Maximum pagination limit
define('DEFAULT_ITEMS_PER_PAGE', 10); // Default pagination limit

// ============================================================
// SECURITY CONFIGURATION
// ============================================================

define('JWT_SECRET', 'your-super-secret-key-change-in-production');
define('JWT_EXPIRY', 86400); // 24 hours in seconds
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_OPTIONS', ['cost' => 10]); // Cost factor for bcrypt

// CORS Configuration
define('ALLOWED_ORIGINS', '*'); // Change to specific domain in production
define('ALLOWED_METHODS', 'GET, POST, PUT, DELETE, OPTIONS');
define('ALLOWED_HEADERS', 'Content-Type, Authorization');

// ============================================================
// PDO DATABASE CONNECTION
// ============================================================

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