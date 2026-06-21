<?php

namespace App\Filament\Widgets;

use App\Services\EsgMaturityService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EsgMaturityIndexWidget extends BaseWidget
{
    protected static ?int $sort = 17;
    protected static ?string $pollingInterval = '120s';

    public function getHeading(): string
    {
        return 'ESG Maturity Index (ESG-MI) — Environmental · Social · Governance';
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff', 'hse_manager', 'esg_officer', 'business_director']) ?? false;
    }

    protected function getStats(): array
    {
        try {
            return $this->buildStats();
        } catch (\Throwable) {
            return [
                Stat::make('ESG Maturity Index', '—')
                    ->description('Pending: php artisan migrate --force on production')
                    ->color('gray'),
            ];
        }
    }

    private function buildStats(): array
    {
        $data      = EsgMaturityService::latestOrLive();
        $composite = $data['composite'];
        $scores    = $data['scores'];
        $latest    = $data['latest'] ?? null;

        $esgMi = (float) $composite['esg_mi'];
        $level  = $composite['level'];
        $color  = $composite['color'];

        $source = $latest
            ? "Period: {$latest->period} — Finalized assessment"
            : 'Live calculation from current ERP data (no finalized assessment)';

        return [
            // ── Headline ESG-MI ──────────────────────────────────────
            Stat::make('ESG Maturity Index', number_format($esgMi, 2) . '%')
                ->description($level . ' — ' . $source)
                ->descriptionIcon('heroicon-o-globe-alt')
                ->color($color),

            // ── E: Environmental (40%) ────────────────────────────────
            Stat::make('E — Environmental (40%)', number_format((float) $composite['e'], 2) . '%')
                ->description(
                    'CR: ' . number_format((float)($scores['cr'] ?? 0), 1) . '%'
                    . ' | WR: ' . number_format((float)($scores['wr'] ?? 0), 1) . '%'
                    . ' | EMS: ' . number_format((float)($scores['ems'] ?? 0), 1) . '%'
                )
                ->descriptionIcon('heroicon-o-globe-europe-africa')
                ->color(match (true) {
                    (float)$composite['e'] >= 90 => 'success',
                    (float)$composite['e'] >= 80 => 'info',
                    (float)$composite['e'] >= 70 => 'warning',
                    default => 'danger',
                }),

            // ── S: Social (30%) ──────────────────────────────────────
            Stat::make('S — Social (30%)', number_format((float) $composite['s'], 2) . '%')
                ->description(
                    'TR: ' . number_format((float)($scores['tr'] ?? 0), 1) . '%'
                    . ' | LTIFR: ' . number_format((float)($scores['ltifr'] ?? 0), 1) . '%'
                    . ' | DEI: ' . number_format((float)($scores['dei'] ?? 0), 1) . '%'
                )
                ->descriptionIcon('heroicon-o-users')
                ->color(match (true) {
                    (float)$composite['s'] >= 90 => 'success',
                    (float)$composite['s'] >= 80 => 'info',
                    (float)$composite['s'] >= 70 => 'warning',
                    default => 'danger',
                }),

            // ── G: Governance (30%) ──────────────────────────────────
            Stat::make('G — Governance (30%)', number_format((float) $composite['g'], 2) . '%')
                ->description(
                    'ACR: ' . number_format((float)($scores['acr'] ?? 0), 1) . '%'
                    . ' | DCR: ' . number_format((float)($scores['dcr'] ?? 0), 1) . '%'
                    . ' | ECR: ' . number_format((float)($scores['ecr'] ?? 0), 1) . '%'
                )
                ->descriptionIcon('heroicon-o-building-library')
                ->color(match (true) {
                    (float)$composite['g'] >= 90 => 'success',
                    (float)$composite['g'] >= 80 => 'info',
                    (float)$composite['g'] >= 70 => 'warning',
                    default => 'danger',
                }),

            // ── Formula verification ──────────────────────────────────
            Stat::make('ESG-MI Formula', number_format($esgMi, 2) . '%')
                ->description(
                    '(' . number_format((float)$composite['e'], 1) . '×40'
                    . ' + ' . number_format((float)$composite['s'], 1) . '×30'
                    . ' + ' . number_format((float)$composite['g'], 1) . '×30) ÷ 100'
                )
                ->descriptionIcon('heroicon-o-calculator')
                ->color($color),
        ];
    }
}
