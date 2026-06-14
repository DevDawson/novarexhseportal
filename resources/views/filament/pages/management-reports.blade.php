<x-filament-panels::page>

    <div class="space-y-6">

        {{-- PAYROLL REPORTS --}}
        <div>
            <h3 class="text-sm font-semibold uppercase tracking-widest text-gray-500 mb-3">
                Payroll Reports
            </h3>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['action' => 'payrollRegister', 'label' => '1. Payroll Register', 'desc' => 'All staff earnings & deductions for the selected month', 'format' => 'PDF'],
                    ['action' => 'departmentPayroll', 'label' => '3. Department Payroll', 'desc' => 'Payroll totals grouped by department', 'format' => 'PDF'],
                    ['action' => 'overtimeReport', 'label' => '5. Overtime Report', 'desc' => 'Staff who worked overtime hours in the selected period', 'format' => 'CSV'],
                    ['action' => 'salaryCostReport', 'label' => '11. Salary Cost Report', 'desc' => 'Total payroll cost summary including employer contributions', 'format' => 'PDF'],
                    ['action' => 'bankTransferSchedule', 'label' => '12. Bank Transfer Schedule', 'desc' => 'Staff names, bank accounts, net salary amounts for bank payment', 'format' => 'PDF'],
                    ['action' => 'annualPayrollSummary', 'label' => '14. Annual Payroll Summary', 'desc' => 'Month-by-month payroll totals for a full year', 'format' => 'PDF'],
                ] as $r)
                    <x-filament::section compact>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $r['label'] }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $r['desc'] }}</p>
                            </div>
                            <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-mono font-medium {{ $r['format'] === 'PDF' ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700' }}">
                                {{ $r['format'] }}
                            </span>
                        </div>
                        <div class="mt-3">
                            {{ ($this->{$r['action'].'Action'}())->render() }}
                        </div>
                    </x-filament::section>
                @endforeach
            </div>
        </div>

        {{-- STATUTORY REPORTS --}}
        <div>
            <h3 class="text-sm font-semibold uppercase tracking-widest text-gray-500 mb-3">
                Statutory Compliance Reports (TRA / NSSF / WCF / VETA)
            </h3>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['action' => 'payeReport', 'label' => '7. PAYE Report', 'desc' => 'PAYE deductions for TRA monthly return', 'authority' => 'TRA'],
                    ['action' => 'nssfReport', 'label' => '8. NSSF Report', 'desc' => 'Employee + Employer NSSF contributions', 'authority' => 'NSSF'],
                    ['action' => 'wcfReport', 'label' => '9. WCF Report', 'desc' => 'Workers Compensation Fund employer contributions', 'authority' => 'WCF'],
                    ['action' => 'sdlReport', 'label' => '10. SDL Report', 'desc' => 'Skills Development Levy (4.5%) for VETA remittance', 'authority' => 'VETA'],
                ] as $r)
                    <x-filament::section compact>
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $r['label'] }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $r['desc'] }}</p>
                            </div>
                            <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-mono font-medium bg-yellow-50 text-yellow-700">
                                {{ $r['authority'] }}
                            </span>
                        </div>
                        <div class="mt-3">
                            {{ ($this->{$r['action'].'Action'}())->render() }}
                        </div>
                    </x-filament::section>
                @endforeach
            </div>
        </div>

        {{-- HR / ATTENDANCE / COST REPORTS --}}
        <div>
            <h3 class="text-sm font-semibold uppercase tracking-widest text-gray-500 mb-3">
                HR, Attendance & Cost Reports
            </h3>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['action' => 'attendanceSummary', 'label' => '6. Attendance Summary', 'desc' => 'Days present/absent, total hours per staff member', 'format' => 'CSV'],
                    ['action' => 'staffCostByDepartment', 'label' => '15. Staff Cost by Department', 'desc' => 'Total payroll cost analysed by department', 'format' => 'CSV'],
                    ['action' => 'staffCostByProject', 'label' => '16. Staff Cost by Project', 'desc' => 'Field expenses allocated per project (staff costs)', 'format' => 'CSV'],
                    ['action' => 'employeeEarningsHistory', 'label' => '17. Employee Earnings History', 'desc' => 'Full year payslip history for a single employee', 'format' => 'CSV'],
                    ['action' => 'projectPayrollCost', 'label' => '4. Project Payroll Cost', 'desc' => 'Approved field expenses grouped by project', 'format' => 'CSV'],
                ] as $r)
                    <x-filament::section compact>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $r['label'] }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $r['desc'] }}</p>
                            </div>
                            <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-mono font-medium {{ $r['format'] === 'PDF' ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700' }}">
                                {{ $r['format'] }}
                            </span>
                        </div>
                        <div class="mt-3">
                            {{ ($this->{$r['action'].'Action'}())->render() }}
                        </div>
                    </x-filament::section>
                @endforeach
            </div>
        </div>

        <p class="text-xs text-gray-400 mt-4">
            PDF reports download directly. CSV reports open in Excel/Sheets.
            Individual Payslips (#2) are generated from HR &amp; Payroll → Payroll → [Payslip button per record].
        </p>

    </div>

</x-filament-panels::page>
