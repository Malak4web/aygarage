<?php

namespace App\Filament\Resources\Bookings\Tables;

use App\Models\BikeModel;
use App\Models\Booking;
use App\Models\ServiceRecord;
use App\Models\UserBike;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

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
                    ->query(fn ($query) => $query->whereDate('date', now()->toDateString())->where('status', '!=', 'cancelled')),
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
                        Select::make('bike_choice')
                            ->label('الموتوسيكل المراد تسجيل الصيانة له')
                            ->options(function () use ($record) {
                                $opts = [];
                                if ($record->user) {
                                    foreach ($record->user->bikes as $b) {
                                        $label = ($b->plate ? $b->plate . ' · ' : '') . ($b->bikeModel?->name ?? $b->bike_model_code);
                                        $opts['bike_' . $b->id] = $label;
                                    }
                                }
                                $opts['new'] = '➕ تسجيل موتوسيكل جديد للعميل' . ($record->custom_model ? " ({$record->custom_model})" : '');
                                return $opts;
                            })
                            ->default(function () use ($record) {
                                if ($record->user_bike_id) {
                                    return 'bike_' . $record->user_bike_id;
                                }
                                $firstBike = $record->user?->bikes()->first();
                                if ($firstBike && empty($record->custom_model)) {
                                    return 'bike_' . $firstBike->id;
                                }
                                return 'new';
                            })
                            ->reactive()
                            ->required(),

                        Select::make('new_bike_model_code')
                            ->label('موديل الموتوسيكل الجديد')
                            ->options(fn () => BikeModel::pluck('name', 'code')->toArray())
                            ->default('ZT350-T')
                            ->visible(fn ($get) => $get('bike_choice') === 'new')
                            ->required(fn ($get) => $get('bike_choice') === 'new'),

                        TextInput::make('new_bike_plate')
                            ->label('رقم اللوحة')
                            ->placeholder('مثال: أ ب ج 1234 (اختياري)')
                            ->visible(fn ($get) => $get('bike_choice') === 'new'),

                        DatePicker::make('record_date')
                            ->label('تاريخ الصيانة')
                            ->default(fn () => ($record->date && $record->date->lte(now())) ? $record->date->toDateString() : now()->toDateString())
                            ->maxDate(now())
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
                        DB::transaction(function () use ($record, $data) {
                            // 1. Determine or create the bike
                            if ($data['bike_choice'] === 'new' || str_starts_with($data['bike_choice'] ?? '', 'new')) {
                                $bike = UserBike::create([
                                    'user_id' => $record->user_id,
                                    'bike_model_code' => $data['new_bike_model_code'] ?? 'ZT350-T',
                                    'model_name' => $record->custom_model,
                                    'plate' => $data['new_bike_plate'] ?? null,
                                    'year' => (int) date('Y'),
                                    'odometer' => (int) $data['odometer'],
                                    'next_service_km' => (int) $data['odometer'] + 3000,
                                    'last_visit_at' => $data['record_date'],
                                ]);
                                $record->updateQuietly(['user_bike_id' => $bike->id]);
                            } else {
                                $bikeId = (int) str_replace('bike_', '', $data['bike_choice']);
                                $bike = UserBike::where('user_id', $record->user_id)->find($bikeId);
                                if (!$bike) {
                                    $bike = $record->userBike ?? $record->user->bikes()->first();
                                }
                            }

                            // 2. Create the service record
                            ServiceRecord::create([
                                'user_bike_id' => $bike->id,
                                'booking_id' => $record->id,
                                'date' => $data['record_date'],
                                'odometer' => (int) $data['odometer'],
                                'work' => $data['work'],
                                'technician' => $data['technician'],
                                'cost' => (int) $data['cost'],
                            ]);

                            // 3. Update bike odometer, next_service_km, and last_visit_at
                            $newOdo = max($bike->odometer ?? 0, (int) $data['odometer']);
                            $bike->update([
                                'odometer' => $newOdo,
                                'next_service_km' => $newOdo + 3000,
                                'last_visit_at' => $data['record_date'],
                            ]);

                            // 4. Mark booking done
                            $record->update(['status' => 'done']);
                        });

                        Notification::make()
                            ->title('تم إنهاء الحجز وتسجيل الصيانة بنجاح')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
