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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../style/auth.css">
    <link rel="stylesheet" href="masterlist_selector.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h2 class="mb-0">📋 Masterlist Column Selector</h2>
            </div>
            <div class="card-body">
                <p class="text-muted">Select which columns you want to include in your enrollment masterlist export.</p>

                <!-- Calendar Info Banner -->
                <div style="background: #f0f4ff; border-left: 4px solid #667eea; padding: 12px; margin-bottom: 20px; border-radius: 4px; font-size: 13px;">
                    <strong>📅 DepEd Three-Term Calendar SY 2026-2027</strong><br>
                    <small>Term 1: June-Aug | Term 2: Sept-Nov | Term 3: Dec-Apr</small>
                </div>

                <form method="POST" action="generate_masterlist.php" id="selectorForm">
                    <!-- Section Filter -->
                    <div class="mb-4">
                        <label for="classSection" class="form-label fw-semibold">Filter by Section:</label>
                        <select name="section" id="classSection" class="form-select">
                            <option value="">All Sections</option>
                            <option value="K-Obedience">K-Obedience</option>
                            <option value="K-Kindness">K-Kindness</option>
                            <option value="K-Joy">K-Joy</option>
                            <option value="1-Care">1-Care</option>
                            <option value="1-Love">1-Love</option>
                            <option value="1-Hope">1-Hope</option>
                            <option value="2-Integrity">2-Integrity</option>
                            <option value="2-Patience">2-Patience</option>
                            <option value="2-Unity">2-Unity</option>
                            <option value="3-Peace">3-Peace</option>
                            <option value="3-Faith">3-Faith</option>
                            <option value="4-Charity">4-Charity</option>
                            <option value="4-Generosity">4-Generosity</option>
                            <option value="5-Loyalty">5-Loyalty</option>
                            <option value="5-Honesty">5-Honesty</option>
                            <option value="6-Honesty">6-Honesty</option>
                            <option value="6-Purity">6-Purity</option>
                        </select>
                    </div>

                    <hr class="my-4">

                    <!-- Column Selection Controls -->
                    <div class="mb-3 d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAllColumns()">Select All</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllColumns()">Deselect All</button>
                    </div>

                    <!-- Column Groups Grid -->
                    <div class="row g-3 mb-4">
                        <?php foreach ($columnGroups as $groupName => $columns): ?>
                            <div class="col-lg-6">
                                <div class="border rounded p-3 bg-white h-100">
                                    <h6 class="fw-semibold text-primary border-bottom pb-2 mb-3"><?php echo htmlspecialchars($groupName); ?></h6>
                                    <?php foreach ($columns as $dbField => $displayName): ?>
                                        <div class="form-check mb-2">
                                            <input 
                                                class="form-check-input" 
                                                type="checkbox" 
                                                name="columns[]" 
                                                value="<?php echo htmlspecialchars($dbField); ?>" 
                                                id="col_<?php echo htmlspecialchars($dbField); ?>"
                                                checked
                                            >
                                            <label class="form-check-label" for="col_<?php echo htmlspecialchars($dbField); ?>">
                                                <?php echo htmlspecialchars($displayName); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Selected Count Badge -->
                    <div class="alert alert-info d-inline-block mb-4" role="alert">
                        <strong><span id="selectedCount">45</span> column(s) selected</strong>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-secondary" onclick="goBack()">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-download"></i> 📥 Download Masterlist
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="masterlist_selector.js"></script>
</body>
</html>
