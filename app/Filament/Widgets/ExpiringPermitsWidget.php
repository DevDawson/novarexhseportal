<?php

namespace App\Filament\Widgets;

use App\Models\PermitToWork;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class ExpiringPermitsWidget extends BaseWidget
{
    protected static ?string $heading = 'Permits Expiring Soon / Overdue for Closeout';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 9;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'hse_staff']) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PermitToWork::query()
                    ->whereIn('status', ['approved', 'active', 'suspended'])
                    ->where('valid_to', '<=', now()->addHours(4))
                    ->orderBy('valid_to')
            )
            ->columns([
                Tables\Columns\TextColumn::make('permit_number')
                    ->label('Permit No.')
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('permit_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => PermitToWork::PERMIT_TYPE_LABELS[$state] ?? $state)
                    ->colors([
                        'danger' => ['hot_work', 'confined_space', 'electrical_isolation'],
                        'warning' => ['working_at_height', 'excavation', 'lifting_operations'],
                        'gray' => ['cold_work', 'general'],
                    ]),

                Tables\Columns\TextColumn::make('location'),

                Tables\Columns\TextColumn::make('valid_to')
                    ->label('Valid To')
                    ->dateTime('d M Y H:i')
                    ->color(fn (PermitToWork $record) => $record->is_overdue ? 'danger' : 'warning')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('requestedBy.name')
                    ->label('Permit Holder'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => PermitToWork::STATUS_LABELS[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'success',
                        'approved' => 'primary',
                        'suspended' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Open')
                    ->icon('heroicon-o-eye')
                    ->url(fn (PermitToWork $record) => route('filament.admin.resources.permit-to-works.view', $record)),
            ])
            ->paginated(false);
    }
}
