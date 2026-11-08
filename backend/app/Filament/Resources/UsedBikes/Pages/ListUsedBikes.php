<?php

namespace App\Filament\Resources\UsedBikes\Pages;

use App\Filament\Resources\UsedBikes\UsedBikeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsedBikes extends ListRecords
{
    protected static string $resource = UsedBikeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
