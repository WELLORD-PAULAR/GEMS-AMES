<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Model.php';
require_once __DIR__ . '/../../classes/models/Enrollment.php';
require_once __DIR__ . '/../../classes/models/EnrollmentAddress.php';
require_once __DIR__ . '/../../classes/models/EnrollmentMedical.php';
require_once __DIR__ . '/../../classes/models/EnrollmentParents.php';
require_once __DIR__ . '/../../classes/models/EnrollmentSpecialNeeds.php';
require_once __DIR__ . '/../../handlers/EnrollmentHandler.php';

use AMS\Database;
use AMS\Handlers\EnrollmentHandler;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ./');
    exit;
}

$post = $_POST;

$setFields = [
    'li_learning_modality',
    'mf_o_medical_conditions',
    'mf_mc_conditions',
    'snep_a1_diagnosis',
    'snep_a2_manifestations',
];

foreach ($setFields as $field) {
    if (isset($post[$field]) && is_array($post[$field])) {
        $post[$field] = implode(',', $post[$field]);
    }
}

$db = new Database($pdo);
$handler = new EnrollmentHandler($db);
$result = $handler->handle($post);

session_start();
$_SESSION['enrollment_result'] = $result;

if ($result['success']) {
    header('Location: ./index.php?success=1&enrollment_id=' . urlencode($result['enrollment_id']));
} else {
    header('Location: ./index.php?error=' . urlencode($result['message']));
}
exit;
?>