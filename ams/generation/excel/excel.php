<?php
require_once __DIR__ . '/GenerateExcel.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../login/SessionManager.php';

use Classes\GenerateExcel;

SessionManager::requireAuth();
SessionManager::requireRole(['ADMIN', 'TEACHER']);

$student_id = $_GET['student_id'] ?? null;
$type = $_GET['type'] ?? 'medical';

if (!$student_id) {
    http_response_code(400);
    echo 'missing student_id';
    exit;
}

try {
    $g = new GenerateExcel();
    $path = $g->generateFromStudentDb($pdo, $student_id, $type);
    if (!file_exists($path)) {
        throw new \RuntimeException('Generated file not found: ' . $path);
    }
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($path) . '"');
    readfile($path);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo $e->getMessage();
    exit;
}

?>
