<?php

namespace App\Filament\Resources\StaffResource\RelationManagers;

use App\Services\PayrollCalculationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PayrollsRelationManager extends RelationManager
{
    protected static string $relationship = 'payrolls';

    protected static ?string $title = 'Payroll History';

    public static function canViewForRecord(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->can('manage payroll') ?? false;
    }

    public function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Grid::make(3)->schema([
                Forms\Components\DatePicker::make('payroll_period')
                    ->label('Payroll Period (Month)')
                    ->displayFormat('F Y')
                    ->required()
                    ->native(false),

                Forms\Components\TextInput::make('basic_salary')
                    ->numeric()
                    ->prefix('TZS')
                    ->required()
                    ->default(fn () => $this->getOwnerRecord()->basic_salary)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculate($get, $set)),

                Forms\Components\TextInput::make('allowances')
                    ->numeric()
                    ->prefix('TZS')
                    ->default(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculate($get, $set)),
            ]),

            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('gross_salary')
                    ->numeric()
                    ->prefix('TZS')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set) => self::runStatutoryCalculation($get, $set)),

                Forms\Components\Select::make('wcf_rate')
                    ->label('WCF Rate')
                    ->options([
                        '0.005' => '0.5% (Private)',
                        '0.01' => '1% (Public)',
                    ])
                    ->default('0.005')
                    ->native(false)
                    ->dehydrated(false)
                    ->live()
                    ->afterStateUpdated(fn (Get $get, Set $set) => self::runStatutoryCalculation($get, $set)),

                Forms\Components\TextInput::make('net_salary')
                    ->numeric()
                    ->prefix('TZS')
                    ->readOnly()
                    ->dehydrated(),
            ]),

            Forms\Components\Section::make('Statutory Deductions')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('paye')
                        ->label('PAYE')
                        ->numeric()
                        ->prefix('TZS')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateNet($get, $set)),

                    Forms\Components\TextInput::make('nssf')
                        ->label('NSSF (Employee)')
                        ->numeric()
                        ->prefix('TZS')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateNet($get, $set)),

                    Forms\Components\TextInput::make('nssf_employer')
                        ->label('NSSF (Employer)')
                        ->numeric()
                        ->prefix('TZS')
                        ->required(),

                    Forms\Components\TextInput::make('wcf')
                        ->label('WCF')
                        ->numeric()
                        ->prefix('TZS')
                        ->required(),

                    Forms\Components\TextInput::make('nhif')
                        ->label('NHIF')
                        ->numeric()
                        ->prefix('TZS')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateNet($get, $set)),

                    Forms\Components\TextInput::make('other_deductions')
                        ->numeric()
                        ->prefix('TZS')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateNet($get, $set)),
                ]),

            Forms\Components\Grid::make(3)->schema([
                Forms\Components\Select::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                    ])
                    ->default('pending')
                    ->required()
                    ->native(false)
                    ->live(),

                Forms\Components\DatePicker::make('payment_date')
                    ->native(false)
                    ->visible(fn (Get $get) => $get('payment_status') === 'paid'),

                Forms\Components\TextInput::make('payment_reference')
                    ->maxLength(255)
                    ->visible(fn (Get $get) => $get('payment_status') === 'paid'),
            ]),
        ]);
    }

    protected static function recalculate(Get $get, Set $set): void
    {
        $gross = (float) ($get('basic_salary') ?? 0) + (float) ($get('allowances') ?? 0);
        $set('gross_salary', round($gross, 2));

        self::runStatutoryCalculation($get, $set);
    }

    protected static function runStatutoryCalculation(Get $get, Set $set): void
    {
        $gross = (float) ($get('gross_salary') ?? 0);
        $wcfRate = (float) ($get('wcf_rate') ?? PayrollCalculationService::WCF_RATE_DEFAULT);

        $result = PayrollCalculationService::calculate($gross, $wcfRate);

        $set('nssf', $result['nssf']);
        $set('nssf_employer', $result['nssf_employer']);
        $set('wcf', $result['wcf']);
        $set('nhif', $result['nhif']);
        $set('paye', $result['paye']);

        self::recalculateNet($get, $set);
    }

    protected static function recalculateNet(Get $get, Set $set): void
    {
        $gross = (float) ($get('gross_salary') ?? 0);
        $deductions = (float) ($get('paye') ?? 0)
            + (float) ($get('nssf') ?? 0)
            + (float) ($get('nhif') ?? 0)
            + (float) ($get('other_deductions') ?? 0);

        $set('net_salary', round($gross - $deductions, 2));
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payroll_period')
                    ->date('F Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('gross_salary')
                    ->money('TZS'),

                Tables\Columns\TextColumn::make('paye')
                    ->label('PAYE')
                    ->money('TZS')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('nssf')
                    ->label('NSSF')
                    ->money('TZS')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('nhif')
                    ->label('NHIF')
                    ->money('TZS')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('net_salary')
                    ->money('TZS')
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('payment_status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                    ]),
            ])
            ->defaultSort('payroll_period', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()?->hasRole('md') ?? false),
            ]);
    }
}
