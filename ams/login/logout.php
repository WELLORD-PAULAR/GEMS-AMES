<?php
/**
 * Logout Handler
 */

require_once __DIR__ . '/SessionManager.php';

// Clear session
SessionManager::logout();

// Redirect to login
header('Location: ./');
exit;
?>
