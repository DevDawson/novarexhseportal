<?php

namespace App\Services;

use Filament\Support\Colors\Color;

class HazopScoringService
{
    // ----------------------------------------------------------------
    // Core formulas
    // ----------------------------------------------------------------

    /** Initial Risk Score = Likelihood × Severity × Exposure (range 1–125) */
    public static function initialScore(int $l, int $s, int $e): int
    {
        return max(0, $l * $s * $e);
    }

    /** Residual Risk = IR × (1 − CE%) where CE is 0–100 */
    public static function residualRisk(float $initialRisk, float $controlEffectiveness): float
    {
        $ce = max(0, min(100, $controlEffectiveness));
        return round($initialRisk * (1 - $ce / 100), 2);
    }

    /** Risk Reduction Factor = IR / RR */
    public static function rrf(float $initialRisk, float $residualRisk): float
    {
        return $residualRisk > 0 ? round($initialRisk / $residualRisk, 2) : 0;
    }

    /** Risk Priority Number = Severity × Occurrence × Detectability (range 1–125) */
    public static function rpn(int $severity, int $occurrence, int $detectability): int
    {
        return max(0, $severity * $occurrence * $detectability);
    }

    // ----------------------------------------------------------------
    // NOVAREX risk matrix (L×S×E / RPN share same thresholds)
    //   1–20   Low      → Supervisor approval
    //  21–50   Medium   → HSE Officer approval
    //  51–80   High     → HSE Manager approval
    //  81–125  Critical → HSE Manager + Top Management
    // ----------------------------------------------------------------

    public static function riskLevel(float $score): string
    {
        return match (true) {
            $score >= 81 => 'critical',
            $score >= 51 => 'high',
            $score >= 21 => 'medium',
            default      => 'low',
        };
    }

    public static function approvalRequirement(string $level): string
    {
        return match ($level) {
            'critical' => 'HSE Manager + Top Management — immediate stop-work authorization required',
            'high'     => 'HSE Manager — corrective actions before resuming work',
            'medium'   => 'HSE Officer — additional controls to be implemented',
            default    => 'Supervisor — monitor and maintain existing controls',
        };
    }

    // ----------------------------------------------------------------
    // Color helpers (Filament Color arrays)
    // ----------------------------------------------------------------

    public static function colorForLevel(string $level): array
    {
        return match ($level) {
            'critical' => Color::Red,
            'high'     => Color::Orange,
            'medium'   => Color::Yellow,
            default    => Color::Green,
        };
    }

    public static function colorForScore(float $score): array
    {
        return self::colorForLevel(self::riskLevel($score));
    }

    // ----------------------------------------------------------------
    // Select field options
    // ----------------------------------------------------------------

    public static function likelihoodOptions(): array
    {
        return [
            1 => '1 — Rare (almost never happens)',
            2 => '2 — Unlikely (has happened in industry)',
            3 => '3 — Possible (has happened in company)',
            4 => '4 — Likely (happens occasionally)',
            5 => '5 — Almost Certain (happens regularly)',
        ];
    }

    public static function severityOptions(): array
    {
        return [
            1 => '1 — Negligible (no injury, minimal property damage)',
            2 => '2 — Minor Injury (first aid, minor property damage)',
            3 => '3 — Medical Treatment Case (MTC, reportable)',
            4 => '4 — Major Injury / Lost Time (hospitalisation)',
            5 => '5 — Fatality / Catastrophic Loss',
        ];
    }

    public static function exposureOptions(): array
    {
        return [
            1 => '1 — Annual (very rarely exposed)',
            2 => '2 — Quarterly (infrequent exposure)',
            3 => '3 — Monthly (occasional exposure)',
            4 => '4 — Weekly (frequent exposure)',
            5 => '5 — Daily / Continuous (constant exposure)',
        ];
    }

    public static function detectabilityOptions(): array
    {
        return [
            1 => '1 — Almost Certain Detection (automated, fail-safe)',
            2 => '2 — High Detection (visual indicators, alarms)',
            3 => '3 — Moderate Detection (periodic inspection)',
            4 => '4 — Low Detection (difficult to detect)',
            5 => '5 — Very Difficult Detection (no current detection method)',
        ];
    }

    public static function guideWordOptions(): array
    {
        return [
            'NO / NOT'     => 'NO / NOT — Complete negation of design intent',
            'MORE OF'      => 'MORE OF — Quantitative increase of a parameter',
            'LESS OF'      => 'LESS OF — Quantitative decrease of a parameter',
            'AS WELL AS'   => 'AS WELL AS — Additional activity or component',
            'PART OF'      => 'PART OF — Only part of the intention is achieved',
            'REVERSE'      => 'REVERSE — Logical opposite of design intent',
            'OTHER THAN'   => 'OTHER THAN — Complete substitution',
            'EARLY'        => 'EARLY — Something happens sooner than intended',
            'LATE'         => 'LATE — Something happens later than intended',
            'BEFORE'       => 'BEFORE — Wrong order or sequence',
            'AFTER'        => 'AFTER — Wrong order or sequence',
            'HIGH'         => 'HIGH — Higher than design value',
            'LOW'          => 'LOW — Lower than design value',
        ];
    }

    public static function parameterOptions(): array
    {
        return [
            'Flow'            => 'Flow',
            'Pressure'        => 'Pressure',
            'Temperature'     => 'Temperature',
            'Level'           => 'Level',
            'Composition'     => 'Composition / Concentration',
            'Reaction'        => 'Reaction Rate / Type',
            'Phase'           => 'Phase (solid/liquid/gas)',
            'Time / Duration' => 'Time / Duration',
            'Speed'           => 'Speed / Velocity',
            'Viscosity'       => 'Viscosity',
            'pH'              => 'pH / Corrosion',
            'Mixing'          => 'Mixing / Agitation',
            'Voltage'         => 'Voltage / Current',
            'Signal'          => 'Signal / Data',
            'Maintenance'     => 'Maintenance / Inspection',
            'Other'           => 'Other',
        ];
    }

    // ----------------------------------------------------------------
    // RPN level label for display
    // ----------------------------------------------------------------

    public static function rpnLabel(int $rpn): string
    {
        $level = self::riskLevel($rpn);
        return "{$rpn} / 125 — " . ucfirst($level);
    }

    public static function scoreLabel(float $score): string
    {
        $level = self::riskLevel($score);
        return "{$score} / 125 — " . ucfirst($level);
    }
}
