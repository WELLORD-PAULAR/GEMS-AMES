<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../login/SessionManager.php';
require_once __DIR__ . '/GeneratePDF.php';

use Classes\GeneratePDF;

SessionManager::requireAuth();
SessionManager::requireRole(['ADMIN', 'TEACHER']);

$fk = isset($_GET['fk']) ? trim($_GET['fk']) : '';
$studentId = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;

if (empty($fk) && $studentId <= 0) {
    http_response_code(400);
    echo 'Missing fk or student_id parameter.';
    exit;
}

// -----------------------------------------------------------------------
// If only student_id was given, look up the fk_full_name_bd key first.
// The enrollment2 table does not have a numeric PK, so we join via
// user_account_id or search all tables. We accept either:
//   ?fk=FIRSTNAME_MIDDLENAME_LASTNAME_YYYYMMDD
//   ?student_id=<numeric id from enrollment_address2 / enrollment_medical2>
// -----------------------------------------------------------------------
if (empty($fk) && $studentId > 0) {
    // Try to find fk from any child table using numeric id
    foreach (['enrollment_address2', 'enrollment_medical2', 'enrollment_parent2', 'enrollment_special_needs2'] as $tbl) {
        $stmt = $pdo->prepare("SELECT fk_full_name_bd FROM `{$tbl}` WHERE id = ? LIMIT 1");
        $stmt->execute([$studentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $fk = $row['fk_full_name_bd'];
            break;
        }
    }
    if (empty($fk)) {
        http_response_code(404);
        echo 'Student record not found for student_id=' . $studentId;
        exit;
    }
}

// -----------------------------------------------------------------------
// Pull all five tables by fk_full_name_bd and merge into one flat array
// -----------------------------------------------------------------------

// 1. Core enrollment (no numeric PK)
$stmt = $pdo->prepare('SELECT e.*, mt.name AS mother_tongue_name, r.name AS religion_name, ig.name AS indigenous_group_name
    FROM enrollment2 e
    LEFT JOIN mother_tongue mt ON mt.id = e.pi_mother_tongue_id
    LEFT JOIN religion r ON r.id = e.pi_religion_id
    LEFT JOIN indigenous_group ig ON ig.id = e.ac_indigenous_group_id
    WHERE e.fk_full_name_bd = ?
    LIMIT 1');
$stmt->execute([$fk]);
$enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$enrollment) {
    http_response_code(404);
    echo 'Enrollment record not found for key: ' . htmlspecialchars($fk);
    exit;
}

// 2. Address
$stmt = $pdo->prepare('SELECT * FROM enrollment_address2 WHERE fk_full_name_bd = ? ORDER BY id DESC LIMIT 1');
$stmt->execute([$fk]);
$address = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

// 3. Medical
$stmt = $pdo->prepare('SELECT * FROM enrollment_medical2 WHERE fk_full_name_bd = ? ORDER BY id DESC LIMIT 1');
$stmt->execute([$fk]);
$medical = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

// 4. Parents
$stmt = $pdo->prepare('SELECT * FROM enrollment_parent2 WHERE fk_full_name_bd = ? ORDER BY id DESC LIMIT 1');
$stmt->execute([$fk]);
$parents = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

// 5. Special Needs
$stmt = $pdo->prepare('SELECT * FROM enrollment_special_needs2 WHERE fk_full_name_bd = ? ORDER BY id DESC LIMIT 1');
$stmt->execute([$fk]);
$specialNeeds = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

// Merge all into one flat data array (enrollment wins on key conflicts)
$data = array_merge($address, $medical, $parents, $specialNeeds, $enrollment);

// -----------------------------------------------------------------------
// Generate and stream the combined PDF
// -----------------------------------------------------------------------
try {
    $g = new GeneratePDF();
    $path = $g->generate($data, true);          // save=true → returns path
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($path) . '"');
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo 'PDF generation error: ' . $e->getMessage();
    exit;
}