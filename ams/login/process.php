<?php
require_once __DIR__ . '/SessionManager.php';
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ./');
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header('Location: ./index.php?error=' . urlencode('Username and password are required'));
    exit;
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$apiUrl = $protocol . '://' . $host . '/GEMS-AMES/ams/api/index.php?request=auth';

$response = SessionManager::apiRequest($apiUrl, 'POST', [
    'username' => $username,
    'password' => $password
]);

if (!$response['success']) {
    $errorMessage = $response['data']['message'] ?? 'Authentication failed';
    header('Location: ./index.php?error=' . urlencode($errorMessage));
    exit;
}

$data = $response['data']['data'] ?? null;

if (!$data || !isset($data['token']) || !isset($data['user'])) {
    header('Location: ./index.php?error=' . urlencode('Invalid response format from server'));
    exit;
}

SessionManager::setAuth(
    $data['token'],
    $data['user'],
    $data['expires_at']
);

SessionManager::redirectToDashboard();
?>
