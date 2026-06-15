<?php

namespace App\Filament\Widgets;

use App\Models\EsgTarget;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class EsgTargetsProgressWidget extends BaseWidget
{
    protected static ?string $heading = 'ESG Targets Progress';

    protected static ?int $sort = 16;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['md', 'esg_officer', 'business_director']) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                EsgTarget::query()
                    ->whereNotIn('status', ['achieved'])
                    ->orderByRaw("FIELD(status, 'off_track', 'at_risk', 'on_track', 'not_started')")
                    ->orderBy('period', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('period')
                    ->label('Period')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('category')
                    ->formatStateUsing(fn ($state) => EsgTarget::CATEGORY_LABELS[$state] ?? $state)
                    ->colors([
                        'success' => 'environmental',
                        'info'    => 'social',
                        'warning' => 'governance',
                    ]),

                Tables\Columns\TextColumn::make('indicator')
                    ->limit(45)
                    ->searchable(),

                Tables\Columns\TextColumn::make('target_value')
                    ->label('Target')
                    ->numeric(2)
                    ->suffix(fn ($record) => ' ' . $record->unit),

                Tables\Columns\TextColumn::make('actual_value')
                    ->label('Actual')
                    ->getStateUsing(fn ($record) => $record->actual_value !== null
                        ? number_format((float) $record->actual_value, 2) . ' ' . $record->unit
                        : '—'),

                Tables\Columns\TextColumn::make('progress_percent')
                    ->label('Progress')
                    ->getStateUsing(fn ($record) => $record->progress_percent !== null
                        ? number_format($record->progress_percent, 1) . '%'
                        : '—')
                    ->color(fn ($record) => match (true) {
                        $record->progress_percent === null   => null,
                        $record->progress_percent >= 100     => 'success',
                        $record->progress_percent >= 75      => 'info',
                        $record->progress_percent >= 50      => 'warning',
                        default                              => 'danger',
                    }),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn ($state) => EsgTarget::STATUS_LABELS[$state] ?? $state)
                    ->colors([
                        'success' => 'achieved',
                        'info'    => 'on_track',
                        'warning' => 'at_risk',
                        'danger'  => 'off_track',
                        'gray'    => 'not_started',
                    ]),

                Tables\Columns\TextColumn::make('owner.name')
                    ->label('Owner')
                    ->toggleable(),
            ])
            ->paginated(false);
    }
}
