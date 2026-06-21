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

                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn ($state) => EsgTarget::CATEGORY_LABELS[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'environmental' => 'success',
                        'social'        => 'info',
                        'governance'    => 'warning',
                        default         => 'gray',
                    }),

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

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => EsgTarget::STATUS_LABELS[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'achieved'    => 'success',
                        'on_track'    => 'info',
                        'at_risk'     => 'warning',
                        'off_track'   => 'danger',
                        default       => 'gray',
                    }),

                Tables\Columns\TextColumn::make('owner.name')
                    ->label('Owner')
                    ->toggleable(),
            ])
            ->paginated(false);
    }
}
