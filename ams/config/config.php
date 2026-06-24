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

// DepEd Three-Term School Calendar (SY 2026-2027)
define('SCHOOL_YEAR_CURRENT', '2026-2027');
define('TERM_1_LABEL', '1st Term (June - August)');
define('TERM_2_LABEL', '2nd Term (September - November)');
define('TERM_3_LABEL', '3rd Term (December - April)');
define('TERM_SUMMER_LABEL', 'Summer');

$termLabels = [
    1 => TERM_1_LABEL,
    2 => TERM_2_LABEL,
    3 => TERM_3_LABEL
];

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET . ";port=" . DB_PORT;

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    $message = API_DEBUG ? 'Connection failed: ' . $e->getMessage() : 'Database connection error. Please contact support.';
    exit(json_encode(['success' => false, 'message' => $message]));
}