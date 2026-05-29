<?php

namespace API;

require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../classes/ApiResponse.php';

/**
 * Enrollments Controller
 * Handles enrollment data CRUD operations
 */

class Enrollments extends BaseController
{
    protected $table = 'enrollment2';

    /**
     * GET /enrollments - List all enrollments with pagination and filters
     */
    public function index()
    {
        try {
            $page = max(1, (int)$this->input('page', 1));
            $limit = min(MAX_ITEMS_PER_PAGE, max(1, (int)$this->input('limit', DEFAULT_ITEMS_PER_PAGE)));
            $offset = ($page - 1) * $limit;

            // Build query with filters
            $query = "SELECT * FROM {$this->table} WHERE 1=1";
            $params = [];

            // Filter by grade level
            if ($this->input('grade_level')) {
                $query .= " AND ed_grade_level = ?";
                $params[] = $this->input('grade_level');
            }

            // Filter by school year
            if ($this->input('school_year')) {
                $query .= " AND ed_school_year = ?";
                $params[] = $this->input('school_year');
            }

            // Filter by user (parent/student)
            if ($this->input('user_id')) {
                $query .= " AND user_account_id = ?";
                $params[] = (int)$this->input('user_id');
            }

            // Get total count
            $countQuery = str_replace('SELECT *', 'SELECT COUNT(*) as count', $query);
            $total = $this->db->fetch($countQuery, $params);

            // Add pagination and sort
            $query .= " ORDER BY ed_school_year DESC, fk_full_name_bd ASC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $enrollments = $this->db->query($query, $params);

            ApiResponse::paginated($enrollments, $total['count'], $page, $limit);
        } catch (\Exception $e) {
            ApiResponse::error("Failed to retrieve enrollments", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * GET /enrollments/{id} - Get a specific enrollment with related data
     */
    public function show($id)
    {
        try {
            $enrollment = $this->db->fetch(
                "SELECT e.*, 
                        mt.name as mother_tongue_name,
                        r.name as religion_name,
                        ig.name as indigenous_group_name
                 FROM {$this->table} e
                 LEFT JOIN mother_tongue mt ON e.pi_mother_tongue_id = mt.id
                 LEFT JOIN religion r ON e.pi_religion_id = r.id
                 LEFT JOIN indigenous_group ig ON e.ac_indigenous_group_id = ig.id
                 WHERE e.fk_full_name_bd = ?",
                [$id]
            );

            if (!$enrollment) {
                ApiResponse::error("Enrollment not found", ApiResponse::HTTP_NOT_FOUND);
            }

            // Get related address data
            $address = $this->db->fetch(
                "SELECT * FROM enrollment_address2 WHERE fk_full_name_bd = ?",
                [$id]
            );

            // Get related medical data
            $medical = $this->db->fetch(
                "SELECT * FROM enrollment_medical2 WHERE fk_full_name_bd = ?",
                [$id]
            );

            // Get related parent data
            $parents = $this->db->query(
                "SELECT * FROM enrollment_parent2 WHERE fk_full_name_bd = ?",
                [$id]
            );

            // Get related special needs data
            $specialNeeds = $this->db->fetch(
                "SELECT * FROM enrollment_special_needs2 WHERE fk_full_name_bd = ?",
                [$id]
            );

            $enrollment['address'] = $address;
            $enrollment['medical'] = $medical;
            $enrollment['parents'] = $parents;
            $enrollment['special_needs'] = $specialNeeds;

            ApiResponse::success($enrollment);
        } catch (\Exception $e) {
            ApiResponse::error("Failed to retrieve enrollment", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * POST /enrollments - Create a new enrollment
     */
    public function store()
    {
        try {
            $data = $this->input();

            // Validate required fields
            $required = [
                'fk_full_name_bd', 'ed_grade_level', 'ed_lrn', 
                'ed_school_year', 'pi_last_name', 'pi_first_name'
            ];

            $errors = [];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = "$field is required";
                }
            }

            if (!empty($data['ed_lrn']) && !preg_match('/^[0-9]+$/', $data['ed_lrn'])) {
                $errors['ed_lrn'] = 'ed_lrn must be a numeric value';
            }

            if (isset($data['user_account_id']) && $data['user_account_id'] !== '' && (!is_numeric($data['user_account_id']) || (int)$data['user_account_id'] <= 0)) {
                $errors['user_account_id'] = 'user_account_id must be a positive integer';
            }

            if (!empty($errors)) {
                ApiResponse::error("Validation failed", 422, $errors);
            }

            if (isset($data['user_account_id']) && $data['user_account_id'] !== '') {
                $userAccount = $this->db->fetch(
                    "SELECT id FROM user_account WHERE id = ?",
                    [(int)$data['user_account_id']]
                );

                if (!$userAccount) {
                    ApiResponse::error("Validation failed", 422, [
                        'user_account_id' => 'User account does not exist'
                    ]);
                }
            }

            // Check if enrollment already exists
            $existing = $this->db->fetch(
                "SELECT fk_full_name_bd FROM {$this->table} WHERE fk_full_name_bd = ?",
                [$data['fk_full_name_bd']]
            );

            if ($existing) {
                ApiResponse::error("Enrollment already exists", ApiResponse::HTTP_CONFLICT);
            }

            // Prepare data for insertion
            $insertData = [
                'fk_full_name_bd' => $data['fk_full_name_bd'],
                'ed_grade_level' => $data['ed_grade_level'],
                'ed_lrn' => (int)$data['ed_lrn'],
                'ed_school_year' => $data['ed_school_year'],
                'rl_last_grade_level_completed' => $data['rl_last_grade_level_completed'] ?? '',
                'rl_last_school_year_completed' => $data['rl_last_school_year_completed'] ?? '',
                'rl_school_attended' => $data['rl_school_attended'] ?? '',
                'rl_school_id' => (int)($data['rl_school_id'] ?? 0),
                'pi_psa_bcn' => (int)($data['pi_psa_bcn'] ?? 0),
                'pi_last_name' => $data['pi_last_name'],
                'pi_first_name' => $data['pi_first_name'],
                'pi_middle_name' => $data['pi_middle_name'] ?? '',
                'pi_extension' => $data['pi_extension'] ?? '',
                'pi_birth_date' => $data['pi_birth_date'] ?? date('Y-m-d'),
                'pi_sex' => $data['pi_sex'] ?? 'MALE',
                'pi_place_of_birth' => $data['pi_place_of_birth'] ?? '',
                'pi_mother_tongue_id' => (int)($data['pi_mother_tongue_id'] ?? 1),
                'pi_religion_id' => (int)($data['pi_religion_id'] ?? 1),
                'pi__attended_early_learning_program_name' => $data['pi__attended_early_learning_program_name'] ?? '',
                'pi_learning_classification' => $data['pi_learning_classification'] ?? 'GRADED',
                'ac_indigenous_group_id' => (int)($data['ac_indigenous_group_id'] ?? 1),
                'ac_4ps_household_number' => $data['ac_4ps_household_number'] ?? '',
                'user_account_id' => isset($data['user_account_id']) && $data['user_account_id'] !== '' ? (int)$data['user_account_id'] : null,
                'li_learning_modality' => $data['li_learning_modality'] ?? 'BLENDED (COMBINATION)'
            ];

            $this->db->insert($this->table, $insertData);

            $enrollment = $this->db->fetch(
                "SELECT * FROM {$this->table} WHERE fk_full_name_bd = ?",
                [$data['fk_full_name_bd']]
            );

            ApiResponse::success($enrollment, ApiResponse::HTTP_CREATED, "Enrollment created successfully");
        } catch (\Exception $e) {
            ApiResponse::error("Failed to create enrollment", ApiResponse::HTTP_INTERNAL_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * PUT /enrollments/{id} - Update an enrollment
     */
    public function update($id)
    {
        try {
            $existing = $this->db->fetch(
                "SELECT fk_full_name_bd FROM {$this->table} WHERE fk_full_name_bd = ?",
                [$id]
            );

            if (!$existing) {
                ApiResponse::error("Enrollment not found", ApiResponse::HTTP_NOT_FOUND);
            }

            $data = $this->input();
            $updateData = [];

            // Only update provided fields
            $allowedFields = [
                'ed_grade_level', 'ed_lrn', 'ed_school_year',
                'rl_last_grade_level_completed', 'rl_last_school_year_completed',
                'rl_school_attended', 'rl_school_id', 'pi_psa_bcn',
                'pi_last_name', 'pi_first_name', 'pi_middle_name', 'pi_extension',
                'pi_birth_date', 'pi_sex', 'pi_place_of_birth',
                'pi_mother_tongue_id', 'pi_religion_id', 'pi__attended_early_learning_program_name',
                'pi_learning_classification', 'ac_indigenous_group_id', 'ac_4ps_household_number',
                'user_account_id', 'li_learning_modality'
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

            $enrollment = $this->db->fetch(
                "SELECT * FROM {$this->table} WHERE fk_full_name_bd = ?",
                [$id]
            );

            ApiResponse::success($enrollment, ApiResponse::HTTP_OK, "Enrollment updated successfully");
        } catch (\Exception $e) {
            ApiResponse::error("Failed to update enrollment", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * DELETE /enrollments/{id} - Delete an enrollment
     */
    public function destroy($id)
    {
        try {
            $existing = $this->db->fetch(
                "SELECT fk_full_name_bd FROM {$this->table} WHERE fk_full_name_bd = ?",
                [$id]
            );

            if (!$existing) {
                ApiResponse::error("Enrollment not found", ApiResponse::HTTP_NOT_FOUND);
            }

            $this->db->delete($this->table, ['fk_full_name_bd' => $id]);

            ApiResponse::success([], ApiResponse::HTTP_OK, "Enrollment deleted successfully");
        } catch (\Exception $e) {
            ApiResponse::error("Failed to delete enrollment", ApiResponse::HTTP_INTERNAL_ERROR);
        }
    }
}
?>
