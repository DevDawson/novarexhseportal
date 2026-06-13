# Demo Data Seeder - Setup & Login Credentials

## 1. File placement
```
database/seeders/RoleSeeder.php          (already provided earlier)
database/seeders/LeaveTypeSeeder.php      (already provided earlier)
database/seeders/DemoDataSeeder.php       (new)
database/seeders/DatabaseSeeder.php       (replace existing)
```

## 2. Run the seeders
```powershell
php artisan migrate:fresh --seed
```
or, if your database already has data and you just want to add demo records:
```powershell
php artisan db:seed
```

> `migrate:fresh --seed` will **drop all tables** and rebuild from scratch -
> use this for a clean demo environment. Don't run it on production data.

## 3. Login Credentials (all use password: `password`)

| Role | Name | Email |
|---|---|---|
| Managing Director | Amani Mwakalinga | md@webmastercrew.online |
| HR Director | Grace Mushi | hr@webmastercrew.online |
| Business Director | Daniel Kessy | bd@webmastercrew.online |
| Accountant | Fatuma Salum | accounts@webmastercrew.online |
| IT Technician | Joseph Mbwana | it@webmastercrew.online |
| HSE Officer | Neema Chacha | hse@webmastercrew.online |
| Secretary | Rehema Juma | secretary@webmastercrew.online |
| Environmental Officer (HSE) | Peter Mwamba | hse2@webmastercrew.online |

## 4. What gets created

- **6 Departments**: Management, HSE & Technical, Finance, HR & Admin,
  Business Development, IT
- **8 Users** (one per role + 1 extra HSE officer), each with a linked
  **Staff** record (NSSF/NHIF/TIN numbers, basic salary, bank details)
- **4 Clients**: NOVAREX Mining, TPDC, Green Future NGO, JARICO Electrical
- **4 Projects** across ESIA, OHS Audit, Training, and a completed
  Consultancy project
- **5 Tenders** across different pipeline stages (won, lost, shortlisted, etc.)
- **5 Incidents** with varying severities (low/medium/high)
- **5 Risks** in the risk register with calculated risk ratings
- **2 ESIA/Audit** records
- **7 Field Expense** claims (mix of pending/approved/rejected/reimbursed)
- **Payroll** for all 8 staff for the current month (fully calculated:
  PAYE, NSSF, WCF, NHIF, net salary)
- **3 Invoices** (draft, sent, paid) with line items, VAT, totals
- **6 Petty Cash transactions** with running balance
- **5 Deliverables** in various document control states
- **5 Corporate Documents** (one already expired, one expiring in 20 days -
  to demonstrate the expiry alert widget)

## 5. Note on file uploads
`CorporateDocument.file_path` is seeded as a placeholder string
(`corporate-documents/placeholder.pdf`) which does not physically exist
in storage. The "View" link on these records will 404 - this is expected
for demo data. Upload a real file via the edit form to replace it if
needed for the demo.

## 6. Suggested demo flow for NOVAREX

1. Login as **MD** (`md@webmastercrew.online`) - show the full Dashboard
   (Stats Overview, Revenue vs Expenses chart, Incident Severity chart,
   Expiring Documents widget).
2. Login as **HSE Officer** (`hse@webmastercrew.online`) - open
   "ESIA for Mwanza Gold Processing Plant Expansion" project, walk through
   the Incidents, Risks, and ESIA/Audits tabs.
3. Login as **Accountant** (`accounts@webmastercrew.online`) - show
   Invoices, Field Expense approvals, Petty Cash, and the Monthly Financial
   Summary PDF export.
4. Login as **HR Director** (`hr@webmastercrew.online`) - show Staff
   Registry, Payroll (with live PAYE/NSSF/NHIF/WCF calculation), and Leave
   Requests.
