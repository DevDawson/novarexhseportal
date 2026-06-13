<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FieldExpenseResource\Pages;
use App\Models\FieldExpense;
use App\Models\Project;
use App\Models\Staff;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FieldExpenseResource extends Resource
{
    protected static ?string $model = FieldExpense::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationGroup = 'Finance & Expenses';

    /**
     * Anyone who can submit expenses (staff, HSE, secretary, accountant, MD)
     * can view the list - the table/query is scoped to "own records" for
     * non-approvers via getEloquentQuery() below.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage field_expenses') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage field_expenses') ?? false;
    }

    /**
     * Non-approvers (anyone except MD/Accountant) only see their own claims.
     */
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if ($user && ! $user->hasAnyRole(['md', 'accountant'])) {
            $query->where('staff_id', $user->staff?->id);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Expense Details')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('project_id')
                        ->label('Project')
                        ->relationship('project', 'title')
                        ->searchable()
                        ->preload()
                        ->required(),

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

                    // NOTE: 'per_diem' should be added to the `category` enum
                    // on the field_expenses table migration alongside the
                    // existing fuel / accommodation / meals / etc. values.
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
                        ->native(false)
                        ->live(),

                    Forms\Components\TextInput::make('amount')
                        ->numeric()
                        ->prefix('TZS')
                        ->required(),

                    Forms\Components\Textarea::make('description')
                        ->label('Notes / Justification')
                        ->columnSpanFull()
                        ->rows(2),

                    Forms\Components\FileUpload::make('receipt_file')
                        ->label('Receipt / Proof')
                        ->directory('field-expenses/receipts')
                        ->image()
                        ->openable()
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Approval')
                ->description('To be completed by the Accountant / Managing Director.')
                ->columns(3)
                ->schema([
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
                        ->live(),

                    Forms\Components\Select::make('approved_by')
                        ->label('Reviewed By')
                        ->relationship('approvedBy', 'name')
                        ->searchable()
                        ->preload()
                        ->visible(fn (Forms\Get $get) => in_array($get('status'), ['approved', 'rejected', 'reimbursed'])),

                    Forms\Components\DateTimePicker::make('approved_at')
                        ->label('Reviewed At')
                        ->native(false)
                        ->visible(fn (Forms\Get $get) => in_array($get('status'), ['approved', 'rejected', 'reimbursed'])),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project.title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('staff.full_name')
                    ->label('Claimed By')
                    ->searchable(['staff.first_name', 'staff.last_name']),

                Tables\Columns\TextColumn::make('expense_date')
                    ->date('d M Y')
                    ->sortable(),

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
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'reimbursed' => 'Reimbursed',
                    ]),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'title')
                    ->searchable(),

                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'fuel' => 'Fuel',
                        'per_diem' => 'Per Diem',
                        'accommodation' => 'Accommodation',
                        'transport' => 'Transport',
                        'meals' => 'Meals',
                        'supplies' => 'Supplies',
                        'communication' => 'Communication',
                        'other' => 'Other',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (FieldExpense $record) => $record->status === 'pending'
                        && (auth()->user()?->hasAnyRole(['md', 'accountant']) ?? false))
                    ->requiresConfirmation()
                    ->action(fn (FieldExpense $record) => $record->update([
                        'status' => 'approved',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ])),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (FieldExpense $record) => $record->status === 'pending'
                        && (auth()->user()?->hasAnyRole(['md', 'accountant']) ?? false))
                    ->requiresConfirmation()
                    ->action(fn (FieldExpense $record) => $record->update([
                        'status' => 'rejected',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ])),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFieldExpenses::route('/'),
            'create' => Pages\CreateFieldExpense::route('/create'),
            'edit' => Pages\EditFieldExpense::route('/{record}/edit'),
        ];
    }
}
