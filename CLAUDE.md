# PortalHSE — CLAUDE.md

Project context and conventions for Claude Code. Read this before making any changes.

---

## Project Identity

- **Product name:** PortalHSE (also referred to as NovarexHSE internally)
- **Client/owner:** Novarex — HSE & Sustainability consultancy, Mwanza, Tanzania
- **Purpose:** Integrated HSE, EMS, ESG, ESIA, PTW, finance, HR, and payroll management platform
- **Stack:** Laravel 11.54 · Filament v3.2 · MySQL (`portal` DB) · DomPDF (barryvdh/laravel-dompdf ^3.1) · Spatie Laravel Permission ^6.25
- **Admin panel URL:** `/admin` (Filament)
- **Landing page:** `/` (custom `resources/views/welcome.blade.php` — SaaS marketing page)
- **Training manual:** `/training/manual` (route name `training.manual`) — HTML page, use `window.print()` to save as PDF

---

## Key Architectural Patterns

### Filament Navigation Groups (exact names, exact order)
Defined in `app/Providers/Filament/AdminPanelProvider.php` via `->navigationGroups([])`.
Order is determined by **array position** — no `.sort()` method exists on NavigationGroup.
Groups must NOT have `->icon()` if their resource items already have `$navigationIcon`.

| # | Group name |
|---|------------|
| 1 | HSE System |
| 2 | Incident Management |
| 3 | Risk Assessment (HAZID) |
| 4 | Risk Assessment (HAZOP) |
| 5 | HIRA |
| 6 | Permit to Work (PTW) |
| 7 | Environmental Management (EMS) |
| 8 | ESG Management |
| 9 | EIA / ESIA |
| 10 | Environmental Audit |
| 11 | Audit Management System |
| — | Finance & Expenses |
| — | HR & Payroll |
| — | Training & Competency |
| — | Document Control |
| — | Energy Management |
| — | Settings |

### Settings (Key-Value Store)
Model: `app/Models/Setting.php`
- Stored in `settings` table as key/value pairs, cached with `Cache::rememberForever`
- Static constants: `KEY_COMPANY_NAME`, `KEY_COMPANY_TAGLINE`, `KEY_COMPANY_LOGO`, `KEY_COMPANY_ADDRESS`, `KEY_COMPANY_TIN`, `KEY_COMPANY_PHONE`, `KEY_COMPANY_EMAIL`, `KEY_BANK_NAME`, `KEY_BANK_BRANCH`, `KEY_BANK_ACCOUNT_NAME`, `KEY_BANK_ACCOUNT_NUMBER`, `KEY_BANK_SWIFT`
- Static accessors: `Setting::companyName()`, `Setting::companyTagline()`, `Setting::companyAddress()`, `Setting::companyTin()`, `Setting::companyPhone()`, `Setting::companyEmail()`, `Setting::bankDetails()` (returns array)
- Managed via `app/Filament/Pages/CompanySettingsPage.php` (MD-only)

### PDF Generation
- Library: `Barryvdh\DomPDF\Facade\Pdf`
- All PDFs live in `resources/views/pdf/`
- **DomPDF does NOT support `display:flex` or CSS Grid.** Use real `<table><tr><td>` HTML for multi-column layouts. Using `display:table-cell` without an explicit `display:table-row` parent throws "Parent table not found for table cell".
- Logo path must be an **absolute filesystem path** (use `public_path(...)`) — URLs do not work in DomPDF
- Shared letterhead partial: `resources/views/filament/pdf/partials/letterhead.blade.php` — `@include('filament.pdf.partials.letterhead')`
- All PDF routes are under the `pdf.` prefix group in `routes/web.php`

### PDF Routes (all auth-gated)
| Route name | Path | Controller method |
|---|---|---|
| `pdf.hira` | `/pdf/hira/{hazard}` | `hira()` |
| `pdf.audit` | `/pdf/audit/{audit}` | `auditReport()` |
| `pdf.incident` | `/pdf/incident/{incident}` | `incidentReport()` |
| `pdf.ems.aspect` | `/pdf/ems/aspect/{aspect}` | `environmentalAspect()` |
| `pdf.esg.summary` | `/pdf/esg/summary` | `esgSummary()` |
| `pdf.esia.report` | `/pdf/esia/report/{report}` | `esiaReport()` |
| `pdf.hazop.study` | `/pdf/hazop/study/{study}` | `hazopStudy()` |
| `pdf.hazop.procedure` | `/pdf/hazop/procedure` | `hazopProcedure()` |
| `pdf.ptw.permit` | `/pdf/ptw/permit/{permit}` | `ptwPermit()` |
| `pdf.env.audit` | `/pdf/env/audit/{audit}` | `environmentalAudit()` |
| `pdf.ams.audit` | `/pdf/ams/audit/{audit}` | `amsAuditReport()` |
| `pdf.invoice` | `/pdf/invoice/{invoice}` | `invoicePdf()` |

### Auth Gate Pattern
```php
abort_unless(auth()->user()?->can('manage invoices'), 403);
```
The IDE static analyzer reports P1013 "Undefined method 'user'" — these are **false positives**. The code works at runtime. Do not change this pattern.

### CSV Export (ManagementReportService)
- Helper: `toCsv()` in `app/Services/ManagementReportService.php`
- `fputcsv` fails with "Array to string conversion" if any row value is an array
- Fix applied: `toCsv()` defensively stringifies arrays as `key: value; key: value`
- Source methods should return pre-formatted strings, not arrays (see `staffCostByProject()`)

### Filament v3 Component Notes
- `BadgeColumn` is deprecated → use `TextColumn::make()->badge()->color(fn($state) => match($state){...})`
- `Select::readOnly()` does **not** exist → use `->disabled()->dehydrated()` to show-but-preserve value
- `DateTimePicker::readOnly()` works fine
- `NavigationGroup::sort()` does **not** exist → use array position in `->navigationGroups([])`

---

## Roles & Permissions

15 roles total. Key ones:

| Role | Key permissions |
|------|----------------|
| `md` | Everything — full access |
| `accountant` | manage invoices, manage payroll, manage petty\_cash, approve field\_expenses, view finance |
| `hse_manager` / `hse_staff` | manage incidents, hazards, hazop, permits, audits, esia\_\*, environmental\_\*, energy, risks |
| `hr_director` | manage staff, payroll, leave\_requests, training, certifications |
| `business_director` | manage projects, tenders, audits, esg\_targets |
| `esg_officer` | manage stakeholders, grievances, governance\_policies, social\_indicators, esg\_targets |
| `lead_auditor` | manage audits, capa, incidents |
| `system_admin` / `it_technician` | manage users, roles, settings |
| `field_staff` | manage field\_expenses, leave\_requests, incidents |
| `employee` / `contractor` / `supervisor` | limited — leave, field expenses, incidents |

Permission check in resources: `auth()->user()?->can('manage X')` or `->hasRole('md')` for MD-only actions.

---

## Data Models (key ones)

| Model | Table | Notes |
|-------|-------|-------|
| `Invoice` | `invoices` | Client invoices; `balance` = computed attribute (total − paid) |
| `InvoiceItem` | `invoice_items` | Line items; hasMany on Invoice |
| `Client` | `clients` | Has `company_name`, `contact_person`, `address`, `region`, `tin_number`, `email`, `phone` |
| `ConsultantInvoice` | `consultant_invoices` | Incoming consultant payment requests — 4-stage workflow |
| `Setting` | `settings` | Key-value store for company config |
| `Staff` | `staff` | `first_name` + `last_name`; accessor `full_name`; `consultant` employment type |
| `Project` | `projects` | Central hub; most other records FK to `project_id` |
| `HazardRegister` | `hazard_registers` | HIRA records |
| `HazopStudy` | `hazop_studies` | + nodes via `hazop_nodes` |
| `PermitToWork` | `permit_to_works` | PTW with approval chain |
| `InternalAudit` | `internal_audits` | AMS audits (ISO 9001/14001/45001/50001) |
| `EnvironmentalAudit` | `environmental_audits` | Separate from InternalAudit |
| `EsiaReport` | `esia_reports` | + 7 related ESIA models |

---

## Consultant Payment Workflow (`ConsultantInvoice`)

4-stage sequential workflow enforced via status column:

```
pending → proforma_received → proforma_verified (= awaiting_efd) → efd_received → paid
                                                                                  → rejected (from pending or proforma_received)
```

- **Stage 1** — Create request: project, consultant identity (staff FK or free-form name), proforma invoice details (number, date, description, net/VAT/total, attachment), consultant TIN/VRN/BRELA/address/bank
- **Stage 2** — Accountant clicks "Mark Received" then "Verify Proforma" (stamps `proforma_verified_at` + `proforma_verified_by`)
- **Stage 3** — After consultant sends EFD or VFD receipt: enter receipt number, date, amount, optional scan
- **Stage 4** — "Mark Paid": payment date, reference/voucher number, actual amount paid

Navigation: Finance & Expenses → Consultant Payments

---

## Invoice Features (Client Invoices)

Located: Finance & Expenses → Invoices

Row actions available:
- **Mark Paid** — one-click: sets status=paid, amount\_paid=total
- **PDF** — downloads invoice PDF via `route('pdf.invoice', $record)` (new tab)
- **Email** — modal form (pre-filled recipient email, subject, body) → sends `InvoiceMail` with PDF attached via `app/Mail/InvoiceMail.php`
- **WhatsApp** — builds `https://wa.me/{phone}?text={message}` URL; only visible when client has phone

Invoice PDF template: `resources/views/pdf/invoice.blade.php` (uses HTML tables throughout — no CSS flex/grid)
Email template: `resources/views/mail/invoice.blade.php`

---

## Mail

- `app/Mail/InvoiceMail.php` — sends invoice email with PDF attached in-memory via DomPDF `->output()`
- Current `MAIL_MAILER=log` in dev (emails logged, not sent). Change to `smtp` for production.
- Attachment pattern:
```php
Attachment::fromData(fn () => $pdfContent, $filename)->withMime('application/pdf')
```

---

## File Storage

- Public uploads (logos, company assets): `disk('public')`, directory `company/`
- Private uploads (consultant invoices, EFD scans): `disk('private')`, directories `consultant-invoices/proforma/` and `consultant-invoices/efd/`

---

## Views Structure

```
resources/views/
  welcome.blade.php          # SaaS landing page (dark theme, Tailwind CDN)
  training/
    manual.blade.php         # HTML training manual v2.1 — print/save as PDF via window.print()
  pdf/
    invoice.blade.php        # Client invoice PDF (HTML tables, no flex)
    hira.blade.php
    audit-report.blade.php
    incident-report.blade.php
    environmental-aspect.blade.php
    esg-summary.blade.php
    esia-report.blade.php
    hazop-study.blade.php
    hazop-procedure.blade.php
    ptw-permit.blade.php
    environmental-audit.blade.php
    ams-audit-report.blade.php
    layout.blade.php
    partials/letterhead.blade.php  # Shared PDF header with logo + company name
  mail/
    invoice.blade.php        # HTML email body for InvoiceMail
  filament/
    pages/
      company-settings.blade.php
```

---

## Coding Conventions

- No comments unless the WHY is non-obvious
- No docblocks or multi-line comment blocks
- Auth gates: `abort_unless(auth()->user()?->can('permission'), 403)` — ignore P1013 IDE warnings
- Notification after actions: `Notification::make()->title('...')->success()->send()`
- Navigation items added via `AdminPanelProvider::navigationItems()` for non-resource pages
- Currency: TZS (Tanzanian Shilling); VAT rate: 18%
- Dates: `d M Y` format for display (e.g. "18 Jun 2026")
- Tanzania statutory payroll deductions: PAYE (TRA bands), NSSF employee 10%, NHIF 3%, WCF employer 0.5%

---

## Do Not

- Do NOT add `->sort()` to `NavigationGroup::make()` — method does not exist
- Do NOT add `->icon()` to NavigationGroup if resource items have `$navigationIcon`
- Do NOT use `BadgeColumn` — deprecated, use `TextColumn->badge()`
- Do NOT use `Select::readOnly()` — use `->disabled()->dehydrated()`
- Do NOT use CSS flex/grid in DomPDF templates — use HTML `<table>` elements
- Do NOT pass arrays to `fputcsv` rows — stringify them first
- Do NOT commit `.env` or credentials
