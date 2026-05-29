<?php

namespace API;

require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../classes/ApiResponse.php';

/**
 * Authentication Controller
 * Handles user login and authentication
 */

class Auth extends BaseController
{
    protected $table = 'user_account';

    public function index()
    {
        ApiResponse::error("Method not allowed", 405);
    }

    public function show($id)
    {
        ApiResponse::error("Method not allowed", 405);
    }

    /**
     * POST /auth - Login user
     */
    public function store()
    {
        try {
            $username = $this->input('username');
            $password = $this->input('password');

            if (empty($username) || empty($password)) {
                ApiResponse::error("Username and password are required", ApiResponse::HTTP_BAD_REQUEST);
            }

            $user = $this->db->fetch(
                "SELECT id, username, password_hash, email, role, is_active 
                 FROM {$this->table} 
                 WHERE username = ? AND is_active = 1",
                [$username]
            );

            if (!$user) {
                ApiResponse::error("Invalid credentials", ApiResponse::HTTP_UNAUTHORIZED);
            }

            if (!password_verify($password, $user['password_hash'])) {
                ApiResponse::error("Invalid credentials", ApiResponse::HTTP_UNAUTHORIZED);
            }

            // Remove password hash from response
            unset($user['password_hash']);

            // Generate a simple token (in production, use JWT)
            $token = bin2hex(random_bytes(32));

            ApiResponse::success([
                'user' => $user,
                'token' => $token,
                'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours'))
            ], ApiResponse::HTTP_OK, "Login successful");
        } catch (\Exception $e) {
            ApiResponse::error("Authentication failed", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    public function update($id)
    {
        ApiResponse::error("Method not allowed", 405);
    }

    public function destroy($id)
    {
        ApiResponse::error("Method not allowed", 405);
    }
}
?>
