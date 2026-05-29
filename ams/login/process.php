<?php
require_once __DIR__ . '/SessionManager.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/models/User.php';

use AMS\Database;
use AMS\Models\User;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ./');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    header('Location: ./index.php?error=' . urlencode('Username and password are required'));
    exit;
}

try {
    $db = new Database($pdo);
    $userModel = new User($db);
    $user = $userModel->findByUsername($username);

    if (!$user || !$user->verifyPassword($password)) {
        header('Location: ./index.php?error=' . urlencode('Invalid username or password'));
        exit;
    }

    $userData = $user->toArray();
    if (empty($userData['is_active'])) {
        header('Location: ./index.php?error=' . urlencode('User account is not active'));
        exit;
    }

    unset($userData['password_hash']);
    $token = bin2hex(random_bytes(16));
    $expiresAt = date('c', time() + JWT_EXPIRY);

    SessionManager::setAuth($token, $userData, $expiresAt);
    SessionManager::redirectToDashboard();
} catch (\Exception $e) {
    header('Location: ./index.php?error=' . urlencode('Login error: ' . $e->getMessage()));
    exit;
}
?>
