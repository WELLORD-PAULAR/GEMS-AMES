<?php
/**
 * AMS Login Page
 */

require_once __DIR__ . '/SessionManager.php';

// If already logged in, redirect to dashboard
if (SessionManager::isAuthenticated()) {
    header('Location: /GEMS-AMES/ams/dashboard/admin_dashboard/');
    exit;
}

$error = '';
$success = '';

// Check for error/success messages from process.php
if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}
if (isset($_GET['success'])) {
    $success = htmlspecialchars($_GET['success']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AMS</title>
</head>
<body>
    <div class="login-container">
        <h1>AMS Login</h1>
        
        <?php if ($error): ?>
            <div class="error-message">
                <p><?php echo $error; ?></p>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message">
                <p><?php echo $success; ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="process.php">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">Login</button>
        </form>

        <p><a href="../">Back to AMS</a></p>
    </div>
</body>
</html>
