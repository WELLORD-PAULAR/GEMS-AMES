<?php

require_once __DIR__ . '/../../../login/SessionManager.php';
require_once __DIR__ . '/../../../config/config.php';

SessionManager::requireAuth();

if (!SessionManager::hasRole('ADMIN')) {
    http_response_code(403);
    exit('Access Denied: Admin only.');
}

$allColumns = [
    'fk_full_name_bd' => 'ID',
    'pi_last_name' => 'Last Name',
    'pi_first_name' => 'First Name',
    'pi_middle_name' => 'Middle Name',
    'pi_extension' => 'Extension',
    'pi_sex' => 'Sex',
    'pi_birth_date' => 'Date of Birth',
    'pi_place_of_birth' => 'Place of Birth',
    'pi_psa_bcn' => 'PSA/Birth Certificate #',
    'pi_learning_classification' => 'Learning Classification',
    'ed_grade_level' => 'Grade Level',
    'ed_school_year' => 'School Year',
    'ed_lrn' => 'LRN',
    'rl_last_grade_level_completed' => 'Last Grade Level Completed',
    'rl_last_school_year_completed' => 'Last School Year Completed',
    'rl_school_attended' => 'Last School Attended',
    'rl_school_id' => 'Last School ID',
    'pi_mother_tongue_id' => 'Mother Tongue ID',
    'pi_religion_id' => 'Religion ID',
    'ac_indigenous_group_id' => 'Indigenous Group ID',
    'li_learning_modality' => 'Learning Modality',
    'verification' => 'Verification Status',
    'created-at' => 'Enrollment Date',
    'ca_house_number' => 'Address - House #',
    'ca_street_name' => 'Address - Street',
    'ca_barangay' => 'Address - Barangay',
    'ca_municipality' => 'Address - Municipality',
    'ca_provice' => 'Address - Province',
    'ca_country' => 'Address - Country',
    'ca_zipcode' => 'Address - Zip Code',
    'ca_address_status' => 'Address Status',
    'fi_last_name' => 'Father\'s Last Name',
    'fi_first_name' => 'Father\'s First Name',
    'fi_contact_number' => 'Father\'s Contact',
    'mi_last_name' => 'Mother\'s Last Name',
    'mi_first_name' => 'Mother\'s First Name',
    'mi_contact_number' => 'Mother\'s Contact',
    'gi_last_name' => 'Guardian Last Name',
    'gi_first_name' => 'Guardian First Name',
    'gi_middle_name' => 'Guardian Middle Name',
    'gi_contact_number' => 'Guardian Contact',
    'gi_occupation' => 'Guardian Occupation',
    'gi_relationship_status' => 'Guardian Relationship Status',
    'gi_communication' => 'Guardian Communication',
    'ec_to_contact' => 'Emergency Contact',
    'mf_o_medical_conditions' => 'Medical Conditions',
    'mf_o_others' => 'Medical Others',
    'mf_mc_conditions' => 'Medical Comorbidities',
    'mf_mc_others' => 'Medical Comorbidities Others',
    'mf_exposure_c_v' => 'COVID-19 Exposure',
    'mf_o_pertinent_information' => 'Medical Pertinent Information',
    'snep_a1_diagnosis' => 'Special Needs Diagnosis',
    'snep_a1_sub_shpcd' => 'Special Needs Sub-Category',
    'snep_pwd_id' => 'PWD ID',
];

$selectedColumns = $_POST['columns'] ?? [];
$selectedColumns = array_filter($selectedColumns, function($col) use ($allColumns) {
    return isset($allColumns[$col]);
});

if (empty($selectedColumns)) {
    http_response_code(400);
    exit('No columns selected. Please go back and select at least one column.');
}

$selectedSection = $_POST['section'] ?? '';
$selectedSection = trim($selectedSection);
$selectedSummaryTotals = $_POST['summary_totals'] ?? [];
$selectedSummaryTotals = array_filter(array_map('trim', (array) $selectedSummaryTotals), function($value) {
    return in_array($value, ['4ps', 'indigenous', 'mother_tongue', 'religion'], true);
});

function quoteIdentifier(string $identifier): string {
    return '`' . str_replace('`', '``', $identifier) . '`';
}

function buildSummaryCondition(string $whereClause, array $params, string $columnExpression): array {
    $condition = '';
    if ($whereClause === '') {
        $condition = ' WHERE ';
    } else {
        $condition = ' AND ';
    }

    $condition .= "TRIM(COALESCE(NULLIF({$columnExpression}, ''), '')) <> '' AND UPPER(TRIM(COALESCE({$columnExpression}, ''))) NOT IN ('NO', 'N/A', 'NA')";

    return [$condition, $params];
}

$columnMap = [
    // enrollment2 table
    'fk_full_name_bd' => 'e.fk_full_name_bd',
    'pi_last_name' => 'e.pi_last_name',
    'pi_first_name' => 'e.pi_first_name',
    'pi_middle_name' => 'e.pi_middle_name',
    'pi_extension' => 'e.pi_extension',
    'pi_sex' => 'e.pi_sex',
    'pi_birth_date' => 'e.pi_birth_date',
    'pi_place_of_birth' => 'e.pi_place_of_birth',
    'pi_psa_bcn' => 'e.pi_psa_bcn',
    'pi_learning_classification' => 'e.pi_learning_classification',
    'ed_grade_level' => 'e.ed_grade_level',
    'ed_school_year' => 'e.ed_school_year',
    'ed_lrn' => 'e.ed_lrn',
    'rl_last_grade_level_completed' => 'e.rl_last_grade_level_completed',
    'rl_last_school_year_completed' => 'e.rl_last_school_year_completed',
    'rl_school_attended' => 'e.rl_school_attended',
    'rl_school_id' => 'e.rl_school_id',
    'pi_mother_tongue_id' => 'COALESCE(mt.name, e.pi_mother_tongue_id)',
    'pi_religion_id' => 'COALESCE(r.name, e.pi_religion_id)',
    'ac_indigenous_group_id' => 'COALESCE(ig.name, e.ac_indigenous_group_id)',
    'li_learning_modality' => 'e.li_learning_modality',
    'verification' => 'e.verification',
    'created-at' => 'e.`created-at`',
    // enrollment_address2 table
    'ca_house_number' => 'ea.ca_house_number',
    'ca_street_name' => 'ea.ca_street_name',
    'ca_barangay' => 'ea.ca_barangay',
    'ca_municipality' => 'ea.ca_municipality',
    'ca_provice' => 'ea.ca_provice',
    'ca_country' => 'ea.ca_country',
    'ca_zipcode' => 'ea.ca_zipcode',
    'ca_address_status' => 'ea.ca_address_status',
    // enrollment_parent2 table
    'fi_last_name' => 'ep.fi_last_name',
    'fi_first_name' => 'ep.fi_first_name',
    'fi_contact_number' => 'ep.fi_contact_number',
    'mi_last_name' => 'ep.mi_last_name',
    'mi_first_name' => 'ep.mi_first_name',
    'mi_contact_number' => 'ep.mi_contact_number',
    'gi_last_name' => 'ep.gi_last_name',
    'gi_first_name' => 'ep.gi_first_name',
    'gi_middle_name' => 'ep.gi_middle_name',
    'gi_contact_number' => 'ep.gi_contact_number',
    'gi_occupation' => 'ep.gi_occupation',
    'gi_relationship_status' => 'ep.gi_relationship_status',
    'gi_communication' => 'ep.gi_communication',
    'ec_to_contact' => 'ep.ec_to_contact',
    // enrollment_medical2 table
    'mf_o_medical_conditions' => 'em.mf_o_medical_conditions',
    'mf_o_others' => 'em.mf_o_others',
    'mf_mc_conditions' => 'em.mf_mc_conditions',
    'mf_mc_others' => 'em.mf_mc_others',
    'mf_exposure_c_v' => 'em.mf_exposure_c_v',
    'mf_o_pertinent_information' => 'em.mf_o_pertinent_information',
    // enrollment_special_needs2 table
    'snep_a1_diagnosis' => 'es.snep_a1_diagnosis',
    'snep_a1_sub_shpcd' => 'es.snep_a1_sub_shpcd',
    'snep_pwd_id' => 'es.snep_pwd_id',
];

try {
    $selectCols = implode(', ', array_map(function($col) use ($columnMap) {
        return $columnMap[$col] . ' AS ' . quoteIdentifier($col);
    }, $selectedColumns));

    $whereClause = '';
    $params = [];
    
    if (!empty($selectedSection)) {
        $whereClause = ' WHERE e.section = ?';
        $params[] = $selectedSection;
    }

    $summaryCounts = [];
    $summaryGroups = [];

    if (in_array('4ps', $selectedSummaryTotals, true)) {
        $countCondition = '';
        if ($whereClause === '') {
            $countCondition = ' WHERE ';
        } else {
            $countCondition = ' AND ';
        }

        $countCondition .= "TRIM(COALESCE(NULLIF(e.ac_4ps_household_number, ''), '')) <> '' AND UPPER(TRIM(COALESCE(e.ac_4ps_household_number, ''))) NOT IN ('NO', 'N/A', 'NA')";

        $countStmt = $pdo->prepare("SELECT COUNT(*) AS total_4ps FROM enrollment2 e{$whereClause}{$countCondition}");
        $countStmt->execute($params);
        $summaryCounts['4ps'] = (int) $countStmt->fetchColumn();
    }

    if (in_array('indigenous', $selectedSummaryTotals, true)) {
        $countStmt = $pdo->prepare(
            "SELECT COALESCE(ig.name, e.ac_indigenous_group_id) AS value, COUNT(*) AS total " .
            "FROM enrollment2 e LEFT JOIN indigenous_group ig ON ig.id = e.ac_indigenous_group_id " .
            "{$whereClause}" .
            ($whereClause === '' ? ' WHERE ' : ' AND ') .
            "((TRIM(COALESCE(ig.name, '')) <> '' AND UPPER(TRIM(ig.name)) NOT IN ('NO', 'N/A', 'NA')) OR (TRIM(COALESCE(e.ac_indigenous_group_id, '')) <> '' AND UPPER(TRIM(COALESCE(e.ac_indigenous_group_id, ''))) NOT IN ('NO', 'N/A', 'NA'))) " .
            "GROUP BY value ORDER BY value"
        );
        $countStmt->execute($params);
        $summaryGroups['indigenous'] = $countStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    if (in_array('mother_tongue', $selectedSummaryTotals, true)) {
        $countStmt = $pdo->prepare(
            "SELECT COALESCE(mt.name, e.pi_mother_tongue_id) AS value, COUNT(*) AS total " .
            "FROM enrollment2 e LEFT JOIN mother_tongue mt ON mt.id = e.pi_mother_tongue_id " .
            "{$whereClause}" .
            ($whereClause === '' ? ' WHERE ' : ' AND ') .
            "((TRIM(COALESCE(mt.name, '')) <> '' AND UPPER(TRIM(mt.name)) NOT IN ('NO', 'N/A', 'NA')) OR (TRIM(COALESCE(e.pi_mother_tongue_id, '')) <> '' AND UPPER(TRIM(COALESCE(e.pi_mother_tongue_id, ''))) NOT IN ('NO', 'N/A', 'NA'))) " .
            "GROUP BY value ORDER BY value"
        );
        $countStmt->execute($params);
        $summaryGroups['mother_tongue'] = $countStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    if (in_array('religion', $selectedSummaryTotals, true)) {
        $countStmt = $pdo->prepare(
            "SELECT COALESCE(r.name, e.pi_religion_id) AS value, COUNT(*) AS total " .
            "FROM enrollment2 e LEFT JOIN religion r ON r.id = e.pi_religion_id " .
            "{$whereClause}" .
            ($whereClause === '' ? ' WHERE ' : ' AND ') .
            "((TRIM(COALESCE(r.name, '')) <> '' AND UPPER(TRIM(r.name)) NOT IN ('NO', 'N/A', 'NA')) OR (TRIM(COALESCE(e.pi_religion_id, '')) <> '' AND UPPER(TRIM(COALESCE(e.pi_religion_id, ''))) NOT IN ('NO', 'N/A', 'NA'))) " .
            "GROUP BY value ORDER BY value"
        );
        $countStmt->execute($params);
        $summaryGroups['religion'] = $countStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    $stmt = $pdo->prepare("
        SELECT {$selectCols}
        FROM enrollment2 e
        LEFT JOIN enrollment_address2 ea ON ea.fk_full_name_bd = e.fk_full_name_bd
        LEFT JOIN enrollment_parent2 ep ON ep.fk_full_name_bd = e.fk_full_name_bd
        LEFT JOIN enrollment_medical2 em ON em.fk_full_name_bd = e.fk_full_name_bd
        LEFT JOIN enrollment_special_needs2 es ON es.fk_full_name_bd = e.fk_full_name_bd
        LEFT JOIN mother_tongue mt ON mt.id = e.pi_mother_tongue_id
        LEFT JOIN religion r ON r.id = e.pi_religion_id
        LEFT JOIN indigenous_group ig ON ig.id = e.ac_indigenous_group_id
        {$whereClause}
        ORDER BY e.pi_last_name ASC, e.pi_first_name ASC
    ");
    $stmt->execute($params);
    $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($enrollments)) {
        $sectionInfo = !empty($selectedSection) ? " for section '{$selectedSection}'" : '';
        http_response_code(404);
        exit('No enrollment records found' . $sectionInfo . '.');
    }

    $filename = 'enrollment_masterlist_' . date('Y-m-d_H-i-s') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo "\xEF\xBB\xBF";

    $headers = [];
    foreach ($selectedColumns as $col) {
        $headers[] = $allColumns[$col];
    }

    $fp = fopen('php://output', 'w');

    $summaryLabels = [
        '4ps' => 'Total 4Ps Household Beneficiaries',
        'indigenous' => 'Indigenous Group',
        'mother_tongue' => 'Mother Tongue',
        'religion' => 'Religion',
    ];

    foreach ($summaryLabels as $key => $label) {
        if ($key === '4ps' && isset($summaryCounts[$key])) {
            fputcsv($fp, [$label . ': ' . $summaryCounts[$key]]);
            continue;
        }

        if (isset($summaryGroups[$key]) && !empty($summaryGroups[$key])) {
            fputcsv($fp, [$label]);
            foreach ($summaryGroups[$key] as $row) {
                $value = trim((string) ($row['value'] ?? ''));
                if ($value === '') {
                    continue;
                }
                fputcsv($fp, [$value . ': ' . (int) $row['total']]);
            }
        }
    }

    if (!empty($summaryCounts) || !empty($summaryGroups)) {
        fputcsv($fp, []);
    }

    fputcsv($fp, $headers);

    foreach ($enrollments as $row) {
        $csvRow = [];
        foreach ($selectedColumns as $col) {
            $value = $row[$col] ?? '';
            $csvRow[] = $value;
        }
        fputcsv($fp, $csvRow);
    }

    fclose($fp);
    exit;

} catch (\PDOException $e) {
    http_response_code(500);
    exit('Database error: ' . htmlspecialchars($e->getMessage()));
} catch (\Exception $e) {
    http_response_code(500);
    exit('Error: ' . htmlspecialchars($e->getMessage()));
}
?>
