<?php

namespace AMS\Models;

require_once __DIR__ . '/../Model.php';

class Lookup extends Model
{
    protected string $table = 'lookup_values';

    // Map of lookup types to actual table names
    private array $tableMap = [
        'mother_tongue' => 'mother_tongue',
        'religion' => 'religion',
        'indigenous_group' => 'indigenous_group'
    ];

    public function findByTypeAndQuery(string $type, string $query, int $limit = 10): array
    {
        if (!isset($this->tableMap[$type])) {
            throw new \InvalidArgumentException('Invalid lookup type: ' . $type);
        }

        $tableName = $this->tableMap[$type];
        $sql = "SELECT id, name FROM {$tableName} WHERE (name LIKE ? OR id LIKE ?) ORDER BY name LIMIT ?";
        $this->db->query($sql, ["%{$query}%", "%{$query}%", $limit]);
        return $this->db->fetchAll();
    }

    public function findAllByType(string $type, int $limit = 50): array
    {
        if (!isset($this->tableMap[$type])) {
            throw new \InvalidArgumentException('Invalid lookup type: ' . $type);
        }

        $tableName = $this->tableMap[$type];
        $sql = "SELECT id, name FROM {$tableName} ORDER BY name LIMIT ?";
        $this->db->query($sql, [$limit]);
        return $this->db->fetchAll();
    }
}
?>