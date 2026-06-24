<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class SessionManager
{
    const TOKEN_KEY = 'auth_token';
    const USER_KEY = 'auth_user';
    const EXPIRY_KEY = 'auth_expiry';


    public static function isAuthenticated()
    {
        if (!isset($_SESSION[self::TOKEN_KEY])) {
            return false;
        }

        if (isset($_SESSION[self::EXPIRY_KEY])) {
            if (strtotime($_SESSION[self::EXPIRY_KEY]) < time()) {
                self::logout();
                return false;
            }
        }

        return true;
    }

    public static function setAuth($token, $user, $expiresAt)
    {
        $_SESSION[self::TOKEN_KEY] = $token;
        $_SESSION[self::USER_KEY] = $user;
        $_SESSION[self::EXPIRY_KEY] = $expiresAt;
    }

    public static function getUser()
    {
        return $_SESSION[self::USER_KEY] ?? null;
    }

    public static function getToken()
    {
        return $_SESSION[self::TOKEN_KEY] ?? null;
    }

    public static function logout()
    {
        unset($_SESSION[self::TOKEN_KEY]);
        unset($_SESSION[self::USER_KEY]);
        unset($_SESSION[self::EXPIRY_KEY]);
        session_destroy();
    }

    public static function getAuthHeader()
    {
        $token = self::getToken();
        return $token ? ['Authorization: Bearer ' . $token] : [];
    }

    public static function apiRequest($url, $method = 'GET', $data = null)
    {
        $ch = curl_init();

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => array_merge(
                ['Content-Type: application/json'],
                self::getAuthHeader()
            )
        ];

        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'error' => 'Request failed: ' . $error,
                'httpCode' => 0
            ];
        }

        $data = json_decode($response, true);
        if ($httpCode === 401) {
            self::logout();
            header('Location: ../login/');
            exit;
        }

        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'data' => $data,
            'httpCode' => $httpCode
        ];
    }

    public static function requireAuth()
    {
        if (!self::isAuthenticated()) {
            header('Location: ../login/');
            exit;
        }
    }

    public static function normalizeRole($role)
    {
        return strtoupper(trim((string)$role));
    }

    public static function getUserRole()
    {
        $user = self::getUser();
        return self::normalizeRole($user['role'] ?? '');
    }

    public static function redirectToDashboard()
    {
        $role = self::getUserRole();

        if ($role === 'ADMIN') {
            header('Location: ../dashboard/admin_dashboard/');
        } else {
            header('Location: ../dashboard/teacher_dashboard/');
        }

        exit;
    }

    public static function hasRole($role)
    {
        return self::getUserRole() === self::normalizeRole($role);
    }

    public static function requireRole($roles)
    {
        self::requireAuth();
        
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $userRole = self::getUserRole();
        foreach ($roles as $role) {
            if ($userRole === self::normalizeRole($role)) {
                return;
            }
        }

        header('HTTP/1.1 403 Forbidden');
        http_response_code(403);
        echo 'Access denied: insufficient permissions';
        exit;
    }
}