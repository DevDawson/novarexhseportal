<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\CorporateDocument;
use App\Models\Deliverable;
use App\Models\Department;
use App\Models\EsiaAudit;
use App\Models\FieldExpense;
use App\Models\Incident;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Payroll;
use App\Models\PettyCashTransaction;
use App\Models\Project;
use App\Models\Risk;
use App\Models\Staff;
use App\Models\Tender;
use App\Models\User;
use App\Services\PayrollCalculationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // ------------------------------------------------------------
        // 1. Departments
        // ------------------------------------------------------------
        $departments = [
            'Management' => Department::firstOrCreate(['name' => 'Management'], ['description' => 'Executive & administration']),
            'HSE & Technical' => Department::firstOrCreate(['name' => 'HSE & Technical'], ['description' => 'HSE consulting, ESIA, audits']),
            'Finance' => Department::firstOrCreate(['name' => 'Finance'], ['description' => 'Accounts, payroll, invoicing']),
            'HR & Admin' => Department::firstOrCreate(['name' => 'HR & Admin'], ['description' => 'Human resources & administration']),
            'Business Development' => Department::firstOrCreate(['name' => 'Business Development'], ['description' => 'Tenders & client relations']),
            'IT' => Department::firstOrCreate(['name' => 'IT'], ['description' => 'Systems & infrastructure']),
        ];

        // ------------------------------------------------------------
        // 2. Users - one per company role (password: "password" for all)
        // ------------------------------------------------------------
        $userDefs = [
            ['name' => 'Amani Mwakalinga', 'email' => 'md@webmastercrew.online', 'role' => 'md', 'dept' => 'Management', 'title' => 'Managing Director'],
            ['name' => 'Grace Mushi', 'email' => 'hr@webmastercrew.online', 'role' => 'hr_director', 'dept' => 'HR & Admin', 'title' => 'HR Director'],
            ['name' => 'Daniel Kessy', 'email' => 'bd@webmastercrew.online', 'role' => 'business_director', 'dept' => 'Business Development', 'title' => 'Business Director'],
            ['name' => 'Fatuma Salum', 'email' => 'accounts@webmastercrew.online', 'role' => 'accountant', 'dept' => 'Finance', 'title' => 'Accountant'],
            ['name' => 'Joseph Mbwana', 'email' => 'it@webmastercrew.online', 'role' => 'it_technician', 'dept' => 'IT', 'title' => 'IT Technician'],
            ['name' => 'Neema Chacha', 'email' => 'hse@webmastercrew.online', 'role' => 'hse_staff', 'dept' => 'HSE & Technical', 'title' => 'HSE Officer'],
            ['name' => 'Rehema Juma', 'email' => 'secretary@webmastercrew.online', 'role' => 'secretary', 'dept' => 'HR & Admin', 'title' => 'Secretary'],
            // Extra HSE staff for variety
            ['name' => 'Peter Mwamba', 'email' => 'hse2@webmastercrew.online', 'role' => 'hse_staff', 'dept' => 'HSE & Technical', 'title' => 'Environmental Officer'],
        ];

        $users = [];

        foreach ($userDefs as $def) {
            $user = User::firstOrCreate(
                ['email' => $def['email']],
                [
                    'name' => $def['name'],
                    'password' => Hash::make('password'),
                    'department_id' => $departments[$def['dept']]->id,
                    'job_title' => $def['title'],
                    'phone' => '+255 7'.rand(10000000, 99999999),
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            if (! $user->hasRole($def['role'])) {
                $user->assignRole($def['role']);
            }

            $users[$def['role']][] = $user;
        }

        // ------------------------------------------------------------
        // 3. Staff Registry (linked to some users)
        // ------------------------------------------------------------
        $staffDefs = [
            ['user' => $users['md'][0], 'staff_no' => 'WMC-EMP-001', 'salary' => 4_500_000, 'type' => 'permanent', 'dept' => 'Management'],
            ['user' => $users['hr_director'][0], 'staff_no' => 'WMC-EMP-002', 'salary' => 2_800_000, 'type' => 'permanent', 'dept' => 'HR & Admin'],
            ['user' => $users['business_director'][0], 'staff_no' => 'WMC-EMP-003', 'salary' => 3_000_000, 'type' => 'permanent', 'dept' => 'Business Development'],
            ['user' => $users['accountant'][0], 'staff_no' => 'WMC-EMP-004', 'salary' => 2_200_000, 'type' => 'permanent', 'dept' => 'Finance'],
            ['user' => $users['it_technician'][0], 'staff_no' => 'WMC-EMP-005', 'salary' => 1_500_000, 'type' => 'contract', 'dept' => 'IT'],
            ['user' => $users['hse_staff'][0], 'staff_no' => 'WMC-EMP-006', 'salary' => 1_800_000, 'type' => 'permanent', 'dept' => 'HSE & Technical'],
            ['user' => $users['secretary'][0], 'staff_no' => 'WMC-EMP-007', 'salary' => 900_000, 'type' => 'permanent', 'dept' => 'HR & Admin'],
            ['user' => $users['hse_staff'][1], 'staff_no' => 'WMC-EMP-008', 'salary' => 1_600_000, 'type' => 'contract', 'dept' => 'HSE & Technical'],
        ];

        $staffRecords = [];

        foreach ($staffDefs as $i => $def) {
            /** @var User $user */
            $user = $def['user'];
            [$first, $last] = array_pad(explode(' ', $user->name, 2), 2, '');

            $staff = Staff::firstOrCreate(
                ['staff_no' => $def['staff_no']],
                [
                    'user_id' => $user->id,
                    'first_name' => $first,
                    'last_name' => $last,
                    'gender' => $i % 2 === 0 ? 'male' : 'female',
                    'date_of_birth' => now()->subYears(rand(25, 50))->subDays(rand(0, 365)),
                    'national_id' => '19'.rand(80, 99).'0'.rand(100000000000, 999999999999),
                    'nssf_no' => 'NSSF-'.rand(100000, 999999),
                    'tin_no' => rand(100, 999).'-'.rand(100, 999).'-'.rand(100, 999),
                    'nhif_no' => 'NHIF-'.rand(100000, 999999),
                    'job_title' => $user->job_title,
                    'department_id' => $departments[$def['dept']]->id,
                    'employment_type' => $def['type'],
                    'date_joined' => now()->subMonths(rand(6, 36)),
                    'basic_salary' => $def['salary'],
                    'bank_name' => 'CRDB Bank',
                    'bank_account_no' => '01'.rand(1000000000, 2147483647),
                    'status' => 'active',
                ]
            );

            $staffRecords[] = $staff;
        }

        // ------------------------------------------------------------
        // 4. Clients
        // ------------------------------------------------------------
        $clients = [
            Client::firstOrCreate(['company_name' => 'NOVAREX Mining Co. Ltd'], [
                'contact_person' => 'Eng. Hassan Mrema',
                'email' => 'info@novarex.co.tz',
                'phone' => '+255 712 345 678',
                'address' => 'Plot 45, Industrial Area, Mwanza',
                'region' => 'Mwanza',
                'tin_number' => '101-234-567',
                'client_type' => 'private',
                'status' => 'active',
            ]),
            Client::firstOrCreate(['company_name' => 'Tanzania Petroleum Development Corporation (TPDC)'], [
                'contact_person' => 'Mr. John Kapinga',
                'email' => 'procurement@tpdc.co.tz',
                'phone' => '+255 22 211 5453',
                'address' => 'TPDC House, Kurasini, Dar es Salaam',
                'region' => 'Dar es Salaam',
                'tin_number' => '100-987-654',
                'client_type' => 'government',
                'status' => 'active',
            ]),
            Client::firstOrCreate(['company_name' => 'Green Future NGO'], [
                'contact_person' => 'Ms. Asha Nuru',
                'email' => 'contact@greenfuture.org',
                'phone' => '+255 754 222 333',
                'address' => 'Mikocheni, Dar es Salaam',
                'region' => 'Dar es Salaam',
                'tin_number' => '102-345-678',
                'client_type' => 'ngo',
                'status' => 'active',
            ]),
            Client::firstOrCreate(['company_name' => 'JARICO Electrical Suppliers Ltd'], [
                'contact_person' => 'Mr. Elias Sanga',
                'email' => 'sales@jarico.co.tz',
                'phone' => '+255 28 250 1122',
                'address' => 'Nyerere Road, Mwanza',
                'region' => 'Mwanza',
                'tin_number' => '103-456-789',
                'client_type' => 'private',
                'status' => 'active',
            ]),
        ];

        // ------------------------------------------------------------
        // 5. Projects
        // ------------------------------------------------------------
        $projects = [
            Project::firstOrCreate(['project_code' => 'WMC-2026-001'], [
                'client_id' => $clients[0]->id,
                'title' => 'ESIA for Mwanza Gold Processing Plant Expansion',
                'description' => 'Full Environmental and Social Impact Assessment for the proposed expansion of the gold processing plant.',
                'service_type' => 'esia',
                'project_manager_id' => $users['hse_staff'][0]->id,
                'start_date' => now()->subMonths(2),
                'end_date' => now()->addMonths(2),
                'contract_value' => 85_000_000,
                'location' => 'Mwanza',
                'status' => 'ongoing',
            ]),
            Project::firstOrCreate(['project_code' => 'WMC-2026-002'], [
                'client_id' => $clients[1]->id,
                'title' => 'Annual OHS Compliance Audit - Mtwara Gas Terminal',
                'description' => 'Occupational health and safety compliance audit covering OSHA requirements.',
                'service_type' => 'environmental_audit',
                'project_manager_id' => $users['hse_staff'][1]->id,
                'start_date' => now()->subMonth(),
                'end_date' => now()->addWeeks(3),
                'contract_value' => 42_000_000,
                'location' => 'Mtwara',
                'status' => 'ongoing',
            ]),
            Project::firstOrCreate(['project_code' => 'WMC-2026-003'], [
                'client_id' => $clients[2]->id,
                'title' => 'Community HSE Awareness Training Programme',
                'description' => 'Series of HSE training workshops for community members near project sites.',
                'service_type' => 'training',
                'project_manager_id' => $users['hse_staff'][0]->id,
                'start_date' => now()->subWeeks(2),
                'end_date' => now()->addWeeks(2),
                'contract_value' => 18_500_000,
                'location' => 'Dar es Salaam',
                'status' => 'ongoing',
            ]),
            Project::firstOrCreate(['project_code' => 'WMC-2025-014'], [
                'client_id' => $clients[3]->id,
                'title' => 'Environmental Compliance Review - Warehouse Facility',
                'description' => 'Completed compliance review and recommendations report.',
                'service_type' => 'consultancy',
                'project_manager_id' => $users['hse_staff'][1]->id,
                'start_date' => now()->subMonths(5),
                'end_date' => now()->subMonths(1),
                'contract_value' => 12_000_000,
                'location' => 'Mwanza',
                'status' => 'completed',
            ]),
        ];

        // ------------------------------------------------------------
        // 6. Tenders (Business Development pipeline)
        // ------------------------------------------------------------
        $tenders = [
            ['title' => 'ESIA for Proposed Cement Factory - Tanga', 'entity' => 'Tanga Cement PLC', 'value' => 65_000_000, 'stage' => 'submitted', 'prob' => 50],
            ['title' => 'OHS Audit Framework Contract - 3 Year', 'entity' => 'TPDC', 'value' => 150_000_000, 'stage' => 'shortlisted', 'prob' => 75],
            ['title' => 'Environmental Monitoring Services - Geita Gold Mine', 'entity' => 'Geita Gold Mining Ltd', 'value' => 95_000_000, 'stage' => 'won', 'prob' => 100],
            ['title' => 'Waste Management Audit - Dodoma Industrial Park', 'entity' => 'Dodoma Industrial Development Corp', 'value' => 38_000_000, 'stage' => 'prequalification', 'prob' => 25],
            ['title' => 'Social Impact Study - Rural Water Project', 'entity' => 'Ministry of Water', 'value' => 28_000_000, 'stage' => 'lost', 'prob' => 0],
        ];

        foreach ($tenders as $i => $t) {
            Tender::firstOrCreate(['tender_title' => $t['title']], [
                'client_id' => null,
                'tender_number' => 'TND-2026-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'procuring_entity' => $t['entity'],
                'description' => $t['title'].' - tender opportunity.',
                'estimated_value' => $t['value'],
                'currency' => 'TZS',
                'exchange_rate' => 1,
                'submission_deadline' => now()->addDays(rand(-30, 45)),
                'stage' => $t['stage'],
                'assigned_to' => $users['business_director'][0]->id,
                'win_probability' => $t['prob'],
                'notes' => 'Demo seed data.',
            ]);
        }

        // ------------------------------------------------------------
        // 7. Incidents (HSE)
        // ------------------------------------------------------------
        $incidents = [
            ['project' => $projects[0], 'type' => 'near_miss', 'severity' => 'low', 'desc' => 'Loose scaffolding plank noticed near sample collection point, corrected immediately.'],
            ['project' => $projects[0], 'type' => 'first_aid', 'severity' => 'low', 'desc' => 'Minor cut on hand while handling sampling equipment - first aid administered on site.'],
            ['project' => $projects[1], 'type' => 'environmental', 'severity' => 'medium', 'desc' => 'Small oil spill (less than 5L) observed near generator area, contained with absorbent pads.'],
            ['project' => $projects[1], 'type' => 'property_damage', 'severity' => 'medium', 'desc' => 'Vehicle reversing damaged a perimeter fence panel at the terminal site.'],
            ['project' => null, 'type' => 'near_miss', 'severity' => 'high', 'desc' => 'Office electrical wiring fault caused minor smoke - building evacuated as precaution.'],
        ];

        foreach ($incidents as $inc) {
            Incident::create([
                'project_id' => $inc['project']?->id,
                'reported_by' => $users['hse_staff'][array_rand($users['hse_staff'])]->id,
                'incident_date' => now()->subDays(rand(1, 60)),
                'location' => $inc['project']?->location ?? 'Head Office, Dar es Salaam',
                'incident_type' => $inc['type'],
                'severity' => $inc['severity'],
                'description' => $inc['desc'],
                'immediate_action' => 'Area secured and supervisor notified.',
                'status' => collect(['open', 'investigating', 'closed'])->random(),
            ]);
        }

        // ------------------------------------------------------------
        // 8. Risks (Risk Register)
        // ------------------------------------------------------------
        $risks = [
            ['project' => $projects[0], 'title' => 'Community resistance to land access for sampling', 'category' => 'reputational', 'l' => 3, 's' => 3],
            ['project' => $projects[0], 'title' => 'Delayed lab results affecting report timeline', 'category' => 'operational', 'l' => 4, 's' => 2],
            ['project' => $projects[1], 'title' => 'Confined space entry during tank inspection', 'category' => 'safety', 'l' => 2, 's' => 5],
            ['project' => $projects[1], 'title' => 'Hazardous material exposure during sampling', 'category' => 'safety', 'l' => 2, 's' => 4],
            ['project' => $projects[2], 'title' => 'Low community turnout affecting training KPIs', 'category' => 'operational', 'l' => 3, 's' => 2],
        ];

        foreach ($risks as $r) {
            Risk::create([
                'project_id' => $r['project']->id,
                'risk_title' => $r['title'],
                'description' => $r['title'].'. Identified during project planning phase.',
                'category' => $r['category'],
                'likelihood' => $r['l'],
                'severity' => $r['s'],
                'risk_rating' => $r['l'] * $r['s'],
                'mitigation_measures' => 'Mitigation plan to be reviewed monthly with project team.',
                'risk_owner_id' => $users['hse_staff'][array_rand($users['hse_staff'])]->id,
                'status' => collect(['open', 'mitigated'])->random(),
                'review_date' => now()->addMonth(),
            ]);
        }

        // ------------------------------------------------------------
        // 9. ESIA / Audits
        // ------------------------------------------------------------
        EsiaAudit::firstOrCreate(['reference_number' => 'ESIA-NOVAREX-2026-01'], [
            'project_id' => $projects[0]->id,
            'type' => 'esia',
            'assessment_date' => now()->subWeeks(3),
            'lead_assessor_id' => $users['hse_staff'][0]->id,
            'findings_summary' => 'Baseline environmental conditions documented; key concerns relate to dust emissions and water usage.',
            'recommendations' => 'Implement dust suppression measures and water recycling system.',
            'status' => 'submitted',
        ]);

        EsiaAudit::firstOrCreate(['reference_number' => 'OHS-TPDC-2026-01'], [
            'project_id' => $projects[1]->id,
            'type' => 'ohs_audit',
            'assessment_date' => now()->subWeeks(1),
            'lead_assessor_id' => $users['hse_staff'][1]->id,
            'findings_summary' => 'Overall good compliance; minor gaps in PPE record-keeping and fire extinguisher inspection logs.',
            'recommendations' => 'Update PPE issuance register and monthly fire safety checklist.',
            'status' => 'draft',
        ]);

        // ------------------------------------------------------------
        // 10. Field Expenses
        // ------------------------------------------------------------
        $expenseDefs = [
            ['project' => $projects[0], 'staff' => $staffRecords[5], 'category' => 'fuel', 'amount' => 180_000, 'status' => 'approved'],
            ['project' => $projects[0], 'staff' => $staffRecords[5], 'category' => 'per_diem', 'amount' => 250_000, 'status' => 'approved'],
            ['project' => $projects[0], 'staff' => $staffRecords[5], 'category' => 'accommodation', 'amount' => 320_000, 'status' => 'pending'],
            ['project' => $projects[1], 'staff' => $staffRecords[7], 'category' => 'fuel', 'amount' => 220_000, 'status' => 'approved'],
            ['project' => $projects[1], 'staff' => $staffRecords[7], 'category' => 'per_diem', 'amount' => 300_000, 'status' => 'pending'],
            ['project' => $projects[2], 'staff' => $staffRecords[5], 'category' => 'transport', 'amount' => 90_000, 'status' => 'rejected'],
            ['project' => $projects[1], 'staff' => $staffRecords[7], 'category' => 'meals', 'amount' => 60_000, 'status' => 'reimbursed'],
        ];

        foreach ($expenseDefs as $exp) {
            $approved = in_array($exp['status'], ['approved', 'rejected', 'reimbursed']);

            FieldExpense::create([
                'project_id' => $exp['project']->id,
                'staff_id' => $exp['staff']->id,
                'expense_date' => now()->subDays(rand(1, 25)),
                'category' => $exp['category'],
                'description' => ucfirst($exp['category']).' expense for '.$exp['project']->title,
                'amount' => $exp['amount'],
                'status' => $exp['status'],
                'approved_by' => $approved ? $users['accountant'][0]->id : null,
                'approved_at' => $approved ? now()->subDays(rand(1, 10)) : null,
            ]);
        }

        // ------------------------------------------------------------
        // 11. Payroll - current month, for all staff
        // ------------------------------------------------------------
        $period = now()->startOfMonth();

        foreach ($staffRecords as $staff) {
            $exists = Payroll::where('staff_id', $staff->id)
                ->whereDate('payroll_period', $period)
                ->exists();

            if ($exists) {
                continue;
            }

            $allowances = round($staff->basic_salary * 0.10, 2); // 10% allowance for demo
            $gross = (float) $staff->basic_salary + $allowances;

            $calc = PayrollCalculationService::calculate($gross);

            Payroll::create([
                'staff_id' => $staff->id,
                'payroll_period' => $period,
                'basic_salary' => $staff->basic_salary,
                'allowances' => $allowances,
                'gross_salary' => $calc['gross_salary'],
                'paye' => $calc['paye'],
                'nssf' => $calc['nssf'],
                'nssf_employer' => $calc['nssf_employer'],
                'wcf' => $calc['wcf'],
                'nhif' => $calc['nhif'],
                'other_deductions' => 0,
                'net_salary' => $calc['net_salary'],
                'payment_status' => 'pending',
            ]);
        }

        // ------------------------------------------------------------
        // 12. Invoices (with line items)
        // ------------------------------------------------------------
        $invoiceDefs = [
            [
                'project' => $projects[0],
                'number' => 'INV-2026-0001',
                'date' => now()->subDays(20),
                'status' => 'sent',
                'items' => [
                    ['desc' => 'ESIA - Phase 1 Mobilisation Fee', 'qty' => 1, 'price' => 25_000_000],
                    ['desc' => 'Field Sampling & Lab Analysis', 'qty' => 1, 'price' => 12_000_000],
                ],
            ],
            [
                'project' => $projects[3],
                'number' => 'INV-2025-0098',
                'date' => now()->subMonths(1),
                'status' => 'paid',
                'paid' => true,
                'items' => [
                    ['desc' => 'Environmental Compliance Review - Final Report', 'qty' => 1, 'price' => 12_000_000],
                ],
            ],
            [
                'project' => $projects[2],
                'number' => 'INV-2026-0002',
                'date' => now()->subDays(5),
                'status' => 'draft',
                'items' => [
                    ['desc' => 'HSE Training Workshop - Session 1', 'qty' => 2, 'price' => 3_500_000],
                    ['desc' => 'Training Materials & Logistics', 'qty' => 1, 'price' => 1_500_000],
                ],
            ],
        ];

        foreach ($invoiceDefs as $inv) {
            $invoice = Invoice::firstOrCreate(['invoice_number' => $inv['number']], [
                'client_id' => $inv['project']->client_id,
                'project_id' => $inv['project']->id,
                'invoice_date' => $inv['date'],
                'due_date' => $inv['date']->copy()->addDays(30),
                'subtotal' => 0,
                'vat' => 0,
                'total_amount' => 0,
                'amount_paid' => 0,
                'status' => $inv['status'],
                'created_by' => $users['accountant'][0]->id,
            ]);

            if ($invoice->items()->count() === 0) {
                $subtotal = 0;

                foreach ($inv['items'] as $item) {
                    $amount = $item['qty'] * $item['price'];
                    $subtotal += $amount;

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'description' => $item['desc'],
                        'quantity' => $item['qty'],
                        'unit_price' => $item['price'],
                        'amount' => $amount,
                    ]);
                }

                $vat = round($subtotal * 0.18, 2);
                $total = $subtotal + $vat;

                $invoice->update([
                    'subtotal' => $subtotal,
                    'vat' => $vat,
                    'total_amount' => $total,
                    'amount_paid' => ($inv['paid'] ?? false) ? $total : 0,
                ]);
            }
        }

        // ------------------------------------------------------------
        // 13. Petty Cash Transactions
        // ------------------------------------------------------------
        $pettyCashDefs = [
            ['type' => 'top_up', 'category' => null, 'amount' => 1_000_000, 'desc' => 'Monthly petty cash top-up from main account'],
            ['type' => 'utility_payment', 'category' => 'electricity', 'amount' => 180_000, 'desc' => 'LUKU tokens - Head Office'],
            ['type' => 'utility_payment', 'category' => 'water', 'amount' => 45_000, 'desc' => 'DAWASA water bill - Head Office'],
            ['type' => 'utility_payment', 'category' => 'internet', 'amount' => 120_000, 'desc' => 'Office internet subscription'],
            ['type' => 'expense', 'category' => 'office_supplies', 'amount' => 65_000, 'desc' => 'Stationery and printing supplies'],
            ['type' => 'expense', 'category' => 'transport', 'amount' => 30_000, 'desc' => 'Boda boda fares for document delivery'],
        ];

        $runningBalance = 0;

        foreach ($pettyCashDefs as $i => $pc) {
            $delta = $pc['type'] === 'top_up' ? $pc['amount'] : -$pc['amount'];
            $runningBalance += $delta;

            PettyCashTransaction::create([
                'transaction_type' => $pc['type'],
                'category' => $pc['category'],
                'amount' => $pc['amount'],
                'description' => $pc['desc'],
                'transaction_date' => now()->subDays(30 - ($i * 4)),
                'recorded_by' => $users['accountant'][0]->id,
                'project_id' => null,
                'balance_after' => $runningBalance,
            ]);
        }

        // ------------------------------------------------------------
        // 14. Deliverables
        // ------------------------------------------------------------
        $deliverableDefs = [
            ['project' => $projects[0], 'title' => 'ESIA Scoping Report', 'code' => 'WMC-2026-001-RPT-001', 'type' => 'report', 'status' => 'approved', 'due' => now()->subDays(10)],
            ['project' => $projects[0], 'title' => 'ESIA Draft Report', 'code' => 'WMC-2026-001-RPT-002', 'type' => 'report', 'status' => 'client_review', 'due' => now()->addDays(5)],
            ['project' => $projects[1], 'title' => 'OHS Audit Findings Report', 'code' => 'WMC-2026-002-RPT-001', 'type' => 'report', 'status' => 'internal_review', 'due' => now()->addDays(3)],
            ['project' => $projects[2], 'title' => 'Training Completion Certificate Template', 'code' => 'WMC-2026-003-CRT-001', 'type' => 'certificate', 'status' => 'draft', 'due' => now()->addDays(10)],
            ['project' => $projects[3], 'title' => 'Environmental Compliance Final Report', 'code' => 'WMC-2025-014-RPT-001', 'type' => 'report', 'status' => 'approved', 'due' => now()->subMonths(1)],
        ];

        foreach ($deliverableDefs as $d) {
            Deliverable::firstOrCreate(['document_code' => $d['code']], [
                'project_id' => $d['project']->id,
                'document_title' => $d['title'],
                'document_type' => $d['type'],
                'revision_no' => 'A',
                'status' => $d['status'],
                'prepared_by' => $users['hse_staff'][array_rand($users['hse_staff'])]->id,
                'reviewed_by' => $d['status'] !== 'draft' ? $users['md'][0]->id : null,
                'due_date' => $d['due'],
                'submission_date' => $d['status'] === 'approved' ? $d['due'] : null,
            ]);
        }

        // ------------------------------------------------------------
        // 15. Corporate Documents
        // ------------------------------------------------------------
        $corporateDocs = [
            ['title' => 'OSHA Workplace Registration Certificate', 'category' => 'certificate', 'number' => 'OSHA/REG/2025/1187', 'issue' => now()->subMonths(8), 'expiry' => now()->addMonths(4)],
            ['title' => 'Business License - Webmaster Crew', 'category' => 'license', 'number' => 'BRELA/2024/55821', 'issue' => now()->subYear(), 'expiry' => now()->addDays(20)],
            ['title' => 'TIN Certificate', 'category' => 'certificate', 'number' => 'TIN-100-555-321', 'issue' => now()->subYears(2), 'expiry' => null],
            ['title' => 'HSE Policy Manual v2', 'category' => 'manual', 'number' => null, 'issue' => now()->subMonths(3), 'expiry' => null],
            ['title' => 'Vehicle Insurance - Toyota Hilux (T123 ABC)', 'category' => 'certificate', 'number' => 'INS-2026-00231', 'issue' => now()->subMonths(10), 'expiry' => now()->subDays(5)],
        ];

        foreach ($corporateDocs as $doc) {
            CorporateDocument::firstOrCreate(['title' => $doc['title']], [
                'category' => $doc['category'],
                'document_number' => $doc['number'],
                'file_path' => 'corporate-documents/placeholder.pdf',
                'issue_date' => $doc['issue'],
                'expiry_date' => $doc['expiry'],
                'uploaded_by' => $users['secretary'][0]->id,
                'status' => $doc['expiry'] && $doc['expiry']->isPast() ? 'expired' : 'active',
            ]);
        }

        $this->command?->info('Demo data seeded successfully. Login with any of the seeded emails + password "password".');
    }
}
