<?php

namespace App\Filament\Widgets;

use App\Services\EmsKpiService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EmsKpiOverview extends BaseWidget
{
    

    protected static ?string $pollingInterval = '120s';

    protected static ?int $sort = 12;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff', 'business_director']) ?? false;
    }

    public function getHeading(): string
    {
        return 'Environmental KPIs (Current Month)';
    }

    protected function getStats(): array
    {
        $from = now()->startOfMonth();
        $to   = now();

        $totals          = EmsKpiService::totalsByMetric($from, $to);
        $recyclingRate   = EmsKpiService::wasteRecyclingRate($from, $to);
        $sigAspects      = EmsKpiService::significantAspectsCount();
        $expiringLicenses = EmsKpiService::expiringLicensesCount(60);

        return [
            Stat::make('Water Consumption', number_format($totals['water_consumption'], 1) . ' m³')
                ->description(now()->format('F Y'))
                ->descriptionIcon('heroicon-m-beaker')
                ->color('primary'),

            Stat::make('Energy Consumption', number_format($totals['energy_consumption'], 1) . ' kWh')
                ->description(now()->format('F Y'))
                ->descriptionIcon('heroicon-m-bolt')
                ->color('warning'),

            Stat::make('Fuel Consumption', number_format($totals['fuel_consumption'], 1) . ' L')
                ->description(now()->format('F Y'))
                ->descriptionIcon('heroicon-m-fire')
                ->color($totals['fuel_consumption'] > 0 ? 'warning' : 'success'),

            Stat::make('Waste Generated', number_format(
                    $totals['waste_generated_hazardous'] + $totals['waste_generated_nonhazardous'], 1
                ) . ' kg')
                ->description('Hazardous + Non-Hazardous')
                ->descriptionIcon('heroicon-m-trash')
                ->color('gray'),

            Stat::make('Waste Recycled', number_format($totals['waste_recycled'], 1) . ' kg')
                ->description('Recycling Rate: ' . number_format($recyclingRate, 1) . '%')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color($recyclingRate >= 50 ? 'success' : 'warning'),

            Stat::make('GHG Emissions', number_format($totals['ghg_emissions'], 2) . ' tCO₂e')
                ->description(now()->format('F Y'))
                ->descriptionIcon('heroicon-m-cloud')
                ->color($totals['ghg_emissions'] > 0 ? 'warning' : 'success'),

            Stat::make('Significant Aspects', $sigAspects)
                ->description('Open environmental risks')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($sigAspects > 0 ? 'danger' : 'success'),

            Stat::make('Licences Expiring (60d)', $expiringLicenses)
                ->description('Requiring renewal action')
                ->descriptionIcon('heroicon-m-document-check')
                ->color($expiringLicenses > 0 ? 'warning' : 'success'),
        ];
    }
}
