<x-filament-panels::page>

    <form wire:submit.prevent>
        {{ $this->form }}
    </form>

    @php($summary = $this->getSummary())

    <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">

        <x-filament::section>
            <x-slot name="heading">Revenue</x-slot>
            <p class="text-2xl font-bold text-success-600">
                TZS {{ number_format($summary['revenue_total'], 2) }}
            </p>
            <p class="text-sm text-gray-500">Invoiced for {{ $summary['period']->format('F Y') }}</p>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Total Outflows</x-slot>
            <p class="text-2xl font-bold text-danger-600">
                TZS {{ number_format($summary['total_outflows'], 2) }}
            </p>
            <p class="text-sm text-gray-500">Payroll cost + Field Expenses + Petty Cash</p>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Net Position</x-slot>
            <p class="text-2xl font-bold {{ $summary['net_position'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                TZS {{ number_format($summary['net_position'], 2) }}
            </p>
            <p class="text-sm text-gray-500">Revenue - Total Outflows</p>
        </x-filament::section>

    </div>

    <x-filament::section class="mt-6">
        <x-slot name="heading">Payroll & Statutory Obligations</x-slot>

        <table class="w-full text-sm">
            <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                <tr>
                    <td class="py-2">Staff Paid</td>
                    <td class="py-2 text-right">{{ $summary['payroll']->staff_count }}</td>
                </tr>
                <tr>
                    <td class="py-2">Total Gross Salaries</td>
                    <td class="py-2 text-right">TZS {{ number_format($summary['payroll']->total_gross, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2">Total Net Salaries (Take-Home)</td>
                    <td class="py-2 text-right">TZS {{ number_format($summary['payroll']->total_net, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2">PAYE (TRA)</td>
                    <td class="py-2 text-right">TZS {{ number_format($summary['payroll']->total_paye, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2">NSSF - Employee</td>
                    <td class="py-2 text-right">TZS {{ number_format($summary['payroll']->total_nssf_employee, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2">NSSF - Employer</td>
                    <td class="py-2 text-right">TZS {{ number_format($summary['payroll']->total_nssf_employer, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2">WCF</td>
                    <td class="py-2 text-right">TZS {{ number_format($summary['payroll']->total_wcf, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-2">NHIF</td>
                    <td class="py-2 text-right">TZS {{ number_format($summary['payroll']->total_nhif, 2) }}</td>
                </tr>
                <tr class="font-semibold">
                    <td class="py-2">Total Statutory Obligations</td>
                    <td class="py-2 text-right">TZS {{ number_format($summary['statutory_total'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </x-filament::section>

    <x-filament::section class="mt-6">
        <x-slot name="heading">Field Expenses by Category</x-slot>

        <table class="w-full text-sm">
            <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                @forelse ($summary['field_expenses_by_category'] as $category => $total)
                    <tr>
                        <td class="py-2 capitalize">{{ str_replace('_', ' ', $category) }}</td>
                        <td class="py-2 text-right">TZS {{ number_format($total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="py-2 text-gray-500" colspan="2">No approved field expenses for this period.</td>
                    </tr>
                @endforelse
                <tr class="font-semibold">
                    <td class="py-2">Total Field Expenses</td>
                    <td class="py-2 text-right">TZS {{ number_format($summary['field_expenses_total'], 2) }}</td>
                </tr>
                <tr class="font-semibold">
                    <td class="py-2">Petty Cash Outflows</td>
                    <td class="py-2 text-right">TZS {{ number_format($summary['petty_cash_total'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </x-filament::section>

    <div class="mt-6">
        <x-filament::button wire:click="exportPdf" icon="heroicon-o-arrow-down-tray">
            Export as PDF
        </x-filament::button>
    </div>

</x-filament-panels::page>
