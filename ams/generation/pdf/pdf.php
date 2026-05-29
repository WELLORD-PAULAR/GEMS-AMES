<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../login/SessionManager.php';
require_once __DIR__ . '/GeneratePDF.php';

use Classes\GeneratePDF;

SessionManager::requireAuth();
SessionManager::requireRole(['ADMIN', 'TEACHER']);

$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;
$type = $_GET['type'] ?? 'combined';

if ($student_id <= 0) {
    http_response_code(400);
    echo 'missing student_id';
    exit;
}

// Read directly from DB to avoid unauthenticated HTTP calls.
$stmt = $pdo->prepare('SELECT * FROM student_school_records WHERE student_id = ? ORDER BY created_at DESC');
$stmt->execute([$student_id]);
$school = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare('SELECT * FROM student_medical_records WHERE student_id = ? ORDER BY created_at DESC');
$stmt->execute([$student_id]);
$medical = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data = [];
if (!empty($school) && is_array($school[0])) {
    foreach ($school[0] as $k => $v) $data[$k] = $v;
}
if (!empty($medical) && is_array($medical[0])) {
    foreach ($medical[0] as $k => $v) $data[$k] = $v;
}

try {
    $g = new GeneratePDF();
    $path = $g->generate($data, $type);
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($path) . '"');
    readfile($path);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo $e->getMessage();
    exit;
}

?>
