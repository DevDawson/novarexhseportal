<?php

namespace Database\Seeders;

use App\Models\MaturityDimension;
use App\Models\MaturityIndicator;
use Illuminate\Database\Seeder;

class MaturitySeeder extends Seeder
{
    public function run(): void
    {
        // Skip if already seeded
        if (MaturityDimension::count() > 0) {
            return;
        }

        $dimensions = [
            [
                'code' => 'A', 'name' => 'Leadership & Commitment', 'weight' => 15, 'sort_order' => 1,
                'indicators' => [
                    ['name' => 'Safety policy implementation',  'auto_source' => null],
                    ['name' => 'Management review frequency',   'auto_source' => null],
                    ['name' => 'Resource allocation for HSE',   'auto_source' => null],
                ],
            ],
            [
                'code' => 'B', 'name' => 'Risk Management', 'weight' => 15, 'sort_order' => 2,
                'indicators' => [
                    ['name' => 'HIRA / HAZOP / HAZID implementation', 'auto_source' => 'hazard_count'],
                    ['name' => 'Job Hazard Analysis (JHA) coverage',   'auto_source' => null],
                    ['name' => 'Risk register updates',                'auto_source' => 'risk_register_updates'],
                ],
            ],
            [
                'code' => 'C', 'name' => 'Legal & Compliance', 'weight' => 10, 'sort_order' => 3,
                'indicators' => [
                    ['name' => 'Legal register maintained & updated', 'auto_source' => 'legal_register'],
                    ['name' => 'Compliance audits completed',          'auto_source' => 'env_audits'],
                    ['name' => 'Permit-to-work system effectiveness',  'auto_source' => 'ptw_usage'],
                ],
            ],
            [
                'code' => 'D', 'name' => 'Operational Controls', 'weight' => 15, 'sort_order' => 4,
                'indicators' => [
                    ['name' => 'SOP / procedure availability',    'auto_source' => null],
                    ['name' => 'PPE compliance monitoring',        'auto_source' => null],
                    ['name' => 'Maintenance & inspection controls','auto_source' => null],
                ],
            ],
            [
                'code' => 'E', 'name' => 'Incident Management', 'weight' => 10, 'sort_order' => 5,
                'indicators' => [
                    ['name' => 'Incident reporting rate / speed',      'auto_source' => 'incident_reporting'],
                    ['name' => 'Root cause analysis completion',        'auto_source' => 'rca_completion'],
                    ['name' => 'Corrective actions (CAPA) closure rate','auto_source' => 'capa_closure'],
                ],
            ],
            [
                'code' => 'F', 'name' => 'Training & Competence', 'weight' => 10, 'sort_order' => 6,
                'indicators' => [
                    ['name' => 'Training coverage %',                            'auto_source' => 'training_coverage'],
                    ['name' => 'Competency certification tracking (NEBOSH/IOSH)','auto_source' => 'certifications'],
                    ['name' => 'Refresh / renewal training compliance',           'auto_source' => null],
                ],
            ],
            [
                'code' => 'G', 'name' => 'Emergency Preparedness', 'weight' => 10, 'sort_order' => 7,
                'indicators' => [
                    ['name' => 'Emergency drill frequency',          'auto_source' => null],
                    ['name' => 'Emergency response time targets met', 'auto_source' => null],
                    ['name' => 'Emergency equipment readiness',       'auto_source' => null],
                ],
            ],
            [
                'code' => 'H', 'name' => 'Environmental Management (EMS)', 'weight' => 10, 'sort_order' => 8,
                'indicators' => [
                    ['name' => 'Waste management KPIs tracked',   'auto_source' => 'waste_tracking'],
                    ['name' => 'Emissions / monitoring records',   'auto_source' => 'env_monitoring'],
                    ['name' => 'Environmental audits completed',   'auto_source' => 'env_audits'],
                ],
            ],
            [
                'code' => 'I', 'name' => 'Audit & Continuous Improvement', 'weight' => 5, 'sort_order' => 9,
                'indicators' => [
                    ['name' => 'Internal audit completion rate',     'auto_source' => 'ams_audits'],
                    ['name' => 'Non-conformity closure time',        'auto_source' => 'nc_closure'],
                    ['name' => 'Improvement action tracking (CAPA)', 'auto_source' => 'capa_closure'],
                ],
            ],
        ];

        foreach ($dimensions as $dimData) {
            $indicators = $dimData['indicators'];
            unset($dimData['indicators']);

            $dim = MaturityDimension::create($dimData);

            foreach ($indicators as $i => $ind) {
                MaturityIndicator::create([
                    'dimension_id' => $dim->id,
                    'name'         => $ind['name'],
                    'auto_source'  => $ind['auto_source'],
                    'sort_order'   => $i + 1,
                ]);
            }
        }
    }
}
