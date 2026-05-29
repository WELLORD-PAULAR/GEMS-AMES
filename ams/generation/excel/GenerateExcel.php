<?php
namespace Classes;

// autoload composer if available
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

class GenerateExcel {

    /**
     * Generate an Excel (XLSX) file from the provided data.
     * Uses SimpleXLSXGen when available, otherwise falls back to CSV.
     *
     * @param array $data Key => Value pairs to write
     * @param string $type used in filename (e.g. 'medical')
     * @return string path to generated file
     */
    public function generate(array $data, string $type = 'medical'): string {
        $outputDir = __DIR__ . '/completed';
        if (!is_dir($outputDir)) mkdir($outputDir, 0755, true);

        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_',
            ($data['lrn'] ?? 'unknown') . '_' . ($data['first_name'] ?? 'unknown') . '_' . ($data['last_name'] ?? 'unknown')
        );

        // Prepare filename and rows for XLSX/CSV
        $filename = $type . '_' . $safeName . '.xlsx';
        $path = $outputDir . '/' . $filename;

        $rows = [];
        $rows[] = ['Field', 'Value'];
        foreach ($data as $key => $value) {
            $rows[] = [$this->humanizeKey($key), (string)$value];
        }

        // Try SimpleXLSXGen first (may be autoloaded as SimpleXLSXGen or Shuchkin\SimpleXLSXGen\SimpleXLSXGen)
        if (class_exists('SimpleXLSXGen')) {
            $xlsx = \SimpleXLSXGen::fromArray($rows);
            $xlsx->saveAs($path);
            return $path;
        }
        if (class_exists('Shuchkin\\SimpleXLSXGen')) {
            $fqcn = 'Shuchkin\\SimpleXLSXGen';
            $xlsx = $fqcn::fromArray($rows);
            $xlsx->saveAs($path);
            return $path;
        }

        // Fallback to CSV
        $filename = $type . '_' . $safeName . '.csv';
        $csvPath = $outputDir . '/' . $filename;
        $fh = fopen($csvPath, 'w');
        if ($fh === false) throw new \RuntimeException('Unable to open file for writing: ' . $csvPath);
        foreach ($rows as $r) {
            fputcsv($fh, $r);
        }
        fclose($fh);
        return $csvPath;
    }

    private function humanizeKey(string $key): string {
        $key = preg_replace('/_+/', ' ', $key);
        $key = preg_replace('/([a-z])([A-Z])/', '$1 $2', $key);
        return ucwords(trim($key));
    }

    /**
     * Fetch JSON from local API endpoint under /WEBSYST1_FINAL/ams/api/records
     */
    private function fetchViaApi(string $endpoint, string $studentId): ?array {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $base = $scheme . '://' . $host;
        $url = $base . '/WEBSYST1_FINAL/ams/api/records/' . $endpoint . '?student_id=' . urlencode($studentId);
        $json = @file_get_contents($url);
        if (!$json) return null;
        $data = json_decode($json, true);
        return $data;
    }

    /**
     * Generate a file for a given student by fetching records from the API.
     */
    public function generateFromStudent(string $studentId, string $type = 'medical'): string {
        $school = $this->fetchViaApi('school_records.php', $studentId) ?: [];
        $medical = $this->fetchViaApi('medical_records.php', $studentId) ?: [];

        // prefer the latest school record and latest medical record
        $data = [];
        if (!empty($school) && is_array($school[0])) {
            foreach ($school[0] as $k => $v) $data[$k] = $v;
        }
        if (!empty($medical) && is_array($medical[0])) {
            foreach ($medical[0] as $k => $v) $data[$k] = $v;
        }

        return $this->generate($data, $type);
    }

    /**
     * Generate file for a student by reading records directly from DB.
     * @param \PDO $pdo
     */
    public function generateFromStudentDb(\PDO $pdo, string $studentId, string $type = 'medical'): string {
        $stmt = $pdo->prepare('SELECT * FROM student_school_records WHERE student_id = ? ORDER BY created_at DESC');
        $stmt->execute([$studentId]);
        $school = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare('SELECT * FROM student_medical_records WHERE student_id = ? ORDER BY created_at DESC');
        $stmt->execute([$studentId]);
        $medical = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $data = [];
        if (!empty($school) && is_array($school[0])) {
            foreach ($school[0] as $k => $v) $data[$k] = $v;
        }
        if (!empty($medical) && is_array($medical[0])) {
            foreach ($medical[0] as $k => $v) $data[$k] = $v;
        }

        return $this->generate($data, $type);
    }
}
