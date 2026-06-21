<?php

namespace App\Filament\Widgets;

use App\Services\EmsMaturityService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EmsMaturityIndexWidget extends BaseWidget
{
    protected static ?int $sort = 13;
    protected static ?string $pollingInterval = '120s';

    public function getHeading(): string
    {
        return 'EMS Maturity Index (EMI) — ISO 14001 Continual Improvement';
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff', 'hse_manager', 'business_director']) ?? false;
    }

    protected function getStats(): array
    {
        try {
            return $this->buildStats();
        } catch (\Throwable) {
            return [
                Stat::make('EMS Maturity Index', '—')
                    ->description('Pending: php artisan migrate --force on production')
                    ->color('gray'),
            ];
        }
    }

    private function buildStats(): array
    {
        $emi = EmsMaturityService::calculate();
        $c   = $emi['components'];

        return [
            // ── EMI composite score — headline card ──────────────────
            Stat::make('EMS Maturity Index (EMI)', number_format($emi['emi'], 2) . '%')
                ->description($emi['level'] . ' — ' . $emi['status'])
                ->descriptionIcon('heroicon-o-chart-bar-square')
                ->color($emi['color']),

            // ── CR (25%) ─────────────────────────────────────────────
            Stat::make('CR — Compliance Rate (25%)', number_format($c['cr']['value'], 1) . '%')
                ->description('Weighted contribution: ' . number_format($c['cr']['weighted'], 2) . ' pts')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color(match (true) {
                    $c['cr']['value'] >= 90 => 'success',
                    $c['cr']['value'] >= 80 => 'info',
                    $c['cr']['value'] >= 70 => 'warning',
                    default                 => 'danger',
                }),

            // ── AS (20%) ─────────────────────────────────────────────
            Stat::make('AS — Audit Score (20%)', number_format($c['as']['value'], 1) . '%')
                ->description('Weighted contribution: ' . number_format($c['as']['weighted'], 2) . ' pts')
                ->descriptionIcon('heroicon-o-magnifying-glass-circle')
                ->color(match (true) {
                    $c['as']['value'] >= 90 => 'success',
                    $c['as']['value'] >= 80 => 'info',
                    $c['as']['value'] >= 70 => 'warning',
                    default                 => 'danger',
                }),

            // ── CAC (20%) ────────────────────────────────────────────
            Stat::make('CAC — CAPA Closure Rate (20%)', number_format($c['cac']['value'], 1) . '%')
                ->description('Weighted contribution: ' . number_format($c['cac']['weighted'], 2) . ' pts')
                ->descriptionIcon('heroicon-o-arrow-path')
                ->color(match (true) {
                    $c['cac']['value'] >= 90 => 'success',
                    $c['cac']['value'] >= 70 => 'warning',
                    default                  => 'danger',
                }),

            // ── OA (20%) ─────────────────────────────────────────────
            Stat::make('OA — Objective Achievement (20%)', number_format($c['oa']['value'], 1) . '%')
                ->description('Weighted contribution: ' . number_format($c['oa']['weighted'], 2) . ' pts')
                ->descriptionIcon('heroicon-o-flag')
                ->color(match (true) {
                    $c['oa']['value'] >= 90 => 'success',
                    $c['oa']['value'] >= 70 => 'warning',
                    default                 => 'danger',
                }),

            // ── TR (15%) ─────────────────────────────────────────────
            Stat::make('TR — Training Completion (15%)', number_format($c['tr']['value'], 1) . '%')
                ->description('Weighted contribution: ' . number_format($c['tr']['weighted'], 2) . ' pts')
                ->descriptionIcon('heroicon-o-academic-cap')
                ->color(match (true) {
                    $c['tr']['value'] >= 90 => 'success',
                    $c['tr']['value'] >= 70 => 'warning',
                    default                 => 'danger',
                }),
        ];
    }
}
