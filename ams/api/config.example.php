<?php
/**
 * Environment Configuration Example
 * Copy this to .env and update values for your environment
 * 
 * For production, use environment variables instead of hardcoding values:
 * $db = $_ENV['DB_NAME'] ?? 'gem_db';
 */

// ============================================================
// DATABASE CONFIGURATION
// ============================================================

// Development Environment
define('DB_HOST', 'localhost');
define('DB_NAME', 'gem_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', 3306);
define('DB_CHARSET', 'utf8mb4');

// Production Environment (example)
// define('DB_HOST', $_ENV['DB_HOST'] ?? 'prod-db.example.com');
// define('DB_NAME', $_ENV['DB_NAME'] ?? 'gem_db_prod');
// define('DB_USER', $_ENV['DB_USER'] ?? 'db_user');
// define('DB_PASS', $_ENV['DB_PASS'] ?? '');
// define('DB_PORT', $_ENV['DB_PORT'] ?? 3306);

// ============================================================
// API CONFIGURATION
// ============================================================

define('API_ENV', 'development'); // 'development' or 'production'
define('API_DEBUG', true); // Enable debugging
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
define('ALLOW_CREDENTIALS', false);

// ============================================================
// LOGGING CONFIGURATION
// ============================================================

define('LOG_ENABLED', true);
define('LOG_PATH', __DIR__ . '/../logs/');
define('LOG_LEVEL', 'INFO'); // 'DEBUG', 'INFO', 'WARNING', 'ERROR'

// Create logs directory if it doesn't exist
if (!is_dir(LOG_PATH)) {
    @mkdir(LOG_PATH, 0755, true);
}

// ============================================================
// API RESPONSE CONFIGURATION
// ============================================================

define('RESPONSE_JSON_PRETTY_PRINT', API_DEBUG); // Pretty print JSON in development
define('RESPONSE_INCLUDE_SQL', API_DEBUG); // Include SQL queries in errors (development only)

// ============================================================
// PAGINATION DEFAULTS
// ============================================================

define('PAGINATION_DEFAULT_PAGE', 1);
define('PAGINATION_DEFAULT_LIMIT', 10);

// ============================================================
// DATE & TIME CONFIGURATION
// ============================================================

define('TIMEZONE', 'Asia/Manila');
date_default_timezone_set(TIMEZONE);

// ============================================================
// FILE UPLOAD CONFIGURATION
// ============================================================

define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('UPLOAD_ALLOWED_TYPES', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']);

// ============================================================
// CACHE CONFIGURATION
// ============================================================

define('CACHE_ENABLED', false); // Enable caching
define('CACHE_TTL', 3600); // Time to live in seconds (1 hour)

// ============================================================
// RATE LIMITING CONFIGURATION
// ============================================================

define('RATE_LIMIT_ENABLED', false);
define('RATE_LIMIT_REQUESTS', 100); // Requests per window
define('RATE_LIMIT_WINDOW', 3600); // Window in seconds (1 hour)

// ============================================================
// EMAIL CONFIGURATION
// ============================================================

define('MAIL_DRIVER', 'smtp'); // 'smtp' or 'mail'
define('MAIL_HOST', 'smtp.example.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your-email@example.com');
define('MAIL_PASSWORD', '');
define('MAIL_FROM', 'noreply@ams-system.local');
define('MAIL_FROM_NAME', 'AMS System');

// ============================================================
// NOTIFICATION CONFIGURATION
// ============================================================

define('NOTIFY_ON_ERROR', true);
define('NOTIFY_EMAIL', 'admin@example.com');

// ============================================================
// ADMIN SETTINGS
// ============================================================

define('ADMIN_EMAIL', 'admin@example.com');
define('SYSTEM_NAME', 'Academic Management System');
define('SYSTEM_VERSION', '1.0.0');
define('SYSTEM_AUTHOR', 'Your Organization');

// ============================================================
// FEATURE FLAGS
// ============================================================

define('FEATURE_JWT_AUTH', false); // Enable JWT authentication
define('FEATURE_WEBHOOK', false); // Enable webhooks
define('FEATURE_GRAPHQL', false); // Enable GraphQL endpoint
define('FEATURE_API_VERSIONING', false); // Enable API versioning

?>
