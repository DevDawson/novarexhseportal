<?php

namespace App\Filament\Widgets;

use App\Models\EsiaAlternative;
use App\Models\EsiaImpactAssessment;
use App\Models\EsiaMitigationAction;
use App\Models\EsiaRegulatorySubmission;
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
        $categoryA         = EsiaScreening::where('category', 'A')->where('status', 'approved')->count();

        // 3-level system: High Impact (score ≥ 81)
        $highImpacts = EsiaImpactAssessment::where('impact_level', 'high')->count();

        // Legacy 5-level: critical + major
        $criticalImpacts = EsiaImpactAssessment::whereIn('significance_level', ['critical', 'major'])->count();

        $overdueActions = EsiaMitigationAction::whereNotIn('status', ['completed', 'cancelled'])
            ->where('timeline_end', '<', now())
            ->count();

        // ESMP Compliance% = Completed / Total × 100
        $totalActions     = EsiaMitigationAction::count();
        $completedActions = EsiaMitigationAction::where('status', 'completed')->count();
        $compliancePct    = $totalActions > 0
            ? round(($completedActions / $totalActions) * 100, 1)
            : 0;

        $pendingSubmissions = EsiaRegulatorySubmission::whereIn('status', ['submitted', 'under_review'])->count();
        $approvedProjects   = EsiaRegulatorySubmission::where('status', 'approved')->count();

        // Alternatives: recommended alternatives chosen across projects
        $recommendedAlts = EsiaAlternative::where('is_recommended', true)->count();
        $totalAlts       = EsiaAlternative::count();

        return [
            Stat::make('Screenings Pending', $pendingScreenings)
                ->description('Awaiting review / in review')
                ->icon('heroicon-o-funnel')
                ->color($pendingScreenings > 0 ? 'warning' : 'success'),

            Stat::make('Category A Projects', $categoryA)
                ->description('Full ESIA required — approved screenings')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger'),

            Stat::make('High Impact (Score ≥81)', $highImpacts)
                ->description('Red-level impacts requiring immediate mitigation')
                ->icon('heroicon-o-bolt')
                ->color($highImpacts > 0 ? 'danger' : 'success'),

            Stat::make('Critical / Major Impacts', $criticalImpacts)
                ->description('Detailed significance: critical or major')
                ->icon('heroicon-o-fire')
                ->color($criticalImpacts > 0 ? 'warning' : 'success'),

            Stat::make('ESMP Compliance', $compliancePct . '%')
                ->description("{$completedActions} of {$totalActions} mitigation actions completed")
                ->icon('heroicon-o-chart-bar')
                ->color($compliancePct >= 80 ? 'success' : ($compliancePct >= 50 ? 'warning' : 'danger')),

            Stat::make('Overdue ESMP Actions', $overdueActions)
                ->description('Mitigation actions past deadline')
                ->icon('heroicon-o-clock')
                ->color($overdueActions > 0 ? 'danger' : 'success'),

            Stat::make('Submissions Under Review', $pendingSubmissions)
                ->description('With NEMC / other regulatory authorities')
                ->icon('heroicon-o-building-library')
                ->color('primary'),

            Stat::make('Approved Projects', $approvedProjects)
                ->description('With valid regulatory approval')
                ->icon('heroicon-o-check-badge')
                ->color('success'),

            Stat::make('Alternatives Evaluated', $totalAlts)
                ->description("{$recommendedAlts} recommended alternative(s) selected")
                ->icon('heroicon-o-arrows-right-left')
                ->color($totalAlts > 0 ? 'info' : 'gray'),
        ];
    }
}
