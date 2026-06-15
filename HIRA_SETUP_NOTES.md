# HIRA Module Setup Notes
## Hazard Identification & Risk Assessment (Module 1)

---

### What Was Built

| File | Purpose |
|------|---------|
| `database/migrations/2024_01_13_000001_create_hazard_register_table.php` | Creates `hazard_register` table |
| `app/Models/HazardRegister.php` | Eloquent model with auto-scoring `booted()` hook |
| `app/Filament/Resources/HazardRegisterResource.php` | Full Filament resource with form, table, filters |
| `app/Filament/Resources/HazardRegisterResource/Pages/ListHazardRegisters.php` | List page |
| `app/Filament/Resources/HazardRegisterResource/Pages/CreateHazardRegister.php` | Create page |
| `app/Filament/Resources/HazardRegisterResource/Pages/EditHazardRegister.php` | Edit page |
| `app/Filament/Widgets/HighRiskHazardsWidget.php` | Dashboard widget - open High/Critical hazards |
| `database/seeders/RoleSeeder.php` | *(modified)* Added `manage hazards` permission |
| `app/Providers/Filament/AdminPanelProvider.php` | *(modified)* Registered `HighRiskHazardsWidget` |

---

### Install Steps

```bash
# 1. Run the new migration
php artisan migrate

# 2. Re-seed roles & permissions
php artisan db:seed --class=RoleSeeder

# 3. Clear all caches
php artisan optimize:clear
```

> **Note:** `db:seed --class=RoleSeeder` uses `syncPermissions()`, so it is safe to re-run — it will not duplicate permissions.

---

### How It Works

#### Hazard Register (HIRA)
A formal task-based hazard register distinct from the existing `risks` table. The `risks` table is a high-level project risk register; HIRA captures individual activity-level hazards with before/after risk scoring.

**Form sections:**
1. **Activity & Hazard** — Project (optional, for company-wide office hazards leave blank), activity/task name, location, hazard description, category (8 types: physical, chemical, biological, ergonomic, psychosocial, environmental, mechanical, electrical), who might be harmed.
2. **Initial Risk Assessment** — Likelihood × Severity dropdowns (0–5 using `RiskScoringService::ratingOptions()`). Live preview shows the computed score and 4-tier level (Low/Medium/High/Critical) exactly matching the existing risk matrix used in Incidents.
3. **Controls** — Existing controls textarea, then a CheckboxList for the Hierarchy of Controls in the correct order: Elimination → Substitution → Engineering → Administrative → PPE. Free-text description of additional controls.
4. **Residual Risk Assessment** — Same pattern as Initial; shows risk AFTER all controls applied. Target should be Low/Medium.
5. **Action Tracking** — Responsible person (user select), review date, and status (`open` / `controls_in_progress` / `controlled` / `closed`).

**Auto-scoring:** `HazardRegister::booted()` fires a `saving` listener that calls `RiskScoringService::score()` for both initial and residual scores — identical pattern to `Incident::booted()`. The form's Hidden fields (`initial_risk_score`, `residual_risk_score`) are kept in sync live for immediate feedback.

**Table columns:** Activity, Project, Category badge (color-coded by hazard type), Initial Risk badge (color via `RiskScoringService::colorForScore()`), Residual Risk badge (same), Responsible person, Review Date (red if overdue and not closed), Status badge.

**Filters:** Category, Status, Project, Overdue Review toggle, High/Critical Residual Risk toggle.

---

#### HighRiskHazardsWidget (Dashboard)
Appears on the dashboard for `md` and `hse_staff` roles. Shows all hazards where:
- `residual_risk_score >= 10` (High or Critical per `RiskScoringService`)
- `status != 'closed'`

Ordered by residual risk score descending. Includes a direct Edit link for each row. This gives management immediate visibility of uncontrolled high-risk hazards without navigating into the HIRA module.

---

### Permissions

| Permission | Roles Granted |
|-----------|--------------|
| `manage hazards` | `md`, `hse_staff`, `business_director` |

---

### Risk Matrix Reference (shared with Incidents, HIRA, future EMS)

| Score | Level | Color | Required Action |
|-------|-------|-------|----------------|
| 0–4 | Low | Green | Monitor periodically |
| 5–9 | Medium | Yellow | Additional controls where practicable |
| 10–15 | High | Orange | Immediate corrective actions required |
| 16–25 | Critical | Red | Stop work. Senior management authorization |

All scoring is handled by `App\Services\RiskScoringService` — one definition, consistent everywhere.

---

### Navigation
HIRA appears under **HSE & Technical Operations** navigation group in the sidebar, labelled "HIRA", with sort order 4 (after Incidents, Risks, Permit to Work).
