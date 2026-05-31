<?php
session_start();
require_once __DIR__ . '/../../login/SessionManager.php';
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

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Model.php';
require_once __DIR__ . '/../../classes/models/Enrollment.php';
require_once __DIR__ . '/../../classes/models/EnrollmentAddress.php';
require_once __DIR__ . '/../../classes/models/EnrollmentMedical.php';
require_once __DIR__ . '/../../classes/models/EnrollmentParents.php';
require_once __DIR__ . '/../../classes/models/EnrollmentSpecialNeeds.php';

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
    $enrollmentModel = new Enrollment($db);
    $db->query("SELECT * FROM enrollment2 WHERE fk_full_name_bd = ?", [$enrollmentId]);
    $enrollment = $db->fetch();

    if (!$enrollment) {
        echo json_encode(['success' => false, 'message' => 'Enrollment not found']);
        exit;
    }

    $db->query("SELECT * FROM enrollment_address2 WHERE fk_full_name_bd = ?", [$enrollmentId]);
    $address = $db->fetch();

    $db->query("SELECT * FROM enrollment_medical2 WHERE fk_full_name_bd = ?", [$enrollmentId]);
    $medical = $db->fetch();

    if ($medical) {
        if (!empty($medical['mf_o_medical_conditions'])) {
            $medical['mf_o_medical_conditions'] = array_filter(explode(',', $medical['mf_o_medical_conditions']));
        } else {
            $medical['mf_o_medical_conditions'] = [];
        }
        
        if (!empty($medical['mf_mc_conditions'])) {
            $medical['mf_mc_conditions'] = array_filter(explode(',', $medical['mf_mc_conditions']));
        } else {
            $medical['mf_mc_conditions'] = [];
        }
    }

    $db->query("SELECT * FROM enrollment_parent2 WHERE fk_full_name_bd = ?", [$enrollmentId]);
    $parents = $db->fetch();

    $db->query("SELECT * FROM enrollment_special_needs2 WHERE fk_full_name_bd = ?", [$enrollmentId]);
    $specialNeeds = $db->fetch();

    if ($specialNeeds) {
        if (!empty($specialNeeds['snep_a1_diagnosis'])) {
            $specialNeeds['snep_a1_diagnosis'] = array_filter(explode(',', $specialNeeds['snep_a1_diagnosis']));
        } else {
            $specialNeeds['snep_a1_diagnosis'] = [];
        }
        
        if (!empty($specialNeeds['snep_a2_manifestations'])) {
            $specialNeeds['snep_a2_manifestations'] = array_filter(explode(',', $specialNeeds['snep_a2_manifestations']));
        } else {
            $specialNeeds['snep_a2_manifestations'] = [];
        }
    }

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
