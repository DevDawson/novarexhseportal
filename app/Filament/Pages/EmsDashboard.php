<?php

namespace App\Filament\Pages;

use App\Services\EmsMaturityService;
use Filament\Pages\Page;

class EmsDashboard extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-beaker';
    protected static ?string $navigationLabel = 'EMS Dashboard';
    protected static ?string $navigationGroup = 'Environmental Management (EMS)';
    protected static ?int    $navigationSort  = 1;
    protected static string  $view            = 'filament.pages.ems-dashboard';

    public float  $emi   = 0.0;
    public string $level = 'Initial';
    public string $status = 'Significant Improvement Required';
    public string $color = 'danger';
    public array  $components = [];
    public array  $kpis       = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff', 'hse_manager', 'business_director', 'lead_auditor']) ?? false;
    }

    public function mount(): void
    {
        $result = EmsMaturityService::calculate();

        $this->emi        = $result['emi'];
        $this->level      = $result['level'];
        $this->status     = $result['status'];
        $this->color      = $result['color'];
        $this->components = $result['components'];

        $this->kpis = [
            '15.1' => ['label' => 'Objective Achievement Rate',    'value' => EmsMaturityService::kpi151(), 'target' => 90,  'lower_is_better' => false],
            '15.2' => ['label' => 'Improvement Action Closure Rate','value' => EmsMaturityService::kpi152(), 'target' => 90,  'lower_is_better' => false],
            '15.3' => ['label' => 'Repeat Environmental Incident Rate','value' => EmsMaturityService::kpi153(), 'target' => 0, 'lower_is_better' => true],
            '15.4' => ['label' => 'EMS Maturity Index',            'value' => $result['emi'],                'target' => 90,  'lower_is_better' => false],
        ];
    }

    public function getTitle(): string
    {
        return 'EMS Dashboard — ISO 14001 PDCA';
    }
}
