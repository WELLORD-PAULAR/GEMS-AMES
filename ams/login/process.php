<?php
/**
 * Login Process Handler
 * Processes login form and calls API
 */

require_once __DIR__ . '/SessionManager.php';
require_once __DIR__ . '/../config/config.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ./');
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Validate input
if (empty($username) || empty($password)) {
    header('Location: ./index.php?error=' . urlencode('Username and password are required'));
    exit;
}

// Build API URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$apiUrl = $protocol . '://' . $host . '/WEBSYST1_FINAL/ams/api/auth';

// Make API request
$response = SessionManager::apiRequest($apiUrl, 'POST', [
    'username' => $username,
    'password' => $password
]);

if (!$response['success']) {
    $errorMessage = $response['data']['message'] ?? 'Authentication failed';
    header('Location: ./index.php?error=' . urlencode($errorMessage));
    exit;
}

// Extract token and user data
$data = $response['data']['data'] ?? null;

if (!$data || !isset($data['token']) || !isset($data['user'])) {
    header('Location: ./index.php?error=' . urlencode('Invalid response format from server'));
    exit;
}

// Store in session
SessionManager::setAuth(
    $data['token'],
    $data['user'],
    $data['expires_at']
);

// Redirect to dashboard
header('Location: ../dashboard/admin_dashboard/');
exit;
?>
