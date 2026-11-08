<?php

namespace App\Filament\Resources\SellRequests\Schemas;

use App\Models\BikeModel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SellRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم العميل')
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('رقم الهاتف')
                    ->tel()
                    ->required()
                    ->maxLength(50),
                Select::make('bike_model_code')
                    ->label('موديل الموتوسيكل')
                    ->options(fn () => BikeModel::pluck('name', 'code')->toArray())
                    ->required(),
                TextInput::make('year')
                    ->label('سنة الموديل')
                    ->numeric(),
                TextInput::make('km')
                    ->label('العداد (كم)')
                    ->numeric(),
                Select::make('status')
                    ->label('حالة الطلب')
                    ->options([
                        'new' => 'جديد',
                        'contacted' => 'تم التواصل',
                        'inspected' => 'تمت المعاينة',
                        'closed' => 'مكتمل / مغلق',
                    ])
                    ->default('new')
                    ->required(),
                Textarea::make('notes')
                    ->label('ملاحظات العميل')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
