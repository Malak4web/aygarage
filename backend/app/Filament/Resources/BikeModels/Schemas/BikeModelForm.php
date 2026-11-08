<?php

namespace App\Filament\Resources\BikeModels\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BikeModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('كود الموديل (مثل ZT350-T)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),
                TextInput::make('name')
                    ->label('اسم الموديل (مثل زونتس ZT350-T)')
                    ->required()
                    ->maxLength(255),
                TextInput::make('sort')
                    ->label('الترتيب')
                    ->numeric()
                    ->default(0),
            ]);
    }
}
