<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
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
            'manage permits',

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

            // HIRA
            'manage hazards',

            // Internal Audit Module
            'manage audits',

            // EMS - Environmental Management System
            'manage environmental_aspects',
            'manage legal_register',
            'manage environmental_monitoring',

            // ESG - Environment, Society & Governance
            'manage stakeholders',
            'manage grievances',
            'manage social_indicators',
            'manage governance_policies',
            'manage ethics_incidents',
            'manage esg_targets',

            // EIA / ESIA
            'manage esia_screenings',
            'manage esia_impacts',
            'manage esia_mitigation',
            'manage esia_reports',
            'manage esia_submissions',

            // System
            'manage users',
            'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // ------------------------------------------------------------
        // Managing Director (MD)
        // ------------------------------------------------------------
        $md = Role::findOrCreate('md');
        $md->syncPermissions([
            'view dashboard',
            'manage corporate_documents',
            'view hse', 'manage incidents', 'manage risks', 'manage projects',
            'manage esia_audits', 'manage permits', 'manage hazards', 'manage audits',
            'manage esia_screenings', 'manage esia_impacts', 'manage esia_mitigation',
            'manage esia_reports', 'manage esia_submissions',
            'view business_development', 'manage tenders',
            'view finance', 'manage invoices', 'manage field_expenses',
            'approve field_expenses', 'manage petty_cash',
            'view hr', 'manage staff', 'manage leave_requests',
            'approve leave_requests', 'manage payroll',
            'manage deliverables',
            'manage environmental_aspects', 'manage legal_register', 'manage environmental_monitoring',
            'manage stakeholders', 'manage grievances', 'manage social_indicators',
            'manage governance_policies', 'manage ethics_incidents', 'manage esg_targets',
            'manage users', 'manage settings',
        ]);

        // ------------------------------------------------------------
        // HR Director
        // ------------------------------------------------------------
        $hr = Role::findOrCreate('hr_director');
        $hr->syncPermissions([
            'view dashboard',
            'view hr', 'manage staff', 'manage leave_requests',
            'approve leave_requests', 'manage payroll',
            'manage corporate_documents',
        ]);

        // ------------------------------------------------------------
        // Business Director
        // ------------------------------------------------------------
        $bd = Role::findOrCreate('business_director');
        $bd->syncPermissions([
            'view dashboard',
            'view business_development', 'manage tenders',
            'manage projects',
            'manage corporate_documents',
            'manage hazards',
            'manage audits',
            'manage esg_targets',
        ]);

        // ------------------------------------------------------------
        // Accountant
        // ------------------------------------------------------------
        $accountant = Role::findOrCreate('accountant');
        $accountant->syncPermissions([
            'view dashboard',
            'view finance', 'manage invoices', 'manage field_expenses',
            'approve field_expenses', 'manage petty_cash',
            'view hr', 'manage payroll',
        ]);

        // ------------------------------------------------------------
        // IT Technician
        // ------------------------------------------------------------
        $it = Role::findOrCreate('it_technician');
        $it->syncPermissions([
            'view dashboard',
            'manage users', 'manage settings',
        ]);

        // ------------------------------------------------------------
        // HSE Staff
        // ------------------------------------------------------------
        $hse = Role::findOrCreate('hse_staff');
        $hse->syncPermissions([
            'view dashboard',
            'view hse', 'manage incidents', 'manage risks',
            'manage esia_audits', 'manage permits', 'manage hazards', 'manage audits',
            'manage esia_screenings', 'manage esia_impacts', 'manage esia_mitigation',
            'manage esia_reports', 'manage esia_submissions',
            'manage deliverables',
            'manage field_expenses',
            'manage leave_requests',
            'manage environmental_aspects', 'manage legal_register', 'manage environmental_monitoring',
        ]);

        // ------------------------------------------------------------
        // ESG Officer
        // ------------------------------------------------------------
        $esg = Role::findOrCreate('esg_officer');
        $esg->syncPermissions([
            'view dashboard',
            'manage stakeholders', 'manage grievances', 'manage social_indicators',
            'manage governance_policies', 'manage ethics_incidents', 'manage esg_targets',
            'manage corporate_documents',
        ]);

        // ------------------------------------------------------------
        // Field Staff
        // ------------------------------------------------------------
        $fieldStaff = Role::findOrCreate('field_staff');
        $fieldStaff->syncPermissions([
            'view dashboard',
            'manage leave_requests',
            'manage field_expenses',
        ]);

        // ------------------------------------------------------------
        // Secretary
        // ------------------------------------------------------------
        $secretary = Role::findOrCreate('secretary');
        $secretary->syncPermissions([
            'view dashboard',
            'manage corporate_documents',
            'manage leave_requests',
            'manage field_expenses',
        ]);
    }
}
