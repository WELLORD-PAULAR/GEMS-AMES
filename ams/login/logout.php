<?php

require_once __DIR__ . '/SessionManager.php';

SessionManager::logout();

header('Location: ./');
exit;
?>
