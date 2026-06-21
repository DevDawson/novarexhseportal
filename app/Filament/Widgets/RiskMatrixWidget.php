<?php

namespace App\Filament\Widgets;

use App\Models\HazardRegister;
use Filament\Widgets\Widget;

class RiskMatrixWidget extends Widget
{
    protected static string $view = 'filament.widgets.risk-matrix-widget';

    protected static ?string $pollingInterval = '120s';
    protected static ?int    $sort            = 11;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff', 'hse_manager', 'business_director', 'lead_auditor']) ?? false;
    }

    public function getMatrix(): array
    {
        // Build a 5×5 matrix [likelihood][severity] => ['initial' => n, 'residual' => n]
        $matrix = [];
        for ($l = 1; $l <= 5; $l++) {
            for ($s = 1; $s <= 5; $s++) {
                $matrix[$l][$s] = ['initial' => 0, 'residual' => 0];
            }
        }

        $rows = HazardRegister::whereNotNull('initial_likelihood')
            ->whereNotNull('initial_severity')
            ->whereNotNull('residual_likelihood')
            ->whereNotNull('residual_severity')
            ->where('initial_likelihood', '>=', 1)
            ->where('initial_likelihood', '<=', 5)
            ->where('initial_severity', '>=', 1)
            ->where('initial_severity', '<=', 5)
            ->get(['initial_likelihood', 'initial_severity', 'residual_likelihood', 'residual_severity']);

        foreach ($rows as $r) {
            $il = (int) $r->initial_likelihood;
            $is = (int) $r->initial_severity;
            $rl = min(5, max(1, (int) $r->residual_likelihood));
            $rs = min(5, max(1, (int) $r->residual_severity));

            if (isset($matrix[$il][$is])) {
                $matrix[$il][$is]['initial']++;
            }
            if (isset($matrix[$rl][$rs])) {
                $matrix[$rl][$rs]['residual']++;
            }
        }

        return $matrix;
    }

    public static function cellColor(int $likelihood, int $severity): string
    {
        $score = $likelihood * $severity;
        return match (true) {
            $score >= 16 => '#EF4444', // Critical — red
            $score >= 10 => '#F97316', // High — orange
            $score >= 5  => '#EAB308', // Medium — yellow
            default      => '#22C55E', // Low — green
        };
    }

    public static function cellLabel(int $likelihood, int $severity): string
    {
        $score = $likelihood * $severity;
        return match (true) {
            $score >= 16 => 'CRITICAL',
            $score >= 10 => 'HIGH',
            $score >= 5  => 'MEDIUM',
            default      => 'LOW',
        };
    }
}
