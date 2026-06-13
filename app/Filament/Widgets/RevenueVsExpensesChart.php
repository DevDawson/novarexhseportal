<?php

namespace App\Filament\Widgets;

use App\Models\FieldExpense;
use App\Models\Invoice;
use App\Models\PettyCashTransaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueVsExpensesChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Revenue vs Operational Expenses';

    protected static ?string $maxHeight = '300px';

    protected static ?string $pollingInterval = '120s';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'accountant', 'business_director']) ?? false;
    }

    /**
     * Number of months (including current) to show.
     */
    protected int $months = 6;

    protected function getData(): array
    {
        $months = collect();

        for ($i = $this->months - 1; $i >= 0; $i--) {
            $months->push(now()->subMonths($i)->startOfMonth());
        }

        $revenue = $months->map(function (Carbon $month) {
            return (float) Invoice::whereYear('invoice_date', $month->year)
                ->whereMonth('invoice_date', $month->month)
                ->whereNotIn('status', ['cancelled', 'draft'])
                ->sum('total_amount');
        });

        $fieldExpenses = $months->map(function (Carbon $month) {
            return (float) FieldExpense::where('status', 'approved')
                ->whereYear('expense_date', $month->year)
                ->whereMonth('expense_date', $month->month)
                ->sum('amount');
        });

        $pettyCash = $months->map(function (Carbon $month) {
            return (float) PettyCashTransaction::whereIn('transaction_type', ['expense', 'utility_payment'])
                ->whereYear('transaction_date', $month->year)
                ->whereMonth('transaction_date', $month->month)
                ->sum('amount');
        });

        // Combine field expenses + petty cash into a single "Operational Costs" series
        $operationalCosts = $fieldExpenses->map(fn ($value, $i) => $value + $pettyCash[$i]);

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (Invoices)',
                    'data' => $revenue->values(),
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Operational Costs (Field Expenses + Petty Cash)',
                    'data' => $operationalCosts->values(),
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $months->map(fn (Carbon $month) => $month->format('M Y'))->values(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
