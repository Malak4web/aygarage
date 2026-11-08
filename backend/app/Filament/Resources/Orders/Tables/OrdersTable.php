<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('رقم الطلب')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable(),
                TextColumn::make('total')
                    ->label('الإجمالي')
                    ->formatStateUsing(fn ($state) => number_format((int) $state) . ' ج.م')
                    ->weight('bold')
                    ->sortable(),
                TextColumn::make('delivery_method')
                    ->label('طريقة الاستلام')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'home' ? 'توصيل' : 'استلام من الجراج'),
                TextColumn::make('payment_status')
                    ->label('الدفع')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'paid' => 'مدفوع',
                        'pending' => 'قيد الانتظار',
                        default => $state,
                    }),
                TextColumn::make('status')
                    ->label('حالة الطلب')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'info',
                        'preparing' => 'warning',
                        'shipped' => 'primary',
                        'ready_for_pickup' => 'warning',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'new' => 'طلب جديد',
                        'preparing' => 'جاري التجهيز',
                        'shipped' => 'تم الشحن',
                        'ready_for_pickup' => 'جاهز للاستلام',
                        'delivered' => 'تم التسليم',
                        'cancelled' => 'ملغي',
                        default => $state,
                    }),
                TextColumn::make('created_at')
                    ->label('تاريخ الطلب')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('حالة الطلب')
                    ->options([
                        'new' => 'طلب جديد',
                        'preparing' => 'جاري التجهيز',
                        'shipped' => 'تم الشحن',
                        'ready_for_pickup' => 'جاهز للاستلام',
                        'delivered' => 'تم التسليم',
                        'cancelled' => 'ملغي',
                    ]),
                SelectFilter::make('payment_status')
                    ->label('حالة الدفع')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'paid' => 'مدفوع',
                    ]),
                SelectFilter::make('delivery_method')
                    ->label('طريقة الاستلام')
                    ->options([
                        'home' => 'توصيل',
                        'pickup' => 'استلام من الجراج',
                    ]),
                Filter::make('filter_date')
                    ->form([
                        DatePicker::make('from_date')->label('من تاريخ'),
                        DatePicker::make('until_date')->label('إلى تاريخ'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from_date'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until_date'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
