<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../handlers/SearchHandler.php';

use AMS\Database;
use AMS\Handlers\SearchHandler;

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['type'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Lookup type is required'
    ]);
    exit;
}

try {
    $db = new Database($pdo);
    $handler = new SearchHandler($db);

    $type = $_GET['type'];
    $query = trim($_GET['q'] ?? '');
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

    $results = $query !== ''
        ? $handler->search($type, $query, $limit)
        : $handler->getAll($type, $limit);

    echo json_encode([
        'success' => true,
        'data' => $results,
        'count' => count($results),
        'type' => $type,
        'query' => $query,
        'timestamp' => date('c')
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Search failed: ' . $e->getMessage(),
        'type' => $_GET['type'] ?? 'not specified',
        'error_trace' => $e->getTraceAsString()
    ]);
}
