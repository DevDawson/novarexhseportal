<?php

namespace App\Filament\Resources\StaffResource\RelationManagers;

use App\Services\AttendanceCalculationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AttendanceRelationManager extends RelationManager
{
    protected static string $relationship = 'attendances';

    protected static ?string $title = 'Attendance';

    public static function canViewForRecord(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hr_director']) ?? false;
    }

    public function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Grid::make(4)->schema([
                Forms\Components\DatePicker::make('attendance_date')
                    ->native(false)
                    ->default(now())
                    ->required(),

                Forms\Components\Select::make('status')
                    ->options([
                        'present' => 'Present',
                        'absent' => 'Absent',
                        'leave' => 'On Leave',
                        'holiday' => 'Holiday',
                    ])
                    ->default('present')
                    ->required()
                    ->native(false)
                    ->live(),

                Forms\Components\TimePicker::make('time_in')
                    ->seconds(false)
                    ->visible(fn (Get $get) => $get('status') === 'present')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculate($get, $set)),

                Forms\Components\TimePicker::make('time_out')
                    ->seconds(false)
                    ->visible(fn (Get $get) => $get('status') === 'present')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculate($get, $set)),
            ]),

            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('hours_worked')
                    ->numeric()
                    ->readOnly()
                    ->dehydrated()
                    ->visible(fn (Get $get) => $get('status') === 'present')
                    ->helperText('Hours Worked = Time Out - Time In'),

                Forms\Components\TextInput::make('overtime_hours')
                    ->numeric()
                    ->readOnly()
                    ->dehydrated()
                    ->visible(fn (Get $get) => $get('status') === 'present')
                    ->helperText('Overtime = Hours Worked - 8 (Standard Daily Hours), if positive.'),
            ]),

            Forms\Components\Textarea::make('notes')
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    /**
     * Recalculate hours_worked / overtime_hours live as time_in / time_out
     * are entered (mirrors the Attendance model's saving hook).
     */
    protected static function recalculate(Get $get, Set $set): void
    {
        $result = AttendanceCalculationService::calculate(
            $get('time_in'),
            $get('time_out'),
        );

        $set('hours_worked', $result['hours_worked']);
        $set('overtime_hours', $result['overtime_hours']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('attendance_date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('time_in')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('time_out')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('hours_worked')
                    ->label('Hours'),

                Tables\Columns\TextColumn::make('overtime_hours')
                    ->label('OT Hours')
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'gray'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'present',
                        'danger' => 'absent',
                        'info' => 'leave',
                        'gray' => 'holiday',
                    ]),
            ])
            ->defaultSort('attendance_date', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
