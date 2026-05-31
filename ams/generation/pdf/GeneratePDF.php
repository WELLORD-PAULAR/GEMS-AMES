<?php
namespace Classes;

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * GeneratePDF
 *
 * Renders a 3-page PDF:
 *   Page 1-2 : DepEd Basic Education Enrollment Form (BEEF) — exact match
 *   Page 3   : SHD Form 1 Medical History & Consent — exact match
 *
 * Requires: tecnickcom/tcpdf  →  composer install  (inside ams/generation/pdf/)
 */
class GeneratePDF
{
    // ── Layout ───────────────────────────────────────────────────────────
    private const L   = 12;     // left margin (mm)
    private const R   = 12;     // right margin (mm)
    private const PW  = 210;    // A4 page width
    private const W   = 186;    // usable width  (210 - 12 - 12)
    private const FS  = 7;      // base font size
    private const FSS = 6.5;    // small font size
    private const CB  = 3.0;    // checkbox square size

    // ── Entry point ──────────────────────────────────────────────────────

    public function generate(array $data, bool $save = false): string
    {
        if (!class_exists('TCPDF')) {
            throw new \RuntimeException(
                'TCPDF not found. Run: composer install inside ams/generation/pdf/'
            );
        }
        $pdf = $this->buildPdf($data);
        $filename = $this->buildFilename($data);

        if ($save) {
            $dir = __DIR__ . '/completed';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $path = $dir . '/' . $filename;
            $pdf->Output($path, 'F');
            return $path;
        }
        $pdf->Output($filename, 'D');
        exit;
    }

    // ── Build all pages ──────────────────────────────────────────────────

    private function buildPdf(array $d): \TCPDF
    {
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('GEMS-AMES');
        $pdf->SetAuthor('DepEd Baguio City');
        $pdf->SetTitle('Enrollment & Medical Form');
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        $pdf->SetMargins(self::L, 8, self::R);
        $pdf->SetAutoPageBreak(false);

        $pdf->AddPage();
        $this->renderEnrollmentPage1($pdf, $d);

        $pdf->AddPage();
        $this->renderEnrollmentPage2($pdf, $d);

        $pdf->AddPage();
        $this->renderMedicalPage($pdf, $d);

        return $pdf;
    }

    // ════════════════════════════════════════════════════════════════════
    //  ENROLLMENT — PAGE 1   (Sections 1-4)
    // ════════════════════════════════════════════════════════════════════

    private function renderEnrollmentPage1(\TCPDF $pdf, array $d): void
    {
        $L = self::L;
        $W = self::W;

        // ── Logo ─────────────────────────────────────────────────────
        $logo = __DIR__ . '/../../style/logo.png';
        if (file_exists($logo)) {
            $pdf->Image($logo, $L + 1, 7, 16, 16, 'PNG');
        }

        // ── Header text ──────────────────────────────────────────────
        $this->font($pdf, '', 7);
        $pdf->SetXY($L, 6);
        $pdf->Cell($W, 4, 'Revised as of 06/01/2025', 0, 0, 'R');

        $this->font($pdf, 'B', 13);
        $pdf->SetXY($L, 12);
        $pdf->Cell($W, 6, 'BASIC EDUCATION ENROLLMENT FORM', 0, 0, 'C');

        $this->font($pdf, '', 8);
        $pdf->SetXY($L, 18);
        $pdf->Cell($W, 5, 'THIS FORM IS NOT FOR SALE', 0, 0, 'C');

        $y = 24;
        $pdf->Line($L, $y, $L + $W, $y);

        // ── Instructions ─────────────────────────────────────────────
        $this->font($pdf, 'BI', 7.5);
        $pdf->SetXY($L, 25.5);
        $pdf->MultiCell($W, 3.8,
            'Instructions: Print legibly all information required in CAPITAL letters and check all appropriate ' .
            'boxes. Submit accomplished form to the Person-in-Charge/Registrar/Class Adviser. Use black or blue pen only.',
            0, 'J');

        // ── Section 1 — School Year & LRN ────────────────────────────
        $y = 35;
        $this->font($pdf, '', self::FS);
        $pdf->SetXY($L, $y);
        $pdf->Cell(30, 5, '1. School Year');
        $this->box($pdf, $d['ed_school_year'] ?? '', $L + 30, $y, 42, 5.5);

        $this->font($pdf, '', self::FS);
        $pdf->SetXY($L + 100, $y);
        $pdf->Cell(98, 4.5, 'Learner Reference No. (LRN), if applicable:');
        $this->box($pdf, $d['ed_lrn'] ?? '', $L + 100, $y + 4.5, 86, 5.5);

        // ── Section 2 — Grade Level ───────────────────────────────────
        $y = 48;
        $this->font($pdf, '', self::FS);
        $pdf->SetXY($L, $y);
        $pdf->Cell(50, 5, '2. Grade Level to Enroll:');

        $isGraded = strtoupper($d['pi_learning_classification'] ?? 'GRADED') !== 'NON-GRADED';
        $y2 = $y + 5.5;
        $this->cb($pdf, $L, $y2, 'Graded, specify Grade Level', $isGraded, 58);
        $this->box($pdf, $d['ed_grade_level'] ?? '', $L + 62, $y2 - 0.5, 16, 5);

        $y3 = $y2 + 6;
        $this->cb($pdf, $L, $y3, 'Non-Graded (For Special Needs Education (SNEd) Only)', !$isGraded, 80);

        // Kindergarten block
        $kgX = $L + 100;
        $this->font($pdf, 'B', self::FS);
        $pdf->SetXY($kgX, $y + 1);
        $pdf->Cell(86, 4.5, 'For Kindergarten Enrollees:');

        $earlyProg = $d['pi__attended_early_learning_program_name'] ?? '';
        $this->cb($pdf, $kgX, $y + 6.5,
            "Does the learner have attended any Early\nLearning Program? If yes, please specify:",
            !empty($earlyProg), 84);
        if (!empty($earlyProg)) {
            $pdf->SetXY($kgX, $y + 16);
            $this->font($pdf, '', self::FS);
            $pdf->Cell(86, 4, $earlyProg, 'B');
        }

        // ── Section 3 — Learner Personal Information ──────────────────
        $y = 65;
        $this->font($pdf, 'B', self::FS);
        $pdf->SetXY($L, $y);
        $pdf->Cell($W, 5, "3. Learner's Personal Information");

        $y += 5.5;
        $bY = $y;
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Rect($L, $bY, $W, 108, 'D');

        $p = 1.5; // inner padding

        // PSA BCN
        $this->font($pdf, '', self::FSS);
        $pdf->SetXY($L + $p, $bY + $p);
        $pdf->Cell(100, 3.5, 'PSA Birth Certificate No. (If available upon registration)');
        $this->box($pdf, $d['pi_psa_bcn'] ?? '', $L + $p, $bY + 5, 110, 5.5);

        // Last Name | Birthdate
        $ry = $bY + 13;
        $this->colHead($pdf, $L + $p, $ry, 104, 'Last Name');
        $this->colHead($pdf, $L + 108, $ry, 78, 'Birthdate (mm/dd/yyyy)');
        $ry += 3.5;
        $bdate = '';
        if (!empty($d['pi_birth_date'])) {
            $ts = strtotime($d['pi_birth_date']);
            if ($ts) $bdate = date('m/d/Y', $ts);
        }
        $this->box($pdf, strtoupper($d['pi_last_name'] ?? ''), $L + $p, $ry, 104, 5.5);
        $this->box($pdf, $bdate, $L + 108, $ry, 78, 5.5);

        // First Name | Age | Sex
        $ry += 8;
        $this->colHead($pdf, $L + $p, $ry, 104, 'First Name');
        $this->colHead($pdf, $L + 108, $ry, 20, 'Age');
        $this->colHead($pdf, $L + 130, $ry, 58, 'Sex');
        $ry += 3.5;
        $age = !empty($d['pi_birth_date']) ? (string)$this->calcAge($d['pi_birth_date']) : '';
        $sex = strtoupper($d['pi_sex'] ?? '');
        $this->box($pdf, strtoupper($d['pi_first_name'] ?? ''), $L + $p, $ry, 104, 5.5);
        $this->box($pdf, $age, $L + 108, $ry, 20, 5.5);
        $this->cb($pdf, $L + 130, $ry + 1, 'Male', $sex === 'MALE', 16);
        $this->cb($pdf, $L + 153, $ry + 1, 'Female', $sex === 'FEMALE', 20);

        // Middle Name | Place of Birth
        $ry += 8;
        $this->colHead($pdf, $L + $p, $ry, 104, 'Middle Name');
        $this->colHead($pdf, $L + 108, $ry, 78, 'Place of Birth (Municipality/City)');
        $ry += 3.5;
        $this->box($pdf, strtoupper($d['pi_middle_name'] ?? ''), $L + $p, $ry, 104, 5.5);
        $this->box($pdf, $d['pi_place_of_birth'] ?? '', $L + 108, $ry, 78, 5.5);

        // Extension | Religion
        $ry += 8;
        $this->colHead($pdf, $L + $p, $ry, 56, 'Extension Name e.g. Jr., III (If applicable)');
        $this->colHead($pdf, $L + 108, $ry, 78, 'Religion');
        $ry += 3.5;
        $this->box($pdf, $d['pi_extension'] ?? '', $L + $p, $ry, 56, 5.5);
        $this->box($pdf, $d['religion_name'] ?? '', $L + 108, $ry, 78, 5.5);

        // IP Community | Mother Tongue
        $ry += 8;
        $this->font($pdf, '', self::FSS);
        $pdf->SetXY($L + $p, $ry);
        $pdf->MultiCell(103, 3.5,
            'Belonging to any Indigenous Peoples (IP) Community/Indigenous Cultural Community?', 0, 'L');
        $this->colHead($pdf, $L + 108, $ry, 78, 'Mother Tongue');
        $this->box($pdf, $d['mother_tongue_name'] ?? '', $L + 108, $ry + 3.5, 78, 5.5);

        $isIP = !empty($d['indigenous_group_name']) &&
                !in_array(strtoupper($d['indigenous_group_name'] ?? ''), ['NONE', 'N/A', 'NOT APPLICABLE', '']);
        $ry += 7.5;
        $this->cb($pdf, $L + $p, $ry, 'Yes', $isIP, 13);
        $this->cb($pdf, $L + 20, $ry, 'No', !$isIP, 13);
        $this->font($pdf, '', self::FSS);
        $pdf->SetXY($L + 36, $ry + 0.3);
        $specifyVal = $isIP ? ($d['indigenous_group_name'] ?? '') : '';
        $pdf->Cell(68, 3.5, 'If Yes, please specify: ' . $specifyVal, 'B');

        // 4Ps
        $ry += 6.5;
        $has4ps = !empty($d['ac_4ps_household_number']);
        $this->font($pdf, '', self::FSS);
        $pdf->SetXY($L + $p, $ry);
        $pdf->Cell(58, 3.5, 'Is your family a beneficiary of 4Ps?');
        $this->cb($pdf, $L + 60, $ry, 'Yes', $has4ps, 12);
        $this->cb($pdf, $L + 74, $ry, 'No', !$has4ps, 12);
        $ry += 4.5;
        $this->font($pdf, '', self::FSS);
        $pdf->SetXY($L + $p, $ry);
        $pdf->Cell(80, 3.5, 'If Yes, please write the 4Ps Household ID Number');
        $ry += 4;
        $this->box($pdf, $d['ac_4ps_household_number'] ?? '', $L + $p, $ry, 155, 5.5);

        // Current Address
        $ry += 7;
        $this->font($pdf, 'B', self::FSS);
        $pdf->SetXY($L + $p, $ry);
        $pdf->Cell(40, 3.5, 'Current Address');
        $ry += 4;
        $this->font($pdf, '', self::FSS);
        $pdf->SetXY($L + $p, $ry);     $pdf->Cell(22, 3, 'House No.');
        $pdf->SetXY($L + 27, $ry);     $pdf->Cell(42, 3, 'Sitio/Street Name');
        $pdf->SetXY($L + 114, $ry);    $pdf->Cell(30, 3, 'Barangay');
        $ry += 3.2;
        $this->box($pdf, $d['ca_house_number'] ?? '', $L + $p, $ry, 22, 5.5);
        $this->box($pdf, $d['ca_street_name'] ?? '', $L + 27, $ry, 84, 5.5);
        $this->box($pdf, $d['ca_barangay'] ?? '', $L + 114, $ry, 74, 5.5);

        // Current Address row 2
        $ry += 7;
        $this->font($pdf, '', self::FSS);
        $pdf->SetXY($L + $p, $ry);     $pdf->Cell(45, 3, 'Municipality/City');
        $pdf->SetXY($L + 60, $ry);     $pdf->Cell(30, 3, 'Province');
        $pdf->SetXY($L + 100, $ry);    $pdf->Cell(28, 3, 'Country');
        $pdf->SetXY($L + 155, $ry);    $pdf->Cell(30, 3, 'Zip Code');
        $ry += 3.2;
        $this->box($pdf, $d['ca_municipality'] ?? '', $L + $p, $ry, 55, 5.5);
        $this->box($pdf, $d['ca_provice'] ?? '', $L + 60, $ry, 36, 5.5);
        $this->box($pdf, $d['ca_country'] ?? 'Philippines', $L + 100, $ry, 50, 5.5);
        $this->box($pdf, (string)($d['ca_zipcode'] ?? ''), $L + 155, $ry, 33, 5.5);

        // ── Permanent Address block ────────────────────────────────────
        $y = $bY + 108 + 1;
        $pdf->Rect($L, $y, $W, 23, 'D');

        $sameAddr = ($d['ca_barangay'] ?? '') !== '' &&
                    ($d['ca_barangay'] ?? '') === ($d['pa_barangay'] ?? '') &&
                    ($d['ca_municipality'] ?? '') === ($d['pa_municipality'] ?? '');

        $this->font($pdf, 'B', self::FSS);
        $pdf->SetXY($L + $p, $y + $p);
        $pdf->Cell(36, 3.5, 'Permanent Address');
        $this->font($pdf, '', self::FSS);
        $pdf->SetXY($L + 40, $y + $p);
        $pdf->Cell(54, 3.5, 'Same with your Current Address?');
        $this->cb($pdf, $L + 96, $y + $p, 'Yes', $sameAddr, 12);
        $this->cb($pdf, $L + 111, $y + $p, 'No', !$sameAddr, 40);
        $pdf->SetXY($L + 127, $y + $p);
        $pdf->Cell(58, 3.5, 'If Yes, proceed to item 4');

        $ry = $y + 7;
        $this->font($pdf, '', self::FSS);
        $pdf->SetXY($L + $p, $ry);     $pdf->Cell(22, 3, 'House No.');
        $pdf->SetXY($L + 27, $ry);     $pdf->Cell(42, 3, 'Sitio/Street Name');
        $pdf->SetXY($L + 114, $ry);    $pdf->Cell(30, 3, 'Barangay');
        $ry += 3.2;
        $this->box($pdf, $d['pa_house_number'] ?? '', $L + $p, $ry, 22, 5.5);
        $this->box($pdf, $d['pa_street_name'] ?? '', $L + 27, $ry, 84, 5.5);
        $this->box($pdf, $d['pa_barangay'] ?? '', $L + 114, $ry, 74, 5.5);

        $ry += 7;
        $this->font($pdf, '', self::FSS);
        $pdf->SetXY($L + $p, $ry);     $pdf->Cell(45, 3, 'Municipality/City');
        $pdf->SetXY($L + 60, $ry);     $pdf->Cell(30, 3, 'Province');
        $pdf->SetXY($L + 100, $ry);    $pdf->Cell(28, 3, 'Country');
        $pdf->SetXY($L + 155, $ry);    $pdf->Cell(30, 3, 'Zip Code');
        $ry += 3.2;
        $this->box($pdf, $d['pa_municipality'] ?? '', $L + $p, $ry, 55, 5.5);
        $this->box($pdf, $d['pa_province'] ?? '', $L + 60, $ry, 36, 5.5);
        $this->box($pdf, $d['pa_country'] ?? 'Philippines', $L + 100, $ry, 50, 5.5);
        $this->box($pdf, (string)($d['pa_zip_code'] ?? ''), $L + 155, $ry, 33, 5.5);

        // ── Section 4 — Parents/Guardian ──────────────────────────────
        $y = $y + 23 + 1;
        $this->font($pdf, 'B', self::FS);
        $pdf->SetXY($L, $y);
        $pdf->Cell($W, 5, "4. Parent's/Guardian's Information");

        $y += 5.5;
        $pdf->Rect($L, $y, $W, 52, 'D');

        $parents = [
            ["Father's Name",        'fi_'],
            ["Mother's Maiden Name",  'mi_'],
            ["Legal Guardian's Name", 'gi_'],
        ];
        $ry = $y;
        foreach ($parents as [$label, $pfx]) {
            $this->font($pdf, 'B', self::FSS);
            $pdf->SetXY($L + $p, $ry + $p);
            $pdf->Cell(50, 3.5, $label);
            $ry += 5.5;
            $this->font($pdf, '', self::FSS);
            foreach ([
                [$L + $p,  47, 'Last Name'],
                [$L + 50,  47, 'First Name'],
                [$L + 100, 42, 'Middle Name'],
                [$L + 145, 42, 'Contact Number'],
            ] as [$cx, $cw, $ch]) {
                $pdf->SetXY($cx, $ry);
                $pdf->Cell($cw, 3.2, $ch, 0);
            }
            $ry += 3.5;
            $this->box($pdf, strtoupper($d[$pfx.'last_name'] ?? ''), $L + $p, $ry, 46, 5.5);
            $this->box($pdf, strtoupper($d[$pfx.'first_name'] ?? ''), $L + 50, $ry, 47, 5.5);
            $this->box($pdf, strtoupper($d[$pfx.'middle_name'] ?? ''), $L + 100, $ry, 42, 5.5);
            $this->box($pdf, $d[$pfx.'contact_number'] ?? '', $L + 145, $ry, 43, 5.5);
            $ry += 7.5;
        }
    }

    // ════════════════════════════════════════════════════════════════════
    //  ENROLLMENT — PAGE 2   (Sections 5-8 + Certification)
    // ════════════════════════════════════════════════════════════════════

    private function renderEnrollmentPage2(\TCPDF $pdf, array $d): void
    {
        $L = self::L;
        $W = self::W;
        $y = 10;

        // ── Section 5 — Special Needs ─────────────────────────────────
        $snep = !empty($d['snep_a1_diagnosis']) &&
                !in_array(strtoupper($d['snep_a1_diagnosis'] ?? ''), ['NONE', '']);
        $this->font($pdf, 'B', self::FS);
        $pdf->SetXY($L, $y);
        $pdf->Cell(130, 5, '5. Is the Learner under the Special Needs Education Program?');
        $this->cb($pdf, $L + 133, $y + 1, 'Yes', $snep, 14);
        $this->cb($pdf, $L + 150, $y + 1, 'No', !$snep, 14);

        $y += 6;
        $this->font($pdf, '', self::FS);
        $pdf->SetXY($L, $y);
        $pdf->Cell($W, 4, 'If Yes, check only 1, either from a1 or a2');

        $y += 5;
        $this->font($pdf, 'BI', self::FS);
        $pdf->SetXY($L, $y);
        $pdf->Cell($W, 4, 'a1. With Diagnosis from Licensed Medical Specialist:');

        $y += 5;
        $diag = strtoupper($d['snep_a1_diagnosis'] ?? '');

        $leftDiag = [
            'ADHD'  => 'Attention Deficit Hyperactivity Disorder',
            'ASD'   => 'Autism Spectrum Disorder',
            'CP'    => 'Cerebral Palsy',
            'E-B D' => 'Emotional-Behavior Disorder',
            'HI'    => 'Hearing Impairment',
        ];
        $midDiag = [
            'ID'    => 'Intellectual Disability',
            'LD'    => 'Learning Disability',
            'MD'    => 'Multiple Disabilities',
            'O/P H' => 'Orthopedic/Physical Handicap',
            'S/L D' => 'Speech/Language Disorder',
        ];
        $ry = $y;
        foreach ($leftDiag as $k => $v) {
            $this->cb($pdf, $L, $ry, $v, str_contains($diag, $k), 62);
            $ry += 5;
        }
        $ry = $y;
        foreach ($midDiag as $k => $v) {
            $this->cb($pdf, $L + 65, $ry, $v, str_contains($diag, $k), 62);
            $ry += 5;
        }

        // Right column — SHP/CD + VI
        $shpSub = strtoupper($d['snep_a1_sub_shpcd'] ?? '');
        $viSub  = strtoupper($d['snep_a1_sub_vi'] ?? '');
        $this->cb($pdf, $L + 132, $y, 'Special Health Problem/Chronic Disease',
            str_contains($diag, 'SHP/CD'), 56);
        $this->font($pdf, '', self::FSS);
        $pdf->SetXY($L + 140, $y + 5.5);
        $pdf->Cell(55, 3, 'Cancer');
        $this->cb($pdf, $L + 140, $y + 5, 'Cancer', $shpSub === 'CANCER', 18);
        $this->cb($pdf, $L + 160, $y + 5, 'Non-Cancer', $shpSub === 'NON-CANCER', 26);
        $this->cb($pdf, $L + 132, $y + 11, 'Visual Impairment', str_contains($diag, 'VI'), 56);
        $this->cb($pdf, $L + 140, $y + 16, 'Blind', $viSub === 'BLIND', 14);
        $this->cb($pdf, $L + 160, $y + 16, 'Low Vision', $viSub === 'LOW-VISION', 26);

        // a2 Manifestations
        $y += 28;
        $this->font($pdf, 'BI', self::FS);
        $pdf->SetXY($L, $y);
        $pdf->Cell($W, 4, 'a2. With Manifestations');

        $y += 5;
        $manif = strtoupper($d['snep_a2_manifestations'] ?? '');
        $leftManif = [
            'DiAK'  => 'Difficulty in Applying Knowledge',
            'DiC'   => 'Difficulty in Communicating',
            'DiDIB' => 'Difficulty in Displaying Interpersonal Behavior\n(Emotional and Behavioral)',
            'DiH'   => 'Difficulty in Hearing',
        ];
        $rightManif = [
            'DiM'      => 'Difficulty in Mobility (Walking, Climbing and Grasping)',
            'DiPAS'    => 'Difficulty in Performing Adaptive Skills (Self-Care)',
            'DiRCPAaU' => 'Difficulty in Remembering, Concentrating,\nPaying Attention and Understanding',
            'DiS'      => 'Difficulty in Seeing',
        ];
        $ry = $y;
        foreach ($leftManif as $k => $v) {
            $this->cb($pdf, $L, $ry, str_replace('\n', "\n", $v), str_contains($manif, $k), 90);
            $ry += 6;
        }
        $ry = $y;
        foreach ($rightManif as $k => $v) {
            $this->cb($pdf, $L + 95, $ry, str_replace('\n', "\n", $v), str_contains($manif, $k), 90);
            $ry += 6;
        }

        // PWD ID
        $y += 26;
        $pwdId = !empty($d['snep_pwd_id']) && $d['snep_pwd_id'] != 0;
        $this->font($pdf, 'B', self::FS);
        $pdf->SetXY($L, $y);
        $pdf->Cell(75, 5, 'b. Does the Learner have a PWD ID?');
        $this->cb($pdf, $L + 77, $y + 1, 'Yes', $pwdId, 14);
        $this->cb($pdf, $L + 93, $y + 1, 'No', !$pwdId, 14);

        // ── Section 6 — Returning Learner ────────────────────────────
        $y += 7;
        $this->font($pdf, 'B', self::FS);
        $pdf->SetXY($L, $y);
        $pdf->Cell($W, 5, '6. For Returning Learner (Balik-Aral) and those who will Transfer/Move In');
        $y += 5.5;
        $pdf->Rect($L, $y, $W, 22, 'D');

        $this->colHead($pdf, $L + 1.5, $y + 1.5, 90, 'Last Grade Level Completed');
        $this->colHead($pdf, $L + 95, $y + 1.5, 90, 'Last School Year Completed');
        $this->box($pdf, $d['rl_last_grade_level_completed'] ?? '', $L + 1.5, $y + 6, 90, 5.5);
        $this->box($pdf, $d['rl_last_school_year_completed'] ?? '', $L + 95, $y + 6, 90, 5.5);

        $this->colHead($pdf, $L + 1.5, $y + 13, 90, 'Last School Attended');
        $this->font($pdf, '', self::FSS);
        $pdf->SetXY($L + 95, $y + 13);
        $pdf->Cell(18, 3.5, 'School ID');
        $this->box($pdf, $d['rl_school_attended'] ?? '', $L + 1.5, $y + 17, 90, 4.5);
        $this->box($pdf, (string)($d['rl_school_id'] ?? ''), $L + 115, $y + 13, 73, 9);

        // ── Section 7 — Senior High School ───────────────────────────
        $y += 24;
        $this->font($pdf, 'B', self::FS);
        $pdf->SetXY($L, $y);
        $pdf->Cell($W, 5, '7. For Learner in Senior High School');
        $y += 5.5;
        $pdf->Rect($L, $y, $W, 25, 'D');

        $this->font($pdf, '', self::FS);
        $pdf->SetXY($L + 1.5, $y + 2);
        $pdf->Cell(20, 4.5, 'Semester');
        $this->cb($pdf, $L + 23, $y + 2, '1st', false, 12);
        $this->cb($pdf, $L + 39, $y + 2, '2nd', false, 12);

        $this->font($pdf, '', self::FS);
        $pdf->SetXY($L + 1.5, $y + 10);
        $pdf->Cell(14, 4, 'Track:');
        $pdf->Line($L + 18, $y + 13.5, $L + $W - 1.5, $y + 13.5);

        $pdf->SetXY($L + 1.5, $y + 17);
        $pdf->Cell(14, 4, 'Strand:');
        $pdf->Line($L + 18, $y + 20.5, $L + $W - 1.5, $y + 20.5);

        // ── Section 8 — Learning Modalities ──────────────────────────
        $y += 28;
        $this->font($pdf, 'B', self::FS);
        $pdf->SetXY($L, $y);
        $pdf->MultiCell($W, 4,
            '8. If the school will implement other distance learning modalities aside from face-to-face ' .
            'instruction, what would you prefer for your child?', 0, 'L');
        $y += 9;
        $pdf->Rect($L, $y, $W, 15, 'D');

        $this->font($pdf, '', self::FS);
        $pdf->SetXY($L + 1.5, $y + 2);
        $pdf->Cell(32, 4, 'Check all that applies:');

        $mod = strtoupper($d['li_learning_modality'] ?? '');
        $row1 = [
            'BLENDED (COMBINATION)'  => 'Blended (Combination)',
            'HOMESCHOOLING'          => 'Homeschooling',
            'MODULAR (PRINT)'        => 'Modular (Print)',
            'RADIO-BASED TELEVISION' => 'Radio-Based Television',
        ];
        $row2 = [
            'EDUCATIONAL TELEVISION' => 'Educational Television',
            'MODULAR (DIGITAL)'      => 'Modular (Digital)',
            'ONLINE'                 => 'Online',
        ];
        $r1x = [$L + 1.5, $L + 49, $L + 96, $L + 142];
        $r2x = [$L + 1.5, $L + 49, $L + 96];
        $i = 0;
        foreach ($row1 as $k => $v) {
            $this->cb($pdf, $r1x[$i], $y + 7, $v, str_contains($mod, $k), 44);
            $i++;
        }
        $i = 0;
        foreach ($row2 as $k => $v) {
            $this->cb($pdf, $r2x[$i], $y + 12, $v, str_contains($mod, $k), 44);
            $i++;
        }

        // ── Certification ─────────────────────────────────────────────
        $y += 18;
        $this->font($pdf, '', 7.5);
        $pdf->SetXY($L, $y);
        $pdf->MultiCell($W, 4,
            'I hereby certify that the above information given are true and correct to the best of my knowledge ' .
            'and I allow the Department of Education to process the learner\'s personal information to create ' .
            'and/or update his/her learner profile in the Learner Information System.', 0, 'J');

        $y += 14;
        $pdf->SetXY($L, $y);
        $pdf->MultiCell($W, 4,
            'The personal information herein shall be treated as confidential in compliance with the Data Privacy Act of 2012.',
            0, 'J');

        $y += 18;
        $pdf->Line($L, $y, $L + 95, $y);
        $pdf->Line($L + 115, $y, $L + $W, $y);
        $this->font($pdf, '', self::FSS);
        $pdf->SetXY($L, $y + 1.5);
        $pdf->Cell(95, 4, 'Signature Over Printed Name of Parent/Guardian', 0, 0, 'C');
        $pdf->SetXY($L + 115, $y + 1.5);
        $pdf->Cell(71, 4, 'Date', 0, 0, 'C');
    }

    // ════════════════════════════════════════════════════════════════════
    //  MEDICAL PAGE   (SHD Form 1 — exact match to official form)
    // ════════════════════════════════════════════════════════════════════

    private function renderMedicalPage(\TCPDF $pdf, array $d): void
    {
        $L = self::L;
        $W = self::W;
        $p = 1.5;

        // ── Header ────────────────────────────────────────────────────
        $logo = __DIR__ . '/../../style/logo.png';
        if (file_exists($logo)) {
            $pdf->Image($logo, $L, 6, 14, 14, 'PNG');
            $pdf->Image($logo, $L + $W - 14, 6, 14, 14, 'PNG');
        }

        $this->font($pdf, '', 6.5);
        $pdf->SetXY($L, 6);
        $pdf->Cell($W, 4, 'SHD Form 1 (Revised @CAR/SDO BAGUIO CITY)', 0, 0, 'R');

        $this->font($pdf, 'B', 14);
        $pdf->SetXY($L, 11);
        $pdf->Cell($W, 7, 'GIBRALTAR ELEMENTARY SCHOOL', 0, 0, 'C');

        $y = 19;
        $pdf->Line($L, $y, $L + $W, $y);
        $y += 1.5;

        // ── Data Privacy Notice ───────────────────────────────────────
        $this->font($pdf, 'B', 7.5);
        $pdf->SetXY($L, $y);
        $pdf->Cell($W, 4.5, 'DATA PRIVACY NOTICE', 0, 0, 'C');
        $y += 5;

        $this->font($pdf, '', 6.3);
        $pdf->SetXY($L, $y);
        $pdf->MultiCell($W, 3.2,
            "The Department of Education shall engage in the collection of health / medical information for the purposes of tracking, provision of necessary health / medical interventions, and educational purposes.\n" .
            "This information shall be processed in accordance with the provisions of the Data Privacy Act and the Data Privacy Policies of the Department.\n" .
            "This information shall be stored and held confidentially in accordance with the provisions of the Basic Education Act and may only be shared with other government agencies or third parties subject to Data sharing agreements and data privacy requirements for legitimate purposes only.\n" .
            "For inquiries, requests and concerns regarding your data privacy rights, please contact the data privacy compliance officer, team of the school, schools division office or regional office concerned.",
            0, 'J');
        $y += 17;
        $pdf->Line($L, $y, $L + $W, $y);
        $y += 1.5;

        // ── Medical History heading ───────────────────────────────────
        $this->font($pdf, 'BU', 9);
        $pdf->SetXY($L, $y);
        $pdf->Cell($W, 5, 'MEDICAL HISTORY', 0, 0, 'C');
        $y += 6;

        // ── Student info table ────────────────────────────────────────
        $bdate = '';
        if (!empty($d['pi_birth_date'])) {
            $ts = strtotime($d['pi_birth_date']);
            if ($ts) $bdate = date('m/d/Y', $ts);
        }
        $age  = !empty($d['pi_birth_date']) ? (string)$this->calcAge($d['pi_birth_date']) : '';
        $sex  = strtoupper($d['pi_sex'] ?? '');
        $name = trim(strtoupper($d['pi_first_name'] ?? '') . ' ' .
                     strtoupper($d['pi_middle_name'] ?? '') . ' ' .
                     strtoupper($d['pi_last_name'] ?? ''));
        $guardian = trim(
            strtoupper($d['fi_first_name'] ?? $d['gi_first_name'] ?? '') . ' ' .
            strtoupper($d['fi_last_name']  ?? $d['gi_last_name']  ?? '')
        );
        $address = trim(
            ($d['ca_house_number'] ?? '') . ' ' . ($d['ca_street_name'] ?? '') .
            ', ' . ($d['ca_barangay'] ?? '') . ', ' . ($d['ca_municipality'] ?? '')
        );
        $contact = $d['fi_contact_number'] ?? $d['gi_contact_number'] ?? '';

        // Row 1: Name | Grade
        $h = 7;
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Rect($L, $y, $W, $h, 'D');
        $this->font($pdf, '', self::FSS);
        $pdf->SetXY($L + $p, $y + $p);  $pdf->Cell(22, 3.5, 'Name of learner');
        $this->box($pdf, $name, $L + 22, $y + $p, 118, 5);
        $pdf->SetXY($L + 143, $y + $p); $pdf->Cell(12, 3.5, 'Grade');
        $this->box($pdf, $d['ed_grade_level'] ?? '', $L + 154, $y + $p, 30, 5);
        $y += $h;

        // Row 2: DOB | Age | Sex | Guardian
        $pdf->Rect($L, $y, $W, $h, 'D');
        $pdf->SetXY($L + $p, $y + $p);  $pdf->Cell(20, 3.5, 'Date of Birth');
        $this->box($pdf, $bdate, $L + 20, $y + $p, 48, 5);
        $pdf->SetXY($L + 70, $y + $p);  $pdf->Cell(8, 3.5, 'Age.');
        $this->box($pdf, $age, $L + 77, $y + $p, 14, 5);
        $pdf->SetXY($L + 93, $y + $p);  $pdf->Cell(8, 3.5, 'Sex');
        $this->box($pdf, $sex, $L + 100, $y + $p, 18, 5);
        $pdf->SetXY($L + 121, $y + $p); $pdf->Cell(28, 3.5, 'Name of parent/ guardian');
        $this->box($pdf, $guardian, $L + 149, $y + $p, 35, 5);
        $y += $h;

        // Row 3: Address | Contact
        $pdf->Rect($L, $y, $W, $h, 'D');
        $pdf->SetXY($L + $p, $y + $p);  $pdf->Cell(16, 3.5, 'Address');
        $this->box($pdf, $address, $L + 16, $y + $p, 98, 5);
        $pdf->SetXY($L + 117, $y + $p); $pdf->Cell(22, 3.5, 'Contact Number');
        $this->box($pdf, $contact, $L + 138, $y + $p, 46, 5);
        $y += $h + 2;

        // ── Instruction ───────────────────────────────────────────────
        $this->font($pdf, 'B', self::FS);
        $pdf->SetXY($L, $y);
        $pdf->MultiCell($W, 3.8,
            'Instruction: Please put a check (/) on appropriate items and fill up blanks as indicated', 0, 'L');
        $y += 5;

        // ── Q1: Allergies ─────────────────────────────────────────────
        $this->font($pdf, '', self::FS);
        $pdf->SetXY($L, $y);
        $pdf->Cell(58, 4, '1.  Does your child/ward have an allergies?');
        $hasAllergy = !empty($d['mf_a_medicine']) || !empty($d['mf_a_pollen']) ||
                      !empty($d['mf_a_food'])    || !empty($d['mf_a_others']);
        $this->cb($pdf, $L + 58, $y + 0.5, 'Yes', $hasAllergy, 10);
        $this->cb($pdf, $L + 72, $y + 0.5, 'No', !$hasAllergy, 62);
        $pdf->SetXY($L + 82, $y);
        $pdf->Cell(100, 4, 'if Yes, please identify below.');
        $y += 5;

        $pdf->Rect($L, $y, $W, 14, 'D');
        // Medicine | Pollen
        $this->font($pdf, '', self::FSS);
        $pdf->SetXY($L + $p, $y + $p);
        $pdf->Cell(24, 3.5, 'Medicine: Specify');
        $pdf->Line($L + 27, $y + $p + 3.5, $L + 90, $y + $p + 3.5);
        $this->font($pdf, '', 7);
        $pdf->SetXY($L + 29, $y + $p);
        $pdf->Cell(61, 3.5, $d['mf_a_medicine'] ?? '');

        $this->font($pdf, '', self::FSS);
        $pdf->SetXY($L + $p, $y + 8);
        $pdf->Cell(12, 3.5, 'Pollen');
        $pdf->Line($L + 14, $y + 11.5, $L + 90, $y + 11.5);
        $this->font($pdf, '', 7);
        $pdf->SetXY($L + 16, $y + 8);
        $pdf->Cell(74, 3.5, $d['mf_a_pollen'] ?? '');

        // Food | Others
        $this->font($pdf, '', self::FSS);
        $pdf->SetXY($L + 95, $y + $p);
        $pdf->Cell(22, 3.5, 'Food: Specify');
        $pdf->Line($L + 109, $y + $p + 3.5, $L + $W - $p, $y + $p + 3.5);
        $this->font($pdf, '', 7);
        $pdf->SetXY($L + 111, $y + $p);
        $pdf->Cell(77, 3.5, $d['mf_a_food'] ?? '');

        $this->font($pdf, '', self::FSS);
        $pdf->SetXY($L + 95, $y + 8);
        $pdf->Cell(14, 3.5, 'Others:');
        $pdf->Line($L + 109, $y + 11.5, $L + $W - $p, $y + 11.5);
        $this->font($pdf, '', 7);
        $pdf->SetXY($L + 111, $y + 8);
        $pdf->Cell(77, 3.5, $d['mf_a_others'] ?? '');
        $y += 16;

        // ── Q2: Ongoing Medical Conditions ────────────────────────────
        $this->font($pdf, '', self::FS);
        $pdf->SetXY($L, $y);
        $pdf->Cell(106, 4, '2.  Does your child/ward have any ongoing medical condition?');
        $hasMed = !empty($d['mf_o_medical_conditions']);
        $this->cb($pdf, $L + 107, $y + 0.5, 'Yes', $hasMed, 12);
        $this->cb($pdf, $L + 121, $y + 0.5, 'No.', !$hasMed, 30);
        $y += 5;
        $this->font($pdf, '', self::FSS);
        $pdf->SetXY($L + 4, $y);
        $pdf->Cell(50, 3.5, 'If Yes, please identify below:');
        $y += 4.5;

        $pdf->Rect($L, $y, $W, 24, 'D');
        $conds = strtoupper($d['mf_o_medical_conditions'] ?? '');
        $condL = [
            'ERROR OF REFRACTION' => 'Error of refraction (Eye ailment)',
            'ASTHMA'              => 'Asthma (Lung ailment)',
            'SEIZURE'             => 'Seizure (Convulsions)',
            'HEART ILLNESS'       => 'Heart illness',
        ];
        $condR = [
            'ANEMIA'               => 'Anemia',
            'BLEEDING DISORDER'    => 'Bleeding disorder',
            'FRACTURE/DISLOCATION' => 'Fracture/dislocation',
            'OTHERS'               => 'Others',
        ];
        $ry = $y + $p;
        foreach ($condL as $k => $v) {
            $this->cb($pdf, $L + $p, $ry, $v, str_contains($conds, $k), 88);
            $ry += 5.5;
        }
        $ry = $y + $p;
        foreach ($condR as $k => $v) {
            $this->cb($pdf, $L + 95, $ry, $v, str_contains($conds, $k), 88);
            $ry += 5.5;
        }
        $y += 26;

        // ── Q3: Surgery / Hospitalization ────────────────────────────
        $this->font($pdf, '', self::FS);
        $pdf->SetXY($L, $y);
        $pdf->Cell(108, 4, '3.  Did your child/ward ever have surgery/ hospitalization?');
        $hasSurg = !empty($d['mf_sh_hospital_name']) || !empty($d['mf_sh_surgery_date']);
        $this->cb($pdf, $L + 109, $y + 0.5, 'Yes', $hasSurg, 12);
        $this->cb($pdf, $L + 123, $y + 0.5, 'No.', !$hasSurg, 30);
        $y += 5;

        $surgDetail = trim(
            ($d['mf_sh_surgery_date'] ?? '') . ' ' .
            ($d['mf_sh_hospital_name'] ?? '') . ' ' .
            ($d['mf_sh_bodypart_affected'] ?? '')
        );
        $this->font($pdf, '', self::FSS);
        $pdf->SetXY($L + 4, $y);
        $pdf->Cell(90, 3.5, 'If yes, please specify details: (when/where/what part of the body)');
        $pdf->Line($L + 93, $y + 3.8, $L + $W, $y + 3.8);
        $this->font($pdf, '', 7);
        $pdf->SetXY($L + 95, $y);
        $pdf->Cell(90, 3.5, $surgDetail);
        $y += 7;

        // ── Q4: Treatment / Medicines ─────────────────────────────────
        $this->font($pdf, '', self::FS);
        $pdf->SetXY($L, $y);
        $pdf->Cell(98, 4, '4.  Is your child currently taking treatment/medicines?');
        $hasTreat = !empty($d['mf_tm_type']);
        $this->cb($pdf, $L + 99, $y + 0.5, 'Yes', $hasTreat, 12);
        $this->cb($pdf, $L + 113, $y + 0.5, 'No.', !$hasTreat, 30);
        $y += 5;

        $this->font($pdf, '', self::FSS);
        $pdf->SetXY($L + 4, $y);
        $pdf->Cell(50, 3.5, 'If yes, please specify as to:');
        $y += 4;
        $pdf->SetXY($L + 4, $y);
        $pdf->Cell(38, 3.5, 'Kind of treatment/medicine:');
        $pdf->Line($L + 42, $y + 3.8, $L + 110, $y + 3.8);
        $this->font($pdf, '', 7);
        $pdf->SetXY($L + 44, $y);
        $pdf->Cell(66, 3.5, $d['mf_tm_type'] ?? '');

        $this->font($pdf, '', self::FSS);
        $pdf->SetXY($L + 113, $y);
        $pdf->Cell(26, 3.5, 'Schedule/ dosage:');
        $pdf->Line($L + 137, $y + 3.8, $L + $W, $y + 3.8);
        $this->font($pdf, '', 7);
        $pdf->SetXY($L + 139, $y);
        $pdf->Cell(47, 3.5, $d['mf_tm_dosage_schedule'] ?? '');
        $y += 8;

        // ── Q5: Family History ────────────────────────────────────────
        $this->font($pdf, '', self::FS);
        $pdf->SetXY($L, $y);
        $pdf->Cell($W, 4, '5.  Does your family have a history of the following conditions:');
        $y += 5;

        $pdf->Rect($L, $y, $W, 24, 'D');
        $famHist = strtoupper($d['mf_mc_conditions'] ?? '');
        $cancerType = $d['mf_mc_cancer_type'] ?? '';
        $histL = [
            'TUBERCOLOSIS'    => 'Tuberculosis',
            'CANCER'          => 'Cancer, what kind? ' . $cancerType,
            'DIABETES MELLITUS' => 'Diabetes Mellitus',
            'HYPERTENSION'    => 'Hypertension',
        ];
        $histR = [
            'STROKE/ HEART ATTACK' => 'Stroke/ Heart attack',
            'DEPRESSION'           => 'Depression',
            'KIDNEY PROBLEMS'      => 'Kidney problems',
            'OTHERS'               => 'Others: ' . ($d['mf_mc_others'] ?? ''),
        ];
        $ry = $y + $p;
        foreach ($histL as $k => $v) {
            $this->cb($pdf, $L + $p, $ry, $v, str_contains($famHist, $k), 88);
            $ry += 5.5;
        }
        $ry = $y + $p;
        foreach ($histR as $k => $v) {
            $this->cb($pdf, $L + 95, $ry, $v, str_contains($famHist, $k), 88);
            $ry += 5.5;
        }
        $y += 26;

        // ── Q6: Cigarette/Vape Exposure ──────────────────────────────
        $this->font($pdf, '', self::FS);
        $pdf->SetXY($L, $y);
        $pdf->Cell(117, 4, '6.  Does your child/ward have exposure to cigarette/vape smoke at home?');
        $hasExp = !empty($d['mf_exposure_c_v']) && $d['mf_exposure_c_v'] != 0;
        $this->cb($pdf, $L + 118, $y + 0.5, 'Yes', $hasExp, 10);
        $this->cb($pdf, $L + 131, $y + 0.5, 'No', !$hasExp, 10);
        $y += 6;

        // ── Q7: Other Pertinent Info ──────────────────────────────────
        $this->font($pdf, '', self::FS);
        $pdf->SetXY($L, $y);
        $pdf->Cell(52, 4, '7.  Other pertinent learner information:');
        $pdf->Line($L + 54, $y + 3.8, $L + $W, $y + 3.8);
        $this->font($pdf, '', 7);
        $pdf->SetXY($L + 56, $y);
        $pdf->Cell($W - 56, 4, $d['mf_o_pertinent_information'] ?? '');
        $y += 7;

        $pdf->Line($L, $y, $L + $W, $y);
        $y += 2;

        // ── Certification ─────────────────────────────────────────────
        $this->font($pdf, 'I', self::FS);
        $pdf->SetXY($L, $y);
        $pdf->MultiCell($W, 4,
            'I certify that the above information is correct and I hereby authorize the Department of Education ' .
            'to use, collect, and process the information for the purposes of the above stated.', 0, 'J');
        $y += 12;

        $pdf->Line($L, $y, $L + 70, $y);
        $pdf->Line($L + 120, $y, $L + $W, $y);
        $this->font($pdf, '', self::FSS);
        $pdf->SetXY($L, $y + 1.5);
        $pdf->Cell(70, 3.5, 'Parent/ Guardian Name and Signature', 0, 0, 'C');
        $pdf->SetXY($L + 120, $y + 1.5);
        $pdf->Cell(66, 3.5, 'Date', 0, 0, 'C');

        // ════════════════════════════════════════════════════════════
        //  CONSENT FORM  (second half of page)
        // ════════════════════════════════════════════════════════════
        $y += 12;
        $pdf->Line($L, $y, $L + $W, $y);
        $y += 2;

        $this->font($pdf, 'BU', 9);
        $pdf->SetXY($L, $y);
        $pdf->Cell($W, 5, "PARENTS/GUARDIAN'S CONSENT FORM", 0, 0, 'C');
        $y += 6;

        $this->font($pdf, '', self::FS);
        $pdf->SetXY($L, $y);
        $pdf->Cell($W, 4, 'Please put a check mark (✓) on the box provided for');
        $y += 5;

        $pdf->SetXY($L, $y);
        $pdf->Cell($W, 4, 'Title of School Activities:');
        $y += 5;

        // Activity checkboxes
        $activities = [
            'Enrolment to Philhealth Konsulta package',
            'Health/ Nutritional assessment      Weight ______   Height ______',
            'School Based Deworming (January and July of each year)',
            'Dental Assessment and Treatment',
            'School Based Immunization (Measles Containing Vaccine; Tetanus Toxoid for Grade 1 and 7, HPV vaccine grade 4 female)',
            'Weekly Iron Folic Acid Supplementation (for Grade 7 to 12 female learners)',
        ];
        foreach ($activities as $i => $act) {
            $this->cb($pdf, $L + 4, $y, $act, false, $W - 8);
            $y += 5;
            if ($i === 0) {
                // Philhealth sub-item
                $this->font($pdf, '', self::FSS);
                $pdf->SetXY($L + 14, $y);
                $pdf->Cell(64, 3.5, 'o  Philhealth ID number of member (parent/ guardian)');
                $pdf->Line($L + 80, $y + 3.5, $L + 145, $y + 3.5);
                $y += 5;
            }
        }

        $y += 1;
        $this->font($pdf, '', self::FS);
        $pdf->SetXY($L, $y);
        $pdf->Cell($W, 4, 'Date/s of Activity: SY 2025-2026');
        $y += 5.5;

        $this->font($pdf, '', self::FSS);
        $pdf->SetXY($L, $y);
        $pdf->MultiCell($W, 3.8,
            'As the parent/ guardian of the abovementioned learner, I hereby acknowledge that I have been informed of the ' .
            'details of these activities and voluntarily and freely elect to participate in this school health activities. ' .
            'Furthermore, I understand the risks associated with any activity and agree that the rules and regulations ' .
            'established for the said activities are for the safety and security of the participants, and thus agree to ' .
            'instruct my child or children to obey them.', 0, 'J');
        $y += 16;

        $pdf->SetXY($L, $y);
        $pdf->MultiCell($W, 3.8,
            'Having understood all the aforementioned, I hereby consent to allow my child or children to participate, ' .
            'acknowledging all of the foregoing.', 0, 'J');
        $y += 10;

        // Final signature lines
        $pdf->Line($L, $y, $L + 75, $y);
        $pdf->Line($L + 115, $y, $L + $W, $y);
        $this->font($pdf, '', self::FSS);
        $pdf->SetXY($L, $y + 1.5);
        $pdf->Cell(75, 3.5, 'Signature of Parent/Guardian', 0, 0, 'C');
        $pdf->SetXY($L + 115, $y + 1.5);
        $pdf->Cell(71, 3.5, 'Signature of Learner', 0, 0, 'C');
    }

    // ════════════════════════════════════════════════════════════════════
    //  Helper primitives
    // ════════════════════════════════════════════════════════════════════

    /** Set font shorthand */
    private function font(\TCPDF $pdf, string $style, float $size): void
    {
        $pdf->SetFont('helvetica', $style, $size);
    }

    /** Labelled field box with value filled in */
    private function box(\TCPDF $pdf, string $value, float $x, float $y, float $w, float $h): void
    {
        $pdf->SetDrawColor(120, 120, 120);
        $pdf->Rect($x, $y, $w, $h, 'D');
        $pdf->SetDrawColor(0, 0, 0);
        if ($value !== '') {
            $this->font($pdf, '', self::FS);
            $pdf->SetXY($x + 1, $y + 0.8);
            $pdf->Cell($w - 2, $h - 1, $value, 0, 0, 'L', false, '', 1);
        }
    }

    /** Small grey column header label */
    private function colHead(\TCPDF $pdf, float $x, float $y, float $w, string $label): void
    {
        $this->font($pdf, '', self::FSS);
        $pdf->SetXY($x, $y);
        $pdf->Cell($w, 3.5, $label, 0);
    }

    /** Checkbox square with X mark when checked, and label to the right */
    private function cb(\TCPDF $pdf, float $x, float $y, string $label,
                        bool $checked = false, float $lw = 60): void
    {
        $s = self::CB;
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect($x, $y, $s, $s, 'DF');
        if ($checked) {
            $pdf->Line($x + 0.4, $y + 0.4, $x + $s - 0.4, $y + $s - 0.4);
            $pdf->Line($x + $s - 0.4, $y + 0.4, $x + 0.4, $y + $s - 0.4);
        }
        $this->font($pdf, '', self::FS);
        $pdf->SetXY($x + $s + 1, $y + 0.2);
        $pdf->MultiCell($lw, $s, $label, 0, 'L', false, 0);
    }

    private function calcAge(string $birthDate): int
    {
        try {
            return (int)(new \DateTime($birthDate))->diff(new \DateTime())->y;
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
        return 'enrollment_' . $name . '.pdf';
    }
}