<?php

namespace App\Filament\Widgets;

use App\Models\EmsImprovementAction;
use App\Services\EmsMaturityService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EmsContinualImprovementWidget extends BaseWidget
{
    protected static ?int $sort = 14;
    protected static ?string $pollingInterval = '120s';

    public function getHeading(): string
    {
        return 'EMS Continual Improvement — KPIs 15.1–15.4 (ISO 14001 Clause 10)';
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff', 'hse_manager', 'business_director']) ?? false;
    }

    protected function getStats(): array
    {
        try {
            return $this->buildStats();
        } catch (\Throwable $e) {
            return [
                Stat::make('EMS Continual Improvement', '—')
                    ->description('Pending: php artisan migrate --force on production')
                    ->color('gray'),
            ];
        }
    }

    private function buildStats(): array
    {
        // ── KPI 15.1: Environmental Objective Achievement Rate ────────
        $kpi151 = EmsMaturityService::kpi151();

        // ── KPI 15.2: Improvement Action Closure Rate ─────────────────
        $kpi152 = EmsMaturityService::kpi152();
        $totalCI  = EmsImprovementAction::whereNotIn('status', ['cancelled'])->count();
        $closedCI = EmsImprovementAction::where('status', 'closed')->count();
        $openCI   = EmsImprovementAction::whereIn('status', ['open', 'in_progress'])->count();
        $overdueCI = EmsImprovementAction::whereIn('status', ['open', 'in_progress'])
                        ->whereNotNull('target_date')
                        ->where('target_date', '<', now())->count();

        // ── KPI 15.3: Repeat Environmental Incident Rate ──────────────
        $kpi153 = EmsMaturityService::kpi153();

        // ── KPI 15.4: EMS Maturity Index (EMI) ───────────────────────
        $emi = EmsMaturityService::calculate();

        // ── Open improvement actions by PDCA phase ────────────────────
        $byPdca = EmsImprovementAction::whereIn('status', ['open', 'in_progress'])
                    ->selectRaw('pdca_phase, COUNT(*) as cnt')
                    ->groupBy('pdca_phase')
                    ->pluck('cnt', 'pdca_phase');

        return [
            Stat::make('KPI 15.1 — Objective Achievement Rate', "{$kpi151}%")
                ->description('Environmental objectives achieved / total · Target: ≥ 90%')
                ->descriptionIcon('heroicon-o-flag')
                ->color($kpi151 >= 90 ? 'success' : ($kpi151 >= 70 ? 'warning' : 'danger')),

            Stat::make('KPI 15.2 — Improvement Action Closure Rate', "{$kpi152}%")
                ->description(
                    "{$closedCI} closed / {$totalCI} total · {$openCI} open · {$overdueCI} overdue"
                )
                ->descriptionIcon('heroicon-o-arrow-path')
                ->color($kpi152 >= 90 ? 'success' : ($kpi152 >= 70 ? 'warning' : 'danger')),

            Stat::make('KPI 15.3 — Repeat Environmental Incident Rate', "{$kpi153}%")
                ->description('% of env. incidents that are repeats from the same location · Lower is better')
                ->descriptionIcon('heroicon-o-arrow-uturn-left')
                ->color($kpi153 === 0.0 ? 'success' : ($kpi153 <= 20 ? 'warning' : 'danger')),

            Stat::make('KPI 15.4 — EMS Maturity Index', number_format($emi['emi'], 2) . '%')
                ->description(
                    $emi['level'] . ' (' . $emi['status'] . ')'
                    . ' — Composite: CR×25 + AS×20 + CAC×20 + OA×20 + TR×15'
                )
                ->descriptionIcon('heroicon-o-chart-bar-square')
                ->color($emi['color']),

            Stat::make('Open CI Actions (by Phase)', $openCI ?: '0')
                ->description(
                    collect(['plan', 'do', 'check', 'act'])
                        ->map(fn ($p) => strtoupper($p) . ': ' . ($byPdca[$p] ?? 0))
                        ->implode(' | ')
                )
                ->descriptionIcon('heroicon-o-arrow-path-rounded-square')
                ->color($overdueCI > 0 ? 'danger' : ($openCI > 0 ? 'warning' : 'success')),

            Stat::make('CI Actions — This Year', EmsImprovementAction::whereYear('created_at', now()->year)->count())
                ->description(
                    'Verified: ' . EmsImprovementAction::where('effectiveness_verified', true)->count()
                    . ' | Cancelled: ' . EmsImprovementAction::where('status', 'cancelled')->count()
                )
                ->descriptionIcon('heroicon-o-plus-circle')
                ->color('primary'),
        ];
    }
}
