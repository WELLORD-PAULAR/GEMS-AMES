<?php

namespace AMS\Models;

require_once __DIR__ . '/../Model.php';

class Lookup extends Model
{
    protected string $table = 'lookup_values';

    public function findByTypeAndQuery(string $type, string $query, int $limit = 10): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE type = ? AND (name LIKE ? OR value LIKE ?) ORDER BY name LIMIT ?";
        $this->db->query($sql, [$type, "%{$query}%", "%{$query}%", $limit]);
        return $this->db->fetchAll();
    }

    public function findAllByType(string $type, int $limit = 50): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE type = ? ORDER BY name LIMIT ?";
        $this->db->query($sql, [$type, $limit]);
        return $this->db->fetchAll();
    }
}
?>