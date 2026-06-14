<?php

namespace App\Services;

use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayslipService
{
    /**
     * Generate the Payslip PDF for a single Payroll record and return it
     * as a streamed download response.
     *
     * Covers the 15 Payslip fields from the spec:
     *  1. Employee Name        9. PAYE
     *  2. Employee ID (Staff No) 10. NSSF
     *  3. Department            11. Loan Deductions
     *  4. Position               12. Other Deductions
     *  5. Payroll Month          13. Net Salary
     *  6. Gross Salary           14. Approval Reference
     *  7. Allowances             15. Payment Reference
     *  8. Overtime
     */
    public static function download(Payroll $payroll): StreamedResponse
    {
        $payroll->loadMissing(['staff.department']);

        // Sanitize string attributes in-memory (not persisted) to prevent
        // DomPDF's "Malformed UTF-8 characters" error if any text data
        // (staff name, department, job title, bank details, references,
        // etc.) contains invalid byte sequences. Casts/accessors (dates,
        // decimals, full_name) are preserved since we only touch raw
        // string attributes.
        self::sanitizeModelStrings($payroll);

        if ($payroll->staff) {
            self::sanitizeModelStrings($payroll->staff);

            if ($payroll->staff->department) {
                self::sanitizeModelStrings($payroll->staff->department);
            }
        }

        $pdf = Pdf::loadView('filament.pdf.payslip', [
            'payroll' => $payroll,
            'staff' => $payroll->staff,
        ])->setPaper('a5', 'portrait');

        $filename = sprintf(
            'payslip-%s-%s.pdf',
            $payroll->staff?->staff_no ?? 'staff',
            $payroll->payroll_period?->format('Y-m') ?? 'period',
        );

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename,
        );
    }

    /**
     * Repair invalid UTF-8 in a model's raw string attributes, in-memory
     * only (does not save to DB). Casted attributes (decimals, dates) are
     * untouched - only raw string values are sanitized.
     */
    protected static function sanitizeModelStrings(\Illuminate\Database\Eloquent\Model $model): void
    {
        $attributes = $model->getAttributes();

        foreach ($attributes as $key => $value) {
            if (is_string($value)) {
                $attributes[$key] = Utf8Sanitizer::cleanString($value);
            }
        }

        $model->setRawAttributes($attributes, true);
    }
}
