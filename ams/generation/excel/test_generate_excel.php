<?php
require_once __DIR__ . '/GenerateExcel.php';

$g = new Classes\GenerateExcel();
$data = [
    'lrn' => '12345',
    'first_name' => 'Alice',
    'last_name' => 'Smith',
    'grade_level' => '01',
    'school_year' => '2026-2027'
];

try {
    $path = $g->generate($data, 'enrollment');
    echo "Generated: {$path}\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
