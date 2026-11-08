<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TodayBookingsTable extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('جدول حجوزات صيانة اليوم في الجراج')
            ->query(fn (): Builder => Booking::query()->whereDate('date', now()->toDateString())->orderBy('time'))
            ->columns([
                TextColumn::make('number')
                    ->label('رقم الحجز')
                    ->badge(),
                TextColumn::make('time')
                    ->label('الميعاد')
                    ->badge()
                    ->color('info'),
                TextColumn::make('user.name')
                    ->label('اسم العميل')
                    ->weight('bold'),
                TextColumn::make('phone')
                    ->label('رقم الهاتف')
                    ->default(fn ($record) => $record->user?->phone),
                TextColumn::make('service.name')
                    ->label('الخدمة المطلوبة'),
                TextColumn::make('bike')
                    ->label('الموتوسيكل')
                    ->state(fn ($record) => $record->userBike ? ($record->userBike->plate ?: $record->userBike->bike_model_code) : ($record->custom_model ?: '—')),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'done' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'قيد الانتظار',
                        'confirmed' => 'مؤكد',
                        'done' => 'مكتمل',
                        'cancelled' => 'ملغي',
                        default => $state,
                    }),
            ])
            ->emptyStateHeading('لا توجد حجوزات صيانة مسجلة لليوم')
            ->emptyStateDescription('الحجوزات الجديدة لليوم ستظهر هنا تلقائياً');
    }
}
