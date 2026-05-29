<?php

require_once __DIR__ . '/login/SessionManager.php';
require_once __DIR__ . '/config/config.php';

$isAuthenticated = SessionManager::isAuthenticated();
$user = $isAuthenticated ? SessionManager::getUser() : null;
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
                <li><strong>Lookup Endpoint:</strong> <code>./api/search.php</code></li>
                <li><strong>Database:</strong> <?php echo htmlspecialchars(DB_NAME); ?></li>
                <li><strong>Environment:</strong> <?php echo htmlspecialchars(API_ENV); ?></li>
                <li><strong>Architecture:</strong> Server-side OOP + PDO with lightweight AJAX lookup support</li>
            </ul>
        </section>

        <section id="endpoints">
            <h2>Available Lookup Endpoints</h2>
            <ul>
                <li><code>GET /api/search.php?type=mother-tongue&amp;q=...</code> - Autocomplete lookup</li>
                <li><code>GET /api/search.php?type=religions&amp;q=...</code> - Autocomplete lookup</li>
                <li><code>GET /api/search.php?type=indigenous-groups&amp;q=...</code> - Autocomplete lookup</li>
            </ul>
            <p>Authentication and enrollment processing are handled server-side in <code>config/</code>, <code>classes/</code>, and <code>handlers/</code>.</p>
        </section>

        <section id="quick-start">
            <h2>Quick Start</h2>
            <ol>
                <li><a href="./login/">Go to login page</a></li>
                <li>Enter your credentials</li>
                <li>Access your dashboard</li>
                <li>Use the form interface for enrollment and lookup workflows</li>
            </ol>
        </section>
    </main>
</body>
</html>
