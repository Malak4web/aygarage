<?php

namespace App\Filament\Resources\UsedBikes\Schemas;

use App\Models\BikeModel;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UsedBikeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Select::make('bike_model_code')
                    ->label('الموديل')
                    ->options(fn () => BikeModel::pluck('name', 'code')->toArray())
                    ->required(),
                TextInput::make('model_name')
                    ->label('اسم الموديل التجاري')
                    ->maxLength(255),
                TextInput::make('family')
                    ->label('الفئة (مثل Adventure, Cruiser)')
                    ->maxLength(100),
                TextInput::make('year')
                    ->label('سنة الصنع (الموديل)')
                    ->numeric()
                    ->required(),
                TextInput::make('km')
                    ->label('العداد (كم)')
                    ->numeric()
                    ->required(),
                TextInput::make('price')
                    ->label('السعر (ج.م)')
                    ->numeric()
                    ->required(),
                TextInput::make('installment_monthly')
                    ->label('القسط الشهري (ج.م إن وجد)')
                    ->numeric(),
                TextInput::make('inspection_score')
                    ->label('درجة الفحص الشامل (من 100)')
                    ->numeric()
                    ->default(95),
                TextInput::make('color')
                    ->label('اللون')
                    ->maxLength(100),
                TextInput::make('owner_label')
                    ->label('صفة المالك (مثل مالك أول)')
                    ->maxLength(100),
                TextInput::make('license_until')
                    ->label('سريان الرخصة')
                    ->maxLength(100),
                Select::make('status')
                    ->label('حالة العرض')
                    ->options([
                        'available' => 'متاح للبيع',
                        'reserved' => 'محجوز',
                        'sold' => 'تم البيع',
                    ])
                    ->default('available')
                    ->required(),
                Textarea::make('notes')
                    ->label('ملاحظات عامة')
                    ->rows(3)
                    ->columnSpanFull(),
                KeyValue::make('specs')
                    ->label('المواصفات الفنية')
                    ->keyLabel('الخاصية')
                    ->valueLabel('القيمة')
                    ->columnSpanFull(),
                Repeater::make('inspections')
                    ->relationship('inspections')
                    ->label('بنود الفحص الفني (تقرير جراج أبو يوسف)')
                    ->schema([
                        TextInput::make('section')
                            ->label('بند الفحص (مثل المحرك، الكهرباء)')
                            ->required(),
                        TextInput::make('score')
                            ->label('الدرجة /100')
                            ->numeric()
                            ->required(),
                        TextInput::make('note')
                            ->label('الملاحظة'),
                        Toggle::make('is_warning')
                            ->label('تنبيه فني')
                            ->default(false),
                        TextInput::make('sort')
                            ->label('الترتيب')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(5)
                    ->collapsible()
                    ->columnSpanFull(),
                Repeater::make('images')
                    ->relationship('images')
                    ->label('صور الموتوسيكل')
                    ->orderColumn('sort')
                    ->schema([
                        FileUpload::make('path')
                            ->label('الصورة')
                            ->image()
                            ->maxSize(2048)
                            ->disk('public')
                            ->directory('bikes')
                            ->visibility('public')
                            ->required(),
                        TextInput::make('sort')
                            ->label('الترتيب')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->columnSpanFull(),
            ]);
    }
}
