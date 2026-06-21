<?php

namespace App\Filament\Pages;

use App\Models\EsgMaturityAssessment;
use App\Services\EsgMaturityService;
use Filament\Pages\Page;

class EsgMaturityDashboard extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-globe-alt';
    protected static ?string $navigationLabel = 'ESG-MI Dashboard';
    protected static ?string $navigationGroup = 'ESG Management';
    protected static ?int    $navigationSort  = 11;
    protected static string  $view            = 'filament.pages.esg-maturity-dashboard';

    public array  $composite   = [];
    public array  $scores      = [];
    public array  $autoSources = [];
    public ?array $latestInfo  = null;
    public array  $history     = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff', 'hse_manager', 'esg_officer', 'business_director']) ?? false;
    }

    public function mount(): void
    {
        $data = EsgMaturityService::latestOrLive();

        $this->composite = $data['composite'];
        $this->scores    = $data['scores'];

        if ($data['latest']) {
            $l = $data['latest'];
            $this->latestInfo = [
                'period'      => $l->period,
                'status'      => $l->status,
                'assessed_by' => $l->assessedBy?->name ?? '—',
                'assessed_at' => $l->assessed_at?->format('d M Y') ?? '—',
            ];
            $this->autoSources = $l->auto_sources ?? [];
        } else {
            $this->autoSources = array_map(fn($v) => $v['source'], $data['auto'] ?? []);
        }

        $this->history = EsgMaturityAssessment::where('status', 'finalized')
            ->orderByDesc('period')
            ->limit(6)
            ->get(['period', 'e_score', 's_score', 'g_score', 'esg_mi'])
            ->toArray();
    }

    public function getTitle(): string
    {
        return 'ESG Maturity Index Dashboard';
    }
}
