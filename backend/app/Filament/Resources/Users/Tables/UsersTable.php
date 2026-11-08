<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم العميل')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('phone')
                    ->label('رقم الهاتف')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('البريد')
                    ->searchable(),
                TextColumn::make('role')
                    ->label('الصلاحية')
                    ->badge()
                    ->color(fn ($state) => $state === 'admin' ? 'danger' : 'info')
                    ->formatStateUsing(fn ($state) => $state === 'admin' ? 'مدير' : 'عميل'),
                TextColumn::make('bikes_count')
                    ->label('الموتوسيكلات')
                    ->counts('bikes')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('orders_count')
                    ->label('الطلبات')
                    ->counts('orders')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('bookings_count')
                    ->label('الحجوزات')
                    ->counts('bookings')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('role')
                    ->label('الصلاحية')
                    ->options([
                        'customer' => 'عميل',
                        'admin' => 'مدير',
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
