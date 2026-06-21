<?php

namespace App\Filament\Widgets;

use App\Models\Incident;
use Filament\Widgets\ChartWidget;

class IncidentTypePieChartWidget extends ChartWidget
{
    protected static ?string $heading   = 'Incidents by Category — All Time';
    protected static ?string $maxHeight = '280px';
    protected static ?string $pollingInterval = '120s';
    protected static ?int    $sort      = 4;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff', 'hse_manager', 'hr_director']) ?? false;
    }

    protected function getType(): string { return 'pie'; }

    protected function getData(): array
    {
        $typeLabels = [
            'near_miss'          => 'Near Miss',
            'first_aid'          => 'First Aid',
            'medical_treatment'  => 'Medical Treatment',
            'lost_time'          => 'Lost Time Injury',
            'fatality'           => 'Fatality',
            'environmental'      => 'Environmental',
            'property_damage'    => 'Property Damage',
        ];

        $counts = Incident::selectRaw('incident_type, COUNT(*) as total')
            ->groupBy('incident_type')
            ->pluck('total', 'incident_type');

        $palette = [
            'near_miss'         => '#6B7280',
            'first_aid'         => '#3B82F6',
            'medical_treatment' => '#F59E0B',
            'lost_time'         => '#F97316',
            'fatality'          => '#EF4444',
            'environmental'     => '#10B981',
            'property_damage'   => '#8B5CF6',
        ];

        $data   = [];
        $labels = [];
        $colors = [];

        foreach ($typeLabels as $key => $label) {
            $count = (int) ($counts[$key] ?? 0);
            if ($count > 0) {
                $data[]   = $count;
                $labels[] = $label . ' (' . $count . ')';
                $colors[] = $palette[$key];
            }
        }

        if (empty($data)) {
            return ['datasets' => [['data' => [1], 'backgroundColor' => ['#D1D5DB']]], 'labels' => ['No incidents recorded']];
        }

        return [
            'datasets' => [['data' => $data, 'backgroundColor' => $colors, 'hoverOffset' => 6]],
            'labels'   => $labels,
        ];
    }
}
