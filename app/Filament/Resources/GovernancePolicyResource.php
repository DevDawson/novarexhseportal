<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GovernancePolicyResource\Pages;
use App\Models\GovernancePolicy;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GovernancePolicyResource extends Resource
{
    protected static ?string $model = GovernancePolicy::class;

    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'ESG';
    protected static ?string $navigationLabel = 'Policy Register';
    protected static ?string $modelLabel      = 'Policy';
    protected static ?int    $navigationSort  = 5;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage governance_policies') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage governance_policies') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage governance_policies') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'esg_officer']) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Policy Information')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Policy Title')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('policy_number')
                        ->label('Policy Number')
                        ->maxLength(50),

                    Forms\Components\Select::make('policy_type')
                        ->label('Type')
                        ->options(GovernancePolicy::TYPE_LABELS)
                        ->required(),

                    Forms\Components\TextInput::make('document_owner')
                        ->label('Document Owner')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('version')
                        ->label('Version')
                        ->default('1.0')
                        ->maxLength(20),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(GovernancePolicy::STATUS_LABELS)
                        ->required()
                        ->default('draft'),

                    Forms\Components\Select::make('approved_by')
                        ->label('Approved By')
                        ->relationship('approvedBy', 'name')
                        ->searchable()
                        ->preload(),

                    Forms\Components\Textarea::make('scope')
                        ->label('Scope')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Dates & Document')
                ->columns(2)
                ->schema([
                    Forms\Components\DatePicker::make('effective_date')
                        ->label('Effective Date')
                        ->required(),

                    Forms\Components\DatePicker::make('review_date')
                        ->label('Next Review Date')
                        ->required(),

                    Forms\Components\DatePicker::make('last_reviewed_date')
                        ->label('Last Reviewed'),

                    Forms\Components\FileUpload::make('document_file')
                        ->label('Policy Document')
                        ->acceptedFileTypes(['application/pdf', 'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                        ->directory('governance-policies')
                        ->maxSize(10240)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('policy_number')
                    ->label('No.')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\BadgeColumn::make('policy_type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => GovernancePolicy::TYPE_LABELS[$state] ?? $state)
                    ->colors(['primary' => fn () => true]),

                Tables\Columns\TextColumn::make('version')
                    ->label('Ver.')
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn ($state) => GovernancePolicy::STATUS_LABELS[$state] ?? $state)
                    ->colors([
                        'success' => 'active',
                        'warning' => ['draft', 'under_review'],
                        'gray'    => ['superseded', 'archived'],
                    ]),

                Tables\Columns\TextColumn::make('document_owner')
                    ->label('Owner')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('review_date')
                    ->label('Review Due')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => match (true) {
                        $record?->is_overdue_review => 'danger',
                        $record?->is_due_for_review => 'warning',
                        default                     => null,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('policy_type')
                    ->options(GovernancePolicy::TYPE_LABELS),
                Tables\Filters\SelectFilter::make('status')
                    ->options(GovernancePolicy::STATUS_LABELS),
                Tables\Filters\Filter::make('due_for_review')
                    ->label('Due for Review (60d)')
                    ->query(fn ($query) => $query
                        ->where('status', 'active')
                        ->where('review_date', '<=', now()->addDays(60))
                    ),
            ])
            ->defaultSort('review_date');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListGovernancePolicies::route('/'),
            'create' => Pages\CreateGovernancePolicy::route('/create'),
            'edit'   => Pages\EditGovernancePolicy::route('/{record}/edit'),
        ];
    }
}
