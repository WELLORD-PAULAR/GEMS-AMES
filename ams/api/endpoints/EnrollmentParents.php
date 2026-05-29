<?php
namespace API;
require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../classes/ApiResponse.php';


class EnrollmentParents extends BaseController
{
    protected $table = 'enrollment_parent2';

    public function index()
    {
        try {
            $page = max(1, (int)$this->input('page', 1));
            $limit = min(MAX_ITEMS_PER_PAGE, max(1, (int)$this->input('limit', DEFAULT_ITEMS_PER_PAGE)));
            $offset = ($page - 1) * $limit;

            $total = $this->db->fetch("SELECT COUNT(*) as count FROM {$this->table}");

            $parents = $this->db->query(
                "SELECT * FROM {$this->table} ORDER BY id DESC LIMIT ? OFFSET ?",
                [$limit, $offset]
            );

            ApiResponse::paginated($parents, $total['count'], $page, $limit);
        } catch (\Exception $e) {
            ApiResponse::error("Failed to retrieve parent records", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    public function show($id)
    {
        try {
            $parents = $this->db->query(
                "SELECT * FROM {$this->table} WHERE fk_full_name_bd = ?",
                [$id]
            );

            if (empty($parents)) {
                ApiResponse::error("Parent records not found", ApiResponse::HTTP_NOT_FOUND);
            }

            ApiResponse::success($parents);
        } catch (\Exception $e) {
            ApiResponse::error("Failed to retrieve parent records", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    public function store()
    {
        try {
            $data = $this->input();
            
            $enrollment = $this->db->fetch(
                "SELECT fk_full_name_bd FROM enrollment2 WHERE fk_full_name_bd = ?",
                [$data['fk_full_name_bd'] ?? null]
            );

            if (!$enrollment) {
                ApiResponse::error("Enrollment not found", ApiResponse::HTTP_NOT_FOUND);
            }

            $insertData = [
                'fk_full_name_bd' => $data['fk_full_name_bd'],
                'fi_last_name' => $data['fi_last_name'] ?? '',
                'fi_first_name' => $data['fi_first_name'] ?? '',
                'fi_middle_name' => $data['fi_middle_name'] ?? '',
                'fi_contact_number' => $data['fi_contact_number'] ?? '',
                'fi_occupation' => $data['fi_occupation'] ?? '',
                'fi_relationship_status' => $data['fi_relationship_status'] ?? '',
                'fi_communication' => $data['fi_communication'] ?? '',
                'mi_last_name' => $data['mi_last_name'] ?? '',
                'mi_first_name' => $data['mi_first_name'] ?? '',
                'mi_middle_name' => $data['mi_middle_name'] ?? '',
                'mi_contact_number' => $data['mi_contact_number'] ?? '',
                'mi_occupation' => $data['mi_occupation'] ?? '',
                'mi_relationship_status' => $data['mi_relationship_status'] ?? '',
                'mi_communication' => $data['mi_communication'] ?? '',
                'gi_last_name' => $data['gi_last_name'] ?? '',
                'gi_first_name' => $data['gi_first_name'] ?? '',
                'gi_middle_name' => $data['gi_middle_name'] ?? '',
                'gi_contact_number' => $data['gi_contact_number'] ?? '',
                'gi_occupation' => $data['gi_occupation'] ?? '',
                'gi_relationship_status' => $data['gi_relationship_status'] ?? '',
                'gi_communication' => $data['gi_communication'] ?? '',
                'ec_to_contact' => $data['ec_to_contact'] ?? 'FATHER'
            ];

            $id = $this->db->insert($this->table, $insertData);

            $parent = $this->db->fetch(
                "SELECT * FROM {$this->table} WHERE id = ?",
                [$id]
            );

            ApiResponse::success($parent, ApiResponse::HTTP_CREATED, "Parent record created successfully");
        } catch (\Exception $e) {
            ApiResponse::error("Failed to create parent record", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    public function update($id)
    {
        try {
            $parent = $this->db->fetch(
                "SELECT id FROM {$this->table} WHERE fk_full_name_bd = ?",
                [$id]
            );

            if (!$parent) {
                ApiResponse::error("Parent record not found", ApiResponse::HTTP_NOT_FOUND);
            }

            $data = $this->input();
            $updateData = [];

            $allowedFields = [
                'fi_last_name', 'fi_first_name', 'fi_middle_name', 'fi_contact_number',
                'fi_occupation', 'fi_relationship_status', 'fi_communication',
                'mi_last_name', 'mi_first_name', 'mi_middle_name', 'mi_contact_number',
                'mi_occupation', 'mi_relationship_status', 'mi_communication',
                'gi_last_name', 'gi_first_name', 'gi_middle_name', 'gi_contact_number',
                'gi_occupation', 'gi_relationship_status', 'gi_communication', 'ec_to_contact'
            ];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }

            if (empty($updateData)) {
                ApiResponse::error("No data to update", ApiResponse::HTTP_BAD_REQUEST);
            }

            $this->db->update($this->table, $updateData, ['fk_full_name_bd' => $id]);

            $updated = $this->db->query(
                "SELECT * FROM {$this->table} WHERE fk_full_name_bd = ?",
                [$id]
            );

            ApiResponse::success($updated, ApiResponse::HTTP_OK, "Parent records updated successfully");
        } catch (\Exception $e) {
            ApiResponse::error("Failed to update parent record", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            $parent = $this->db->fetch(
                "SELECT id FROM {$this->table} WHERE fk_full_name_bd = ?",
                [$id]
            );

            if (!$parent) {
                ApiResponse::error("Parent record not found", ApiResponse::HTTP_NOT_FOUND);
            }

            $this->db->delete($this->table, ['fk_full_name_bd' => $id]);

            ApiResponse::success([], ApiResponse::HTTP_OK, "Parent records deleted successfully");
        } catch (\Exception $e) {
            ApiResponse::error("Failed to delete parent record", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }
}
?>
