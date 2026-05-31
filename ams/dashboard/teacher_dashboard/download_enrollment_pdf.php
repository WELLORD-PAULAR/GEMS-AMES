<?php

require_once __DIR__ . '/../../login/SessionManager.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../generation/pdf/GeneratePDF.php';

use Classes\GeneratePDF;

SessionManager::requireAuth();
SessionManager::requireRole(['TEACHER', 'ADMIN']);

// ── Validate input ──────────────────────────────────────────────────────────
$enrollmentId = trim($_GET['id'] ?? '');
if ($enrollmentId === '') {
    http_response_code(400);
    exit('Missing enrollment ID.');
}

// ── Load enrollment data ────────────────────────────────────────────────────
try {
    // Core enrollment + lookup joins (mother_tongue, religion, indigenous_group)
    $stmt = $pdo->prepare("
        SELECT
            e.*,
            mt.name  AS mother_tongue_name,
            r.name   AS religion_name,
            ig.name  AS indigenous_group_name
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
        exit('Enrollment not found.');
    }

    // Address
    $stmt = $pdo->prepare("SELECT * FROM enrollment_address2 WHERE fk_full_name_bd = ? LIMIT 1");
    $stmt->execute([$enrollmentId]);
    $address = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    // Parents / Guardian
    $stmt = $pdo->prepare("SELECT * FROM enrollment_parent2 WHERE fk_full_name_bd = ? LIMIT 1");
    $stmt->execute([$enrollmentId]);
    $parents = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    // Medical
    $stmt = $pdo->prepare("SELECT * FROM enrollment_medical2 WHERE fk_full_name_bd = ? LIMIT 1");
    $stmt->execute([$enrollmentId]);
    $medical = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    // Special Needs
    $stmt = $pdo->prepare("SELECT * FROM enrollment_special_needs2 WHERE fk_full_name_bd = ? LIMIT 1");
    $stmt->execute([$enrollmentId]);
    $specialNeeds = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

} catch (PDOException $e) {
    http_response_code(500);
    exit('Database error: ' . htmlspecialchars($e->getMessage()));
}

// ── Merge all rows into a single flat array ─────────────────────────────────
$data = array_merge(
    $enrollment,
    $address,
    $parents,
    $medical,
    $specialNeeds
);

// ── Generate and stream the PDF ─────────────────────────────────────────────
try {
    $generator = new GeneratePDF();
    $generator->generate($data);   // streams & exits
} catch (Throwable $e) {
    http_response_code(500);
    exit('PDF generation failed: ' . htmlspecialchars($e->getMessage()));
}