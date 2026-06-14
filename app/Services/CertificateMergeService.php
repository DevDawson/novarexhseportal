<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use setasign\Fpdi\Fpdi;

/**
 * Merges multiple uploaded "certificate" files - which may be a mix of
 * PDFs (possibly multi-page) and images (JPG/PNG) - into a single PDF
 * document, so a staff member's professional certificates can be stored
 * and downloaded as one file.
 *
 * Requires:
 *   composer require setasign/fpdi setasign/fpdi-fpdf
 *
 * (Both packages are MIT/permissive-licensed and lightweight - no
 * conflict with the existing barryvdh/laravel-dompdf installation,
 * which is used for *generating* reports rather than merging uploads.)
 */
class CertificateMergeService
{
    /**
     * Merge the given files (paths relative to the 'public' disk) into
     * a single PDF, store it on the 'public' disk under
     * 'staff-documents/certificates/', and return its relative path.
     *
     * @param  array<int, string>  $relativePaths  Paths as returned by Filament FileUpload (relative to disk).
     * @return string  Relative path (on 'public' disk) to the merged PDF.
     */
    public static function merge(array $relativePaths): string
    {
        $pdf = new Fpdi();

        foreach ($relativePaths as $relativePath) {
            $absolutePath = Storage::disk('public')->path($relativePath);

            if (! file_exists($absolutePath)) {
                continue;
            }

            $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

            if ($extension === 'pdf') {
                self::appendPdf($pdf, $absolutePath);
            } elseif (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                self::appendImage($pdf, $absolutePath, $extension);
            }
            // Other file types are silently skipped - FileUpload should
            // already restrict accepted types to pdf/jpg/jpeg/png.
        }

        if ($pdf->PageNo() === 0) {
            throw new \RuntimeException('No valid PDF or image files were provided to merge.');
        }

        $outputRelativePath = 'staff-documents/certificates/'.Str::uuid().'.pdf';
        $outputAbsolutePath = Storage::disk('public')->path($outputRelativePath);

        // Ensure the destination directory exists.
        Storage::disk('public')->makeDirectory('staff-documents/certificates');

        $pdf->Output('F', $outputAbsolutePath);

        return $outputRelativePath;
    }

    /**
     * Append every page of an existing PDF file to the output document,
     * preserving each page's original size/orientation.
     */
    private static function appendPdf(Fpdi $pdf, string $absolutePath): void
    {
        $pageCount = $pdf->setSourceFile($absolutePath);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);

            $orientation = $size['width'] > $size['height'] ? 'L' : 'P';

            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
        }
    }

    /**
     * Add a single A4 page containing the given image, scaled to fit
     * within the page margins while preserving its aspect ratio.
     */
    private static function appendImage(Fpdi $pdf, string $absolutePath, string $extension): void
    {
        [$widthPx, $heightPx] = getimagesize($absolutePath);

        // A4 in mm, with a 10mm margin on all sides.
        $pageWidth = 210;
        $pageHeight = 297;
        $margin = 10;
        $maxWidth = $pageWidth - (2 * $margin);
        $maxHeight = $pageHeight - (2 * $margin);

        $imageRatio = $widthPx / $heightPx;
        $boxRatio = $maxWidth / $maxHeight;

        if ($imageRatio > $boxRatio) {
            // Image is wider relative to the page - fit to width.
            $drawWidth = $maxWidth;
            $drawHeight = $maxWidth / $imageRatio;
        } else {
            // Image is taller relative to the page - fit to height.
            $drawHeight = $maxHeight;
            $drawWidth = $maxHeight * $imageRatio;
        }

        $x = ($pageWidth - $drawWidth) / 2;
        $y = ($pageHeight - $drawHeight) / 2;

        $pdf->AddPage('P', 'A4');

        $type = $extension === 'jpg' ? 'JPEG' : strtoupper($extension);
        $pdf->Image($absolutePath, $x, $y, $drawWidth, $drawHeight, $type);
    }
}
