<?php
session_start();
require_once __DIR__ . '/../../login/SessionManager.php';
if (!SessionManager::isAuthenticated()) {
    header('Location: ../../login/');
    exit;
}

$user = SessionManager::getUser();
if ($user['role'] !== 'TEACHER' && $user['role'] !== 'ADMIN') {
    http_response_code(403);
    die('Access Denied: Only teachers and admins can verify enrollments.');
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

$db = new Database($pdo);

$db->query("
    SELECT fk_full_name_bd, pi_first_name, pi_last_name, pi_middle_name,
           pi_sex, pi_birth_date, ed_grade_level, ed_school_year,
           ed_lrn, verification
    FROM enrollment2
    ORDER BY pi_last_name, pi_first_name
");
$allEnrollments = $db->fetchAll();

$gradeLevels  = array_unique(array_column($allEnrollments, 'ed_grade_level'));
$schoolYears  = array_unique(array_column($allEnrollments, 'ed_school_year'));

usort($gradeLevels, function($a, $b) {
    $aNum = preg_match('/\d+/', $a, $m) ? (int)$m[0] : 0;
    $bNum = preg_match('/\d+/', $b, $m) ? (int)$m[0] : 0;
    return $aNum <=> $bNum;
});
rsort($schoolYears);

$counts = ['PENDING' => 0, 'PROCESSING' => 0, 'VERIFIED' => 0, 'REJECTED' => 0, 'WITHDRAWN' => 0, 'TRANSFERRED_IN' => 0, 'TRANSFERRED_OUT' => 0, 'DROPPED' => 0];
foreach ($allEnrollments as $e) {
    $s = strtoupper(trim($e['verification'] ?? ''));
    if (array_key_exists($s, $counts)) {
        $counts[$s]++;
    } else {
        $counts['PENDING']++;
    }
}

$verificationStatuses = ['VERIFIED', 'PROCESSING', 'REJECTED', 'WITHDRAWN', 'TRANSFERRED_IN', 'TRANSFERRED_OUT', 'DROPPED'];

$success = isset($_GET['success']) && $_GET['success'] == '1';
$error   = isset($_GET['error']) ? urldecode($_GET['error']) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify & Edit Enrollment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../enrollment_form/enrollment.css">
    <link rel="stylesheet" href="verify.css">
</head>
<body>
<div class="container">
<div class="enrollment-container">

    <!-- Header -->
    <div class="teacher-header">
        <p>TEACHER VERIFICATION PANEL</p>
        <p>Logged in as: <strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
    </div>

    <h1>Verify &amp; Edit Student Enrollment</h1>
    <p class="text-muted mb-4">Search for a student, apply filters, then click a row to load their enrollment.</p>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>✅ Enrollment Updated Successfully!</strong> The record has been saved.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>❌ Update Failed:</strong> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- ── Search & Filter Panel ──────────────────────────────── -->
    <div class="search-panel">
        <div class="panel-title">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.099zm-5.242 1.156a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11"/>
            </svg>
            Search &amp; Filter Enrollments
        </div>

        <!-- Status tabs -->
        <div class="status-tabs">
            <button class="status-tab active-all" data-status="">
                All
                <span class="badge bg-secondary"><?php echo count($allEnrollments); ?></span>
            </button>
            <button class="status-tab" data-status="PENDING">
                Pending
                <span class="badge bg-warning text-dark"><?php echo $counts['PENDING']; ?></span>
            </button>
            <button class="status-tab" data-status="VERIFIED">
                Verified
                <span class="badge bg-success"><?php echo $counts['VERIFIED']; ?></span>
            </button>
            <button class="status-tab" data-status="REJECTED">
                Rejected
                <span class="badge bg-danger"><?php echo $counts['REJECTED']; ?></span>
            </button>
            <button class="status-tab" data-status="PROCESSING">
                Processing
                <span class="badge bg-info text-dark"><?php echo $counts['PROCESSING']; ?></span>
            </button>
            <button class="status-tab" data-status="WITHDRAWN">
                Withdrawn
                <span class="badge bg-secondary"><?php echo $counts['WITHDRAWN']; ?></span>
            </button>
            <button class="status-tab" data-status="TRANSFERRED_IN">
                Transferred In
                <span class="badge bg-secondary"><?php echo $counts['TRANSFERRED_IN']; ?></span>
            </button>
            <button class="status-tab" data-status="TRANSFERRED_OUT">
                Transferred Out
                <span class="badge bg-secondary"><?php echo $counts['TRANSFERRED_OUT']; ?></span>
            </button>
            <button class="status-tab" data-status="DROPPED">
                Dropped
                <span class="badge bg-secondary"><?php echo $counts['DROPPED']; ?></span>
            </button>
        </div>

        <!-- Search + dropdowns -->
        <div class="search-row">
            <div class="search-main">
                <label class="form-label fw-semibold" for="searchInput">Search by name or LRN</label>
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <svg width="14" height="14" fill="#888" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.099zm-5.242 1.156a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11"/>
                        </svg>
                    </span>
                    <input type="text" id="searchInput" class="form-control"
                           placeholder="e.g. Juan dela Cruz or LRN…" autocomplete="off">
                    <button class="btn btn-outline-secondary" id="clearSearch" title="Clear search">✕</button>
                </div>
            </div>

            <div class="filter-item">
                <label class="form-label fw-semibold" for="filterGrade">Grade Level</label>
                <select id="filterGrade" class="form-select">
                    <option value="">All Grades</option>
                    <?php foreach ($gradeLevels as $g): ?>
                        <option value="<?php echo htmlspecialchars($g); ?>">
                            Grade <?php echo htmlspecialchars($g); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-item">
                <label class="form-label fw-semibold" for="filterSex">Sex</label>
                <select id="filterSex" class="form-select">
                    <option value="">All</option>
                    <option value="MALE">Male</option>
                    <option value="FEMALE">Female</option>
                </select>
            </div>

            <div class="filter-item wide">
                <label class="form-label fw-semibold" for="filterSY">School Year</label>
                <select id="filterSY" class="form-select">
                    <option value="">All Years</option>
                    <?php foreach ($schoolYears as $sy): ?>
                        <option value="<?php echo htmlspecialchars($sy); ?>"><?php echo htmlspecialchars($sy); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-item">
                <button class="btn btn-outline-secondary w-100" id="resetFilters">
                    Reset Filters
                </button>
            </div>
        </div>
    </div>

    <!-- ── Results Table ──────────────────────────────────────── -->
    <div class="results-panel">
        <div class="results-header">
            <span class="title">Enrollment Results</span>
            <span class="results-count" id="resultsCount">Loading…</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover" id="resultsTable">
                <thead>
                    <tr>
                        <th data-sort="name">Full Name <span class="sort-icon"></span></th>
                        <th data-sort="grade">Grade <span class="sort-icon"></span></th>
                        <th data-sort="sy">School Year <span class="sort-icon"></span></th>
                        <th data-sort="sex">Sex <span class="sort-icon"></span></th>
                        <th data-sort="status">Status <span class="sort-icon"></span></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody"></tbody>
            </table>
            <div id="noResults" class="no-results" style="display:none;">
                <svg width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.099zm-5.242 1.156a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11"/>
                </svg>
                <p>No enrollments match your search.</p>
            </div>
        </div>
    </div>

    <!-- ── Enrollment Detail Form (loaded on row click) ──────── -->
    <div id="enrollmentDetailsWrapper">

        <!-- Loading Spinner -->
        <div class="loading-spinner" id="loadingSpinner">
            <div class="spinner-border" role="status"><span class="visually-hidden">Loading…</span></div>
            <p>Loading enrollment data…</p>
        </div>

        <!-- Form -->
        <div class="enrollment-details" id="enrollmentDetails">
            <form id="verifyForm" method="POST" action="./process_verify.php">
                <input type="hidden" id="enrollmentId" name="fk_full_name_bd" value="">

                <div class="section" id="section-enrollment" style="display:none;">
                    <?php include '../enrollment_form/sections/enrollment_details.php'; ?>
                </div>
                <div class="section" id="section-personal" style="display:none;">
                    <?php include '../enrollment_form/sections/personal_info.php'; ?>
                </div>
                <div class="section" id="section-address" style="display:none;">
                    <?php include '../enrollment_form/sections/address_info.php'; ?>
                </div>
                <div class="section" id="section-medical" style="display:none;">
                    <?php include '../enrollment_form/sections/medical_info.php'; ?>
                </div>
                <div class="section" id="section-parents" style="display:none;">
                    <?php include '../enrollment_form/sections/parents_info.php'; ?>
                </div>
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
</div>

<script>
const ALL_ENROLLMENTS = <?php echo json_encode(array_values($allEnrollments), JSON_HEX_TAG); ?>;
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="verify.js"></script>
</body>
</html>