<?php

require_once __DIR__ . '/../../../login/SessionManager.php';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../handlers/SectionAssignmentHandler.php';

use AMS\Handlers\SectionAssignmentHandler;

SessionManager::requireAuth();
if (!SessionManager::hasRole('ADMIN')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $handler = new SectionAssignmentHandler($pdo);

        $action = $_GET['action'] ?? '';

        if ($action === 'get_students') {
            $gradeLevel = $_GET['grade'] ?? null;
            $status = $_GET['status'] ?? null;

            if ($status) {
                $students = $handler->getStudentsByStatus($status);
            } else {
                $students = $handler->getStudents($gradeLevel);
            }

            echo json_encode([
                'success' => true,
                'students' => $students
            ]);
        } elseif ($action === 'get_sections') {
            $gradeLevel = $_GET['grade'] ?? null;

            if ($gradeLevel) {
                $sections = $handler->getSectionsForGrade($gradeLevel);
            } else {
                $sections = $handler->getAllSections();
            }

            echo json_encode([
                'success' => true,
                'sections' => $sections
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid request data']);
            exit;
        }

        $handler = new SectionAssignmentHandler($pdo);
        $action = $data['action'] ?? '';

        if ($action === 'assign_section') {
            $studentId = $data['student_id'] ?? null;
            $section = $data['section'] ?? null;

            if (!$studentId || !$section) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Missing required fields'
                ]);
                exit;
            }

            $result = $handler->assignSection($studentId, $section);

            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Section assigned successfully' : 'Failed to assign section'
            ]);
        } elseif ($action === 'bulk_assign') {
            $assignments = $data['assignments'] ?? [];

            if (empty($assignments)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No assignments provided'
                ]);
                exit;
            }

            $results = $handler->bulkAssignSections($assignments);

            echo json_encode([
                'success' => true,
                'results' => $results,
                'message' => 'Bulk assignment completed'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
        }
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
