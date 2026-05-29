<?php

namespace AMS\Handlers;

use AMS\Database;
use AMS\Models\Enrollment;
use AMS\Models\EnrollmentAddress;
use AMS\Models\EnrollmentMedical;
use AMS\Models\EnrollmentParents;
use AMS\Models\EnrollmentSpecialNeeds;

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/models/Enrollment.php';
require_once __DIR__ . '/../classes/models/EnrollmentAddress.php';
require_once __DIR__ . '/../classes/models/EnrollmentMedical.php';
require_once __DIR__ . '/../classes/models/EnrollmentParents.php';
require_once __DIR__ . '/../classes/models/EnrollmentSpecialNeeds.php';

class EnrollmentHandler
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function handle(array $data): array
    {
        try {
            $this->db->beginTransaction();

            $enrollment = (new Enrollment($this->db))->fill($this->mapEnrollment($data));
            $enrollment->save();

            $enrollmentId = $enrollment->id;

            $this->saveSection(EnrollmentAddress::class, $this->mapAddress($data), $enrollmentId);
            $this->saveSection(EnrollmentMedical::class, $this->mapMedical($data), $enrollmentId);
            $this->saveSection(EnrollmentParents::class, $this->mapParents($data), $enrollmentId);
            $this->saveSection(EnrollmentSpecialNeeds::class, $this->mapSpecialNeeds($data), $enrollmentId);

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Enrollment submitted successfully',
                'enrollment_id' => $enrollmentId
            ];
        } catch (\Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => 'Enrollment error: ' . $e->getMessage()
            ];
        }
    }

    private function saveSection(string $class, array $data, $enrollmentId): void
    {
        if (empty($data)) {
            return;
        }

        $model = new $class($this->db);
        $data['enrollment_id'] = $enrollmentId;
        $model->fill($data);
        $model->save();
    }

    private function mapEnrollment(array $data): array
    {
        return array_filter([ 
            'fk_full_name_bd' => $data['fk_full_name_bd'] ?? null,
            'ed_grade_level' => $data['ed_grade_level'] ?? null,
            'ed_lrn' => $data['ed_lrn'] ?? null,
            'ed_school_year' => $data['ed_school_year'] ?? null,
            'rl_last_grade_level_completed' => $data['rl_last_grade_level_completed'] ?? null,
            'rl_last_school_year_completed' => $data['rl_last_school_year_completed'] ?? null,
            'rl_school_attended' => $data['rl_school_attended'] ?? null,
            'rl_school_id' => $data['rl_school_id'] ?? null,
            'user_account_id' => $data['user_account_id'] ?? null,
            'li_learning_modality' => $data['li_learning_modality'] ?? null,
        ], fn($value) => $value !== null && $value !== '');
    }

    private function mapAddress(array $data): array
    {
        return array_filter([
            'ac_street' => $data['ac_street'] ?? null,
            'ac_barangay' => $data['ac_barangay'] ?? null,
            'ac_city' => $data['ac_city'] ?? null,
            'ac_province' => $data['ac_province'] ?? null,
            'ac_zip_code' => $data['ac_zip_code'] ?? null,
        ], fn($value) => $value !== null && $value !== '');
    }

    private function mapMedical(array $data): array
    {
        return array_filter([
            'md_allergies' => $data['md_allergies'] ?? null,
            'md_existing_conditions' => $data['md_existing_conditions'] ?? null,
            'md_medication' => $data['md_medication'] ?? null,
            'md_doctor_name' => $data['md_doctor_name'] ?? null,
            'md_doctor_phone' => $data['md_doctor_phone'] ?? null,
        ], fn($value) => $value !== null && $value !== '');
    }

    private function mapParents(array $data): array
    {
        return array_filter([
            'pr_father_name' => $data['pr_father_name'] ?? null,
            'pr_mother_name' => $data['pr_mother_name'] ?? null,
            'pr_guardian_name' => $data['pr_guardian_name'] ?? null,
            'pr_contact_number' => $data['pr_contact_number'] ?? null,
            'pr_email' => $data['pr_email'] ?? null,
        ], fn($value) => $value !== null && $value !== '');
    }

    private function mapSpecialNeeds(array $data): array
    {
        return array_filter([
            'sn_needs_description' => $data['sn_needs_description'] ?? null,
            'sn_accommodations' => $data['sn_accommodations'] ?? null,
        ], fn($value) => $value !== null && $value !== '');
    }
}
