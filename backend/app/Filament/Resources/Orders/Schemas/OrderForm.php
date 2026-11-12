<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Setting;
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
                TextInput::make('delivery_method')
                    ->label('طريقة الاستلام')
                    ->formatStateUsing(fn ($state) => $state === 'home' ? 'توصيل للمنزل' : 'استلام من الجراج')
                    ->disabled()
                    ->dehydrated(false),
                Select::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options(function () {
                        $methods = Setting::get('payment_methods') ?? [];
                        $opts = [];
                        if (is_array($methods)) {
                            foreach ($methods as $m) {
                                if (is_array($m) && isset($m['id'])) {
                                    $opts[$m['id']] = $m['title'] ?? $m['id'];
                                }
                            }
                        }
                        $defaults = [
                            'cod' => 'دفع عند الاستلام',
                            'pickup' => 'الدفع عند الاستلام في الجراج',
                            'vodafone' => 'فودافون كاش',
                            'instapay' => 'إنستاباي',
                            'card' => 'بطاقة بنكية',
                        ];
                        return array_merge($defaults, $opts);
                    })
                    // Chosen by the customer at checkout; shown for reference only.
                    ->disabled()
                    ->dehydrated(false),
                DatePicker::make('delivery_date')
                    ->label('تاريخ التسليم المتوقع')
                    ->native(false),
                TextInput::make('name')
                    ->label('اسم المستلم')
                    ->required(),
                TextInput::make('phone')
                    ->label('رقم هاتف المستلم')
                    ->tel()
                    ->required()
                    ->dehydrateStateUsing(fn ($state) => \App\Support\Phone::normalize((string) $state))
                    ->rule(fn () => function (string $attribute, $value, \Closure $fail) {
                        if (! preg_match('/^01[0125]\d{8}$/', \App\Support\Phone::normalize((string) $value))) {
                            $fail('رقم الموبايل لازم يكون 11 رقم ويبدأ بـ 01');
                        }
                    }),
                TextInput::make('governorate')
                    ->label('المحافظة'),
                TextInput::make('area')
                    ->label('المنطقة / الحي'),
                TextInput::make('street')
                    ->label('العنوان بالتفصيل')
                    ->columnSpanFull(),
                TextInput::make('subtotal')
                    ->label('المجموع الفرعي (ج.م)')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('discount')
                    ->label('الخصم (ج.م)')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('coupon_code')
                    ->label('كوبون الخصم المستعمل')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('delivery_fee')
                    ->label('مصاريف الشحن (ج.م)')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('total')
                    ->label('الإجمالي النهائي (ج.م)')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),
                Repeater::make('items')
                    ->relationship('items')
                    ->label('منتجات الطلب (للقراءة فقط)')
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->schema([
                        TextInput::make('name_snapshot')
                            ->label('المنتج')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('option_code')
                            ->label('الخيار / الحجم')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('unit_price')
                            ->label('سعر القطعة (ج.م)')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('qty')
                            ->label('الكمية')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('line_total')
                            ->label('الإجمالي (ج.م)')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(5)
                    ->collapsible()
                    ->columnSpanFull(),
            ]);
    }
}
