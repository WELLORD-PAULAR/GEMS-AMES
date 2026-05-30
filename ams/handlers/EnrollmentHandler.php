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

            $mappedData = $this->mapEnrollment($data);
            $fkFullNameBd = $this->generatePrimaryKey($data);
            $mappedData['fk_full_name_bd'] = $fkFullNameBd;

            $enrollment = (new Enrollment($this->db))->fill($mappedData);
            $enrollment->save();

            $addressData = $this->mapAddress($data);
            if (!empty($addressData)) {
                $addressData['fk_full_name_bd'] = $fkFullNameBd;
                $address = (new EnrollmentAddress($this->db))->fill($addressData);
                $address->save();
            }

            $medicalData = $this->mapMedical($data);
            if (!empty($medicalData)) {
                $medicalData['fk_full_name_bd'] = $fkFullNameBd;
                $medical = (new EnrollmentMedical($this->db))->fill($medicalData);
                $medical->save();
            }

            $parentsData = $this->mapParents($data);
            if (!empty($parentsData)) {
                $parentsData['fk_full_name_bd'] = $fkFullNameBd;
                $parents = (new EnrollmentParents($this->db))->fill($parentsData);
                $parents->save();
            }

            $specialNeedsData = $this->mapSpecialNeeds($data);
            if (!empty($specialNeedsData)) {
                $specialNeedsData['fk_full_name_bd'] = $fkFullNameBd;
                $specialNeeds = (new EnrollmentSpecialNeeds($this->db))->fill($specialNeedsData);
                $specialNeeds->save();
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Enrollment submitted successfully',
                'enrollment_id' => $fkFullNameBd
            ];
        } catch (\Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => 'Enrollment error: ' . $e->getMessage()
            ];
        }
    }

    private function generatePrimaryKey(array $data): string
    {
        $firstName = strtoupper(str_replace(' ', '', $data['pi_first_name'] ?? ''));
        $middleName = strtoupper(str_replace(' ', '', $data['pi_middle_name'] ?? ''));
        $lastName = strtoupper(str_replace(' ', '', $data['pi_last_name'] ?? ''));
        $birthDate = str_replace('-', '', $data['pi_birth_date'] ?? '');

        return "{$firstName}_{$middleName}_{$lastName}_{$birthDate}";
    }

    private function mapEnrollment(array $data): array
    {
        return array_filter([ 
            'ed_grade_level' => $data['ed_grade_level'] ?? null,
            'ed_lrn' => $data['ed_lrn'] ?? null,
            'ed_school_year' => $data['ed_school_year'] ?? null,
            'rl_last_grade_level_completed' => $data['rl_last_grade_level_completed'] ?? null,
            'rl_last_school_year_completed' => $data['rl_last_school_year_completed'] ?? null,
            'rl_school_attended' => $data['rl_school_attended'] ?? null,
            'rl_school_id' => $data['rl_school_id'] ?? null,
            'pi_psa_bcn' => $data['pi_psa_bcn'] ?? null,
            'pi_last_name' => $data['pi_last_name'] ?? null,
            'pi_first_name' => $data['pi_first_name'] ?? null,
            'pi_middle_name' => $data['pi_middle_name'] ?? null,
            'pi_extension' => $data['pi_extension'] ?? null,
            'pi_birth_date' => $data['pi_birth_date'] ?? null,
            'pi_sex' => $data['pi_sex'] ?? null,
            'pi_place_of_birth' => $data['pi_place_of_birth'] ?? null,
            // these DB columns are NOT NULL in the schema; provide sensible
            // defaults to avoid insert errors if the client omitted them
            'pi_mother_tongue_id' => $data['pi_mother_tongue_id'] ?? 1,
            'pi_religion_id' => $data['pi_religion_id'] ?? 1,
            'pi__attended_early_learning_program_name' => $data['pi__attended_early_learning_program_name'] ?? null,
            'pi_learning_classification' => $data['pi_learning_classification'] ?? null,
            'ac_indigenous_group_id' => $data['ac_indigenous_group_id'] ?? null,
            'ac_4ps_household_number' => $data['ac_4ps_household_number'] ?? null,
            'user_account_id' => $data['user_account_id'] ?? null,
            'li_learning_modality' => $data['li_learning_modality'] ?? null,
            // enrollment verification status must be present (DB has no default)
            'verification' => $data['verification'] ?? 'PROCESSING',
        ], fn($value) => $value !== null && $value !== '');
    }

    private function mapAddress(array $data): array
    {
        // Address table requires many NOT NULL columns; when any address
        // information is provided, fill missing fields with safe defaults
        $defaults = [
            'ca_house_number' => '',
            'ca_street_name' => '',
            'ca_barangay' => '',
            'ca_municipality' => '',
            'ca_provice' => '',
            'ca_country' => 'Philippines',
            'ca_zipcode' => 0,
            'ca_address_status' => 'Owned',
            'pa_house_number' => '',
            'pa_street_name' => '',
            'pa_barangay' => '',
            'pa_municipality' => '',
            'pa_province' => '',
            'pa_country' => 'Philippines',
            'pa_zip_code' => 0,
            'pa_address_status' => 'Owned',
        ];

        $payload = array_merge($defaults, [
            'ca_house_number' => $data['ca_house_number'] ?? $defaults['ca_house_number'],
            'ca_street_name' => $data['ca_street_name'] ?? $defaults['ca_street_name'],
            'ca_barangay' => $data['ca_barangay'] ?? $defaults['ca_barangay'],
            'ca_municipality' => $data['ca_municipality'] ?? $defaults['ca_municipality'],
            'ca_provice' => $data['ca_provice'] ?? $defaults['ca_provice'],
            'ca_country' => $data['ca_country'] ?? $defaults['ca_country'],
            'ca_zipcode' => $data['ca_zipcode'] ?? $defaults['ca_zipcode'],
            'ca_address_status' => $data['ca_address_status'] ?? $defaults['ca_address_status'],
            'pa_house_number' => $data['pa_house_number'] ?? $defaults['pa_house_number'],
            'pa_street_name' => $data['pa_street_name'] ?? $defaults['pa_street_name'],
            'pa_barangay' => $data['pa_barangay'] ?? $defaults['pa_barangay'],
            'pa_municipality' => $data['pa_municipality'] ?? $defaults['pa_municipality'],
            'pa_province' => $data['pa_province'] ?? $defaults['pa_province'],
            'pa_country' => $data['pa_country'] ?? $defaults['pa_country'],
            'pa_zip_code' => $data['pa_zip_code'] ?? $defaults['pa_zip_code'],
            'pa_address_status' => $data['pa_address_status'] ?? $defaults['pa_address_status'],
        ]);

        // Only insert address if at least one non-default value was provided
        $isProvided = false;
        foreach ($data as $k => $v) {
            if (str_starts_with($k, 'ca_') || str_starts_with($k, 'pa_')) {
                if ($v !== null && $v !== '') {
                    $isProvided = true;
                    break;
                }
            }
        }

        return $isProvided ? $payload : [];
    }

    private function mapMedical(array $data): array
    {
        return array_filter([
            'mf_a_medicine' => $data['mf_a_medicine'] ?? null,
            'mf_a_pollen' => $data['mf_a_pollen'] ?? null,
            'mf_a_food' => $data['mf_a_food'] ?? null,
            'mf_a_others' => $data['mf_a_others'] ?? null,
            'mf_o_medical_conditions' => $data['mf_o_medical_conditions'] ?? null,
            'mf_o_others' => $data['mf_o_others'] ?? null,
            'mf_sh_surgery_date' => $data['mf_sh_surgery_date'] ?? null,
            'mf_sh_hospital_name' => $data['mf_sh_hospital_name'] ?? null,
            'mf_sh_bodypart_affected' => $data['mf_sh_bodypart_affected'] ?? null,
            'mf_tm_type' => $data['mf_tm_type'] ?? null,
            'mf_tm_dosage_schedule' => $data['mf_tm_dosage_schedule'] ?? null,
            'mf_mc_conditions' => $data['mf_mc_conditions'] ?? null,
            'mf_mc_cancer_type' => $data['mf_mc_cancer_type'] ?? null,
            'mf_mc_others' => $data['mf_mc_others'] ?? null,
            'mf_exposure_c_v' => $data['mf_exposure_c_v'] ?? null,
            'mf_o_pertinent_information' => $data['mf_o_pertinent_information'] ?? null,
        ], fn($value) => $value !== null && $value !== '');
    }

    private function mapParents(array $data): array
    {
        return array_filter([
            'fi_last_name' => $data['fi_last_name'] ?? null,
            'fi_first_name' => $data['fi_first_name'] ?? null,
            'fi_middle_name' => $data['fi_middle_name'] ?? null,
            'fi_contact_number' => $data['fi_contact_number'] ?? null,
            'fi_occupation' => $data['fi_occupation'] ?? null,
            'fi_relationship_status' => $data['fi_relationship_status'] ?? null,
            'fi_communication' => $data['fi_communication'] ?? null,
            'mi_last_name' => $data['mi_last_name'] ?? null,
            'mi_first_name' => $data['mi_first_name'] ?? null,
            'mi_middle_name' => $data['mi_middle_name'] ?? null,
            'mi_contact_number' => $data['mi_contact_number'] ?? null,
            'mi_occupation' => $data['mi_occupation'] ?? null,
            'mi_relationship_status' => $data['mi_relationship_status'] ?? null,
            'mi_communication' => $data['mi_communication'] ?? null,
            'gi_last_name' => $data['gi_last_name'] ?? null,
            'gi_first_name' => $data['gi_first_name'] ?? null,
            'gi_middle_name' => $data['gi_middle_name'] ?? null,
            'gi_contact_number' => $data['gi_contact_number'] ?? null,
            'gi_occupation' => $data['gi_occupation'] ?? null,
            'gi_relationship_status' => $data['gi_relationship_status'] ?? null,
            'gi_communication' => $data['gi_communication'] ?? null,
            'ec_to_contact' => $data['ec_to_contact'] ?? null,
        ], fn($value) => $value !== null && $value !== '');
    }

    private function mapSpecialNeeds(array $data): array
    {
        return array_filter([
            'snep_a1_diagnosis' => $data['snep_a1_diagnosis'] ?? null,
            'snep_a1_sub_shpcd' => $data['snep_a1_sub_shpcd'] ?? null,
            'snep_a1_sub_vi' => $data['snep_a1_sub_vi'] ?? null,
            'snep_a2_manifestations' => $data['snep_a2_manifestations'] ?? null,
            'snep_pwd_id' => $data['snep_pwd_id'] ?? null,
        ], fn($value) => $value !== null && $value !== '');
    }
}
