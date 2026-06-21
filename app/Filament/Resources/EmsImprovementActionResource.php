<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmsImprovementActionResource\Pages;
use App\Models\EmsImprovementAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmsImprovementActionResource extends Resource
{
    protected static ?string $model        = EmsImprovementAction::class;
    protected static ?string $navigationIcon  = 'heroicon-o-arrow-trending-up';
    protected static ?string $navigationGroup = 'Environmental Management (EMS)';
    protected static ?string $navigationLabel = 'Continual Improvement';
    protected static ?string $modelLabel      = 'CI Action';
    protected static ?string $pluralModelLabel = 'Continual Improvement Actions';
    protected static ?int    $navigationSort   = 10;

    public static function canViewAny(): bool { return auth()->user()?->hasAnyRole(['md', 'hse_staff', 'hse_manager', 'lead_auditor', 'business_director']) ?? false; }
    public static function canCreate(): bool  { return auth()->user()?->hasAnyRole(['md', 'hse_staff', 'hse_manager']) ?? false; }
    public static function canEdit($record): bool   { return auth()->user()?->hasAnyRole(['md', 'hse_staff', 'hse_manager']) ?? false; }
    public static function canDelete($record): bool { return auth()->user()?->hasRole('md') ?? false; }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Action Identity')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('reference')
                        ->label('CI Reference')
                        ->placeholder('Auto-generated')
                        ->maxLength(30)
                        ->disabled(fn ($operation) => $operation === 'edit'),

                    Forms\Components\Select::make('source')
                        ->label('Source of Improvement')
                        ->options(EmsImprovementAction::SOURCE_LABELS)
                        ->required()->native(false)->default('kpi_analysis'),

                    Forms\Components\Select::make('pdca_phase')
                        ->label('PDCA Phase')
                        ->options(EmsImprovementAction::PDCA_LABELS)
                        ->required()->native(false)->default('act'),

                    Forms\Components\TextInput::make('title')
                        ->label('Improvement Action Title')
                        ->required()->maxLength(255)->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Details & Assignment')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('priority')
                        ->options(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'])
                        ->required()->native(false)->default('medium'),

                    Forms\Components\Select::make('target_kpi')
                        ->label('Targets KPI')
                        ->options(EmsImprovementAction::TARGET_KPI_LABELS)
                        ->nullable()->native(false),

                    Forms\Components\Select::make('project_id')
                        ->label('Project')
                        ->relationship('project', 'title')
                        ->searchable()->preload()->nullable(),

                    Forms\Components\Select::make('assigned_to_id')
                        ->label('Assigned To')
                        ->relationship('assignedTo', 'name')
                        ->searchable()->preload()->nullable(),

                    Forms\Components\DatePicker::make('target_date')
                        ->label('Target Completion Date')
                        ->native(false),

                    Forms\Components\Select::make('status')
                        ->options(EmsImprovementAction::STATUS_LABELS)
                        ->required()->native(false)->default('open'),

                    Forms\Components\Textarea::make('description')
                        ->label('Description of Improvement Needed')
                        ->rows(3)->columnSpanFull(),

                    Forms\Components\Textarea::make('expected_benefit')
                        ->label('Expected Environmental Benefit')
                        ->rows(2)->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Action Taken & Closure')
                ->columns(2)
                ->collapsed()
                ->schema([
                    Forms\Components\DatePicker::make('completed_date')
                        ->label('Completed Date')->native(false),

                    Forms\Components\Select::make('raised_by_id')
                        ->label('Raised By')
                        ->relationship('raisedBy', 'name')
                        ->searchable()->preload()
                        ->default(auth()->id()),

                    Forms\Components\Textarea::make('action_taken')
                        ->label('Action Taken / Implementation Notes')
                        ->rows(4)->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Effectiveness Verification')
                ->columns(2)
                ->collapsed()
                ->schema([
                    Forms\Components\Toggle::make('effectiveness_verified')
                        ->label('Effectiveness Verified?')
                        ->inline(false),

                    Forms\Components\DatePicker::make('verified_date')
                        ->label('Verification Date')->native(false),

                    Forms\Components\Select::make('verified_by_id')
                        ->label('Verified By')
                        ->relationship('verifiedBy', 'name')
                        ->searchable()->preload()->nullable(),

                    Forms\Components\Textarea::make('effectiveness_notes')
                        ->label('Verification Notes / Evidence')
                        ->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Reference')
                    ->badge()->color('gray')
                    ->searchable()->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()->limit(35)->tooltip(fn ($record) => $record->title),

                Tables\Columns\TextColumn::make('source')
                    ->label('Source')
                    ->formatStateUsing(fn ($state) => EmsImprovementAction::SOURCE_LABELS[$s] ?? $s)
                    ->badge()->color('primary')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('pdca_phase')
                    ->label('PDCA')
                    ->badge()
                    ->formatStateUsing(fn ($state) => strtoupper($s))
                    ->color(fn ($state) => match ($state) {
                        'plan'  => 'info',
                        'do'    => 'warning',
                        'check' => 'primary',
                        'act'   => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst($s))
                    ->color(fn ($state) => EmsImprovementAction::PRIORITY_COLORS[$state] ?? 'gray'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => EmsImprovementAction::STATUS_LABELS[$s] ?? $s)
                    ->color(fn ($state) => EmsImprovementAction::STATUS_COLORS[$state] ?? 'gray'),

                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('target_date')
                    ->label('Due')
                    ->date('d M Y')
                    ->color(fn ($record) => $record->target_date?->isPast() && $record->status !== 'closed'
                        ? 'danger' : null)
                    ->sortable(),

                Tables\Columns\IconColumn::make('effectiveness_verified')
                    ->label('Verified')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(EmsImprovementAction::STATUS_LABELS),

                Tables\Filters\SelectFilter::make('pdca_phase')
                    ->label('PDCA Phase')
                    ->options(['plan' => 'Plan', 'do' => 'Do', 'check' => 'Check', 'act' => 'Act']),

                Tables\Filters\SelectFilter::make('priority')
                    ->options(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High']),

                Tables\Filters\SelectFilter::make('source')
                    ->options(EmsImprovementAction::SOURCE_LABELS),

                Tables\Filters\Filter::make('overdue')
                    ->label('Overdue Only')
                    ->query(fn ($query) => $query->whereIn('status', ['open', 'in_progress'])
                        ->whereNotNull('target_date')
                        ->where('target_date', '<', now())
                    ),
            ])
            ->defaultSort('target_date', 'asc')
            ->actions([
                // Quick close action
                Tables\Actions\Action::make('close')
                    ->label('Close')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => in_array($record->status, ['open', 'in_progress', 'completed', 'verified']))
                    ->action(function ($record) {
                        $record->update(['status' => 'closed', 'completed_date' => now()]);
                        Notification::make()->title('Action closed')->success()->send();
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index'  => Pages\ListEmsImprovementActions::route('/'),
            'create' => Pages\CreateEmsImprovementAction::route('/create'),
            'edit'   => Pages\EditEmsImprovementAction::route('/{record}/edit'),
        ];
    }
}
