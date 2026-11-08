<?php

namespace App\Filament\Resources\SellRequests\Pages;

use App\Filament\Resources\SellRequests\SellRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSellRequest extends EditRecord
{
    protected static string $resource = SellRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
