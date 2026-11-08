<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم المنتج')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('brand')
                    ->label('الماركة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('القسم')
                    ->sortable(),
                TextColumn::make('price')
                    ->label('السعر')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('old_price')
                    ->label('قبل الخصم')
                    ->money('EGP')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('stock')
                    ->label('المخزون')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 3 => 'danger',
                        $state <= 10 => 'warning',
                        default => 'success',
                    }),
                ToggleColumn::make('is_active')
                    ->label('مفعّل'),
                IconColumn::make('is_best_seller')
                    ->label('الأكثر مبيعاً')
                    ->boolean()
                    ->toggleable(),
                IconColumn::make('is_weekly_offer')
                    ->label('عرض أسبوعي')
                    ->boolean()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('القسم')
                    ->relationship('category', 'name'),
                TernaryFilter::make('is_active')
                    ->label('الحالة'),
                Filter::make('low_stock')
                    ->label('مخزون منخفض (<= 3)')
                    ->query(fn ($query) => $query->where('stock', '<=', 3)),
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
