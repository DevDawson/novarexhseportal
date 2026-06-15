<?php

namespace App\Filament\Resources\EsiaScreeningResource\Pages;

use App\Filament\Resources\EsiaScreeningResource;
use Filament\Resources\Pages\CreateRecord;

/** @deprecated Replaced by CreateEiaScreenings — kept to satisfy autoloader */
class CreateEsiaScreenings extends CreateRecord
{
    protected static string $resource = EsiaScreeningResource::class;
}
