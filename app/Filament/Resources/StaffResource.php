<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StaffResource\Pages;
use App\Filament\Resources\StaffResource\RelationManagers;
use App\Models\Staff;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StaffResource extends Resource
{
    protected static ?string $model = Staff::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'HR & Payroll';

    protected static ?string $modelLabel = 'Staff Member';

    /**
     * Staff Registry: HR Director and MD manage records. Accountant has
     * read-only access (needed for Payroll/Field Expense linking context).
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hr_director', 'accountant']) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hr_director']) ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hr_director']) ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('md') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Personal Information')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('first_name')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('last_name')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Select::make('gender')
                        ->options([
                            'male' => 'Male',
                            'female' => 'Female',
                        ])
                        ->native(false),

                    Forms\Components\DatePicker::make('date_of_birth')
                        ->native(false)
                        ->maxDate(now()->subYears(18))
                        ->displayFormat('d/m/Y'),

                    Forms\Components\TextInput::make('national_id')
                        ->label('National ID (NIDA)')
                        ->maxLength(255),

                    Forms\Components\Select::make('user_id')
                        ->label('Linked Login Account')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->helperText('Optional - link to a system user account for self-service (leave requests, etc).'),
                ]),

            Forms\Components\Section::make('Employment Details')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('staff_no')
                        ->label('Staff Number')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->helperText('e.g. WMC-EMP-001'),

                    Forms\Components\TextInput::make('job_title')
                        ->maxLength(255),

                    Forms\Components\Select::make('department_id')
                        ->label('Department')
                        ->relationship('department', 'name')
                        ->searchable()
                        ->preload(),

                    Forms\Components\Select::make('employment_type')
                        ->options([
                            'permanent' => 'Permanent',
                            'part_time' => 'Part-Time',
                            'casual' => 'Casual',
                            'consultant' => 'Consultant',
                            'contract' => 'Contract',
                            'intern' => 'Intern',
                        ])
                        ->default('permanent')
                        ->required()
                        ->native(false)
                        ->live(),

                    Forms\Components\DatePicker::make('date_joined')
                        ->native(false)
                        ->displayFormat('d/m/Y'),

                    Forms\Components\Select::make('status')
                        ->options([
                            'active' => 'Active',
                            'on_leave' => 'On Leave',
                            'terminated' => 'Terminated',
                            'suspended' => 'Suspended',
                        ])
                        ->default('active')
                        ->required()
                        ->native(false),
                ]),

            Forms\Components\Section::make('Statutory Identification Numbers (Tanzania)')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('nssf_no')
                        ->label('NSSF Number')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('nhif_no')
                        ->label('NHIF Number')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('tin_no')
                        ->label('TIN Number')
                        ->maxLength(255),
                ]),

            Forms\Components\Section::make('Salary & Banking')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('basic_salary')
                        ->label('Basic Salary')
                        ->numeric()
                        ->prefix('TZS')
                        ->visible(fn (Forms\Get $get) => in_array($get('employment_type'), ['permanent', 'contract', 'intern']))
                        ->required(fn (Forms\Get $get) => in_array($get('employment_type'), ['permanent', 'contract', 'intern']))
                        ->helperText('Used to pre-fill new Payroll records for this staff member.'),

                    Forms\Components\TextInput::make('hourly_rate')
                        ->label('Hourly Rate')
                        ->numeric()
                        ->prefix('TZS')
                        ->visible(fn (Forms\Get $get) => $get('employment_type') === 'part_time')
                        ->required(fn (Forms\Get $get) => $get('employment_type') === 'part_time')
                        ->helperText('Gross Pay = Hours Worked x Hourly Rate.'),

                    Forms\Components\TextInput::make('daily_rate')
                        ->label('Daily Rate')
                        ->numeric()
                        ->prefix('TZS')
                        ->visible(fn (Forms\Get $get) => $get('employment_type') === 'casual')
                        ->required(fn (Forms\Get $get) => $get('employment_type') === 'casual')
                        ->helperText('Gross Pay = Days Worked x Daily Rate.'),

                    Forms\Components\TextInput::make('contract_amount')
                        ->label('Contract Amount')
                        ->numeric()
                        ->prefix('TZS')
                        ->visible(fn (Forms\Get $get) => $get('employment_type') === 'consultant')
                        ->required(fn (Forms\Get $get) => $get('employment_type') === 'consultant')
                        ->helperText('Gross Payment = Contract Amount (per payment period).'),

                    Forms\Components\TextInput::make('bank_name')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('bank_account_no')
                        ->label('Bank Account Number')
                        ->maxLength(255),
                ]),

            Forms\Components\Section::make('Documents')
                ->description('Upload the staff member\'s National ID, CV, and professional certificates. Certificates uploaded below are automatically merged into a single PDF.')
                ->columns(2)
                ->schema([
                    Forms\Components\FileUpload::make('nida_card_path')
                        ->label('NIDA Card')
                        ->disk('public')
                        ->directory('staff-documents/nida')
                        ->visibility('public')
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->maxSize(5120)
                        ->downloadable()
                        ->openable()
                        ->helperText('PDF or photo of the National ID card (front/back combined or single scan). Max 5MB.'),

                    Forms\Components\FileUpload::make('cv_path')
                        ->label('CV / Resume')
                        ->disk('public')
                        ->directory('staff-documents/cv')
                        ->visibility('public')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(5120)
                        ->downloadable()
                        ->openable()
                        ->helperText('PDF only, max 5MB.'),

                    Forms\Components\FileUpload::make('certificate_uploads')
                        ->label('Add Certificates (multiple files)')
                        ->disk('public')
                        ->directory('staff-documents/certificates-source')
                        ->visibility('public')
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->multiple()
                        ->reorderable()
                        ->maxSize(5120)
                        ->dehydrated()
                        ->columnSpanFull()
                        ->helperText('Upload one or more certificates (PDF or photos of certificates). On save, these are merged into a single "Certificates" PDF below, in the order shown. Re-uploading replaces the merged file.'),

                    Forms\Components\Placeholder::make('certificates_path_display')
                        ->label('Merged Certificates PDF')
                        ->columnSpanFull()
                        ->content(function (?Staff $record) {
                            if (! $record?->certificates_path) {
                                return 'No merged certificates file yet - upload certificates above and save.';
                            }

                            $url = \Illuminate\Support\Facades\Storage::disk('public')->url($record->certificates_path);

                            return new \Illuminate\Support\HtmlString(
                                "<a href=\"{$url}\" target=\"_blank\" class=\"text-primary-600 underline\">Download merged certificates PDF</a>"
                            );
                        }),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('staff_no')
                    ->label('Staff No.')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name']),

                Tables\Columns\TextColumn::make('job_title')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('employment_type')
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title())
                    ->colors([
                        'success' => 'permanent',
                        'info' => ['contract', 'part_time'],
                        'warning' => 'casual',
                        'primary' => 'consultant',
                        'gray' => 'intern',
                    ]),

                Tables\Columns\TextColumn::make('basic_salary')
                    ->money('TZS')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('date_joined')
                    ->date('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'warning' => ['on_leave', 'suspended'],
                        'danger' => 'terminated',
                    ]),

                Tables\Columns\TextColumn::make('documents_status')
                    ->label('Documents')
                    ->state(function (Staff $record): string {
                        $present = collect([
                            'NIDA' => $record->nida_card_path,
                            'CV' => $record->cv_path,
                            'Certs' => $record->certificates_path,
                        ])->filter()->keys();

                        return $present->isEmpty() ? '0/3' : $present->count().'/3 ('.$present->implode(', ').')';
                    })
                    ->badge()
                    ->color(function (Staff $record) {
                        $count = collect([$record->nida_card_path, $record->cv_path, $record->certificates_path])
                            ->filter()->count();

                        return match (true) {
                            $count === 3 => 'success',
                            $count === 0 => 'danger',
                            default => 'warning',
                        };
                    })
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name'),

                Tables\Filters\SelectFilter::make('employment_type')
                    ->options([
                        'permanent' => 'Permanent',
                        'contract' => 'Contract',
                        'casual' => 'Casual',
                        'intern' => 'Intern',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'on_leave' => 'On Leave',
                        'terminated' => 'Terminated',
                        'suspended' => 'Suspended',
                    ]),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Tabs on the Staff Edit page: leave history and payroll history
     * for this specific staff member.
     */
    public static function getRelations(): array
    {
        return [
            RelationManagers\AttendanceRelationManager::class,
            RelationManagers\LeaveRequestsRelationManager::class,
            RelationManagers\PayrollsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStaff::route('/'),
            'create' => Pages\CreateStaff::route('/create'),
            'edit' => Pages\EditStaff::route('/{record}/edit'),
        ];
    }
}
