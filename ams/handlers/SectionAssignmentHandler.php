<?php

namespace AMS\Handlers;

use PDO;
use PDOException;

class SectionAssignmentHandler
{
    private PDO $pdo;

    /**
     * Section options organized by grade level
     */
    const SECTIONS = [
        'K' => ['K-Obedience', 'K-Kindness', 'K-Joy'],
        '1' => ['1-Care', '1-Love', '1-Hope'],
        '2' => ['2-Integrity', '2-Patience', '2-Unity'],
        '3' => ['3-Peace', '3-Faith'],
        '4' => ['4-Charity', '4-Generosity'],
        '5' => ['5-Loyalty', '5-Honesty'],
        '6' => ['6-Humility', '6-Purity'],
    ];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get all students with their current sections
     * @param string|null $gradeLevel Filter by grade level (optional)
     * @return array Students data
     */
    public function getStudents(?string $gradeLevel = null): array
    {
        try {
            $query = "SELECT 
                fk_full_name_bd,
                CONCAT(pi_first_name, ' ', pi_middle_name, ' ', pi_last_name) as full_name,
                pi_first_name,
                pi_middle_name,
                pi_last_name,
                CASE 
                    WHEN ed_grade_level = 'Kindergart' THEN 'K'
                    ELSE ed_grade_level
                END as ed_grade_level,
                section,
                ed_lrn,
                verification
            FROM enrollment2";

            $params = [];

            if ($gradeLevel !== null) {
                // Convert 'K' to 'Kindergart' for database query
                $dbGradeLevel = ($gradeLevel === 'K') ? 'Kindergart' : $gradeLevel;
                $query .= " WHERE ed_grade_level = ?";
                $params[] = $dbGradeLevel;
            }

            $query .= " ORDER BY CASE WHEN ed_grade_level = 'Kindergart' THEN 0 ELSE CAST(ed_grade_level AS UNSIGNED) END ASC, pi_last_name ASC, pi_first_name ASC";

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get students by verification status
     * @param string $status VERIFIED, PROCESSING, or REJECTED
     * @return array Students data
     */
    public function getStudentsByStatus(string $status): array
    {
        try {
            $query = "SELECT 
                fk_full_name_bd,
                CONCAT(pi_first_name, ' ', pi_middle_name, ' ', pi_last_name) as full_name,
                pi_first_name,
                pi_middle_name,
                pi_last_name,
                CASE 
                    WHEN ed_grade_level = 'Kindergart' THEN 'K'
                    ELSE ed_grade_level
                END as ed_grade_level,
                section,
                ed_lrn,
                verification
            FROM enrollment2
            WHERE verification = ?
            ORDER BY CASE WHEN ed_grade_level = 'Kindergart' THEN 0 ELSE CAST(ed_grade_level AS UNSIGNED) END ASC, pi_last_name ASC, pi_first_name ASC";

            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$status]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Update section for a student
     * @param string $studentId The student's fk_full_name_bd
     * @param string $section The section to assign
     * @return bool Success status
     */
    public function assignSection(string $studentId, string $section): bool
    {
        try {
            // Validate section exists
            $isValidSection = false;
            foreach (self::SECTIONS as $sections) {
                if (in_array($section, $sections)) {
                    $isValidSection = true;
                    break;
                }
            }

            if (!$isValidSection) {
                return false;
            }

            $query = "UPDATE enrollment2 SET section = ? WHERE fk_full_name_bd = ?";
            $stmt = $this->pdo->prepare($query);

            return $stmt->execute([$section, $studentId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get available sections for a grade level
     * @param string $gradeLevel The grade level (K, 1, 2, 3, 4, 5, 6)
     * @return array Array of section names
     */
    public function getSectionsForGrade(string $gradeLevel): array
    {
        return self::SECTIONS[$gradeLevel] ?? [];
    }

    /**
     * Get all available sections
     * @return array Array of all sections organized by grade
     */
    public function getAllSections(): array
    {
        return self::SECTIONS;
    }

    /**
     * Bulk assign sections to multiple students
     * @param array $assignments Array of ['studentId' => 'section']
     * @return array Results of each assignment
     */
    public function bulkAssignSections(array $assignments): array
    {
        $results = [];

        try {
            $this->pdo->beginTransaction();

            foreach ($assignments as $studentId => $section) {
                $results[$studentId] = $this->assignSection($studentId, $section);
            }

            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return [];
        }

        return $results;
    }
}
?>
