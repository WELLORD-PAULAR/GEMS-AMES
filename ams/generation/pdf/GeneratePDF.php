<?php
namespace Classes;

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

class GeneratePDF
{
    /**
     * Generate PDF from merged enrollment data array.
     * $data should contain keys from all 5 tables merged flat.
     * $save = true → save to disk and return path; false → stream download.
     */
    public function generate(array $data, bool $save = false): string
    {
        if (!class_exists('TCPDF')) {
            throw new \RuntimeException(
                'TCPDF not found. Run: composer install  inside ams/generation/pdf/'
            );
        }

        $pdf = $this->buildPdf($data);
        $filename = $this->buildFilename($data);

        if ($save) {
            $outputDir = __DIR__ . '/completed';
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }
            $path = $outputDir . '/' . $filename;
            $pdf->Output($path, 'F');
            return $path;
        }

        $pdf->Output($filename, 'D');
        exit;
    }

    // ===================================================================
    //  Build full PDF: Page1 + Page2 (Enrollment) + Page3 (Medical)
    // ===================================================================

    private function buildPdf(array $d): \TCPDF
    {
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('GEMS-AMES');
        $pdf->SetAuthor('DepEd');
        $pdf->SetTitle('Basic Education Enrollment & Medical Form');
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        $pdf->SetMargins(12, 10, 12);
        $pdf->SetAutoPageBreak(false);

        // --- Enrollment Form: Page 1 ---
        $pdf->AddPage();
        $this->renderEnrollmentPage1($pdf, $d);

        // --- Enrollment Form: Page 2 ---
        $pdf->AddPage();
        $this->renderEnrollmentPage2($pdf, $d);

        // --- Medical Form: Page 3 ---
        $pdf->AddPage();
        $this->renderMedicalPage($pdf, $d);

        return $pdf;
    }

    // ===================================================================
    //  ENROLLMENT PAGE 1  (Sections 1-4)
    // ===================================================================

    private function renderEnrollmentPage1(\TCPDF $pdf, array $d): void
    {
        $W = 186;

        // Header
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetXY(12, 6);
        $pdf->Cell($W, 4, 'Revised as of 06/01/2025', 0, 1, 'R');

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetXY(12, 12);
        $pdf->Cell($W, 6, 'BASIC EDUCATION ENROLLMENT FORM', 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetXY(12, 18);
        $pdf->Cell($W, 4, 'THIS FORM IS NOT FOR SALE', 0, 1, 'C');

        $pdf->Line(12, 23, 198, 23);

        // Instructions
        $pdf->SetFont('helvetica', 'I', 7.5);
        $pdf->SetXY(12, 25);
        $pdf->MultiCell($W, 4,
            'Instructions: Print legibly all information required in CAPITAL letters and check all appropriate boxes. ' .
            'Submit accomplished form to the Person-in-Charge/Registrar/Class Adviser. Use black or blue pen only.',
            0, 'J');

        // ---- Section 1 & 2 ---- //
        $y = 35;
        $this->sectionLabel($pdf, '1. School Year', 12, $y);
        $this->fieldBox($pdf, $d['ed_school_year'] ?? '', 50, $y, 50, 6);

        $this->sectionLabel($pdf, 'Learner Reference No. (LRN), if applicable:', 115, $y);
        $this->fieldBox($pdf, $d['ed_lrn'] ?? '', 115, $y + 4, 83, 6);

        $y += 10;
        $this->sectionLabel($pdf, '2. Grade Level to Enroll:', 12, $y);
        $gradeLevel = $d['ed_grade_level'] ?? '';
        $this->checkbox($pdf, 12, $y + 5, 'Graded, specify Grade Level');
        $this->fieldBox($pdf, $gradeLevel, 75, $y + 5, 15, 5);

        $isNonGraded = strtoupper($d['pi_learning_classification'] ?? '') === 'NON-GRADED';
        $this->checkbox($pdf, 12, $y + 12, 'Non-Graded (For Special Needs Education (SNEd) Only)', $isNonGraded);

        $this->sectionLabel($pdf, 'For Kindergarten Enrollees:', 115, $y + 5);
        $earlyProgram = $d['pi__attended_early_learning_program_name'] ?? '';
        $this->checkbox($pdf, 115, $y + 11,
            'Does the learner have attended any Early Learning Program? If yes, please specify:', !empty($earlyProgram));
        if (!empty($earlyProgram)) {
            $pdf->SetFont('helvetica', '', 7);
            $pdf->SetXY(115, $y + 18);
            $pdf->Cell(83, 4, $earlyProgram, 'B', 0, 'L');
        }

        // ---- Section 3: Personal Info ---- //
        $y += 24;
        $this->sectionLabel($pdf, '3. Learner\'s Personal Information', 12, $y, true);
        $y += 5;

        $pdf->Rect(12, $y, $W, 100, 'D');

        // PSA BCN
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(13, $y + 1);
        $pdf->Cell(60, 3, 'PSA Birth Certificate No. (If available upon registration)', 0);
        $this->fieldBox($pdf, $d['pi_psa_bcn'] ?? '', 13, $y + 5, 100, 5);

        // Last Name / Birthdate
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(13, $y + 12);
        $pdf->Cell(80, 3, 'Last Name');
        $pdf->SetXY(120, $y + 12);
        $pdf->Cell(60, 3, 'Birthdate (mm/dd/yyyy)');

        $bdate = '';
        if (!empty($d['pi_birth_date'])) {
            $ts = strtotime($d['pi_birth_date']);
            if ($ts) $bdate = date('m/d/Y', $ts);
        }
        $this->fieldBox($pdf, strtoupper($d['pi_last_name'] ?? ''), 13, $y + 16, 100, 6);
        $this->fieldBox($pdf, $bdate, 120, $y + 16, 77, 6);

        // First Name / Age / Sex
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(13, $y + 24);
        $pdf->Cell(80, 3, 'First Name');
        $pdf->SetXY(120, $y + 24);
        $pdf->Cell(20, 3, 'Age');
        $pdf->SetXY(145, $y + 24);
        $pdf->Cell(40, 3, 'Sex');

        $age = '';
        if (!empty($d['pi_birth_date'])) {
            $age = (string) $this->calcAge($d['pi_birth_date']);
        }
        $sex = strtoupper($d['pi_sex'] ?? '');

        $this->fieldBox($pdf, strtoupper($d['pi_first_name'] ?? ''), 13, $y + 28, 100, 6);
        $this->fieldBox($pdf, $age, 120, $y + 28, 20, 6);
        $this->checkbox($pdf, 145, $y + 29, 'Male', $sex === 'MALE');
        $this->checkbox($pdf, 162, $y + 29, 'Female', $sex === 'FEMALE');

        // Middle Name / Place of Birth
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(13, $y + 36);
        $pdf->Cell(80, 3, 'Middle Name');
        $pdf->SetXY(120, $y + 36);
        $pdf->Cell(60, 3, 'Place of Birth (Municipality/City)');

        $this->fieldBox($pdf, strtoupper($d['pi_middle_name'] ?? ''), 13, $y + 40, 100, 6);
        $this->fieldBox($pdf, $d['pi_place_of_birth'] ?? '', 120, $y + 40, 77, 6);

        // Extension / Religion
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(13, $y + 48);
        $pdf->Cell(60, 3, 'Extension Name e.g. Jr., III (If applicable)');
        $pdf->SetXY(120, $y + 48);
        $pdf->Cell(40, 3, 'Religion');

        $this->fieldBox($pdf, $d['pi_extension'] ?? '', 13, $y + 52, 50, 5);
        $this->fieldBox($pdf, $d['religion_name'] ?? '', 120, $y + 52, 77, 5);

        // IP Community
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(13, $y + 59);
        $pdf->MultiCell(100, 3,
            'Belonging to any Indigenous Peoples (IP) Community/Indigenous Cultural Community?', 0);
        $isIP = !empty($d['indigenous_group_name']) &&
                strtoupper($d['indigenous_group_name']) !== 'NONE' &&
                strtoupper($d['indigenous_group_name']) !== 'N/A' &&
                strtoupper($d['indigenous_group_name']) !== 'NOT APPLICABLE';
        $this->checkbox($pdf, 13, $y + 65, 'Yes', $isIP);
        $this->checkbox($pdf, 28, $y + 65, 'No', !$isIP);
        if ($isIP) {
            $pdf->SetFont('helvetica', '', 7);
            $pdf->SetXY(50, $y + 65);
            $pdf->Cell(60, 4, 'If Yes, please specify: ' . ($d['indigenous_group_name'] ?? ''), 'B');
        } else {
            $pdf->SetXY(50, $y + 65);
            $pdf->Cell(60, 4, 'If Yes, please specify:', 0);
            $pdf->SetXY(90, $y + 65);
            $pdf->Cell(30, 4, '', 'B');
        }

        // Mother Tongue
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(120, $y + 59);
        $pdf->Cell(40, 3, 'Mother Tongue');
        $this->fieldBox($pdf, $d['mother_tongue_name'] ?? '', 120, $y + 63, 77, 5);

        // 4Ps
        $has4ps = !empty($d['ac_4ps_household_number']);
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(13, $y + 72);
        $pdf->Cell(70, 3, 'Is your family a beneficiary of 4Ps?');
        $this->checkbox($pdf, 82, $y + 72, 'Yes', $has4ps);
        $this->checkbox($pdf, 94, $y + 72, 'No', !$has4ps);
        $pdf->SetXY(13, $y + 76);
        $pdf->Cell(70, 3, 'If Yes, please write the 4Ps Household ID Number');
        $this->fieldBox($pdf, $d['ac_4ps_household_number'] ?? '', 13, $y + 80, 150, 5);

        // Current Address
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetXY(13, $y + 87);
        $pdf->Cell(40, 3, 'Current Address');
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(13, $y + 91);
        $pdf->Cell(20, 3, 'House No.');
        $pdf->SetXY(38, $y + 91);
        $pdf->Cell(40, 3, 'Sitio/Street Name');
        $pdf->SetXY(110, $y + 91);
        $pdf->Cell(30, 3, 'Barangay');

        $this->fieldBox($pdf, $d['ca_house_number'] ?? '', 13, $y + 95, 22, 5);
        $this->fieldBox($pdf, $d['ca_street_name'] ?? '', 37, $y + 95, 70, 5);
        $this->fieldBox($pdf, $d['ca_barangay'] ?? '', 110, $y + 95, 77, 5);

        $y += 100;
        $pdf->Rect(12, $y, $W, 16, 'D');
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(13, $y + 1);
        $pdf->Cell(30, 3, 'Municipality/City');
        $pdf->SetXY(60, $y + 1);
        $pdf->Cell(30, 3, 'Province');
        $pdf->SetXY(110, $y + 1);
        $pdf->Cell(20, 3, 'Country');
        $pdf->SetXY(158, $y + 1);
        $pdf->Cell(30, 3, 'Zip Code');

        $this->fieldBox($pdf, $d['ca_municipality'] ?? '', 13, $y + 5, 44, 5);
        $this->fieldBox($pdf, $d['ca_provice'] ?? '', 60, $y + 5, 46, 5);
        $this->fieldBox($pdf, $d['ca_country'] ?? 'Philippines', 110, $y + 5, 44, 5);
        $this->fieldBox($pdf, (string)($d['ca_zipcode'] ?? ''), 158, $y + 5, 39, 5);

        // Permanent Address
        $y += 17;
        $pdf->Rect(12, $y, $W, 16, 'D');
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetXY(13, $y + 1);
        $pdf->Cell(30, 3, 'Permanent Address');
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(45, $y + 1);
        $pdf->Cell(50, 3, 'Same with your Current Address?');
        $sameAddr = ($d['ca_barangay'] ?? '') === ($d['pa_barangay'] ?? '') &&
                    ($d['ca_municipality'] ?? '') === ($d['pa_municipality'] ?? '');
        $this->checkbox($pdf, 99, $y + 1, 'Yes', $sameAddr);
        $this->checkbox($pdf, 111, $y + 1, 'No', !$sameAddr);
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(125, $y + 1);
        $pdf->Cell(60, 3, 'If Yes, proceed to item 4');

        $pdf->SetXY(13, $y + 6);
        $pdf->Cell(20, 3, 'House No.');
        $pdf->SetXY(38, $y + 6);
        $pdf->Cell(40, 3, 'Sitio/Street Name');
        $pdf->SetXY(110, $y + 6);
        $pdf->Cell(30, 3, 'Barangay');

        $this->fieldBox($pdf, $d['pa_house_number'] ?? '', 13, $y + 10, 22, 5);
        $this->fieldBox($pdf, $d['pa_street_name'] ?? '', 37, $y + 10, 70, 5);
        $this->fieldBox($pdf, $d['pa_barangay'] ?? '', 110, $y + 10, 77, 5);

        $y += 17;
        $pdf->Rect(12, $y, $W, 11, 'D');
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(13, $y + 1);
        $pdf->Cell(30, 3, 'Municipality/City');
        $pdf->SetXY(60, $y + 1);
        $pdf->Cell(30, 3, 'Province');
        $pdf->SetXY(110, $y + 1);
        $pdf->Cell(20, 3, 'Country');
        $pdf->SetXY(158, $y + 1);
        $pdf->Cell(30, 3, 'Zip Code');

        $this->fieldBox($pdf, $d['pa_municipality'] ?? '', 13, $y + 5, 44, 5);
        $this->fieldBox($pdf, $d['pa_province'] ?? '', 60, $y + 5, 46, 5);
        $this->fieldBox($pdf, $d['pa_country'] ?? 'Philippines', 110, $y + 5, 44, 5);
        $this->fieldBox($pdf, (string)($d['pa_zip_code'] ?? ''), 158, $y + 5, 39, 5);

        // ---- Section 4: Parents ---- //
        $y += 13;
        $this->sectionLabel($pdf, '4. Parent\'s/Guardian\'s Information', 12, $y, true);
        $y += 5;

        $pdf->Rect(12, $y, $W, 48, 'D');

        // Father
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetXY(13, $y + 1);
        $pdf->Cell(40, 3, "Father's Name");
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(13, $y + 5);
        $pdf->Cell(40, 3, 'Last Name');
        $pdf->SetXY(60, $y + 5);
        $pdf->Cell(40, 3, 'First Name');
        $pdf->SetXY(110, $y + 5);
        $pdf->Cell(40, 3, 'Middle Name');
        $pdf->SetXY(155, $y + 5);
        $pdf->Cell(40, 3, 'Contact Number');

        $this->fieldBox($pdf, strtoupper($d['fi_last_name'] ?? ''), 13, $y + 9, 44, 5);
        $this->fieldBox($pdf, strtoupper($d['fi_first_name'] ?? ''), 60, $y + 9, 47, 5);
        $this->fieldBox($pdf, strtoupper($d['fi_middle_name'] ?? ''), 110, $y + 9, 42, 5);
        $this->fieldBox($pdf, $d['fi_contact_number'] ?? '', 155, $y + 9, 42, 5);

        // Mother
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetXY(13, $y + 16);
        $pdf->Cell(40, 3, "Mother's Maiden Name");
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(13, $y + 20);
        $pdf->Cell(40, 3, 'Last Name');
        $pdf->SetXY(60, $y + 20);
        $pdf->Cell(40, 3, 'First Name');
        $pdf->SetXY(110, $y + 20);
        $pdf->Cell(40, 3, 'Middle Name');
        $pdf->SetXY(155, $y + 20);
        $pdf->Cell(40, 3, 'Contact Number');

        $this->fieldBox($pdf, strtoupper($d['mi_last_name'] ?? ''), 13, $y + 24, 44, 5);
        $this->fieldBox($pdf, strtoupper($d['mi_first_name'] ?? ''), 60, $y + 24, 47, 5);
        $this->fieldBox($pdf, strtoupper($d['mi_middle_name'] ?? ''), 110, $y + 24, 42, 5);
        $this->fieldBox($pdf, $d['mi_contact_number'] ?? '', 155, $y + 24, 42, 5);

        // Guardian
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetXY(13, $y + 31);
        $pdf->Cell(40, 3, "Legal Guardian's Name");
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(13, $y + 35);
        $pdf->Cell(40, 3, 'Last Name');
        $pdf->SetXY(60, $y + 35);
        $pdf->Cell(40, 3, 'First Name');
        $pdf->SetXY(110, $y + 35);
        $pdf->Cell(40, 3, 'Middle Name');
        $pdf->SetXY(155, $y + 35);
        $pdf->Cell(40, 3, 'Contact Number');

        $this->fieldBox($pdf, strtoupper($d['gi_last_name'] ?? ''), 13, $y + 39, 44, 5);
        $this->fieldBox($pdf, strtoupper($d['gi_first_name'] ?? ''), 60, $y + 39, 47, 5);
        $this->fieldBox($pdf, strtoupper($d['gi_middle_name'] ?? ''), 110, $y + 39, 42, 5);
        $this->fieldBox($pdf, $d['gi_contact_number'] ?? '', 155, $y + 39, 42, 5);
    }

    // ===================================================================
    //  ENROLLMENT PAGE 2  (Sections 5-8 + Certification)
    // ===================================================================

    private function renderEnrollmentPage2(\TCPDF $pdf, array $d): void
    {
        $W = 186;
        $y = 10;

        // ---- Section 5: Special Needs ---- //
        $this->sectionLabel($pdf, '5. Is the Learner under the Special Needs Education Program?', 12, $y, true);
        $snep = !empty($d['snep_a1_diagnosis']) &&
                strtoupper($d['snep_a1_diagnosis']) !== 'NONE';
        $this->checkbox($pdf, 132, $y, 'Yes', $snep);
        $this->checkbox($pdf, 146, $y, 'No', !$snep);

        $y += 5;
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(12, $y);
        $pdf->Cell($W, 3, 'If Yes, check only 1, either from a1 or a2');

        $y += 5;
        $pdf->SetFont('helvetica', 'I', 7);
        $pdf->SetXY(12, $y);
        $pdf->Cell($W, 3, 'a1. With Diagnosis from Licensed Medical Specialist:');

        $y += 4;
        $diagMap = [
            'ADHD'   => 'Attention Deficit Hyperactivity Disorder',
            'ASD'    => 'Autism Spectrum Disorder',
            'CP'     => 'Cerebral Palsy',
            'E-B D'  => 'Emotional-Behavior Disorder',
            'HI'     => 'Hearing Impairment',
            'ID'     => 'Intellectual Disability',
            'LD'     => 'Learning Disability',
            'MD'     => 'Multiple Disabilities',
            'O/P H'  => 'Orthopedic/Physical Handicap',
            'S/L D'  => 'Speech/Language Disorder',
            'SHP/CD' => 'Special Health Problem/Chronic Disease',
            'VI'     => 'Visual Impairment',
        ];

        $currentDiag = strtoupper($d['snep_a1_diagnosis'] ?? '');
        $diagFlat = array_keys($diagMap);
        $chunks = array_chunk($diagFlat, 5);
        $colX = [12, 80, 148];
        foreach ($chunks as $ci => $chunk) {
            $cx = $colX[$ci] ?? 12;
            $ry = $y;
            foreach ($chunk as $key) {
                $checked = strpos($currentDiag, $key) !== false;
                $this->checkbox($pdf, $cx, $ry, $diagMap[$key], $checked, 50);
                $ry += 5;
            }
        }

        // SHP sub
        $y += 25;
        $shpSub = strtoupper($d['snep_a1_sub_shpcd'] ?? '');
        $this->checkbox($pdf, 148, $y, 'Cancer', $shpSub === 'CANCER');
        $this->checkbox($pdf, 165, $y, 'Non-Cancer', $shpSub === 'NON-CANCER');

        // VI sub
        $viSub = strtoupper($d['snep_a1_sub_vi'] ?? '');
        $y += 5;
        $this->checkbox($pdf, 148, $y, 'Blind', $viSub === 'BLIND');
        $this->checkbox($pdf, 165, $y, 'Low Vision', $viSub === 'LOW-VISION');

        // a2 Manifestations
        $y += 8;
        $pdf->SetFont('helvetica', 'I', 7);
        $pdf->SetXY(12, $y);
        $pdf->Cell($W, 3, 'a2. With Manifestations');

        $y += 4;
        $manifestMap = [
            'DiAK'     => 'Difficulty in Applying Knowledge',
            'DiC'      => 'Difficulty in Communicating',
            'DiDIB'    => 'Difficulty in Displaying Interpersonal Behavior (Emotional and Behavioral)',
            'DiH'      => 'Difficulty in Hearing',
            'DiM'      => 'Difficulty in Mobility (Walking, Climbing and Grasping)',
            'DiPAS'    => 'Difficulty in Performing Adaptive Skills (Self-Care)',
            'DiRCPAaU' => 'Difficulty in Remembering, Concentrating, Paying Attention and Understanding',
            'DiS'      => 'Difficulty in Seeing',
        ];

        $currentManif = strtoupper($d['snep_a2_manifestations'] ?? '');
        $manifFlat = array_keys($manifestMap);
        $manifChunks = array_chunk($manifFlat, 4);
        $manifestCols = [12, 100];
        foreach ($manifChunks as $ci => $chunk) {
            $cx = $manifestCols[$ci] ?? 12;
            $ry = $y;
            foreach ($chunk as $key) {
                $checked = strpos($currentManif, $key) !== false;
                $this->checkbox($pdf, $cx, $ry, $manifestMap[$key], $checked, 80);
                $ry += 5;
            }
        }

        $y += 22;
        $pwdId = !empty($d['snep_pwd_id']) && $d['snep_pwd_id'] != 0;
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetXY(12, $y);
        $pdf->Cell(80, 3, 'b. Does the Learner have a PWD ID?');
        $this->checkbox($pdf, 80, $y, 'Yes', $pwdId);
        $this->checkbox($pdf, 93, $y, 'No', !$pwdId);

        // ---- Section 6: Returning Learner ---- //
        $y += 7;
        $this->sectionLabel($pdf, '6. For Returning Learner (Balik-Aral) and those who will Transfer/Move In', 12, $y, true);
        $y += 5;

        $pdf->Rect(12, $y, $W, 20, 'D');
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(13, $y + 1);
        $pdf->Cell(90, 3, 'Last Grade Level Completed');
        $pdf->SetXY(103, $y + 1);
        $pdf->Cell(90, 3, 'Last School Year Completed');

        $this->fieldBox($pdf, $d['rl_last_grade_level_completed'] ?? '', 13, $y + 5, 86, 5);
        $this->fieldBox($pdf, $d['rl_last_school_year_completed'] ?? '', 103, $y + 5, 84, 5);

        $pdf->SetXY(13, $y + 12);
        $pdf->Cell(90, 3, 'Last School Attended');
        $pdf->SetXY(103, $y + 12);
        $pdf->Cell(20, 3, 'School ID');

        $this->fieldBox($pdf, $d['rl_school_attended'] ?? '', 13, $y + 16, 86, 3);
        $this->fieldBox($pdf, (string)($d['rl_school_id'] ?? ''), 130, $y + 12, 67, 7);

        // ---- Section 7: Senior High School ---- //
        $y += 24;
        $this->sectionLabel($pdf, '7. For Learner in Senior High School', 12, $y, true);
        $y += 5;

        $pdf->Rect(12, $y, $W, 24, 'D');
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(13, $y + 2);
        $pdf->Cell(15, 3, 'Semester');
        $this->checkbox($pdf, 35, $y + 2, '1st');
        $this->checkbox($pdf, 50, $y + 2, '2nd');

        $pdf->SetXY(13, $y + 9);
        $pdf->Cell(15, 3, 'Track:');
        $pdf->SetXY(13, $y + 16);
        $pdf->Cell(15, 3, 'Strand:');

        // ---- Section 8: Learning Modalities ---- //
        $y += 28;
        $this->sectionLabel($pdf, '8. If the school will implement other distance learning modalities aside from face-to-face instruction, what would you prefer for your child?', 12, $y, true);
        $y += 8;

        $pdf->Rect(12, $y, $W, 14, 'D');
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(13, $y + 2);
        $pdf->Cell($W, 3, 'Check all that applies:');

        $modality = strtoupper($d['li_learning_modality'] ?? '');
        $modalities = [
            'BLENDED (COMBINATION)' => 'Blended (Combination)',
            'HOMESCHOOLING'         => 'Homeschooling',
            'MODULAR (PRINT)'       => 'Modular (Print)',
            'RADIO-BASED TELEVISION'=> 'Radio-Based Television',
            'EDUCATIONAL TELEVISION'=> 'Educational Television',
            'MODULAR (DIGITAL)'     => 'Modular (Digital)',
            'ONLINE'                => 'Online',
        ];

        $row1 = array_slice($modalities, 0, 4, true);
        $row2 = array_slice($modalities, 4, 3, true);
        $xPositions1 = [18, 65, 110, 152];
        $xPositions2 = [18, 65, 110];

        $xi = 0;
        foreach ($row1 as $key => $label) {
            $checked = strpos($modality, $key) !== false;
            $this->checkbox($pdf, $xPositions1[$xi], $y + 6, $label, $checked, 42);
            $xi++;
        }
        $xi = 0;
        foreach ($row2 as $key => $label) {
            $checked = strpos($modality, $key) !== false;
            $this->checkbox($pdf, $xPositions2[$xi], $y + 11, $label, $checked, 42);
            $xi++;
        }

        // ---- Certification ---- //
        $y += 20;
        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetXY(12, $y);
        $pdf->MultiCell($W, 4,
            'I hereby certify that the above information given are true and correct to the best of my knowledge and I allow the ' .
            'Department of Education to process the learner\'s personal information to create and/or update his/her learner ' .
            'profile in the Learner Information System.', 0, 'J');

        $y += 13;
        $pdf->SetXY(12, $y);
        $pdf->MultiCell($W, 4,
            'The personal information herein shall be treated as confidential in compliance with the Data Privacy Act of 2012.',
            0, 'J');

        $y += 20;
        $pdf->Line(12, $y, 110, $y);
        $pdf->Line(130, $y, 198, $y);

        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(12, $y + 1);
        $pdf->Cell(100, 3, 'Signature Over Printed Name of Parent/Guardian', 0, 0, 'C');
        $pdf->SetXY(130, $y + 1);
        $pdf->Cell(68, 3, 'Date', 0, 0, 'C');
    }

    // ===================================================================
    //  MEDICAL PAGE 3  (one-to-one with medical_info.php form)
    // ===================================================================

    private function renderMedicalPage(\TCPDF $pdf, array $d): void
    {
        $W = 186;

        // ---- Header ---- //
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetXY(12, 10);
        $pdf->Cell($W, 6, 'STUDENT MEDICAL INFORMATION FORM', 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetXY(12, 16);
        $pdf->Cell($W, 4, 'THIS FORM IS NOT FOR SALE', 0, 1, 'C');

        $pdf->Line(12, 21, 198, 21);

        // Student name header line
        $fullName = strtoupper(trim(
            ($d['pi_last_name'] ?? '') . ', ' .
            ($d['pi_first_name'] ?? '') . ' ' .
            ($d['pi_middle_name'] ?? '')
        ));
        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetXY(12, 23);
        $pdf->Cell(40, 4, 'Learner\'s Name:', 0);
        $this->fieldBox($pdf, $fullName, 52, 23, 146, 5);

        $y = 32;

        // ---- Section 1: Allergies ---- //
        $this->sectionLabel($pdf, '1. Allergies', 12, $y, true);
        $y += 5;

        $pdf->Rect(12, $y, $W, 16, 'D');
        $pdf->SetFont('helvetica', '', 7);

        $pdf->SetXY(13, $y + 1);
        $pdf->Cell(44, 3, 'Medicine Allergies');
        $pdf->SetXY(59, $y + 1);
        $pdf->Cell(44, 3, 'Pollen Allergies');
        $pdf->SetXY(105, $y + 1);
        $pdf->Cell(44, 3, 'Food Allergies');
        $pdf->SetXY(153, $y + 1);
        $pdf->Cell(44, 3, 'Other Allergies');

        $this->fieldBox($pdf, $d['mf_a_medicine'] ?? '', 13,  $y + 5, 43, 5);
        $this->fieldBox($pdf, $d['mf_a_pollen']   ?? '', 59,  $y + 5, 43, 5);
        $this->fieldBox($pdf, $d['mf_a_food']     ?? '', 105, $y + 5, 45, 5);
        $this->fieldBox($pdf, $d['mf_a_others']   ?? '', 153, $y + 5, 44, 5);

        $y += 17;

        // ---- Section 2: Medical Conditions (Other/Current) ---- //
        $this->sectionLabel($pdf, '2. Medical Conditions', 12, $y, true);
        $y += 5;

        $medConditions = [
            'ERROR OF REFRACTION',
            'SEIZURE',
            'ANEMIA',
            'FRACTURE/DISLOCATION',
            'ASTHMA',
            'HEART ILLNESS',
            'BLEEDING DISORDER',
        ];
        $currentMedConds = strtoupper($d['mf_o_medical_conditions'] ?? '');

        $pdf->Rect(12, $y, $W, 12, 'D');
        $condChunks = array_chunk($medConditions, 4);
        $colXMed = [14, 62, 110, 155];
        foreach ($condChunks as $ri => $row) {
            $ry = $y + 2 + ($ri * 6);
            foreach ($row as $ci => $cond) {
                $cx = $colXMed[$ci] ?? 14;
                $checked = strpos($currentMedConds, $cond) !== false;
                $this->checkbox($pdf, $cx, $ry, $cond, $checked, 42);
            }
        }
        $y += 13;

        // Other Medical Conditions
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(13, $y + 1);
        $pdf->Cell(60, 3, 'Other Medical Conditions:');
        $this->fieldBox($pdf, $d['mf_o_others'] ?? '', 70, $y, 127, 5);
        $y += 7;

        // ---- Section 3: Surgical History ---- //
        $this->sectionLabel($pdf, '3. Surgical History', 12, $y, true);
        $y += 5;

        $pdf->Rect(12, $y, $W, 11, 'D');
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(13, $y + 1);
        $pdf->Cell(44, 3, 'Surgery Date');
        $pdf->SetXY(59, $y + 1);
        $pdf->Cell(60, 3, 'Hospital Name');
        $pdf->SetXY(120, $y + 1);
        $pdf->Cell(60, 3, 'Body Part Affected');

        $surgDate = '';
        if (!empty($d['mf_sh_surgery_date'])) {
            $ts = strtotime($d['mf_sh_surgery_date']);
            if ($ts) $surgDate = date('m/d/Y', $ts);
        }
        $this->fieldBox($pdf, $surgDate, 13, $y + 5, 43, 5);
        $this->fieldBox($pdf, $d['mf_sh_hospital_name'] ?? '', 59, $y + 5, 58, 5);
        $this->fieldBox($pdf, $d['mf_sh_bodypart_affected'] ?? '', 120, $y + 5, 77, 5);

        $y += 13;

        // ---- Section 4: Therapy / Medication ---- //
        $this->sectionLabel($pdf, '4. Therapy / Medication', 12, $y, true);
        $y += 5;

        $pdf->Rect(12, $y, $W, 11, 'D');
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(13, $y + 1);
        $pdf->Cell(90, 3, 'Therapy Type');
        $pdf->SetXY(105, $y + 1);
        $pdf->Cell(90, 3, 'Dosage / Schedule');

        $this->fieldBox($pdf, $d['mf_tm_type'] ?? '', 13, $y + 5, 89, 5);
        $this->fieldBox($pdf, $d['mf_tm_dosage_schedule'] ?? '', 105, $y + 5, 92, 5);

        $y += 13;

        // ---- Section 5: Family Medical History / Chronic Conditions ---- //
        $this->sectionLabel($pdf, '5. Family Medical History / Chronic Conditions', 12, $y, true);
        $y += 5;

        $chronicConditions = [
            'TUBERCOLOSIS',
            'DIABETES MELLITUS',
            'HYPERTENSION',
            'STROKE/ HEART ATTACK',
            'DEPRESSION',
            'KIDNEY PROBLEMS',
        ];
        $currentChronic = strtoupper($d['mf_mc_conditions'] ?? '');

        $pdf->Rect(12, $y, $W, 12, 'D');
        $chronChunks = array_chunk($chronicConditions, 3);
        $colXChr = [14, 75, 138];
        foreach ($chronChunks as $ri => $row) {
            $ry = $y + 2 + ($ri * 6);
            foreach ($row as $ci => $cond) {
                $cx = $colXChr[$ci] ?? 14;
                $checked = strpos($currentChronic, $cond) !== false;
                $this->checkbox($pdf, $cx, $ry, $cond, $checked, 58);
            }
        }
        $y += 13;

        // Cancer Type / Other Chronic
        $pdf->Rect(12, $y, $W, 10, 'D');
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(13, $y + 1);
        $pdf->Cell(44, 3, 'Cancer Type:');
        $this->fieldBox($pdf, $d['mf_mc_cancer_type'] ?? '', 52, $y + 1, 50, 5);
        $pdf->SetXY(107, $y + 1);
        $pdf->Cell(45, 3, 'Other Chronic Conditions:');
        $this->fieldBox($pdf, $d['mf_mc_others'] ?? '', 152, $y + 1, 45, 5);
        $y += 12;

        // ---- Section 6: COVID-19 Exposure ---- //
        $this->sectionLabel($pdf, '6. COVID-19 Exposure', 12, $y, true);
        $y += 5;

        $covidExposed = !empty($d['mf_exposure_c_v']) && $d['mf_exposure_c_v'] == 1;
        $pdf->Rect(12, $y, $W, 8, 'D');
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(13, $y + 1);
        $pdf->Cell(80, 3, 'Has the learner been exposed to COVID-19?');
        $this->checkbox($pdf, 95, $y + 2, 'Yes', $covidExposed);
        $this->checkbox($pdf, 110, $y + 2, 'No', !$covidExposed);
        $y += 10;

        // ---- Section 7: Pertinent Medical Information ---- //
        $this->sectionLabel($pdf, '7. Pertinent Medical Information', 12, $y, true);
        $y += 5;

        $pdf->Rect(12, $y, $W, 20, 'D');
        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetXY(13, $y + 1);
        $pertinent = $d['mf_o_pertinent_information'] ?? '';
        $pdf->MultiCell($W - 2, 4, $pertinent, 0, 'L');
        $y += 22;

        // ---- Certification ---- //
        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetXY(12, $y);
        $pdf->MultiCell($W, 4,
            'I hereby certify that the above medical information is true and correct to the best of my knowledge.',
            0, 'J');
        $y += 12;

        $pdf->Line(12, $y, 110, $y);
        $pdf->Line(130, $y, 198, $y);
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(12, $y + 1);
        $pdf->Cell(100, 3, 'Signature Over Printed Name of Parent/Guardian', 0, 0, 'C');
        $pdf->SetXY(130, $y + 1);
        $pdf->Cell(68, 3, 'Date', 0, 0, 'C');
    }

    // ===================================================================
    //  Helper methods
    // ===================================================================

    private function sectionLabel(\TCPDF $pdf, string $text, float $x, float $y, bool $bold = false): void
    {
        $pdf->SetFont('helvetica', $bold ? 'B' : '', 8);
        $pdf->SetXY($x, $y);
        $pdf->Cell(186, 4, $text, 0);
    }

    private function fieldBox(\TCPDF $pdf, string $value, float $x, float $y, float $w, float $h): void
    {
        $pdf->SetDrawColor(150, 150, 150);
        $pdf->Rect($x, $y, $w, $h, 'D');
        $pdf->SetDrawColor(0, 0, 0);
        if ($value !== '') {
            $pdf->SetFont('helvetica', '', 7.5);
            $pdf->SetXY($x + 1, $y + 0.8);
            $pdf->Cell($w - 2, $h - 1, $value, 0, 0, 'L');
        }
    }

    private function checkbox(\TCPDF $pdf, float $x, float $y, string $label, bool $checked = false, float $labelWidth = 60): void
    {
        $size = 3;
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Rect($x, $y, $size, $size, 'D');
        if ($checked) {
            $pdf->SetFont('zapfdingbats', '', 8);
            $pdf->SetXY($x - 0.5, $y - 0.5);
            $pdf->Cell($size + 1, $size + 1, chr(52), 0, 0, 'C');
            $pdf->SetFont('helvetica', '', 7);
        }
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY($x + $size + 1, $y + 0.2);
        $pdf->Cell($labelWidth, $size, $label, 0);
    }

    private function calcAge(string $birthDate): int
    {
        try {
            $dob = new \DateTime($birthDate);
            $now = new \DateTime();
            return (int) $dob->diff($now)->y;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function buildFilename(array $d): string
    {
        $name = preg_replace('/[^A-Za-z0-9_\-]/', '_',
            ($d['pi_last_name'] ?? 'unknown') . '_' .
            ($d['pi_first_name'] ?? '') . '_' .
            ($d['ed_school_year'] ?? date('Y'))
        );
        return 'enrollment_medical_' . $name . '.pdf';
    }
}