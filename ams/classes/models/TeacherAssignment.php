<?php

namespace AMS\Models;

require_once __DIR__ . '/../Model.php';

class TeacherAssignment extends Model
{
    protected string $table = 'teacher_class_roster';

    public function getAssignmentsByTeacher(int $teacherId): array
    {
        $this->db->query(
            "SELECT ta.*, t.first_name, t.last_name, s.subject_name, sec.section_name, sec.grade_level
             FROM {$this->table} ta
             JOIN teachers t ON ta.teacher_id = t.teacher_id
             JOIN subjects s ON ta.subject_id = s.subject_id
             JOIN sections sec ON ta.section_id = sec.section_id
             WHERE ta.teacher_id = ?
             ORDER BY ta.school_year DESC, ta.term, s.subject_name",
            [$teacherId]
        );
        return $this->db->fetchAll();
    }

    public function getAssignmentsBySection(int $sectionId): array
    {
        $this->db->query(
            "SELECT ta.*, t.first_name, t.last_name, s.subject_name, sec.section_name
             FROM {$this->table} ta
             JOIN teachers t ON ta.teacher_id = t.teacher_id
             JOIN subjects s ON ta.subject_id = s.subject_id
             JOIN sections sec ON ta.section_id = sec.section_id
             WHERE ta.section_id = ?
             ORDER BY ta.school_year DESC, ta.term, s.subject_name",
            [$sectionId]
        );
        return $this->db->fetchAll();
    }

    public function getAssignmentsBySubject(int $subjectId): array
    {
        $this->db->query(
            "SELECT ta.*, t.first_name, t.last_name, sec.section_name, sec.grade_level
             FROM {$this->table} ta
             JOIN teachers t ON ta.teacher_id = t.teacher_id
             JOIN sections sec ON ta.section_id = sec.section_id
             WHERE ta.subject_id = ?
             ORDER BY ta.school_year DESC, ta.term, t.last_name",
            [$subjectId]
        );
        return $this->db->fetchAll();
    }

    public function getAssignmentsByYear(int $schoolYear): array
    {
        $this->db->query(
            "SELECT ta.*, t.first_name, t.last_name, s.subject_name, sec.section_name, sec.grade_level
             FROM {$this->table} ta
             JOIN teachers t ON ta.teacher_id = t.teacher_id
             JOIN subjects s ON ta.subject_id = s.subject_id
             JOIN sections sec ON ta.section_id = sec.section_id
             WHERE ta.school_year = ?
             ORDER BY ta.term, sec.grade_level, sec.section_name, t.last_name",
            [$schoolYear]
        );
        return $this->db->fetchAll();
    }

    public function getAssignmentsByYearAndTerm(int $schoolYear, int $term): array
    {
        $this->db->query(
            "SELECT ta.*, t.first_name, t.last_name, s.subject_name, sec.section_name, sec.grade_level
             FROM {$this->table} ta
             JOIN teachers t ON ta.teacher_id = t.teacher_id
             JOIN subjects s ON ta.subject_id = s.subject_id
             JOIN sections sec ON ta.section_id = sec.section_id
             WHERE ta.school_year = ? AND ta.term = ?
             ORDER BY sec.grade_level, sec.section_name, t.last_name",
            [$schoolYear, $term]
        );
        return $this->db->fetchAll();
    }

    public function exists(int $teacherId, int $subjectId, int $sectionId, int $schoolYear, int $term): bool
    {
        $this->db->query(
            "SELECT COUNT(*) as count FROM {$this->table} 
             WHERE teacher_id = ? AND subject_id = ? AND section_id = ? AND school_year = ? AND term = ?",
            [$teacherId, $subjectId, $sectionId, $schoolYear, $term]
        );
        $result = $this->db->fetch();
        return ($result['count'] ?? 0) > 0;
    }

    public function find($id): ?self
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE assignment_id = ?", [$id]);
        $result = $this->db->fetch();

        if ($result) {
            $this->attributes = $result;
            $this->dirty = [];
            return $this;
        }

        return null;
    }

    public function insert(): bool
    {
        $columns = array_keys($this->attributes);
        $placeholders = array_fill(0, count($columns), '?');
        $values = array_values($this->attributes);

        $sql = "INSERT INTO {$this->table} (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
        $this->db->query($sql, $values);

        $this->attributes['assignment_id'] = $this->db->lastId();
        return $this->db->rowCount() > 0;
    }

    public function update(): bool
    {
        if (empty($this->dirty)) {
            return true;
        }

        $updates = [];
        $values = [];

        foreach ($this->dirty as $key => $isDirty) {
            if ($isDirty) {
                $updates[] = "$key = ?";
                $values[] = $this->attributes[$key];
            }
        }

        $values[] = $this->attributes['assignment_id'];
        $sql = "UPDATE {$this->table} SET " . implode(', ', $updates) . " WHERE assignment_id = ?";
        $this->db->query($sql, $values);

        $this->dirty = [];
        return $this->db->rowCount() > 0;
    }
}
