<?php
/**
 * Session Manager
 * Handles authentication and session management server-side
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class SessionManager
{
    const TOKEN_KEY = 'auth_token';
    const USER_KEY = 'auth_user';
    const EXPIRY_KEY = 'auth_expiry';

    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated()
    {
        if (!isset($_SESSION[self::TOKEN_KEY])) {
            return false;
        }

        // Check if token has expired
        if (isset($_SESSION[self::EXPIRY_KEY])) {
            if (strtotime($_SESSION[self::EXPIRY_KEY]) < time()) {
                self::logout();
                return false;
            }
        }

        return true;
    }

    /**
     * Store authentication data after successful login
     */
    public static function setAuth($token, $user, $expiresAt)
    {
        $_SESSION[self::TOKEN_KEY] = $token;
        $_SESSION[self::USER_KEY] = $user;
        $_SESSION[self::EXPIRY_KEY] = $expiresAt;
    }

    /**
     * Get stored user data
     */
    public static function getUser()
    {
        return $_SESSION[self::USER_KEY] ?? null;
    }

    /**
     * Get stored authentication token
     */
    public static function getToken()
    {
        return $_SESSION[self::TOKEN_KEY] ?? null;
    }

    /**
     * Clear authentication data (logout)
     */
    public static function logout()
    {
        unset($_SESSION[self::TOKEN_KEY]);
        unset($_SESSION[self::USER_KEY]);
        unset($_SESSION[self::EXPIRY_KEY]);
        session_destroy();
    }

    /**
     * Get authorization header for API requests
     */
    public static function getAuthHeader()
    {
        $token = self::getToken();
        return $token ? ['Authorization: Bearer ' . $token] : [];
    }

    /**
     * Make authenticated API request
     */
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

        // If 401, logout user
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

    /**
     * Require authentication
     * Redirect to login if not authenticated
     */
    public static function requireAuth()
    {
        if (!self::isAuthenticated()) {
            header('Location: ../login/');
            exit;
        }
    }

    /**
     * Normalize stored roles so role comparisons are reliable.
     */
    public static function normalizeRole($role)
    {
        return strtoupper(trim((string)$role));
    }

    /**
     * Get user role
     */
    public static function getUserRole()
    {
        $user = self::getUser();
        return self::normalizeRole($user['role'] ?? '');
    }

    /**
     * Redirect the user to the dashboard that matches their role.
     */
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

    /**
     * Check if user has specific role
     */
    public static function hasRole($role)
    {
        return self::getUserRole() === self::normalizeRole($role);
    }
}
?>
