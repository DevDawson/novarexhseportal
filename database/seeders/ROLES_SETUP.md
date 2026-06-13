# Roles & Permissions Setup (spatie/laravel-permission)

## 1. Install the package
```bash
composer require spatie/laravel-permission
```

## 2. Publish & run migrations
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

## 3. Add HasRoles trait to User model
Already applied in app/Models/User.php in this delivery:
```php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;
    ...
}
```

## 4. Register & run the RoleSeeder
In `database/seeders/DatabaseSeeder.php`:
```php
public function run(): void
{
    $this->call([
        RoleSeeder::class,
    ]);
}
```

Then:
```bash
php artisan db:seed --class=RoleSeeder
```

## 5. Assign roles to users
Either via Tinker, a seeder, or a Filament UserResource form (Select with
`roles` relationship). Example via Tinker:
```bash
php artisan tinker
```
```php
$user = App\Models\User::find(1);
$user->assignRole('md');

$user2 = App\Models\User::find(2);
$user2->assignRole('hr_director');
```

## 6. Role -> Module Access Matrix (as implemented)

| Role               | Dashboard | HSE/Incidents | BD/Tenders | Finance/Expenses | Approve Expenses | HR/Payroll | Deliverables | Users/Settings |
|--------------------|:---------:|:--------------:|:-----------:|:------------------:|:-------------------:|:------------:|:--------------:|:----------------:|
| MD                 | yes | yes | yes | yes | **yes** | yes (view/approve) | yes | yes |
| HR Director        | yes | - | - | - | - | **yes** | - | - |
| Business Director  | yes | - | **yes** | - | - | - | - | - |
| Accountant         | yes | - | - | **yes** | **yes** | yes (process) | - | - |
| IT Technician      | yes | - | - | - | - | - | - | **yes** |
| HSE Staff          | yes | **yes** | - | submit only | - | submit leave only | yes | - |
| Secretary          | yes | - | - | submit only | - | submit leave only | - | - |

## 7. Pattern used in each Resource
```php
public static function canViewAny(): bool
{
    return auth()->user()?->can('manage payroll') ?? false;
}
```

For per-record visibility (e.g. staff only seeing their own expense
claims), override `getEloquentQuery()`:
```php
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();

    if (! auth()->user()->hasAnyRole(['md', 'accountant'])) {
        $query->where('staff_id', auth()->user()->staff?->id);
    }

    return $query;
}
```

## 8. Alternative: Filament Shield
For a UI-driven permission manager (auto-generates permissions per
Resource/Page/Widget and lets MD assign roles via a panel), consider:
```bash
composer require bezhansalleh/filament-shield
php artisan shield:install
```
This generates `view_x`, `create_x`, `update_x`, `delete_x` permissions
per resource automatically and ships a Roles management page - useful
if the manual permission list above grows hard to maintain.
