<?php
namespace API;
require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../classes/ApiResponse.php';

class MotherTongue extends BaseController
{
    protected $table = 'mother_tongue';

    public function index()
    {
        try {
            $languages = $this->db->query(
                "SELECT * FROM {$this->table} ORDER BY name ASC"
            );

            ApiResponse::success($languages);
        } catch (\Exception $e) {
            ApiResponse::error("Failed to retrieve languages", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    public function show($id)
    {
        try {
            $language = $this->db->fetch(
                "SELECT * FROM {$this->table} WHERE id = ?",
                [(int)$id]
            );

            if (!$language) {
                ApiResponse::error("Language not found", ApiResponse::HTTP_NOT_FOUND);
            }

            ApiResponse::success($language);
        } catch (\Exception $e) {
            ApiResponse::error("Failed to retrieve language", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    public function store()
    {
        try {
            $name = $this->input('name');

            if (empty($name)) {
                ApiResponse::error("Name is required", ApiResponse::HTTP_BAD_REQUEST);
            }

            $existing = $this->db->fetch(
                "SELECT id FROM {$this->table} WHERE name = ?",
                [$name]
            );

            if ($existing) {
                ApiResponse::error("Language already exists", ApiResponse::HTTP_CONFLICT);
            }

            $id = $this->db->insert($this->table, ['name' => $name]);

            $language = $this->db->fetch(
                "SELECT * FROM {$this->table} WHERE id = ?",
                [$id]
            );

            ApiResponse::success($language, ApiResponse::HTTP_CREATED, "Language created successfully");
        } catch (\Exception $e) {
            ApiResponse::error("Failed to create language", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    public function update($id)
    {
        try {
            $language = $this->db->fetch(
                "SELECT id FROM {$this->table} WHERE id = ?",
                [(int)$id]
            );

            if (!$language) {
                ApiResponse::error("Language not found", ApiResponse::HTTP_NOT_FOUND);
            }

            $name = $this->input('name');

            if (empty($name)) {
                ApiResponse::error("Name is required", ApiResponse::HTTP_BAD_REQUEST);
            }

            $this->db->update($this->table, ['name' => $name], ['id' => (int)$id]);

            $updated = $this->db->fetch(
                "SELECT * FROM {$this->table} WHERE id = ?",
                [(int)$id]
            );

            ApiResponse::success($updated, ApiResponse::HTTP_OK, "Language updated successfully");
        } catch (\Exception $e) {
            ApiResponse::error("Failed to update language", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            $language = $this->db->fetch(
                "SELECT id FROM {$this->table} WHERE id = ?",
                [(int)$id]
            );

            if (!$language) {
                ApiResponse::error("Language not found", ApiResponse::HTTP_NOT_FOUND);
            }

            $this->db->delete($this->table, ['id' => (int)$id]);

            ApiResponse::success([], ApiResponse::HTTP_OK, "Language deleted successfully");
        } catch (\Exception $e) {
            ApiResponse::error("Failed to delete language", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }
}

class Religion extends BaseController
{
    protected $table = 'religion';

    public function index()
    {
        try {
            $religions = $this->db->query(
                "SELECT * FROM {$this->table} ORDER BY name ASC"
            );

            ApiResponse::success($religions);
        } catch (\Exception $e) {
            ApiResponse::error("Failed to retrieve religions", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    public function show($id)
    {
        try {
            $religion = $this->db->fetch(
                "SELECT * FROM {$this->table} WHERE id = ?",
                [(int)$id]
            );

            if (!$religion) {
                ApiResponse::error("Religion not found", ApiResponse::HTTP_NOT_FOUND);
            }

            ApiResponse::success($religion);
        } catch (\Exception $e) {
            ApiResponse::error("Failed to retrieve religion", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    public function store()
    {
        try {
            $name = $this->input('name');

            if (empty($name)) {
                ApiResponse::error("Name is required", ApiResponse::HTTP_BAD_REQUEST);
            }

            $existing = $this->db->fetch(
                "SELECT id FROM {$this->table} WHERE name = ?",
                [$name]
            );

            if ($existing) {
                ApiResponse::error("Religion already exists", ApiResponse::HTTP_CONFLICT);
            }

            $id = $this->db->insert($this->table, ['name' => $name]);

            $religion = $this->db->fetch(
                "SELECT * FROM {$this->table} WHERE id = ?",
                [$id]
            );

            ApiResponse::success($religion, ApiResponse::HTTP_CREATED, "Religion created successfully");
        } catch (\Exception $e) {
            ApiResponse::error("Failed to create religion", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    public function update($id)
    {
        try {
            $religion = $this->db->fetch(
                "SELECT id FROM {$this->table} WHERE id = ?",
                [(int)$id]
            );

            if (!$religion) {
                ApiResponse::error("Religion not found", ApiResponse::HTTP_NOT_FOUND);
            }

            $name = $this->input('name');

            if (empty($name)) {
                ApiResponse::error("Name is required", ApiResponse::HTTP_BAD_REQUEST);
            }

            $this->db->update($this->table, ['name' => $name], ['id' => (int)$id]);

            $updated = $this->db->fetch(
                "SELECT * FROM {$this->table} WHERE id = ?",
                [(int)$id]
            );

            ApiResponse::success($updated, ApiResponse::HTTP_OK, "Religion updated successfully");
        } catch (\Exception $e) {
            ApiResponse::error("Failed to update religion", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            $religion = $this->db->fetch(
                "SELECT id FROM {$this->table} WHERE id = ?",
                [(int)$id]
            );

            if (!$religion) {
                ApiResponse::error("Religion not found", ApiResponse::HTTP_NOT_FOUND);
            }

            $this->db->delete($this->table, ['id' => (int)$id]);

            ApiResponse::success([], ApiResponse::HTTP_OK, "Religion deleted successfully");
        } catch (\Exception $e) {
            ApiResponse::error("Failed to delete religion", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }
}

class IndigenousGroup extends BaseController
{
    protected $table = 'indigenous_group';

    public function index()
    {
        try {
            $groups = $this->db->query(
                "SELECT * FROM {$this->table} ORDER BY name ASC"
            );

            ApiResponse::success($groups);
        } catch (\Exception $e) {
            ApiResponse::error("Failed to retrieve indigenous groups", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    public function show($id)
    {
        try {
            $group = $this->db->fetch(
                "SELECT * FROM {$this->table} WHERE id = ?",
                [(int)$id]
            );

            if (!$group) {
                ApiResponse::error("Indigenous group not found", ApiResponse::HTTP_NOT_FOUND);
            }

            ApiResponse::success($group);
        } catch (\Exception $e) {
            ApiResponse::error("Failed to retrieve indigenous group", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    public function store()
    {
        try {
            $name = $this->input('name');

            if (empty($name)) {
                ApiResponse::error("Name is required", ApiResponse::HTTP_BAD_REQUEST);
            }

            $existing = $this->db->fetch(
                "SELECT id FROM {$this->table} WHERE name = ?",
                [$name]
            );

            if ($existing) {
                ApiResponse::error("Indigenous group already exists", ApiResponse::HTTP_CONFLICT);
            }

            $id = $this->db->insert($this->table, ['name' => $name]);

            $group = $this->db->fetch(
                "SELECT * FROM {$this->table} WHERE id = ?",
                [$id]
            );

            ApiResponse::success($group, ApiResponse::HTTP_CREATED, "Indigenous group created successfully");
        } catch (\Exception $e) {
            ApiResponse::error("Failed to create indigenous group", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    public function update($id)
    {
        try {
            $group = $this->db->fetch(
                "SELECT id FROM {$this->table} WHERE id = ?",
                [(int)$id]
            );

            if (!$group) {
                ApiResponse::error("Indigenous group not found", ApiResponse::HTTP_NOT_FOUND);
            }

            $name = $this->input('name');

            if (empty($name)) {
                ApiResponse::error("Name is required", ApiResponse::HTTP_BAD_REQUEST);
            }

            $this->db->update($this->table, ['name' => $name], ['id' => (int)$id]);

            $updated = $this->db->fetch(
                "SELECT * FROM {$this->table} WHERE id = ?",
                [(int)$id]
            );

            ApiResponse::success($updated, ApiResponse::HTTP_OK, "Indigenous group updated successfully");
        } catch (\Exception $e) {
            ApiResponse::error("Failed to update indigenous group", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            $group = $this->db->fetch(
                "SELECT id FROM {$this->table} WHERE id = ?",
                [(int)$id]
            );

            if (!$group) {
                ApiResponse::error("Indigenous group not found", ApiResponse::HTTP_NOT_FOUND);
            }

            $this->db->delete($this->table, ['id' => (int)$id]);

            ApiResponse::success([], ApiResponse::HTTP_OK, "Indigenous group deleted successfully");
        } catch (\Exception $e) {
            ApiResponse::error("Failed to delete indigenous group", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }
}
?>
