<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LessonsLearnedResource\Pages;
use App\Models\LessonsLearned;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LessonsLearnedResource extends Resource
{
    protected static ?string $model = LessonsLearned::class;

    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';

    protected static ?string $navigationGroup = 'HSE & Technical Operations';

    protected static ?string $navigationLabel = 'Lessons Learned';

    protected static ?string $modelLabel = 'Lesson Learned';

    protected static ?int $navigationSort = 9;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage incidents') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage incidents') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage incidents') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Lesson Details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('lesson_type')
                        ->options([
                            'safety' => 'Safety',
                            'environmental' => 'Environmental',
                            'process' => 'Process / Operational',
                            'quality' => 'Quality',
                            'emergency_response' => 'Emergency Response',
                            'regulatory' => 'Regulatory / Compliance',
                            'other' => 'Other',
                        ])
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('applicable_to')
                        ->options([
                            'all_projects' => 'All Projects',
                            'specific_project' => 'Specific Project',
                            'department' => 'Department',
                            'organization_wide' => 'Organization-Wide',
                        ])
                        ->required()
                        ->native(false)
                        ->live(),

                    Forms\Components\Select::make('project_id')
                        ->relationship('project', 'name')
                        ->searchable()
                        ->native(false)
                        ->visible(fn ($get) => $get('applicable_to') === 'specific_project'),

                    Forms\Components\Select::make('department_id')
                        ->relationship('department', 'name')
                        ->searchable()
                        ->native(false)
                        ->visible(fn ($get) => $get('applicable_to') === 'department'),
                ]),

            Forms\Components\Section::make('Source')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('incident_id')
                        ->label('Related Incident')
                        ->relationship('incident', 'description')
                        ->searchable()
                        ->native(false),

                    Forms\Components\Select::make('audit_id')
                        ->label('Related Audit')
                        ->relationship('audit', 'audit_reference')
                        ->searchable()
                        ->native(false),
                ]),

            Forms\Components\Section::make('Content')
                ->schema([
                    Forms\Components\Textarea::make('description')
                        ->label('What happened / What was observed')
                        ->required()
                        ->rows(4),

                    Forms\Components\Textarea::make('recommendations')
                        ->required()
                        ->rows(4),

                    Forms\Components\Textarea::make('actions_taken')
                        ->rows(3)
                        ->label('Actions Taken / Controls Implemented'),
                ]),

            Forms\Components\Section::make('Review & Publication')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('author_id')
                        ->label('Author')
                        ->options(User::all()->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->default(fn () => auth()->id())
                        ->native(false),

                    Forms\Components\Select::make('reviewed_by_id')
                        ->label('Reviewed By')
                        ->options(User::all()->pluck('name', 'id'))
                        ->searchable()
                        ->native(false),

                    Forms\Components\Select::make('status')
                        ->options(['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'])
                        ->required()
                        ->native(false),

                    Forms\Components\DateTimePicker::make('published_at')
                        ->native(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->limit(40),

                Tables\Columns\BadgeColumn::make('lesson_type')
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state)))
                    ->colors(['danger' => 'safety', 'success' => 'environmental', 'primary' => 'process', 'warning' => 'emergency_response']),

                Tables\Columns\TextColumn::make('author.name')->label('Author')->toggleable(),

                Tables\Columns\TextColumn::make('applicable_to')
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state)))
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['gray' => 'draft', 'success' => 'published', 'warning' => 'archived']),

                Tables\Columns\TextColumn::make('published_at')->dateTime('d M Y')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->date('d M Y')->sortable()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('lesson_type')
                    ->options(['safety' => 'Safety', 'environmental' => 'Environmental', 'process' => 'Process', 'quality' => 'Quality', 'emergency_response' => 'Emergency Response', 'regulatory' => 'Regulatory', 'other' => 'Other']),
                Tables\Filters\SelectFilter::make('status')
                    ->options(['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived']),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLessonsLearned::route('/'),
            'create' => Pages\CreateLessonsLearned::route('/create'),
            'edit' => Pages\EditLessonsLearned::route('/{record}/edit'),
        ];
    }
}
