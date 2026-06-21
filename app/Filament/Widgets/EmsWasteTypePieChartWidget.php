<?php

namespace App\Filament\Widgets;

use App\Models\WasteTrackingRecord;
use Filament\Widgets\ChartWidget;

class EmsWasteTypePieChartWidget extends ChartWidget
{
    protected static ?string $heading   = 'Waste Distribution by Type — Current Year';
    protected static ?string $maxHeight = '280px';
    protected static ?string $pollingInterval = '120s';
    protected static ?int    $sort      = 13;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff', 'hse_manager', 'business_director']) ?? false;
    }

    protected function getType(): string { return 'doughnut'; }

    protected function getData(): array
    {
        try {
            $rows = WasteTrackingRecord::whereYear('generation_date', now()->year)
                ->selectRaw('waste_type, SUM(quantity) as total')
                ->groupBy('waste_type')
                ->orderByDesc('total')
                ->limit(8)
                ->pluck('total', 'waste_type');

            if ($rows->isEmpty()) {
                return ['datasets' => [['data' => [1], 'backgroundColor' => ['#D1D5DB']]], 'labels' => ['No data']];
            }

            $palette = ['#EF4444','#F97316','#EAB308','#22C55E','#06B6D4','#3B82F6','#8B5CF6','#EC4899'];

            return [
                'datasets' => [[
                    'data'            => $rows->values()->map(fn ($v) => round((float) $v, 2))->all(),
                    'backgroundColor' => array_slice($palette, 0, $rows->count()),
                    'hoverOffset'     => 6,
                ]],
                'labels' => $rows->keys()->map(fn ($t) => ucwords(str_replace('_', ' ', $t)))->all(),
            ];
        } catch (\Throwable) {
            return ['datasets' => [['data' => []]], 'labels' => []];
        }
    }
}
