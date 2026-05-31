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

use AMS\Database;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $db = new Database($pdo);
    $fkFullNameBd = $_POST['fk_full_name_bd'] ?? null;

    if (!$fkFullNameBd) {
        echo json_encode(['success' => false, 'message' => 'Enrollment ID is required']);
        exit;
    }

    $db->beginTransaction();

    $enrollmentFields = [
        'ed_grade_level', 'ed_lrn', 'ed_school_year', 'rl_last_grade_level_completed',
        'rl_last_school_year_completed', 'rl_school_attended', 'rl_school_id', 'pi_psa_bcn',
        'pi_last_name', 'pi_first_name', 'pi_middle_name', 'pi_extension', 'pi_birth_date',
        'pi_sex', 'pi_place_of_birth', 'pi_learning_classification', 'ac_4ps_household_number',
        'verification'
    ];

    $enrollmentData = [];
    foreach ($enrollmentFields as $field) {
        if (isset($_POST[$field]) && $_POST[$field] !== '') {
            $enrollmentData[$field] = $_POST[$field];
        }
    }

    if (!empty($enrollmentData)) {
        $sets = [];
        $values = [];
        foreach ($enrollmentData as $column => $value) {
            $sets[] = "$column = ?";
            $values[] = $value;
        }
        $values[] = $fkFullNameBd;

        $sql = "UPDATE enrollment2 SET " . implode(', ', $sets) . " WHERE fk_full_name_bd = ?";
        $db->query($sql, $values);
    }

    $addressFields = [
        'ca_house_number', 'ca_street_name', 'ca_barangay', 'ca_municipality', 'ca_provice',
        'ca_country', 'ca_zipcode', 'ca_address_status', 'pa_house_number', 'pa_street_name',
        'pa_barangay', 'pa_municipality', 'pa_province', 'pa_country', 'pa_zip_code', 'pa_address_status'
    ];

    $addressData = [];
    foreach ($addressFields as $field) {
        if (isset($_POST[$field]) && $_POST[$field] !== '') {
            $addressData[$field] = $_POST[$field];
        }
    }

    if (!empty($addressData)) {
        $addressData['fk_full_name_bd'] = $fkFullNameBd;
        $db->query("SELECT * FROM enrollment_address2 WHERE fk_full_name_bd = ?", [$fkFullNameBd]);
        $addressExists = $db->fetch();

        if ($addressExists) {
            $sets = [];
            $values = [];
            foreach ($addressData as $column => $value) {
                if ($column !== 'fk_full_name_bd') {
                    $sets[] = "$column = ?";
                    $values[] = $value;
                }
            }
            $values[] = $fkFullNameBd;
            $sql = "UPDATE enrollment_address2 SET " . implode(', ', $sets) . " WHERE fk_full_name_bd = ?";
            $db->query($sql, $values);
        } else {
            $columns = array_keys($addressData);
            $placeholders = array_fill(0, count($columns), '?');
            $values = array_values($addressData);
            $sql = "INSERT INTO enrollment_address2 (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
            $db->query($sql, $values);
        }
    }

    $medicalFields = [
        'mf_a_medicine', 'mf_a_pollen', 'mf_a_food', 'mf_a_others', 'mf_o_others',
        'mf_tm_type', 'mf_o_pertinent_information'
    ];

    $medicalData = [];
    foreach ($medicalFields as $field) {
        if (isset($_POST[$field]) && $_POST[$field] !== '') {
            $medicalData[$field] = $_POST[$field];
        }
    }

    if (!empty($medicalData)) {
        $medicalData['fk_full_name_bd'] = $fkFullNameBd;
        $db->query("SELECT * FROM enrollment_medical2 WHERE fk_full_name_bd = ?", [$fkFullNameBd]);
        $medicalExists = $db->fetch();

        if ($medicalExists) {
            $sets = [];
            $values = [];
            foreach ($medicalData as $column => $value) {
                if ($column !== 'fk_full_name_bd') {
                    $sets[] = "$column = ?";
                    $values[] = $value;
                }
            }
            $values[] = $fkFullNameBd;
            $sql = "UPDATE enrollment_medical2 SET " . implode(', ', $sets) . " WHERE fk_full_name_bd = ?";
            $db->query($sql, $values);
        } else {
            $columns = array_keys($medicalData);
            $placeholders = array_fill(0, count($columns), '?');
            $values = array_values($medicalData);
            $sql = "INSERT INTO enrollment_medical2 (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
            $db->query($sql, $values);
        }
    }

    $parentsFields = [
        'fi_last_name', 'fi_first_name', 'fi_middle_name', 'fi_contact_number', 'fi_occupation',
        'fi_communication', 'mi_last_name', 'mi_first_name', 'mi_middle_name', 'mi_contact_number',
        'mi_occupation', 'mi_communication', 'gi_last_name', 'gi_first_name', 'gi_middle_name',
        'gi_contact_number', 'gi_occupation', 'gi_communication', 'ec_to_contact'
    ];

    $parentsData = [];
    foreach ($parentsFields as $field) {
        if (isset($_POST[$field]) && $_POST[$field] !== '') {
            $parentsData[$field] = $_POST[$field];
        }
    }

    if (!empty($parentsData)) {
        $parentsData['fk_full_name_bd'] = $fkFullNameBd;
        $db->query("SELECT * FROM enrollment_parent2 WHERE fk_full_name_bd = ?", [$fkFullNameBd]);
        $parentsExist = $db->fetch();

        if ($parentsExist) {
            $sets = [];
            $values = [];
            foreach ($parentsData as $column => $value) {
                if ($column !== 'fk_full_name_bd') {
                    $sets[] = "$column = ?";
                    $values[] = $value;
                }
            }
            $values[] = $fkFullNameBd;
            $sql = "UPDATE enrollment_parent2 SET " . implode(', ', $sets) . " WHERE fk_full_name_bd = ?";
            $db->query($sql, $values);
        } else {
            $columns = array_keys($parentsData);
            $placeholders = array_fill(0, count($columns), '?');
            $values = array_values($parentsData);
            $sql = "INSERT INTO enrollment_parent2 (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
            $db->query($sql, $values);
        }
    }

    $specialNeedsFields = [
        'snep_a1_diagnosis', 'snep_a1_sub_shpcd', 'snep_a1_sub_vi', 'snep_a2_manifestations'
    ];
    
    $arrayFields = ['snep_a1_diagnosis', 'snep_a2_manifestations'];

    $specialNeedsData = [];
    foreach ($specialNeedsFields as $field) {
        if (isset($_POST[$field]) && $_POST[$field] !== '') {
            if (in_array($field, $arrayFields) && is_array($_POST[$field])) {
                $specialNeedsData[$field] = implode(',', $_POST[$field]);
            } else {
                $specialNeedsData[$field] = $_POST[$field];
            }
        }
    }

    if (!empty($specialNeedsData)) {
        $specialNeedsData['fk_full_name_bd'] = $fkFullNameBd;
        $db->query("SELECT * FROM enrollment_special_needs2 WHERE fk_full_name_bd = ?", [$fkFullNameBd]);
        $specialNeedsExist = $db->fetch();

        if ($specialNeedsExist) {
            $sets = [];
            $values = [];
            foreach ($specialNeedsData as $column => $value) {
                if ($column !== 'fk_full_name_bd') {
                    $sets[] = "$column = ?";
                    $values[] = $value;
                }
            }
            $values[] = $fkFullNameBd;
            $sql = "UPDATE enrollment_special_needs2 SET " . implode(', ', $sets) . " WHERE fk_full_name_bd = ?";
            $db->query($sql, $values);
        } else {
            $columns = array_keys($specialNeedsData);
            $placeholders = array_fill(0, count($columns), '?');
            $values = array_values($specialNeedsData);
            $sql = "INSERT INTO enrollment_special_needs2 (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
            $db->query($sql, $values);
        }
    }

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Enrollment updated successfully'
    ]);

} catch (\Exception $e) {
    $db->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
