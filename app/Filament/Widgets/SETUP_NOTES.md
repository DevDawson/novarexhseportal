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

## 7. HSE KPI Widgets (added in v2, #4)

```
app/Services/HseKpiService.php
app/Filament/Widgets/HseKpiOverview.php
app/Filament/Widgets/IncidentTrendChart.php
app/Filament/Widgets/OpenCorrectiveActionsWidget.php
app/Filament/Widgets/ProjectSafetyPerformanceWidget.php
```

Register alongside the existing widgets:
```php
use App\Filament\Widgets\HseKpiOverview;
use App\Filament\Widgets\IncidentTrendChart;
use App\Filament\Widgets\OpenCorrectiveActionsWidget;
use App\Filament\Widgets\ProjectSafetyPerformanceWidget;

->widgets([
    StatsOverview::class,
    HseKpiOverview::class,
    RevenueVsExpensesChart::class,
    IncidentSeverityChart::class,
    IncidentTrendChart::class,
    ExpiringDocumentsWidget::class,
    OpenCorrectiveActionsWidget::class,
    ProjectSafetyPerformanceWidget::class,
])
```

### What these cover from the client spec
- **Total Incidents, Near Misses, LTIs, Environmental Incidents**: `HseKpiOverview` (year-to-date).
- **LTIFR / TRIR**: calculated as `(count x 200,000) / Total Hours Worked`, using
  the sum of `Attendance.hours_worked` for the year. Adjust
  `HseKpiService::RATE_BASE_HOURS` to 1,000,000 if NOVAREX needs the ILO
  convention instead of OSHA.
- **Open / Overdue Corrective Actions**: `OpenCorrectiveActionsWidget` lists
  incidents with status `open`/`investigating`, flags any open longer than
  `HseKpiService::OVERDUE_THRESHOLD_DAYS` (30 days) in red. This is a
  proxy based on Incident status - a dedicated Corrective Actions module
  (separate table with due dates/assignees) would give more precise
  tracking if needed later.
- **Root Cause Trends**: `IncidentTrendChart` shows a stacked bar chart of
  incidents by type group over the last 6 months (root_cause is currently
  free text, so this groups by incident_type as the closest structured proxy).
- **Department Performance**: `ProjectSafetyPerformanceWidget` reports
  per-Project safety performance (incidents, open count, average risk
  score) - see the NOTE in that file regarding the Project vs Department
  distinction.

### Accuracy depends on Attendance data
LTIFR/TRIR will read as 0 until staff start logging Attendance records
(#1) with `hours_worked` populated - make sure HR/site supervisors are
using the Attendance tab regularly for these KPIs to be meaningful.

