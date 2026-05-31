<?php

require_once __DIR__ . '/../../login/SessionManager.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../classes/Database.php';

use AMS\Database;

SessionManager::requireAuth();

$user = SessionManager::getUser();

if (!SessionManager::hasRole('TEACHER')) {
    header('Location: ../admin_dashboard/');
    exit;
}

$db = new Database($pdo);
$db->query("SELECT fk_full_name_bd, pi_first_name, pi_last_name, ed_grade_level, verification FROM enrollment2");
$students = $db->fetchAll();
$studentGroups = [
    'VERIFIED' => [],
    'PENDING' => [],
    'REJECTED' => []
];

foreach ($students as $student) {
    $status = strtoupper(trim($student['verification'] ?? ''));
    if ($status === 'VERIFIED') {
        $group = 'VERIFIED';
    } elseif ($status === 'REJECTED') {
        $group = 'REJECTED';
    } else {
        $group = 'PENDING';
    }
    $studentGroups[$group][] = $student;
}

$gradeSort = function ($a, $b) {
    $aVal = $a['ed_grade_level'] ?? '';
    $bVal = $b['ed_grade_level'] ?? '';

    preg_match('/\d+/', $aVal, $aMatches);
    preg_match('/\d+/', $bVal, $bMatches);

    $aNum = isset($aMatches[0]) ? intval($aMatches[0]) : PHP_INT_MAX;
    $bNum = isset($bMatches[0]) ? intval($bMatches[0]) : PHP_INT_MAX;

    if ($aNum === $bNum) {
        return strcasecmp($aVal, $bVal);
    }
    return $aNum <=> $bNum;
};

foreach ($studentGroups as &$group) {
    usort($group, $gradeSort);
}
unset($group);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - AMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <header class="bg-white shadow-sm mb-4">
        <div class="container py-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <h1 class="h4 mb-0">Teacher Dashboard</h1>
                <p class="mb-0 text-muted">Student enrollment overview</p>
            </div>
            <div class="text-end">
                <p class="mb-1">Logged in as: <strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
                <a href="../../login/logout.php" class="btn btn-outline-secondary btn-sm">Logout</a>
            </div>
        </div>
    </header>

    <main class="container mb-5">
        <div class="row g-4">
            <div class="col-12 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h5">Teacher Tools</h2>
                        <p class="text-muted">Quick access to enrollment tasks.</p>
                        <div class="d-grid gap-2">
                            <a href="../../forms/enrollment_form" class="btn btn-primary btn-sm">Go to Enrollment Form</a>
                            <a href="../../forms/verify" class="btn btn-secondary btn-sm">Go to Verify Enrollments</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h5">Student Enrollment Management</h2>
                        <p class="text-muted mb-3">Student enrollments are grouped by verification status. Sort each list by grade level.</p>
                        <div class="mb-3">
                            <label for="sortOrder" class="form-label">Sort by grade</label>
                            <select id="sortOrder" class="form-select form-select-sm w-auto">
                                <option value="asc">Grade ascending</option>
                                <option value="desc">Grade descending</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php foreach (['VERIFIED' => 'Verified', 'PENDING' => 'Pending', 'REJECTED' => 'Rejected'] as $statusKey => $statusLabel): ?>
            <section class="mt-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h3 class="h6 mb-0"><?php echo $statusLabel; ?> Students</h3>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($studentGroups[$statusKey])): ?>
                            <div class="p-3 text-muted">No students in this group.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">Student</th>
                                            <th scope="col">Grade Level</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($studentGroups[$statusKey] as $student): ?>
                                            <tr data-grade="<?php echo htmlspecialchars($student['ed_grade_level'] ?? ''); ?>">
                                                <td><?php echo htmlspecialchars(trim(($student['pi_first_name'] ?? '') . ' ' . ($student['pi_last_name'] ?? ''))); ?></td>
                                                <td><?php echo htmlspecialchars($student['ed_grade_level'] ?? 'Unknown'); ?></td>
                                                <td><?php echo htmlspecialchars($statusLabel); ?></td>
                                                <td>
                                                    <a href="../../forms/verify/index.php?selected=<?php echo urlencode($student['fk_full_name_bd']); ?>&status=<?php echo urlencode($statusKey); ?>" class="btn btn-sm btn-outline-primary me-2">Edit</a>
                                                    <a href="./download_enrollment_pdf.php?id=<?php echo urlencode($student['fk_full_name_bd']); ?>" class="btn btn-sm btn-outline-secondary">Download PDF</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        <?php endforeach; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function getGradeSortValue(text) {
            const match = text.match(/\d+/);
            if (!match) {
                if (/K|KD|Kindergarten/i.test(text)) return 0;
                return Number.MAX_SAFE_INTEGER;
            }
            return parseInt(match[0], 10);
        }

        function sortStudentTables(direction) {
            document.querySelectorAll('.table tbody').forEach(tbody => {
                const rows = Array.from(tbody.querySelectorAll('tr'));
                rows.sort((a, b) => {
                    const aGrade = getGradeSortValue(a.dataset.grade || '');
                    const bGrade = getGradeSortValue(b.dataset.grade || '');
                    if (aGrade === bGrade) {
                        return a.textContent.trim().localeCompare(b.textContent.trim());
                    }
                    return direction === 'asc' ? aGrade - bGrade : bGrade - aGrade;
                });
                rows.forEach(row => tbody.appendChild(row));
            });
        }

        document.getElementById('sortOrder').addEventListener('change', function () {
            sortStudentTables(this.value);
        });
    </script>
</body>
</html>
