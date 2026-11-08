<?php

namespace App\Filament\Resources\Bookings\Schemas;

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
                    ->required(),
                TextInput::make('phone')
                    ->label('هاتف التواصل')
                    ->tel()
                    ->maxLength(50),
                Select::make('service_id')
                    ->label('الخدمة المطلوبة')
                    ->relationship('service', 'name')
                    ->required(),
                Select::make('user_bike_id')
                    ->label('موتوسيكل العميل المحفوظ')
                    ->relationship('userBike', 'plate')
                    ->searchable()
                    ->helperText('إن وجد في حساب العميل'),
                TextInput::make('custom_model')
                    ->label('أو موديل آخر غير مسجل')
                    ->maxLength(255),
                DatePicker::make('date')
                    ->label('تاريخ الميعاد')
                    ->required()
                    ->native(false),
                TextInput::make('time')
                    ->label('توقيت الميعاد')
                    ->placeholder('مثال: 10:00 ص أو 02:00 م')
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
                    ->default('pending')
                    ->required(),
                Textarea::make('notes')
                    ->label('ملاحظات الصيانة / العميل')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
