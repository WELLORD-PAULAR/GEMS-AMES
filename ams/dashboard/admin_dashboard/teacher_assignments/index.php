<?php

require_once __DIR__ . '/../../../login/SessionManager.php';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../classes/Database.php';
require_once __DIR__ . '/../../../handlers/TeacherAssignmentHandler.php';
require_once __DIR__ . '/../../../handlers/TeacherHandler.php';
require_once __DIR__ . '/../../../handlers/SubjectHandler.php';
require_once __DIR__ . '/../../../handlers/SectionHandler.php';

use AMS\Database;
use AMS\Handlers\TeacherAssignmentHandler;
use AMS\Handlers\TeacherHandler;
use AMS\Handlers\SubjectHandler;
use AMS\Handlers\SectionHandler;

SessionManager::requireAuth();

if (!SessionManager::hasRole('ADMIN')) {
    header('Location: ../');
    exit;
}

$db = new Database($pdo);
$assignmentHandler = new TeacherAssignmentHandler($db);
$teacherHandler = new TeacherHandler($db);
$subjectHandler = new SubjectHandler($db);
$sectionHandler = new SectionHandler($db);

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $result = $assignmentHandler->assignTeacher(
            (int)$_POST['teacher_id'],
            (int)$_POST['subject_id'],
            (int)$_POST['section_id'],
            trim($_POST['school_year']),
            (int)$_POST['term']
        );
        $messageType = $result['success'] ? 'success' : 'danger';
        $message = $result['message'];
    } elseif ($_POST['action'] === 'update') {
        $result = $assignmentHandler->updateAssignment((int)$_POST['assignment_id'], [
            'teacher_id' => (int)$_POST['teacher_id'],
            'subject_id' => (int)$_POST['subject_id'],
            'section_id' => (int)$_POST['section_id'],
            'school_year' => trim($_POST['school_year']),
            'term' => (int)$_POST['term']
        ]);
        $messageType = $result['success'] ? 'success' : 'danger';
        $message = $result['message'];
    } elseif ($_POST['action'] === 'delete') {
        $result = $assignmentHandler->deleteAssignment((int)$_POST['assignment_id']);
        $messageType = $result['success'] ? 'success' : 'danger';
        $message = $result['message'];
    }
}

$assignments = $assignmentHandler->getAssignmentsByYear((int)date('Y'));
$teachers = $teacherHandler->getAllTeachers();
$subjects = $subjectHandler->getAllSubjects();
$sections = $sectionHandler->getAllSections();

$editAssignment = null;
if (isset($_GET['edit'])) {
    $editAssignment = $assignmentHandler->getAssignmentById((int)$_GET['edit']);
}

$termLabels = [
    1 => '1st Term (June - August)',
    2 => '2nd Term (September - November)',
    3 => '3rd Term (December - April)'
];

$currentYear = (int)date('Y');
$schoolYearStart = $currentYear;
$schoolYearEnd = $currentYear + 1;
$currentSchoolYear = "$schoolYearStart-$schoolYearEnd";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Assignments - AMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../style/style.css">
</head>
<body class="bg-light">
    <header class="bg-white shadow-sm mb-4">
        <div class="container py-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <h1 class="h4 mb-0">Teacher Assignments</h1>
                <p class="mb-0 text-muted">Manage teacher subject and section assignments</p>
            </div>
            <div class="text-end">
                <a href="../" class="btn btn-outline-secondary btn-sm me-2">Back to Dashboard</a>
                <a href="../../../login/logout.php" class="btn btn-outline-secondary btn-sm">Logout</a>
            </div>
        </div>
    </header>

    <main class="container mb-5">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><?php echo $editAssignment ? 'Edit Assignment' : 'Create Assignment'; ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="<?php echo $editAssignment ? 'update' : 'create'; ?>">
                            <?php if ($editAssignment): ?>
                                <input type="hidden" name="assignment_id" value="<?php echo $editAssignment['assignment_id']; ?>">
                                <div class="alert alert-info">Editing assignment</div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="teacher_id" class="form-label">Teacher <span class="text-danger">*</span></label>
                                <select id="teacher_id" name="teacher_id" class="form-select" required>
                                    <option value="">-- Select Teacher --</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <option value="<?php echo $teacher['teacher_id']; ?>"
                                            <?php echo $editAssignment && $editAssignment['teacher_id'] == $teacher['teacher_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="subject_id" class="form-label">Subject <span class="text-danger">*</span></label>
                                <select id="subject_id" name="subject_id" class="form-select" required>
                                    <option value="">-- Select Subject --</option>
                                    <?php foreach ($subjects as $subject): ?>
                                        <option value="<?php echo $subject['subject_id']; ?>"
                                            <?php echo $editAssignment && $editAssignment['subject_id'] == $subject['subject_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($subject['subject_name'] . ' (' . $subject['subject_code'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="section_id" class="form-label">Section <span class="text-danger">*</span></label>
                                <select id="section_id" name="section_id" class="form-select" required>
                                    <option value="">-- Select Section --</option>
                                    <?php foreach ($sections as $section): ?>
                                        <option value="<?php echo $section['section_id']; ?>"
                                            <?php echo $editAssignment && $editAssignment['section_id'] == $section['section_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($section['section_name'] . ' - ' . $section['grade_level'] . ' (' . $section['school_year'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="school_year" class="form-label">School Year <span class="text-danger">*</span></label>
                                <select id="school_year" name="school_year" class="form-select" required>
                                    <?php for ($year = $currentYear - 1; $year <= $currentYear + 2; $year++): ?>
                                        <?php $yearLabel = $year . '-' . ($year + 1); ?>
                                        <option value="<?php echo $yearLabel; ?>"
                                            <?php echo $editAssignment && $editAssignment['school_year'] == $yearLabel ? 'selected' : ($year == $currentYear ? 'selected' : ''); ?>>
                                            SY <?php echo $yearLabel; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="term" class="form-label">Term <span class="text-danger">*</span></label>
                                <select id="term" name="term" class="form-select" required>
                                    <option value="">-- Select Term --</option>
                                    <option value="1" <?php echo $editAssignment && $editAssignment['term'] == 1 ? 'selected' : ''; ?>>1st Term (June - August)</option>
                                    <option value="2" <?php echo $editAssignment && $editAssignment['term'] == 2 ? 'selected' : ''; ?>>2nd Term (September - November)</option>
                                    <option value="3" <?php echo $editAssignment && $editAssignment['term'] == 3 ? 'selected' : ''; ?>>3rd Term (December - April)</option>
                                </select>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <?php echo $editAssignment ? 'Update Assignment' : 'Create Assignment'; ?>
                                </button>
                                <?php if ($editAssignment): ?>
                                    <a href="?" class="btn btn-secondary">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Assignments (<?php echo count($assignments); ?>)</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>Teacher</th>
                                    <th>Subject</th>
                                    <th>Section</th>
                                    <th>Term</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($assignments)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">No assignments</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($assignments as $assignment): ?>
                                        <tr>
                                            <td>
                                                <small>
                                                    <?php echo htmlspecialchars($assignment['first_name'] . ' ' . $assignment['last_name']); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars($assignment['subject_name']); ?></small>
                                            </td>
                                            <td>
                                                <small>
                                                    <?php echo htmlspecialchars($assignment['section_name']); ?>
                                                    <br>
                                                    <span class="text-muted"><?php echo htmlspecialchars($assignment['grade_level']); ?></span>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo $termLabels[$assignment['term']]; ?></span>
                                            </td>
                                            <td>
                                                <a href="?edit=<?php echo $assignment['assignment_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this assignment?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="assignment_id" value="<?php echo $assignment['assignment_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
