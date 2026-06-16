<?php

namespace App\Filament\Widgets;

use App\Models\HazopNode;
use App\Models\HazopStudy;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class HazopKpiWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';

    protected static ?int $sort = 11;

    protected function getStats(): array
    {
        $totalStudies = HazopStudy::count();
        $activeStudies = HazopStudy::whereNotIn('status', ['closed'])->count();

        $totalNodes = HazopNode::count();
        $openNodes = HazopNode::where('status', '!=', 'closed')->count();

        $criticalNodes = HazopNode::where('risk_classification', 'critical')
            ->where('status', '!=', 'closed')
            ->count();

        $highNodes = HazopNode::where('risk_classification', 'high')
            ->where('status', '!=', 'closed')
            ->count();

        $overdueActions = HazopNode::where('status', '!=', 'closed')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->count();

        $pendingApproval = HazopNode::where('approval_status', 'pending')
            ->where('status', '!=', 'closed')
            ->count();

        // Average control effectiveness (across all nodes with CE > 0)
        $avgCe = HazopNode::where('control_effectiveness', '>', 0)
            ->avg('control_effectiveness');
        $avgCe = $avgCe ? round((float) $avgCe, 1) : null;

        // Average RRF across all non-trivial nodes
        $avgRrf = HazopNode::where('risk_reduction_factor', '>', 0)
            ->avg('risk_reduction_factor');
        $avgRrf = $avgRrf ? round((float) $avgRrf, 2) : null;

        // RPN > 80 (high priority per spec)
        $highRpnNodes = HazopNode::where('rpn_score', '>', 80)
            ->where('status', '!=', 'closed')
            ->count();

        // This month node count
        $thisMonthNodes = HazopNode::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        return [
            Stat::make('HAZOP Studies', $totalStudies)
                ->description("{$activeStudies} active (not closed)")
                ->icon('heroicon-o-beaker')
                ->color('gray'),

            Stat::make('Total Nodes / Deviations', $totalNodes)
                ->description("{$openNodes} open — not yet closed")
                ->icon('heroicon-o-clipboard-document-list')
                ->color($openNodes > 0 ? 'primary' : 'success'),

            Stat::make('Critical Risk Nodes (Open)', $criticalNodes)
                ->description('Initial risk score 81–125 — stop-work authorization required')
                ->icon('heroicon-o-shield-exclamation')
                ->color($criticalNodes > 0 ? 'danger' : 'success'),

            Stat::make('High Risk Nodes (Open)', $highNodes)
                ->description('Initial risk score 51–80 — HSE Manager action required')
                ->icon('heroicon-o-fire')
                ->color($highNodes > 0 ? 'warning' : 'success'),

            Stat::make('Overdue Actions', $overdueActions)
                ->description('Open nodes past their due date')
                ->icon('heroicon-o-clock')
                ->color($overdueActions > 0 ? 'danger' : 'success'),

            Stat::make('Pending Approval', $pendingApproval)
                ->description('Nodes awaiting review or sign-off')
                ->icon('heroicon-o-document-check')
                ->color($pendingApproval > 0 ? 'warning' : 'success'),

            Stat::make('High RPN Nodes (>80)', $highRpnNodes)
                ->description('S×O×D > 80 — high detectability concern')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($highRpnNodes > 0 ? 'warning' : 'success'),

            Stat::make('Avg. Control Effectiveness', $avgCe !== null ? "{$avgCe}%" : 'N/A')
                ->description('Mean CE% across all nodes with controls applied')
                ->icon('heroicon-o-shield-check')
                ->color(
                    $avgCe === null ? 'gray'
                    : ($avgCe >= 70 ? 'success' : ($avgCe >= 40 ? 'warning' : 'danger'))
                ),

            Stat::make('Avg. Risk Reduction Factor', $avgRrf !== null ? "×{$avgRrf}" : 'N/A')
                ->description('Mean RRF = Initial Risk ÷ Residual Risk')
                ->icon('heroicon-o-arrow-trending-down')
                ->color('info'),

            Stat::make('This Month — Nodes Added', $thisMonthNodes)
                ->description(now()->format('F Y') . ' — HAZOP activity rate')
                ->icon('heroicon-o-chart-bar')
                ->color('primary'),
        ];
    }
}
