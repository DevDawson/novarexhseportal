<?php

namespace App\Filament\Resources\StaffResource\Pages;

use App\Filament\Resources\StaffResource;
use App\Filament\Resources\StaffResource\Concerns\MergesCertificateUploads;
use Filament\Resources\Pages\CreateRecord;

class CreateStaff extends CreateRecord
{
    use MergesCertificateUploads;

    protected static string $resource = StaffResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->mergeCertificateUploads($data);
    }
}
