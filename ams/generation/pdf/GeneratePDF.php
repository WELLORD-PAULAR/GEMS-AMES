<?php
namespace Classes;

// autoload composer if available
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use mikehaertl\pdftk\Pdf;

class GeneratePDF {

    private array $templates = [
        'medical'    => 'medical_form_compressed.pdf',
        'enrollment' => 'enrollment_form_compressed.pdf',
        'combined'   => 'enrollment_and_medical_form_compressed.pdf',
    ];

    /**
     * @param array  $data  Field key-value pairs matching the target PDF's form fields.
     * @param string $type  'medical' | 'enrollment' | 'combined'  (defaults to 'medical')
     */
    public function generate(array $data, string $type = 'medical'): string {
        if (!array_key_exists($type, $this->templates)) {
            throw new \InvalidArgumentException(
                "Unknown form type '{$type}'. Valid types: " . implode(', ', array_keys($this->templates))
            );
        }

        $outputDir = __DIR__ . '/completed';
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $safeName = preg_replace(
            '/[^A-Za-z0-9_\-]/', '_',
            ($data['lrn'] ?? 'unknown') . '_' .
            ($data['first_name'] ?? 'unknown') . '_' .
            ($data['last_name'] ?? 'unknown')
        );

        $filename   = $type . '_' . $safeName . '.pdf';
        $outputPath = $outputDir . '/' . $filename;
        $formPath   = __DIR__ . '/templates/' . $this->templates[$type];

        if (!file_exists($formPath)) {
            throw new \RuntimeException("Template not found: {$formPath}");
        }

        $pdf = new Pdf($formPath);
        $pdf->fillForm($data);
        $pdf->flatten();

        if (!$pdf->saveAs($outputPath)) {
            $error = $pdf->getError();
            throw new \RuntimeException('PDF generation failed: ' . $error . '. Ensure pdftk binary is installed and in PATH.');
        }

        return $outputPath;
    }
}
?>