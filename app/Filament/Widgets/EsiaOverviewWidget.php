<?php

namespace App\Filament\Widgets;

use App\Models\EsiaImpactAssessment;
use App\Models\EsiaMitigationAction;
use App\Models\EsiaRegulatorySubmission;
use App\Models\EsiaReport;
use App\Models\EsiaScreening;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EsiaOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 18;

    public function getHeading(): string
    {
        return 'EIA / ESIA Dashboard';
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff', 'business_director']) ?? false;
    }

    protected function getStats(): array
    {
        $pendingScreenings = EsiaScreening::whereIn('status', ['pending', 'in_review'])->count();
        $categoryA = EsiaScreening::where('category', 'A')->where('status', 'approved')->count();

        $criticalImpacts = EsiaImpactAssessment::whereIn('significance_level', ['critical', 'major'])->count();

        $overdueActions = EsiaMitigationAction::whereNotIn('status', ['completed', 'cancelled'])
            ->where('timeline_end', '<', now())
            ->count();

        $pendingSubmissions = EsiaRegulatorySubmission::whereIn('status', ['submitted', 'under_review'])->count();

        $approvedProjects = EsiaRegulatorySubmission::where('status', 'approved')->count();

        return [
            Stat::make('Screenings Pending', $pendingScreenings)
                ->description('Awaiting review / in review')
                ->icon('heroicon-o-funnel')
                ->color($pendingScreenings > 0 ? 'warning' : 'success'),

            Stat::make('Category A Projects', $categoryA)
                ->description('Full ESIA required — approved screenings')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger'),

            Stat::make('Critical / Major Impacts', $criticalImpacts)
                ->description('Impacts requiring priority mitigation')
                ->icon('heroicon-o-bolt')
                ->color($criticalImpacts > 0 ? 'danger' : 'success'),

            Stat::make('Overdue ESMP Actions', $overdueActions)
                ->description('Mitigation actions past deadline')
                ->icon('heroicon-o-clock')
                ->color($overdueActions > 0 ? 'danger' : 'success'),

            Stat::make('Submissions Under Review', $pendingSubmissions)
                ->description('With NEMC / other authorities')
                ->icon('heroicon-o-building-library')
                ->color('primary'),

            Stat::make('Approved Projects', $approvedProjects)
                ->description('With valid regulatory approval')
                ->icon('heroicon-o-check-badge')
                ->color('success'),
        ];
    }
}
