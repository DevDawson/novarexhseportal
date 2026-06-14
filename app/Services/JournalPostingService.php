<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\Payroll;
use Illuminate\Support\Facades\DB;

class JournalPostingService
{
    /**
     * Account codes used by automatic payroll postings.
     * See ChartOfAccountsSeeder for descriptions.
     */
    private const ACCOUNT_BANK = '1001';
    private const ACCOUNT_STAFF_LOANS_RECEIVABLE = '1002';
    private const ACCOUNT_SALARY_PAYABLE = '2001';
    private const ACCOUNT_PAYE_PAYABLE = '2002';
    private const ACCOUNT_NSSF_PAYABLE_EMPLOYEE = '2003';
    private const ACCOUNT_NSSF_PAYABLE_EMPLOYER = '2004';
    private const ACCOUNT_NHIF_PAYABLE = '2005';
    private const ACCOUNT_WCF_PAYABLE = '2006';
    private const ACCOUNT_SDL_PAYABLE = '2007';
    private const ACCOUNT_WITHHOLDING_TAX_PAYABLE = '2008';
    private const ACCOUNT_OTHER_DEDUCTIONS_PAYABLE = '2009';
    private const ACCOUNT_SALARY_EXPENSE = '5001';
    private const ACCOUNT_EMPLOYER_STATUTORY_EXPENSE = '5002';

    /**
     * ---------------------------------------------------------------
     * AUTOMATIC JOURNAL POSTING AFTER PAYROLL APPROVAL
     * ---------------------------------------------------------------
     * Per spec:
     *   DR Staff Salary Expense Account
     *   CR Salary Payable Account
     *
     * Expanded here into a fully-balanced multi-line entry that also
     * records each statutory liability separately (so the Statutory
     * Compliance Reports - PAYE/NSSF/WCF/SDL - tie back to the ledger),
     * and the employer's own statutory contributions as a cost:
     *
     *   DR Staff Salary Expense ............. = gross_salary
     *   DR Employer Statutory Contributions . = nssf_employer + wcf + sdl
     *       CR Salary Payable ................ = net_salary
     *       CR PAYE Payable .................. = paye
     *       CR NSSF Payable - Employee ....... = nssf
     *       CR NSSF Payable - Employer ....... = nssf_employer
     *       CR NHIF Payable ................... = nhif
     *       CR WCF Payable ..................... = wcf
     *       CR SDL Payable ...................... = sdl
     *       CR Withholding Tax Payable ........ = withholding_tax (Consultants)
     *       CR Staff Loans & Advances Receivable = loan_deduction + advance_deduction
     *       CR Other Payroll Deductions Payable = other_deductions
     *
     * gross_salary = net_salary + paye + nssf + nhif + loan_deduction
     *               + advance_deduction + other_deductions + withholding_tax
     * (see Payroll::booted() - this is guaranteed by the calculation engine),
     * so the salary side of this entry always balances. The employer
     * statutory side (nssf_employer + wcf + sdl) balances independently.
     *
     * Idempotent: if an entry with source_type='payroll_approval' already
     * exists for this payroll, it is returned without posting again.
     */
    public static function postPayrollApproval(Payroll $payroll): JournalEntry
    {
        $existing = JournalEntry::where('source_type', 'payroll_approval')
            ->where('source_id', $payroll->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($payroll) {
            $lines = [];

            // --- Debit side: cost of this payroll period ---------------
            $lines[] = self::line(self::ACCOUNT_SALARY_EXPENSE, debit: (float) $payroll->gross_salary,
                description: 'Gross salary - '.$payroll->payroll_period->format('F Y'));

            $employerStatutory = (float) $payroll->nssf_employer + (float) $payroll->wcf + (float) $payroll->sdl;

            if ($employerStatutory > 0) {
                $lines[] = self::line(self::ACCOUNT_EMPLOYER_STATUTORY_EXPENSE, debit: $employerStatutory,
                    description: 'Employer NSSF + WCF + SDL - '.$payroll->payroll_period->format('F Y'));
            }

            // --- Credit side: liabilities created -----------------------
            self::addCreditIfPositive($lines, self::ACCOUNT_SALARY_PAYABLE, (float) $payroll->net_salary, 'Net salary payable');
            self::addCreditIfPositive($lines, self::ACCOUNT_PAYE_PAYABLE, (float) $payroll->paye, 'PAYE withheld');
            self::addCreditIfPositive($lines, self::ACCOUNT_NSSF_PAYABLE_EMPLOYEE, (float) $payroll->nssf, 'NSSF employee contribution withheld');
            self::addCreditIfPositive($lines, self::ACCOUNT_NSSF_PAYABLE_EMPLOYER, (float) $payroll->nssf_employer, 'NSSF employer contribution');
            self::addCreditIfPositive($lines, self::ACCOUNT_NHIF_PAYABLE, (float) $payroll->nhif, 'NHIF contribution withheld');
            self::addCreditIfPositive($lines, self::ACCOUNT_WCF_PAYABLE, (float) $payroll->wcf, 'WCF employer contribution');
            self::addCreditIfPositive($lines, self::ACCOUNT_SDL_PAYABLE, (float) $payroll->sdl, 'SDL employer contribution');
            self::addCreditIfPositive($lines, self::ACCOUNT_WITHHOLDING_TAX_PAYABLE, (float) $payroll->withholding_tax, 'Withholding tax (consultant)');

            $loansRecovered = (float) $payroll->loan_deduction + (float) $payroll->advance_deduction;
            self::addCreditIfPositive($lines, self::ACCOUNT_STAFF_LOANS_RECEIVABLE, $loansRecovered, 'Loan / advance recovered via payroll');

            self::addCreditIfPositive($lines, self::ACCOUNT_OTHER_DEDUCTIONS_PAYABLE, (float) $payroll->other_deductions, 'Other deductions');

            return self::createEntry(
                date: now(),
                description: 'Payroll approved - '.($payroll->staff?->full_name ?? 'Staff #'.$payroll->staff_id).' - '.$payroll->payroll_period->format('F Y'),
                sourceType: 'payroll_approval',
                sourceId: $payroll->id,
                lines: $lines,
            );
        });
    }

    /**
     * ---------------------------------------------------------------
     * AUTOMATIC JOURNAL POSTING AFTER SALARY PAYMENT
     * ---------------------------------------------------------------
     * Per spec:
     *   DR Salary Payable Account
     *   CR Bank Account
     *
     * Posted when payment_status transitions to 'paid'. Requires the
     * 'payroll_approval' entry to already exist (Salary Payable must
     * have been credited first) - if it doesn't, it is posted
     * automatically first so the ledger stays consistent even if a
     * payroll is marked Paid without going through Approved.
     *
     * Idempotent: if an entry with source_type='payroll_payment' already
     * exists for this payroll, it is returned without posting again.
     */
    public static function postPayrollPayment(Payroll $payroll): JournalEntry
    {
        $existing = JournalEntry::where('source_type', 'payroll_payment')
            ->where('source_id', $payroll->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        // Ensure the approval entry (which creates the Salary Payable
        // liability) exists before we try to clear it.
        self::postPayrollApproval($payroll);

        return DB::transaction(function () use ($payroll) {
            $netSalary = (float) $payroll->net_salary;

            $lines = [
                self::line(self::ACCOUNT_SALARY_PAYABLE, debit: $netSalary, description: 'Clear salary payable'),
                self::line(self::ACCOUNT_BANK, credit: $netSalary, description: 'Salary payment'),
            ];

            return self::createEntry(
                date: $payroll->payment_date ?? now(),
                description: 'Salary paid - '.($payroll->staff?->full_name ?? 'Staff #'.$payroll->staff_id).' - '.$payroll->payroll_period->format('F Y')
                    .($payroll->payment_reference ? ' (Ref: '.$payroll->payment_reference.')' : ''),
                sourceType: 'payroll_payment',
                sourceId: $payroll->id,
                lines: $lines,
            );
        });
    }

    /**
     * Reverse both automatic postings for a payroll record (e.g. if a
     * payroll is reverted from 'approved'/'paid' back to 'pending' by
     * mistake). Deletes the journal entries entirely rather than
     * creating reversing entries, since these are same-period corrections
     * of an automatic posting, not historical adjustments.
     */
    public static function reversePayrollPostings(Payroll $payroll): void
    {
        JournalEntry::where('source_type', 'payroll_payment')
            ->where('source_id', $payroll->id)
            ->each(fn (JournalEntry $entry) => $entry->delete());

        JournalEntry::where('source_type', 'payroll_approval')
            ->where('source_id', $payroll->id)
            ->each(fn (JournalEntry $entry) => $entry->delete());
    }

    // =================================================================
    // Helpers
    // =================================================================

    /**
     * @param  array<int, array{account_id: int, debit: float, credit: float, description: ?string}>  $lines
     */
    private static function createEntry(\Illuminate\Support\Carbon $date, string $description, string $sourceType, int $sourceId, array $lines): JournalEntry
    {
        $entry = JournalEntry::create([
            'reference' => JournalEntry::nextReference($date),
            'entry_date' => $date,
            'description' => $description,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'posted_by' => auth()->id(),
        ]);

        foreach ($lines as $line) {
            $entry->lines()->create($line);
        }

        return $entry;
    }

    private static function line(string $accountCode, float $debit = 0, float $credit = 0, ?string $description = null): array
    {
        return [
            'account_id' => self::accountId($accountCode),
            'debit' => round($debit, 2),
            'credit' => round($credit, 2),
            'description' => $description,
        ];
    }

    private static function addCreditIfPositive(array &$lines, string $accountCode, float $amount, string $description): void
    {
        if ($amount > 0) {
            $lines[] = self::line($accountCode, credit: $amount, description: $description);
        }
    }

    /**
     * Resolve a Chart of Accounts code to its primary key, throwing a
     * clear error if the Chart of Accounts hasn't been seeded yet.
     */
    private static function accountId(string $code): int
    {
        $id = Account::where('code', $code)->value('id');

        if (! $id) {
            throw new \RuntimeException(
                "Chart of Accounts is missing required account code '{$code}'. "
                ."Run: php artisan db:seed --class=ChartOfAccountsSeeder"
            );
        }

        return $id;
    }
}
