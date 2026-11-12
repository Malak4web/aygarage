<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\BikeModel;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم المنتج')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set, $operation) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null)
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label('الرابط المخصص (Slug)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Select::make('category_id')
                    ->label('القسم')
                    ->relationship('category', 'name')
                    ->required(),
                TextInput::make('brand')
                    ->label('الماركة')
                    ->required()
                    ->maxLength(255),
                TextInput::make('sku')
                    ->label('كود المنتج (SKU)')
                    ->maxLength(100),
                TextInput::make('type')
                    ->label('النوع / الصنف')
                    ->maxLength(100),
                TextInput::make('color')
                    ->label('اللون')
                    ->maxLength(100),
                TextInput::make('price')
                    ->label('السعر (ج.م)')
                    ->numeric()
                    ->minValue(0)
                    ->helperText('السعر الأساسي بيظهر في الكارت، وأسعار الأحجام من الخيارات بالأسفل')
                    ->required(),
                TextInput::make('old_price')
                    ->label('السعر قبل الخصم (ج.م)')
                    ->numeric()
                    ->minValue(0)
                    ->gt('price'),
                TextInput::make('stock')
                    ->label('المخزون')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
                TextInput::make('badge')
                    ->label('شارة ترويجية')
                    ->placeholder('مثال: الأكثر مبيعاً، خصم 15%')
                    ->maxLength(100),
                Toggle::make('is_active')
                    ->label('مفعّل في المتجر')
                    ->default(true),
                Toggle::make('is_best_seller')
                    ->label('الأكثر مبيعاً')
                    ->default(false),
                Toggle::make('is_weekly_offer')
                    ->label('عرض أسبوعي')
                    ->default(false),
                TextInput::make('viscosity')
                    ->label('اللزوجة (للزّيوت)')
                    ->maxLength(100),
                TextInput::make('oil_type')
                    ->label('نوع الزيت')
                    ->maxLength(100),
                TextInput::make('fit_note')
                    ->label('ملاحظة التوافق')
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('وصف المنتج')
                    ->rows(4)
                    ->columnSpanFull(),
                Select::make('fits')
                    ->label('الموديلات المتوافقة (Zontes)')
                    ->options(fn () => BikeModel::pluck('name', 'code')->toArray())
                    ->multiple()
                    ->columnSpanFull(),
                KeyValue::make('specs')
                    ->label('المواصفات الفنية')
                    ->keyLabel('الخاصية')
                    ->valueLabel('القيمة')
                    ->columnSpanFull(),
                Repeater::make('options')
                    ->relationship('options')
                    ->label('خيارات وسعات المنتج (مثل 1 لتر، 4 لتر)')
                    ->schema([
                        TextInput::make('code')
                            ->label('الكود (مثل 1l, 4l)')
                            ->distinct()
                            ->required(),
                        TextInput::make('label')
                            ->label('اسم الخيار (مثل 1 لتر)')
                            ->required(),
                        TextInput::make('price')
                            ->label('السعر (ج.م)')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        TextInput::make('old_price')
                            ->label('السعر قبل الخصم')
                            ->numeric()
                            ->minValue(0),
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
                    ->label('صور المنتج')
                    ->orderColumn('sort')
                    ->reorderable()
                    ->addActionLabel('إضافة صورة')
                    ->schema([
                        FileUpload::make('path')
                            ->label('الصورة')
                            ->image()
                            ->maxSize(2048)
                            ->disk('public')
                            ->directory('products')
                            ->visibility('public')
                            ->required(),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->columnSpanFull(),
            ]);
    }
}
