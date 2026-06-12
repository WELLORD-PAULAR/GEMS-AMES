<?php

namespace AMS\Models;

require_once __DIR__ . '/../Model.php';

class Subject extends Model
{
    protected string $table = 'subjects';

    public function findByCode(string $code): ?self
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE subject_code = ?", [$code]);
        $result = $this->db->fetch();

        if ($result) {
            $this->attributes = $result;
            $this->dirty = [];
            return $this;
        }

        return null;
    }

    public function getAllSubjects(): array
    {
        $this->db->query("SELECT * FROM {$this->table} ORDER BY subject_name");
        return $this->db->fetchAll();
    }

    public function getSubjectsByTerm(int $term): array
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE offered_term = ? OR offered_term IS NULL ORDER BY subject_name", [$term]);
        return $this->db->fetchAll();
    }

    public function find($id): ?self
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE subject_id = ?", [$id]);
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

        $this->attributes['subject_id'] = $this->db->lastId();
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

        $values[] = $this->attributes['subject_id'];
        $sql = "UPDATE {$this->table} SET " . implode(', ', $updates) . " WHERE subject_id = ?";
        $this->db->query($sql, $values);

        $this->dirty = [];
        return $this->db->rowCount() > 0;
    }
}
