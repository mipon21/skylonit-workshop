<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader\PageBoundaries;

class ContractSigningService
{
    /** PDF uses points (pt); 1 mm = 72/25.4 pt */
    private const MM_TO_PT = 72.0 / 25.4;

    /**
     * Load contract config from file so changes take effect without running config:clear.
     *
     * @return array<string, mixed>
     */
    private function getContractConfig(): array
    {
        $path = config_path('contract.php');
        if (! is_file($path)) {
            return [];
        }
        $config = require $path;
        return is_array($config) ? ($config['signature'] ?? []) : [];
    }

    /**
     * Stamp signature (PNG image), signer name, date, and IP onto the last page of the PDF.
     * Returns the path to the saved signed PDF.
     * Config values are in mm; we convert to points when the page is in points (common for imported PDFs).
     */
    public function stampSignature(
        string $sourcePdfPath,
        string $signatureImagePath,
        string $signedBy,
        string $signedAt,
        string $ipAddress,
        string $outputStoragePath
    ): string {
        $fullPath = storage_path('app/' . $outputStoragePath);
        $dir = dirname($fullPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $cfg = $this->getContractConfig();

        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($sourcePdfPath);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $tplId = $pdf->importPage($pageNo, PageBoundaries::CROP_BOX);
            $size = $pdf->getTemplateSize($tplId);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId, 0, 0);

            $stampOnThisPage = $pageNo === 1 || $pageNo === 2 || $pageNo === $pageCount;
            if ($stampOnThisPage) {
                $pageHeight = (float) $size['height'];
                $isPageInPoints = $pageHeight > 400;
                $toUnit = $isPageInPoints ? self::MM_TO_PT : 1.0;

                $marginLeft = (float) ($cfg['margin_left'] ?? 15) * $toUnit;
                $marginBottom = (float) ($cfg['margin_bottom'] ?? 25) * $toUnit;
                $blockHeight = (float) ($cfg['block_height'] ?? 35) * $toUnit;
                $imgW = (float) ($cfg['image_width'] ?? 40) * $toUnit;
                $imgH = (float) ($cfg['image_height'] ?? 15) * $toUnit;
                $fontSize = (int) ($cfg['font_size'] ?? 9);

                $signedByX = (float) ($cfg['signed_by_x'] ?? 40) * $toUnit;
                $signedByYFromBottom = (float) ($cfg['signed_by_y_from_bottom'] ?? 25) * $toUnit;
                $dateX = (float) ($cfg['date_x'] ?? 40) * $toUnit;
                $dateYFromBottom = (float) ($cfg['date_y_from_bottom'] ?? 19) * $toUnit;
                $ipX = (float) ($cfg['ip_x'] ?? 40) * $toUnit;
                $ipYFromBottom = (float) ($cfg['ip_y_from_bottom'] ?? 13) * $toUnit;

                $pdf->SetFont('Helvetica', '', $fontSize);
                $pdf->SetTextColor(80, 80, 80);

                // Disable auto page break so our manually placed cells don't trigger new pages
                $pdf->SetAutoPageBreak(false);

                $y = $pageHeight - $marginBottom - $blockHeight;
                $pdf->SetXY($marginLeft, $y);

                if (is_file($signatureImagePath)) {
                    $pdf->Image($signatureImagePath, $marginLeft, $y, $imgW, $imgH);
                }

                // Use ln=0 so Cell does not advance cursor; we explicitly SetXY before each
                $pdf->SetXY($signedByX, $pageHeight - $signedByYFromBottom);
                $pdf->MultiCell(0, 4, "Signed by:\n" . $signedBy, 0, 0);
                $pdf->SetXY($dateX, $pageHeight - $dateYFromBottom);
                $pdf->Cell(0, 6, 'Date: ' . $signedAt, 0, 0);
                $pdf->SetXY($ipX, $pageHeight - $ipYFromBottom);
                $pdf->Cell(0, 6, 'IP: ' . $ipAddress, 0, 0);

                $pdf->SetAutoPageBreak(true);
            }
        }

        $pdf->Output('F', $fullPath);

        return $outputStoragePath;
    }
}
