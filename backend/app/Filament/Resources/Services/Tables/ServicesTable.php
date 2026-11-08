<?php

namespace App\Filament\Resources\Services\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('الخدمة')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('code')
                    ->label('الكود')
                    ->badge(),
                TextColumn::make('duration_label')
                    ->label('المدة'),
                TextColumn::make('price')
                    ->label('السعر')
                    ->money('EGP')
                    ->placeholder('حسب الفحص')
                    ->sortable(),
                TextColumn::make('price_label')
                    ->label('وصف السعر'),
                ToggleColumn::make('is_active')
                    ->label('مفعّلة'),
                TextColumn::make('sort')
                    ->label('الترتيب')
                    ->sortable(),
            ])
            ->defaultSort('sort', 'asc')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('الحالة'),
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
