<?php

namespace API;

require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../classes/ApiResponse.php';

/**
 * Enrollment Medical Controller
 */

class EnrollmentMedical extends BaseController
{
    protected $table = 'enrollment_medical2';

    public function index()
    {
        try {
            $page = max(1, (int)$this->input('page', 1));
            $limit = min(MAX_ITEMS_PER_PAGE, max(1, (int)$this->input('limit', DEFAULT_ITEMS_PER_PAGE)));
            $offset = ($page - 1) * $limit;

            $total = $this->db->fetch("SELECT COUNT(*) as count FROM {$this->table}");

            $medical = $this->db->query(
                "SELECT * FROM {$this->table} ORDER BY id DESC LIMIT ? OFFSET ?",
                [$limit, $offset]
            );

            ApiResponse::paginated($medical, $total['count'], $page, $limit);
        } catch (\Exception $e) {
            ApiResponse::error("Failed to retrieve medical records", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    public function show($id)
    {
        try {
            $medical = $this->db->fetch(
                "SELECT * FROM {$this->table} WHERE fk_full_name_bd = ?",
                [$id]
            );

            if (!$medical) {
                ApiResponse::error("Medical record not found", ApiResponse::HTTP_NOT_FOUND);
            }

            ApiResponse::success($medical);
        } catch (\Exception $e) {
            ApiResponse::error("Failed to retrieve medical record", ApiResponse::HTTP_INTERNAL_ERROR);
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
                'mf_a_medicine' => $data['mf_a_medicine'] ?? '',
                'mf_a_pollen' => $data['mf_a_pollen'] ?? '',
                'mf_a_food' => $data['mf_a_food'] ?? '',
                'mf_a_others' => $data['mf_a_others'] ?? '',
                'mf_o_medical_conditions' => $data['mf_o_medical_conditions'] ?? '',
                'mf_o_others' => $data['mf_o_others'] ?? '',
                'mf_sh_surgery_date' => $data['mf_sh_surgery_date'] ?? date('Y-m-d'),
                'mf_sh_hospital_name' => $data['mf_sh_hospital_name'] ?? '',
                'mf_sh_bodypart_affected' => $data['mf_sh_bodypart_affected'] ?? '',
                'mf_tm_type' => $data['mf_tm_type'] ?? '',
                'mf_tm_dosage_schedule' => $data['mf_tm_dosage_schedule'] ?? '',
                'mf_mc_conditions' => $data['mf_mc_conditions'] ?? '',
                'mf_mc_cancer_type' => $data['mf_mc_cancer_type'] ?? '',
                'mf_mc_others' => $data['mf_mc_others'] ?? '',
                'mf_exposure_c_v' => (int)($data['mf_exposure_c_v'] ?? 0),
                'mf_o_pertinent_information' => $data['mf_o_pertinent_information'] ?? ''
            ];

            $id = $this->db->insert($this->table, $insertData);

            $medical = $this->db->fetch(
                "SELECT * FROM {$this->table} WHERE id = ?",
                [$id]
            );

            ApiResponse::success($medical, ApiResponse::HTTP_CREATED, "Medical record created successfully");
        } catch (\Exception $e) {
            ApiResponse::error("Failed to create medical record", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    public function update($id)
    {
        try {
            $medical = $this->db->fetch(
                "SELECT id FROM {$this->table} WHERE fk_full_name_bd = ?",
                [$id]
            );

            if (!$medical) {
                ApiResponse::error("Medical record not found", ApiResponse::HTTP_NOT_FOUND);
            }

            $data = $this->input();
            $updateData = [];

            $allowedFields = [
                'mf_a_medicine', 'mf_a_pollen', 'mf_a_food', 'mf_a_others',
                'mf_o_medical_conditions', 'mf_o_others', 'mf_sh_surgery_date',
                'mf_sh_hospital_name', 'mf_sh_bodypart_affected', 'mf_tm_type',
                'mf_tm_dosage_schedule', 'mf_mc_conditions', 'mf_mc_cancer_type',
                'mf_mc_others', 'mf_exposure_c_v', 'mf_o_pertinent_information'
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

            ApiResponse::success($updated, ApiResponse::HTTP_OK, "Medical record updated successfully");
        } catch (\Exception $e) {
            ApiResponse::error("Failed to update medical record", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            $medical = $this->db->fetch(
                "SELECT id FROM {$this->table} WHERE fk_full_name_bd = ?",
                [$id]
            );

            if (!$medical) {
                ApiResponse::error("Medical record not found", ApiResponse::HTTP_NOT_FOUND);
            }

            $this->db->delete($this->table, ['fk_full_name_bd' => $id]);

            ApiResponse::success([], ApiResponse::HTTP_OK, "Medical record deleted successfully");
        } catch (\Exception $e) {
            ApiResponse::error("Failed to delete medical record", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }
}
?>
