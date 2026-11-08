<?php

namespace App\Filament\Resources\UsedBikes;

use App\Filament\Resources\UsedBikes\Pages\CreateUsedBike;
use App\Filament\Resources\UsedBikes\Pages\EditUsedBike;
use App\Filament\Resources\UsedBikes\Pages\ListUsedBikes;
use App\Filament\Resources\UsedBikes\Schemas\UsedBikeForm;
use App\Filament\Resources\UsedBikes\Tables\UsedBikesTable;
use App\Models\UsedBike;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UsedBikeResource extends Resource
{
    protected static ?string $model = UsedBike::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    public static function getModelLabel(): string
    {
        return 'موتوسيكل مستعمل';
    }

    public static function getPluralModelLabel(): string
    {
        return 'الموتوسيكلات المستعملة';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'الموتوسيكلات';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Schema $schema): Schema
    {
        return UsedBikeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsedBikesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsedBikes::route('/'),
            'create' => CreateUsedBike::route('/create'),
            'edit' => EditUsedBike::route('/{record}/edit'),
        ];
    }
}
