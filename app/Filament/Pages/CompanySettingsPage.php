<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class CompanySettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Company Settings';

    protected static ?string $title = 'Company Settings';

    protected static string $view = 'filament.pages.company-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('md') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'company_name' => Setting::companyName(),
            'company_tagline' => Setting::companyTagline(),
            'company_logo_path' => Setting::get(Setting::KEY_COMPANY_LOGO),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Company Letterhead')
                    ->description('This logo and name appear on the Payslip and all generated PDF reports (Payroll Register, PAYE/NSSF/WCF/SDL reports, Trial Balance, etc).')
                    ->schema([
                        Forms\Components\FileUpload::make('company_logo_path')
                            ->label('Company Logo')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('company')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->helperText('Recommended: square or wide PNG/JPG with transparent or white background, max 2MB.'),

                        Forms\Components\TextInput::make('company_name')
                            ->label('Company Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('company_tagline')
                            ->label('Tagline / Address')
                            ->maxLength(255),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set(Setting::KEY_COMPANY_LOGO, $data['company_logo_path'] ?? null);
        Setting::set(Setting::KEY_COMPANY_NAME, $data['company_name'] ?? null);
        Setting::set(Setting::KEY_COMPANY_TAGLINE, $data['company_tagline'] ?? null);

        Notification::make()
            ->title('Company settings saved')
            ->success()
            ->send();
    }
}
