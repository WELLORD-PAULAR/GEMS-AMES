<?php

require_once __DIR__ . '/../../login/SessionManager.php';
require_once __DIR__ . '/../../config/config.php';

SessionManager::requireAuth();

$user = SessionManager::getUser();

if (!SessionManager::hasRole('TEACHER')) {
    header('Location: ../admin_dashboard/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - AMS</title>
</head>
<body>
    <!-- Navigation/Header -->
    <header id="navbar">
        <h1>Teacher Dashboard</h1>
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
        <h2>Welcome to Teacher Dashboard</h2>
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

        <section id="note">
            <h3>Form Access</h3>
            <p>
                <a href="../../forms/enrollment_form" class="button">Go to Enrollment Form</a>
            </p>
            <p>
                <a href="../../forms/enrollment_form/verify/verify.php" class="button">Go to Verify Enrollments</a>
            </p>
        </section>
    </main>
</body>
</html>
