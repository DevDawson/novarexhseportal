<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultant_invoices', function (Blueprint $table) {
            $table->id();

            // ── Project link ──────────────────────────────────────────────
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();

            // ── Consultant identity ───────────────────────────────────────
            // If the consultant is already in Staff, link them; otherwise
            // use the free-form fields below.
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->string('consultant_name')->nullable();
            $table->string('consultant_type')->default('external'); // 'staff' | 'external'

            // Consultant registration details (filled from their proforma)
            $table->string('consultant_tin')->nullable();
            $table->string('consultant_vrn')->nullable();
            $table->string('consultant_business_reg')->nullable();
            $table->text('consultant_address')->nullable();
            $table->string('consultant_phone')->nullable();
            $table->string('consultant_email')->nullable();

            // Consultant bank details (for payment)
            $table->string('consultant_bank_name')->nullable();
            $table->string('consultant_bank_branch')->nullable();
            $table->string('consultant_bank_account_name')->nullable();
            $table->string('consultant_bank_account_number')->nullable();
            $table->string('consultant_bank_swift')->nullable();

            // ── Stage 1: Proforma Invoice ─────────────────────────────────
            $table->string('proforma_number')->nullable();
            $table->date('proforma_date')->nullable();
            $table->text('service_description')->nullable();
            $table->decimal('proforma_net_amount', 15, 2)->nullable();
            $table->decimal('proforma_vat_amount', 15, 2)->nullable();
            $table->decimal('proforma_total_amount', 15, 2)->nullable();
            $table->string('proforma_attachment')->nullable();

            // ── Stage 2: Proforma Verification ───────────────────────────
            $table->timestamp('proforma_verified_at')->nullable();
            $table->foreignId('proforma_verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('proforma_verification_notes')->nullable();

            // ── Stage 3: EFD / VFD Receipt ───────────────────────────────
            $table->string('efd_receipt_number')->nullable();
            $table->date('efd_receipt_date')->nullable();
            $table->decimal('efd_amount', 15, 2)->nullable();
            $table->string('efd_attachment')->nullable();

            // ── Stage 4: Payment ──────────────────────────────────────────
            $table->date('payment_date')->nullable();
            $table->string('payment_reference')->nullable();
            $table->decimal('payment_amount', 15, 2)->nullable();
            $table->text('payment_notes')->nullable();

            // ── Workflow ──────────────────────────────────────────────────
            $table->string('status')->default('pending');
            $table->text('rejection_reason')->nullable();

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultant_invoices');
    }
};
