<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LowStockProductsTable extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('تنبيه: منتجات قاربت على النفاد (المخزون 3 قطع أو أقل)')
            ->query(fn (): Builder => Product::query()->where('stock', '<=', 3)->orderBy('stock', 'asc'))
            ->columns([
                TextColumn::make('name')
                    ->label('المنتج')
                    ->weight('bold'),
                TextColumn::make('brand')
                    ->label('الماركة'),
                TextColumn::make('category.name')
                    ->label('القسم'),
                TextColumn::make('price')
                    ->label('السعر')
                    ->money('EGP'),
                TextColumn::make('stock')
                    ->label('الرصيد المتبقي')
                    ->badge()
                    ->color('danger')
                    ->suffix(' قطعة'),
            ])
            ->emptyStateHeading('المخزون ممتاز')
            ->emptyStateDescription('لا توجد أي منتجات بمخزون 3 أو أقل حالياً');
    }
}
