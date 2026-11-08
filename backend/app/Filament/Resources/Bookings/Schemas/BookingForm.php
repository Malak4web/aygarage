<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Models\Service;
use App\Models\Setting;
use App\Models\UserBike;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('number')
                    ->label('رقم الحجز')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('يُولّد تلقائياً (BK-XXXX)'),
                Select::make('user_id')
                    ->label('العميل')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->reactive()
                    ->required(),
                TextInput::make('phone')
                    ->label('هاتف التواصل')
                    ->tel()
                    ->maxLength(50),
                Select::make('service_id')
                    ->label('الخدمة المطلوبة')
                    ->relationship('service', 'name')
                    ->reactive()
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state) {
                            $price = Service::find($state)?->price;
                            if ($price) {
                                $set('price_snapshot', $price);
                            }
                        }
                    })
                    ->required(),
                Select::make('user_bike_id')
                    ->label('موتوسيكل العميل المحفوظ')
                    ->options(function ($get) {
                        $userId = $get('user_id');
                        if (!$userId) return [];
                        return UserBike::where('user_id', $userId)
                            ->get()
                            ->mapWithKeys(fn ($b) => [$b->id => ($b->plate ? $b->plate . ' · ' : '') . ($b->bikeModel?->name ?? $b->bike_model_code)])
                            ->toArray();
                    })
                    ->helperText('مصفى حسب العميل المختار'),
                TextInput::make('custom_model')
                    ->label('أو موديل آخر غير مسجل')
                    ->maxLength(255),
                DatePicker::make('date')
                    ->label('تاريخ الميعاد')
                    ->required()
                    ->native(false),
                Select::make('time')
                    ->label('توقيت الميعاد')
                    ->options(function () {
                        $slots = Setting::get('booking_slots', []);
                        if (is_string($slots)) $slots = json_decode($slots, true);
                        if (!is_array($slots)) $slots = [];
                        $opts = [];
                        foreach ($slots as $s) {
                            if (is_array($s) && isset($s['time'])) {
                                $opts[$s['time']] = $s['label'] ?? $s['time'];
                            }
                        }
                        if (empty($opts)) {
                            $opts = [
                                '11:00' => '11:00 ص',
                                '13:00' => '1:00 م',
                                '14:00' => '2:00 م',
                                '15:00' => '3:00 م',
                                '16:00' => '4:00 م',
                                '18:00' => '6:00 م',
                                '19:00' => '7:00 م',
                                '20:00' => '8:00 م',
                            ];
                        }
                        return $opts;
                    })
                    ->required(),
                TextInput::make('odometer')
                    ->label('قراءة العداد الحالية (كم)')
                    ->numeric(),
                TextInput::make('price_snapshot')
                    ->label('السعر التقديري (ج.م)')
                    ->numeric(),
                Select::make('status')
                    ->label('حالة الحجز')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'confirmed' => 'مؤكد',
                        'done' => 'تم ومكتمل',
                        'cancelled' => 'ملغي',
                    ])
                    ->default('confirmed')
                    ->required(),
                Textarea::make('notes')
                    ->label('ملاحظات الصيانة / العميل')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
