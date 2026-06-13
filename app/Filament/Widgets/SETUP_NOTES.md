# Dashboard Widgets & Monthly Financial Summary - Setup Notes

## 1. File placement
```
app/Filament/Widgets/StatsOverview.php
app/Filament/Widgets/RevenueVsExpensesChart.php
app/Filament/Widgets/IncidentSeverityChart.php
app/Filament/Pages/MonthlyFinancialSummary.php
resources/views/filament/pages/monthly-financial-summary.blade.php
resources/views/filament/pdf/monthly-financial-summary.blade.php
```
(rename the two view files from this delivery's `views/` folder accordingly)

## 2. Register widgets on the dashboard
In `app/Providers/Filament/AdminPanelProvider.php`:
```php
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\RevenueVsExpensesChart;
use App\Filament\Widgets\IncidentSeverityChart;

->widgets([
    StatsOverview::class,
    RevenueVsExpensesChart::class,
    IncidentSeverityChart::class,
])
```

Each widget already has a `canView()` method scoping it to the
relevant roles, so unauthorized users simply won't see it on the
dashboard - no extra config needed.

## 3. PDF export dependency
```bash
composer require barryvdh/laravel-dompdf
```
No further publish/config needed for basic usage - `Pdf::loadView()`
works out of the box.

## 4. MonthlyFinancialSummary page
- Appears in the "Finance & Expenses" nav group, restricted via
  `canAccess()` to `md` and `accountant` roles.
- The month/year picker (`period`) re-runs `getSummary()` on submit.
- "Export as PDF" button calls `exportPdf()`, which renders
  `filament.pdf.monthly-financial-summary` via DomPDF and streams a
  download named `financial-summary-YYYY-MM.pdf`.

## 5. Notes on the StatsOverview "Payroll Liability" figure
`gross_salary` already nets off employee-side PAYE/NSSF/NHIF before
`net_salary` is paid out - those amounts are still owed to TRA/NSSF/NHIF
by the company. The widget therefore reports:

```
Payroll Liability = SUM(gross_salary) + SUM(nssf_employer) + SUM(wcf)
```

i.e. total wage cost + the additional employer-only contributions.
If you'd rather show "Total Cash Out the Door" (= net salaries paid +
all statutory remittances), swap to:

```
SUM(net_salary) + SUM(paye) + SUM(nssf) + SUM(nssf_employer) + SUM(wcf) + SUM(nhif)
```//  this is mathematically equal to the gross-based formula above,
just expressed differently - pick whichever framing is clearer for MD.

## 6. Chart widget data ranges
- `RevenueVsExpensesChart` defaults to the last 6 months
  (`protected int $months = 6;`) - adjust as needed.
- `IncidentSeverityChart` covers all-time incident counts by severity.
  To scope to "this year" or "this month", add a `whereYear()` /
  `whereMonth()` filter on `Incident::query()` in `getData()`.
