<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('كود الكوبون')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),
                Select::make('type')
                    ->label('نوع الخصم')
                    ->options([
                        'percent' => 'نسبة مئوية (%)',
                        'fixed' => 'مبلغ ثابت (ج.م)',
                    ])
                    ->default('percent')
                    ->live()
                    ->required(),
                TextInput::make('value')
                    ->label('قيمة الخصم')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(fn ($get) => $get('type') === 'percent' ? 100 : null)
                    ->required()
                    ->helperText('إذا كانت نسبة مئوية، اكتب مثلاً 10 لـ 10% (بين 1 و 100)'),
                TextInput::make('min_subtotal')
                    ->label('الحد الأدنى لقيمة الطلب (ج.م)')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                DatePicker::make('expires_at')
                    ->label('تاريخ الانتهاء')
                    ->native(false),
                Toggle::make('is_active')
                    ->label('مفعّل')
                    ->default(true),
            ]);
    }
}
