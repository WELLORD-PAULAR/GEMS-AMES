<?php
/**
 * download_enrollment_pdf.php
 *
 * Fetches all enrollment data for a student and streams
 * a 3-page PDF (enrollment pages 1-2 + medical/consent page 3).
 *
 * Usage:  download_enrollment_pdf.php?id=<fk_full_name_bd>
 */

require_once __DIR__ . '/../../login/SessionManager.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../generation/pdf/GeneratePDF.php';

use Classes\GeneratePDF;

SessionManager::requireAuth();
SessionManager::requireRole(['TEACHER', 'ADMIN']);

// ── Validate ─────────────────────────────────────────────────────────────────
$enrollmentId = trim($_GET['id'] ?? '');
if ($enrollmentId === '') {
    http_response_code(400);
    exit('Missing enrollment ID.');
}

// ── Fetch data ───────────────────────────────────────────────────────────────
try {
    $stmt = $pdo->prepare("
        SELECT
            e.*,
            mt.name AS mother_tongue_name,
            r.name  AS religion_name,
            ig.name AS indigenous_group_name
        FROM enrollment2 e
        LEFT JOIN mother_tongue   mt ON mt.id = e.pi_mother_tongue_id
        LEFT JOIN religion         r  ON r.id  = e.pi_religion_id
        LEFT JOIN indigenous_group ig ON ig.id = e.ac_indigenous_group_id
        WHERE e.fk_full_name_bd = ?
        LIMIT 1
    ");
    $stmt->execute([$enrollmentId]);
    $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$enrollment) {
        http_response_code(404);
        exit('Enrollment record not found.');
    }

    $related = [];
    foreach (['enrollment_address2', 'enrollment_parent2',
              'enrollment_medical2', 'enrollment_special_needs2'] as $table) {
        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE fk_full_name_bd = ? LIMIT 1");
        $stmt->execute([$enrollmentId]);
        $related[] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

} catch (\PDOException $e) {
    http_response_code(500);
    exit('Database error: ' . htmlspecialchars($e->getMessage()));
}

// ── Merge & generate ─────────────────────────────────────────────────────────
$data = array_merge($enrollment, ...$related);

try {
    (new GeneratePDF())->generate($data);
} catch (\Throwable $e) {
    http_response_code(500);
    exit('PDF generation failed: ' . htmlspecialchars($e->getMessage()));
}