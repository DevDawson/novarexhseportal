<?php

namespace App\Services;

use Filament\Support\Colors\Color;

class RiskScoringService
{
    /**
     * Risk Score = Likelihood (L) x Impact/Severity (I)
     * L and I each range 0-5, so Risk Score ranges 0-25.
     */
    public static function score(int $likelihood, int $impact): int
    {
        return max(0, min(25, $likelihood * $impact));
    }

    /**
     * Risk Level Classification (per NOVAREX Risk Assessment Methodology):
     *
     *   0-4   Low       - Maintain existing controls, monitor periodically.
     *   5-9   Medium    - Implement additional controls where reasonably practicable.
     *   10-15 High      - Immediate corrective actions required. Management review.
     *   16-25 Critical  - Stop work immediately. Senior management authorization required.
     */
    public static function level(int $score): string
    {
        return match (true) {
            $score >= 16 => 'critical',
            $score >= 10 => 'high',
            $score >= 5 => 'medium',
            default => 'low',
        };
    }

    /**
     * Human-readable required action for a given risk level.
     */
    public static function requiredAction(string $level): string
    {
        return match ($level) {
            'critical' => 'Stop work immediately until risk is reduced. Senior management authorization required before resuming.',
            'high' => 'Immediate corrective actions required. Management review necessary.',
            'medium' => 'Implement additional controls where reasonably practicable.',
            default => 'Maintain existing controls and monitor periodically.',
        };
    }

    /**
     * Dynamic color coding for the risk level:
     *   Low = Green, Medium = Yellow, High = Orange, Critical = Red.
     *
     * Returns a Filament color palette array suitable for passing directly
     * to ->color() on badge/text columns (Filament\Support\Colors\Color).
     */
    public static function colorForLevel(string $level): array
    {
        return match ($level) {
            'critical' => Color::Red,
            'high' => Color::Orange,
            'medium' => Color::Yellow,
            default => Color::Green,
        };
    }

    /**
     * Convenience: color palette directly from a risk score.
     */
    public static function colorForScore(int $score): array
    {
        return self::colorForLevel(self::level($score));
    }

    /**
     * Options array for Likelihood / Impact select fields (0-5).
     */
    public static function ratingOptions(): array
    {
        return [
            0 => '0 - None',
            1 => '1 - Rare / Insignificant',
            2 => '2 - Unlikely / Minor',
            3 => '3 - Possible / Moderate',
            4 => '4 - Likely / Major',
            5 => '5 - Almost Certain / Catastrophic',
        ];
    }
}
