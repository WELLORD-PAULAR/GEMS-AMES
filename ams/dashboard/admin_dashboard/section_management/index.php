<?php

require_once __DIR__ . '/../../../login/SessionManager.php';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../classes/Database.php';
require_once __DIR__ . '/../../../handlers/SectionHandler.php';

use AMS\Database;
use AMS\Handlers\SectionHandler;

SessionManager::requireAuth();

if (!SessionManager::hasRole('ADMIN')) {
    header('Location: ../');
    exit;
}

$db = new Database($pdo);
$handler = new SectionHandler($db);

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $result = $handler->createSection(
            trim($_POST['section_name']),
            trim($_POST['grade_level']),
            trim($_POST['school_year'])
        );
        $messageType = $result['success'] ? 'success' : 'danger';
        $message = $result['message'];
    } elseif ($_POST['action'] === 'update') {
        $result = $handler->updateSection((int)$_POST['section_id'], [
            'section_name' => trim($_POST['section_name']),
            'grade_level' => trim($_POST['grade_level']),
            'school_year' => trim($_POST['school_year'])
        ]);
        $messageType = $result['success'] ? 'success' : 'danger';
        $message = $result['message'];
    } elseif ($_POST['action'] === 'delete') {
        $result = $handler->deleteSection((int)$_POST['section_id']);
        $messageType = $result['success'] ? 'success' : 'danger';
        $message = $result['message'];
    }
}

$sections = $handler->getAllSections();

$editSection = null;
if (isset($_GET['edit'])) {
    $editSection = $handler->getSectionById((int)$_GET['edit']);
}

$grades = ['KINDERGARTEN', 'GRADE 1', 'GRADE 2', 'GRADE 3', 'GRADE 4', 'GRADE 5', 'GRADE 6'];
$currentYear = (int)date('Y');
$schoolYearStart = $currentYear;
$schoolYearEnd = $currentYear + 1;

// School year options
$schoolYearOptions = [];
for ($year = $currentYear - 1; $year <= $currentYear + 2; $year++) {
    $schoolYearOptions[$year . '-' . ($year + 1)] = 'SY ' . $year . '-' . ($year + 1);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Section Management - AMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../style/style.css">
</head>
<body class="bg-light">
    <header class="bg-white shadow-sm mb-4">
        <div class="container py-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <h1 class="h4 mb-0">Section Management</h1>
                <p class="mb-0 text-muted">Manage classes and school sections</p>
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
                        <h5 class="mb-0"><?php echo $editSection ? 'Edit Section' : 'Add New Section'; ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="<?php echo $editSection ? 'update' : 'create'; ?>">
                            <?php if ($editSection): ?>
                                <input type="hidden" name="section_id" value="<?php echo $editSection['section_id']; ?>">
                                <div class="alert alert-info">Editing: <?php echo htmlspecialchars($editSection['section_name']); ?></div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="section_name" class="form-label">Section Name <span class="text-danger">*</span></label>
                                <input type="text" id="section_name" name="section_name" class="form-control" 
                                    value="<?php echo $editSection ? htmlspecialchars($editSection['section_name']) : ''; ?>" 
                                    placeholder="e.g., Grade 5-A" required>
                            </div>

                            <div class="mb-3">
                                <label for="grade_level" class="form-label">Grade Level <span class="text-danger">*</span></label>
                                <select id="grade_level" name="grade_level" class="form-select" required>
                                    <option value="">-- Select Grade --</option>
                                    <?php foreach ($grades as $grade): ?>
                                        <option value="<?php echo $grade; ?>" 
                                            <?php echo $editSection && $editSection['grade_level'] === $grade ? 'selected' : ''; ?>>
                                            <?php echo $grade; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="school_year" class="form-label">School Year <span class="text-danger">*</span></label>
                                <select id="school_year" name="school_year" class="form-select" required>
                                    <?php foreach ($schoolYearOptions as $value => $label): ?>
                                        <option value="<?php echo $value; ?>" 
                                            <?php echo $editSection && $editSection['school_year'] == $value ? 'selected' : ($value == ($currentYear . '-' . ($currentYear + 1)) ? 'selected' : ''); ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <?php echo $editSection ? 'Update Section' : 'Add Section'; ?>
                                </button>
                                <?php if ($editSection): ?>
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
                        <h5 class="mb-0">Sections List (<?php echo count($sections); ?>)</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Section Name</th>
                                    <th>Grade</th>
                                    <th>Year</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($sections)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">No sections yet</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($sections as $section): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($section['section_name']); ?></strong></td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?php echo htmlspecialchars($section['grade_level']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $section['school_year']; ?></td>
                                            <td>
                                                <a href="?edit=<?php echo $section['section_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this section?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="section_id" value="<?php echo $section['section_id']; ?>">
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
