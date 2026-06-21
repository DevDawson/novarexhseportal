<?php

namespace App\Filament\Widgets;

use App\Models\EsgMaturityAssessment;
use App\Services\EsgMaturityService;
use Filament\Widgets\ChartWidget;

class EsgScoreGaugeWidget extends ChartWidget
{
    protected static ?string $heading   = 'ESG-MI Score Gauge';
    protected static ?string $maxHeight = '280px';
    protected static ?string $pollingInterval = '120s';
    protected static ?int    $sort      = 19;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'esg_officer', 'business_director']) ?? false;
    }

    protected function getType(): string { return 'doughnut'; }

    protected function getData(): array
    {
        try {
            $data      = EsgMaturityService::latestOrLive();
            $composite = $data['composite'];
            $esgMi     = (float) $composite['esg_mi'];
            $level     = $composite['level'];

            $color = match ($level) {
                'Transformational' => '#10B981',
                'Advanced'         => '#3B82F6',
                'Managed'          => '#6366F1',
                'Developing'       => '#F59E0B',
                default            => '#EF4444',
            };

            return [
                'datasets' => [[
                    'data'            => [round($esgMi, 1), round(100 - $esgMi, 1)],
                    'backgroundColor' => [$color, '#E5E7EB'],
                    'borderWidth'     => 0,
                    'hoverOffset'     => 0,
                ]],
                'labels' => ['ESG-MI: ' . round($esgMi, 1) . '%  (' . $level . ')', 'Remaining'],
            ];
        } catch (\Throwable) {
            return [
                'datasets' => [['data' => [0, 100], 'backgroundColor' => ['#D1D5DB', '#F3F4F6'], 'borderWidth' => 0]],
                'labels'   => ['No data', ''],
            ];
        }
    }

    protected function getOptions(): array
    {
        return [
            'circumference' => 180,
            'rotation'      => -90,
            'cutout'        => '70%',
            'plugins'       => ['legend' => ['position' => 'bottom']],
        ];
    }
}
