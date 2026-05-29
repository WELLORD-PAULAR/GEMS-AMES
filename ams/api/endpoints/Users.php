<?php

namespace API;

require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../classes/ApiResponse.php';

/**
 * Users Controller
 * Handles user account CRUD operations
 */

class Users extends BaseController
{
    protected $table = 'user_account';

    protected $validationRules = [
        'username' => 'required|min:3|max:100',
        'email' => 'required|email',
        'password' => 'required|min:6',
        'role' => 'required'
    ];

    /**
     * GET /users - List all users with pagination
     */
    public function index()
    {
        try {
            $page = max(1, (int)$this->input('page', 1));
            $limit = min(100, max(1, (int)$this->input('limit', 10)));
            $offset = ($page - 1) * $limit;

            $total = $this->db->fetch(
                "SELECT COUNT(*) as count FROM {$this->table}"
            );

            $users = $this->db->query(
                "SELECT id, username, email, role, created_at, is_active 
                 FROM {$this->table} 
                 ORDER BY created_at DESC 
                 LIMIT ? OFFSET ?",
                [$limit, $offset]
            );

            ApiResponse::paginated($users, $total['count'], $page, $limit);
        } catch (\Exception $e) {
            ApiResponse::error("Failed to retrieve users", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * GET /users/{id} - Get a specific user
     */
    public function show($id)
    {
        try {
            $user = $this->db->fetch(
                "SELECT id, username, email, role, created_at, is_active 
                 FROM {$this->table} 
                 WHERE id = ?",
                [(int)$id]
            );

            if (!$user) {
                ApiResponse::error("User not found", ApiResponse::HTTP_NOT_FOUND);
            }

            ApiResponse::success($user);
        } catch (\Exception $e) {
            ApiResponse::error("Failed to retrieve user", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * POST /users - Create a new user
     */
    public function store()
    {
        try {
            $this->validate();

            $username = $this->input('username');
            $email = $this->input('email');
            $password = $this->input('password');
            $role = $this->input('role', 'PARENT');

            // Check if username already exists
            $existing = $this->db->fetch(
                "SELECT id FROM {$this->table} WHERE username = ?",
                [$username]
            );

            if ($existing) {
                ApiResponse::error("Username already exists", ApiResponse::HTTP_CONFLICT);
            }

            // Check if email already exists
            $existing = $this->db->fetch(
                "SELECT id FROM {$this->table} WHERE email = ?",
                [$email]
            );

            if ($existing) {
                ApiResponse::error("Email already exists", ApiResponse::HTTP_CONFLICT);
            }

            // Hash password
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            $id = $this->db->insert($this->table, [
                'username' => $username,
                'email' => $email,
                'password_hash' => $passwordHash,
                'role' => $role,
                'is_active' => 1
            ]);

            $user = $this->db->fetch(
                "SELECT id, username, email, role, created_at, is_active 
                 FROM {$this->table} 
                 WHERE id = ?",
                [$id]
            );

            ApiResponse::success($user, ApiResponse::HTTP_CREATED, "User created successfully");
        } catch (\Exception $e) {
            if ($e->getCode() === 422) {
                ApiResponse::error($e->getMessage(), 422);
            }
            ApiResponse::error("Failed to create user", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * PUT /users/{id} - Update a user
     */
    public function update($id)
    {
        try {
            $user = $this->db->fetch(
                "SELECT id FROM {$this->table} WHERE id = ?",
                [(int)$id]
            );

            if (!$user) {
                ApiResponse::error("User not found", ApiResponse::HTTP_NOT_FOUND);
            }

            $updateData = [];

            if ($this->input('username')) {
                // Check if username is already taken by another user
                $existing = $this->db->fetch(
                    "SELECT id FROM {$this->table} WHERE username = ? AND id != ?",
                    [$this->input('username'), (int)$id]
                );
                if ($existing) {
                    ApiResponse::error("Username already taken", ApiResponse::HTTP_CONFLICT);
                }
                $updateData['username'] = $this->input('username');
            }

            if ($this->input('email')) {
                $existing = $this->db->fetch(
                    "SELECT id FROM {$this->table} WHERE email = ? AND id != ?",
                    [$this->input('email'), (int)$id]
                );
                if ($existing) {
                    ApiResponse::error("Email already taken", ApiResponse::HTTP_CONFLICT);
                }
                $updateData['email'] = $this->input('email');
            }

            if ($this->input('password')) {
                $updateData['password_hash'] = password_hash($this->input('password'), PASSWORD_BCRYPT);
            }

            if ($this->input('role')) {
                $updateData['role'] = $this->input('role');
            }

            if ($this->input('is_active') !== null) {
                $updateData['is_active'] = (int)$this->input('is_active');
            }

            if (empty($updateData)) {
                ApiResponse::error("No data to update", ApiResponse::HTTP_BAD_REQUEST);
            }

            $this->db->update($this->table, $updateData, ['id' => (int)$id]);

            $updated = $this->db->fetch(
                "SELECT id, username, email, role, created_at, is_active 
                 FROM {$this->table} 
                 WHERE id = ?",
                [(int)$id]
            );

            ApiResponse::success($updated, ApiResponse::HTTP_OK, "User updated successfully");
        } catch (\Exception $e) {
            ApiResponse::error("Failed to update user", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * DELETE /users/{id} - Delete a user
     */
    public function destroy($id)
    {
        try {
            $user = $this->db->fetch(
                "SELECT id FROM {$this->table} WHERE id = ?",
                [(int)$id]
            );

            if (!$user) {
                ApiResponse::error("User not found", ApiResponse::HTTP_NOT_FOUND);
            }

            $this->db->delete($this->table, ['id' => (int)$id]);

            ApiResponse::success([], ApiResponse::HTTP_OK, "User deleted successfully");
        } catch (\Exception $e) {
            ApiResponse::error("Failed to delete user", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }
}
?>
