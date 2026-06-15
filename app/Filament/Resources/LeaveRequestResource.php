<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveRequestResource\Pages;
use App\Models\LeaveRequest;
use App\Models\Staff;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;

    protected static ?string $navigationIcon  = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'HR & Payroll';
    protected static ?string $navigationLabel = 'Leave Requests';
    protected static ?string $modelLabel      = 'Leave Request';
    protected static ?int    $navigationSort  = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage leave_requests') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage leave_requests') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage leave_requests') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hr_director']) ?? false;
    }

    /**
     * Scope query: non-HR/non-MD users see only their own leave requests.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['staff', 'leaveType', 'approvedBy']);

        $user = auth()->user();

        // HR Director, MD, and Secretary see all requests.
        if ($user?->hasAnyRole(['md', 'hr_director', 'secretary'])) {
            return $query;
        }

        // Everyone else (field_staff, hse_staff, etc.) sees only their own records.
        $staffId = Staff::where('user_id', $user?->id)->value('id');

        if ($staffId) {
            return $query->where('staff_id', $staffId);
        }

        // No staff record linked — return empty set.
        return $query->whereRaw('1 = 0');
    }

    public static function form(Form $form): Form
    {
        $user    = auth()->user();
        $isHrMd  = $user?->hasAnyRole(['md', 'hr_director']);

        return $form->schema([

            Forms\Components\Section::make('Leave Request')
                ->columns(2)
                ->schema([
                    // HR/MD can choose any staff; others default to their own Staff record.
                    Forms\Components\Select::make('staff_id')
                        ->label('Staff Member')
                        ->relationship('staff', 'first_name')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->first_name . ' ' . $record->last_name)
                        ->searchable()
                        ->preload()
                        ->required()
                        ->default(fn () => Staff::where('user_id', auth()->id())->value('id'))
                        ->disabled(! $isHrMd)
                        ->dehydrated(),

                    Forms\Components\Select::make('leave_type_id')
                        ->label('Leave Type')
                        ->relationship('leaveType', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\DatePicker::make('start_date')
                        ->label('Start Date')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::recalcDays($get, $set)),

                    Forms\Components\DatePicker::make('end_date')
                        ->label('End Date')
                        ->required()
                        ->afterOrEqual('start_date')
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::recalcDays($get, $set)),

                    Forms\Components\TextInput::make('days_requested')
                        ->label('Days Requested')
                        ->numeric()
                        ->readOnly()
                        ->dehydrated(),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'pending'   => 'Pending',
                            'approved'  => 'Approved',
                            'rejected'  => 'Rejected',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default('pending')
                        ->required()
                        ->disabled(! $isHrMd)
                        ->dehydrated(),

                    Forms\Components\Textarea::make('reason')
                        ->label('Reason')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Approval')
                ->columns(2)
                ->visible($isHrMd)
                ->schema([
                    Forms\Components\Select::make('approved_by')
                        ->label('Approved / Rejected By')
                        ->relationship('approvedBy', 'name')
                        ->searchable()
                        ->preload()
                        ->default(fn () => auth()->id()),

                    Forms\Components\DateTimePicker::make('approved_at')
                        ->label('Decision Date')
                        ->default(now()),
                ]),
        ]);
    }

    protected static function recalcDays(Forms\Get $get, Forms\Set $set): void
    {
        $start = $get('start_date');
        $end   = $get('end_date');
        if ($start && $end) {
            $days = \Illuminate\Support\Carbon::parse($start)->diffInDays(\Illuminate\Support\Carbon::parse($end)) + 1;
            $set('days_requested', max(0, $days));
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('staff.first_name')
                    ->label('Staff')
                    ->formatStateUsing(fn ($state, $record) => $record->staff?->first_name . ' ' . $record->staff?->last_name)
                    ->searchable(['staff.first_name', 'staff.last_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('leaveType.name')
                    ->label('Leave Type'),

                Tables\Columns\TextColumn::make('start_date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('days_requested')
                    ->label('Days')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger'  => 'rejected',
                        'gray'    => 'cancelled',
                    ]),

                Tables\Columns\TextColumn::make('approvedBy.name')
                    ->label('Decided By')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Decision Date')
                    ->dateTime('d M Y')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'   => 'Pending',
                        'approved'  => 'Approved',
                        'rejected'  => 'Rejected',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (LeaveRequest $record) => $record->status === 'pending'
                        && (auth()->user()?->hasAnyRole(['md', 'hr_director']) ?? false))
                    ->requiresConfirmation()
                    ->action(fn (LeaveRequest $record) => $record->update([
                        'status'      => 'approved',
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
                        'status'      => 'rejected',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ])),

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn (LeaveRequest $record) => $record->status === 'pending'),
            ])
            ->defaultSort('start_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLeaveRequests::route('/'),
            'create' => Pages\CreateLeaveRequest::route('/create'),
            'edit'   => Pages\EditLeaveRequest::route('/{record}/edit'),
        ];
    }
}
