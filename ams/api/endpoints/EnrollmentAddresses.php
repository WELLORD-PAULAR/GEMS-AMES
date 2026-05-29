<?php
namespace API;
require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../classes/ApiResponse.php';

class EnrollmentAddresses extends BaseController
{
    protected $table = 'enrollment_address2';

    public function index()
    {
        try {
            $page = max(1, (int)$this->input('page', 1));
            $limit = min(MAX_ITEMS_PER_PAGE, max(1, (int)$this->input('limit', DEFAULT_ITEMS_PER_PAGE)));
            $offset = ($page - 1) * $limit;

            $total = $this->db->fetch("SELECT COUNT(*) as count FROM {$this->table}");

            $addresses = $this->db->query(
                "SELECT * FROM {$this->table} ORDER BY fkromenroll2 DESC LIMIT ? OFFSET ?",
                [$limit, $offset]
            );

            ApiResponse::paginated($addresses, $total['count'], $page, $limit);
        } catch (\Exception $e) {
            ApiResponse::error("Failed to retrieve addresses", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    public function show($id)
    {
        try {
            $address = $this->db->fetch(
                "SELECT * FROM {$this->table} WHERE fk_full_name_bd = ?",
                [$id]
            );

            if (!$address) {
                ApiResponse::error("Address not found", ApiResponse::HTTP_NOT_FOUND);
            }

            ApiResponse::success($address);
        } catch (\Exception $e) {
            ApiResponse::error("Failed to retrieve address", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    public function store()
    {
        try {
            $data = $this->input();
            
            // Verify enrollment exists
            $enrollment = $this->db->fetch(
                "SELECT fk_full_name_bd FROM enrollment2 WHERE fk_full_name_bd = ?",
                [$data['fk_full_name_bd'] ?? null]
            );

            if (!$enrollment) {
                ApiResponse::error("Enrollment not found", ApiResponse::HTTP_NOT_FOUND);
            }

            $insertData = [
                'fk_full_name_bd' => $data['fk_full_name_bd'],
                'ca_house_number' => $data['ca_house_number'] ?? '',
                'ca_street_name' => $data['ca_street_name'] ?? '',
                'ca_barangay' => $data['ca_barangay'] ?? '',
                'ca_municipality' => $data['ca_municipality'] ?? '',
                'ca_provice' => $data['ca_provice'] ?? '',
                'ca_country' => $data['ca_country'] ?? '',
                'ca_zipcode' => (int)($data['ca_zipcode'] ?? 0),
                'ca_address_status' => $data['ca_address_status'] ?? 'Rental',
                'pa_house_number' => $data['pa_house_number'] ?? '',
                'pa_street_name' => $data['pa_street_name'] ?? '',
                'pa_barangay' => $data['pa_barangay'] ?? '',
                'pa_municipality' => $data['pa_municipality'] ?? '',
                'pa_province' => $data['pa_province'] ?? '',
                'pa_country' => $data['pa_country'] ?? '',
                'pa_zip_code' => (int)($data['pa_zip_code'] ?? 0),
                'pa_address_status' => $data['pa_address_status'] ?? 'Rental'
            ];

            $id = $this->db->insert($this->table, $insertData);

            $address = $this->db->fetch(
                "SELECT * FROM {$this->table} WHERE fkromenroll2 = ?",
                [$id]
            );

            ApiResponse::success($address, ApiResponse::HTTP_CREATED, "Address created successfully");
        } catch (\Exception $e) {
            ApiResponse::error("Failed to create address", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    public function update($id)
    {
        try {
            $address = $this->db->fetch(
                "SELECT fkromenroll2 FROM {$this->table} WHERE fk_full_name_bd = ?",
                [$id]
            );

            if (!$address) {
                ApiResponse::error("Address not found", ApiResponse::HTTP_NOT_FOUND);
            }

            $data = $this->input();
            $updateData = [];

            $allowedFields = [
                'ca_house_number', 'ca_street_name', 'ca_barangay', 'ca_municipality',
                'ca_provice', 'ca_country', 'ca_zipcode', 'ca_address_status',
                'pa_house_number', 'pa_street_name', 'pa_barangay', 'pa_municipality',
                'pa_province', 'pa_country', 'pa_zip_code', 'pa_address_status'
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

            ApiResponse::success($updated, ApiResponse::HTTP_OK, "Address updated successfully");
        } catch (\Exception $e) {
            ApiResponse::error("Failed to update address", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            $address = $this->db->fetch(
                "SELECT fkromenroll2 FROM {$this->table} WHERE fk_full_name_bd = ?",
                [$id]
            );

            if (!$address) {
                ApiResponse::error("Address not found", ApiResponse::HTTP_NOT_FOUND);
            }

            $this->db->delete($this->table, ['fk_full_name_bd' => $id]);

            ApiResponse::success([], ApiResponse::HTTP_OK, "Address deleted successfully");
        } catch (\Exception $e) {
            ApiResponse::error("Failed to delete address", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }
}
?>
