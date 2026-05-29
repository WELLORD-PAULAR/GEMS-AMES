<?php
namespace AMS\Models;

use AMS\Database;

abstract class Model
{
    protected Database $db;
    protected string $table = '';
    protected array $attributes = [];
    protected array $dirty = [];

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function __set(string $name, $value)
    {
        $this->attributes[$name] = $value;
        $this->dirty[$name] = true;
    }

    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    public function fill(array $data): self
    {
        foreach ($data as $key => $value) {
            $this->attributes[$key] = $value;
        }

        return $this;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function find($id): ?self
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
        $result = $this->db->fetch();

        if ($result) {
            $this->attributes = $result;
            $this->dirty = [];
            return $this;
        }

        return null;
    }

    public function all(int $limit = null, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params = [$limit, $offset];
        }

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    public function save(): bool
    {
        if (empty($this->attributes)) {
            throw new \Exception('No attributes to save');
        }

        if (isset($this->attributes['id']) && $this->attributes['id']) {
            return $this->update();
        }

        return $this->insert();
    }

    protected function insert(): bool
    {
        $columns = array_keys($this->attributes);
        $placeholders = array_fill(0, count($columns), '?');
        $values = array_values($this->attributes);

        $sql = "INSERT INTO {$this->table} (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
        $this->db->query($sql, $values);

        $this->attributes['id'] = $this->db->lastId();
        return $this->db->rowCount() > 0;
    }

    protected function update(): bool
    {
        if (!isset($this->attributes['id'])) {
            throw new \Exception('Cannot update without ID');
        }

        $id = $this->attributes['id'];
        unset($this->attributes['id']);

        $sets = [];
        $values = [];
        foreach ($this->attributes as $column => $value) {
            $sets[] = "{$column} = ?";
            $values[] = $value;
        }

        $values[] = $id;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE id = ?";

        $this->db->query($sql, $values);
        return $this->db->rowCount() > 0;
    }

    public function delete($id): bool
    {
        $this->db->query("DELETE FROM {$this->table} WHERE id = ?", [$id]);
        return $this->db->success();
    }

    public function where(string $column, $operator, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->db->query("SELECT * FROM {$this->table} WHERE {$column} {$operator} ?", [$value]);
        return $this->db->fetch();
    }

    public function count(string $where = null, $value = null): int
    {
        if ($where && $value !== null) {
            $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE {$where} = ?", [$value]);
        } else {
            $this->db->query("SELECT COUNT(*) as count FROM {$this->table}");
        }

        $result = $this->db->fetch();
        return $result['count'] ?? 0;
    }
}
