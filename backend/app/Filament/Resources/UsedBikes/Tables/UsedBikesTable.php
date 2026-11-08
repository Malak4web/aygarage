<?php

namespace App\Filament\Resources\UsedBikes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsedBikesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('model_name')
                    ->label('الموتوسيكل')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('bike_model_code')
                    ->label('كود الموديل')
                    ->badge(),
                TextColumn::make('year')
                    ->label('الموديل')
                    ->sortable(),
                TextColumn::make('km')
                    ->label('العداد')
                    ->numeric()
                    ->suffix(' كم')
                    ->sortable(),
                TextColumn::make('price')
                    ->label('السعر')
                    ->formatStateUsing(fn ($state) => number_format((int) $state) . ' ج.م')
                    ->sortable(),
                TextColumn::make('inspection_score')
                    ->label('الفحص')
                    ->suffix('/100')
                    ->badge()
                    ->color('success'),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'reserved' => 'warning',
                        'sold' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => 'متاح',
                        'reserved' => 'محجوز',
                        'sold' => 'تم البيع',
                        default => $state,
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'available' => 'متاح',
                        'reserved' => 'محجوز',
                        'sold' => 'تم البيع',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
