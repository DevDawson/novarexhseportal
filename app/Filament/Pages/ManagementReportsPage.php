<?php

namespace App\Filament\Pages;

use App\Models\Department;
use App\Models\Staff;
use App\Services\ManagementReportService;
use App\Services\Utf8Sanitizer;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class ManagementReportsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationGroup = 'Finance & Expenses';

    protected static ?string $navigationLabel = 'Management Reports';

    protected static ?string $title = 'Management Reports';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.management-reports';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'accountant', 'hr_director']) ?? false;
    }

    /**
     * All download actions are exposed here. Each action shows a
     * modal form (period picker + optional filters), then streams
     * a PDF or CSV download.
     */
    protected function getHeaderActions(): array
    {
        return []; // Actions are rendered in the view via groups.
    }

    /**
     * DomPDF throws "Malformed UTF-8 characters" if any string value passed
     * to the view contains invalid UTF-8 byte sequences - see
     * App\Services\Utf8Sanitizer for details.
     */
    protected function sanitizeUtf8(mixed $data): mixed
    {
        return \App\Services\Utf8Sanitizer::clean($data);
    }

    // ============================================================
    // Shared form schema helpers
    // ============================================================

    protected function periodField(string $default = 'this_month'): DatePicker
    {
        return DatePicker::make('period')
            ->label('Report Month')
            ->displayFormat('F Y')
            ->native(false)
            ->default(now()->startOfMonth())
            ->required();
    }

    protected function departmentField(): Select
    {
        return Select::make('department_id')
            ->label('Department (optional - leave blank for all)')
            ->options(
                Department::pluck('name', 'id')
                    ->map(fn ($name) => Utf8Sanitizer::cleanString((string) $name))
            )
            ->native(false);
    }

    protected function staffField(): Select
    {
        return Select::make('staff_id')
            ->label('Staff Member')
            ->options(
                Staff::all()->mapWithKeys(fn ($s) => [
                    $s->id => Utf8Sanitizer::cleanString("{$s->full_name} ({$s->staff_no})"),
                ])
            )
            ->searchable()
            ->required();
    }

    protected function yearField(): Select
    {
        $years = [];
        for ($y = now()->year; $y >= now()->year - 4; $y--) {
            $years[$y] = (string) $y;
        }

        return Select::make('year')
            ->label('Year')
            ->options($years)
            ->default(now()->year)
            ->required()
            ->native(false);
    }

    // ============================================================
    // 1. Payroll Register
    // ============================================================
    public function payrollRegisterAction(): Action
    {
        return Action::make('payrollRegister')
            ->label('Payroll Register')
            ->icon('heroicon-o-table-cells')
            ->color('gray')
            ->form([$this->periodField(), $this->departmentField()])
            ->action(function (array $data): Response {
                $period = Carbon::parse($data['period']);

                $pdf = Pdf::loadView('filament.pdf.reports.payroll-register', [
                    'rows' => $this->sanitizeUtf8(ManagementReportService::payrollRegister($period, $data['department_id'] ?? null)),
                    'period' => $period,
                ])->setPaper('a4', 'landscape');

                $filename = 'payroll-register-'.$period->format('Y-m').'.pdf';

                return response()->streamDownload(
                    fn () => print($pdf->output()),
                    $filename,
                    ['Content-Type' => 'application/pdf'],
                );
            });
    }

    // ============================================================
    // 3. Department Payroll Report
    // ============================================================
    public function departmentPayrollAction(): Action
    {
        return Action::make('departmentPayroll')
            ->label('Department Payroll')
            ->icon('heroicon-o-building-office')
            ->color('gray')
            ->form([$this->periodField()])
            ->action(function (array $data): Response {
                $period = Carbon::parse($data['period']);

                $pdf = Pdf::loadView('filament.pdf.reports.department-payroll', [
                    'rows' => $this->sanitizeUtf8(ManagementReportService::departmentPayrollReport($period)),
                    'period' => $period,
                ])->setPaper('a4', 'portrait');

                $filename = 'department-payroll-'.$period->format('Y-m').'.pdf';

                return response()->streamDownload(
                    fn () => print($pdf->output()),
                    $filename,
                    ['Content-Type' => 'application/pdf'],
                );
            });
    }

    // ============================================================
    // 4. Project Payroll Cost Report
    // ============================================================
    public function projectPayrollCostAction(): Action
    {
        return Action::make('projectPayrollCost')
            ->label('Project Payroll Cost')
            ->icon('heroicon-o-briefcase')
            ->color('gray')
            ->form([$this->periodField()])
            ->action(function (array $data): Response {
                $period = Carbon::parse($data['period']);

                return ManagementReportService::toCsv(
                    ManagementReportService::projectPayrollCostReport($period),
                    'project-payroll-cost-'.$period->format('Y-m')
                );
            });
    }

    // ============================================================
    // 5. Overtime Report
    // ============================================================
    public function overtimeReportAction(): Action
    {
        return Action::make('overtimeReport')
            ->label('Overtime Report')
            ->icon('heroicon-o-clock')
            ->color('gray')
            ->form([$this->periodField(), $this->departmentField()])
            ->action(function (array $data): Response {
                $period = Carbon::parse($data['period']);

                return ManagementReportService::toCsv(
                    ManagementReportService::overtimeReport($period, $data['department_id'] ?? null),
                    'overtime-report-'.$period->format('Y-m')
                );
            });
    }

    // ============================================================
    // 6. Attendance Summary
    // ============================================================
    public function attendanceSummaryAction(): Action
    {
        return Action::make('attendanceSummary')
            ->label('Attendance Summary')
            ->icon('heroicon-o-calendar-days')
            ->color('gray')
            ->form([$this->periodField(), $this->departmentField()])
            ->action(function (array $data): Response {
                $period = Carbon::parse($data['period']);

                return ManagementReportService::toCsv(
                    ManagementReportService::attendanceSummary($period, $data['department_id'] ?? null),
                    'attendance-summary-'.$period->format('Y-m')
                );
            });
    }

    // ============================================================
    // 7. PAYE Report (PDF + CSV)
    // ============================================================
    public function payeReportAction(): Action
    {
        return Action::make('payeReport')
            ->label('PAYE Report (TRA)')
            ->icon('heroicon-o-document-text')
            ->color('warning')
            ->form([$this->periodField()])
            ->action(function (array $data): Response {
                $period = Carbon::parse($data['period']);

                $pdf = Pdf::loadView('filament.pdf.reports.statutory-report', [
                    'title' => 'PAYE Monthly Return',
                    'authority' => 'Tanzania Revenue Authority (TRA)',
                    'rows' => $this->sanitizeUtf8(ManagementReportService::payeReport($period)),
                    'period' => $period,
                    'columns' => ['staff_no' => 'Staff No', 'name' => 'Name', 'tin_no' => 'TIN', 'gross_salary' => 'Gross', 'nssf_employee' => 'NSSF (Employee)', 'taxable_income' => 'Taxable Income', 'paye' => 'PAYE'],
                    'total_key' => 'paye',
                ])->setPaper('a4', 'landscape');

                $filename = 'paye-report-'.$period->format('Y-m').'.pdf';

                return response()->streamDownload(
                    fn () => print($pdf->output()),
                    $filename,
                    ['Content-Type' => 'application/pdf'],
                );
            });
    }

    // ============================================================
    // 8. NSSF Report
    // ============================================================
    public function nssfReportAction(): Action
    {
        return Action::make('nssfReport')
            ->label('NSSF Report')
            ->icon('heroicon-o-document-text')
            ->color('warning')
            ->form([$this->periodField()])
            ->action(function (array $data): Response {
                $period = Carbon::parse($data['period']);

                $pdf = Pdf::loadView('filament.pdf.reports.statutory-report', [
                    'title' => 'NSSF Monthly Contribution Report',
                    'authority' => 'National Social Security Fund (NSSF)',
                    'rows' => $this->sanitizeUtf8(ManagementReportService::nssfReport($period)),
                    'period' => $period,
                    'columns' => ['staff_no' => 'Staff No', 'name' => 'Name', 'nssf_no' => 'NSSF No', 'gross_salary' => 'Gross', 'nssf_employee' => 'Employee (10%)', 'nssf_employer' => 'Employer (10%)', 'total_nssf' => 'Total'],
                    'total_key' => 'total_nssf',
                ])->setPaper('a4', 'landscape');

                $filename = 'nssf-report-'.$period->format('Y-m').'.pdf';

                return response()->streamDownload(
                    fn () => print($pdf->output()),
                    $filename,
                    ['Content-Type' => 'application/pdf'],
                );
            });
    }

    // ============================================================
    // 9. WCF Report
    // ============================================================
    public function wcfReportAction(): Action
    {
        return Action::make('wcfReport')
            ->label('WCF Report')
            ->icon('heroicon-o-document-text')
            ->color('warning')
            ->form([$this->periodField()])
            ->action(function (array $data): Response {
                $period = Carbon::parse($data['period']);

                $pdf = Pdf::loadView('filament.pdf.reports.statutory-report', [
                    'title' => 'WCF Contribution Report',
                    'authority' => 'Workers Compensation Fund (WCF)',
                    'rows' => $this->sanitizeUtf8(ManagementReportService::wcfReport($period)),
                    'period' => $period,
                    'columns' => ['staff_no' => 'Staff No', 'name' => 'Name', 'gross_salary' => 'Gross', 'wcf' => 'WCF (0.5%)'],
                    'total_key' => 'wcf',
                ])->setPaper('a4', 'portrait');

                $filename = 'wcf-report-'.$period->format('Y-m').'.pdf';

                return response()->streamDownload(
                    fn () => print($pdf->output()),
                    $filename,
                    ['Content-Type' => 'application/pdf'],
                );
            });
    }

    // ============================================================
    // 10. SDL Report
    // ============================================================
    public function sdlReportAction(): Action
    {
        return Action::make('sdlReport')
            ->label('SDL Report (VETA)')
            ->icon('heroicon-o-document-text')
            ->color('warning')
            ->form([$this->periodField()])
            ->action(function (array $data): Response {
                $period = Carbon::parse($data['period']);

                $pdf = Pdf::loadView('filament.pdf.reports.statutory-report', [
                    'title' => 'Skills Development Levy (SDL) Report',
                    'authority' => 'Vocational Education Training Authority (VETA)',
                    'rows' => $this->sanitizeUtf8(ManagementReportService::sdlReport($period)),
                    'period' => $period,
                    'columns' => ['staff_no' => 'Staff No', 'name' => 'Name', 'department' => 'Dept', 'gross_salary' => 'Gross', 'sdl' => 'SDL (4.5%)'],
                    'total_key' => 'sdl',
                ])->setPaper('a4', 'portrait');

                $filename = 'sdl-report-'.$period->format('Y-m').'.pdf';

                return response()->streamDownload(
                    fn () => print($pdf->output()),
                    $filename,
                    ['Content-Type' => 'application/pdf'],
                );
            });
    }

    // ============================================================
    // 11. Salary Cost Report
    // ============================================================
    public function salaryCostReportAction(): Action
    {
        return Action::make('salaryCostReport')
            ->label('Salary Cost Report')
            ->icon('heroicon-o-banknotes')
            ->color('gray')
            ->form([$this->periodField()])
            ->action(function (array $data): Response {
                $period = Carbon::parse($data['period']);
                $summary = $this->sanitizeUtf8(ManagementReportService::salaryCostReport($period));

                $pdf = Pdf::loadView('filament.pdf.reports.salary-cost', [
                    'summary' => $summary,
                    'period' => $period,
                ])->setPaper('a4', 'portrait');

                $filename = 'salary-cost-'.$period->format('Y-m').'.pdf';

                return response()->streamDownload(
                    fn () => print($pdf->output()),
                    $filename,
                    ['Content-Type' => 'application/pdf'],
                );
            });
    }

    // ============================================================
    // 12. Bank Transfer Schedule
    // ============================================================
    public function bankTransferScheduleAction(): Action
    {
        return Action::make('bankTransferSchedule')
            ->label('Bank Transfer Schedule')
            ->icon('heroicon-o-building-library')
            ->color('primary')
            ->form([$this->periodField()])
            ->action(function (array $data): Response {
                $period = Carbon::parse($data['period']);

                $pdf = Pdf::loadView('filament.pdf.reports.bank-transfer-schedule', [
                    'rows' => $this->sanitizeUtf8(ManagementReportService::bankTransferSchedule($period)),
                    'period' => $period,
                ])->setPaper('a4', 'landscape');

                $filename = 'bank-transfer-schedule-'.$period->format('Y-m').'.pdf';

                return response()->streamDownload(
                    fn () => print($pdf->output()),
                    $filename,
                    ['Content-Type' => 'application/pdf'],
                );
            });
    }

    // ============================================================
    // 14. Annual Payroll Summary
    // ============================================================
    public function annualPayrollSummaryAction(): Action
    {
        return Action::make('annualPayrollSummary')
            ->label('Annual Payroll Summary')
            ->icon('heroicon-o-chart-bar')
            ->color('primary')
            ->form([$this->yearField()])
            ->action(function (array $data): Response {
                $year = (int) $data['year'];

                $pdf = Pdf::loadView('filament.pdf.reports.annual-payroll-summary', [
                    'rows' => $this->sanitizeUtf8(ManagementReportService::annualPayrollSummary($year)),
                    'year' => $year,
                ])->setPaper('a4', 'landscape');

                $filename = 'annual-payroll-summary-'.$year.'.pdf';

                return response()->streamDownload(
                    fn () => print($pdf->output()),
                    $filename,
                    ['Content-Type' => 'application/pdf'],
                );
            });
    }

    // ============================================================
    // 15. Staff Cost By Department
    // ============================================================
    public function staffCostByDepartmentAction(): Action
    {
        return Action::make('staffCostByDepartment')
            ->label('Staff Cost by Department')
            ->icon('heroicon-o-building-office-2')
            ->color('gray')
            ->form([$this->periodField()])
            ->action(function (array $data): Response {
                $period = Carbon::parse($data['period']);

                return ManagementReportService::toCsv(
                    ManagementReportService::staffCostByDepartment($period),
                    'staff-cost-by-department-'.$period->format('Y-m')
                );
            });
    }

    // ============================================================
    // 16. Staff Cost By Project
    // ============================================================
    public function staffCostByProjectAction(): Action
    {
        return Action::make('staffCostByProject')
            ->label('Staff Cost by Project')
            ->icon('heroicon-o-briefcase')
            ->color('gray')
            ->form([$this->periodField()])
            ->action(function (array $data): Response {
                $period = Carbon::parse($data['period']);

                return ManagementReportService::toCsv(
                    ManagementReportService::staffCostByProject($period),
                    'staff-cost-by-project-'.$period->format('Y-m')
                );
            });
    }

    // ============================================================
    // 17. Employee Earnings History
    // ============================================================
    public function employeeEarningsHistoryAction(): Action
    {
        return Action::make('employeeEarningsHistory')
            ->label('Employee Earnings History')
            ->icon('heroicon-o-user')
            ->color('gray')
            ->form([$this->staffField(), $this->yearField()])
            ->action(function (array $data): Response {
                return ManagementReportService::toCsv(
                    ManagementReportService::employeeEarningsHistory((int) $data['staff_id'], (int) $data['year']),
                    'earnings-history-staff-'.$data['staff_id'].'-'.$data['year']
                );
            });
    }

    // ============================================================
    // BONUS: Trial Balance (Accounting Journal - #8)
    // ============================================================
    public function trialBalanceAction(): Action
    {
        return Action::make('trialBalance')
            ->label('Trial Balance')
            ->icon('heroicon-o-scale')
            ->color('primary')
            ->requiresConfirmation()
            ->modalDescription('Generate a Trial Balance as of today, based on all posted journal entries (including automatic Payroll postings).')
            ->action(function (): Response {
                $data = ManagementReportService::trialBalance();

                $pdf = Pdf::loadView('filament.pdf.reports.trial-balance', [
                    'rows' => $this->sanitizeUtf8($data['rows']),
                    'totalDebit' => $data['total_debit'],
                    'totalCredit' => $data['total_credit'],
                    'asOf' => now()->format('d M Y'),
                ])->setPaper('a4', 'portrait');

                $filename = 'trial-balance-'.now()->format('Y-m-d').'.pdf';

                return response()->streamDownload(
                    fn () => print($pdf->output()),
                    $filename,
                    ['Content-Type' => 'application/pdf'],
                );
            });
    }
}
