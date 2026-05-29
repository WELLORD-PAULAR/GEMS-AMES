<?php
/**
 * AMS Home Page
 */

require_once __DIR__ . '/login/SessionManager.php';
require_once __DIR__ . '/config/config.php';

$isAuthenticated = SessionManager::isAuthenticated();
$user = $isAuthenticated ? SessionManager::getUser() : null;

// Test API connection
$apiStatus = 'Offline';
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$testApiUrl = $protocol . '://' . $host . '/WEBSYST1_FINAL/ams/api/users';

$response = SessionManager::apiRequest($testApiUrl, 'GET');
if ($response['success']) {
    $apiStatus = 'Online';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMS - Academic Management System</title>
</head>
<body>
    <header>
        <h1>Academic Management System (AMS)</h1>
        <p>Student enrollment and academic records management</p>
    </header>

    <nav id="main-nav">
        <ul>
            <li><a href="./">Home</a></li>
            <li><a href="./api/">API Documentation</a></li>
        </ul>
    </nav>

    <main>
        <section id="auth-section">
            <h2>Access Portal</h2>
            <?php if ($isAuthenticated && $user): ?>
                <p>Welcome, <strong><?php echo htmlspecialchars($user['username']); ?></strong>!</p>
                <ul>
                    <li><a href="./dashboard/admin_dashboard/">Admin Dashboard</a></li>
                    <li><a href="./dashboard/teacher_dashboard/">Teacher Dashboard</a></li>
                    <li><a href="./login/logout.php">Logout</a></li>
                </ul>
            <?php else: ?>
                <p><a href="./login/">Login to AMS</a></p>
            <?php endif; ?>
        </section>

        <section id="system-info">
            <h2>System Information</h2>
            <ul>
                <li><strong>Base URL:</strong> http://localhost/WEBSYST1_FINAL/ams/</li>
                <li><strong>API Base:</strong> http://localhost/WEBSYST1_FINAL/ams/api/</li>
                <li><strong>Database:</strong> <?php echo htmlspecialchars(DB_NAME); ?></li>
                <li><strong>Environment:</strong> <?php echo htmlspecialchars(API_ENV); ?></li>
                <li><strong>API Status:</strong> <?php echo $apiStatus; ?></li>
            </ul>
        </section>

        <section id="endpoints">
            <h2>Available Endpoints</h2>
            <ul>
                <li><code>POST /api/auth</code> - User login</li>
                <li><code>GET /api/users</code> - List all users</li>
                <li><code>GET /api/enrollments</code> - List enrollments</li>
                <li><code>GET /api/addresses</code> - List addresses</li>
                <li><code>GET /api/medical</code> - List medical records</li>
                <li><code>GET /api/parents</code> - List parent records</li>
                <li><code>GET /api/special-needs</code> - List special needs</li>
                <li><code>GET /api/mother-tongue</code> - List mother tongues</li>
                <li><code>GET /api/religions</code> - List religions</li>
                <li><code>GET /api/indigenous-groups</code> - List indigenous groups</li>
            </ul>
            <p><a href="./api/API_DOCUMENTATION.md">View Full API Documentation</a></p>
        </section>

        <section id="quick-start">
            <h2>Quick Start</h2>
            <ol>
                <li><a href="./login/">Go to login page</a></li>
                <li>Enter your credentials</li>
                <li>Access your dashboard</li>
                <li>Use the test buttons to verify API connectivity</li>
            </ol>
        </section>
    </main>
</body>
</html>
