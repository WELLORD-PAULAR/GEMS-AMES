<?php
/**
 * REST API Entry Point
 * Handles all API requests with routing
 */

namespace API;

header('Content-Type: application/json; charset=utf-8');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Include configuration and classes
require_once '../config/config.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/ApiResponse.php';

use API\ApiResponse;

// Handle CORS and OPTIONS requests
ApiResponse::handleOptions();

// Initialize database
try {
    $db = new \API\Database($pdo);
} catch (Exception $e) {
    ApiResponse::error("Database connection failed", ApiResponse::HTTP_INTERNAL_ERROR);
}

// Get request details
$request = $_GET['request'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];
$parts = array_filter(explode('/', $request));
$parts = array_values($parts); // Re-index array

// Route mapping
$routes = [
    // Users
    'users' => ['controller' => 'Users', 'file' => 'Users.php'],
    'auth' => ['controller' => 'Auth', 'file' => 'Auth.php'],
    
    // Enrollments
    'enrollments' => ['controller' => 'Enrollments', 'file' => 'Enrollments.php'],
    'addresses' => ['controller' => 'EnrollmentAddresses', 'file' => 'EnrollmentAddresses.php'],
    'medical' => ['controller' => 'EnrollmentMedical', 'file' => 'EnrollmentMedical.php'],
    'parents' => ['controller' => 'EnrollmentParents', 'file' => 'EnrollmentParents.php'],
    'special-needs' => ['controller' => 'EnrollmentSpecialNeeds', 'file' => 'EnrollmentSpecialNeeds.php'],
    
    // Reference data
    'mother-tongue' => ['controller' => 'MotherTongue', 'file' => 'Lookup.php'],
    'religions' => ['controller' => 'Religion', 'file' => 'Lookup.php'],
    'indigenous-groups' => ['controller' => 'IndigenousGroup', 'file' => 'Lookup.php'],
];

// Get the resource from URL
$resource = $parts[0] ?? '';
$id = $parts[1] ?? null;

if (!$resource) {
    ApiResponse::error("No resource specified", ApiResponse::HTTP_BAD_REQUEST);
}

if (!isset($routes[$resource])) {
    ApiResponse::error("Resource not found", ApiResponse::HTTP_NOT_FOUND);
}

// Include the appropriate controller file
$routeConfig = $routes[$resource];
$controllerPath = __DIR__ . "/endpoints/" . $routeConfig['file'];

if (!file_exists($controllerPath)) {
    ApiResponse::error("Endpoint not implemented", ApiResponse::HTTP_NOT_FOUND);
}

require_once $controllerPath;
$controller = $routeConfig['controller'];

// Create controller instance and call method
try {
    $controllerClass = "\\API\\{$controller}";
    
    if (!class_exists($controllerClass)) {
        ApiResponse::error("Controller class not found", ApiResponse::HTTP_INTERNAL_ERROR);
    }
    
    $controller = new $controllerClass($db);
    
    // Route to appropriate method
    switch ($method) {
        case 'GET':
            if ($id) {
                $controller->show($id);
            } else {
                $controller->index();
            }
            break;
        case 'POST':
            $controller->store();
            break;
        case 'PUT':
        case 'PATCH':
            if (!$id) {
                ApiResponse::error("ID required for update", ApiResponse::HTTP_BAD_REQUEST);
            }
            $controller->update($id);
            break;
        case 'DELETE':
            if (!$id) {
                ApiResponse::error("ID required for delete", ApiResponse::HTTP_BAD_REQUEST);
            }
            $controller->destroy($id);
            break;
        default:
            ApiResponse::error("Method not allowed", 405);
    }
} catch (Exception $e) {
    ApiResponse::error(
        "Request failed: " . $e->getMessage(),
        ApiResponse::HTTP_INTERNAL_ERROR,
        ['exception' => get_class($e)]
    );
}
?>
