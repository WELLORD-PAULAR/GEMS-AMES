<?php

namespace AMS\Models;

require_once __DIR__ . '/../Model.php';

class Teacher extends Model
{
    protected string $table = 'teachers';

    public function findByUserId(int $userId): ?self
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE user_id = ?", [$userId]);
        $result = $this->db->fetch();

        if ($result) {
            $this->attributes = $result;
            $this->dirty = [];
            return $this;
        }

        return null;
    }

    public function findByEmployeeNo(string $employeeNo): ?self
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE employee_no = ?", [$employeeNo]);
        $result = $this->db->fetch();

        if ($result) {
            $this->attributes = $result;
            $this->dirty = [];
            return $this;
        }

        return null;
    }

    public function getAllTeachers(): array
    {
        $this->db->query("SELECT * FROM {$this->table} ORDER BY last_name, first_name");
        return $this->db->fetchAll();
    }

    public function getFullName(): string
    {
        $parts = array_filter([
            $this->attributes['first_name'] ?? '',
            $this->attributes['middle_name'] ?? '',
            $this->attributes['last_name'] ?? ''
        ]);

        return implode(' ', $parts);
    }

    public function find($id): ?self
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE teacher_id = ?", [$id]);
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

        $this->attributes['teacher_id'] = $this->db->lastId();
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

        $values[] = $this->attributes['teacher_id'];
        $sql = "UPDATE {$this->table} SET " . implode(', ', $updates) . " WHERE teacher_id = ?";
        $this->db->query($sql, $values);

        $this->dirty = [];
        return $this->db->rowCount() > 0;
    }
}
