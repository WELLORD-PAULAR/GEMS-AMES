<?php
session_start();

// Check if user is authenticated and is a TEACHER
require_once __DIR__ . '/../../../login/SessionManager.php';

if (!SessionManager::isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user = SessionManager::getUser();
if ($user['role'] !== 'TEACHER') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access Denied']);
    exit;
}

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../classes/Database.php';
require_once __DIR__ . '/../../../classes/Model.php';
require_once __DIR__ . '/../../../classes/models/Enrollment.php';
require_once __DIR__ . '/../../../classes/models/EnrollmentAddress.php';
require_once __DIR__ . '/../../../classes/models/EnrollmentMedical.php';
require_once __DIR__ . '/../../../classes/models/EnrollmentParents.php';
require_once __DIR__ . '/../../../classes/models/EnrollmentSpecialNeeds.php';

use AMS\Database;
use AMS\Models\Enrollment;
use AMS\Models\EnrollmentAddress;
use AMS\Models\EnrollmentMedical;
use AMS\Models\EnrollmentParents;
use AMS\Models\EnrollmentSpecialNeeds;

header('Content-Type: application/json');

$enrollmentId = $_GET['id'] ?? null;

if (!$enrollmentId) {
    echo json_encode(['success' => false, 'message' => 'Enrollment ID is required']);
    exit;
}

try {
    $db = new Database($pdo);

    // Fetch enrollment data
    $enrollmentModel = new Enrollment($db);
    $db->query("SELECT * FROM enrollment2 WHERE fk_full_name_bd = ?", [$enrollmentId]);
    $enrollment = $db->fetch();

    if (!$enrollment) {
        echo json_encode(['success' => false, 'message' => 'Enrollment not found']);
        exit;
    }

    // Fetch address data
    $db->query("SELECT * FROM enrollment_address2 WHERE fk_full_name_bd = ?", [$enrollmentId]);
    $address = $db->fetch();

    // Fetch medical data
    $db->query("SELECT * FROM enrollment_medical2 WHERE fk_full_name_bd = ?", [$enrollmentId]);
    $medical = $db->fetch();

    // Fetch parents data
    $db->query("SELECT * FROM enrollment_parent2 WHERE fk_full_name_bd = ?", [$enrollmentId]);
    $parents = $db->fetch();

    // Fetch special needs data
    $db->query("SELECT * FROM enrollment_special_needs2 WHERE fk_full_name_bd = ?", [$enrollmentId]);
    $specialNeeds = $db->fetch();

    echo json_encode([
        'success' => true,
        'data' => [
            'enrollment' => $enrollment ?: [],
            'address' => $address ?: [],
            'medical' => $medical ?: [],
            'parents' => $parents ?: [],
            'specialNeeds' => $specialNeeds ?: []
        ]
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
