<?php

require_once __DIR__ . '/../../../login/SessionManager.php';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../classes/Database.php';
require_once __DIR__ . '/../../../handlers/SubjectHandler.php';

use AMS\Database;
use AMS\Handlers\SubjectHandler;

SessionManager::requireAuth();

if (!SessionManager::hasRole('ADMIN')) {
    header('Location: ../');
    exit;
}

$db = new Database($pdo);
$handler = new SubjectHandler($db);

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $result = $handler->createSubject(
            trim($_POST['subject_code']),
            trim($_POST['subject_name']),
            !empty($_POST['offered_term']) ? (int)$_POST['offered_term'] : null
        );
        $messageType = $result['success'] ? 'success' : 'danger';
        $message = $result['message'];
    } elseif ($_POST['action'] === 'update') {
        $result = $handler->updateSubject((int)$_POST['subject_id'], [
            'subject_code' => trim($_POST['subject_code']),
            'subject_name' => trim($_POST['subject_name']),
            'offered_term' => !empty($_POST['offered_term']) ? (int)$_POST['offered_term'] : null
        ]);
        $messageType = $result['success'] ? 'success' : 'danger';
        $message = $result['message'];
    } elseif ($_POST['action'] === 'delete') {
        $result = $handler->deleteSubject((int)$_POST['subject_id']);
        $messageType = $result['success'] ? 'success' : 'danger';
        $message = $result['message'];
    }
}

$subjects = $handler->getAllSubjects();

$editSubject = null;
if (isset($_GET['edit'])) {
    $editSubject = $handler->getSubjectById((int)$_GET['edit']);
}

$termLabels = [
    1 => '1st Term (June - August)',
    2 => '2nd Term (September - November)',
    3 => '3rd Term (December - April)'
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Management - AMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../style/style.css">
</head>
<body class="bg-light">
    <header class="bg-white shadow-sm mb-4">
        <div class="container py-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <h1 class="h4 mb-0">Subject Management</h1>
                <p class="mb-0 text-muted">Manage academic subjects and offerings</p>
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
                        <h5 class="mb-0"><?php echo $editSubject ? 'Edit Subject' : 'Add New Subject'; ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="<?php echo $editSubject ? 'update' : 'create'; ?>">
                            <?php if ($editSubject): ?>
                                <input type="hidden" name="subject_id" value="<?php echo $editSubject['subject_id']; ?>">
                                <div class="alert alert-info">Editing: <?php echo htmlspecialchars($editSubject['subject_name']); ?></div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="subject_code" class="form-label">Subject Code <span class="text-danger">*</span></label>
                                <input type="text" id="subject_code" name="subject_code" class="form-control" 
                                    value="<?php echo $editSubject ? htmlspecialchars($editSubject['subject_code']) : ''; ?>" 
                                    placeholder="e.g., ENG101" required>
                            </div>

                            <div class="mb-3">
                                <label for="subject_name" class="form-label">Subject Name <span class="text-danger">*</span></label>
                                <input type="text" id="subject_name" name="subject_name" class="form-control"
                                    value="<?php echo $editSubject ? htmlspecialchars($editSubject['subject_name']) : ''; ?>"
                                    placeholder="e.g., English Language Arts" required>
                            </div>

                            <div class="mb-3">
                                <label for="offered_term" class="form-label">Offered Term</label>
                                <select id="offered_term" name="offered_term" class="form-select">
                                    <option value="">-- Year-round (All Terms) --</option>
                                    <option value="1" <?php echo $editSubject && $editSubject['offered_term'] == 1 ? 'selected' : ''; ?>>1st Term (June - August)</option>
                                    <option value="2" <?php echo $editSubject && $editSubject['offered_term'] == 2 ? 'selected' : ''; ?>>2nd Term (September - November)</option>
                                    <option value="3" <?php echo $editSubject && $editSubject['offered_term'] == 3 ? 'selected' : ''; ?>>3rd Term (December - April)</option>
                                </select>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <?php echo $editSubject ? 'Update Subject' : 'Add Subject'; ?>
                                </button>
                                <?php if ($editSubject): ?>
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
                        <h5 class="mb-0">Subjects List (<?php echo count($subjects); ?>)</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Term</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($subjects)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">No subjects yet</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($subjects as $subject): ?>
                                        <tr>
                                            <td><code><?php echo htmlspecialchars($subject['subject_code']); ?></code></td>
                                            <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo $subject['offered_term'] ? $termLabels[$subject['offered_term']] : 'Year-round'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="?edit=<?php echo $subject['subject_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this subject?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="subject_id" value="<?php echo $subject['subject_id']; ?>">
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
