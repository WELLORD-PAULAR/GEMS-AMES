<?php

namespace AMS\Models;

require_once __DIR__ . '/../Model.php';

class Section extends Model
{
    protected string $table = 'sections';

    public function getAllSections(): array
    {
        $this->db->query("SELECT * FROM {$this->table} ORDER BY school_year DESC, grade_level, section_name");
        return $this->db->fetchAll();
    }

    public function getSectionsByGrade(string $gradeLevel): array
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE grade_level = ? ORDER BY school_year DESC, section_name", [$gradeLevel]);
        return $this->db->fetchAll();
    }

    public function getSectionsByYear(int $schoolYear): array
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE school_year = ? ORDER BY grade_level, section_name", [$schoolYear]);
        return $this->db->fetchAll();
    }

    public function getSectionsByGradeAndYear(string $gradeLevel, int $schoolYear): array
    {
        $this->db->query(
            "SELECT * FROM {$this->table} WHERE grade_level = ? AND school_year = ? ORDER BY section_name",
            [$gradeLevel, $schoolYear]
        );
        return $this->db->fetchAll();
    }

    public function find($id): ?self
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE section_id = ?", [$id]);
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

        $this->attributes['section_id'] = $this->db->lastId();
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

        $values[] = $this->attributes['section_id'];
        $sql = "UPDATE {$this->table} SET " . implode(', ', $updates) . " WHERE section_id = ?";
        $this->db->query($sql, $values);

        $this->dirty = [];
        return $this->db->rowCount() > 0;
    }
}
