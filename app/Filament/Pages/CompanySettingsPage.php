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
            'company_name'          => Setting::companyName(),
            'company_tagline'       => Setting::companyTagline(),
            'company_logo_path'     => Setting::get(Setting::KEY_COMPANY_LOGO),
            'company_address'       => Setting::companyAddress(),
            'company_tin'           => Setting::companyTin(),
            'company_phone'         => Setting::companyPhone(),
            'company_email'         => Setting::companyEmail(),
            'company_bank_name'     => Setting::get(Setting::KEY_BANK_NAME),
            'company_bank_branch'   => Setting::get(Setting::KEY_BANK_BRANCH),
            'company_bank_account_name'   => Setting::get(Setting::KEY_BANK_ACCOUNT_NAME),
            'company_bank_account_number' => Setting::get(Setting::KEY_BANK_ACCOUNT_NUMBER),
            'company_bank_swift'    => Setting::get(Setting::KEY_BANK_SWIFT),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Company Letterhead')
                    ->description('Logo and name appear on all generated PDF reports.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\FileUpload::make('company_logo_path')
                            ->label('Company Logo')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('company')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->columnSpanFull()
                            ->helperText('Recommended: square or wide PNG/JPG, max 2 MB.'),

                        Forms\Components\TextInput::make('company_name')
                            ->label('Company Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('company_tagline')
                            ->label('Tagline / Short Description')
                            ->maxLength(255),
                    ]),

                Forms\Components\Section::make('Company Contact & Registration')
                    ->description('Shown on invoices and official documents.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Textarea::make('company_address')
                            ->label('Company Address')
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('company_tin')
                            ->label('TIN Number')
                            ->maxLength(50)
                            ->placeholder('e.g. 100-XXX-XXX'),

                        Forms\Components\TextInput::make('company_phone')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(30),

                        Forms\Components\TextInput::make('company_email')
                            ->label('Official Email')
                            ->email()
                            ->maxLength(255),
                    ]),

                Forms\Components\Section::make('Bank Details')
                    ->description('Used on invoices for client payment instructions.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('company_bank_name')
                            ->label('Bank Name')
                            ->maxLength(100)
                            ->placeholder('e.g. CRDB Bank PLC'),

                        Forms\Components\TextInput::make('company_bank_branch')
                            ->label('Branch')
                            ->maxLength(100)
                            ->placeholder('e.g. Mwanza Branch'),

                        Forms\Components\TextInput::make('company_bank_account_name')
                            ->label('Account Name')
                            ->maxLength(100),

                        Forms\Components\TextInput::make('company_bank_account_number')
                            ->label('Account Number')
                            ->maxLength(50),

                        Forms\Components\TextInput::make('company_bank_swift')
                            ->label('SWIFT / BIC Code')
                            ->maxLength(20)
                            ->placeholder('e.g. CRDBTZTZ'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set(Setting::KEY_COMPANY_LOGO,    $data['company_logo_path'] ?? null);
        Setting::set(Setting::KEY_COMPANY_NAME,    $data['company_name'] ?? null);
        Setting::set(Setting::KEY_COMPANY_TAGLINE, $data['company_tagline'] ?? null);
        Setting::set(Setting::KEY_COMPANY_ADDRESS, $data['company_address'] ?? null);
        Setting::set(Setting::KEY_COMPANY_TIN,     $data['company_tin'] ?? null);
        Setting::set(Setting::KEY_COMPANY_PHONE,   $data['company_phone'] ?? null);
        Setting::set(Setting::KEY_COMPANY_EMAIL,   $data['company_email'] ?? null);
        Setting::set(Setting::KEY_BANK_NAME,           $data['company_bank_name'] ?? null);
        Setting::set(Setting::KEY_BANK_BRANCH,         $data['company_bank_branch'] ?? null);
        Setting::set(Setting::KEY_BANK_ACCOUNT_NAME,   $data['company_bank_account_name'] ?? null);
        Setting::set(Setting::KEY_BANK_ACCOUNT_NUMBER, $data['company_bank_account_number'] ?? null);
        Setting::set(Setting::KEY_BANK_SWIFT,          $data['company_bank_swift'] ?? null);

        Notification::make()
            ->title('Company settings saved successfully.')
            ->success()
            ->send();
    }
}
