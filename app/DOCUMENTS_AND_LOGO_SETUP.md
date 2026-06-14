# Logo on PDFs + Staff Documents (NIDA / CV / Certificates)

## Part A - Company Logo on all PDFs

### New files
```
migrations/2024_01_10_000001_create_settings_table.php
models/Setting.php
pages/CompanySettingsPage.php
views/company-settings.blade.php          -> resources/views/filament/pages/company-settings.blade.php
views/letterhead-partial.blade.php         -> resources/views/filament/pdf/partials/letterhead.blade.php
```

### Modified PDF templates (logo + company name now appear at the top)
```
views/payslip.blade.php
views/payroll-register-pdf.blade.php       -> filament/pdf/reports/payroll-register.blade.php
views/department-payroll-pdf.blade.php     -> filament/pdf/reports/department-payroll.blade.php
views/statutory-report-pdf.blade.php       -> filament/pdf/reports/statutory-report.blade.php
views/salary-cost-pdf.blade.php            -> filament/pdf/reports/salary-cost.blade.php
views/bank-transfer-schedule-pdf.blade.php -> filament/pdf/reports/bank-transfer-schedule.blade.php
views/annual-payroll-summary-pdf.blade.php -> filament/pdf/reports/annual-payroll-summary.blade.php
views/trial-balance-pdf.blade.php          -> filament/pdf/reports/trial-balance.blade.php
```

### Install steps
1. `php artisan migrate` (creates `settings` table)
2. Place `CompanySettingsPage.php` in `app/Filament/Pages/` - it will be
   auto-discovered (no need to edit AdminPanelProvider).
3. Place `company-settings.blade.php` at
   `resources/views/filament/pages/company-settings.blade.php`
4. Place `letterhead-partial.blade.php` at
   `resources/views/filament/pdf/partials/letterhead.blade.php`
5. Replace the listed PDF templates with the updated versions.
6. `php artisan storage:link` (if not already done) - the logo is
   uploaded to `storage/app/public/company/` and served from
   `public/storage/company/`.

### Usage
- Login as **MD** -> Settings -> **Company Settings**.
- Upload a logo (PNG/JPG, recommend square or wide, max 2MB), set
  Company Name and Tagline/Address.
- Save. Every PDF generated afterwards (Payslip, Payroll Register,
  PAYE/NSSF/WCF/SDL, Salary Cost, Bank Transfer Schedule, Annual
  Summary, Trial Balance) will show the logo + name + tagline at the
  top automatically.
- If no logo is uploaded, the letterhead still shows the company
  name/tagline (text only, no broken image).

---

## Part B - Staff Documents (NIDA Card, CV, Certificates)

### New files
```
migrations/2024_01_10_000002_add_document_fields_to_staff_table.php
services/CertificateMergeService.php
filament-resources/StaffResource/Concerns/MergesCertificateUploads.php
filament-resources/StaffResource/Pages/CreateStaff.php
filament-resources/StaffResource/Pages/EditStaff.php
filament-resources/StaffResource/Pages/ListStaff.php
```

### Modified files
- `models/Staff.php` - added `nida_card_path`, `cv_path`,
  `certificates_path` to `$fillable`.
- `filament-resources/StaffResource.php` - new "Documents" section in
  the form, plus a "Documents" status column (0/3, 1/3.., color-coded)
  in the staff list table.

### REQUIRED composer packages
PDF merging uses FPDI (for merging PDFs/images into one PDF) - this is
**separate** from `barryvdh/laravel-dompdf` (which only *generates*
PDFs from HTML, it cannot merge existing PDF files):

```bash
composer require setasign/fpdi setasign/fpdi-fpdf
```

Both packages are lightweight and permissively licensed (MIT/FPDI
license, FPDF license) - safe for commercial use, no GPL concerns.

### Install steps
1. `composer require setasign/fpdi setasign/fpdi-fpdf`
2. `php artisan migrate`
3. Place all new/modified files in their paths.
4. `php artisan storage:link` (if not already done).
5. `php artisan optimize:clear`

### How it works

On the **Staff -> Create/Edit** form, a new "Documents" section has:

- **NIDA Card** - single file (PDF or photo), stored directly to
  `nida_card_path`.
- **CV / Resume** - single PDF, stored directly to `cv_path`.
- **Add Certificates (multiple files)** - upload one or more files
  (PDFs and/or photos of certificates, in any order). On Save, these
  are merged via `CertificateMergeService::merge()` into **one single
  PDF** (each image becomes its own A4 page; multi-page PDFs are
  imported page-by-page in their original size/orientation), saved to
  `certificates_path`, and the original temporary uploads are deleted.
- A **"Download merged certificates PDF"** link appears once a merge
  has been completed.

Re-uploading new certificate files and saving again **replaces** the
merged PDF with a new one (old merged file is left in place but
overwritten in the DB reference - see "Known limitations" below).

### Staff list - Documents column

The Staff list shows a "Documents" badge per staff member:
- 🔴 `0/3` - no documents uploaded
- 🟡 `1/3 (NIDA)` / `2/3 (NIDA, CV)` etc. - partially complete
- 🟢 `3/3 (NIDA, CV, Certs)` - all three documents present

This gives HR a quick visual audit of which staff files are incomplete.

### Permissions

File visibility follows the existing StaffResource permissions (HR
Director / MD, per the existing canViewAny/canEdit on StaffResource -
no changes made to those).

### Known limitations / possible follow-ups

- The old merged certificates PDF (when re-merging) is **not**
  automatically deleted from storage - only the DB reference is
  replaced. A scheduled cleanup command could remove orphaned files in
  `storage/app/public/staff-documents/certificates/` later if needed.
- Certificate merge order follows the upload/reorder order in the
  "Add Certificates" field at the time of saving.
- Very large multi-page PDF certificates will increase the merged
  file size proportionally - `maxSize(5120)` (5MB) applies **per
  uploaded file**, not to the merged total.
