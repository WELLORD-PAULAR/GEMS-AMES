<?php

namespace AMS\Handlers;

require_once __DIR__ . '/../classes/models/Section.php';

use AMS\Database;
use AMS\Models\Section;

class SectionHandler
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function createSection(string $name, string $gradeLevel, string $schoolYear): array
    {
        try {
            $this->db->beginTransaction();

            $validGrades = ['KINDERGARTEN', 'GRADE 1', 'GRADE 2', 'GRADE 3', 'GRADE 4', 'GRADE 5', 'GRADE 6'];
            if (!in_array($gradeLevel, $validGrades)) {
                throw new \Exception('Invalid grade level');
            }

            // Validate school year format (YYYY-YYYY)
            if (!preg_match('/^\d{4}-\d{4}$/', $schoolYear)) {
                throw new \Exception('Invalid school year format. Use YYYY-YYYY');
            }

            $section = new Section($this->db);
            $section->fill([
                'section_name' => $name,
                'grade_level' => $gradeLevel,
                'school_year' => $schoolYear
            ]);

            if (!$section->save()) {
                throw new \Exception('Failed to create section');
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Section created successfully',
                'section_id' => $section->section_id
            ];
        } catch (\Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function updateSection(int $sectionId, array $data): array
    {
        try {
            $this->db->beginTransaction();

            $section = new Section($this->db);
            if (!$section->find($sectionId)) {
                throw new \Exception('Section not found');
            }

            if (isset($data['grade_level'])) {
                $validGrades = ['KINDERGARTEN', 'GRADE 1', 'GRADE 2', 'GRADE 3', 'GRADE 4', 'GRADE 5', 'GRADE 6'];
                if (!in_array($data['grade_level'], $validGrades)) {
                    throw new \Exception('Invalid grade level');
                }
            }

            $allowedFields = ['section_name', 'grade_level', 'school_year'];
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $section->{$field} = $data[$field];
                }
            }

            if (!$section->save()) {
                throw new \Exception('Failed to update section');
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Section updated successfully'
            ];
        } catch (\Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getSectionById(int $sectionId): ?array
    {
        $section = new Section($this->db);
        $result = $section->find($sectionId);
        return $result ? $section->toArray() : null;
    }

    public function getAllSections(): array
    {
        $section = new Section($this->db);
        return $section->getAllSections();
    }

    public function getSectionsByGrade(string $gradeLevel): array
    {
        $section = new Section($this->db);
        return $section->getSectionsByGrade($gradeLevel);
    }

    public function getSectionsByYear(int $schoolYear): array
    {
        $section = new Section($this->db);
        return $section->getSectionsByYear($schoolYear);
    }

    public function getSectionsByGradeAndYear(string $gradeLevel, int $schoolYear): array
    {
        $section = new Section($this->db);
        return $section->getSectionsByGradeAndYear($gradeLevel, $schoolYear);
    }

    public function deleteSection(int $sectionId): array
    {
        try {
            $this->db->beginTransaction();

            $this->db->query("DELETE FROM sections WHERE section_id = ?", [$sectionId]);

            if ($this->db->rowCount() === 0) {
                throw new \Exception('Section not found');
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Section deleted successfully'
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
