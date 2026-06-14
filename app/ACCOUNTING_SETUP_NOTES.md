# Accounting Journal (DR/CR) - Setup Notes (#8)

## New files

```
migrations/2024_01_09_000001_create_chart_of_accounts_table.php
migrations/2024_01_09_000002_create_journal_entries_tables.php
migrations/2024_01_09_000003_add_approved_status_to_payroll_table.php

models/Account.php
models/JournalEntry.php
models/JournalEntryLine.php

services/JournalPostingService.php

seeders/ChartOfAccountsSeeder.php

filament-resources/AccountResource.php
filament-resources/AccountResource/Pages/ListAccounts.php
filament-resources/AccountResource/Pages/CreateAccount.php
filament-resources/AccountResource/Pages/EditAccount.php

filament-resources/JournalEntryResource.php
filament-resources/JournalEntryResource/Pages/ListJournalEntries.php
filament-resources/JournalEntryResource/Pages/CreateJournalEntry.php
filament-resources/JournalEntryResource/Pages/EditJournalEntry.php
filament-resources/JournalEntryResource/Pages/ViewJournalEntry.php

views/trial-balance-pdf.blade.php -> resources/views/filament/pdf/reports/trial-balance.blade.php
```

## Modified files

- `models/Payroll.php` - added `saved()` hook that posts/reverses journal
  entries based on `payment_status` transitions.
- `filament-resources/PayrollResource.php` - `payment_status` now has 3
  options: Pending / Approved / Paid (was Pending / Paid).
- `filament-resources/StaffResource/RelationManagers/PayrollsRelationManager.php`
  - same 3-option update.
- `services/ManagementReportService.php` - added `trialBalance()`.
- `pages/ManagementReportsPage.php` - added `trialBalanceAction()`.
- `views/management-reports.blade.php` - added "Accounting Journal" section.

## Install steps

1. Place all new files in their corresponding `app/...` paths.
2. Run migrations:
   ```
   php artisan migrate
   ```
3. Seed the Chart of Accounts (required before any payroll can be
   approved/paid - JournalPostingService throws a clear error if a
   required account code is missing):
   ```
   php artisan db:seed --class=ChartOfAccountsSeeder
   ```
4. `php artisan optimize:clear`

## How it works

### Salary Approval Workflow (per FRD section 6)

```
HR generates Payroll (payment_status = pending)
    -> Finance Manager / MD sets payment_status = approved
         -> JournalPostingService::postPayrollApproval() runs automatically
            DR Staff Salary Expense (gross_salary)
            DR Employer Statutory Contributions Expense (nssf_employer+wcf+sdl)
                CR Salary Payable (net_salary)
                CR PAYE / NSSF / NHIF / WCF / SDL / Withholding Tax Payable
                CR Staff Loans & Advances Receivable (loan+advance recovered)
                CR Other Payroll Deductions Payable
    -> Accountant sets payment_status = paid (after bank transfer)
         -> JournalPostingService::postPayrollPayment() runs automatically
            DR Salary Payable (net_salary)
            CR Bank Account (net_salary)
```

Both postings are **idempotent** - re-saving a Payroll record without
changing `payment_status` does not create duplicate entries. If
`payment_status` is reverted to `pending`, both automatic entries for
that payroll are deleted (`JournalPostingService::reversePayrollPostings()`).

### Where to view postings

- **Finance & Expenses -> Journal Entries**: every automatic posting
  appears here with source "Payroll Approval" / "Payroll Payment",
  linked back to the originating Payroll record. Automatic entries
  cannot be edited/deleted (must stay in sync with Payroll).
- **Finance & Expenses -> Chart of Accounts**: shows the running
  balance of every account (e.g. "Salary Payable" should return to
  zero once all approved payrolls for a period are also marked Paid).
- **Finance & Expenses -> Management Reports -> Accounting Journal ->
  Trial Balance**: PDF showing all account balances, with a
  Debit=Credit balance check.

### Manual journal entries

Accountant/MD can also create **manual** journal entries (source =
"Manual Entry") for anything outside payroll - e.g. recording an
invoice payment received, office rent, or statutory remittance to TRA/
NSSF/WCF/VETA (DR the relevant Payable account, CR Bank). The form
enforces Debit = Credit before saving via a live balance-check display
and a server-side validation guard.

### Permissions

- **Chart of Accounts** & **Journal Entries**: MD and Accountant only.
- System accounts (`is_system = true`) cannot be edited or deleted -
  these are the 13 accounts JournalPostingService depends on.
- Automatic journal entries (`source_type != 'manual'`) cannot be
  edited or deleted from the UI - only by changing the underlying
  Payroll's `payment_status` (which triggers post/reverse automatically).

## Known simplifications / future extensions

- **Statutory remittance** (paying TRA/NSSF/WCF/VETA from the Payable
  accounts) is recorded via **manual journal entries** for now (DR
  Payable account, CR Bank). A dedicated "Pay Statutory Liability"
  action with a Payable-account picker could automate this later,
  similar to JournalPostingService::postPayrollPayment().
- **SELCOM Gateway integration** remains explicitly out of scope (per
  earlier instruction) - the Bank Account postings assume manual/other
  payment rails.
- Consultant withholding tax is posted to "Withholding Tax Payable"
  (2008) on approval; remitting it to TRA would also be a manual
  journal entry (DR 2008, CR Bank).
