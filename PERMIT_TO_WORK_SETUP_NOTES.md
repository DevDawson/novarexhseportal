# Permit to Work (PTW) Module - Setup Notes

## New files

```
migrations/2024_01_12_000001_create_permit_to_works_table.php
migrations/2024_01_12_000002_create_permit_checklist_items_table.php
migrations/2024_01_12_000003_create_permit_extensions_table.php

models/PermitToWork.php
models/PermitChecklistItem.php
models/PermitExtension.php

services/PermitToWorkService.php

filament-resources/PermitToWorkResource.php
filament-resources/PermitToWorkResource/Pages/ListPermitToWorks.php
filament-resources/PermitToWorkResource/Pages/CreatePermitToWork.php
filament-resources/PermitToWorkResource/Pages/EditPermitToWork.php
filament-resources/PermitToWorkResource/Pages/ViewPermitToWork.php

widgets/ExpiringPermitsWidget.php
```

## Modified files

- `AdminPanelProvider.php` - registered `ExpiringPermitsWidget` (widget #7,
  visible to MD/HSE Staff only).
- `seeders/RoleSeeder.php` - added `manage permits` permission, granted
  to MD and HSE Staff roles.

## Install steps

1. Place all new files in their `app/...` paths.
2. Run migrations:
   ```
   php artisan migrate
   ```
3. Replace `AdminPanelProvider.php` and `seeders/RoleSeeder.php`, then:
   ```
   php artisan db:seed --class=RoleSeeder
   php artisan permission:cache-reset
   php artisan optimize:clear
   ```

## What's included

### Permit Types (7)
Hot Work, Confined Space Entry, Working at Height, Electrical Isolation
(LOTO), Excavation, Lifting Operations, Cold Work / General Work.

### Permit Number
Auto-generated as `PTW-YYYY-MM-NNNN` (e.g. `PTW-2026-06-0001`), sequential
per month.

### Workflow (status field)
```
Draft -> Submitted -> Approved -> Active -> Closed
                  \-> Suspended (with reason) -> back to Active
                  \-> Cancelled / Expired
```
- **Draft**: only the original requester (Permit Holder) can edit.
- Once **Submitted** or beyond: only **MD** or **HSE Staff** (acting as
  Issuer/Area Authority) can edit/progress the permit.
- **Closed** status reveals Closeout Notes, Closed By, Closeout Date/Time
  fields.

### People
- **Permit Holder / Performer** (`requested_by`) - defaults to the
  current user.
- **Issuer / Authorizer** (`issued_by`).
- **Area Authority / Safety Officer** (`area_authority_id`) - for
  high-risk permits (confined space, hot work, electrical).

### Hazards & Controls
- Free-text Hazards Identified / Precautions.
- **PPE Required** - multi-select checklist (Hard Hat, Safety Glasses,
  Hearing Protection, Respirator, Gloves, Safety Boots, Hi-Vis Vest,
  Fall Arrest Harness, Face Shield, FR Coveralls).
- Emergency Procedures / Rescue Plan.

### Isolation (LOTO)
Toggle + details field. Auto-enabled by default when permit type is
**Electrical Isolation** or **Confined Space** (can be overridden).

### Gas Testing
Toggle + O₂ / LEL / H₂S / CO readings, Tested By, Test Date/Time.
Auto-enabled by default for **Confined Space** and **Hot Work**.

### Permit Checklist
A repeater of pre-condition checks (item, Verified/OK toggle, remarks).
The **"Load Default Checklist for this Permit Type"** button populates
the repeater with a standard set of checks per permit type (defined in
`PermitToWorkService::defaultChecklistItems()`), which the Issuer/Area
Authority then reviews and ticks off. Items can be added/removed/edited
freely afterward.

### Extend Permit
On the Edit page (for Approved/Active permits), an **"Extend Permit"**
button lets the Issuer extend `valid_to` with a reason - logged in
`permit_extensions` (full history shown on the View page).

### Dashboard Widget
**"Permits Expiring Soon / Overdue for Closeout"** (MD/HSE Staff only) -
shows all Approved/Active/Suspended permits where `valid_to` is within
4 hours or already passed (highlighted in red as overdue).

## Known simplifications / possible follow-ups

- No email/SMS notifications when a permit is about to expire (the
  dashboard widget is the current alerting mechanism). Could integrate
  with the planned SMS module later.
- No PDF "permit certificate" for printing/posting at the worksite yet -
  could add a `PermitPdfService` similar to `PayslipService` if needed
  (useful since physical PTW copies are often required onsite).
- Permission model is intentionally simple (anyone can request; MD/HSE
  Staff approve and progress). If the client needs stricter
  per-permit-type approval chains (e.g. only Electrical Supervisor can
  issue Electrical Isolation permits), this can be refined later.
- `area_authority_id` / `issued_by` are not currently restricted to
  users with specific roles in the Select - any user can be picked. Can
  be filtered to `hse_staff`/`md` roles if desired.
