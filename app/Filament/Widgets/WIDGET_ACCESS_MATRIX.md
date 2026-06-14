# Dashboard Widget Access Matrix

## Widget → Roles Allowed

| Widget | MD | HR Director | Business Director | Accountant | IT Technician | HSE Staff | Secretary |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| StatsOverview (Active Projects, Tenders, Expenses, Payroll) | ✓ | ✓ | ✓ | ✓ | ✗ | ✓ | ✗ |
| HseKpiOverview (LTIFR, TRIR, LTIs, Near Misses) | ✓ | ✓ | ✗ | ✗ | ✗ | ✓ | ✗ |
| RevenueVsExpensesChart (Finance trend) | ✓ | ✗ | ✓ | ✓ | ✗ | ✗ | ✗ |
| IncidentSeverityChart (Doughnut chart) | ✓ | ✗ | ✗ | ✗ | ✗ | ✓ | ✗ |
| IncidentTrendChart (Bar chart, 6 months) | ✓ | ✗ | ✗ | ✗ | ✗ | ✓ | ✗ |
| OpenCorrectiveActionsWidget (Table) | ✓ | ✗ | ✗ | ✗ | ✗ | ✓ | ✗ |
| ProjectSafetyPerformanceWidget (Table) | ✓ | ✗ | ✓ | ✗ | ✗ | ✓ | ✗ |
| ExpiringDocumentsWidget (Table) | ✓ | ✓ | ✓ | ✗ | ✗ | ✓ | ✓ |

## What each role sees on Dashboard

| Role | Widgets Visible |
|---|---|
| **MD** | All 8 widgets |
| **HR Director** | StatsOverview, HseKpiOverview, ExpiringDocumentsWidget |
| **Business Director** | StatsOverview, RevenueVsExpensesChart, ProjectSafetyPerformanceWidget, ExpiringDocumentsWidget |
| **Accountant** | StatsOverview, RevenueVsExpensesChart |
| **IT Technician** | ∅ (empty dashboard - only sees Users/Settings nav) |
| **HSE Staff** | StatsOverview, HseKpiOverview, IncidentSeverityChart, IncidentTrendChart, OpenCorrectiveActionsWidget, ProjectSafetyPerformanceWidget, ExpiringDocumentsWidget |
| **Secretary** | ExpiringDocumentsWidget only |

## Troubleshooting: Roles not working?

### 1. Verify user has a role assigned
```bash
php artisan tinker
>>> App\Models\User::find(1)->roles->pluck('name')
```

### 2. If roles are empty - re-run seeders
```bash
php artisan db:seed --class=RoleSeeder
```

### 3. Then re-assign the role to the user
```bash
php artisan tinker
>>> App\Models\User::where('email', 'hse@webmastercrew.online')->first()->assignRole('hse_staff')
```

### 4. Clear permission cache (Spatie caches permissions)
```bash
php artisan permission:cache-reset
php artisan optimize:clear
```

### 5. Verify canView() is being called
Temporarily add dd(auth()->user()->roles->pluck('name')) inside any canView()
to confirm the role is set when the dashboard loads.

### 6. Check that discoverWidgets() is REMOVED from AdminPanelProvider.php
If discoverWidgets() is still there alongside ->widgets([...]),
all widgets appear for every role regardless of canView().
