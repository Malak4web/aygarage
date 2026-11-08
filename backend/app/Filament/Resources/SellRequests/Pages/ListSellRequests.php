<?php

namespace App\Filament\Resources\SellRequests\Pages;

use App\Filament\Resources\SellRequests\SellRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSellRequests extends ListRecords
{
    protected static string $resource = SellRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
