<?php
session_start();
require_once __DIR__ . '/../../login/SessionManager.php';
if (!SessionManager::isAuthenticated()) {
    header('Location: ../../login/');
    exit;
}

$user = SessionManager::getUser();
if ($user['role'] !== 'TEACHER') {
    http_response_code(403);
    die('Access Denied: Only teachers can verify enrollments.');
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

$db = new Database($pdo);

$enrollmentModel = new Enrollment($db);
$db->query("SELECT * FROM enrollment2 WHERE verification = ?", ['PROCESSING']);
$enrollments = $db->fetchAll();
$verificationStatuses = ['VERIFIED', 'PROCESSING', 'REJECTED'];

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
    <link rel="stylesheet" href="../enrollment_form/enrollment.css">
    <link rel="stylesheet" href="verify.css">
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
                        <?php include '../enrollment_form/sections/enrollment_details.php'; ?>
                    </div>

                    <!-- Section 2: Student Personal Information -->
                    <div class="section" id="section-personal" style="display:none;">
                        <?php include '../enrollment_form/sections/personal_info.php'; ?>
                    </div>

                    <!-- Section 3: Address Information -->
                    <div class="section" id="section-address" style="display:none;">
                        <?php include '../enrollment_form/sections/address_info.php'; ?>
                    </div>

                    <!-- Section 4: Medical Information -->
                    <div class="section" id="section-medical" style="display:none;">
                        <?php include '../enrollment_form/sections/medical_info.php'; ?>
                    </div>

                    <!-- Section 5: Parent / Guardian Information -->
                    <div class="section" id="section-parents" style="display:none;">
                        <?php include '../enrollment_form/sections/parents_info.php'; ?>
                    </div>

                    <!-- Section 6: Special Needs -->
                    <div class="section" id="section-special-needs" style="display:none;">
                        <?php include '../enrollment_form/sections/special_needs_info.php'; ?>
                    </div>

                    <div class="mb-4">
                        <label for="verificationStatus" class="form-label">Verification Status</label>
                        <select id="verificationStatus" name="verification" class="form-select">
                            <?php foreach ($verificationStatuses as $status): ?>
                                <option value="<?php echo htmlspecialchars($status); ?>"><?php echo htmlspecialchars($status); ?></option>
                            <?php endforeach; ?>
                        </select>
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
    <script src="verify.js"></script>
</body>
</html>
