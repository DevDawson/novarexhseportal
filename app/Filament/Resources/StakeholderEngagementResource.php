<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StakeholderEngagementResource\Pages;
use App\Models\StakeholderEngagement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StakeholderEngagementResource extends Resource
{
    protected static ?string $model = StakeholderEngagement::class;

    protected static ?string $navigationIcon  = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'ESG Management';
    protected static ?string $navigationLabel = 'Engagement Log';
    protected static ?string $modelLabel      = 'Engagement';
    protected static ?int    $navigationSort  = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage stakeholders') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage stakeholders') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage stakeholders') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'esg_officer']) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Engagement Details')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('stakeholder_id')
                        ->label('Stakeholder')
                        ->relationship('stakeholder', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpanFull(),

                    Forms\Components\DatePicker::make('engagement_date')
                        ->label('Engagement Date')
                        ->required()
                        ->default(now()),

                    Forms\Components\Select::make('method')
                        ->label('Method')
                        ->options(StakeholderEngagement::METHOD_LABELS)
                        ->required(),

                    Forms\Components\TextInput::make('topic')
                        ->label('Topic / Subject')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('conducted_by')
                        ->label('Conducted By')
                        ->relationship('conductedBy', 'name')
                        ->searchable()
                        ->preload()
                        ->default(fn () => auth()->id()),

                    Forms\Components\Textarea::make('summary')
                        ->label('Summary')
                        ->required()
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('outcome')
                        ->label('Outcome')
                        ->rows(2)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('commitments_made')
                        ->label('Commitments Made')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Follow-Up')
                ->columns(2)
                ->schema([
                    Forms\Components\DatePicker::make('follow_up_date')
                        ->label('Follow-Up Date'),

                    Forms\Components\Toggle::make('follow_up_completed')
                        ->label('Follow-Up Completed')
                        ->default(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('engagement_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('stakeholder.name')
                    ->label('Stakeholder')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('method')
                    ->formatStateUsing(fn ($state) => StakeholderEngagement::METHOD_LABELS[$state] ?? $state)
                    ->colors(['primary' => fn () => true]),

                Tables\Columns\TextColumn::make('topic')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('conductedBy.name')
                    ->label('Conducted By')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('follow_up_date')
                    ->label('Follow-Up')
                    ->date()
                    ->color(fn ($record) => $record?->is_follow_up_overdue ? 'danger' : null)
                    ->sortable(),

                Tables\Columns\IconColumn::make('follow_up_completed')
                    ->label('Done')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('method')
                    ->options(StakeholderEngagement::METHOD_LABELS),
                Tables\Filters\Filter::make('pending_followup')
                    ->label('Pending Follow-Up')
                    ->query(fn ($query) => $query->where('follow_up_completed', false)->whereNotNull('follow_up_date')),
            ])
            ->defaultSort('engagement_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStakeholderEngagements::route('/'),
            'create' => Pages\CreateStakeholderEngagement::route('/create'),
            'edit'   => Pages\EditStakeholderEngagement::route('/{record}/edit'),
        ];
    }
}
