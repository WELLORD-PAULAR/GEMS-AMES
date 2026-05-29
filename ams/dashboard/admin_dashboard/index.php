<?php
/**
 * Admin Dashboard
 */

require_once __DIR__ . '/../../login/SessionManager.php';
require_once __DIR__ . '/../../config/config.php';

// Require authentication
SessionManager::requireAuth();

$user = SessionManager::getUser();

if (!SessionManager::hasRole('ADMIN')) {
    header('Location: ../teacher_dashboard/');
    exit;
}

$testResult = '';
$testError = '';

// Handle API test
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_api'])) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $apiUrl = $protocol . '://' . $host . '/WEBSYST1_FINAL/ams/api/users';

    $response = SessionManager::apiRequest($apiUrl, 'GET');

    if ($response['success']) {
        $testResult = json_encode($response['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } else {
        $testError = $response['data']['message'] ?? 'API request failed';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AMS</title>
</head>
<body>
    <!-- Navigation/Header -->
    <header id="navbar">
        <h1>Admin Dashboard</h1>
        <nav>
            <ul>
                <li><a href="./">Home</a></li>
                <li><a href="../../">Back to AMS</a></li>
                <li>Logged in as: <strong><?php echo htmlspecialchars($user['username']); ?></strong></li>
                <li><a href="../../login/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <main id="content">
        <h2>Welcome to Admin Dashboard</h2>
        <p>This is a skeleton dashboard. Content will be added here.</p>

        <section id="user-section">
            <h3>Current User Information</h3>
            <ul>
                <li><strong>ID:</strong> <?php echo htmlspecialchars($user['id']); ?></li>
                <li><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></li>
                <li><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></li>
                <li><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></li>
                <li><strong>Active:</strong> <?php echo $user['is_active'] ? 'Yes' : 'No'; ?></li>
            </ul>
        </section>

        <section id="test-api">
            <h3>Test API Connection</h3>
            <form method="POST">
                <button type="submit" name="test_api" value="1">Fetch Users</button>
            </form>

            <?php if ($testError): ?>
                <div class="error-message">
                    <p><strong>Error:</strong> <?php echo htmlspecialchars($testError); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($testResult): ?>
                <div class="test-result">
                    <h4>API Response</h4>
                    <pre><?php echo htmlspecialchars($testResult); ?></pre>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
