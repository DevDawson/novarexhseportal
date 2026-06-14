<?php

namespace App\Filament\Widgets;

use App\Models\FieldExpense;
use App\Models\Payroll;
use App\Models\PettyCashTransaction;
use App\Models\Project;
use App\Models\Tender;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';

    public static function canView(): bool
    {
        // Secretary na IT Technician hawaoni general ops dashboard.
        return auth()->user()?->hasAnyRole([
            'md', 'hr_director', 'business_director', 'accountant', 'hse_staff',
        ]) ?? false;
    }

    protected function getStats(): array
    {
        $now = now();

        // ------------------------------------------------------------
        // 1. Total Active Projects (HSE / ESIA module)
        // ------------------------------------------------------------
        $activeProjects = Project::whereIn('status', ['planning', 'ongoing'])->count();

        // ------------------------------------------------------------
        // 2. Won vs Pending Tenders (Business Development)
        // ------------------------------------------------------------
        $wonTenders = Tender::where('stage', 'won')->count();

        $pendingTenders = Tender::whereIn('stage', [
            'identified', 'prequalification', 'proposal_preparation', 'submitted', 'shortlisted',
        ])->count();

        // ------------------------------------------------------------
        // 3. Field Expenses approved this month (Finance)
        // ------------------------------------------------------------
        $approvedExpensesThisMonth = FieldExpense::where('status', 'approved')
            ->whereYear('expense_date', $now->year)
            ->whereMonth('expense_date', $now->month)
            ->sum('amount');

        // ------------------------------------------------------------
        // 4. Current month's total Payroll liability (HR & Payroll)
        // ------------------------------------------------------------
        // NOTE: gross_salary already includes the employee-side deductions
        // (PAYE, NSSF, NHIF) before arriving at net_salary. "Total liability"
        // here = gross_salary (cost of wages) + employer-only contributions
        // (NSSF employer share + WCF) which are additional cost to the company.
        $payrollThisMonth = Payroll::whereYear('payroll_period', $now->year)
            ->whereMonth('payroll_period', $now->month)
            ->selectRaw('
                COALESCE(SUM(gross_salary), 0) as total_gross,
                COALESCE(SUM(nssf_employer), 0) as total_nssf_employer,
                COALESCE(SUM(wcf), 0) as total_wcf
            ')
            ->first();

        $totalPayrollLiability = (float) $payrollThisMonth->total_gross
            + (float) $payrollThisMonth->total_nssf_employer
            + (float) $payrollThisMonth->total_wcf;

        return [
            Stat::make('Active Projects', $activeProjects)
                ->description('Planning + Ongoing')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('primary'),

            Stat::make('Tenders: Won / Pipeline', "{$wonTenders} / {$pendingTenders}")
                ->description('Won vs Pending in pipeline')
                ->descriptionIcon('heroicon-m-document-magnifying-glass')
                ->color($wonTenders >= $pendingTenders ? 'success' : 'warning'),

            Stat::make('Field Expenses Approved (This Month)', 'TZS '.number_format($approvedExpensesThisMonth, 2))
                ->description($now->format('F Y'))
                ->descriptionIcon('heroicon-m-receipt-percent')
                ->color('info'),

            Stat::make('Payroll Liability (This Month)', 'TZS '.number_format($totalPayrollLiability, 2))
                ->description('Gross salaries + employer NSSF + WCF')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('danger'),
        ];
    }
}
