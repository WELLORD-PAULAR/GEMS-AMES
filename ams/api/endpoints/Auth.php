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

    protected function getColumnNames()
    {
        $columns = $this->db->query("SHOW COLUMNS FROM {$this->table}");
        return array_map(fn($column) => $column['Field'], $columns);
    }

    protected function countUsers()
    {
        $result = $this->db->fetch("SELECT COUNT(*) as total FROM {$this->table}");
        return (int)($result['total'] ?? 0);
    }

    protected function bootstrapDefaultAdmin($username, $password)
    {
        if (strtolower(trim($username)) !== 'admin') {
            return false;
        }

        if ($this->countUsers() > 0) {
            return false;
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $this->db->insert($this->table, [
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => $passwordHash,
            'role' => 'ADMIN',
            'is_active' => 1
        ]);

        return true;
    }

    /**
     * POST /auth - Login user
     */
    public function store()
    {
        try {
            $username = trim((string)$this->input('username'));
            $password = (string)$this->input('password');

            if ($username === '' || $password === '') {
                ApiResponse::error("Username and password are required", ApiResponse::HTTP_BAD_REQUEST);
            }

            $columnNames = $this->getColumnNames();
            $hasPasswordHash = in_array('password_hash', $columnNames, true);
            $hasLegacyPassword = in_array('password', $columnNames, true);

            $selectFields = ['id', 'username', 'email', 'role', 'is_active'];
            if ($hasPasswordHash) {
                $selectFields[] = 'password_hash';
            }
            if ($hasLegacyPassword) {
                $selectFields[] = 'password';
            }

            $user = $this->db->fetch(
                "SELECT " . implode(', ', $selectFields) . " 
                 FROM {$this->table} 
                 WHERE username = ? AND is_active = 1",
                [$username]
            );

            if (!$user) {
                if ($this->bootstrapDefaultAdmin($username, $password)) {
                    $user = $this->db->fetch(
                        "SELECT " . implode(', ', $selectFields) . " 
                         FROM {$this->table} 
                         WHERE username = ? AND is_active = 1",
                        [$username]
                    );
                }
            }

            if (!$user) {
                ApiResponse::error("Invalid credentials", ApiResponse::HTTP_UNAUTHORIZED);
            }

            $passwordVerified = false;

            if ($hasPasswordHash && !empty($user['password_hash'])) {
                $passwordVerified = password_verify($password, $user['password_hash']);
            }

            if (!$passwordVerified && $hasLegacyPassword && !empty($user['password'])) {
                $storedPassword = $user['password'];
                $legacyPasswordVerified = false;

                if (password_get_info($storedPassword)['algo'] !== 0) {
                    $legacyPasswordVerified = password_verify($password, $storedPassword);
                } else {
                    $legacyPasswordVerified = hash_equals($storedPassword, $password);
                }

                if ($legacyPasswordVerified && $hasPasswordHash) {
                    $this->db->update($this->table, [
                        'password_hash' => password_hash($password, PASSWORD_BCRYPT)
                    ], ['id' => (int)$user['id']]);
                    $user['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
                }

                $passwordVerified = $legacyPasswordVerified;
            }

            if (!$passwordVerified) {
                ApiResponse::error("Invalid credentials", ApiResponse::HTTP_UNAUTHORIZED);
            }

            // Remove password hash from response
            unset($user['password_hash']);
            unset($user['password']);

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
