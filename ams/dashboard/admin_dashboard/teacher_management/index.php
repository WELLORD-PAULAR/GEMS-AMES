<?php

require_once __DIR__ . '/../../../login/SessionManager.php';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../classes/Database.php';
require_once __DIR__ . '/../../../handlers/TeacherHandler.php';

use AMS\Database;
use AMS\Handlers\TeacherHandler;

SessionManager::requireAuth();

if (!SessionManager::hasRole('ADMIN')) {
    header('Location: ../');
    exit;
}

$db = new Database($pdo);
$handler = new TeacherHandler($db);

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $result = $handler->createTeacher(
            (int)$_POST['user_id'],
            trim($_POST['first_name']),
            trim($_POST['last_name']),
            trim($_POST['middle_name']) ?: null,
            trim($_POST['employee_no']) ?: null
        );
        $messageType = $result['success'] ? 'success' : 'danger';
        $message = $result['message'];
    } elseif ($_POST['action'] === 'update') {
        $result = $handler->updateTeacher((int)$_POST['teacher_id'], [
            'first_name' => trim($_POST['first_name']),
            'last_name' => trim($_POST['last_name']),
            'middle_name' => trim($_POST['middle_name']) ?: null,
            'employee_no' => trim($_POST['employee_no']) ?: null
        ]);
        $messageType = $result['success'] ? 'success' : 'danger';
        $message = $result['message'];
    } elseif ($_POST['action'] === 'delete') {
        $result = $handler->deleteTeacher((int)$_POST['teacher_id']);
        $messageType = $result['success'] ? 'success' : 'danger';
        $message = $result['message'];
    }
}

$teachers = $handler->getAllTeachers();

// Fetch user_account data for dropdown
$db->query("SELECT id, username FROM user_account WHERE role = 'TEACHER' ORDER BY username");
$users = $db->fetchAll();

$editTeacher = null;
if (isset($_GET['edit'])) {
    $editTeacher = $handler->getTeacherById((int)$_GET['edit']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Management - AMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../style/style.css">
    <style>
        .page-header {
            margin-bottom: 2rem;
        }
        .form-card {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
        }
        .teacher-table {
            background: white;
        }
    </style>
</head>
<body class="bg-light">
    <header class="bg-white shadow-sm mb-4">
        <div class="container py-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <h1 class="h4 mb-0">Teacher Management</h1>
                <p class="mb-0 text-muted">Manage teacher profiles and information</p>
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
                        <h5 class="mb-0"><?php echo $editTeacher ? 'Edit Teacher' : 'Add New Teacher'; ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="needs-validation">
                            <input type="hidden" name="action" value="<?php echo $editTeacher ? 'update' : 'create'; ?>">
                            <?php if ($editTeacher): ?>
                                <input type="hidden" name="teacher_id" value="<?php echo $editTeacher['teacher_id']; ?>">
                                <div class="alert alert-info">Editing: <?php echo htmlspecialchars($editTeacher['first_name'] . ' ' . $editTeacher['last_name']); ?></div>
                            <?php endif; ?>

                            <?php if (!$editTeacher): ?>
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">User Account <span class="text-danger">*</span></label>
                                    <select id="user_id" name="user_id" class="form-select" required>
                                        <option value="">-- Select User --</option>
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?php echo $user['id']; ?>">
                                                <?php echo htmlspecialchars($user['username']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" id="first_name" name="first_name" class="form-control" 
                                    value="<?php echo $editTeacher ? htmlspecialchars($editTeacher['first_name']) : ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="middle_name" class="form-label">Middle Name</label>
                                <input type="text" id="middle_name" name="middle_name" class="form-control"
                                    value="<?php echo $editTeacher ? htmlspecialchars($editTeacher['middle_name'] ?? '') : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" id="last_name" name="last_name" class="form-control"
                                    value="<?php echo $editTeacher ? htmlspecialchars($editTeacher['last_name']) : ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="employee_no" class="form-label">Employee Number</label>
                                <input type="text" id="employee_no" name="employee_no" class="form-control"
                                    value="<?php echo $editTeacher ? htmlspecialchars($editTeacher['employee_no'] ?? '') : ''; ?>">
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <?php echo $editTeacher ? 'Update Teacher' : 'Add Teacher'; ?>
                                </button>
                                <?php if ($editTeacher): ?>
                                    <a href="?action=list" class="btn btn-secondary">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Teachers List (<?php echo count($teachers); ?>)</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Employee #</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($teachers)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">No teachers yet</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></strong>
                                                <?php if ($teacher['middle_name']): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($teacher['middle_name']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($teacher['employee_no'] ?? '-'); ?></td>
                                            <td>
                                                <a href="?edit=<?php echo $teacher['teacher_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this teacher?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="teacher_id" value="<?php echo $teacher['teacher_id']; ?>">
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
