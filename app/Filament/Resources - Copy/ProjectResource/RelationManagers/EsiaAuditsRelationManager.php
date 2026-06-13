<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EsiaAuditsRelationManager extends RelationManager
{
    protected static string $relationship = 'esiaAudits';

    protected static ?string $title = 'ESIA / Audits';

    protected static ?string $recordTitleAttribute = 'reference_number';

    public static function canViewForRecord(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->can('manage esia_audits') ?? false;
    }

    public function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('type')
                    ->options([
                        'esia' => 'ESIA',
                        'environmental_audit' => 'Environmental Audit',
                        'social_audit' => 'Social Audit',
                        'ohs_audit' => 'OHS Audit',
                        'compliance_audit' => 'Compliance Audit',
                    ])
                    ->required()
                    ->native(false),

                Forms\Components\TextInput::make('reference_number')
                    ->maxLength(255),
            ]),

            Forms\Components\Grid::make(2)->schema([
                Forms\Components\DatePicker::make('assessment_date')
                    ->native(false),

                Forms\Components\Select::make('lead_assessor_id')
                    ->label('Lead Assessor')
                    ->relationship('leadAssessor', 'name')
                    ->searchable()
                    ->preload(),
            ]),

            Forms\Components\Textarea::make('findings_summary')
                ->rows(3)
                ->columnSpanFull(),

            Forms\Components\Textarea::make('recommendations')
                ->rows(3)
                ->columnSpanFull(),

            Forms\Components\FileUpload::make('report_file')
                ->label('Report Document')
                ->directory('esia-audits/reports')
                ->openable()
                ->columnSpanFull(),

            Forms\Components\Select::make('status')
                ->options([
                    'draft' => 'Draft',
                    'submitted' => 'Submitted',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ])
                ->default('draft')
                ->required()
                ->native(false),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference_number')
            ->columns([
                Tables\Columns\BadgeColumn::make('type')
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title())
                    ->colors([
                        'primary' => ['esia', 'environmental_audit', 'social_audit'],
                        'info' => ['ohs_audit', 'compliance_audit'],
                    ]),

                Tables\Columns\TextColumn::make('reference_number')
                    ->searchable(),

                Tables\Columns\TextColumn::make('assessment_date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('leadAssessor.name')
                    ->label('Lead Assessor')
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'draft',
                        'info' => 'submitted',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
