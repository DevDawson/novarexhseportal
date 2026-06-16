<?php

namespace App\Services;

use App\Models\PermitToWork;
use Illuminate\Support\Carbon;

class PermitToWorkService
{
    // ----------------------------------------------------------------
    // Work type metadata
    // ----------------------------------------------------------------

    public static function workTypeLabels(): array
    {
        return [
            'hot_work'           => 'Hot Work (Welding, Cutting, Grinding)',
            'cold_work'          => 'Cold Work (Mechanical Maintenance / Assembly)',
            'electrical_isolation'=> 'Electrical Work / Isolation (LOTO)',
            'confined_space'     => 'Confined Space Entry',
            'excavation'         => 'Excavation / Trenching Work',
            'working_at_height'  => 'Working at Height',
            'lifting_operations' => 'Lifting Operations (Crane, Hoist, Rigging)',
            'pressure_system'    => 'Pressure System Work',
            'chemical_handling'  => 'Chemical Handling / Exposure Work',
            'radiation_work'     => 'Radiation Work',
            'commissioning'      => 'Commissioning / Testing Work',
            'general_maintenance'=> 'General Maintenance Work',
            'general'            => 'General Work',
        ];
    }

    /** ISO 45001-based inherent risk classification per work type */
    public static function workTypeRisk(string $workType): string
    {
        if (in_array($workType, ['hot_work', 'electrical_isolation', 'confined_space', 'working_at_height', 'pressure_system', 'chemical_handling', 'radiation_work'], true)) {
            return 'high';
        }
        if (in_array($workType, ['excavation', 'lifting_operations', 'commissioning'], true)) {
            return 'medium';
        }
        return 'low';
    }

    /**
     * Derive final risk classification: higher of work-type inherent risk
     * OR L×S score classification (≥15 = high, ≥5 = medium, else low).
     */
    public static function riskClassification(int $riskScore, string $workType): string
    {
        $typeRisk  = self::workTypeRisk($workType);
        $scoreRisk = match (true) {
            $riskScore >= 15 => 'high',
            $riskScore >= 5  => 'medium',
            default          => 'low',
        };
        $order = ['low' => 0, 'medium' => 1, 'high' => 2];
        return ($order[$typeRisk] ?? 0) >= ($order[$scoreRisk] ?? 0) ? $typeRisk : $scoreRisk;
    }

    public static function requiresGasTestByDefault(string $permitType): bool
    {
        return in_array($permitType, ['confined_space', 'hot_work', 'chemical_handling']);
    }

    public static function requiresIsolationByDefault(string $permitType): bool
    {
        return in_array($permitType, ['electrical_isolation', 'confined_space', 'pressure_system']);
    }

    public static function requiresFireWatchByDefault(string $permitType): bool
    {
        return in_array($permitType, ['hot_work']);
    }

    public static function requiresBarricadingByDefault(string $permitType): bool
    {
        return in_array($permitType, ['excavation', 'working_at_height', 'lifting_operations', 'commissioning']);
    }

    public static function requiresEmergencyStandbyByDefault(string $permitType): bool
    {
        return in_array($permitType, ['confined_space', 'working_at_height', 'radiation_work']);
    }

    // ----------------------------------------------------------------
    // Default safety checklists per work type
    // ----------------------------------------------------------------

    public static function defaultChecklistItems(string $permitType): array
    {
        return match ($permitType) {
            'hot_work' => [
                'Fire extinguisher(s) available and accessible at work area',
                'Combustible materials removed or protected within 10m radius',
                'Fire watch assigned during work and for 30 minutes after completion',
                'Gas cylinders/hoses inspected — no leaks, correct flashback arrestors fitted',
                'Welding screens/curtains in place to protect others',
                'Area below and around the work checked for personnel and flammable materials',
                'Gas test confirmed — no flammable atmosphere present',
                'Hot work completion notification provided to fire watch',
            ],
            'confined_space' => [
                'Atmosphere tested before entry: O₂, LEL, H₂S, CO within safe limits',
                'Continuous gas monitoring equipment in place and calibrated',
                'Ventilation / forced air supply provided',
                'Rescue plan prepared and rescue equipment available at entry point',
                'Trained attendant (standby person) assigned outside the space at all times',
                'Communication method between entrant(s) and attendant established',
                'Entry/exit log maintained for all personnel entering the space',
                'Isolation of mechanical/electrical hazards into the space completed (LOTO)',
                'Entry permit signed and prominently displayed at entrance',
            ],
            'working_at_height' => [
                'Fall arrest / restraint equipment inspected and worn correctly',
                'Anchor points inspected and rated for the load',
                'Scaffold/platform/ladder inspected, tagged and fit for use',
                'Edge protection / guardrails in place where applicable',
                'Tools and materials secured (tethered) to prevent dropped objects',
                'Weather conditions checked (wind speed, rain, lightning)',
                'Rescue plan for a suspended worker (fall arrest) in place',
                'Exclusion zone below work area established and signed',
            ],
            'electrical_isolation' => [
                'Circuit / equipment identified and confirmed on single-line diagram',
                'Isolation point(s) opened and LOTO applied',
                'Absence of voltage confirmed using calibrated test instrument (prove dead)',
                'Isolation points clearly labelled with permit reference',
                'Authorized person retains isolation lock/key for duration of work',
                'Earthing / grounding applied where required',
                'Adjacent live conductors identified and guarded if within striking distance',
            ],
            'excavation' => [
                'Underground services located and marked (utility survey / drawings checked)',
                'Shoring, battering or benching adequate for soil type and depth',
                'Barricades and warning signage in place around the excavation',
                'Spoil placed at safe distance from excavation edge (min 0.5m)',
                'Safe means of access/egress (ladder) provided for excavations over 1.2m',
                'Excavation inspected daily (and after rain) by a competent person',
                'Dewatering plan in place if water ingress expected',
            ],
            'lifting_operations' => [
                'Lifting plan prepared and reviewed for the operation',
                'Crane / lifting equipment inspection certificate valid and in date',
                'Rigger, banksman and signaller assigned and briefed',
                'Load weight confirmed to be within Safe Working Load (SWL)',
                'Exclusion zone established around the lifting area',
                'Ground conditions / outrigger loading assessed and adequate',
                'Load path checked — clearance from overhead lines / structures confirmed',
                'Hand signals / radio communication agreed between all parties',
            ],
            'pressure_system' => [
                'System pressure fully depressurized and isolated before work',
                'LOTO applied at all isolation points — pressure vent/bleed confirmed',
                'Pressure relief valve functional and in-date (inspection certificate)',
                'System pressure certificate / PSSR inspection current',
                'No open flames near any potential residual hydrocarbon content',
                'Test pressure and equipment rated for the test medium',
                'Emergency depressurization procedure available and briefed',
            ],
            'chemical_handling' => [
                'Safety Data Sheet (SDS) reviewed for all chemicals involved',
                'Chemical compatibility confirmed — no reactive materials in proximity',
                'Spill containment in place (bund, drip tray, spill kit)',
                'Gas/vapour monitoring in place for toxic/flammable chemicals',
                'SCBA / appropriate respiratory protection available and tested',
                'Chemical-resistant PPE (gloves, suit) inspected and worn',
                'Emergency eyewash station confirmed within 10 seconds travel distance',
                'Waste disposal route for chemical waste confirmed prior to work',
            ],
            'radiation_work' => [
                'Radiation work permit issued by Radiation Safety Officer (RSO)',
                'Dose rate survey completed — restricted zone established',
                'Dosimeter / radiation badge issued to all personnel in the zone',
                'ALARA principle applied — time, distance, shielding controls in place',
                'Warning signs and barriers in place at zone boundary',
                'Source storage / transport certificate valid and in date',
                'Emergency exposure response procedure briefed to all workers',
                'Post-work radiological survey and clearance confirmed',
            ],
            'commissioning' => [
                'Commissioning procedure / test plan reviewed and approved',
                'Systems and equipment have been inspected and signed off as mechanically complete',
                'LOTO removed only after all checks are complete and permit issued',
                'Control room / operations notified before commissioning activities',
                'Emergency shutdown system (ESD) tested and functional',
                'Communication channels established between field and control room',
                'Commissioning team roles and responsibilities briefed',
            ],
            'general_maintenance' => [
                'Work area barricaded / signposted to warn others',
                'PPE appropriate for the task confirmed and worn',
                'Tools and equipment inspected before use',
                'Housekeeping maintained — work area kept clear of trip/slip hazards',
                'Emergency contacts and procedures briefed to all workers',
            ],
            default => [
                'Work area barricaded / signposted to warn others',
                'PPE appropriate for the task confirmed and worn',
                'Tools and equipment inspected before use',
                'Emergency contacts and procedures briefed to all workers',
            ],
        };
    }

    // ----------------------------------------------------------------
    // PPE options
    // ----------------------------------------------------------------

    public static function ppeOptions(): array
    {
        return [
            'hard_hat'          => 'Hard Hat / Safety Helmet',
            'safety_glasses'    => 'Safety Glasses / Goggles',
            'face_shield'       => 'Face Shield',
            'hearing_protection'=> 'Hearing Protection (Earmuffs / Plugs)',
            'respirator'        => 'Respirator / Dust Mask',
            'scba'              => 'SCBA (Self-Contained Breathing Apparatus)',
            'gloves'            => 'Safety Gloves (General)',
            'chemical_gloves'   => 'Chemical-Resistant Gloves',
            'safety_boots'      => 'Safety Boots / Steel-Toed',
            'hi_vis_vest'       => 'High-Visibility Vest / Coveralls',
            'fall_arrest_harness'=> 'Safety Harness / Fall Arrest',
            'welding_shield'    => 'Welding Shield / Mask',
            'fr_coveralls'      => 'Fire-Resistant Coveralls (FRC)',
            'chemical_suit'     => 'Chemical Protective Suit',
            'radiation_badge'   => 'Radiation Dosimeter Badge',
        ];
    }

    // ----------------------------------------------------------------
    // Risk classification label/color helpers
    // ----------------------------------------------------------------

    public static function riskClassificationColor(string $classification): string
    {
        return match ($classification) {
            'high'   => 'danger',
            'medium' => 'warning',
            default  => 'success',
        };
    }

    // ----------------------------------------------------------------
    // Permit number auto-generation: PTW-YYYY-MM-NNNN
    // ----------------------------------------------------------------

    public static function nextPermitNumber(Carbon $date): string
    {
        $prefix = 'PTW-' . $date->format('Y-m') . '-';

        $lastNumber = PermitToWork::where('permit_number', 'like', $prefix . '%')
            ->selectRaw('MAX(CAST(SUBSTRING(permit_number, ' . (strlen($prefix) + 1) . ') AS UNSIGNED)) as max_num')
            ->value('max_num');

        return $prefix . str_pad((string) (((int) $lastNumber) + 1), 4, '0', STR_PAD_LEFT);
    }
}
