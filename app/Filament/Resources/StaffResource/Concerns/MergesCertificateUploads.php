<?php

namespace App\Filament\Resources\StaffResource\Concerns;

use App\Services\CertificateMergeService;
use Illuminate\Support\Facades\Storage;

/**
 * Shared logic for CreateStaff and EditStaff pages: takes the
 * 'certificate_uploads' array from the form (temporary uploaded files,
 * not a real Staff column), merges them into a single PDF via
 * CertificateMergeService, stores the result path into
 * 'certificates_path', and removes the temporary field from the data
 * before it's saved to the Staff model.
 */
trait MergesCertificateUploads
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mergeCertificateUploads(array $data): array
    {
        $uploads = $data['certificate_uploads'] ?? [];
        unset($data['certificate_uploads']);

        if (empty($uploads)) {
            return $data;
        }

        try {
            $mergedPath = CertificateMergeService::merge($uploads);
            $data['certificates_path'] = $mergedPath;
        } catch (\Throwable $e) {
            \Filament\Notifications\Notification::make()
                ->title('Could not merge certificates')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }

        // Clean up the temporary source files now that they've been
        // merged into a single PDF (or failed - either way, they're
        // not needed after this point).
        foreach ($uploads as $tempPath) {
            Storage::disk('public')->delete($tempPath);
        }

        return $data;
    }
}
