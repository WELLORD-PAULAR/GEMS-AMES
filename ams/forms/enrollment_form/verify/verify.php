<?php
session_start();

// Check if user is authenticated and is a TEACHER
require_once __DIR__ . '/../../../login/SessionManager.php';

if (!SessionManager::isAuthenticated()) {
    header('Location: ../../login/');
    exit;
}

$user = SessionManager::getUser();
if ($user['role'] !== 'TEACHER') {
    http_response_code(403);
    die('Access Denied: Only teachers can verify enrollments.');
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

$db = new Database($pdo);

// Fetch only enrollments currently in PROCESSING status for verification
$enrollmentModel = new Enrollment($db);
$db->query("SELECT * FROM enrollment2 WHERE verification = ?", ['PROCESSING']);
$enrollments = $db->fetchAll();

$success = isset($_GET['success']) && $_GET['success'] == '1';
$error = isset($_GET['error']) ? urldecode($_GET['error']) : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Enrollment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../enrollment.css">
    <style>
        .enrollment-selector-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
            margin-top: 20px;
        }

        .enrollment-selector-container h2 {
            color: #0d5fa8;
            font-size: 20px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .enrollment-dropdown {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }

        .enrollment-dropdown .flex-grow {
            flex-grow: 1;
        }

        .teacher-header {
            background: #0d5fa8;
            color: white;
            padding: 15px;
            border-radius: 8px 8px 0 0;
            margin-bottom: 20px;
            margin-top: -30px;
            margin-left: -30px;
            margin-right: -30px;
        }

        .teacher-header p {
            margin: 0;
            font-size: 14px;
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .enrollment-details {
            display: none;
            margin-top: 20px;
        }

        .enrollment-details.show {
            display: block;
        }

        .btn-save-changes {
            background-color: #28a745;
            color: white;
            font-weight: 600;
        }

        .btn-save-changes:hover {
            background-color: #218838;
            color: white;
        }

        .form-section-locked {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="enrollment-container">
            <div class="teacher-header">
                <p>TEACHER VERIFICATION PANEL</p>
                <p>Logged in as: <strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
            </div>

            <h1>Verify & Edit Student Enrollment</h1>
            <p>Select an enrollment from the list below to review and make edits if necessary.</p>

            <!-- Success/Error Message Display -->
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading">✅ Enrollment Updated Successfully!</h5>
                    <p class="mb-0">The student enrollment has been updated in the system.</p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading">❌ Update Failed</h5>
                    <p class="mb-0"><strong><?php echo htmlspecialchars($error); ?></strong></p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Enrollment Selector -->
            <div class="enrollment-selector-container">
                <h2>Select Enrollment</h2>
                <div class="enrollment-dropdown">
                    <div class="flex-grow">
                        <label for="enrollmentSelect" class="form-label">Choose a student enrollment:</label>
                        <select id="enrollmentSelect" class="form-select" onchange="loadEnrollmentData(this.value)">
                            <option value="">-- Select an enrollment --</option>
                            <?php foreach ($enrollments as $enrollment): ?>
                                <option value="<?php echo htmlspecialchars($enrollment['fk_full_name_bd']); ?>">
                                    <?php echo htmlspecialchars($enrollment['pi_first_name'] . ' ' . $enrollment['pi_last_name'] . ' (' . $enrollment['fk_full_name_bd'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Loading Spinner -->
            <div class="loading-spinner" id="loadingSpinner">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p>Loading enrollment data...</p>
            </div>

            <!-- Enrollment Details Form -->
            <div class="enrollment-details" id="enrollmentDetails">
                <form id="verifyForm" method="POST" action="./process_verify.php">
                    <input type="hidden" id="enrollmentId" name="fk_full_name_bd" value="">

                    <!-- Section 1: Enrollment Details -->
                    <div class="section" id="section-enrollment" style="display:none;">
                        <?php include '../sections/enrollment_details.php'; ?>
                    </div>

                    <!-- Section 2: Student Personal Information -->
                    <div class="section" id="section-personal" style="display:none;">
                        <?php include '../sections/personal_info.php'; ?>
                    </div>

                    <!-- Section 3: Address Information -->
                    <div class="section" id="section-address" style="display:none;">
                        <?php include '../sections/address_info.php'; ?>
                    </div>

                    <!-- Section 4: Medical Information -->
                    <div class="section" id="section-medical" style="display:none;">
                        <?php include '../sections/medical_info.php'; ?>
                    </div>

                    <!-- Section 5: Parent / Guardian Information -->
                    <div class="section" id="section-parents" style="display:none;">
                        <?php include '../sections/parents_info.php'; ?>
                    </div>

                    <!-- Section 6: Special Needs -->
                    <div class="section" id="section-special-needs" style="display:none;">
                        <?php include '../sections/special_needs_info.php'; ?>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <button type="button" class="btn btn-secondary" onclick="resetForm()">Cancel</button>
                        <button type="submit" class="btn btn-save-changes btn-lg">Save Changes</button>
                    </div>
                    <div id="status" class="status" style="display:none;"></div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../enrollment.js"></script>
    <script>
        function loadEnrollmentData(enrollmentId) {
            if (!enrollmentId) {
                document.getElementById('enrollmentDetails').classList.remove('show');
                return;
            }

            const spinner = document.getElementById('loadingSpinner');
            const details = document.getElementById('enrollmentDetails');
            
            spinner.style.display = 'block';
            details.classList.remove('show');

            fetch('./get_enrollment.php?id=' + encodeURIComponent(enrollmentId))
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        populateForm(data.data);
                        document.getElementById('enrollmentId').value = enrollmentId;
                        details.classList.add('show');
                    } else {
                        alert('Error loading enrollment: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading enrollment data');
                })
                .finally(() => {
                    spinner.style.display = 'none';
                });
        }

        function populateForm(data) {
            const sectionMap = {
                'enrollment': 'section-enrollment',
                'address': 'section-address',
                'medical': 'section-medical',
                'parents': 'section-parents',
                'specialNeeds': 'section-special-needs'
            };

            // Populate enrollment fields
            if (data.enrollment) {
                const lookupKeys = ['pi_mother_tongue_id','pi_religion_id','ac_indigenous_group_id'];
                Object.keys(data.enrollment).forEach(key => {
                    const element = document.getElementById(key);
                    if (element) {
                        const value = data.enrollment[key] || '';
                        element.value = value;

                        // If this is a lookup field, also set the visible search input
                        if (lookupKeys.includes(key)) {
                            try {
                                const searchInput = document.getElementById(key + '_search');
                                // options were stored on the hidden input by enrollment.js
                                const optsJson = element.dataset.options;
                                if (searchInput && optsJson) {
                                    const opts = JSON.parse(optsJson || '[]');
                                    const match = opts.find(o => (o.id || o.value || '') == value);
                                    if (match) {
                                        searchInput.value = match.name || match.label || '';
                                    } else {
                                        // if no match by id, attempt to match by name
                                        const byName = opts.find(o => (o.name || o.label || '').toLowerCase() === (value || '').toString().toLowerCase());
                                        if (byName) searchInput.value = byName.name || byName.label || '';
                                    }
                                }
                            } catch (err) {
                                console.warn('Failed to populate lookup display for', key, err);
                            }
                        }
                    }
                });
                // Show enrollment section if it has data
                if (Object.values(data.enrollment).some(val => val)) {
                    document.getElementById('section-enrollment').style.display = 'block';
                    // Show personal section if enrollment contains personal info (pi_ fields)
                    const hasPersonal = Object.keys(data.enrollment).some(k => k.startsWith('pi_') && data.enrollment[k]);
                    if (hasPersonal) {
                        document.getElementById('section-personal').style.display = 'block';
                    }
                }
            }

            // Populate address fields
            if (data.address && Object.keys(data.address).length > 0) {
                Object.keys(data.address).forEach(key => {
                    const element = document.getElementById(key);
                    if (element) {
                        element.value = data.address[key] || '';
                    }
                });
                // Show address section if it has data
                if (Object.values(data.address).some(val => val)) {
                    document.getElementById('section-address').style.display = 'block';
                }
            }

            // Populate medical fields
            if (data.medical && Object.keys(data.medical).length > 0) {
                Object.keys(data.medical).forEach(key => {
                    const element = document.getElementById(key);
                    if (element) {
                        element.value = data.medical[key] || '';
                    }
                });
                // Show medical section if it has data
                if (Object.values(data.medical).some(val => val)) {
                    document.getElementById('section-medical').style.display = 'block';
                }
            }

            // Populate parent fields
            if (data.parents && Object.keys(data.parents).length > 0) {
                Object.keys(data.parents).forEach(key => {
                    const element = document.getElementById(key);
                    if (element) {
                        element.value = data.parents[key] || '';
                    }
                });
                // Show parents section if it has data
                if (Object.values(data.parents).some(val => val)) {
                    document.getElementById('section-parents').style.display = 'block';
                }
            }

            // Populate special needs fields
            if (data.specialNeeds && Object.keys(data.specialNeeds).length > 0) {
                const keyMap = {
                    'pwd': 'snep_pwd_id',
                    'pwd_id': 'snep_pwd_id',
                    'snep_pwd': 'snep_pwd_id'
                };

                Object.keys(data.specialNeeds).forEach(key => {
                    const mappedKey = keyMap[key] || key;
                    const element = document.getElementById(mappedKey);
                    if (element) {
                        const val = data.specialNeeds[key] ?? '';
                        element.value = val;
                        // If it's a select, ensure change listeners run
                        if (element.tagName === 'SELECT') {
                            const evt = new Event('change', { bubbles: true });
                            element.dispatchEvent(evt);
                        }
                    } else {
                        // try to find by partial match (robust fallback)
                        const candidate = document.querySelector(`[id$="${key}"]`);
                        if (candidate) {
                            candidate.value = data.specialNeeds[key] ?? '';
                        }
                    }
                });
                // Show special needs section if it has data
                if (Object.values(data.specialNeeds).some(val => val)) {
                    document.getElementById('section-special-needs').style.display = 'block';
                }
            }
        }

        function resetForm() {
            document.getElementById('verifyForm').reset();
            document.getElementById('enrollmentSelect').value = '';
            document.getElementById('enrollmentDetails').classList.remove('show');
            // hide all sections
            const sections = ['section-enrollment','section-personal','section-address','section-medical','section-parents','section-special-needs'];
            sections.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.display = 'none';
            });
        }

        // Handle form submission
        document.getElementById('verifyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const statusDiv = document.getElementById('status');
            
            fetch('./process_verify.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = './verify.php?success=1';
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving enrollment');
            });
        });
    </script>
</body>
</html>
