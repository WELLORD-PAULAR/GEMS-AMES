<?php
require_once __DIR__ . '/../../login/SessionManager.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../classes/Database.php';

use AMS\Database;

SessionManager::requireAuth();
SessionManager::requireRole(['TEACHER']);

$enrollmentId = $_GET['id'] ?? null;
if (!$enrollmentId) {
    http_response_code(400);
    echo 'Missing enrollment ID.';
    exit;
}

try {
    $db = new Database($pdo);
    $db->query('SELECT fk_full_name_bd, pi_first_name, pi_last_name, ed_grade_level, ed_school_year, verification FROM enrollment2 WHERE fk_full_name_bd = ?', [$enrollmentId]);
    $enrollment = $db->fetch();
} catch (Exception $e) {
    http_response_code(500);
    echo 'Unable to load enrollment data.';
    exit;
}

if (!$enrollment) {
    http_response_code(404);
    echo 'Enrollment not found.';
    exit;
}

function pdfEscape($text) {
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
}

$lines = [
    'Student Enrollment',
    '-------------------',
    'Enrollment ID: ' . ($enrollment['fk_full_name_bd'] ?? ''),
    'Name: ' . trim(($enrollment['pi_first_name'] ?? '') . ' ' . ($enrollment['pi_last_name'] ?? '')),
    'Grade Level: ' . ($enrollment['ed_grade_level'] ?? 'N/A'),
    'School Year: ' . ($enrollment['ed_school_year'] ?? 'N/A'),
    'Verification Status: ' . ($enrollment['verification'] ?? 'N/A'),
];

$stream = "BT /F1 18 Tf 50 760 Td (" . pdfEscape($lines[0]) . ") Tj ET\n";
$y = 730;
foreach (array_slice($lines, 1) as $line) {
    $stream .= "BT /F1 12 Tf 50 $y Td (" . pdfEscape($line) . ") Tj ET\n";
    $y -= 20;
}

$streamLength = strlen($stream);

$objects = [];
$objects[1] = "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n";
$objects[2] = "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> >> endobj\n";
$objects[3] = "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 5 0 R /Resources << /Font << /F1 4 0 R >> >> >> endobj\n";
$objects[4] = "4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n";
$objects[5] = "5 0 obj << /Length $streamLength >> stream\n" . $stream . "endstream endobj\n";

$pdfBody = "%PDF-1.4\n";
$offsets = [0];
foreach ($objects as $obj) {
    $offsets[] = strlen($pdfBody);
    $pdfBody .= $obj;
}

$xref = "xref\n0 6\n0000000000 65535 f \n";
for ($i = 1; $i <= 5; $i++) {
    $xref .= sprintf('%010d 00000 n \n', $offsets[$i]);
}

$pdf = $pdfBody;
$startXref = strlen($pdf);
$pdf .= $xref;
$pdf .= "trailer << /Size 6 /Root 1 0 R >>\nstartxref\n" . $startXref . "\n%%EOF";

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="enrollment_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $enrollmentId) . '.pdf"');
echo $pdf;
exit;
