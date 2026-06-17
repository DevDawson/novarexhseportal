<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SpillReportResource\Pages;
use App\Models\SpillReport;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SpillReportResource extends Resource
{
    protected static ?string $model = SpillReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationGroup = 'Environmental Management';

    protected static ?string $navigationLabel = 'Spill Reports';

    protected static ?string $modelLabel = 'Spill Report';

    protected static ?int $navigationSort = 5;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage environmental_aspects') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage environmental_aspects') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage environmental_aspects') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Spill Information')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('spill_reference')
                        ->disabled()
                        ->dehydrated()
                        ->placeholder('Auto-generated'),

                    Forms\Components\Select::make('project_id')
                        ->relationship('project', 'title')
                        ->searchable()
                        ->native(false),

                    Forms\Components\Select::make('reported_by_id')
                        ->label('Reported By')
                        ->options(User::all()->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->default(fn () => auth()->id())
                        ->native(false),

                    Forms\Components\DatePicker::make('spill_date')
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('location')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('substance_spilled')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Select::make('substance_type')
                        ->options(['oil' => 'Oil', 'chemical' => 'Chemical', 'fuel' => 'Fuel', 'sewage' => 'Sewage', 'acid' => 'Acid', 'paint' => 'Paint', 'solvent' => 'Solvent', 'other' => 'Other'])
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('estimated_volume')->numeric()->minValue(0),
                    Forms\Components\Select::make('volume_unit')
                        ->options(['litres' => 'Litres', 'm3' => 'm³', 'kg' => 'kg', 'tonnes' => 'Tonnes', 'other' => 'Other'])
                        ->native(false),

                    Forms\Components\Select::make('environmental_media_affected')
                        ->options(['soil' => 'Soil', 'water' => 'Water / Watercourse', 'air' => 'Air', 'multiple' => 'Multiple Media', 'none' => 'Contained (None Affected)'])
                        ->required()
                        ->native(false),

                    Forms\Components\Textarea::make('cause')->required()->rows(3)->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Response Actions')
                ->schema([
                    Forms\Components\Textarea::make('immediate_actions')->rows(3)->label('Immediate Actions Taken'),
                    Forms\Components\Textarea::make('containment_actions')->rows(3),
                    Forms\Components\Textarea::make('cleanup_actions')->rows(3),
                ]),

            Forms\Components\Section::make('Regulatory & Closure')
                ->columns(2)
                ->schema([
                    Forms\Components\Toggle::make('regulatory_notification_required')
                        ->label('Regulatory Notification Required')
                        ->live(),

                    Forms\Components\DateTimePicker::make('regulatory_notified_at')
                        ->label('Notified At')
                        ->native(false)
                        ->visible(fn ($get) => $get('regulatory_notification_required')),

                    Forms\Components\Select::make('status')
                        ->options(['reported' => 'Reported', 'contained' => 'Contained', 'cleaned_up' => 'Cleaned Up', 'closed' => 'Closed'])
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('closed_by_id')
                        ->label('Closed By')
                        ->options(User::all()->pluck('name', 'id'))
                        ->searchable()
                        ->native(false),

                    Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('spill_reference')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('spill_date')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('location')->searchable()->limit(25),
                Tables\Columns\TextColumn::make('substance_spilled')->limit(25),
                Tables\Columns\BadgeColumn::make('substance_type')
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->colors(['danger' => ['acid', 'chemical'], 'warning' => ['fuel', 'oil'], 'gray' => 'other']),
                Tables\Columns\BadgeColumn::make('environmental_media_affected')
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state)))
                    ->colors(['danger' => 'water', 'warning' => 'soil', 'gray' => 'none']),
                Tables\Columns\IconColumn::make('regulatory_notification_required')->label('Regulatory')->boolean()->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['danger' => 'reported', 'warning' => 'contained', 'primary' => 'cleaned_up', 'success' => 'closed'])
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['reported' => 'Reported', 'contained' => 'Contained', 'cleaned_up' => 'Cleaned Up', 'closed' => 'Closed']),
            ])
            ->defaultSort('spill_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSpillReports::route('/'),
            'create' => Pages\CreateSpillReport::route('/create'),
            'edit' => Pages\EditSpillReport::route('/{record}/edit'),
        ];
    }
}
