<?php

namespace App\Filament\Resources\Bookings\Tables;

use App\Models\Booking;
use App\Models\ServiceRecord;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('رقم الحجز')
                    ->searchable()
                    ->badge()
                    ->sortable(),
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('time')
                    ->label('الميعاد')
                    ->badge()
                    ->color('info'),
                TextColumn::make('user.name')
                    ->label('العميل')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable()
                    ->default(fn ($record) => $record->user?->phone),
                TextColumn::make('service.name')
                    ->label('الخدمة')
                    ->weight('bold'),
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
            ->defaultSort('date', 'desc')
            ->filters([
                Filter::make('today')
                    ->label('حجوزات اليوم')
                    ->query(fn ($query) => $query->whereDate('date', now()->toDateString())),
                Filter::make('filter_date')
                    ->form([
                        DatePicker::make('from_date')->label('من تاريخ'),
                        DatePicker::make('until_date')->label('إلى تاريخ'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from_date'] ?? null, fn ($q, $date) => $q->whereDate('date', '>=', $date))
                            ->when($data['until_date'] ?? null, fn ($q, $date) => $q->whereDate('date', '<=', $date));
                    }),
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'confirmed' => 'مؤكد',
                        'done' => 'مكتمل',
                        'cancelled' => 'ملغي',
                    ]),
            ])
            ->recordActions([
                Action::make('completeAndRecord')
                    ->label('تم – سجّل في سجل الصيانة')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Booking $record): bool => $record->status !== 'done' && $record->status !== 'cancelled')
                    ->form(fn (Booking $record): array => [
                        DatePicker::make('record_date')
                            ->label('تاريخ الصيانة')
                            ->default($record->date ?: now()->toDateString())
                            ->required(),
                        TextInput::make('odometer')
                            ->label('العداد (كم)')
                            ->numeric()
                            ->default($record->odometer ?: ($record->userBike?->odometer ?: 0))
                            ->required(),
                        TextInput::make('work')
                            ->label('العمل المنجز / الصيانة')
                            ->default($record->service?->name ?: 'صيانة دورية')
                            ->required(),
                        TextInput::make('technician')
                            ->label('الفني المسئول')
                            ->default('أبو يوسف')
                            ->required(),
                        TextInput::make('cost')
                            ->label('التكلفة الفعلية (ج.م)')
                            ->numeric()
                            ->default($record->price_snapshot ?: ($record->service?->price ?: 0))
                            ->required(),
                    ])
                    ->action(function (Booking $record, array $data): void {
                        // 1. Mark booking done
                        $record->update(['status' => 'done']);

                        // 2. Create service record if bike is present or create bike
                        $bike = $record->userBike;
                        if (!$bike && $record->user_id) {
                            // Link to first bike if user has one
                            $bike = $record->user->bikes()->first();
                        }

                        if ($bike) {
                            ServiceRecord::create([
                                'user_bike_id' => $bike->id,
                                'booking_id' => $record->id,
                                'date' => $data['record_date'],
                                'odometer' => $data['odometer'],
                                'work' => $data['work'],
                                'technician' => $data['technician'],
                                'cost' => $data['cost'],
                            ]);

                            $bike->update([
                                'odometer' => max($bike->odometer ?? 0, (int) $data['odometer']),
                                'last_visit_at' => $data['record_date'],
                            ]);
                        }

                        Notification::make()
                            ->title('تم إنهاء الحجز وتسجيل الصيانة بنجاح')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
