<?php

namespace App\Filament\Pages;

use App\Models\FieldExpense;
use App\Models\Invoice;
use App\Models\Payroll;
use App\Models\PettyCashTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MonthlyFinancialSummary extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationGroup = 'Finance & Expenses';

    protected static ?string $navigationLabel = 'Monthly Financial Summary';

    protected static ?string $title = 'Monthly Financial Summary';

    protected static string $view = 'filament.pages.monthly-financial-summary';

    /**
     * Filter state (month/year picker).
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'accountant']) ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'period' => now()->startOfMonth()->toDateString(),
        ]);
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('period')
                    ->label('Report Month')
                    ->displayFormat('F Y')
                    ->native(false)
                    ->required(),
            ])
            ->statePath('data');
    }

    /**
     * Build the full summary dataset for the selected month.
     * Shared by the on-screen view and the PDF export.
     */
    public function getSummary(): array
    {
        $period = \Illuminate\Support\Carbon::parse($this->data['period'] ?? now())->startOfMonth();

        // --------------------------------------------------------
        // Payroll: salaries + statutory obligations
        // --------------------------------------------------------
        $payroll = Payroll::whereYear('payroll_period', $period->year)
            ->whereMonth('payroll_period', $period->month)
            ->selectRaw('
                COALESCE(SUM(gross_salary), 0) as total_gross,
                COALESCE(SUM(net_salary), 0) as total_net,
                COALESCE(SUM(paye), 0) as total_paye,
                COALESCE(SUM(nssf), 0) as total_nssf_employee,
                COALESCE(SUM(nssf_employer), 0) as total_nssf_employer,
                COALESCE(SUM(wcf), 0) as total_wcf,
                COALESCE(SUM(nhif), 0) as total_nhif,
                COUNT(*) as staff_count
            ')
            ->first();

        // --------------------------------------------------------
        // Field expenses (approved/reimbursed) for the month
        // --------------------------------------------------------
        $fieldExpensesTotal = FieldExpense::whereIn('status', ['approved', 'reimbursed'])
            ->whereYear('expense_date', $period->year)
            ->whereMonth('expense_date', $period->month)
            ->sum('amount');

        $fieldExpensesByCategory = FieldExpense::whereIn('status', ['approved', 'reimbursed'])
            ->whereYear('expense_date', $period->year)
            ->whereMonth('expense_date', $period->month)
            ->selectRaw('category, COALESCE(SUM(amount), 0) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        // --------------------------------------------------------
        // Petty cash outflows for the month
        // --------------------------------------------------------
        $pettyCashTotal = PettyCashTransaction::whereIn('transaction_type', ['expense', 'utility_payment'])
            ->whereYear('transaction_date', $period->year)
            ->whereMonth('transaction_date', $period->month)
            ->sum('amount');

        // --------------------------------------------------------
        // Revenue (invoices) for the month
        // --------------------------------------------------------
        $revenueTotal = Invoice::whereNotIn('status', ['cancelled', 'draft'])
            ->whereYear('invoice_date', $period->year)
            ->whereMonth('invoice_date', $period->month)
            ->sum('total_amount');

        $statutoryTotal = (float) $payroll->total_paye
            + (float) $payroll->total_nssf_employee
            + (float) $payroll->total_nssf_employer
            + (float) $payroll->total_wcf
            + (float) $payroll->total_nhif;

        $totalOutflows = (float) $payroll->total_gross
            + (float) $payroll->total_nssf_employer
            + (float) $payroll->total_wcf
            + (float) $fieldExpensesTotal
            + (float) $pettyCashTotal;

        return [
            'period' => $period,
            'revenue_total' => (float) $revenueTotal,
            'payroll' => $payroll,
            'statutory_total' => $statutoryTotal,
            'field_expenses_total' => (float) $fieldExpensesTotal,
            'field_expenses_by_category' => $fieldExpensesByCategory,
            'petty_cash_total' => (float) $pettyCashTotal,
            'total_outflows' => $totalOutflows,
            'net_position' => (float) $revenueTotal - $totalOutflows,
        ];
    }

    /**
     * Re-render the page when the period filter changes.
     */
    public function updatedData(): void
    {
        // Filament automatically re-renders on Livewire property update;
        // no extra action needed - getSummary() is called fresh in the view.
    }

    /**
     * Export the current summary as a downloadable PDF.
     *
     * Requires: composer require barryvdh/laravel-dompdf
     */
    public function exportPdf(): StreamedResponse
    {
        $summary = $this->getSummary();

        $pdf = Pdf::loadView('filament.pdf.monthly-financial-summary', [
            'summary' => $summary,
        ])->setPaper('a4', 'portrait');

        $filename = 'financial-summary-'.$summary['period']->format('Y-m').'.pdf';

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename,
        );
    }
}
