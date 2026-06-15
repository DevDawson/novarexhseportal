<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\LegalRegisterResource;
use App\Models\LegalRegisterItem;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ExpiringLicensesWidget extends BaseWidget
{
    protected static ?string $heading = 'Licences & Permits Expiring Within 60 Days';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 14;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff']) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                LegalRegisterItem::query()
                    ->whereNotNull('expiry_date')
                    ->where('expiry_date', '>=', now()->toDateString())
                    ->where('expiry_date', '<=', now()->addDays(60)->toDateString())
                    ->orderBy('expiry_date')
            )
            ->columns([
                Tables\Columns\TextColumn::make('requirement_title')
                    ->label('Requirement')
                    ->weight('bold')
                    ->limit(50),

                Tables\Columns\BadgeColumn::make('requirement_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string =>
                        LegalRegisterItem::REQUIREMENT_TYPE_LABELS[$state] ?? $state
                    )
                    ->colors([
                        'danger'  => 'law',
                        'warning' => 'regulation',
                        'primary' => 'permit_license',
                        'info'    => 'client_requirement',
                        'gray'    => 'other',
                    ]),

                Tables\Columns\TextColumn::make('issuing_authority')
                    ->label('Authority')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('Expires')
                    ->date('d M Y')
                    ->color(fn (LegalRegisterItem $record): string =>
                        $record->expiry_date->diffInDays(now()) <= 14
                            ? 'danger'
                            : 'warning'
                    )
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('compliance_status')
                    ->label('Compliance')
                    ->formatStateUsing(fn (string $state): string =>
                        LegalRegisterItem::COMPLIANCE_STATUS_LABELS[$state] ?? $state
                    )
                    ->colors([
                        'success' => 'compliant',
                        'danger'  => 'non_compliant',
                        'warning' => 'partially_compliant',
                        'gray'    => 'not_assessed',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->url(fn (LegalRegisterItem $record) =>
                        LegalRegisterResource::getUrl('edit', ['record' => $record])
                    ),
            ])
            ->paginated(false);
    }
}
