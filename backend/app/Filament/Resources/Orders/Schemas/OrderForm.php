<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('number')
                    ->label('رقم الطلب')
                    ->disabled()
                    ->dehydrated(false),
                Select::make('status')
                    ->label('حالة الطلب')
                    ->options([
                        'new' => 'طلب جديد',
                        'preparing' => 'جاري التجهيز',
                        'shipped' => 'تم الشحن',
                        'ready_for_pickup' => 'جاهز للاستلام من الجراج',
                        'delivered' => 'تم التسليم بنجاح',
                        'cancelled' => 'ملغي',
                    ])
                    ->required(),
                Select::make('payment_status')
                    ->label('حالة الدفع')
                    ->options([
                        'pending' => 'في انتظار الدفع',
                        'paid' => 'تم الدفع بنجاح',
                    ])
                    ->required(),
                Select::make('delivery_method')
                    ->label('طريقة الاستلام')
                    ->options([
                        'home' => 'توصيل للمنزل',
                        'pickup' => 'استلام من الجراج',
                    ])
                    ->required(),
                Select::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options([
                        'cod' => 'دفع عند الاستلام',
                        'vodafone' => 'فودافون كاش',
                        'instapay' => 'إنستاباي',
                        'card' => 'بطاقة بنكية',
                    ])
                    ->required(),
                DatePicker::make('delivery_date')
                    ->label('تاريخ التسليم المتوقع')
                    ->native(false),
                TextInput::make('name')
                    ->label('اسم المستلم')
                    ->required(),
                TextInput::make('phone')
                    ->label('رقم هاتف المستلم')
                    ->tel()
                    ->required(),
                TextInput::make('governorate')
                    ->label('المحافظة'),
                TextInput::make('area')
                    ->label('المنطقة / الحي'),
                TextInput::make('street')
                    ->label('العنوان بالتفصيل')
                    ->columnSpanFull(),
                TextInput::make('subtotal')
                    ->label('المجموع الفرعي (ج.م)')
                    ->numeric(),
                TextInput::make('discount')
                    ->label('الخصم (ج.م)')
                    ->numeric(),
                TextInput::make('coupon_code')
                    ->label('كوبون الخصم المستعمل'),
                TextInput::make('delivery_fee')
                    ->label('مصاريف الشحن (ج.م)')
                    ->numeric(),
                TextInput::make('total')
                    ->label('الإجمالي النهائي (ج.م)')
                    ->numeric()
                    ->required(),
                Repeater::make('items')
                    ->relationship('items')
                    ->label('منتجات الطلب')
                    ->schema([
                        TextInput::make('name_snapshot')
                            ->label('المنتج')
                            ->required(),
                        TextInput::make('option_code')
                            ->label('الخيار / الحجم'),
                        TextInput::make('unit_price')
                            ->label('سعر القطعة (ج.م)')
                            ->numeric()
                            ->required(),
                        TextInput::make('qty')
                            ->label('الكمية')
                            ->numeric()
                            ->required(),
                        TextInput::make('line_total')
                            ->label('الإجمالي (ج.م)')
                            ->numeric()
                            ->required(),
                    ])
                    ->columns(5)
                    ->collapsible()
                    ->columnSpanFull(),
            ]);
    }
}
