<?php

namespace App\Filament\Widgets;

use App\Models\CorporateDocument;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ExpiringDocumentsWidget extends BaseWidget
{
    protected static ?string $heading = 'Corporate Documents Expiring Soon';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 5;

    public static function canView(): bool
    {
        return auth()->user()?->can('manage corporate_documents') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CorporateDocument::query()
                    ->whereNotNull('expiry_date')
                    ->where('status', '!=', 'archived')
                    ->where('expiry_date', '<=', now()->addDays(60))
                    ->orderBy('expiry_date')
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->limit(40),

                Tables\Columns\BadgeColumn::make('category')
                    ->formatStateUsing(fn (string $state): string => str($state)->upper()),

                Tables\Columns\TextColumn::make('document_number')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('expiry_date')
                    ->date('d M Y')
                    ->badge()
                    ->color(fn (\Illuminate\Support\Carbon $state): string => match (true) {
                        $state->isPast() => 'danger',
                        $state->diffInDays(now()) <= 14 => 'warning',
                        default => 'gray',
                    }),
            ])
            ->paginated(false);
    }
}
