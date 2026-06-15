# EMS Module Setup Notes
## Environmental Management System (Module 3)

---

### What Was Built

| File | Purpose |
|------|---------|
| `database/migrations/2024_01_13_000005_create_environmental_aspects_table.php` | Aspects & Impacts register |
| `database/migrations/2024_01_13_000006_create_legal_register_table.php` | Legal & Compliance register |
| `database/migrations/2024_01_13_000007_create_environmental_monitoring_records_table.php` | Environmental KPI records |
| `app/Models/EnvironmentalAspect.php` | Model with auto-scoring significance |
| `app/Models/LegalRegisterItem.php` | Model (table: `legal_register`) |
| `app/Models/EnvironmentalMonitoringRecord.php` | Model with metric/unit constants |
| `app/Services/EmsKpiService.php` | KPI calculations (totals, trend, recycling rate, counts) |
| `app/Filament/Resources/EnvironmentalAspectResource.php` | Aspects & Impacts resource |
| `app/Filament/Resources/LegalRegisterResource.php` | Legal Register resource |
| `app/Filament/Resources/EnvironmentalMonitoringRecordResource.php` | Monitoring Records resource |
| `app/Filament/Resources/*/Pages/*.php` | 9 Page classes (3 per resource) |
| `app/Filament/Widgets/EmsKpiOverview.php` | Stats widget — 8 EMS KPIs |
| `app/Filament/Widgets/EnvironmentalMetricsTrendChart.php` | Line chart — waste vs energy/fuel trend |
| `app/Filament/Widgets/ExpiringLicensesWidget.php` | Table widget — licences expiring ≤60 days |
| `database/seeders/RoleSeeder.php` | *(modified)* Added 3 EMS permissions |
| `app/Providers/Filament/AdminPanelProvider.php` | *(modified)* Registered 3 EMS widgets |

---

### Install Steps

```bash
# 1. Run the 3 new migrations
php artisan migrate

# 2. Re-seed roles & permissions
php artisan db:seed --class=RoleSeeder

# 3. Clear all caches
php artisan optimize:clear
```

---

### How It Works

#### Navigation Group: "Environmental Management"

A new sidebar group with 3 items, sorted independently of "HSE & Technical Operations":

---

#### 1. Environmental Aspects & Impacts (`environmental_aspects`)

Records activity-level environmental aspects per ISO 14001 requirements. Distinct from the `risks` table (project risk register).

**Form sections:**
- **Activity & Aspect** — Project (optional), activity/process, impact category (Air/Water/Soil/Waste/Biodiversity/Noise/Energy/Other), environmental aspect string, environmental impact description.
- **Significance Assessment** — Likelihood × Severity using the same `RiskScoringService` matrix as HIRA and Incidents. Live preview shows score and significance label ("Significant" if ≥10, "Not Significant" if <10). Status field is auto-set by the `booted()` saving hook but can be overridden to `Controlled` once controls are fully in place.
- **Controls & References** — Existing controls, legal requirement reference (free text linking to the Legal Register), responsible person, review date.

**Auto-status logic (in `EnvironmentalAspect::booted()`):**
```
saving → compute significance_score = L × S
        if status != 'controlled':
            status = score >= 10 ? 'significant' : 'not_significant'
```

---

#### 2. Legal & Compliance Register (`legal_register`)

Tracks all environmental laws, regulations, permits, licences, and client requirements applicable to NOVAREX operations.

**Key fields:** Requirement title, type (Law/Regulation/Permit/Client/Other), issuing authority (NEMC, OSHA Tanzania, EWURA, etc.), applicable scope, compliance status (Compliant/Non-Compliant/Partially/Not Assessed), evidence file upload, expiry date, last/next review dates.

**Table:** Expiry date column turns amber (≤60 days) or red (expired/≤14 days). Filters for "Expiring within 60 days" and "Already Expired".

---

#### 3. Environmental Monitoring Records (`environmental_monitoring_records`)

Monthly/periodic data entry for 8 environmental KPI metrics:

| Metric | Default Unit |
|--------|-------------|
| Water Consumption | m³ |
| Energy Consumption | kWh |
| Fuel Consumption | litres |
| Waste Generated (Hazardous) | kg |
| Waste Generated (Non-Hazardous) | kg |
| Waste Recycled | kg |
| GHG Emissions | tCO₂e |
| Spills / Environmental Incidents | count |

Selecting a metric type in the form auto-populates the `unit` field. A unique index on `(project_id, record_date, metric_type)` prevents duplicate entries.

---

#### EMS Dashboard Widgets

**`EmsKpiOverview`** — Stats widget with 8 tiles for the current month: Water, Energy, Fuel, Waste Generated, Waste Recycled, GHG Emissions, Significant Aspects count, Licences Expiring count. Visible to `md`, `hse_staff`, `business_director`.

**`EnvironmentalMetricsTrendChart`** — Dual-axis line chart (last 12 months): left axis = waste (kg), right axis = energy/fuel. 5 datasets: Hazardous Waste, Non-Hazardous Waste, Recycled Waste, Energy, Fuel. Visible to `md`, `hse_staff`, `business_director`.

**`ExpiringLicensesWidget`** — Table widget showing legal register entries expiring within 60 days, ordered by expiry date. Entries expiring ≤14 days shown in red, others in amber. Direct Edit link on each row. Visible to `md`, `hse_staff`.

---

### Permissions

| Permission | Roles Granted |
|-----------|--------------|
| `manage environmental_aspects` | `md`, `hse_staff` |
| `manage legal_register` | `md`, `hse_staff` |
| `manage environmental_monitoring` | `md`, `hse_staff` |

> **Note:** `business_director` can VIEW the EMS dashboard widgets but does NOT have manage permissions for EMS records (he sees the KPI data, not the edit forms). If you later want to give `business_director` read-only access to EMS records, add a `view ems` permission and gate `canViewAny()` on it.

---

### EmsKpiService API

```php
EmsKpiService::totalsByMetric(Carbon $from, Carbon $to, ?int $projectId = null): array
// Returns sum per metric_type for period, with 0 for missing types.

EmsKpiService::trend(string $metricType, int $months = 12): array
// Returns ['labels' => [...], 'data' => [...]] for chart.

EmsKpiService::wasteRecyclingRate(Carbon $from, Carbon $to): float
// recycled / (hazardous + non_hazardous + recycled) * 100

EmsKpiService::significantAspectsCount(): int
// Count of environmental_aspects where status = 'significant'

EmsKpiService::expiringLicensesCount(int $withinDays = 60): int
// Count of legal_register with expiry_date in next N days
```
