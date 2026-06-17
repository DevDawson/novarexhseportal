<?php

namespace App\Services;

use App\Models\EnvironmentalAudit;

class EnvironmentalAuditService
{
    // ------------------------------------------------------------------ //
    //  Standard ISO 14001 / ISO 19011 checklist template (42 items, A–I) //
    // ------------------------------------------------------------------ //
    public static function defaultChecklistItems(): array
    {
        return [
            // A — Compliance & Legal Requirements
            ['category' => 'A', 'code' => 'A1', 'description' => 'Valid environmental permits available and current', 'sort' => 10],
            ['category' => 'A', 'code' => 'A2', 'description' => 'Legal and regulatory requirements identified and updated', 'sort' => 11],
            ['category' => 'A', 'code' => 'A3', 'description' => 'Compliance obligations register maintained', 'sort' => 12],
            ['category' => 'A', 'code' => 'A4', 'description' => 'Regulatory reporting submitted within required timelines', 'sort' => 13],
            ['category' => 'A', 'code' => 'A5', 'description' => 'Previous non-compliances reviewed and closed', 'sort' => 14],

            // B — Environmental Aspects & Impacts
            ['category' => 'B', 'code' => 'B1', 'description' => 'Environmental aspects identified and documented', 'sort' => 20],
            ['category' => 'B', 'code' => 'B2', 'description' => 'Significant environmental impacts assessed and ranked', 'sort' => 21],
            ['category' => 'B', 'code' => 'B3', 'description' => 'Risk rating methodology correctly applied', 'sort' => 22],
            ['category' => 'B', 'code' => 'B4', 'description' => 'Operational controls implemented and effective', 'sort' => 23],
            ['category' => 'B', 'code' => 'B5', 'description' => 'Residual risks evaluated and acceptable', 'sort' => 24],

            // C — Environmental Monitoring & Measurement
            ['category' => 'C', 'code' => 'C1', 'description' => 'Air quality monitoring conducted (PM2.5, PM10, CO₂, NOx, SO₂, VOCs)', 'sort' => 30],
            ['category' => 'C', 'code' => 'C2', 'description' => 'Water quality monitoring conducted (pH, BOD, COD, DO, TSS, heavy metals)', 'sort' => 31],
            ['category' => 'C', 'code' => 'C3', 'description' => 'Noise monitoring conducted (Leq, peak, day/night levels)', 'sort' => 32],
            ['category' => 'C', 'code' => 'C4', 'description' => 'Soil contamination monitoring conducted', 'sort' => 33],
            ['category' => 'C', 'code' => 'C5', 'description' => 'Calibration records for monitoring equipment available', 'sort' => 34],
            ['category' => 'C', 'code' => 'C6', 'description' => 'Data trends analyzed and reported', 'sort' => 35],

            // D — Operational Environmental Controls
            ['category' => 'D', 'code' => 'D1', 'description' => 'Waste management procedures implemented and followed', 'sort' => 40],
            ['category' => 'D', 'code' => 'D2', 'description' => 'Hazardous waste storage and disposal compliant', 'sort' => 41],
            ['category' => 'D', 'code' => 'D3', 'description' => 'Spill prevention and response systems functional', 'sort' => 42],
            ['category' => 'D', 'code' => 'D4', 'description' => 'Emission control systems operational', 'sort' => 43],
            ['category' => 'D', 'code' => 'D5', 'description' => 'Environmental SOPs implemented at site level', 'sort' => 44],

            // E — Emergency Preparedness & Response
            ['category' => 'E', 'code' => 'E1', 'description' => 'Spill response plan available and tested', 'sort' => 50],
            ['category' => 'E', 'code' => 'E2', 'description' => 'Emergency drills conducted and recorded', 'sort' => 51],
            ['category' => 'E', 'code' => 'E3', 'description' => 'Fire protection systems functional', 'sort' => 52],
            ['category' => 'E', 'code' => 'E4', 'description' => 'Incident response procedures tested and reviewed', 'sort' => 53],
            ['category' => 'E', 'code' => 'E5', 'description' => 'Emergency equipment inspected and available', 'sort' => 54],

            // F — Environmental Performance Evaluation
            ['category' => 'F', 'code' => 'F1', 'description' => 'Environmental KPIs defined and monitored', 'sort' => 60],
            ['category' => 'F', 'code' => 'F2', 'description' => 'Environmental objectives and targets tracked', 'sort' => 61],
            ['category' => 'F', 'code' => 'F3', 'description' => 'Performance trends analyzed and reported', 'sort' => 62],
            ['category' => 'F', 'code' => 'F4', 'description' => 'Environmental non-conformances reviewed and addressed', 'sort' => 63],

            // G — Corrective & Preventive Actions (CAPA)
            ['category' => 'G', 'code' => 'G1', 'description' => 'Audit findings linked to CAPA system', 'sort' => 70],
            ['category' => 'G', 'code' => 'G2', 'description' => 'Root cause analysis completed (5 Whys / Fishbone)', 'sort' => 71],
            ['category' => 'G', 'code' => 'G3', 'description' => 'Actions assigned with deadlines and owners', 'sort' => 72],
            ['category' => 'G', 'code' => 'G4', 'description' => 'Action effectiveness verified before closure', 'sort' => 73],

            // H — Training, Competency & Awareness
            ['category' => 'H', 'code' => 'H1', 'description' => 'Environmental training records maintained', 'sort' => 80],
            ['category' => 'H', 'code' => 'H2', 'description' => 'Competency matrix updated and validated', 'sort' => 81],
            ['category' => 'H', 'code' => 'H3', 'description' => 'Environmental awareness programs conducted', 'sort' => 82],
            ['category' => 'H', 'code' => 'H4', 'description' => 'Contractor induction completed and recorded', 'sort' => 83],

            // I — Documentation & Records Control
            ['category' => 'I', 'code' => 'I1', 'description' => 'Controlled documents updated and approved', 'sort' => 90],
            ['category' => 'I', 'code' => 'I2', 'description' => 'Document version control maintained', 'sort' => 91],
            ['category' => 'I', 'code' => 'I3', 'description' => 'Records retention policy implemented', 'sort' => 92],
            ['category' => 'I', 'code' => 'I4', 'description' => 'Environmental reports properly archived and accessible', 'sort' => 93],
        ];
    }

    public static function categoryLabels(): array
    {
        return [
            'A' => 'A — Compliance & Legal Requirements',
            'B' => 'B — Environmental Aspects & Impacts',
            'C' => 'C — Monitoring & Measurement',
            'D' => 'D — Operational Controls',
            'E' => 'E — Emergency Preparedness & Response',
            'F' => 'F — Performance Evaluation',
            'G' => 'G — Corrective & Preventive Actions',
            'H' => 'H — Training & Competency',
            'I' => 'I — Documentation & Records',
        ];
    }

    // Score: Compliant=1.0, Partial=0.5, Non-Compliant=0.0, N/A excluded
    public static function computeScore(EnvironmentalAudit $audit): array
    {
        $items      = $audit->checklistItems()->get();
        $applicable = $items->filter(fn ($i) => $i->compliance_status !== 'not_applicable');
        $total      = $applicable->count();

        if ($total === 0) {
            return ['score' => 0.00, 'rating' => null];
        }

        $weighted = $applicable->sum(fn ($i) => match ($i->compliance_status) {
            'compliant'            => 1.0,
            'partially_compliant'  => 0.5,
            default                => 0.0,
        });

        $score  = round(($weighted / $total) * 100, 2);
        $rating = self::scoreToRating($score);

        return ['score' => $score, 'rating' => $rating];
    }

    public static function scoreToRating(float $score): string
    {
        return match (true) {
            $score >= 90 => 'excellent',
            $score >= 75 => 'good',
            $score >= 50 => 'fair',
            default      => 'poor',
        };
    }

    public static function ratingLabel(string $rating): string
    {
        return match ($rating) {
            'excellent' => 'Excellent (90–100%)',
            'good'      => 'Good (75–89%)',
            'fair'      => 'Fair (50–74%)',
            'poor'      => 'Poor (<50%)',
            default     => ucfirst($rating),
        };
    }

    public static function ratingColor(string $rating): string
    {
        return match ($rating) {
            'excellent' => 'success',
            'good'      => 'info',
            'fair'      => 'warning',
            'poor'      => 'danger',
            default     => 'gray',
        };
    }

    public static function findingRiskLevel(int $likelihood, int $severity): string
    {
        $score = $likelihood * $severity;
        return match (true) {
            $score >= 15 => 'critical',
            $score >= 10 => 'high',
            $score >= 5  => 'medium',
            default      => 'low',
        };
    }

    public static function nextAuditNumber(): string
    {
        $year  = now()->format('Y');
        $count = EnvironmentalAudit::whereYear('created_at', $year)->withTrashed()->count() + 1;
        return 'EA/' . $year . '/' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
