<?php

namespace AMS\Handlers;

require_once __DIR__ . '/../classes/models/Subject.php';

use AMS\Database;
use AMS\Models\Subject;

class SubjectHandler
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function createSubject(string $code, string $name, ?int $offeredTerm = null): array
    {
        try {
            $this->db->beginTransaction();

            $subject = new Subject($this->db);
            $subject->fill([
                'subject_code' => $code,
                'subject_name' => $name,
                'offered_term' => $offeredTerm
            ]);

            if (!$subject->save()) {
                throw new \Exception('Failed to create subject');
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Subject created successfully',
                'subject_id' => $subject->subject_id
            ];
        } catch (\Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function updateSubject(int $subjectId, array $data): array
    {
        try {
            $this->db->beginTransaction();

            $subject = new Subject($this->db);
            if (!$subject->find($subjectId)) {
                throw new \Exception('Subject not found');
            }

            $allowedFields = ['subject_code', 'subject_name', 'offered_term'];
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $subject->{$field} = $data[$field];
                }
            }

            if (!$subject->save()) {
                throw new \Exception('Failed to update subject');
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Subject updated successfully'
            ];
        } catch (\Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getSubjectById(int $subjectId): ?array
    {
        $subject = new Subject($this->db);
        $result = $subject->find($subjectId);
        return $result ? $subject->toArray() : null;
    }

    public function getSubjectByCode(string $code): ?array
    {
        $subject = new Subject($this->db);
        $result = $subject->findByCode($code);
        return $result ? $subject->toArray() : null;
    }

    public function getAllSubjects(): array
    {
        $subject = new Subject($this->db);
        return $subject->getAllSubjects();
    }

    public function getSubjectsByTerm(int $term): array
    {
        $subject = new Subject($this->db);
        return $subject->getSubjectsByTerm($term);
    }

    public function deleteSubject(int $subjectId): array
    {
        try {
            $this->db->beginTransaction();

            $this->db->query("DELETE FROM subjects WHERE subject_id = ?", [$subjectId]);

            if ($this->db->rowCount() === 0) {
                throw new \Exception('Subject not found');
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Subject deleted successfully'
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
