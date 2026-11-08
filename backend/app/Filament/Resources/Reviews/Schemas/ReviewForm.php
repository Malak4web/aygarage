<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label('المنتج')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->required(),
                TextInput::make('author_name')
                    ->label('اسم كاتب التقييم')
                    ->required()
                    ->maxLength(255),
                Select::make('rating')
                    ->label('التقييم (النجوم)')
                    ->options([
                        1 => '⭐ 1',
                        2 => '⭐⭐ 2',
                        3 => '⭐⭐⭐ 3',
                        4 => '⭐⭐⭐⭐ 4',
                        5 => '⭐⭐⭐⭐⭐ 5',
                    ])
                    ->default(5)
                    ->required(),
                Textarea::make('body')
                    ->label('نص التقييم')
                    ->rows(3)
                    ->columnSpanFull(),
                Toggle::make('is_approved')
                    ->label('معتمد وظاهر في الموقع')
                    ->default(true),
            ]);
    }
}
