<?php

namespace App\Services;

use App\Models\PermitToWork;
use Illuminate\Support\Carbon;

class PermitToWorkService
{
    /**
     * Standard pre-condition / safety checklist items per permit type.
     * Used to pre-populate the "Permit Checklist" repeater when a permit
     * type is selected, via the "Load Default Checklist" action.
     *
     * These are general industry-standard checks for the Tanzanian HSE
     * consulting context - the Issuer/Area Authority should add/remove
     * items as appropriate for the specific job.
     */
    public static function defaultChecklistItems(string $permitType): array
    {
        return match ($permitType) {
            'hot_work' => [
                'Fire extinguisher(s) available and accessible at work area',
                'Combustible materials removed or protected within 10m radius',
                'Fire watch assigned during work and for 30 minutes after completion',
                'Gas cylinders/hoses inspected - no leaks, correct flashback arrestors fitted',
                'Welding screens/curtains in place to protect others',
                'Area below and around the work checked for personnel and flammable materials',
                'Confirm no flammable atmosphere present (gas test if required)',
            ],
            'confined_space' => [
                'Atmosphere tested before entry: Oxygen, LEL, H2S, CO within safe limits',
                'Continuous gas monitoring equipment in place and calibrated',
                'Ventilation / forced air supply provided',
                'Rescue plan prepared and rescue equipment available at entry point',
                'Trained attendant (standby person) assigned outside the space at all times',
                'Communication method between entrant(s) and attendant established',
                'Entry/exit log maintained for all personnel entering the space',
                'Isolation of mechanical/electrical hazards into the space completed',
            ],
            'working_at_height' => [
                'Fall arrest / restraint equipment inspected and worn correctly',
                'Anchor points inspected and rated for the load',
                'Scaffold/platform/ladder inspected, tagged and fit for use',
                'Edge protection / guardrails in place where applicable',
                'Tools and materials secured (tethered) to prevent dropped objects',
                'Weather conditions checked (wind speed, rain, lightning)',
                'Rescue plan for a suspended worker (fall arrest) in place',
            ],
            'electrical_isolation' => [
                'Circuit / equipment identified and confirmed in single-line diagram',
                'Isolation point(s) opened and Lock-Out/Tag-Out (LOTO) applied',
                'Absence of voltage confirmed using a calibrated test instrument (prove dead)',
                'Isolation points clearly labelled with permit reference',
                'Authorized person retains isolation lock/key for the duration of work',
                'Earthing / grounding applied where required by the isolation procedure',
            ],
            'excavation' => [
                'Underground services located and marked (utility survey / drawings checked)',
                'Shoring, battering or benching adequate for the soil type and depth',
                'Barricades and warning signage in place around the excavation',
                'Spoil placed at a safe distance from the excavation edge',
                'Safe means of access/egress (ladder) provided for excavations over 1.2m',
                'Excavation inspected daily (and after rain) by a competent person',
            ],
            'lifting_operations' => [
                'Lifting plan prepared and reviewed for the operation',
                'Crane / lifting equipment inspection certificate valid and in date',
                'Rigger, banksman and signaller assigned and briefed',
                'Load weight confirmed to be within Safe Working Load (SWL)',
                'Exclusion zone established around the lifting area',
                'Ground conditions / outrigger loading assessed and adequate',
            ],
            'cold_work', 'general' => [
                'Work area barricaded / signposted to warn others',
                'PPE appropriate for the task confirmed and worn',
                'Tools and equipment inspected before use',
                'Housekeeping maintained - work area kept clear of trip/slip hazards',
                'Emergency contacts and procedures briefed to all workers involved',
            ],
            default => [],
        };
    }

    /**
     * Generate the next sequential permit number for a given date,
     * e.g. "PTW-2026-06-0001".
     */
    public static function nextPermitNumber(Carbon $date): string
    {
        $prefix = 'PTW-'.$date->format('Y-m').'-';

        $lastNumber = PermitToWork::where('permit_number', 'like', $prefix.'%')
            ->selectRaw('MAX(CAST(SUBSTRING(permit_number, '.(strlen($prefix) + 1).') AS UNSIGNED)) as max_num')
            ->value('max_num');

        $next = ((int) $lastNumber) + 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Whether permits requiring gas testing/isolation/high-risk controls
     * by default for this type (used to auto-toggle form fields).
     */
    public static function requiresGasTestByDefault(string $permitType): bool
    {
        return in_array($permitType, ['confined_space', 'hot_work']);
    }

    public static function requiresIsolationByDefault(string $permitType): bool
    {
        return in_array($permitType, ['electrical_isolation', 'confined_space']);
    }

    /**
     * Standard PPE options offered in the "PPE Required" checklist.
     */
    public static function ppeOptions(): array
    {
        return [
            'hard_hat' => 'Hard Hat / Safety Helmet',
            'safety_glasses' => 'Safety Glasses / Goggles',
            'hearing_protection' => 'Hearing Protection',
            'respirator' => 'Respirator / Dust Mask',
            'gloves' => 'Gloves',
            'safety_boots' => 'Safety Boots',
            'hi_vis_vest' => 'Hi-Vis Vest / Coveralls',
            'fall_arrest_harness' => 'Fall Arrest Harness',
            'face_shield' => 'Face Shield',
            'fr_coveralls' => 'Fire-Resistant Coveralls',
        ];
    }
}
