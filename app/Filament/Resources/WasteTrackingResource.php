<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WasteTrackingResource\Pages;
use App\Models\User;
use App\Models\WasteTrackingRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WasteTrackingResource extends Resource
{
    protected static ?string $model = WasteTrackingRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-trash';

    protected static ?string $navigationGroup = 'Environmental Management';

    protected static ?string $navigationLabel = 'Waste Tracking';

    protected static ?string $modelLabel = 'Waste Record';

    protected static ?int $navigationSort = 4;

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
            Forms\Components\Section::make('Waste Details')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('waste_type')
                        ->options([
                            'hazardous' => 'Hazardous',
                            'non_hazardous' => 'Non-Hazardous',
                            'recyclable' => 'Recyclable',
                            'clinical' => 'Clinical',
                            'e_waste' => 'E-Waste',
                            'liquid' => 'Liquid Waste',
                            'solid' => 'Solid Waste',
                        ])
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('project_id')
                        ->relationship('project', 'name')
                        ->searchable()
                        ->native(false),

                    Forms\Components\TextInput::make('waste_description')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('quantity')
                        ->required()
                        ->numeric()
                        ->minValue(0),

                    Forms\Components\Select::make('unit')
                        ->options(['kg' => 'kg', 'tonnes' => 'Tonnes', 'litres' => 'Litres', 'm3' => 'm³', 'bags' => 'Bags', 'drums' => 'Drums', 'pieces' => 'Pieces'])
                        ->required()
                        ->native(false),

                    Forms\Components\DatePicker::make('generation_date')
                        ->required()
                        ->native(false),
                ]),

            Forms\Components\Section::make('Disposal')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('disposal_method')
                        ->options([
                            'recycling' => 'Recycling',
                            'landfill' => 'Landfill',
                            'incineration' => 'Incineration',
                            'treatment' => 'Treatment',
                            'recovery' => 'Recovery',
                            'composting' => 'Composting',
                            'reuse' => 'Reuse',
                            'other' => 'Other',
                        ])
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('disposal_facility')->maxLength(255),
                    Forms\Components\TextInput::make('transporter')->maxLength(255),
                    Forms\Components\TextInput::make('manifest_number')->maxLength(100),

                    Forms\Components\Select::make('recorded_by_id')
                        ->label('Recorded By')
                        ->options(User::all()->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->default(fn () => auth()->id())
                        ->native(false),

                    Forms\Components\DatePicker::make('disposal_date')->native(false),

                    Forms\Components\Select::make('status')
                        ->options(['generated' => 'Generated', 'stored' => 'In Storage', 'disposed' => 'Disposed'])
                        ->required()
                        ->native(false),

                    Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('waste_type')
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state)))
                    ->colors(['danger' => 'hazardous', 'warning' => 'clinical', 'success' => 'recyclable', 'gray' => ['non_hazardous', 'solid']]),
                Tables\Columns\TextColumn::make('waste_description')->searchable()->limit(35),
                Tables\Columns\TextColumn::make('quantity')->suffix(fn ($record) => ' ' . $record->unit)->alignRight(),
                Tables\Columns\TextColumn::make('generation_date')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('disposal_method')->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state)))->toggleable(),
                Tables\Columns\TextColumn::make('project.name')->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['warning' => 'generated', 'primary' => 'stored', 'success' => 'disposed']),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('waste_type')
                    ->options(['hazardous' => 'Hazardous', 'non_hazardous' => 'Non-Hazardous', 'recyclable' => 'Recyclable', 'clinical' => 'Clinical', 'e_waste' => 'E-Waste', 'liquid' => 'Liquid', 'solid' => 'Solid']),
                Tables\Filters\SelectFilter::make('status')
                    ->options(['generated' => 'Generated', 'stored' => 'In Storage', 'disposed' => 'Disposed']),
            ])
            ->defaultSort('generation_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWasteTrackingRecords::route('/'),
            'create' => Pages\CreateWasteTrackingRecord::route('/create'),
            'edit' => Pages\EditWasteTrackingRecord::route('/{record}/edit'),
        ];
    }
}
