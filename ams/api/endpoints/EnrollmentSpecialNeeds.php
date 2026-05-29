<?php
namespace API;
require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../classes/ApiResponse.php';

class EnrollmentSpecialNeeds extends BaseController
{
    protected $table = 'enrollment_special_needs2';

    public function index()
    {
        try {
            $page = max(1, (int)$this->input('page', 1));
            $limit = min(MAX_ITEMS_PER_PAGE, max(1, (int)$this->input('limit', DEFAULT_ITEMS_PER_PAGE)));
            $offset = ($page - 1) * $limit;

            $total = $this->db->fetch("SELECT COUNT(*) as count FROM {$this->table}");

            $specialNeeds = $this->db->query(
                "SELECT * FROM {$this->table} ORDER BY id DESC LIMIT ? OFFSET ?",
                [$limit, $offset]
            );

            ApiResponse::paginated($specialNeeds, $total['count'], $page, $limit);
        } catch (\Exception $e) {
            ApiResponse::error("Failed to retrieve special needs records", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    public function show($id)
    {
        try {
            $specialNeeds = $this->db->fetch(
                "SELECT * FROM {$this->table} WHERE fk_full_name_bd = ?",
                [$id]
            );

            if (!$specialNeeds) {
                ApiResponse::error("Special needs record not found", ApiResponse::HTTP_NOT_FOUND);
            }

            ApiResponse::success($specialNeeds);
        } catch (\Exception $e) {
            ApiResponse::error("Failed to retrieve special needs record", ApiResponse::HTTP_INTERNAL_ERROR);
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
                'snep_a1_diagnosis' => $data['snep_a1_diagnosis'] ?? '',
                'snep_a1_sub_shpcd' => $data['snep_a1_sub_shpcd'] ?? 'CANCER',
                'snep_a1_sub_vi' => $data['snep_a1_sub_vi'] ?? 'BLIND',
                'snep_a2_manifestations' => $data['snep_a2_manifestations'] ?? 'DIFFICULTY IN SEEING',
                'snep_pwd_id' => (int)($data['snep_pwd_id'] ?? 0)
            ];

            $id = $this->db->insert($this->table, $insertData);

            $specialNeeds = $this->db->fetch(
                "SELECT * FROM {$this->table} WHERE id = ?",
                [$id]
            );

            ApiResponse::success($specialNeeds, ApiResponse::HTTP_CREATED, "Special needs record created successfully");
        } catch (\Exception $e) {
            ApiResponse::error("Failed to create special needs record", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    public function update($id)
    {
        try {
            $specialNeeds = $this->db->fetch(
                "SELECT id FROM {$this->table} WHERE fk_full_name_bd = ?",
                [$id]
            );

            if (!$specialNeeds) {
                ApiResponse::error("Special needs record not found", ApiResponse::HTTP_NOT_FOUND);
            }

            $data = $this->input();
            $updateData = [];

            $allowedFields = [
                'snep_a1_diagnosis', 'snep_a1_sub_shpcd', 'snep_a1_sub_vi',
                'snep_a2_manifestations', 'snep_pwd_id'
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

            $updated = $this->db->fetch(
                "SELECT * FROM {$this->table} WHERE fk_full_name_bd = ?",
                [$id]
            );

            ApiResponse::success($updated, ApiResponse::HTTP_OK, "Special needs record updated successfully");
        } catch (\Exception $e) {
            ApiResponse::error("Failed to update special needs record", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            $specialNeeds = $this->db->fetch(
                "SELECT id FROM {$this->table} WHERE fk_full_name_bd = ?",
                [$id]
            );

            if (!$specialNeeds) {
                ApiResponse::error("Special needs record not found", ApiResponse::HTTP_NOT_FOUND);
            }

            $this->db->delete($this->table, ['fk_full_name_bd' => $id]);

            ApiResponse::success([], ApiResponse::HTTP_OK, "Special needs record deleted successfully");
        } catch (\Exception $e) {
            ApiResponse::error("Failed to delete special needs record", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }
}
?>
