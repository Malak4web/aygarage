<?php

namespace App\Filament\Resources\SellRequests;

use App\Filament\Resources\SellRequests\Pages\CreateSellRequest;
use App\Filament\Resources\SellRequests\Pages\EditSellRequest;
use App\Filament\Resources\SellRequests\Pages\ListSellRequests;
use App\Filament\Resources\SellRequests\Schemas\SellRequestForm;
use App\Filament\Resources\SellRequests\Tables\SellRequestsTable;
use App\Models\SellRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SellRequestResource extends Resource
{
    protected static ?string $model = SellRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    public static function getModelLabel(): string
    {
        return 'طلب بيع موتوسيكل';
    }

    public static function getPluralModelLabel(): string
    {
        return 'طلبات بيع الموتوسيكلات';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'الموتوسيكلات';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function form(Schema $schema): Schema
    {
        return SellRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SellRequestsTable::configure($table);
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
            'index' => ListSellRequests::route('/'),
            'create' => CreateSellRequest::route('/create'),
            'edit' => EditSellRequest::route('/{record}/edit'),
        ];
    }
}
