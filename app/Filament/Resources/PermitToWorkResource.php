<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermitToWorkResource\Pages;
use App\Models\PermitToWork;
use App\Services\PermitToWorkService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PermitToWorkResource extends Resource
{
    protected static ?string $model = PermitToWork::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'HSE & Technical Operations';

    protected static ?string $navigationLabel = 'Permit to Work';

    protected static ?string $modelLabel = 'Permit to Work';

    protected static ?int $navigationSort = 15;

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    /**
     * Anyone can request a permit (Permit Holder / Performer).
     */
    public static function canCreate(): bool
    {
        return auth()->check();
    }

    /**
     * The original requester can still edit while in Draft. Once
     * submitted, only MD / HSE staff (Issuer / Area Authority role)
     * can progress the workflow (approve, activate, suspend, close).
     */
    public static function canEdit($record): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($record->status === 'draft' && $record->requested_by === $user->id) {
            return true;
        }

        return $user->hasAnyRole(['md', 'hse_staff']);
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('md') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            // ============================================================
            // PERMIT DETAILS
            // ============================================================
            Forms\Components\Section::make('Permit Details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('permit_number')
                        ->label('Permit Number')
                        ->default(fn () => PermitToWorkService::nextPermitNumber(now()))
                        ->disabled()
                        ->dehydrated()
                        ->required(),

                    Forms\Components\Select::make('permit_type')
                        ->label('Permit Type')
                        ->options(PermitToWork::PERMIT_TYPE_LABELS)
                        ->required()
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(function (string $state, Set $set) {
                            $set('isolation_required', PermitToWorkService::requiresIsolationByDefault($state));
                            $set('gas_test_required', PermitToWorkService::requiresGasTestByDefault($state));
                        }),

                    Forms\Components\Select::make('project_id')
                        ->label('Project (if applicable)')
                        ->relationship('project', 'title')
                        ->searchable()
                        ->preload()
                        ->placeholder('Company premises / not project specific'),

                    Forms\Components\TextInput::make('location')
                        ->label('Location / Site / Equipment')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Textarea::make('description')
                        ->label('Description of Work')
                        ->required()
                        ->rows(2)
                        ->columnSpanFull(),

                    Forms\Components\DateTimePicker::make('valid_from')
                        ->label('Valid From')
                        ->native(false)
                        ->seconds(false)
                        ->default(now())
                        ->required(),

                    Forms\Components\DateTimePicker::make('valid_to')
                        ->label('Valid To')
                        ->native(false)
                        ->seconds(false)
                        ->default(now()->addHours(8))
                        ->required()
                        ->after('valid_from'),
                ]),

            // ============================================================
            // PEOPLE
            // ============================================================
            Forms\Components\Section::make('People')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('requested_by')
                        ->label('Permit Holder / Performer')
                        ->relationship('requestedBy', 'name')
                        ->searchable()
                        ->preload()
                        ->default(fn () => auth()->id())
                        ->required()
                        ->helperText('The person/team responsible for carrying out the work.'),

                    Forms\Components\Select::make('issued_by')
                        ->label('Issuer / Authorizer')
                        ->relationship('issuedBy', 'name')
                        ->searchable()
                        ->preload()
                        ->helperText('Approves and issues the permit.'),

                    Forms\Components\Select::make('area_authority_id')
                        ->label('Area Authority / Safety Officer')
                        ->relationship('areaAuthority', 'name')
                        ->searchable()
                        ->preload()
                        ->helperText('Co-signs for high-risk permits (confined space, hot work, electrical).'),
                ]),

            // ============================================================
            // HAZARDS & CONTROLS
            // ============================================================
            Forms\Components\Section::make('Hazards & Controls')
                ->columns(1)
                ->schema([
                    Forms\Components\Textarea::make('hazards_identified')
                        ->label('Hazards Identified')
                        ->rows(2),

                    Forms\Components\Textarea::make('precautions_taken')
                        ->label('Precautions / Control Measures')
                        ->rows(2),

                    Forms\Components\CheckboxList::make('ppe_required')
                        ->label('PPE Required')
                        ->options(PermitToWorkService::ppeOptions())
                        ->columns(3)
                        ->bulkToggleable(),

                    Forms\Components\Textarea::make('emergency_procedures')
                        ->label('Emergency Procedures / Rescue Plan')
                        ->rows(2),
                ]),

            // ============================================================
            // ISOLATION (Electrical Isolation / Confined Space / Hot Work)
            // ============================================================
            Forms\Components\Section::make('Isolation (Lock-Out / Tag-Out)')
                ->description('Required for Electrical Isolation and Confined Space permits - confirm equipment is isolated and locked out before work begins.')
                ->columns(1)
                ->schema([
                    Forms\Components\Toggle::make('isolation_required')
                        ->label('Isolation / LOTO Required for this Permit')
                        ->live(),

                    Forms\Components\Textarea::make('isolation_details')
                        ->label('Isolation Points / LOTO Details')
                        ->rows(2)
                        ->visible(fn (Get $get) => $get('isolation_required'))
                        ->helperText('List each isolation point, lock/tag number, and the authorized person holding the key.'),
                ]),

            // ============================================================
            // GAS TESTING (Confined Space / Hot Work)
            // ============================================================
            Forms\Components\Section::make('Gas Testing')
                ->description('Required for Confined Space Entry and Hot Work in enclosed areas - record atmospheric test results before entry/work begins.')
                ->columns(1)
                ->schema([
                    Forms\Components\Toggle::make('gas_test_required')
                        ->label('Gas Test Required for this Permit')
                        ->live(),

                    Forms\Components\Grid::make(4)
                        ->visible(fn (Get $get) => $get('gas_test_required'))
                        ->schema([
                            Forms\Components\TextInput::make('gas_test_results.o2')
                                ->label('Oxygen (O₂) %')
                                ->placeholder('20.9')
                                ->helperText('Safe: 19.5 - 23.5%'),

                            Forms\Components\TextInput::make('gas_test_results.lel')
                                ->label('LEL %')
                                ->placeholder('0')
                                ->helperText('Safe: below 10%'),

                            Forms\Components\TextInput::make('gas_test_results.h2s')
                                ->label('H₂S (ppm)')
                                ->placeholder('0')
                                ->helperText('Safe: below 10 ppm'),

                            Forms\Components\TextInput::make('gas_test_results.co')
                                ->label('CO (ppm)')
                                ->placeholder('0')
                                ->helperText('Safe: below 35 ppm'),
                        ]),

                    Forms\Components\Grid::make(2)
                        ->visible(fn (Get $get) => $get('gas_test_required'))
                        ->schema([
                            Forms\Components\TextInput::make('gas_test_results.tested_by')
                                ->label('Tested By'),

                            Forms\Components\DateTimePicker::make('gas_test_results.tested_at')
                                ->label('Test Date/Time')
                                ->native(false)
                                ->seconds(false),
                        ]),
                ]),

            // ============================================================
            // PERMIT CHECKLIST
            // ============================================================
            Forms\Components\Section::make('Permit Checklist')
                ->description('Pre-condition checks that must be verified before the permit is issued.')
                ->schema([
                    Forms\Components\Actions::make([
                        Forms\Components\Actions\Action::make('loadDefaultChecklist')
                            ->label('Load Default Checklist for this Permit Type')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('gray')
                            ->action(function (Get $get, Set $set) {
                                $type = $get('permit_type');

                                if (! $type) {
                                    return;
                                }

                                $items = collect(PermitToWorkService::defaultChecklistItems($type))
                                    ->values()
                                    ->map(fn ($item, $index) => [
                                        'item' => $item,
                                        'is_checked' => false,
                                        'remarks' => null,
                                        'sort_order' => $index,
                                    ])
                                    ->all();

                                $set('checklistItems', $items);
                            }),
                    ]),

                    Forms\Components\Repeater::make('checklistItems')
                        ->relationship('checklistItems')
                        ->label('')
                        ->schema([
                            Forms\Components\Grid::make(4)->schema([
                                Forms\Components\TextInput::make('item')
                                    ->label('Checklist Item')
                                    ->required()
                                    ->columnSpan(2),

                                Forms\Components\Toggle::make('is_checked')
                                    ->label('Verified / OK')
                                    ->inline(false),

                                Forms\Components\TextInput::make('remarks')
                                    ->label('Remarks'),
                            ]),
                        ])
                        ->addActionLabel('Add Checklist Item')
                        ->reorderable(true)
                        ->columnSpanFull(),
                ]),

            // ============================================================
            // STATUS & CLOSEOUT
            // ============================================================
            Forms\Components\Section::make('Status & Closeout')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('status')
                        ->options(PermitToWork::STATUS_LABELS)
                        ->default('draft')
                        ->required()
                        ->native(false)
                        ->live()
                        ->helperText('Draft -> Submitted -> Approved -> Active -> Closed. Use Suspended/Cancelled/Expired as needed.'),

                    Forms\Components\Textarea::make('suspension_reason')
                        ->label('Suspension Reason')
                        ->rows(2)
                        ->visible(fn (Get $get) => $get('status') === 'suspended')
                        ->required(fn (Get $get) => $get('status') === 'suspended'),

                    Forms\Components\Textarea::make('closeout_notes')
                        ->label('Closeout Notes')
                        ->rows(2)
                        ->visible(fn (Get $get) => $get('status') === 'closed')
                        ->columnSpanFull(),

                    Forms\Components\Select::make('closeout_by')
                        ->label('Closed By')
                        ->relationship('closeoutBy', 'name')
                        ->searchable()
                        ->preload()
                        ->visible(fn (Get $get) => $get('status') === 'closed')
                        ->default(fn () => auth()->id()),

                    Forms\Components\DateTimePicker::make('closeout_at')
                        ->label('Closeout Date/Time')
                        ->native(false)
                        ->seconds(false)
                        ->visible(fn (Get $get) => $get('status') === 'closed')
                        ->default(now()),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('permit_number')
                    ->label('Permit No.')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('permit_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => PermitToWork::PERMIT_TYPE_LABELS[$state] ?? $state)
                    ->colors([
                        'danger' => ['hot_work', 'confined_space', 'electrical_isolation'],
                        'warning' => ['working_at_height', 'excavation', 'lifting_operations'],
                        'gray' => ['cold_work', 'general'],
                    ]),

                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')
                    ->placeholder('Company premises')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('valid_from')
                    ->label('Valid From')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('valid_to')
                    ->label('Valid To')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->color(fn (PermitToWork $record) => $record->is_overdue ? 'danger' : null)
                    ->weight(fn (PermitToWork $record) => $record->is_overdue ? 'bold' : null),

                Tables\Columns\TextColumn::make('requestedBy.name')
                    ->label('Requested By')
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => PermitToWork::STATUS_LABELS[$state] ?? $state)
                    ->colors([
                        'gray' => 'draft',
                        'info' => 'submitted',
                        'primary' => 'approved',
                        'success' => 'active',
                        'warning' => ['suspended', 'expired'],
                        'secondary' => 'closed',
                        'danger' => 'cancelled',
                    ]),
            ])
            ->defaultSort('valid_from', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('permit_type')
                    ->label('Permit Type')
                    ->options(PermitToWork::PERMIT_TYPE_LABELS),

                Tables\Filters\SelectFilter::make('status')
                    ->options(PermitToWork::STATUS_LABELS),

                Tables\Filters\Filter::make('overdue')
                    ->label('Overdue Closeout')
                    ->query(function (Builder $query): Builder {
                        return $query
                            ->whereIn('status', ['approved', 'active', 'suspended'])
                            ->where('valid_to', '<', now());
                    }),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'title')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListPermitToWorks::route('/'),
            'create' => Pages\CreatePermitToWork::route('/create'),
            'view' => Pages\ViewPermitToWork::route('/{record}'),
            'edit' => Pages\EditPermitToWork::route('/{record}/edit'),
        ];
    }
}
