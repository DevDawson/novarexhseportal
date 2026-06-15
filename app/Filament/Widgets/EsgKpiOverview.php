<?php

namespace App\Filament\Widgets;

use App\Models\EsgTarget;
use App\Models\Grievance;
use App\Models\EthicsIncident;
use App\Models\GovernancePolicy;
use App\Models\StakeholderEngagement;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EsgKpiOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '120s';

    protected static ?int $sort = 15;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'esg_officer', 'business_director']) ?? false;
    }

    public function getHeading(): string
    {
        return 'ESG Dashboard';
    }

    protected function getStats(): array
    {
        // Grievances
        $openGrievances = Grievance::query()
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();

        $overdueGrievances = Grievance::query()
            ->whereNotIn('status', ['resolved', 'closed'])
            ->whereNotNull('target_resolution_date')
            ->where('target_resolution_date', '<', now())
            ->count();

        // Ethics incidents
        $openEthicsIncidents = EthicsIncident::query()
            ->whereNotIn('status', ['closed', 'no_action_required'])
            ->count();

        // Governance policies
        $activePolicies = GovernancePolicy::query()
            ->where('status', 'active')
            ->count();

        $policiesDueReview = GovernancePolicy::query()
            ->where('status', 'active')
            ->where('review_date', '<=', now()->addDays(60))
            ->count();

        // ESG targets
        $totalTargets    = EsgTarget::count();
        $onTrack         = EsgTarget::whereIn('status', ['on_track', 'achieved'])->count();
        $atRiskOrOff     = EsgTarget::whereIn('status', ['at_risk', 'off_track'])->count();

        // Pending follow-ups in engagement log
        $pendingFollowUps = StakeholderEngagement::query()
            ->where('follow_up_completed', false)
            ->whereNotNull('follow_up_date')
            ->where('follow_up_date', '<=', now()->addDays(7))
            ->count();

        return [
            Stat::make('Open Grievances', $openGrievances)
                ->description($overdueGrievances > 0 ? "{$overdueGrievances} overdue" : 'None overdue')
                ->descriptionIcon('heroicon-m-megaphone')
                ->color($overdueGrievances > 0 ? 'danger' : ($openGrievances > 0 ? 'warning' : 'success')),

            Stat::make('Open Ethics Incidents', $openEthicsIncidents)
                ->description('Reported / under investigation')
                ->descriptionIcon('heroicon-m-scale')
                ->color($openEthicsIncidents > 0 ? 'danger' : 'success'),

            Stat::make('Active Policies', $activePolicies)
                ->description($policiesDueReview > 0 ? "{$policiesDueReview} due for review" : 'All reviews current')
                ->descriptionIcon('heroicon-m-document-text')
                ->color($policiesDueReview > 0 ? 'warning' : 'success'),

            Stat::make('ESG Targets On Track', "{$onTrack} / {$totalTargets}")
                ->description($atRiskOrOff > 0 ? "{$atRiskOrOff} at risk or off track" : 'All on track')
                ->descriptionIcon('heroicon-m-flag')
                ->color($atRiskOrOff > 0 ? 'warning' : 'success'),

            Stat::make('Engagement Follow-Ups (7d)', $pendingFollowUps)
                ->description('Due within 7 days')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color($pendingFollowUps > 0 ? 'info' : 'success'),
        ];
    }
}
