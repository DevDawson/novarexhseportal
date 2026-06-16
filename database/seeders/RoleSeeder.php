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

            // Risk Management
            'manage capa',
            'manage risk_register',

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

            // HIRA / HAZID
            'manage hazards',

            // HAZOP (Quantitative Risk Assessment)
            'manage hazop',

            // Internal Audit Module
            'manage audits',

            // EMS - Environmental Management System
            'manage environmental_aspects',
            'manage legal_register',
            'manage environmental_monitoring',
            'manage waste_tracking',
            'manage spill_reports',
            'manage environmental_permits',

            // Training & Competency
            'manage training',
            'manage certifications',
            'manage competency',

            // Energy Management (EnMS)
            'manage energy',

            // Document Control
            'manage documents',

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
            'manage roles',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // ----------------------------------------------------------------
        // Managing Director (MD) — full access, approval authority
        // ----------------------------------------------------------------
        $md = Role::findOrCreate('md');
        $md->syncPermissions($permissions);

        // ----------------------------------------------------------------
        // HSE Manager — full HSE system ownership (higher than HSE Officer)
        // ----------------------------------------------------------------
        $hseMgr = Role::findOrCreate('hse_manager');
        $hseMgr->syncPermissions([
            'view dashboard',
            'view hse', 'manage incidents', 'manage risks', 'manage risk_register',
            'manage esia_audits', 'manage permits', 'manage hazards', 'manage hazop', 'manage audits',
            'manage capa',
            'manage esia_screenings', 'manage esia_impacts', 'manage esia_mitigation',
            'manage esia_reports', 'manage esia_submissions',
            'manage deliverables',
            'manage field_expenses',
            'manage leave_requests',
            'manage environmental_aspects', 'manage legal_register', 'manage environmental_monitoring',
            'manage waste_tracking', 'manage spill_reports', 'manage environmental_permits',
            'manage training', 'manage certifications', 'manage competency',
            'manage energy',
            'manage corporate_documents', 'manage documents',
            'manage stakeholders', 'manage grievances',
        ]);

        // ----------------------------------------------------------------
        // HR Director
        // ----------------------------------------------------------------
        $hr = Role::findOrCreate('hr_director');
        $hr->syncPermissions([
            'view dashboard',
            'view hr', 'manage staff', 'manage leave_requests',
            'approve leave_requests', 'manage payroll',
            'manage corporate_documents', 'manage documents',
            'manage training', 'manage certifications', 'manage competency',
        ]);

        // ----------------------------------------------------------------
        // Business Director
        // ----------------------------------------------------------------
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

        // ----------------------------------------------------------------
        // Accountant
        // ----------------------------------------------------------------
        $accountant = Role::findOrCreate('accountant');
        $accountant->syncPermissions([
            'view dashboard',
            'view finance', 'manage invoices', 'manage field_expenses',
            'approve field_expenses', 'manage petty_cash',
            'view hr', 'manage payroll',
        ]);

        // ----------------------------------------------------------------
        // IT Technician / System Administrator
        // ----------------------------------------------------------------
        $it = Role::findOrCreate('it_technician');
        $it->syncPermissions([
            'view dashboard',
            'manage users', 'manage settings',
        ]);

        $sysAdmin = Role::findOrCreate('system_admin');
        $sysAdmin->syncPermissions([
            'view dashboard',
            'manage users', 'manage settings', 'manage roles',
        ]);

        // ----------------------------------------------------------------
        // HSE Staff (HSE Officer)
        // ----------------------------------------------------------------
        $hse = Role::findOrCreate('hse_staff');
        $hse->syncPermissions([
            'view dashboard',
            'view hse', 'manage incidents', 'manage risks', 'manage risk_register',
            'manage esia_audits', 'manage permits', 'manage hazards', 'manage hazop', 'manage audits',
            'manage capa',
            'manage esia_screenings', 'manage esia_impacts', 'manage esia_mitigation',
            'manage esia_reports', 'manage esia_submissions',
            'manage deliverables',
            'manage field_expenses',
            'manage leave_requests',
            'manage environmental_aspects', 'manage legal_register', 'manage environmental_monitoring',
            'manage waste_tracking', 'manage spill_reports', 'manage environmental_permits',
            'manage training', 'manage certifications', 'manage competency',
            'manage energy',
        ]);

        // ----------------------------------------------------------------
        // Supervisor / Line Manager
        // ----------------------------------------------------------------
        $supervisor = Role::findOrCreate('supervisor');
        $supervisor->syncPermissions([
            'view dashboard',
            'view hse',
            'manage incidents',
            'manage permits',
            'manage risks',
            'manage capa',
            'manage leave_requests',
            'manage field_expenses',
        ]);

        // ----------------------------------------------------------------
        // Lead Auditor (dedicated audit role)
        // ----------------------------------------------------------------
        $leadAuditor = Role::findOrCreate('lead_auditor');
        $leadAuditor->syncPermissions([
            'view dashboard',
            'view hse',
            'manage audits',
            'manage capa',
            'manage incidents',
        ]);

        // ----------------------------------------------------------------
        // Employee / Contractor (lowest tier — create and submit only)
        // ----------------------------------------------------------------
        $employee = Role::findOrCreate('employee');
        $employee->syncPermissions([
            'view dashboard',
            'manage incidents',
            'manage leave_requests',
            'manage field_expenses',
        ]);

        $contractor = Role::findOrCreate('contractor');
        $contractor->syncPermissions([
            'view dashboard',
            'manage incidents',
        ]);

        // ----------------------------------------------------------------
        // ESG Officer
        // ----------------------------------------------------------------
        $esg = Role::findOrCreate('esg_officer');
        $esg->syncPermissions([
            'view dashboard',
            'manage stakeholders', 'manage grievances', 'manage social_indicators',
            'manage governance_policies', 'manage ethics_incidents', 'manage esg_targets',
            'manage corporate_documents',
        ]);

        // ----------------------------------------------------------------
        // Field Staff
        // ----------------------------------------------------------------
        $fieldStaff = Role::findOrCreate('field_staff');
        $fieldStaff->syncPermissions([
            'view dashboard',
            'manage leave_requests',
            'manage field_expenses',
        ]);

        // ----------------------------------------------------------------
        // Secretary
        // ----------------------------------------------------------------
        $secretary = Role::findOrCreate('secretary');
        $secretary->syncPermissions([
            'view dashboard',
            'manage corporate_documents', 'manage documents',
            'manage leave_requests',
            'manage field_expenses',
        ]);
    }
}
