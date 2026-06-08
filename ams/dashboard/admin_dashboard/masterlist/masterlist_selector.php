<?php

require_once __DIR__ . '/../../../login/SessionManager.php';
require_once __DIR__ . '/../../../config/config.php';

SessionManager::requireAuth();

if (!SessionManager::hasRole('ADMIN')) {
    http_response_code(403);
    exit('Access Denied: Admin only.');
}

$columnGroups = [
    'Personal Information' => [
        'fk_full_name_bd' => 'ID',
        'pi_last_name' => 'Last Name',
        'pi_first_name' => 'First Name',
        'pi_middle_name' => 'Middle Name',
        'pi_extension' => 'Extension',
        'pi_sex' => 'Sex',
        'pi_birth_date' => 'Date of Birth',
        'pi_place_of_birth' => 'Place of Birth',
        'pi_psa_bcn' => 'PSA/Birth Certificate #',
    ],
    'Learning Information' => [
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
    ],
    'Enrollment Status' => [
        'verification' => 'Verification Status',
        'created-at' => 'Enrollment Date',
    ],
    'Address Information' => [
        'ca_house_number' => 'Address - House #',
        'ca_street_name' => 'Address - Street',
        'ca_barangay' => 'Address - Barangay',
        'ca_municipality' => 'Address - Municipality',
        'ca_provice' => 'Address - Province',
        'ca_country' => 'Address - Country',
        'ca_zipcode' => 'Address - Zip Code',
        'ca_address_status' => 'Address Status',
    ],
    'Parent/Guardian Information' => [
        'fi_last_name' => 'Father\'s Last Name',
        'fi_first_name' => 'Father\'s First Name',
        'fi_contact_number' => 'Father\'s Contact',
        'mi_last_name' => 'Mother\'s Last Name',
        'mi_first_name' => 'Mother\'s First Name',
        'mi_contact_number' => 'Mother\'s Contact',
    ],
    'Medical Information' => [
        'mf_o_medical_conditions' => 'Medical Conditions',
        'mf_o_others' => 'Medical Others',
        'mf_mc_conditions' => 'Medical Comorbidities',
        'mf_mc_others' => 'Medical Comorbidities Others',
        'mf_exposure_c_v' => 'COVID-19 Exposure',
        'mf_o_pertinent_information' => 'Medical Pertinent Information',
    ],
    'Special Needs Information' => [
        'snep_a1_diagnosis' => 'Special Needs Diagnosis',
        'snep_a1_sub_shpcd' => 'Special Needs Sub-Category',
        'snep_pwd_id' => 'PWD ID',
    ],
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masterlist Column Selector</title>
    <link rel="stylesheet" href="../../../style/auth.css">
    <link rel="stylesheet" href="masterlist_selector.css">
</head>
<body>
    <div class="selector-container">
        <div class="selector-header">
            <h1>📋 Masterlist Column Selector</h1>
            <p>Select which columns you want to include in your enrollment masterlist export.</p>
        </div>

        <form method="POST" action="generate_masterlist.php" id="selectorForm">
            <div class="select-controls">
                <button type="button" onclick="selectAllColumns()">Select All</button>
                <button type="button" onclick="deselectAllColumns()">Deselect All</button>
            </div>

            <div class="column-groups">
                <?php foreach ($columnGroups as $groupName => $columns): ?>
                    <div class="column-group">
                        <div class="column-group-title"><?php echo htmlspecialchars($groupName); ?></div>
                        <?php foreach ($columns as $dbField => $displayName): ?>
                            <div class="column-item">
                                <input 
                                    type="checkbox" 
                                    name="columns[]" 
                                    value="<?php echo htmlspecialchars($dbField); ?>" 
                                    id="col_<?php echo htmlspecialchars($dbField); ?>"
                                    checked
                                >
                                <label for="col_<?php echo htmlspecialchars($dbField); ?>">
                                    <?php echo htmlspecialchars($displayName); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="column-count">
                <span id="selectedCount">45</span> column(s) selected
            </div>

            <div class="action-buttons">
                <button type="submit" class="btn btn-primary">📥 Download Masterlist</button>
                <button type="button" class="btn btn-secondary" onclick="goBack()">Cancel</button>
            </div>
        </form>
    </div>

    <script src="masterlist_selector.js"></script>
</body>
</html>
