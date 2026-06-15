<?php

namespace AMS\Handlers;

require_once __DIR__ . '/../classes/models/TeacherAssignment.php';

use AMS\Database;
use AMS\Models\TeacherAssignment;

class TeacherAssignmentHandler
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function assignTeacher(int $teacherId, int $subjectId, int $sectionId, string $schoolYear, int $term): array
    {
        try {
            $this->db->beginTransaction();

            // Validate term
            if ($term < 1 || $term > 3) {
                throw new \Exception('Invalid term (must be 1-3)');
            }

            // Validate school year format (YYYY-YYYY)
            if (!preg_match('/^\d{4}-\d{4}$/', $schoolYear)) {
                throw new \Exception('Invalid school year format. Use YYYY-YYYY');
            }

            // Check if teacher exists
            $this->db->query("SELECT teacher_id FROM teachers WHERE teacher_id = ?", [$teacherId]);
            if (!$this->db->fetch()) {
                throw new \Exception('Teacher not found');
            }

            // Check if subject exists
            $this->db->query("SELECT subject_id FROM subjects WHERE subject_id = ?", [$subjectId]);
            if (!$this->db->fetch()) {
                throw new \Exception('Subject not found');
            }

            // Check if section exists
            $this->db->query("SELECT section_id FROM sections WHERE section_id = ?", [$sectionId]);
            if (!$this->db->fetch()) {
                throw new \Exception('Section not found');
            }

            // Check for duplicate assignment
            $assignment = new TeacherAssignment($this->db);
            if ($assignment->exists($teacherId, $subjectId, $sectionId, $schoolYear, $term)) {
                throw new \Exception('This assignment already exists');
            }

            $assignment->fill([
                'teacher_id' => $teacherId,
                'subject_id' => $subjectId,
                'section_id' => $sectionId,
                'school_year' => $schoolYear,
                'term' => $term
            ]);

            if (!$assignment->save()) {
                throw new \Exception('Failed to create assignment');
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Teacher assigned successfully',
                'assignment_id' => $assignment->assignment_id
            ];
        } catch (\Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function updateAssignment(int $assignmentId, array $data): array
    {
        try {
            $this->db->beginTransaction();

            $assignment = new TeacherAssignment($this->db);
            if (!$assignment->find($assignmentId)) {
                throw new \Exception('Assignment not found');
            }

            if (isset($data['term'])) {
                if ($data['term'] < 1 || $data['term'] > 3) {
                    throw new \Exception('Invalid term (must be 1-3)');
                }
            }

            $allowedFields = ['teacher_id', 'subject_id', 'section_id', 'school_year', 'term'];
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $assignment->{$field} = $data[$field];
                }
            }

            if (!$assignment->save()) {
                throw new \Exception('Failed to update assignment');
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Assignment updated successfully'
            ];
        } catch (\Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getAssignmentById(int $assignmentId): ?array
    {
        $assignment = new TeacherAssignment($this->db);
        $result = $assignment->find($assignmentId);
        return $result ? $assignment->toArray() : null;
    }

    public function getAssignmentsByTeacher(int $teacherId): array
    {
        $assignment = new TeacherAssignment($this->db);
        return $assignment->getAssignmentsByTeacher($teacherId);
    }

    public function getAssignmentsBySection(int $sectionId): array
    {
        $assignment = new TeacherAssignment($this->db);
        return $assignment->getAssignmentsBySection($sectionId);
    }

    public function getAssignmentsBySubject(int $subjectId): array
    {
        $assignment = new TeacherAssignment($this->db);
        return $assignment->getAssignmentsBySubject($subjectId);
    }

    public function getAssignmentsByYear(int $schoolYear): array
    {
        $assignment = new TeacherAssignment($this->db);
        return $assignment->getAssignmentsByYear($schoolYear);
    }

    public function getAssignmentsByYearAndTerm(int $schoolYear, int $term): array
    {
        $assignment = new TeacherAssignment($this->db);
        return $assignment->getAssignmentsByYearAndTerm($schoolYear, $term);
    }

    public function deleteAssignment(int $assignmentId): array
    {
        try {
            $this->db->beginTransaction();

                $this->db->query("DELETE FROM teacher_class_roster WHERE assignment_id = ?", [$assignmentId]);
            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Assignment deleted successfully'
            ];
        } catch (\Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function deleteTeacherAssignments(int $teacherId): array
    {
        try {
            $this->db->beginTransaction();

                $this->db->query("DELETE FROM teacher_class_roster WHERE teacher_id = ?", [$teacherId]);
            return [
                'success' => true,
                'message' => "Deleted $deletedCount assignments",
                'deleted_count' => $deletedCount
            ];
        } catch (\Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function deleteSectionAssignments(int $sectionId): array
    {
        try {
            $this->db->beginTransaction();

                $this->db->query("DELETE FROM teacher_class_roster WHERE section_id = ?", [$sectionId]);
            return [
                'success' => true,
                'message' => "Deleted $deletedCount assignments",
                'deleted_count' => $deletedCount
            ];
        } catch (\Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
