<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Company roles -> module permissions.
     *
     * Permission naming convention: "{action} {module}"
     * e.g. "view payroll", "approve field_expenses".
     */
    public function run(): void
    {
        $permissions = [
            // Dashboard & Core Admin
            'view dashboard',
            'manage corporate_documents',

            // HSE & Technical Operations
            'view hse',
            'manage incidents',
            'manage risks',
            'manage projects',
            'manage esia_audits',

            // Business Development
            'view business_development',
            'manage tenders',

            // Finance & Expenses
            'view finance',
            'manage invoices',
            'manage field_expenses',
            'approve field_expenses',
            'manage petty_cash',

            // HR & Payroll
            'view hr',
            'manage staff',
            'manage leave_requests',
            'approve leave_requests',
            'manage payroll',

            // Project Deliverables
            'manage deliverables',

            // System
            'manage users',
            'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // ------------------------------------------------------------
        // Managing Director (MD) - full oversight, approvals
        // ------------------------------------------------------------
        $md = Role::findOrCreate('md');
        $md->syncPermissions([
            'view dashboard',
            'manage corporate_documents',
            'view hse', 'manage incidents', 'manage risks', 'manage projects', 'manage esia_audits',
            'view business_development', 'manage tenders',
            'view finance', 'manage invoices', 'manage field_expenses', 'approve field_expenses', 'manage petty_cash',
            'view hr', 'manage staff', 'manage leave_requests', 'approve leave_requests', 'manage payroll',
            'manage deliverables',
            'manage users', 'manage settings',
        ]);

        // ------------------------------------------------------------
        // HR Director - Staff, Leave, Payroll
        // ------------------------------------------------------------
        $hr = Role::findOrCreate('hr_director');
        $hr->syncPermissions([
            'view dashboard',
            'view hr', 'manage staff', 'manage leave_requests', 'approve leave_requests', 'manage payroll',
            'manage corporate_documents',
        ]);

        // ------------------------------------------------------------
        // Business Director - Tenders, Clients, BD pipeline
        // ------------------------------------------------------------
        $bd = Role::findOrCreate('business_director');
        $bd->syncPermissions([
            'view dashboard',
            'view business_development', 'manage tenders',
            'manage projects',
            'manage corporate_documents',
        ]);

        // ------------------------------------------------------------
        // Accountant - Finance, Payroll (view/process), expense approvals
        // ------------------------------------------------------------
        $accountant = Role::findOrCreate('accountant');
        $accountant->syncPermissions([
            'view dashboard',
            'view finance', 'manage invoices', 'manage field_expenses', 'approve field_expenses', 'manage petty_cash',
            'view hr', 'manage payroll',
        ]);

        // ------------------------------------------------------------
        // IT Technician - system/users/settings, no business data
        // ------------------------------------------------------------
        $it = Role::findOrCreate('it_technician');
        $it->syncPermissions([
            'view dashboard',
            'manage users', 'manage settings',
        ]);

        // ------------------------------------------------------------
        // HSE Staff - Incidents, Risks, ESIA/Audits, Projects (read/contribute)
        // ------------------------------------------------------------
        $hse = Role::findOrCreate('hse_staff');
        $hse->syncPermissions([
            'view dashboard',
            'view hse', 'manage incidents', 'manage risks', 'manage esia_audits',
            'manage deliverables',
            'manage field_expenses', // can submit, but not approve
            'manage leave_requests', // can submit own leave
        ]);

        // ------------------------------------------------------------
        // Secretary - Corporate documents, basic admin support
        // ------------------------------------------------------------
        $secretary = Role::findOrCreate('secretary');
        $secretary->syncPermissions([
            'view dashboard',
            'manage corporate_documents',
            'manage leave_requests', // can submit own leave
            'manage field_expenses', // can submit, but not approve
        ]);
    }
}
