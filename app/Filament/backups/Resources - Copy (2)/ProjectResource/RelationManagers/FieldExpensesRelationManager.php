<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Models\FieldExpense;
use App\Models\Staff;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class FieldExpensesRelationManager extends RelationManager
{
    protected static string $relationship = 'fieldExpenses';

    protected static ?string $title = 'Field Expenses';

    protected static ?string $recordTitleAttribute = 'description';

    public static function canViewForRecord(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->can('manage field_expenses') ?? false;
    }

    public function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('staff_id')
                    ->label('Claimed By')
                    ->relationship('staff', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn (Staff $record) => "{$record->first_name} {$record->last_name} ({$record->staff_no})")
                    ->searchable(['first_name', 'last_name', 'staff_no'])
                    ->preload()
                    ->default(fn () => auth()->user()?->staff?->id)
                    ->required(),

                Forms\Components\DatePicker::make('expense_date')
                    ->native(false)
                    ->default(now())
                    ->required(),
            ]),

            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('category')
                    ->options([
                        'fuel' => 'Fuel',
                        'per_diem' => 'Per Diem',
                        'accommodation' => 'Accommodation',
                        'transport' => 'Transport',
                        'meals' => 'Meals',
                        'supplies' => 'Supplies',
                        'communication' => 'Communication',
                        'other' => 'Other',
                    ])
                    ->required()
                    ->native(false),

                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('TZS')
                    ->required(),
            ]),

            Forms\Components\Textarea::make('description')
                ->rows(2)
                ->columnSpanFull(),

            Forms\Components\FileUpload::make('receipt_file')
                ->label('Receipt')
                ->directory('field-expenses/receipts')
                ->image()
                ->openable()
                ->columnSpanFull(),

            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                    'reimbursed' => 'Reimbursed',
                ])
                ->default('pending')
                ->required()
                ->native(false)
                // Only MD/Accountant can change status away from pending here too.
                ->disabled(fn (?FieldExpense $record) => $record !== null && ! (auth()->user()?->hasAnyRole(['md', 'accountant']) ?? false)),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('expense_date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('staff.full_name')
                    ->label('Claimed By'),

                Tables\Columns\BadgeColumn::make('category')
                    ->colors([
                        'primary' => 'fuel',
                        'success' => 'per_diem',
                        'warning' => 'accommodation',
                        'gray' => ['transport', 'meals', 'supplies', 'communication', 'other'],
                    ]),

                Tables\Columns\TextColumn::make('amount')
                    ->money('TZS')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => ['approved', 'reimbursed'],
                        'danger' => 'rejected',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (FieldExpense $record) => $record->status === 'pending'),
            ]);
    }
}
