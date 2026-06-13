<?php

namespace App\Filament\Resources\StaffResource\RelationManagers;

use App\Models\LeaveRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LeaveRequestsRelationManager extends RelationManager
{
    protected static string $relationship = 'leaveRequests';

    protected static ?string $title = 'Leave Requests';

    public static function canViewForRecord(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->can('manage leave_requests') ?? false;
    }

    public function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Select::make('leave_type_id')
                ->label('Leave Type')
                ->relationship('leaveType', 'name')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Grid::make(3)->schema([
                Forms\Components\DatePicker::make('start_date')
                    ->native(false)
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::recalculateDays($get, $set)),

                Forms\Components\DatePicker::make('end_date')
                    ->native(false)
                    ->required()
                    ->afterOrEqual('start_date')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::recalculateDays($get, $set)),

                Forms\Components\TextInput::make('days_requested')
                    ->numeric()
                    ->readOnly()
                    ->dehydrated(),
            ]),

            Forms\Components\Textarea::make('reason')
                ->rows(2)
                ->columnSpanFull(),

            Forms\Components\Section::make('Approval')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('status')
                        ->options([
                            'pending' => 'Pending',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default('pending')
                        ->required()
                        ->native(false)
                        ->live()
                        // Only MD/HR Director can change the approval status.
                        ->disabled(fn () => ! (auth()->user()?->hasAnyRole(['md', 'hr_director']) ?? false)),

                    Forms\Components\Select::make('approved_by')
                        ->relationship('approvedBy', 'name')
                        ->searchable()
                        ->preload()
                        ->visible(fn (Forms\Get $get) => in_array($get('status'), ['approved', 'rejected'])),

                    Forms\Components\DateTimePicker::make('approved_at')
                        ->native(false)
                        ->visible(fn (Forms\Get $get) => in_array($get('status'), ['approved', 'rejected'])),
                ]),
        ]);
    }

    /**
     * Inclusive day count between start_date and end_date.
     */
    protected static function recalculateDays(Forms\Get $get, Forms\Set $set): void
    {
        $start = $get('start_date');
        $end = $get('end_date');

        if ($start && $end) {
            $days = \Illuminate\Support\Carbon::parse($start)->diffInDays(\Illuminate\Support\Carbon::parse($end)) + 1;
            $set('days_requested', max(0, $days));
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('leaveType.name')
                    ->label('Leave Type'),

                Tables\Columns\TextColumn::make('start_date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('days_requested')
                    ->label('Days'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'gray' => 'cancelled',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (LeaveRequest $record) => $record->status === 'pending'
                        && (auth()->user()?->hasAnyRole(['md', 'hr_director']) ?? false))
                    ->requiresConfirmation()
                    ->action(fn (LeaveRequest $record) => $record->update([
                        'status' => 'approved',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ])),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (LeaveRequest $record) => $record->status === 'pending'
                        && (auth()->user()?->hasAnyRole(['md', 'hr_director']) ?? false))
                    ->requiresConfirmation()
                    ->action(fn (LeaveRequest $record) => $record->update([
                        'status' => 'rejected',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ])),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn (LeaveRequest $record) => $record->status === 'pending'),
            ]);
    }
}
