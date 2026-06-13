# Filament v3 Resource Generation Commands
# Run these from your project root. --generate scaffolds form/table
# fields from the migration, which we then replace with the
# customised versions below.

php artisan make:filament-resource Payroll --generate
php artisan make:filament-resource FieldExpense --generate
php artisan make:filament-resource Incident --generate
php artisan make:filament-resource Tender --generate

# Optional: nested resources if you want Leave Requests / Tender Activities
# managed as Relation Managers instead of standalone resources:
# php artisan make:filament-relation-manager StaffResource leaveRequests staff_id
# php artisan make:filament-relation-manager TenderResource activities description
