<?php
// Suppress HTML error output immediately - this MUST be first
ini_set('display_errors', '0');
ini_set('html_errors', '0');
error_reporting(E_ALL);

// Convert ALL PHP errors/warnings into exceptions so they're caught below
set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
});

ob_start();

session_start();
require_once __DIR__ . '/../../login/SessionManager.php';

header('Content-Type: application/json; charset=utf-8');
ob_clean(); // discard anything leaked before this point

if (!SessionManager::isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user = SessionManager::getUser();
if ($user['role'] !== 'TEACHER' && $user['role'] !== 'ADMIN') {
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

$enrollmentId = trim($_GET['id'] ?? '');

if ($enrollmentId === '') {
    echo json_encode(['success' => false, 'message' => 'Enrollment ID is required']);
    exit;
}

try {
    $db = new Database($pdo);

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
        $medical['mf_o_medical_conditions'] = !empty($medical['mf_o_medical_conditions'])
            ? array_values(array_filter(explode(',', $medical['mf_o_medical_conditions'])))
            : [];
        $medical['mf_mc_conditions'] = !empty($medical['mf_mc_conditions'])
            ? array_values(array_filter(explode(',', $medical['mf_mc_conditions'])))
            : [];
    }

    $db->query("SELECT * FROM enrollment_parent2 WHERE fk_full_name_bd = ?", [$enrollmentId]);
    $parents = $db->fetch();

    $db->query("SELECT * FROM enrollment_special_needs2 WHERE fk_full_name_bd = ?", [$enrollmentId]);
    $specialNeeds = $db->fetch();

    if ($specialNeeds) {
        $specialNeeds['snep_a1_diagnosis'] = !empty($specialNeeds['snep_a1_diagnosis'])
            ? array_values(array_filter(explode(',', $specialNeeds['snep_a1_diagnosis'])))
            : [];
        $specialNeeds['snep_a2_manifestations'] = !empty($specialNeeds['snep_a2_manifestations'])
            ? array_values(array_filter(explode(',', $specialNeeds['snep_a2_manifestations'])))
            : [];
    }

    $payload = [
        'success' => true,
        'data'    => [
            'enrollment'   => $enrollment  ?: [],
            'address'      => $address     ?: [],
            'medical'      => $medical     ?: [],
            'parents'      => $parents     ?: [],
            'specialNeeds' => $specialNeeds ?: [],
        ],
    ];

    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    echo $json;

} catch (\Throwable $e) {
    // Discard any partial output that might have accumulated
    if (ob_get_level() > 0) {
        ob_clean();
    }
    http_response_code(500);
    $message = defined('API_DEBUG') && API_DEBUG
        ? $e->getMessage() . ' in ' . basename($e->getFile()) . ':' . $e->getLine()
        : 'Server error. Please try again.';
    echo json_encode(['success' => false, 'message' => strip_tags($message)]);
}