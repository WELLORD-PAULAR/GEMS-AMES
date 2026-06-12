<?php

namespace AMS\Handlers;

require_once __DIR__ . '/../classes/models/Teacher.php';

use AMS\Database;
use AMS\Models\Teacher;

class TeacherHandler
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function createTeacher(int $userId, string $firstName, string $lastName, ?string $middleName = null, ?string $employeeNo = null): array
    {
        try {
            $this->db->beginTransaction();

            $teacher = new Teacher($this->db);
            $teacher->fill([
                'user_id' => $userId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'middle_name' => $middleName,
                'employee_no' => $employeeNo
            ]);

            if (!$teacher->save()) {
                throw new \Exception('Failed to create teacher');
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Teacher created successfully',
                'teacher_id' => $teacher->teacher_id
            ];
        } catch (\Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function updateTeacher(int $teacherId, array $data): array
    {
        try {
            $this->db->beginTransaction();

            $teacher = new Teacher($this->db);
            if (!$teacher->find($teacherId)) {
                throw new \Exception('Teacher not found');
            }

            $allowedFields = ['first_name', 'last_name', 'middle_name', 'employee_no'];
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $teacher->{$field} = $data[$field];
                }
            }

            if (!$teacher->save()) {
                throw new \Exception('Failed to update teacher');
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Teacher updated successfully'
            ];
        } catch (\Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getTeacherById(int $teacherId): ?array
    {
        $teacher = new Teacher($this->db);
        $result = $teacher->find($teacherId);
        return $result ? $teacher->toArray() : null;
    }

    public function getTeacherByUserId(int $userId): ?array
    {
        $teacher = new Teacher($this->db);
        $result = $teacher->findByUserId($userId);
        return $result ? $teacher->toArray() : null;
    }

    public function getAllTeachers(): array
    {
        $teacher = new Teacher($this->db);
        return $teacher->getAllTeachers();
    }

    public function deleteTeacher(int $teacherId): array
    {
        try {
            $this->db->beginTransaction();

            $this->db->query("DELETE FROM teachers WHERE teacher_id = ?", [$teacherId]);

            if ($this->db->rowCount() === 0) {
                throw new \Exception('Teacher not found');
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Teacher deleted successfully'
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
