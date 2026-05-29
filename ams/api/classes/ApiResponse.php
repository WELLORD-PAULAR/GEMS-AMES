<?php
namespace API;

class ApiResponse
{
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_CONFLICT = 409;
    const HTTP_INTERNAL_ERROR = 500;

    public static function success($data = [], $status = self::HTTP_OK, $message = "Success")
    {
        self::send([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ], $status);
    }

    public static function error($message, $status = self::HTTP_BAD_REQUEST, $errors = [])
    {
        self::send([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => date('Y-m-d H:i:s')
        ], $status);
    }

    public static function paginated($data, $total, $page, $limit)
    {
        self::send([
            'success' => true,
            'message' => 'Data retrieved successfully',
            'data' => $data,
            'pagination' => [
                'total' => (int)$total,
                'page' => (int)$page,
                'limit' => (int)$limit,
                'pages' => ceil($total / $limit)
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ], self::HTTP_OK);
    }

    private static function send($response, $status = self::HTTP_OK)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGINS);
        header('Access-Control-Allow-Methods: ' . ALLOWED_METHODS);
        header('Access-Control-Allow-Headers: ' . ALLOWED_HEADERS);
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function handleOptions()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            header('Content-Type: application/json');
            header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGINS);
            header('Access-Control-Allow-Methods: ' . ALLOWED_METHODS);
            header('Access-Control-Allow-Headers: ' . ALLOWED_HEADERS);
            exit;
        }
    }
}
?>
