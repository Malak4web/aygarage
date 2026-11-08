<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم القسم')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set, $operation) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null)
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label('الرابط المخصص (Slug)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('icon')
                    ->label('الأيقونة')
                    ->placeholder('مثال: wrench أو shield')
                    ->maxLength(255),
                TextInput::make('sort')
                    ->label('الترتيب')
                    ->numeric()
                    ->default(0),
            ]);
    }
}
