<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('كود الخدمة')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),
                TextInput::make('name')
                    ->label('اسم الخدمة')
                    ->required()
                    ->maxLength(255),
                TextInput::make('duration_label')
                    ->label('المدة التقديرية')
                    ->placeholder('مثال: 45 - 60 دقيقة')
                    ->maxLength(100),
                TextInput::make('price')
                    ->label('السعر (ج.م)')
                    ->numeric()
                    ->helperText('اتركه فارغاً إذا كان السعر حسب الفحص والقطع'),
                TextInput::make('price_label')
                    ->label('نص السعر المخصص')
                    ->placeholder('مثال: حسب القطع أو حسب العطل')
                    ->maxLength(100),
                TextInput::make('sort')
                    ->label('الترتيب')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('مفعّلة للحجز')
                    ->default(true),
            ]);
    }
}
