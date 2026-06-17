<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EsiaProjectRegistrationResource\Pages;
use App\Models\EsiaProjectRegistration;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EsiaProjectRegistrationResource extends Resource
{
    protected static ?string $model = EsiaProjectRegistration::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'EIA / ESIA';
    protected static ?string $navigationLabel = 'Step 1: Project Registration';
    protected static ?string $modelLabel = 'ESIA Project Registration';
    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canCreate(): bool  { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canEdit($record): bool   { return auth()->user()?->can('manage esia_audits') ?? false; }
    public static function canDelete($record): bool { return auth()->user()?->hasAnyRole(['md', 'hse_staff']) ?? false; }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Project Identity')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('project_id')
                        ->label('Project')
                        ->relationship('project', 'title')
                        ->searchable()->preload()->required()
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('esia_ref_number')
                        ->label('ESIA Reference Number')
                        ->placeholder('Auto-generated on save')
                        ->maxLength(50)
                        ->helperText('Leave blank to auto-generate (e.g. ESIA/2026/0001)'),

                    Forms\Components\Select::make('esia_class')
                        ->label('ESIA Category')
                        ->options(EsiaProjectRegistration::ESIA_CLASS_LABELS)
                        ->default('A')
                        ->required()
                        ->native(false)
                        ->live(),

                    Forms\Components\Toggle::make('esia_required')
                        ->label('Full ESIA Required?')
                        ->default(true)
                        ->helperText('Category A projects require a full ESIA.'),

                    Forms\Components\Select::make('project_type')
                        ->label('Project Type / Sector')
                        ->options(EsiaProjectRegistration::PROJECT_TYPE_LABELS)
                        ->default('industrial')
                        ->required()
                        ->native(false),
                ]),

            Forms\Components\Section::make('Proponent Details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('proponent_name')
                        ->label('Proponent / Developer Name')
                        ->required()->maxLength(255),

                    Forms\Components\TextInput::make('proponent_contact')
                        ->label('Proponent Contact (Email / Phone)')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('proponent_address')
                        ->label('Proponent Address')
                        ->maxLength(255)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Project Location')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('project_location')
                        ->label('Location / Site Description')
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('district')
                        ->label('District')->maxLength(100),

                    Forms\Components\TextInput::make('region')
                        ->label('Region')->maxLength(100),

                    Forms\Components\TextInput::make('project_area_ha')
                        ->label('Project Area (Hectares)')
                        ->numeric()->suffix('ha'),
                ]),

            Forms\Components\Section::make('Project Scope & Timeline')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('estimated_investment')
                        ->label('Estimated Investment')
                        ->numeric()->prefix('TZS'),

                    Forms\Components\DatePicker::make('proposed_start_date')
                        ->label('Proposed Start Date')->native(false),

                    Forms\Components\DatePicker::make('proposed_end_date')
                        ->label('Proposed End Date')->native(false),

                    Forms\Components\Textarea::make('project_objectives')
                        ->label('Project Objectives')
                        ->rows(3)->columnSpanFull(),

                    Forms\Components\Textarea::make('project_components')
                        ->label('Key Project Components / Activities')
                        ->rows(3)->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Lead Consultant')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('lead_consultant')
                        ->label('Lead ESIA Consultant / Firm')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('lead_consultant_contact')
                        ->label('Consultant Contact')
                        ->maxLength(255),
                ]),

            Forms\Components\Section::make('Registration Status')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('registration_status')
                        ->label('Registration Status')
                        ->options(EsiaProjectRegistration::STATUS_LABELS)
                        ->default('draft')
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('registered_by')
                        ->label('Registered By')
                        ->relationship('registeredBy', 'name')
                        ->searchable()->preload(),

                    Forms\Components\DatePicker::make('registered_at')
                        ->label('Registration Date')->native(false),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notes / Remarks')
                        ->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('esia_ref_number')
                    ->label('Ref No.')
                    ->badge()->color('gray')
                    ->searchable(),

                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')->searchable()->limit(30),

                Tables\Columns\TextColumn::make('proponent_name')
                    ->label('Proponent')->searchable()->limit(25),

                Tables\Columns\TextColumn::make('esia_class')
                    ->label('Category')
                    ->badge()
                    ->formatStateUsing(fn (?string $s): string => "Category {$s}")
                    ->color(fn (?string $state): string =>
                        EsiaProjectRegistration::ESIA_CLASS_COLORS[$state] ?? 'gray'
                    ),

                Tables\Columns\TextColumn::make('project_type')
                    ->label('Sector')
                    ->badge()->color('info')
                    ->formatStateUsing(fn (?string $s): string =>
                        EsiaProjectRegistration::PROJECT_TYPE_LABELS[$s] ?? ($s ?? '—')
                    ),

                Tables\Columns\TextColumn::make('registration_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $s): string =>
                        EsiaProjectRegistration::STATUS_LABELS[$s] ?? ($s ?? '—')
                    )
                    ->color(fn (?string $state): string =>
                        EsiaProjectRegistration::STATUS_COLORS[$state] ?? 'gray'
                    ),

                Tables\Columns\TextColumn::make('registered_at')
                    ->label('Registered')
                    ->date('d M Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('esia_class')
                    ->label('Category')
                    ->options(EsiaProjectRegistration::ESIA_CLASS_LABELS),

                Tables\Filters\SelectFilter::make('registration_status')
                    ->label('Status')
                    ->options(EsiaProjectRegistration::STATUS_LABELS),

                Tables\Filters\SelectFilter::make('project_type')
                    ->label('Sector')
                    ->options(EsiaProjectRegistration::PROJECT_TYPE_LABELS),

                Tables\Filters\Filter::make('esia_required')
                    ->label('ESIA Required Only')
                    ->query(fn ($q) => $q->where('esia_required', true))
                    ->toggle(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEsiaProjectRegistrations::route('/'),
            'create' => Pages\CreateEsiaProjectRegistration::route('/create'),
            'edit'   => Pages\EditEsiaProjectRegistration::route('/{record}/edit'),
        ];
    }
}
