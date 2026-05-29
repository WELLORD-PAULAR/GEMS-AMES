<?php

namespace AMS\Handlers;

use AMS\Database;
use AMS\Models\Lookup;

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/models/Lookup.php';

class SearchHandler
{
    private Database $db;
    private Lookup $lookup;

    private array $lookupMap = [
        'mother-tongue' => 'mother_tongue',
        'religions' => 'religion',
        'indigenous-groups' => 'indigenous_group'
    ];

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->lookup = new Lookup($db);
    }

    public function getAll(string $type, int $limit = 50): array
    {
        if (!isset($this->lookupMap[$type])) {
            throw new \InvalidArgumentException('Invalid lookup type');
        }

        return $this->lookup->findAllByType($type, $limit);
    }

    public function search(string $type, string $query, int $limit = 10): array
    {
        if (!isset($this->lookupMap[$type])) {
            throw new \InvalidArgumentException('Invalid lookup type');
        }

        return $this->lookup->findByTypeAndQuery($type, $query, $limit);
    }
}
