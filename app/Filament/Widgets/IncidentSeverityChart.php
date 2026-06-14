<?php

namespace App\Filament\Widgets;

use App\Models\Incident;
use Filament\Widgets\ChartWidget;

class IncidentSeverityChart extends ChartWidget
{
    protected static ?string $heading = 'Incidents by Risk Severity';

    protected static ?string $maxHeight = '300px';

    protected static ?string $pollingInterval = '120s';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff']) ?? false;
    }

    protected function getData(): array
    {
        $counts = Incident::query()
            ->selectRaw('severity, COUNT(*) as total')
            ->groupBy('severity')
            ->pluck('total', 'severity');

        $severities = ['low', 'medium', 'high', 'critical'];

        $data = collect($severities)->map(fn ($severity) => (int) ($counts[$severity] ?? 0));

        return [
            'datasets' => [
                [
                    'label' => 'Incidents',
                    'data' => $data->values(),
                    'backgroundColor' => [
                        '#22c55e', // low - green
                        '#eab308', // medium - yellow
                        '#f97316', // high - orange
                        '#ef4444', // critical - red
                    ],
                ],
            ],
            'labels' => ['Low', 'Medium', 'High', 'Critical'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
