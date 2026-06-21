<?php

namespace App\Filament\Pages;

use App\Models\MaturityAssessment;
use App\Models\MaturityDimension;
use App\Services\MaturityScoringService;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class MaturityDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $navigationGroup = 'HSE System';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationLabel = 'MI Dashboard';

    protected static ?string $title = 'HSE Maturity Index Dashboard';

    protected static string $view = 'filament.pages.maturity-dashboard';

    public ?MaturityAssessment $latest = null;

    public Collection $dimensionBreakdown;

    public Collection $trend;

    public function mount(): void
    {
        $this->latest = MaturityAssessment::where('status', 'finalised')
            ->latest('assessed_at')
            ->first();

        if ($this->latest) {
            $this->dimensionBreakdown = MaturityScoringService::dimensionBreakdown($this->latest);
        } else {
            $this->dimensionBreakdown = collect();
        }

        // Last 6 finalised assessments for trend
        $this->trend = MaturityAssessment::where('status', 'finalised')
            ->orderBy('assessed_at')
            ->latest('id')
            ->take(6)
            ->get()
            ->reverse()
            ->values();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_manager', 'hse_staff', 'lead_auditor', 'business_director']) ?? false;
    }
}
