<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    /**
     * Seeds the minimum Chart of Accounts required for automatic
     * Payroll journal posting (DR/CR rules from the FRD), plus a
     * couple of general accounts an Accountant will need for manual
     * entries (e.g. Office Expenses, Owner's Equity).
     *
     * Account codes follow a simple convention:
     *   1xxx = Assets
     *   2xxx = Liabilities
     *   3xxx = Equity
     *   4xxx = Income
     *   5xxx = Expenses
     */
    public function run(): void
    {
        $accounts = [
            // ---- ASSETS ---------------------------------------------
            ['code' => '1001', 'name' => 'Bank Account', 'type' => 'asset', 'normal_balance' => 'debit', 'is_system' => true,
                'description' => 'Main operating bank account. Credited when payroll is paid out.'],

            ['code' => '1002', 'name' => 'Staff Loans & Advances Receivable', 'type' => 'asset', 'normal_balance' => 'debit', 'is_system' => true,
                'description' => 'Amounts owed by staff for loans/salary advances. Reduced (credited) as loan_deduction/advance_deduction is recovered through payroll.'],

            ['code' => '1003', 'name' => 'Petty Cash', 'type' => 'asset', 'normal_balance' => 'debit', 'is_system' => false,
                'description' => 'Office petty cash float.'],

            // ---- LIABILITIES -----------------------------------------
            ['code' => '2001', 'name' => 'Salary Payable', 'type' => 'liability', 'normal_balance' => 'credit', 'is_system' => true,
                'description' => 'Net salaries owed to staff, pending payment. Credited on payroll approval, debited on payment.'],

            ['code' => '2002', 'name' => 'PAYE Payable (TRA)', 'type' => 'liability', 'normal_balance' => 'credit', 'is_system' => true,
                'description' => 'PAYE withheld from staff, owed to Tanzania Revenue Authority.'],

            ['code' => '2003', 'name' => 'NSSF Payable - Employee', 'type' => 'liability', 'normal_balance' => 'credit', 'is_system' => true,
                'description' => 'NSSF contributions withheld from staff (10%).'],

            ['code' => '2004', 'name' => 'NSSF Payable - Employer', 'type' => 'liability', 'normal_balance' => 'credit', 'is_system' => true,
                'description' => 'NSSF contributions owed by the company as employer (10%).'],

            ['code' => '2005', 'name' => 'NHIF Payable', 'type' => 'liability', 'normal_balance' => 'credit', 'is_system' => true,
                'description' => 'NHIF / health insurance contributions withheld from staff.'],

            ['code' => '2006', 'name' => 'WCF Payable', 'type' => 'liability', 'normal_balance' => 'credit', 'is_system' => true,
                'description' => 'Workers Compensation Fund contributions owed by the company.'],

            ['code' => '2007', 'name' => 'SDL Payable (VETA)', 'type' => 'liability', 'normal_balance' => 'credit', 'is_system' => true,
                'description' => 'Skills Development Levy (4.5%) owed to VETA.'],

            ['code' => '2008', 'name' => 'Withholding Tax Payable', 'type' => 'liability', 'normal_balance' => 'credit', 'is_system' => true,
                'description' => 'Withholding tax deducted from consultant payments, owed to TRA.'],

            ['code' => '2009', 'name' => 'Other Payroll Deductions Payable', 'type' => 'liability', 'normal_balance' => 'credit', 'is_system' => true,
                'description' => 'Other miscellaneous deductions from staff salaries (e.g. union dues, insurance).'],

            // ---- EQUITY -------------------------------------------------
            ['code' => '3001', 'name' => "Owner's Equity", 'type' => 'equity', 'normal_balance' => 'credit', 'is_system' => false,
                'description' => 'Owner capital / retained earnings.'],

            // ---- INCOME --------------------------------------------------
            ['code' => '4001', 'name' => 'Consulting Revenue', 'type' => 'income', 'normal_balance' => 'credit', 'is_system' => false,
                'description' => 'Revenue from HSE consulting projects (invoices).'],

            // ---- EXPENSES ------------------------------------------------
            ['code' => '5001', 'name' => 'Staff Salary Expense', 'type' => 'expense', 'normal_balance' => 'debit', 'is_system' => true,
                'description' => 'Gross salary cost for the period. Debited on payroll approval.'],

            ['code' => '5002', 'name' => 'Employer Statutory Contributions Expense', 'type' => 'expense', 'normal_balance' => 'debit', 'is_system' => true,
                'description' => 'Employer NSSF + WCF + SDL cost for the period.'],

            ['code' => '5003', 'name' => 'Office & Administrative Expenses', 'type' => 'expense', 'normal_balance' => 'debit', 'is_system' => false,
                'description' => 'General office expenses (rent, utilities, supplies).'],

            ['code' => '5004', 'name' => 'Field & Project Expenses', 'type' => 'expense', 'normal_balance' => 'debit', 'is_system' => false,
                'description' => 'Approved field expense claims from project staff.'],
        ];

        foreach ($accounts as $account) {
            Account::updateOrCreate(
                ['code' => $account['code']],
                $account,
            );
        }
    }
}
