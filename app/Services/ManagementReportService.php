<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\FieldExpense;
use App\Models\Payroll;
use App\Models\Staff;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ManagementReportService
{
    // =========================================================
    // 1. PAYROLL REGISTER
    // =========================================================
    public static function payrollRegister(Carbon $period, ?int $departmentId = null): Collection
    {
        return Payroll::with(['staff.department'])
            ->whereYear('payroll_period', $period->year)
            ->whereMonth('payroll_period', $period->month)
            ->when($departmentId, fn ($q) => $q->whereHas('staff', fn ($s) => $s->where('department_id', $departmentId)))
            ->get()
            ->map(fn (Payroll $p) => [
                'staff_no' => $p->staff?->staff_no,
                'name' => $p->staff?->full_name,
                'department' => $p->staff?->department?->name,
                'employment_type' => str($p->employment_type)->replace('_', ' ')->title(),
                'gross_salary' => $p->gross_salary,
                'paye' => $p->paye,
                'nssf' => $p->nssf,
                'nhif' => $p->nhif,
                'loan_deduction' => $p->loan_deduction,
                'other_deductions' => $p->other_deductions,
                'net_salary' => $p->net_salary,
                'payment_status' => $p->payment_status,
            ]);
    }

    // =========================================================
    // 3. DEPARTMENT PAYROLL REPORT
    // =========================================================
    public static function departmentPayrollReport(Carbon $period): Collection
    {
        return Payroll::with(['staff.department'])
            ->whereYear('payroll_period', $period->year)
            ->whereMonth('payroll_period', $period->month)
            ->get()
            ->groupBy(fn (Payroll $p) => $p->staff?->department?->name ?? 'Unassigned')
            ->map(fn ($group, $dept) => [
                'department' => $dept,
                'staff_count' => $group->count(),
                'total_gross' => $group->sum('gross_salary'),
                'total_paye' => $group->sum('paye'),
                'total_nssf' => $group->sum('nssf'),
                'total_nhif' => $group->sum('nhif'),
                'total_net' => $group->sum('net_salary'),
                'total_sdl' => $group->sum('sdl'),
            ])
            ->values();
    }

    // =========================================================
    // 4. PROJECT PAYROLL COST REPORT
    // Uses Field Expenses as the staff-to-project link
    // =========================================================
    public static function projectPayrollCostReport(Carbon $period): Collection
    {
        return FieldExpense::with(['project', 'staff'])
            ->whereIn('status', ['approved', 'reimbursed'])
            ->whereYear('expense_date', $period->year)
            ->whereMonth('expense_date', $period->month)
            ->get()
            ->groupBy(fn ($e) => $e->project?->title ?? 'Unallocated')
            ->map(fn ($group, $project) => [
                'project' => $project,
                'total_field_expenses' => $group->sum('amount'),
                'expense_count' => $group->count(),
                'categories' => $group->groupBy('category')
                    ->map(fn ($c) => $c->sum('amount'))
                    ->toArray(),
            ])
            ->values();
    }

    // =========================================================
    // 5. OVERTIME REPORT
    // =========================================================
    public static function overtimeReport(Carbon $period, ?int $departmentId = null): Collection
    {
        return Payroll::with(['staff.department'])
            ->whereYear('payroll_period', $period->year)
            ->whereMonth('payroll_period', $period->month)
            ->where('overtime_hours', '>', 0)
            ->when($departmentId, fn ($q) => $q->whereHas('staff', fn ($s) => $s->where('department_id', $departmentId)))
            ->get()
            ->map(fn (Payroll $p) => [
                'staff_no' => $p->staff?->staff_no,
                'name' => $p->staff?->full_name,
                'department' => $p->staff?->department?->name,
                'overtime_hours' => $p->overtime_hours,
                'overtime_pay' => $p->overtime_pay,
            ]);
    }

    // =========================================================
    // 6. ATTENDANCE SUMMARY REPORT
    // =========================================================
    public static function attendanceSummary(Carbon $period, ?int $departmentId = null): Collection
    {
        return Staff::with(['department'])
            ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
            ->where('status', 'active')
            ->get()
            ->map(function (Staff $staff) use ($period) {
                $records = Attendance::where('staff_id', $staff->id)
                    ->whereYear('attendance_date', $period->year)
                    ->whereMonth('attendance_date', $period->month)
                    ->get();

                return [
                    'staff_no' => $staff->staff_no,
                    'name' => $staff->full_name,
                    'department' => $staff->department?->name,
                    'days_present' => $records->where('status', 'present')->count(),
                    'days_absent' => $records->where('status', 'absent')->count(),
                    'days_leave' => $records->where('status', 'leave')->count(),
                    'total_hours' => round($records->sum('hours_worked'), 2),
                    'overtime_hours' => round($records->sum('overtime_hours'), 2),
                ];
            });
    }

    // =========================================================
    // 7. PAYE REPORT
    // =========================================================
    public static function payeReport(Carbon $period): Collection
    {
        return Payroll::with(['staff'])
            ->whereYear('payroll_period', $period->year)
            ->whereMonth('payroll_period', $period->month)
            ->get()
            ->map(fn (Payroll $p) => [
                'staff_no' => $p->staff?->staff_no,
                'name' => $p->staff?->full_name,
                'tin_no' => $p->staff?->tin_no,
                'gross_salary' => $p->gross_salary,
                'nssf_employee' => $p->nssf,
                'taxable_income' => round((float)$p->gross_salary - (float)$p->nssf, 2),
                'paye' => $p->paye,
            ]);
    }

    // =========================================================
    // 8. NSSF REPORT
    // =========================================================
    public static function nssfReport(Carbon $period): Collection
    {
        return Payroll::with(['staff'])
            ->whereYear('payroll_period', $period->year)
            ->whereMonth('payroll_period', $period->month)
            ->whereNotIn('employment_type', ['consultant'])
            ->get()
            ->map(fn (Payroll $p) => [
                'staff_no' => $p->staff?->staff_no,
                'name' => $p->staff?->full_name,
                'nssf_no' => $p->staff?->nssf_no,
                'gross_salary' => $p->gross_salary,
                'nssf_employee' => $p->nssf,
                'nssf_employer' => $p->nssf_employer,
                'total_nssf' => round((float)$p->nssf + (float)$p->nssf_employer, 2),
            ]);
    }

    // =========================================================
    // 9. WCF REPORT
    // =========================================================
    public static function wcfReport(Carbon $period): Collection
    {
        return Payroll::with(['staff'])
            ->whereYear('payroll_period', $period->year)
            ->whereMonth('payroll_period', $period->month)
            ->get()
            ->map(fn (Payroll $p) => [
                'staff_no' => $p->staff?->staff_no,
                'name' => $p->staff?->full_name,
                'gross_salary' => $p->gross_salary,
                'wcf' => $p->wcf,
            ]);
    }

    // =========================================================
    // 10. SDL REPORT
    // =========================================================
    public static function sdlReport(Carbon $period): Collection
    {
        return Payroll::with(['staff.department'])
            ->whereYear('payroll_period', $period->year)
            ->whereMonth('payroll_period', $period->month)
            ->get()
            ->map(fn (Payroll $p) => [
                'staff_no' => $p->staff?->staff_no,
                'name' => $p->staff?->full_name,
                'department' => $p->staff?->department?->name,
                'gross_salary' => $p->gross_salary,
                'sdl' => $p->sdl,
            ]);
    }

    // =========================================================
    // 11. SALARY COST REPORT (totals summary)
    // =========================================================
    public static function salaryCostReport(Carbon $period): array
    {
        $rows = Payroll::whereYear('payroll_period', $period->year)
            ->whereMonth('payroll_period', $period->month)
            ->selectRaw('
                COUNT(*) as staff_count,
                COALESCE(SUM(gross_salary), 0) as total_gross,
                COALESCE(SUM(paye), 0) as total_paye,
                COALESCE(SUM(nssf), 0) as total_nssf_employee,
                COALESCE(SUM(nssf_employer), 0) as total_nssf_employer,
                COALESCE(SUM(wcf), 0) as total_wcf,
                COALESCE(SUM(sdl), 0) as total_sdl,
                COALESCE(SUM(nhif), 0) as total_nhif,
                COALESCE(SUM(net_salary), 0) as total_net,
                COALESCE(SUM(overtime_pay), 0) as total_overtime_pay
            ')
            ->first();

        $totalEmployerCost = (float)$rows->total_gross
            + (float)$rows->total_nssf_employer
            + (float)$rows->total_wcf
            + (float)$rows->total_sdl;

        return array_merge($rows->toArray(), ['total_employer_cost' => $totalEmployerCost]);
    }

    // =========================================================
    // 12. BANK TRANSFER SCHEDULE
    // =========================================================
    public static function bankTransferSchedule(Carbon $period): Collection
    {
        return Payroll::with(['staff'])
            ->whereYear('payroll_period', $period->year)
            ->whereMonth('payroll_period', $period->month)
            ->whereNotIn('employment_type', ['consultant'])
            ->get()
            ->map(fn (Payroll $p) => [
                'staff_no' => $p->staff?->staff_no,
                'name' => $p->staff?->full_name,
                'bank_name' => $p->staff?->bank_name,
                'account_no' => $p->staff?->bank_account_no,
                'mobile_number' => $p->staff?->user?->phone,
                'net_salary' => $p->net_salary,
                'payment_reference' => $p->payment_reference,
                'payment_status' => $p->payment_status,
            ]);
    }

    // =========================================================
    // 14. ANNUAL PAYROLL SUMMARY (12 months)
    // =========================================================
    public static function annualPayrollSummary(int $year): Collection
    {
        return collect(range(1, 12))->map(function (int $month) use ($year) {
            $rows = Payroll::whereYear('payroll_period', $year)
                ->whereMonth('payroll_period', $month)
                ->selectRaw('
                    COUNT(*) as staff_count,
                    COALESCE(SUM(gross_salary), 0) as total_gross,
                    COALESCE(SUM(paye), 0) as total_paye,
                    COALESCE(SUM(nssf), 0) as total_nssf,
                    COALESCE(SUM(nssf_employer), 0) as total_nssf_employer,
                    COALESCE(SUM(wcf), 0) as total_wcf,
                    COALESCE(SUM(sdl), 0) as total_sdl,
                    COALESCE(SUM(nhif), 0) as total_nhif,
                    COALESCE(SUM(net_salary), 0) as total_net
                ')
                ->first();

            return [
                'month' => Carbon::create($year, $month)->format('F'),
                'staff_count' => $rows->staff_count,
                'total_gross' => $rows->total_gross,
                'total_paye' => $rows->total_paye,
                'total_nssf' => $rows->total_nssf,
                'total_nssf_employer' => $rows->total_nssf_employer,
                'total_wcf' => $rows->total_wcf,
                'total_sdl' => $rows->total_sdl,
                'total_nhif' => $rows->total_nhif,
                'total_net' => $rows->total_net,
            ];
        });
    }

    // =========================================================
    // 15. STAFF COST BY DEPARTMENT
    // =========================================================
    public static function staffCostByDepartment(Carbon $period): Collection
    {
        return Payroll::with(['staff.department'])
            ->whereYear('payroll_period', $period->year)
            ->whereMonth('payroll_period', $period->month)
            ->get()
            ->groupBy(fn (Payroll $p) => $p->staff?->department?->name ?? 'Unassigned')
            ->map(fn ($group, $dept) => [
                'department' => $dept,
                'staff_count' => $group->count(),
                'total_gross' => $group->sum('gross_salary'),
                'total_net' => $group->sum('net_salary'),
                'total_paye' => $group->sum('paye'),
                'total_sdl' => $group->sum('sdl'),
                'total_wcf' => $group->sum('wcf'),
                'employer_total' => round(
                    $group->sum('gross_salary') + $group->sum('nssf_employer') + $group->sum('wcf') + $group->sum('sdl'),
                    2
                ),
            ])
            ->sortByDesc('total_gross')
            ->values();
    }

    // =========================================================
    // 16. STAFF COST BY PROJECT (via Field Expenses)
    // =========================================================
    public static function staffCostByProject(Carbon $period): Collection
    {
        return FieldExpense::with(['project', 'staff.department'])
            ->whereIn('status', ['approved', 'reimbursed'])
            ->whereYear('expense_date', $period->year)
            ->whereMonth('expense_date', $period->month)
            ->get()
            ->groupBy(fn ($e) => $e->project?->title ?? 'Unallocated')
            ->map(fn ($group, $project) => [
                'project' => $project,
                'total_expenses' => $group->sum('amount'),
                'staff_count' => $group->pluck('staff_id')->unique()->count(),
                'expense_breakdown' => $group->groupBy('category')
                    ->map(fn ($c) => number_format($c->sum('amount'), 2))
                    ->map(fn ($v, $k) => "$k: $v")
                    ->implode('; '),
            ])
            ->sortByDesc('total_expenses')
            ->values();
    }

    // =========================================================
    // 17. EMPLOYEE EARNINGS HISTORY
    // =========================================================
    public static function employeeEarningsHistory(int $staffId, int $year): Collection
    {
        return Payroll::with(['staff'])
            ->where('staff_id', $staffId)
            ->whereYear('payroll_period', $year)
            ->orderBy('payroll_period')
            ->get()
            ->map(fn (Payroll $p) => [
                'month' => $p->payroll_period->format('F Y'),
                'employment_type' => str($p->employment_type)->replace('_', ' ')->title(),
                'gross_salary' => $p->gross_salary,
                'basic_salary' => $p->basic_salary,
                'allowances' => $p->allowances,
                'overtime_pay' => $p->overtime_pay,
                'bonus' => $p->bonus,
                'paye' => $p->paye,
                'nssf' => $p->nssf,
                'nhif' => $p->nhif,
                'loan_deduction' => $p->loan_deduction,
                'other_deductions' => $p->other_deductions,
                'net_salary' => $p->net_salary,
                'payment_status' => $p->payment_status,
            ]);
    }

    // =========================================================
    // BONUS: TRIAL BALANCE (Chart of Accounts ledger balances)
    // =========================================================
    public static function trialBalance(): array
    {
        $accounts = \App\Models\Account::where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(function (\App\Models\Account $account) {
                $balance = $account->balance();

                return [
                    'code' => $account->code,
                    'name' => $account->name,
                    'type' => ucfirst($account->type),
                    // Trial balance convention: positive balance shown on
                    // the side matching the account's normal_balance.
                    'debit' => $account->normal_balance === 'debit' && $balance > 0 ? $balance : 0,
                    'credit' => $account->normal_balance === 'credit' && $balance > 0 ? $balance : 0,
                ];
            })
            ->filter(fn ($row) => $row['debit'] != 0 || $row['credit'] != 0)
            ->values();

        return [
            'rows' => $accounts,
            'total_debit' => $accounts->sum('debit'),
            'total_credit' => $accounts->sum('credit'),
        ];
    }

    // =========================================================
    // CSV EXPORT HELPER
    // =========================================================
    public static function toCsv(Collection|array $data, string $filename): \Symfony\Component\HttpFoundation\Response
    {
        $rows = collect($data)->map(function ($row) {
            return array_map(function ($value) {
                $clean = Utf8Sanitizer::clean($value);
                if (is_array($clean)) {
                    $parts = [];
                    foreach ($clean as $k => $v) {
                        $parts[] = is_string($k) ? "$k: $v" : strval($v);
                    }
                    return implode('; ', $parts);
                }
                return $clean;
            }, (array) $row);
        });

        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'w');

            if ($rows->isNotEmpty()) {
                fputcsv($output, array_keys((array) $rows->first()));

                foreach ($rows as $row) {
                    fputcsv($output, (array) $row);
                }
            }

            fclose($output);
        }, $filename.'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
